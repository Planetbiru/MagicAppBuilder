package config

import (
	"os"
	"strings"
)

// IsPostgres is a flag that is true if the configured database driver is PostgreSQL.
// It is initialized based on the DB_DRIVER environment variable.
var IsPostgres bool

func init() {
	dbDriver := os.Getenv("DB_DRIVER")
	dbDriver = strings.ToLower(dbDriver)
	IsPostgres = strings.Contains(dbDriver, "postgre")
}