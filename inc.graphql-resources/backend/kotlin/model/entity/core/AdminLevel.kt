package com.planetbiru.graphqlapplication.model.entity.core

import jakarta.persistence.Column
import jakarta.persistence.Entity
import jakarta.persistence.Id
import jakarta.persistence.Table
import java.time.LocalDateTime

@Entity
@Table(name = "admin_level")
data class AdminLevel(
    @Id
    @Column(name = "admin_level_id")
    val adminLevelId: String,

    @Column(name = "name")
    val name: String? = null,

    @Column(name = "special_access")
    val specialAccess: Boolean? = false,

    @Column(name = "sort_order")
    val sortOrder: Int? = 0,

    @Column(name = "default_data")
    val defaultData: Boolean? = false,

    @Column(name = "active")
    val active: Boolean? = true
)