package handler

import (
	"crypto/sha1"
	"database/sql"
	"encoding/hex"
	"encoding/json"
	"graphqlapplication/constant"
	"graphqlapplication/util"
	"log"
	"net/http"
	"strings"

	"github.com/gorilla/sessions"
)

type AuthHandler struct {
	DB    *sql.DB
	Store sessions.Store
}

// singleSha1 calculates sha1(password) to be stored in the session.
func singleSha1(input string) string {
	h := sha1.New()
	h.Write([]byte(input))
	return hex.EncodeToString(h.Sum(nil))
}

func (h *AuthHandler) Login(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// === CONTENT TYPE VALIDATION ===
	ct := r.Header.Get("Content-Type")

	if ct == "" {
		h.respondAuthError(w, "Invalid content type")
		return
	}

	// multipart/form-data
	if strings.HasPrefix(ct, "multipart/form-data") {
		if err := r.ParseMultipartForm(10 << 20); err != nil { // 10 MB
			h.respondAuthError(w, "Invalid multipart form")
			return
		}
	} else if strings.HasPrefix(ct, "application/x-www-form-urlencoded") {
		if err := r.ParseForm(); err != nil {
			h.respondAuthError(w, "Invalid urlencoded form")
			return
		}
	} else {
		h.respondAuthError(w, "Unsupported content type: "+ct)
		return
	}

	// Now FormValue works
	username := r.FormValue("username")
	password := r.FormValue("password")

	if username == "" || password == "" {
		h.respondAuthError(w, "Invalid credentials")
		return
	}

	// Fetch full user, like PHP: SELECT * FROM admin WHERE username = :username
	var dbAdminId, dbUsername, dbPassword string
	err := h.DB.QueryRow(
		"SELECT admin_id, username, password FROM admin WHERE username = ?",
		username,
	).Scan(&dbAdminId, &dbUsername, &dbPassword)

	if err != nil {
		if err != sql.ErrNoRows {
			log.Printf("Login database error: %v", err)
		}
		h.respondAuthError(w, "Invalid credentials")
		return
	}

	// Compare sha1(sha1(password))
	if util.DoubleSha1(password) != dbPassword {
		h.respondAuthError(w, "Invalid credentials")
		return
	}

	// Success -> save session
	session, _ := h.Store.Get(r, constant.SessionKey)
	session.Values[constant.SessionUsername] = dbUsername
	session.Values[constant.SessionPassword] = singleSha1(password)
	session.Values[constant.SessionAdminId] = dbAdminId
	session.Save(r, w)

	// Header + JSON output
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	json.NewEncoder(w).Encode(map[string]bool{"success": true})
}

// Logout
func (h *AuthHandler) Logout(w http.ResponseWriter, r *http.Request) {
	session, err := h.Store.Get(r, constant.SessionKey)
	if err != nil {
		log.Printf("Could not get session on logout: %v", err)
	}
	session.Options.MaxAge = -1
	session.Save(r, w)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]bool{"success": true})
}

func (h *AuthHandler) respondAuthError(w http.ResponseWriter, msg string) {
	w.Header().Set("HTTP/1.1", "401 Unauthorized")
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.WriteHeader(http.StatusUnauthorized)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": false,
		"message": msg,
	})
}
