package com.planetbiru.graphqlapplication.model.repository.core

import com.planetbiru.graphqlapplication.model.entity.core.Message
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.data.jpa.repository.Query
import org.springframework.data.repository.query.Param
import java.util.Optional

interface MessageRepository : JpaRepository<Message, String> {

    @Query("SELECT m FROM Message m WHERE m.messageId = :messageId AND (m.senderId = :userId OR m.receiverId = :userId)")
    fun findByMessageIdAndUser(@Param("messageId") messageId: String, @Param("userId") userId: String): Optional<Message>

    fun findBySenderIdOrReceiverId(senderId: String, receiverId: String, pageable: Pageable): Page<Message>

    @Query("""
        SELECT m FROM Message m 
        LEFT JOIN m.sender sender 
        LEFT JOIN m.receiver receiver 
        WHERE (m.senderId = :userId OR m.receiverId = :userId) 
        AND (m.subject LIKE %:search% OR m.content LIKE %:search% OR sender.name LIKE %:search% OR receiver.name LIKE %:search%)
    """)
    fun findBySenderIdOrReceiverIdAndSearch(@Param("userId") userId: String, @Param("search") search: String, pageable: Pageable): Page<Message>

}