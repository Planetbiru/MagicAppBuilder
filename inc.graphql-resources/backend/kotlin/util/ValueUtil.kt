package com.planetbiru.graphqlapplication.util

import com.fasterxml.jackson.databind.DeserializationFeature
import com.fasterxml.jackson.databind.ObjectMapper
import com.fasterxml.jackson.datatype.jsr310.JavaTimeModule
import com.fasterxml.jackson.module.kotlin.registerKotlinModule

object ValueUtil {

    private val MAPPER: ObjectMapper = ObjectMapper().apply {
        registerKotlinModule()
        registerModule(JavaTimeModule())
        configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false)
        configure(DeserializationFeature.ACCEPT_FLOAT_AS_INT, true)
    }

    /**
     * Converts a Map with snake_case keys directly to a target DTO.
     */
    fun <T> convertSnakeCaseToDto(snakeCaseMap: Map<String, Any>?, targetClass: Class<T>): T {
        val camelCaseMap = convertSnakeToCamelCase(snakeCaseMap)
        return MAPPER.convertValue(camelCaseMap, targetClass)
    }

    /**
     * Converts the keys of a Map from snake_case to camelCase.
     */
    private fun convertSnakeToCamelCase(snakeCaseMap: Map<String, Any>?): Map<String, Any> {
        if (snakeCaseMap == null) {
            return emptyMap()
        }
        return snakeCaseMap.mapKeys { toCamelCase(it.key) }
    }

    /**
     * Converts a single snake_case string to camelCase.
     */
    fun toCamelCase(snakeCase: String): String {
        return snakeCase.split('_').reduceIndexed { index, acc, part ->
            if (index == 0) part else acc + part.replaceFirstChar { it.uppercase() }
        }
    }
}