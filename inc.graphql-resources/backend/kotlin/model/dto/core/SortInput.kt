package com.planetbiru.graphqlapplication.model.dto.core

data class SortInput(
    val field: String,
    val direction: String? = "ASC"
)