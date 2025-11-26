package input

import (
	"fmt"
	"strings"
)

// BuildQuery constructs WHERE and ORDER BY clauses for SQL queries based on GraphQL inputs.
// It returns the WHERE clause, ORDER BY clause, and a slice of parameters for safe querying.
func BuildQuery(filter *[]*FilterInput, orderBy *[]*SortInput, isPostgres bool) (string, string, []interface{}) {
	var whereClauses []string
	var orderClauses []string
	var params []interface{}

	// Build WHERE clause from filter
	// Build WHERE clause from filter
	if filter != nil {
		for _, f := range *filter {
			if f == nil || f.Value == nil {
				continue
			}
			// Default operator is EQUALS
			op := "="
			val := f.Value.Value()

			if f.Operator != nil {
				operator := fmt.Sprintf("%v", *f.Operator)
				switch strings.ToUpper(operator) {
				case "NOT_EQUALS":
					op = "!="
				case "CONTAINS":
					// Use ILIKE for PostgreSQL for case-insensitive search, LIKE for others (e.g., MySQL).
					if isPostgres {
						op = "ILIKE"
					} else {
						op = "LIKE"
					}
					val = fmt.Sprintf("%%%v%%", val)
				case "GREATER_THAN":
					op = ">"
				case "GREATER_THAN_OR_EQUALS":
					op = ">="
				case "LESS_THAN":
					op = "<"
				case "LESS_THAN_OR_EQUALS":
					op = "<="
				case "IN", "NOT_IN":
					// For IN/NOT_IN, the value is a comma-separated string. We need to create placeholders.
					valStr := fmt.Sprintf("%v", val)
					values := strings.Split(valStr, ",")
					if len(values) > 0 {
						placeholders := strings.Repeat("?,", len(values))
						placeholders = strings.TrimSuffix(placeholders, ",")
						operator := fmt.Sprintf("%v", *f.Operator)
						whereClauses = append(whereClauses, fmt.Sprintf("%s %s (%s)", f.Field, strings.ToUpper(operator), placeholders))
						for _, v := range values {
							params = append(params, strings.TrimSpace(v))
						}
					}
					continue // Skip the generic param append at the end
				}
			}
			whereClauses = append(whereClauses, fmt.Sprintf("%s %s ?", f.Field, op))
			params = append(params, val)
		}
	}

	// Build ORDER BY clause
	if orderBy != nil {
		for _, s := range *orderBy {
			if s == nil || s.Field == "" {
				continue
			}
			dir := "ASC" // Default direction
			if s.Direction != nil && strings.ToUpper(*s.Direction) == "DESC" {
				dir = "DESC"
			}
			orderClauses = append(orderClauses, fmt.Sprintf("%s %s", s.Field, dir))
		}
	}

	whereSQL := ""
	if len(whereClauses) > 0 {
		whereSQL = "WHERE " + strings.Join(whereClauses, " AND ")
	}

	orderSQL := ""
	if len(orderClauses) > 0 {
		orderSQL = "ORDER BY " + strings.Join(orderClauses, ", ")
	}

	return whereSQL, orderSQL, params
}

// PaginationArgs holds common pagination arguments from GraphQL queries.
type PaginationArgs struct {
	Limit  *int32
	Offset *int32
	Page   *int32
	Size   *int32
}

// GetPagination calculates limit, page, and offset from pagination arguments.
// It sets default values and allows overriding them.
func GetPagination(args PaginationArgs) (limit, page, offset int32) {
	limit = 10 // Default limit
	if args.Limit != nil {
		limit = *args.Limit
	}
	// 'size' is an alias for 'limit'
	if args.Size != nil {
		limit = *args.Size
	}

	page = 1 // Default page
	if args.Page != nil {
		page = *args.Page
	}

	offset = (page - 1) * limit // Calculate offset from page and limit
	if args.Offset != nil {
		offset = *args.Offset // Allow direct offset override
	}

	return limit, page, offset
}