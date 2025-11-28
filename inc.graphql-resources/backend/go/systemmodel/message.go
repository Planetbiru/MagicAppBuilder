package systemmodel

import "database/sql"

// Message represents data from the 'message' table.
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
	SenderName        sql.NullString // From JOIN
	ReceiverName      sql.NullString // From JOIN
	MessageFolderName sql.NullString // From JOIN
}

// MessagePageData is the data needed for the message list template.
type MessagePageData struct {
	Messages     []Message
	TotalPages   int
	CurrentPage  int
	TotalRecords int
	SearchQuery  string
	AdminID      string
}
