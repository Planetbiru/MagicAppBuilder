package com.planetbiru.graphqlapplication.util

import com.planetbiru.graphqlapplication.model.dto.core.FilterInput
import com.planetbiru.graphqlapplication.model.dto.core.SortInput
import org.springframework.data.domain.Page
import org.springframework.data.domain.PageRequest
import org.springframework.data.domain.Pageable
import org.springframework.data.domain.Sort
import org.springframework.data.jpa.domain.Specification

object QueryUtil {

    fun createPageable(limit: Int?, offset: Int?, page: Int?, size: Int?, orderBy: List<SortInput>?): Pageable {
        val pageSize = limit ?: size ?: 20
        val pageNum = when {
            offset != null && pageSize > 0 -> offset / pageSize
            page != null -> if (page > 0) page - 1 else 0
            else -> 0
        }

        val sort = orderBy?.map {
            Sort.Order(Sort.Direction.fromString(it.direction ?: "ASC"), ValueUtil.toCamelCase(it.field))
        }?.let { Sort.by(it) } ?: Sort.unsorted()

        return PageRequest.of(pageNum, pageSize, sort)
    }

    fun <T> createPageResultMap(resultPage: Page<T>): Map<String, Any> {
        return mapOf(
            "items" to resultPage.content,
            "total" to resultPage.totalElements,
            "limit" to resultPage.size,
            "page" to resultPage.number + 1,
            "totalPages" to resultPage.totalPages,
            "hasNext" to resultPage.hasNext(),
            "hasPrevious" to resultPage.hasPrevious()
        )
    }

    fun <T> createSpecification(filter: List<FilterInput>?): Specification<T> {
        if (filter.isNullOrEmpty()) {
            return Specification.where(null)
        }
        val builder = SpecificationBuilder<T>()
        filter.forEach { f ->
            f.value?.let { builder.with(ValueUtil.toCamelCase(f.field), SearchOperation.valueOf(f.operator?.uppercase() ?: "EQUALS"), it) }
        }
        return builder.build() ?: Specification.where(null)
    }
}