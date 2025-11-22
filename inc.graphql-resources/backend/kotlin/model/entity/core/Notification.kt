package com.planetbiru.graphqlapplication.model.entity.core

import jakarta.persistence.*
import java.time.LocalDateTime

@Entity
@Table(name = "notification")
data class Notification(
    @Id
    @Column(name = "notification_id", nullable = false, length = 40)
    var notificationId: String,

    @Column(name = "notification_type", length = 40)
    var notificationType: String? = null,

    @Column(name = "admin_group", length = 40)
    var adminGroup: String? = null,

    @Column(name = "admin_id", length = 40, insertable = false, updatable = false)
    var adminId: String? = null,

    @Column(name = "icon", length = 40)
    var icon: String? = null,

    @Column(name = "subject", length = 255)
    var subject: String? = null,

    @Lob
    @Column(name = "content")
    var content: String? = null,

    @Lob
    @Column(name = "link")
    var link: String? = null,

    @Column(name = "is_read")
    var isRead: Boolean = false,

    @Column(name = "time_create")
    var timeCreate: LocalDateTime? = null,

    @Column(name = "ip_create", length = 50)
    var ipCreate: String? = null,

    @Column(name = "time_read")
    var timeRead: LocalDateTime? = null,

    @Column(name = "ip_read", length = 50)
    var ipRead: String? = null,

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "admin_id", referencedColumnName = "admin_id")
    val admin: Admin? = null
)