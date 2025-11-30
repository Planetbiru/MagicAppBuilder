package com.planetbiru.graphqlapplication.util

data class FilterCriteria(
    val key: String,
    val operation: SearchOperation,
    val value: Any?
)