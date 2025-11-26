package systemmodel

import "database/sql"

// Notification merepresentasikan data dari tabel 'notification'.
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

// NotificationPageData adalah data yang dibutuhkan untuk template daftar notifikasi.
type NotificationPageData struct {
	Notifications []Notification
	TotalPages    int
	CurrentPage   int
	TotalRecords  int
	SearchQuery   string
}
