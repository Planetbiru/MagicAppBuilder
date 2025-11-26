package input

import (
	"fmt"
)

// Any is a custom scalar type that can represent any value.
// It's used for the 'value' field in the FilterInput.
type Any struct{ v interface{} }

// ImplementsGraphQLType returns the name of the GraphQL type.
func (Any) ImplementsGraphQLType(name string) bool { return name == "Any" }

// UnmarshalGraphQL is called when a value is received from a client.
func (a *Any) UnmarshalGraphQL(input interface{}) error {
	a.v = input
	return nil
}

// MarshalJSON is called when sending a value to a client.
func (a Any) MarshalJSON() ([]byte, error) {
	return []byte(fmt.Sprintf("%q", a.v)), nil
}

// Value returns the underlying value of the Any scalar.
func (a *Any) Value() interface{} {
	return a.v
}