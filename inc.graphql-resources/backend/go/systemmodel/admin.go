package systemmodel

import "database/sql"

// AdminListItem represents a single admin in the list view.
type AdminListItem struct {
	AdminID        string         `json:"admin_id"`
	Name           sql.NullString `json:"name"`
	Username       sql.NullString `json:"username"`
	Email          sql.NullString `json:"email"`
	Active         bool           `json:"active"`
	AdminLevelName sql.NullString `json:"admin_level_name"`
}
