package com.planetbiru.graphqlapplication.util

import jakarta.persistence.criteria.Predicate
import org.springframework.data.jpa.domain.Specification

class SpecificationBuilder<T> {
    private val params: MutableList<FilterCriteria> = mutableListOf()

    fun with(key: String, operation: SearchOperation, value: Any?): SpecificationBuilder<T> {
        params.add(FilterCriteria(key, operation, value))
        return this
    }

    fun build(): Specification<T>? {
        if (params.isEmpty()) {
            return null
        }

        val specs: List<Specification<T>> = params.map { criteria ->
            Specification<T> { root, query, builder ->
                val value = criteria.value
                val key = criteria.key
                when (criteria.operation) {
                    SearchOperation.EQUALS -> builder.equal(root.get<Any>(key), value)
                    SearchOperation.CONTAINS -> builder.like(root.get(key), "%$value%")
                    SearchOperation.GREATER_THAN -> builder.greaterThan(root.get(key), value as Comparable<Any>)
                    SearchOperation.LESS_THAN -> builder.lessThan(root.get(key), value as Comparable<Any>)
                    else -> builder.conjunction() // Return a predicate that is always true for unhandled cases
                }
            }
        }

        return specs.reduceOrNull { acc, spec -> acc.and(spec) }
    }
}