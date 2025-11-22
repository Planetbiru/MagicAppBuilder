package com.planetbiru.graphqlapplication.model.entity.core

import jakarta.persistence.*
import java.time.LocalDateTime

@Entity
@Table(name = "message")
data class Message(
    @Id
    @Column(name = "message_id", nullable = false, length = 40)
    var messageId: String,

    @Column(name = "message_direction", length = 40)
    var messageDirection: String? = null,

    @Column(name = "sender_id", length = 40, insertable = false, updatable = false)
    var senderId: String? = null,

    @Column(name = "receiver_id", length = 40, insertable = false, updatable = false)
    var receiverId: String? = null,

    @Column(name = "message_folder_id", length = 40, insertable = false, updatable = false)
    var messageFolderId: String? = null,

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
    @JoinColumn(name = "sender_id", referencedColumnName = "admin_id")
    val sender: Admin? = null,

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "receiver_id", referencedColumnName = "admin_id")
    val receiver: Admin? = null,

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "message_folder_id", referencedColumnName = "message_folder_id")
    val messageFolder: MessageFolder? = null
)