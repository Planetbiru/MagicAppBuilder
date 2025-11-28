package controller

import (
	"context"
	"database/sql"
	"encoding/json"
	"log"
	"math"
	"net/http"
	"graphqlapplication/constant"
	"graphqlapplication/systemmodel"
	"graphqlapplication/util"
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

// handleGet routes GET requests. If a 'messageId' query parameter is present,
// it shows the detail view; otherwise, it displays the message list.
func (h *MessageHandler) handleGet(w http.ResponseWriter, r *http.Request, adminID string) {
	messageID := r.URL.Query().Get("messageId")
	if messageID != "" {
		h.getDetailMessage(w, r, adminID, messageID)
	} else {
		h.getMessageList(w, r, adminID)
	}
}

// handlePost handles POST requests for actions such as marking a message as unread or deleting it.
// It expects a multipart/form-data body with 'action' and 'messageId' fields.
// It responds with a JSON object indicating success or failure.
func (h *MessageHandler) handlePost(w http.ResponseWriter, r *http.Request, adminID string) {
	w.Header().Set("Content-Type", "application/json")
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	// Use ParseMultipartForm to handle multipart/form-data.
	// 10 << 20 specifies a maximum of 10 MB for the in-memory part of the form.
	if err := r.ParseMultipartForm(10 << 20); err != nil {
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

// getDetailMessage fetches and displays the details of a single message.
// It also marks the message as read if it's unread and belongs to the current user.
func (h *MessageHandler) getDetailMessage(w http.ResponseWriter, r *http.Request, adminID, messageID string) {
	ctx := r.Context()

	// Attempt to mark the message as read.
	// This is a "fire and forget" operation; we log an error but continue even if it fails.
	_, err := h.DB.Exec("UPDATE message SET is_read = 1, time_read = ? WHERE message_id = ? AND receiver_id = ? AND (is_read = 0 OR is_read IS NULL)",
		time.Now().Format(constant.DateTimeFormat), messageID, adminID)
	if err != nil {
		log.Printf("Failed to mark message as read: %v", err) // Log error but continue
	}

	query := `
		SELECT 
			m.message_id, m.message_direction, m.sender_id, m.receiver_id, m.message_folder_id,
			m.icon, m.subject, m.content, m.link, m.is_read, m.time_create, m.ip_create,
			m.time_read, m.ip_read,
			mf.name AS message_folder_name, sender.name AS sender_name, receiver.name AS receiver_name
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

// getMessageList fetches a paginated and searchable list of messages for the current user.
// It retrieves messages where the user is either the sender or the receiver.
func (h *MessageHandler) getMessageList(w http.ResponseWriter, r *http.Request, adminID string) {
	ctx := r.Context()

	page, _ := strconv.Atoi(r.URL.Query().Get("page"))
	if page < 1 {
		page = 1
	}
	pageSize := 20 // Default page size
	offset := (page - 1) * pageSize
	search := r.URL.Query().Get("search")

	// Build the WHERE clause and parameters for the query.
	var params []interface{}
	whereClause := "WHERE (m.sender_id = ? OR m.receiver_id = ?)"
	params = append(params, adminID, adminID)

	if search != "" {
		whereClause += " AND (m.subject LIKE ? OR m.content LIKE ? OR sender.name LIKE ? OR receiver.name LIKE ?)"
		searchTerm := "%" + search + "%"
		params = append(params, searchTerm, searchTerm, searchTerm, searchTerm)
	}

	// Get the total number of records matching the filter for pagination.
	var totalRecords int
	countQuery := "SELECT COUNT(*) FROM message m LEFT JOIN admin sender ON m.sender_id = sender.admin_id LEFT JOIN admin receiver ON m.receiver_id = receiver.admin_id " + whereClause
	err := h.DB.QueryRow(countQuery, params...).Scan(&totalRecords)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	// Fetch the actual message records for the current page.
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
