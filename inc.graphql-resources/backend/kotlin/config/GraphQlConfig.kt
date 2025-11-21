package com.planetbiru.graphqlapplication.config

import com.planetbiru.graphqlapplication.config.ObjectScalar
import graphql.schema.GraphQLScalarType
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.graphql.execution.RuntimeWiringConfigurer

@Configuration
class GraphQlConfig {
    @Bean
    fun runtimeWiringConfigurer(objectScalar: ObjectScalar): RuntimeWiringConfigurer {
        val objectScalar = GraphQLScalarType.newScalar()
            .name("Object")
            .description("A custom scalar that can represent any JSON-like object.")
            .coercing(objectScalar)
            .build()

        return RuntimeWiringConfigurer { builder ->
            builder.scalar(objectScalar)
        }
    }
}