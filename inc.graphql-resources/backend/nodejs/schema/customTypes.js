const { GraphQLScalarType, Kind } = require('graphql');
const { toMySqlDateTime } = require('../utils/date');

const GraphQLDateTime = new GraphQLScalarType({
    name: 'DateTime',
    description: 'A custom DateTime scalar type that formats dates as YYYY-MM-DD HH:mm:ss',

    // Serializer: Converts the value from the server to the client
    serialize(value) {
        if (value instanceof Date) {
            return toMySqlDateTime(value); // If it's already a Date object
        }
        if (typeof value === 'string') {
            // Try to parse the string, if valid, reformat. If not, return it as is.
            const date = new Date(value);
            if (!Number.isNaN(date.getTime())) {
                return toMySqlDateTime(date);
            }
        }
        if (typeof value === 'number') {
            return toMySqlDateTime(new Date(value)); // If it's a timestamp
        }
        return value; // Return as is if it cannot be parsed
    },

    // Parser: Converts the value from the client to the server (query variable)
    parseValue(value) {
        return new Date(value); // Convert string/number input into a Date object
    },

    // Parser AST: Converts the value from the client to the server (inline in query)
    parseLiteral(ast) {
        if (ast.kind === Kind.INT || ast.kind === Kind.STRING) {
            return new Date(ast.value); // Convert literal input into a Date object
        }
        return null;
    },
});

module.exports = { GraphQLDateTime };