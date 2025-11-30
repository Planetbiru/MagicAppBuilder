package systemmodel

import "database/sql"

// Notification represents data from the 'notification' table.
type Notification struct {
	NotificationID   string
	NotificationType sql.NullString
	AdminGroup       sql.NullString
	AdminID          sql.NullString
	Icon             sql.NullString
	Subject          sql.NullString
	Content          sql.NullString
	Link             sql.NullString
	IsRead           sql.NullBool
	TimeCreate       sql.NullString
	IpCreate         sql.NullString
	TimeRead         sql.NullString
	IpRead           sql.NullString
}

// NotificationPageData is the data needed for the notification list template.
type NotificationPageData struct {
	Notifications []Notification // For the list page
	TotalPages    int            // For the list page
	CurrentPage   int            // For the list page
	TotalRecords  int            // For the list page
	SearchQuery   string         // For the list page
	AdminID       string         // Needed in some templates
	Found         bool           // For the detail page
	Notification  Notification   // For the detail page
}
