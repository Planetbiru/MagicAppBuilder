package com.planetbiru.graphqlapplication.model.entity.core

import jakarta.persistence.*
import java.time.LocalDate
import java.time.LocalDateTime

@Entity
@Table(name = "admin")
data class Admin(
    @Id
    @Column(name = "admin_id")
    val adminId: String,

    @Column(name = "username")
    var username: String,

    @Column(name = "password")
    var password: String? = null,

    @Column(name = "name")
    var name: String? = null,

    @Column(name = "email")
    var email: String? = null,

    @Column(name = "phone")
    var phone: String? = null,

    @Column(name = "admin_level_id")
    var adminLevelId: String? = null,

    @Column(name = "gender")
    var gender: String? = null, // Pastikan ini adalah 'var'

    @Column(name = "birth_day")
    var birthDay: LocalDate? = null, // Pastikan ini adalah 'var'

    @Column(name = "language_id")
    val languageId: String? = null,

    @Column(name = "last_reset_password")
    var lastResetPassword: LocalDateTime? = null,

    @Column(name = "blocked")
    val blocked: Boolean = false,

    @Column(name = "active")
    var active: Boolean = true,

    @Column(name = "time_create")
    var timeCreate: LocalDateTime? = null,

    @Column(name = "time_edit")
    var timeEdit: LocalDateTime? = null,

    @Column(name = "admin_create")
    var adminCreate: String? = null,

    @Column(name = "admin_edit")
    var adminEdit: String? = null,

    @Column(name = "ip_create")
    var ipCreate: String? = null,

    @Column(name = "ip_edit")
    var ipEdit: String? = null,

    // Relation to AdminLevel
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "admin_level_id", referencedColumnName = "admin_level_id", insertable = false, updatable = false)
    val adminLevel: AdminLevel? = null
)