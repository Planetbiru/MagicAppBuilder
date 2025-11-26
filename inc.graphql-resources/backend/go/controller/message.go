package controller

import (
	"context"
	"database/sql"
	"encoding/json"
	"graphqlapplication/constant"
	"graphqlapplication/systemmodel"
	"graphqlapplication/util"
	"html/template"
	"log"
	"math"
	"net/http"
	"path/filepath"
	"strconv"
	"time"

	"github.com/gorilla/sessions"
)

// MessageHandler handles all message-related logic.
type MessageHandler struct {
	DB    *sql.DB
	Store *sessions.CookieStore
}

// NewMessageHandler creates a new instance of MessageHandler.
func NewMessageHandler(db *sql.DB, store *sessions.CookieStore) *MessageHandler {
	return &MessageHandler{DB: db, Store: store}
}

// ServeHTTP is the main entry point for /message requests.
func (h *MessageHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	session, err := h.Store.Get(r, constant.SessionKey)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_get_session"), http.StatusInternalServerError)
		return
	}

	adminID, ok := session.Values[constant.SessionAdminId].(string)
	if !ok || adminID == "" {
		http.Error(w, util.T(ctx, "forbidden"), http.StatusUnauthorized)
		return
	}

	if r.Method == http.MethodPost {
		h.handlePost(w, r.WithContext(ctx), adminID)
	} else if r.Method == http.MethodGet {
		h.handleGet(w, r.WithContext(ctx), adminID)
	} else {
		http.Error(w, util.T(ctx, "method_not_allowed"), http.StatusMethodNotAllowed)
	}
}

// handleGet handles GET requests for the message list or detail view.
func (h *MessageHandler) handleGet(w http.ResponseWriter, r *http.Request, adminID string) {
	messageID := r.URL.Query().Get("messageId")
	if messageID != "" {
		h.getDetailMessage(w, r, adminID, messageID)
	} else {
		h.getMessageList(w, r, adminID)
	}
}

// handlePost handles POST requests for actions on messages.
func (h *MessageHandler) handlePost(w http.ResponseWriter, r *http.Request, adminID string) {
	w.Header().Set("Content-Type", "application/json")
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	if err := r.ParseForm(); err != nil {
		http.Error(w, util.T(ctx, "failed_to_parse_form"), http.StatusBadRequest)
		return
	}

	action := r.FormValue("action")
	messageID := r.FormValue("messageId")

	if messageID == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "message_id_required")})
		return
	}

	switch action {
	case "mark_as_unread":
		_, err := h.DB.Exec("UPDATE message SET is_read = ?, time_read = NULL WHERE message_id = ? AND receiver_id = ?", false, messageID, adminID)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_update_item", "message", err.Error())})
			return
		}
		json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": util.T(ctx, "message_marked_as_unread")})

	case "delete":
		_, err := h.DB.Exec("DELETE FROM message WHERE message_id = ? AND (sender_id = ? OR receiver_id = ?)", messageID, adminID, adminID)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_delete_item", "message", err.Error())})
			return
		}
		json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": util.T(ctx, "message_deleted_successfully")})

	default:
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "invalid_action_specified")})
	}
}

// getDetailMessage fetches and displays a single message in detail.
func (h *MessageHandler) getDetailMessage(w http.ResponseWriter, r *http.Request, adminID, messageID string) {
	ctx := r.Context()

	// Mark as read
	_, err := h.DB.Exec("UPDATE message SET is_read = 1, time_read = ? WHERE message_id = ? AND receiver_id = ? AND (is_read = 0 OR is_read IS NULL)",
		time.Now().Format(constant.DateTimeFormat), messageID, adminID)
	if err != nil {
		log.Printf("Failed to mark message as read: %v", err) // Log error but continue
	}

	query := `
		SELECT m.*, mf.name AS message_folder_name, sender.name AS sender_name, receiver.name AS receiver_name
		FROM message m
		LEFT JOIN message_folder mf ON m.message_folder_id = mf.message_folder_id
		LEFT JOIN admin sender ON m.sender_id = sender.admin_id
		LEFT JOIN admin receiver ON m.receiver_id = receiver.admin_id
		WHERE (m.sender_id = ? OR m.receiver_id = ?) AND m.message_id = ?`

	var msg systemmodel.Message
	err = h.DB.QueryRow(query, adminID, adminID, messageID).Scan(
		&msg.MessageID, &msg.MessageDirection, &msg.SenderID, &msg.ReceiverID, &msg.MessageFolderID,
		&msg.Icon, &msg.Subject, &msg.Content, &msg.Link, &msg.IsRead, &msg.TimeCreate, &msg.IpCreate,
		&msg.TimeRead, &msg.IpRead, &msg.MessageFolderName, &msg.SenderName, &msg.ReceiverName,
	)

	if err != nil && err != sql.ErrNoRows {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	renderTemplate(w, r, "message-detail.html", map[string]interface{}{
		"Message": msg,
		"Found":   err != sql.ErrNoRows,
		"AdminID": adminID,
	})
}

// getMessageList fetches and displays a list of messages with pagination and search.
func (h *MessageHandler) getMessageList(w http.ResponseWriter, r *http.Request, adminID string) {
	ctx := r.Context()

	page, _ := strconv.Atoi(r.URL.Query().Get("page"))
	if page < 1 {
		page = 1
	}
	pageSize := 20 // Default page size
	offset := (page - 1) * pageSize
	search := r.URL.Query().Get("search")

	// Build query
	var params []interface{}
	whereClause := "WHERE (m.sender_id = ? OR m.receiver_id = ?)"
	params = append(params, adminID, adminID)

	if search != "" {
		whereClause += " AND (m.subject LIKE ? OR m.content LIKE ? OR sender.name LIKE ? OR receiver.name LIKE ?)"
		searchTerm := "%" + search + "%"
		params = append(params, searchTerm, searchTerm, searchTerm, searchTerm)
	}

	// Count total
	var totalRecords int
	countQuery := "SELECT COUNT(*) FROM message m LEFT JOIN admin sender ON m.sender_id = sender.admin_id LEFT JOIN admin receiver ON m.receiver_id = receiver.admin_id " + whereClause
	err := h.DB.QueryRow(countQuery, params...).Scan(&totalRecords)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	// Fetch records
	listQuery := `
		SELECT m.message_id, m.subject, m.content, m.is_read, m.time_create, m.receiver_id,
			sender.name AS sender_name, receiver.name AS receiver_name
		FROM message m
		LEFT JOIN admin receiver ON m.receiver_id = receiver.admin_id
		LEFT JOIN admin sender ON m.sender_id = sender.admin_id ` +
		whereClause + ` ORDER BY m.time_create DESC LIMIT ? OFFSET ?`

	rows, err := h.DB.Query(listQuery, append(params, pageSize, offset)...)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var messages []systemmodel.Message
	for rows.Next() {
		var msg systemmodel.Message
		err := rows.Scan(
			&msg.MessageID, &msg.Subject, &msg.Content, &msg.IsRead, &msg.TimeCreate,
			&msg.ReceiverID, &msg.SenderName, &msg.ReceiverName,
		)
		if err != nil {
			http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
			return
		}
		messages = append(messages, msg)
	}

	totalPages := int(math.Ceil(float64(totalRecords) / float64(pageSize)))

	pageData := systemmodel.MessagePageData{
		Messages:     messages,
		TotalPages:   totalPages,
		CurrentPage:  page,
		TotalRecords: totalRecords,
		SearchQuery:  search,
		AdminID:      adminID,
	}

	renderTemplate(w, r, "message-list.html", pageData)
}

// renderTemplate is a helper function to render HTML templates.
func renderTemplate(w http.ResponseWriter, r *http.Request, tmplName string, data interface{}) {
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	i18nFunc := func(key string, args ...interface{}) string {
		return util.T(ctx, key, args...)
	}

	// Function to truncate content
	truncateFunc := func(s string, length int) string {
		if len(s) > length {
			return s[:length] + "..."
		}
		return s
	}

	// Functions for pagination
	add := func(a, b int) int { return a + b }
	sub := func(a, b int) int { return a - b }

	// Function to create a number sequence (for pagination)
	seqFunc := func(start, end int) []int {
		var s []int
		for i := start; i <= end; i++ {
			s = append(s, i)
		}
		return s
	}

	tmplPath := filepath.Join("template", tmplName)
	tmpl, err := template.New(filepath.Base(tmplPath)).Funcs(template.FuncMap{
		"T":        i18nFunc,
		"truncate": truncateFunc,
		"add":      add,
		"sub":      sub,
		"seq":      seqFunc,
	}).ParseFiles(tmplPath)

	if err != nil {
		http.Error(w, util.T(ctx, "template_error", err.Error()), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	err = tmpl.Execute(w, data)
	if err != nil {
		http.Error(w, util.T(ctx, "template_execution_error", err.Error()), http.StatusInternalServerError)
	}
}
