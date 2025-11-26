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

// UserProfileHandler handles user profile-related logic.
type UserProfileHandler struct {
	DB    *sql.DB
	Store *sessions.CookieStore
}

// NewUserProfileHandler creates a new instance of UserProfileHandler.
func NewUserProfileHandler(db *sql.DB, store *sessions.CookieStore) *UserProfileHandler {
	return &UserProfileHandler{
		DB:    db,
		Store: store,
	}
}

// AdminProfileData defines the structure for the admin profile data to be displayed.
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

// PageData is the structure for data passed to the HTML template.
type PageData struct {
	Profile      AdminProfileData
	IsUpdateMode bool
	I18n         func(string, ...interface{}) string
	Lang         string
}

// GetProfile handles GET and POST requests to /user-profile.
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

// handleGetUserProfile handles GET requests to display the profile or edit form.
func (h *UserProfileHandler) handleGetUserProfile(w http.ResponseWriter, r *http.Request, username string) {
	// Using the injected h.DB
	ctx := r.Context()

	// Get language from header or query param, default to 'en'
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en" // Default language
	}
	// Add language to the context so it can be used by util.T
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

	// Create an i18n closure function for the template
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

	isUpdateMode := r.URL.Query().Get("action") == "update"

	// Path to the template
	tmplPath := filepath.Join("template", "user-profile.html")
	tmpl, err := template.New(filepath.Base(tmplPath)).Funcs(template.FuncMap{
		"T":       i18nFunc,
		"ToLower": strings.ToLower,
	}).ParseFiles(tmplPath)

	if err != nil {
		http.Error(w, "Internal Server Error: "+err.Error(), http.StatusInternalServerError)
		return
	}

	pageData := PageData{
		Profile:      profile,
		IsUpdateMode: isUpdateMode,
		I18n:         i18nFunc,
		Lang:         lang,
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	err = tmpl.Execute(w, pageData)
	if err != nil {
		http.Error(w, "Internal Server Error: "+err.Error(), http.StatusInternalServerError)
	}
}

// handlePostUserProfile handles POST requests to update the profile.
func (h *UserProfileHandler) handlePostUserProfile(w http.ResponseWriter, r *http.Request, username string) {
	w.Header().Set("Content-Type", "application/json")

	// Limit body size for security
	r.Body = http.MaxBytesReader(w, r.Body, 10<<20) // 10 MB
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		http.Error(w, "Failed to parse form", http.StatusBadRequest)
		return
	}

	// Using the injected h.DB
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

	// Get values from the form
	name := r.FormValue("name")
	email := r.FormValue("email")
	gender := r.FormValue("gender")
	birthDay := r.FormValue("birth_day")
	phone := r.FormValue("phone")

	_, err = stmt.Exec(name, email, gender, birthDay, phone, username)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": "Failed to update profile: " + err.Error()})
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": "Profile updated successfully"})
}

// UpdatePassword handles GET and POST requests to /update-password.
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

// handleGetUpdatePassword handles GET requests to display the update password form.
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

// handlePostUpdatePassword handles POST requests to update the password.
func (h *UserProfileHandler) handlePostUpdatePassword(w http.ResponseWriter, r *http.Request, username string) {
	w.Header().Set("Content-Type", "application/json")
	ctx := r.Context()
	lang := r.Header.Get("X-Language-Id")
	if lang == "" {
		lang = "en"
	}
	ctx = context.WithValue(ctx, constant.LanguageKey, lang)

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

	// Fetch the current user's hashed password
	var dbPassword string
	err := h.DB.QueryRow("SELECT password FROM admin WHERE username = ?", username).Scan(&dbPassword)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "failed_to_fetch_details")})
		return
	}

	// Verify the current password
	if util.DoubleSha1(currentPassword) != dbPassword {
		w.WriteHeader(http.StatusUnauthorized)
		json.NewEncoder(w).Encode(map[string]interface{}{"success": false, "message": util.T(ctx, "incorrect_current_password")})
		return
	}

	// Hash the new password
	newPasswordHash := util.DoubleSha1(newPassword)

	// Update the database
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

	// Invalidate the old session password hash.
	// This is good practice but would require re-login. Let's just return success for now.

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]interface{}{"success": true, "message": util.T(ctx, "password_updated_successfully")})
}
