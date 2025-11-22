package com.planetbiru.graphqlapplication.model.dto.core

data class FilterInput(
    val field: String,
    val value: Any?,
    val operator: String?
)