package input

// FilterInput corresponds to the GraphQL FilterInput type.
type FilterInput struct {
	Field    string
	Value    *Any // Must be a pointer for custom scalar unmarshalling
	Operator *string
}

// SortInput corresponds to the GraphQL SortInput type.
type SortInput struct {
	Field     string
	Direction *string
}