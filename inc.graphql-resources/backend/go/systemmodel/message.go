package systemmodel

import "database/sql"

// Message merepresentasikan data dari tabel 'message'.
type Message struct {
	MessageID         string
	MessageDirection  sql.NullString
	SenderID          sql.NullString
	ReceiverID        sql.NullString
	MessageFolderID   sql.NullString
	Icon              sql.NullString
	Subject           sql.NullString
	Content           sql.NullString
	Link              sql.NullString
	IsRead            sql.NullBool
	TimeCreate        sql.NullString
	IpCreate          sql.NullString
	TimeRead          sql.NullString
	IpRead            sql.NullString
	SenderName        sql.NullString // Dari JOIN
	ReceiverName      sql.NullString // Dari JOIN
	MessageFolderName sql.NullString // Dari JOIN
}

// MessagePageData adalah data yang dibutuhkan untuk template daftar pesan.
type MessagePageData struct {
	Messages     []Message
	TotalPages   int
	CurrentPage  int
	TotalRecords int
	SearchQuery  string
	AdminID      string
}
