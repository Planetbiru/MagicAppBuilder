package controller

import (
	"context"
	"database/sql"
	"encoding/json"
	"graphqlapplication/constant"
	"graphqlapplication/systemmodel"
	"graphqlapplication/util"
	"log"
	"math"
	"net/http"
	"strconv"
	"time"

	"github.com/gorilla/sessions"
)

// NotificationHandler handles all notification-related logic.
type NotificationHandler struct {
	DB    *sql.DB
	Store *sessions.CookieStore
}

// NewNotificationHandler creates a new instance of NotificationHandler.
func NewNotificationHandler(db *sql.DB, store *sessions.CookieStore) *NotificationHandler {
	return &NotificationHandler{DB: db, Store: store}
}

// ServeHTTP is the main entry point for /notification requests.
func (h *NotificationHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
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

	// Get admin_level_id from the database, as it's needed for notification queries
	var adminLevelID string
	err = h.DB.QueryRow("SELECT admin_level_id FROM admin WHERE admin_id = ?", adminID).Scan(&adminLevelID)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	if r.Method == http.MethodPost {
		h.handlePost(w, r.WithContext(ctx), adminID, adminLevelID)
	} else if r.Method == http.MethodGet {
		h.handleGet(w, r.WithContext(ctx), adminID, adminLevelID)
	} else {
		http.Error(w, util.T(ctx, "method_not_allowed"), http.StatusMethodNotAllowed)
	}
}

// handleGet handles GET requests for the notification list or detail view.
func (h *NotificationHandler) handleGet(w http.ResponseWriter, r *http.Request, adminID, adminLevelID string) {
	notificationID := r.URL.Query().Get("notificationId")
	if notificationID != "" {
		h.getDetailNotification(w, r, adminID, adminLevelID, notificationID)
	} else {
		h.getNotificationList(w, r, adminID, adminLevelID)
	}
}

// handlePost handles POST requests for actions on notifications.
func (h *NotificationHandler) handlePost(w http.ResponseWriter, r *http.Request, adminID, adminLevelID string) {
	w.Header().Set("Content-Type", "application/json")
	ctx := r.Context()

	// Use ParseMultipartForm to handle form data explicitly.
	// It supports both application/x-www-form-urlencoded and multipart/form-data.
	// 10 << 20 is 10 MB, the memory size limit.
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		// If it's not multipart, try the regular ParseForm for x-www-form-urlencoded
		if err := r.ParseForm(); err != nil {
			http.Error(w, util.T(ctx, "failed_to_parse_form"), http.StatusBadRequest)
			return
		}
	}

	action := r.FormValue("action")
	notificationID := r.FormValue("notificationId")

	if notificationID == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "notification_id_required")})
		return
	}

	switch action {
	case "mark_as_unread":
		_, err := h.DB.Exec("UPDATE notification SET is_read = ?, time_read = NULL WHERE notification_id = ? AND (admin_id = ? OR admin_group = ?)",
			false, notificationID, adminID, adminLevelID)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_update_item", "notification", err.Error())})
			return
		}
		json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": util.T(ctx, "notification_marked_as_unread")})

	case "delete":
		_, err := h.DB.Exec("DELETE FROM notification WHERE notification_id = ? AND (admin_id = ? OR admin_group = ?)",
			notificationID, adminID, adminLevelID)
		if err != nil {
			w.WriteHeader(http.StatusInternalServerError)
			json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_delete_item", "notification", err.Error())})
			return
		}
		json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": util.T(ctx, "notification_deleted_successfully")})

	default:
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "invalid_action_specified")})
	}
}

// getDetailNotification fetches and displays a single notification in detail.
func (h *NotificationHandler) getDetailNotification(w http.ResponseWriter, r *http.Request, adminID, adminLevelID, notificationID string) {
	ctx := r.Context()

	// Mark as read
	_, err := h.DB.Exec("UPDATE notification SET is_read = 1, time_read = ?, ip_read = ? WHERE notification_id = ? AND (admin_id = ? OR admin_group = ?) AND (is_read = 0 OR is_read IS NULL)",
		time.Now().Format(constant.DateTimeFormat), util.GetClientIP(r), notificationID, adminID, adminLevelID)
	if err != nil {
		log.Printf("Failed to mark notification as read: %v", err) // Log error but continue
	}

	query := `SELECT * FROM notification WHERE (admin_id = ? OR admin_group = ?) AND notification_id = ?`

	var notif systemmodel.Notification
	err = h.DB.QueryRow(query, adminID, adminLevelID, notificationID).Scan(
		&notif.NotificationID, &notif.NotificationType, &notif.AdminGroup, &notif.AdminID, &notif.Icon,
		&notif.Subject, &notif.Content, &notif.Link, &notif.IsRead, &notif.TimeCreate, &notif.IpCreate,
		&notif.TimeRead, &notif.IpRead,
	)

	if err != nil && err != sql.ErrNoRows {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	renderTemplate(w, r, "notification-detail.html", map[string]interface{}{
		"Notification": notif,
		"Found":        err != sql.ErrNoRows,
	})
}

// getNotificationList fetches and displays a list of notifications with pagination and search.
func (h *NotificationHandler) getNotificationList(w http.ResponseWriter, r *http.Request, adminID, adminLevelID string) {
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
	whereClause := "WHERE (admin_id = ? OR admin_group = ?)"
	params = append(params, adminID, adminLevelID)

	if search != "" {
		whereClause += " AND (subject LIKE ? OR content LIKE ?)"
		searchTerm := "%" + search + "%"
		params = append(params, searchTerm, searchTerm)
	}

	// Count total
	var totalRecords int
	countQuery := "SELECT COUNT(*) FROM notification " + whereClause
	err := h.DB.QueryRow(countQuery, params...).Scan(&totalRecords)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	// Fetch records
	listQuery := `
		SELECT notification_id, subject, content, is_read, time_create, link
		FROM notification ` +
		whereClause + ` ORDER BY time_create DESC LIMIT ? OFFSET ?`

	rows, err := h.DB.Query(listQuery, append(params, pageSize, offset)...)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var notifications []systemmodel.Notification
	for rows.Next() {
		var notif systemmodel.Notification
		err := rows.Scan(
			&notif.NotificationID, &notif.Subject, &notif.Content, &notif.IsRead,
			&notif.TimeCreate, &notif.Link,
		)
		if err != nil {
			http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
			return
		}
		notifications = append(notifications, notif)
	}

	totalPages := int(math.Ceil(float64(totalRecords) / float64(pageSize)))

	pageData := systemmodel.NotificationPageData{
		Notifications: notifications,
		TotalPages:    totalPages,
		CurrentPage:   page,
		TotalRecords:  totalRecords,
		SearchQuery:   search,
	}

	renderTemplate(w, r, "notification-list.html", pageData)
}
