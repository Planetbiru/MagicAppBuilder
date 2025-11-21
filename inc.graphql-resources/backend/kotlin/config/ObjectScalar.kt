package com.planetbiru.graphqlapplication.config

import graphql.language.Value
import graphql.language.ObjectValue
import graphql.schema.Coercing
import graphql.schema.CoercingParseLiteralException
import graphql.GraphQLContext
import org.springframework.stereotype.Component
import java.util.Locale

@Component
class ObjectScalar : Coercing<Any?, Any?> {
    override fun serialize(dataFetcherResult: Any, graphQLContext: GraphQLContext, locale: Locale): Any? {
        return dataFetcherResult
    }

    override fun parseValue(input: Any, graphQLContext: GraphQLContext, locale: Locale): Any? {
        return input
    }

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