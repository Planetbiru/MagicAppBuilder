package com.planetbiru.graphqlapplication.model.entity.core

import jakarta.persistence.*
import java.time.LocalDateTime

@Entity
@Table(name = "message_folder")
data class MessageFolder(
    @Id
    @Column(name = "message_folder_id", nullable = false, length = 40)
    var messageFolderId: String,

    @Column(name = "name", length = 100)
    var name: String? = null,

    @Column(name = "admin_id", length = 40)
    var adminId: String? = null,

    @Column(name = "sort_order")
    var sortOrder: Int? = null,

    @Column(name = "time_create")
    var timeCreate: LocalDateTime? = null,

    @Column(name = "time_edit")
    var timeEdit: LocalDateTime? = null,

    @Column(name = "admin_create", length = 40)
    var adminCreate: String? = null,

    @Column(name = "admin_edit", length = 40)
    var adminEdit: String? = null,

    @Column(name = "ip_create", length = 50)
    var ipCreate: String? = null,

    @Column(name = "ip_edit", length = 50)
    var ipEdit: String? = null,

    @Column(name = "active")
    var active: Boolean? = true,

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "admin_id", referencedColumnName = "admin_id", insertable = false, updatable = false)
    val admin: Admin? = null
)