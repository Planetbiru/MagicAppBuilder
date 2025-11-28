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

// NotificationHandler handles all notification-related logic.
type NotificationHandler struct {
	DB    *sql.DB
	Store *sessions.CookieStore
}

// NewNotificationHandler creates a new instance of NotificationHandler.
func NewNotificationHandler(db *sql.DB, store *sessions.CookieStore) *NotificationHandler {
	return &NotificationHandler{DB: db, Store: store}
}

// ServeHTTP is the main entry point for all requests to /notification.
// It handles session authentication, retrieves the admin's level ID, and routes requests based on the HTTP method.
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
	var adminLevelID sql.NullString
	err = h.DB.QueryRow("SELECT admin_level_id FROM admin WHERE admin_id = ?", adminID).Scan(&adminLevelID)
	if err != nil {
		// Log the specific error to help with debugging
		log.Printf("Failed to fetch admin_level_id for admin_id %s: %v", adminID, err)
		if err == sql.ErrNoRows {
			http.Error(w, util.T(ctx, "admin_not_found"), http.StatusNotFound)
		} else {
			http.Error(w, util.T(ctx, "failed_to_fetch_admin_details"), http.StatusInternalServerError)
		}
		return
	}
	adminLevelIDStr := adminLevelID.String // Use the string value, which will be empty if NULL

	if r.Method == http.MethodPost {
		h.handlePost(w, r.WithContext(ctx), adminID, adminLevelIDStr)
	} else if r.Method == http.MethodGet {
		h.handleGet(w, r.WithContext(ctx), adminID, adminLevelIDStr)
	} else {
		http.Error(w, util.T(ctx, "method_not_allowed"), http.StatusMethodNotAllowed)
	}
}

// handleGet routes GET requests. If a 'notificationId' query parameter is present,
// it shows the detail view; otherwise, it displays the notification list.
func (h *NotificationHandler) handleGet(w http.ResponseWriter, r *http.Request, adminID, adminLevelID string) {
	notificationID := r.URL.Query().Get("notificationId")
	if notificationID != "" {
		h.getDetailNotification(w, r, adminID, adminLevelID, notificationID)
	} else {
		h.getNotificationList(w, r, adminID, adminLevelID)
	}
}

// handlePost handles POST requests for actions such as marking a notification as unread or deleting it.
// It expects a multipart/form-data body with 'action' and 'notificationId' fields.
// It responds with a JSON object indicating success or failure.
func (h *NotificationHandler) handlePost(w http.ResponseWriter, r *http.Request, adminID, adminLevelID string) {
	w.Header().Set("Content-Type", "application/json")
	ctx := r.Context()

	// Use ParseMultipartForm to handle multipart/form-data.
	// 10 << 20 specifies a maximum of 10 MB for the in-memory part of the form.
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		http.Error(w, util.T(ctx, "failed_to_parse_form")+": "+err.Error(), http.StatusBadRequest)
		return
	}

	action := r.PostFormValue("action")
	notificationID := r.PostFormValue("notificationId")

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

// getDetailNotification fetches and displays the details of a single notification.
// It also marks the notification as read if it's unread and belongs to the current user.
func (h *NotificationHandler) getDetailNotification(w http.ResponseWriter, r *http.Request, adminID, adminLevelID, notificationID string) {
	ctx := r.Context()

	// Attempt to mark the notification as read.
	// This is a "fire and forget" operation; we log an error but continue even if it fails.
	_, err := h.DB.Exec("UPDATE notification SET is_read = 1, time_read = ?, ip_read = ? WHERE notification_id = ? AND (admin_id = ? OR admin_group = ?) AND (is_read = 0 OR is_read IS NULL)",
		time.Now().Format(constant.DateTimeFormat), util.GetClientIP(r), notificationID, adminID, adminLevelID)
	if err != nil {
		log.Printf("Failed to mark notification as read: %v", err) // Log error but continue
	}

	query := `SELECT 
				notification_id, notification_type, admin_group, admin_id, icon,
				subject, content, link, is_read, time_create, ip_create,
				time_read, ip_read
			FROM notification 
			WHERE (admin_id = ? OR admin_group = ?) AND notification_id = ?`

	var notif systemmodel.Notification
	err = h.DB.QueryRow(query, adminID, adminLevelID, notificationID).Scan(
		&notif.NotificationID, &notif.NotificationType, &notif.AdminGroup, &notif.AdminID, &notif.Icon,
		&notif.Subject, &notif.Content, &notif.Link, &notif.IsRead, &notif.TimeCreate, &notif.IpCreate,
		&notif.TimeRead, &notif.IpRead, // 13 fields
	)

	if err != nil && err != sql.ErrNoRows {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	pageData := systemmodel.NotificationPageData{
		Found:        err != sql.ErrNoRows,
		Notification: notif,
		AdminID:      adminID,
	}
	renderTemplate(w, r, "notification-detail.html", pageData)
}

// getNotificationList fetches a paginated and searchable list of notifications for the current user.
// It retrieves notifications targeted at the user directly or their admin group.
func (h *NotificationHandler) getNotificationList(w http.ResponseWriter, r *http.Request, adminID, adminLevelID string) {
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
	whereClause := "WHERE (admin_id = ? OR admin_group = ?)"
	params = append(params, adminID, adminLevelID)

	if search != "" {
		whereClause += " AND (subject LIKE ? OR content LIKE ?)"
		searchTerm := "%" + search + "%"
		params = append(params, searchTerm, searchTerm)
	}

	// Get the total number of records matching the filter for pagination.
	var totalRecords int
	countQuery := "SELECT COUNT(*) FROM notification " + whereClause
	err := h.DB.QueryRow(countQuery, params...).Scan(&totalRecords)
	if err != nil {
		http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
		return
	}

	// Fetch the actual notification records for the current page.
	listQuery := `SELECT notification_id, subject, content, is_read, time_create, link
            FROM notification ` + whereClause + ` ORDER BY time_create DESC LIMIT ? OFFSET ?`

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
			&notif.NotificationID, &notif.Subject, &notif.Content, &notif.IsRead, &notif.TimeCreate, &notif.Link,
		)
		if err != nil {
			log.Println("ERROR COUNT:", err)
			http.Error(w, util.T(ctx, "failed_to_fetch_details"), http.StatusInternalServerError)
			return
		}
		notifications = append(notifications, notif)
	}

	totalPages := int(math.Ceil(float64(totalRecords) / float64(pageSize)))

	pageData := systemmodel.NotificationPageData{
		Notifications: notifications, // For the list page
		TotalPages:    totalPages,    // For the list page
		CurrentPage:   page,          // For the list page
		TotalRecords:  totalRecords,  // For the list page
		SearchQuery:   search,        // For the list page
		AdminID:       adminID,       // Needed in the template
	}

	renderTemplate(w, r, "notification-list.html", pageData)
}
