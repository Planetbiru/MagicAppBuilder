package controller

import (
	"context"
	"database/sql"
	"encoding/json"
	"graphqlapplication/constant"
	"graphqlapplication/util"
	"html/template"
	"net/http"
	"path/filepath"
	"strings"
	"time"

	"github.com/gorilla/sessions"
)

// UserProfileHandler handles all logic related to the user's own profile.
type UserProfileHandler struct {
	DB    *sql.DB
	Store *sessions.CookieStore
}

// NewUserProfileHandler creates and returns a new instance of UserProfileHandler.
func NewUserProfileHandler(db *sql.DB, store *sessions.CookieStore) *UserProfileHandler {
	return &UserProfileHandler{
		DB:    db,
		Store: store,
	}
}

// AdminProfileData defines the structure for the admin's profile data fetched from the database.
type AdminProfileData struct {
	AdminID           string
	Name              sql.NullString
	Username          sql.NullString
	Gender            sql.NullString
	BirthDay          sql.NullString // Using string for DATE type for easy formatting
	Email             sql.NullString
	Phone             sql.NullString
	LanguageID        sql.NullString
	LastResetPassword sql.NullString
	Blocked           sql.NullBool
	Active            sql.NullBool
	AdminLevelName    sql.NullString
}

// PageData holds all the necessary data for rendering the user-profile.html template.
type PageData struct {
	Profile      AdminProfileData
	IsUpdateMode bool
	I18n         func(string, ...interface{}) string
	Lang         string
}

// GetProfile is the main HTTP handler for the /user-profile endpoint.
// It authenticates the user via session and routes the request to GET or POST handlers.
func (h *UserProfileHandler) GetProfile(w http.ResponseWriter, r *http.Request) {
	session, err := h.Store.Get(r, constant.SessionKey)
	if err != nil {
		http.Error(w, "Failed to get session", http.StatusInternalServerError)
		return
	}

	username, ok := session.Values[constant.SessionUsername].(string)
	if !ok || username == "" {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	if r.Method == http.MethodGet {
		h.handleGetUserProfile(w, r, username)
	} else if r.Method == http.MethodPost {
		h.handlePostUserProfile(w, r, username)
	} else {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
	}
}

// handleGetUserProfile handles GET requests to display the user's profile page.
// It can render a read-only view or an editable form based on the 'action=update' URL query parameter.
func (h *UserProfileHandler) handleGetUserProfile(w http.ResponseWriter, r *http.Request, username string) {
	ctx := r.Context()

	// Get language from header or query param, default to 'en'
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en" // Default language
	}
	// Add language to the context so it can be used by the translation utility.
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	// Create an i18n closure function to be passed to the template.
	i18nFunc := func(key string, args ...interface{}) string {
		return util.T(ctx, key, args...)
	}

	query := `
		SELECT 
			a.admin_id, a.name, a.username, a.gender, a.birth_day, a.email, a.phone, 
			a.language_id, a.last_reset_password, a.blocked, a.active,
			al.name AS admin_level_name
		FROM admin a
		LEFT JOIN admin_level al ON a.admin_level_id = al.admin_level_id
		WHERE a.username = ?`

	var profile AdminProfileData
	// Fetch the user's profile data from the database.
	err := h.DB.QueryRow(query, username).Scan(
		&profile.AdminID, &profile.Name, &profile.Username, &profile.Gender, &profile.BirthDay,
		&profile.Email, &profile.Phone, &profile.LanguageID, &profile.LastResetPassword,
		&profile.Blocked, &profile.Active, &profile.AdminLevelName,
	)

	if err != nil {
		if err == sql.ErrNoRows {
			http.Error(w, i18nFunc("admin_not_found"), http.StatusNotFound)
		} else {
			http.Error(w, i18nFunc("failed_to_fetch_details"), http.StatusInternalServerError)
		}
		return
	}

	// Determine if the page should be in edit mode.
	isUpdateMode := r.URL.Query().Get("action") == "update"

	// Prepare and parse the HTML template, injecting necessary functions.
	tmplPath := filepath.Join("template", "user-profile.html")
	tmpl, err := template.New(filepath.Base(tmplPath)).Funcs(template.FuncMap{
		"T":       i18nFunc,
		"ToLower": strings.ToLower,
	}).ParseFiles(tmplPath)

	if err != nil {
		http.Error(w, "Internal Server Error: "+err.Error(), http.StatusInternalServerError)
		return
	}

	// Assemble the data structure to be passed into the template.
	pageData := PageData{
		Profile:      profile,
		IsUpdateMode: isUpdateMode,
		I18n:         i18nFunc,
		Lang:         lang,
	}

	// Execute and render the template.
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	err = tmpl.Execute(w, pageData)
	if err != nil {
		http.Error(w, "Internal Server Error: "+err.Error(), http.StatusInternalServerError)
	}
}

// handlePostUserProfile handles POST requests to update the user's profile.
// It expects a multipart/form-data body and responds with JSON.
func (h *UserProfileHandler) handlePostUserProfile(w http.ResponseWriter, r *http.Request, username string) {
	w.Header().Set("Content-Type", "application/json")

	// Limit request body size for security and parse the multipart form.
	r.Body = http.MaxBytesReader(w, r.Body, 10<<20) // 10 MB
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		http.Error(w, "Failed to parse form", http.StatusBadRequest)
		return
	}

	// Prepare the SQL statement for the update operation.
	query := `
		UPDATE admin SET 
			name = ?, email = ?, gender = ?, birth_day = ?, phone = ? 
		WHERE username = ?`

	stmt, err := h.DB.Prepare(query)
	if err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": "Failed to update profile: " + err.Error()})
		return
	}
	defer stmt.Close()

	// Retrieve updated values from the parsed form.
	name := r.FormValue("name")
	email := r.FormValue("email")
	gender := r.FormValue("gender")
	birthDay := r.FormValue("birth_day")
	phone := r.FormValue("phone")

	// Execute the prepared statement with the new values.
	_, err = stmt.Exec(name, email, gender, birthDay, phone, username)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": "Failed to update profile: " + err.Error()})
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": "Profile updated successfully"})
}

// UpdatePassword is the main HTTP handler for the /update-password endpoint.
// It authenticates the user and routes the request to GET or POST handlers.
func (h *UserProfileHandler) UpdatePassword(w http.ResponseWriter, r *http.Request) {
	session, err := h.Store.Get(r, constant.SessionKey)
	if err != nil {
		http.Error(w, "Failed to get session", http.StatusInternalServerError)
		return
	}

	username, ok := session.Values[constant.SessionUsername].(string)
	if !ok || username == "" {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	if r.Method == http.MethodGet {
		h.handleGetUpdatePassword(w, r)
	} else if r.Method == http.MethodPost {
		h.handlePostUpdatePassword(w, r, username)
	} else {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
	}
}

// handleGetUpdatePassword handles GET requests to display the 'update-password.html' form.
func (h *UserProfileHandler) handleGetUpdatePassword(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	i18nFunc := func(key string, args ...interface{}) string {
		return util.T(ctx, key, args...)
	}

	tmplPath := filepath.Join("template", "update-password.html")
	tmpl, err := template.New(filepath.Base(tmplPath)).Funcs(template.FuncMap{
		"T": i18nFunc,
	}).ParseFiles(tmplPath)

	if err != nil {
		http.Error(w, "Internal Server Error: "+err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	err = tmpl.Execute(w, nil) // No data needed for this template
	if err != nil {
		http.Error(w, "Internal Server Error: "+err.Error(), http.StatusInternalServerError)
	}
}

// handlePostUpdatePassword handles POST requests to update the user's password.
// It validates the current password, checks for new password confirmation, and updates the database.
func (h *UserProfileHandler) handlePostUpdatePassword(w http.ResponseWriter, r *http.Request, username string) {
	w.Header().Set("Content-Type", "application/json")
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	// Parse form values from the POST request.
	r.ParseForm()
	currentPassword := r.FormValue("current_password")
	newPassword := r.FormValue("new_password")
	confirmPassword := r.FormValue("confirm_password")

	if newPassword == "" {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "new_password_required")})
		return
	}

	if newPassword != confirmPassword {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "password_mismatch")})
		return
	}

	// Fetch the current hashed password from the database to verify the user's input.
	var dbPassword string
	err := h.DB.QueryRow("SELECT password FROM admin WHERE username = ?", username).Scan(&dbPassword)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_fetch_details")})
		return
	}

	// Verify that the provided current password matches the one stored in the database.
	if util.DoubleSha1(currentPassword) != dbPassword {
		w.WriteHeader(http.StatusUnauthorized)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "incorrect_current_password")})
		return
	}

	// Hash the new password before saving it.
	newPasswordHash := util.DoubleSha1(newPassword)

	// Update the password and the 'last_reset_password' timestamp in the database.
	_, err = h.DB.Exec(
		"UPDATE admin SET password = ?, last_reset_password = ? WHERE username = ?",
		newPasswordHash,
		time.Now().Format(constant.DateTimeFormat),
		username,
	)

	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_update_password")})
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": util.T(ctx, "password_updated_successfully")})
}
