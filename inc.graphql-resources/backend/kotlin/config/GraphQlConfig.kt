package com.planetbiru.graphqlapplication.config

import com.planetbiru.graphqlapplication.config.ObjectScalar
import graphql.schema.GraphQLScalarType
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.graphql.execution.RuntimeWiringConfigurer

/**
 * Configuration class for GraphQL specific settings.
 * This class is responsible for customizing the GraphQL schema wiring, such as registering custom scalar types.
 */
@Configuration
class GraphQlConfig {
    /**
     * Creates a [RuntimeWiringConfigurer] bean to register custom GraphQL scalars.
     * This method defines a custom scalar named "Object" which can represent any JSON-like object structure.
     * The actual parsing and serialization logic is provided by the [ObjectScalar] bean.
     *
     * @param objectScalar The [ObjectScalar] bean that implements the coercing logic for the custom scalar.
     * @return A [RuntimeWiringConfigurer] that registers the custom "Object" scalar with the GraphQL schema.
     */
    @Bean
    fun runtimeWiringConfigurer(objectScalar: ObjectScalar): RuntimeWiringConfigurer {
        // Defines the custom GraphQL scalar type
        val objectScalar = GraphQLScalarType.newScalar()
            .name("Object")
            .description("A custom scalar that can represent any JSON-like object.")
            .coercing(objectScalar)
            .build()

        return RuntimeWiringConfigurer { builder ->
            // Registers the custom scalar with the runtime wiring
            builder.scalar(objectScalar)
        }
    }
}