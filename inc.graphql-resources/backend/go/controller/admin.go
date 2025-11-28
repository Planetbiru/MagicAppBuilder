package controller

import (
	"context"
	"crypto/sha1"
	"database/sql"
	"encoding/json"
	"fmt"
	"math"
	"net/http"
	"graphqlapplication/constant"
	"graphqlapplication/systemmodel"
	"graphqlapplication/util"
	"strconv"
	"time"

	"github.com/gorilla/sessions"
)

// AdminHandler handles all admin-related logic.
type AdminHandler struct {
	DB    *sql.DB
	Store *sessions.CookieStore
}

// NewAdminHandler creates a new instance of AdminHandler.
func NewAdminHandler(db *sql.DB, store *sessions.CookieStore) *AdminHandler {
	return &AdminHandler{DB: db, Store: store}
}

// AdminTemplateItem is a view-specific struct for rendering in templates.
type AdminTemplateItem struct {
	AdminID        string
	Name           string
	Username       string
	Email          string
	Active         bool
	AdminLevelName string
}

// AdminDetail holds detailed information for a single admin.
type AdminDetail struct {
	AdminID        string
	Name           sql.NullString
	Username       sql.NullString
	Email          sql.NullString
	AdminLevelID   string
	AdminLevelName sql.NullString
	Active         bool
	TimeCreate     string
	TimeEdit       sql.NullString
	AdminCreate    sql.NullString
	AdminEdit      sql.NullString
}

// AdminLevel holds data for an admin level option.
type AdminLevel struct {
	ID   string
	Name string
}

// AdminFormPageData holds data for the create/edit admin form.
type AdminFormPageData struct {
	IsCreateMode bool
	Admin        AdminDetail
	AdminLevels  []AdminLevel
}

// AdminListPageData holds all the data needed to render the admin list template.
type AdminListPageData struct {
	Admins            []AdminTemplateItem
	TotalAdmins       int
	TotalPages        int
	Page              int
	Search            string
	AppAdminID        string
	HasPrev           bool
	HasNext           bool
	PrevPage          int
	NextPage          int
	StartPage         int
	EndPage           int
	ShowStartEllipsis bool
	ShowEndEllipsis   bool
}

// AdminDetailPageData holds data for the admin detail page.
type AdminDetailPageData struct {
	Admin      AdminDetail
	AppAdminID string
}

// AdminChangePasswordPageData holds data for the change password page.
type AdminChangePasswordPageData struct {
	AdminID string
}

// ListAdmins handles the request to display the list of admins.
func (h *AdminHandler) ListAdmins(w http.ResponseWriter, r *http.Request, adminID string) {
	// Parse query parameters
	search := r.URL.Query().Get("search")
	pageStr := r.URL.Query().Get("page")
	page, err := strconv.Atoi(pageStr)
	if err != nil || page < 1 {
		page = 1
	}

	// For now, we'll use a fixed page size. This can be loaded from config.
	dataLimit := 20
	offset := (page - 1) * dataLimit

	// Build query
	var args []interface{}
	countQuery := "SELECT COUNT(*) FROM admin a"
	dataQuery := `
		SELECT a.admin_id, a.name, a.username, a.email, a.active, al.name as admin_level_name
		FROM admin a
		LEFT JOIN admin_level al ON a.admin_level_id = al.admin_level_id
	`
	whereClause := ""
	if search != "" {
		whereClause = " WHERE (a.name LIKE ? OR a.username LIKE ?)"
		args = append(args, "%"+search+"%", "%"+search+"%")
	}

	// Get total count
	var totalAdmins int
	err = h.DB.QueryRow(countQuery+whereClause, args...).Scan(&totalAdmins)
	if err != nil && err != sql.ErrNoRows {
		http.Error(w, "Database error: "+err.Error(), http.StatusInternalServerError)
		return
	}

	totalPages := int(math.Ceil(float64(totalAdmins) / float64(dataLimit)))

	// Get admin data for the current page
	dataQuery += whereClause + " ORDER BY a.name LIMIT ? OFFSET ?"
	args = append(args, dataLimit, offset)

	rows, err := h.DB.Query(dataQuery, args...)
	if err != nil {
		http.Error(w, "Database error: "+err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var admins []AdminTemplateItem
	for rows.Next() {
		var admin systemmodel.AdminListItem
		if err := rows.Scan(&admin.AdminID, &admin.Name, &admin.Username, &admin.Email, &admin.Active, &admin.AdminLevelName); err != nil {
			http.Error(w, "Error scanning admin data: "+err.Error(), http.StatusInternalServerError)
			return
		}
		admins = append(admins, AdminTemplateItem{
			AdminID:        admin.AdminID,
			Name:           admin.Name.String,
			Username:       admin.Username.String,
			Email:          admin.Email.String,
			Active:         admin.Active,
			AdminLevelName: admin.AdminLevelName.String,
		})
	}

	// Pagination window logic
	window := 1
	startPage := max(1, page-window)
	endPage := min(totalPages, page+window)

	// Prepare data for the template
	data := AdminListPageData{
		Admins:            admins,
		TotalAdmins:       totalAdmins,
		TotalPages:        totalPages,
		Page:              page,
		Search:            search,
		AppAdminID:        adminID,
		HasPrev:           page > 1,
		PrevPage:          page - 1,
		HasNext:           page < totalPages,
		NextPage:          page + 1,
		StartPage:         startPage,
		EndPage:           endPage,
		ShowStartEllipsis: startPage > 2,
		ShowEndEllipsis:   endPage < totalPages-1,
	}

	renderTemplate(w, r, "admin_list.html", data)
}

// getDetailAdmin handles fetching and displaying the details of a single admin.
func (h *AdminHandler) getDetailAdmin(w http.ResponseWriter, r *http.Request, appAdminID, entityID string) {
	ctx := r.Context()
	var admin AdminDetail
	query := `
		SELECT a.admin_id, a.name, a.username, a.email, a.admin_level_id, al.name as admin_level_name,
			a.active, a.time_create, a.time_edit, ac.name as admin_create_name, ae.name as admin_edit_name
		FROM admin a
		LEFT JOIN admin_level al ON a.admin_level_id = al.admin_level_id
		LEFT JOIN admin ac ON a.admin_create = ac.admin_id
		LEFT JOIN admin ae ON a.admin_edit = ae.admin_id
		WHERE a.admin_id = ?
	`
	err := h.DB.QueryRowContext(ctx, query, entityID).Scan(
		&admin.AdminID, &admin.Name, &admin.Username, &admin.Email, &admin.AdminLevelID, &admin.AdminLevelName,
		&admin.Active, &admin.TimeCreate, &admin.TimeEdit, &admin.AdminCreate, &admin.AdminEdit,
	)

	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, util.T(ctx, "admin_not_found"), http.StatusNotFound)
		} else {
			http.Error(w, "Database error: "+err.Error(), http.StatusInternalServerError)
		}
		return
	}
	renderTemplate(w, r, "admin_detail.html", AdminDetailPageData{Admin: admin, AppAdminID: appAdminID})
}

// getAdminForm handles fetching data for and displaying the admin create/edit form.
func (h *AdminHandler) getAdminForm(w http.ResponseWriter, r *http.Request, entityID string) {
	ctx := r.Context()
	isCreateMode := entityID == ""

	data := AdminFormPageData{
		IsCreateMode: isCreateMode,
	}

	// Fetch admin levels
	levelRows, err := h.DB.QueryContext(ctx, "SELECT admin_level_id, name FROM admin_level WHERE active = 1 ORDER BY sort_order")
	if err != nil {
		http.Error(w, "Database error fetching admin levels: "+err.Error(), http.StatusInternalServerError)
		return
	}
	defer levelRows.Close()

	var adminLevels []AdminLevel
	for levelRows.Next() {
		var level AdminLevel
		if err := levelRows.Scan(&level.ID, &level.Name); err != nil {
			http.Error(w, "Error scanning admin levels: "+err.Error(), http.StatusInternalServerError)
			return
		}
		adminLevels = append(adminLevels, level)
	}
	data.AdminLevels = adminLevels

	if isCreateMode {
		// For create mode, set default values
		data.Admin.Active = true
	} else {
		// For edit mode, fetch the existing admin data
		var admin AdminDetail
		query := `SELECT admin_id, name, username, email, admin_level_id, active FROM admin WHERE admin_id = ?`
		err := h.DB.QueryRowContext(ctx, query, entityID).Scan(
			&admin.AdminID, &admin.Name, &admin.Username, &admin.Email, &admin.AdminLevelID, &admin.Active,
		)

		if err != nil {
			if err == sql.ErrNoRows {
				http.Error(w, util.T(ctx, "admin_not_found"), http.StatusNotFound)
			} else {
				http.Error(w, "Database error: "+err.Error(), http.StatusInternalServerError)
			}
			return
		}
		data.Admin = admin
	}

	renderTemplate(w, r, "admin_form.html", data)
}

// getChangePasswordForm displays the form to change an admin's password.
func (h *AdminHandler) getChangePasswordForm(w http.ResponseWriter, r *http.Request, entityID string) {
	data := AdminChangePasswordPageData{
		AdminID: entityID,
	}
	renderTemplate(w, r, "admin_change_password.html", data)
}

// ServeHTTP is the main entry point for /admin requests.
func (h *AdminHandler) ServeHTTP(w http.ResponseWriter, r *http.Request) {
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
		// If it's a GET request for the list, maybe we can show a login page or a limited view.
		// For now, we'll return unauthorized for any unauthenticated access.
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

// handleGet routes GET requests to the appropriate function based on the 'view' query parameter.
// It can display the admin list, detail page, create/edit form, or change password form.
func (h *AdminHandler) handleGet(w http.ResponseWriter, r *http.Request, adminID string) {
	ctx := r.Context()
	// Get the 'view' parameter from the URL query to determine which page to render.
	view := r.URL.Query().Get("view")
	// Get the 'adminId' parameter, which is the ID of the admin to be viewed, edited, or have their password changed.
	entityID := r.URL.Query().Get("adminId")

	// Route the request based on the 'view' parameter.
	switch view {
	case "list", "":
		// If view is "list" or not specified, display the list of admins.
		h.ListAdmins(w, r, adminID)
	case "detail":
		// If view is "detail", display the details of a specific admin.
		if entityID == "" {
			http.Error(w, util.T(ctx, "admin_id_required"), http.StatusBadRequest)
			return
		}
		h.getDetailAdmin(w, r, adminID, entityID)
	case "create":
		// If view is "create", display the form to create a new admin.
		h.getAdminForm(w, r, "") // Send an empty entityID for create mode.
	case "edit":
		// If view is "edit", display the form to edit an existing admin.
		if entityID == "" {
			http.Error(w, "Admin ID required for edit view", http.StatusBadRequest)
			return
		}
		h.getAdminForm(w, r, entityID)
	case "change-password":
		// If view is "change-password", display the form to change an admin's password.
		if entityID == "" {
			http.Error(w, "Admin ID required for change password view", http.StatusBadRequest)
			return
		}
		h.getChangePasswordForm(w, r, entityID)
	default:
		// If the 'view' parameter is invalid or doesn't match, display the admin list as a default.
		h.ListAdmins(w, r, adminID)
	}
}

// handlePost handles POST requests for actions on admins.
func (h *AdminHandler) handlePost(w http.ResponseWriter, r *http.Request, adminID string) {
	ctx := r.Context()
	w.Header().Set("Content-Type", "application/json")
	// Use ParseMultipartForm to handle both multipart/form-data and application/x-www-form-urlencoded
	// 10 << 20 specifies a maximum of 10 MB for the in-memory part of the form.
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		http.Error(w, util.T(ctx, "failed_to_parse_form")+": "+err.Error(), http.StatusBadRequest)
		return
	}

	action := r.FormValue("action")
	entityID := r.FormValue("adminId")

	var response map[string]interface{}
	var err error

	switch action {
	case "create":
		response, err = h.createAdmin(ctx, r, adminID)
	case "update":
		response, err = h.updateAdmin(ctx, r, adminID, entityID)
	case "toggle_active":
		response, err = h.toggleAdminActive(ctx, adminID, entityID)
	case "change_password":
		response, err = h.changeAdminPassword(ctx, r, entityID)
	case "delete":
		response, err = h.deleteAdmin(ctx, adminID, entityID)
	default:
		response = map[string]interface{}{"success": false, "message": util.T(ctx, "invalid_action_specified")}
	}

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(response)
}

func (h *AdminHandler) createAdmin(ctx context.Context, r *http.Request, appAdminID string) (map[string]interface{}, error) {
	password := r.FormValue("password")
	if password == "" {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "password_is_required")}, nil
	}
	hashedPassword := doubleSha1(password)
	newID := generateUniqueID()
	active := r.FormValue("active") == "on"

	sql := `INSERT INTO admin (admin_id, name, username, email, password, admin_level_id, active, time_create, admin_create, ip_create) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
	_, err := h.DB.ExecContext(ctx, sql, newID, r.FormValue("name"), r.FormValue("username"), r.FormValue("email"), hashedPassword, r.FormValue("admin_level_id"), active, time.Now(), appAdminID, r.RemoteAddr)
	if err != nil {
		return nil, fmt.Errorf(util.T(ctx, "failed_to_create_item", "Admin", err.Error()))
	}
	return map[string]interface{}{"success": true, "message": util.T(ctx, "admin_created_successfully")}, nil
}

func (h *AdminHandler) updateAdmin(ctx context.Context, r *http.Request, appAdminID, entityID string) (map[string]interface{}, error) {
	if entityID == "" {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "admin_id_required")}, nil
	}

	active := r.FormValue("active") == "on"
	adminLevelID := r.FormValue("admin_level_id")

	// Prevent self-deactivation or level change
	if entityID == appAdminID {
		active = true
		// We need to fetch the current user's level to prevent change
		var currentLevelID string
		err := h.DB.QueryRowContext(ctx, "SELECT admin_level_id FROM admin WHERE admin_id = ?", appAdminID).Scan(&currentLevelID)
		if err != nil {
			return nil, fmt.Errorf("failed to fetch current admin level: %w", err)
		}
		adminLevelID = currentLevelID
	}

	sql := `UPDATE admin SET name = ?, username = ?, email = ?, admin_level_id = ?, active = ?, 
            time_edit = ?, admin_edit = ?, ip_edit = ? WHERE admin_id = ?`
	_, err := h.DB.ExecContext(ctx, sql, r.FormValue("name"), r.FormValue("username"), r.FormValue("email"), adminLevelID, active, time.Now(), appAdminID, r.RemoteAddr, entityID)
	if err != nil {
		return nil, fmt.Errorf(util.T(ctx, "failed_to_update_item", "Admin", err.Error()))
	}
	return map[string]interface{}{"success": true, "message": util.T(ctx, "admin_updated_successfully")}, nil
}

func (h *AdminHandler) toggleAdminActive(ctx context.Context, appAdminID, entityID string) (map[string]interface{}, error) {
	if entityID == "" {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "admin_id_required")}, nil
	}
	if entityID == appAdminID {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "cannot_deactivate_self")}, nil
	}

	var currentStatus bool
	err := h.DB.QueryRowContext(ctx, "SELECT active FROM admin WHERE admin_id = ?", entityID).Scan(&currentStatus)
	if err != nil {
		return nil, fmt.Errorf("failed to fetch admin status: %w", err)
	}

	newStatus := !currentStatus
	_, err = h.DB.ExecContext(ctx, "UPDATE admin SET active = ? WHERE admin_id = ?", newStatus, entityID)
	if err != nil {
		return nil, fmt.Errorf(util.T(ctx, "failed_to_change_status", "admin", "active", err.Error()))
	}
	return map[string]interface{}{"success": true, "message": util.T(ctx, "admin_status_updated")}, nil
}

func (h *AdminHandler) changeAdminPassword(ctx context.Context, r *http.Request, entityID string) (map[string]interface{}, error) {
	if entityID == "" {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "admin_id_required")}, nil
	}
	password := r.FormValue("password")
	if password == "" {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "password_is_required")}, nil
	}

	hashedPassword := doubleSha1(password)
	_, err := h.DB.ExecContext(ctx, "UPDATE admin SET password = ? WHERE admin_id = ?", hashedPassword, entityID)
	if err != nil {
		return nil, fmt.Errorf(util.T(ctx, "failed_to_update_password"))
	}
	return map[string]interface{}{"success": true, "message": util.T(ctx, "password_updated_successfully")}, nil
}

func (h *AdminHandler) deleteAdmin(ctx context.Context, appAdminID, entityID string) (map[string]interface{}, error) {
	if entityID == "" {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "admin_id_required")}, nil
	}
	if entityID == appAdminID {
		return map[string]interface{}{"success": false, "message": util.T(ctx, "cannot_delete_self")}, nil
	}

	_, err := h.DB.ExecContext(ctx, "DELETE FROM admin WHERE admin_id = ?", entityID)
	if err != nil {
		return nil, fmt.Errorf(util.T(ctx, "failed_to_delete_item", "Admin", err.Error()))
	}
	return map[string]interface{}{"success": true, "message": util.T(ctx, "admin_deleted_successfully")}, nil
}

// doubleSha1 replicates the PHP sha1(sha1()) hashing.
// Note: This is not a secure hashing method for production. Use bcrypt or scrypt.
func doubleSha1(s string) string {
	h1 := sha1.New()
	h1.Write([]byte(s))
	bs1 := h1.Sum(nil)

	h2 := sha1.New()
	h2.Write([]byte(fmt.Sprintf("%x", bs1)))
	return fmt.Sprintf("%x", h2.Sum(nil))
}

// generateUniqueID creates a unique ID similar to PHP's uniqid().
func generateUniqueID() string {
	now := time.Now()
	sec := now.Unix()
	usec := int64(now.Nanosecond() / 1000)
	return fmt.Sprintf("%08x%05x", sec, usec)
}
