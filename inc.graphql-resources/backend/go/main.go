package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net"
	"net/http"
	"os"
	"path/filepath"
	"graphqlapplication/constant"
	"graphqlapplication/controller"
	"graphqlapplication/handler"
	"graphqlapplication/resolver"
	"graphqlapplication/util"
	"strconv"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"github.com/gorilla/sessions"
	"github.com/graph-gophers/graphql-go"
	"github.com/graph-gophers/graphql-go/relay"
	"github.com/joho/godotenv"
	_ "modernc.org/sqlite"
)

var store *sessions.CookieStore

// ipMiddleware injects the client's IP address into the request context.
func ipMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		ip := util.GetClientIP(r)

		session, err := store.Get(r, constant.SessionKey)
		if err != nil {
			log.Printf("session error: %v", err)
		}

		var adminId string
		if v, ok := session.Values[constant.SessionAdminId]; ok {
			if s, ok2 := v.(string); ok2 {
				adminId = s
			}
		}

		ctx := r.Context()
		ctx = context.WithValue(ctx, constant.RemoteAddr, ip)          // NOSONAR
		ctx = context.WithValue(ctx, constant.SessionAdminId, adminId) // NOSONAR

		next.ServeHTTP(w, r.WithContext(ctx))
	})
}

// authMiddleware checks if a user is logged in before allowing access to a handler.
// It only enforces the check if the REQUIRE_LOGIN environment variable is set to "true".
func authMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// Only check for login if REQUIRE_LOGIN is explicitly true
		if os.Getenv("REQUIRE_LOGIN") != "true" {
			next.ServeHTTP(w, r)
			return
		}

		session, _ := store.Get(r, constant.SessionKey)
		username, ok := session.Values[constant.SessionUsername].(string)

		if !ok || username == "" {
			http.Error(w, "Unauthorized", http.StatusUnauthorized)
			return
		}

		next.ServeHTTP(w, r)
	})
}
func GetClientIP(r *http.Request) string {
	// Check for X-Forwarded-For header, which can be a comma-separated list.
	// The client's IP is typically the first one.
	if forwardedFor := r.Header.Get("X-Forwarded-For"); forwardedFor != "" {
		// Split the list and return the first IP
		ips := strings.Split(forwardedFor, ",")
		if len(ips) > 0 {
			return strings.TrimSpace(ips[0])
		}
	}

	// Check for X-Real-IP header.
	if realIP := r.Header.Get("X-Real-IP"); realIP != "" {
		return realIP
	}

	// Fallback to the remote address.
	ip, _, _ := net.SplitHostPort(r.RemoteAddr)
	return ip
}

// getDBConfig reads database configuration from environment variables
// and returns the driver name and the Data Source Name (DSN).
func getDBConfig() (string, string) {
	driver := os.Getenv("DB_DRIVER")
	var dsn string

	switch driver {
	case "sqlite":
		dsn = os.Getenv("DB_FILE")
		if dsn == "" {
			log.Fatal("DB_DRIVER is set to sqlite, but DB_FILE environment variable is not set.")
		}
	case "mysql":
		dsn = fmt.Sprintf("%s:%s@tcp(%s:%s)/%s",
			os.Getenv("DB_USER"),
			os.Getenv("DB_PASS"),
			os.Getenv("DB_HOST"),
			os.Getenv("DB_PORT"),
			os.Getenv("DB_NAME"),
		)
	default:
		log.Fatalf("Unsupported database driver: %s. Supported drivers are 'mysql' and 'sqlite'.", driver)
	}
	return driver, dsn
}

func registerRoutes(db *sql.DB, store *sessions.CookieStore) {
	// Read GraphQL schema from file
	schemaPath := os.Getenv("GRAPHQL_SCHEMA")
	schemaData, err := os.ReadFile(schemaPath)
	if err != nil {
		log.Fatalf("Failed to read schema file %s: %v", schemaPath, err)
	}

	// Parse GraphQL schema
	schema := graphql.MustParseSchema(string(schemaData), resolver.NewRootResolver(db))

	// Set handler for GraphQL endpoint
	graphqlEndpoint := os.Getenv("GRAPHQL_ENDPOINT")
	http.Handle(graphqlEndpoint, ipMiddleware(&relay.Handler{Schema: schema}))

	// Initialize and register authentication handlers
	authHandler := &handler.AuthHandler{
		DB:    db,
		Store: store,
	}
	http.HandleFunc("/login", authHandler.Login)
	http.HandleFunc("/logout", authHandler.Logout)

	// Initialize and register UserProfileHandler
	userProfileHandler := controller.NewUserProfileHandler(db, store)
	http.HandleFunc("/user-profile", userProfileHandler.GetProfile) // Example route
	http.HandleFunc("/update-password", userProfileHandler.UpdatePassword)

	// Initialize and register AdminHandler
	adminHandler := controller.NewAdminHandler(db, store)
	http.HandleFunc("/admin", adminHandler.ListAdmins)

	// Initialize and register MessageHandler
	messageHandler := controller.NewMessageHandler(db, store)
	http.Handle("/message", messageHandler)

	// Initialize and register NotificationHandler
	notificationHandler := controller.NewNotificationHandler(db, store)
	http.Handle("/notification", notificationHandler)

	// Handler for available themes
	http.HandleFunc("/available-theme", availableThemesHandler)

	// Handlers for static assets
	http.Handle("/assets/", http.StripPrefix("/assets/", http.FileServer(http.Dir("static/assets"))))
	http.Handle("/langs/", http.StripPrefix("/langs/", http.FileServer(http.Dir("static/langs"))))

	// Handler for frontend configuration, protected by authMiddleware
	frontendConfigHandler := http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Cache-Control", "no-store, no-cache, must-revalidate, proxy-revalidate")
		w.Header().Set("Pragma", "no-cache")
		w.Header().Set("Expires", "0")
		w.Header().Set("Surrogate-Control", "no-store")
		http.ServeFile(w, r, "static/config/frontend-config.json")
	})
	http.Handle("/frontend-config", authMiddleware(frontendConfigHandler))

	// Handler for serving the root (index.html) and favicon
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/" && r.URL.Path != "/index.html" {
			http.NotFound(w, r)
			return
		}
		http.ServeFile(w, r, "static/index.html")
	})
	http.HandleFunc("/favicon.ico", func(w http.ResponseWriter, r *http.Request) {
		http.ServeFile(w, r, "static/favicon.ico")
	})

	// Log server endpoints
	serverPort := os.Getenv("SERVER_PORT")
	log.Printf("Server is running at: http://localhost:%s", serverPort)
	log.Printf("GraphQL endpoint is available at http://localhost:%s%s", serverPort, graphqlEndpoint)
}

func main() {
	// Load variables from .env file
	err := godotenv.Load()
	if err != nil {
		log.Println("Warning: Could not load .env file. Using system environment variables.")
	}

	// Initialize session store
	sessionSecret := os.Getenv("SESSION_SECRET")
	if sessionSecret == "" {
		log.Fatal("SESSION_SECRET environment variable is not set")
	}
	store = sessions.NewCookieStore([]byte(sessionSecret))

	// Initialize i18n translations
	util.InitI18n("static/langs/i18n")

	// Get database configuration
	driver, dsn := getDBConfig()

	// Open database connection
	db, err := sql.Open(driver, dsn)
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	// Register all application routes
	registerRoutes(db, store)

	// Run HTTP server
	serverPort := os.Getenv("SERVER_PORT")
	log.Fatal(http.ListenAndServe(":"+serverPort, nil))
}

func availableThemesHandler(w http.ResponseWriter, r *http.Request) {
	themesPath := "static/assets/themes"

	type Theme struct {
		Name  string `json:"name"`
		Title string `json:"title"`
	}

	var themes []Theme

	entries, err := os.ReadDir(themesPath)
	if err != nil {
		log.Printf("Warning: Could not read themes directory '%s': %v", themesPath, err)
	} else {
		for _, entry := range entries {
			if entry.IsDir() {
				themeName := entry.Name()
				cssPath := filepath.Join(themesPath, themeName, "style.min.css")
				if _, err := os.Stat(cssPath); err == nil {
					title := strings.Title(strings.ToLower(strings.ReplaceAll(strings.ReplaceAll(themeName, "-", " "), "_", " ")))
					themes = append(themes, Theme{Name: themeName, Title: title})
				}
			}
		}
	}

	cacheTimeStr := os.Getenv("THEME_CACHE_TIME")
	cacheTime, err := strconv.Atoi(cacheTimeStr)
	if err != nil || cacheTime <= 0 {
		cacheTime = 86400 // Default to 24 hours
	}

	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Cache-Control", fmt.Sprintf("public, max-age=%d", cacheTime))
	w.Header().Set("Expires", time.Now().Add(time.Duration(cacheTime)*time.Second).Format(http.TimeFormat))

	json.NewEncoder(w).Encode(themes)
}
