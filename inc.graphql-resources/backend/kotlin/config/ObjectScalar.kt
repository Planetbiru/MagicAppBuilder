package com.planetbiru.graphqlapplication.config

import graphql.language.Value
import graphql.language.ObjectValue
import graphql.schema.Coercing
import graphql.schema.CoercingParseLiteralException
import graphql.GraphQLContext
import org.springframework.stereotype.Component
import java.util.Locale

/**
 * Implements the coercing logic for a custom GraphQL "Object" scalar.
 * This class defines how to serialize, parse values, and parse literals for a generic object type,
 * allowing arbitrary JSON-like objects to be used in the GraphQL schema.
 */
@Component
class ObjectScalar : Coercing<Any?, Any?> {
    /**
     * Serializes a data fetcher result into a type that can be sent to the client.
     * For this scalar, it performs a pass-through, returning the object as-is.
     *
     * @param dataFetcherResult The object to serialize.
     * @param graphQLContext The context for the GraphQL execution.
     * @param locale The locale for the serialization.
     * @return The serialized object.
     */
    override fun serialize(dataFetcherResult: Any, graphQLContext: GraphQLContext, locale: Locale): Any? {
        return dataFetcherResult
    }

    /**
     * Parses a value from a GraphQL variable.
     * For this scalar, it performs a pass-through, returning the input value as-is.
     *
     * @param input The value from the GraphQL variables.
     * @param graphQLContext The context for the GraphQL execution.
     * @param locale The locale for parsing.
     * @return The parsed value.
     */
    override fun parseValue(input: Any, graphQLContext: GraphQLContext, locale: Locale): Any? {
        return input
    }

    /**
     * Parses a value from a literal in the GraphQL query string.
     * It expects an `ObjectValue` and converts it into a map of key-value pairs.
     *
     * @param input The GraphQL literal value from the query.
     * @param variables The variables provided with the query.
     * @param graphQLContext The context for the GraphQL execution.
     * @param locale The locale for parsing.
     * @return A map representing the object, or throws [CoercingParseLiteralException] if the input is not an `ObjectValue`.
     */
    fun parseLiteral(
        input: Value<*>,
        variables: Map<String, Any>,
        graphQLContext: GraphQLContext,
        locale: Locale
    ): Any? {
        return if (input is ObjectValue) {
            input.objectFields.associate { it.name to it.value }
        } else {
            throw CoercingParseLiteralException("Expected an ObjectValue literal but was: $input")
        }
    }
}