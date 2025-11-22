package com.planetbiru.graphqlapplication.util

import jakarta.persistence.criteria.CriteriaBuilder
import jakarta.persistence.criteria.CriteriaQuery
import jakarta.persistence.criteria.Predicate
import jakarta.persistence.criteria.Root
import org.springframework.data.jpa.domain.Specification

class GenericSpecification<T>(private val criteria: FilterCriteria) : Specification<T> {

    override fun toPredicate(root: Root<T>, query: CriteriaQuery<*>, builder: CriteriaBuilder): Predicate? {
        val key = criteria.key
        val value = criteria.value
        val fieldType = root.get<Any>(key).javaType

        val typedValue = value?.let {
            val stringValue = it.toString()
            when (fieldType) {
                Integer::class.java, Int::class.java -> stringValue.toIntOrNull()
                Long::class.java, Long::class.java -> stringValue.toLongOrNull()
                Double::class.java, Double::class.java -> stringValue.toDoubleOrNull()
                Float::class.java, Float::class.java -> stringValue.toFloatOrNull()
                Boolean::class.java, Boolean::class.java -> stringValue.toBoolean()
                else -> stringValue
            }
        }

        @Suppress("UNCHECKED_CAST")
        return when (criteria.operation) {
            SearchOperation.EQUALS -> builder.equal(root.get<Any>(key), typedValue)
            SearchOperation.NOT_EQUALS -> builder.notEqual(root.get<Any>(key), typedValue)
            SearchOperation.GREATER_THAN -> {
                val comparableValue = typedValue as? Comparable<Any>
                comparableValue?.let { builder.greaterThan(root.get(key), it) }
            }
            SearchOperation.GREATER_THAN_OR_EQUALS -> {
                val comparableValue = typedValue as? Comparable<Any>
                comparableValue?.let { builder.greaterThanOrEqualTo(root.get(key), it) }
            }
            SearchOperation.LESS_THAN -> {
                val comparableValue = typedValue as? Comparable<Any>
                comparableValue?.let { builder.lessThan(root.get(key), it) }
            }
            SearchOperation.LESS_THAN_OR_EQUALS -> {
                val comparableValue = typedValue as? Comparable<Any>
                comparableValue?.let { builder.lessThanOrEqualTo(root.get(key), it) }
            }
            SearchOperation.CONTAINS -> if (fieldType == String::class.java) builder.like(root.get(key), "%$typedValue%") else builder.equal(root.get<Any>(key), typedValue)
            SearchOperation.IN -> root.get<Any>(key).`in`(typedValue as? Collection<*>)
            SearchOperation.NOT_IN -> builder.not(root.get<Any>(key).`in`(typedValue as? Collection<*>))
        }
    }
}