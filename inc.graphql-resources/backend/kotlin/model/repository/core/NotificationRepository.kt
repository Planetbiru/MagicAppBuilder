package com.planetbiru.graphqlapplication.model.repository.core

import com.planetbiru.graphqlapplication.model.entity.core.Notification
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.data.jpa.repository.Query
import org.springframework.data.repository.query.Param
import java.util.Optional

interface NotificationRepository : JpaRepository<Notification, String> {

    @Query("SELECT n FROM Notification n WHERE n.notificationId = :notificationId AND (n.adminId = :adminId OR n.adminGroup = :adminLevelId)")
    fun findByIdAndAdmin(@Param("notificationId") notificationId: String, @Param("adminId") adminId: String, @Param("adminLevelId") adminLevelId: String?): Optional<Notification>

    @Query("SELECT n FROM Notification n WHERE n.adminId = :adminId OR n.adminGroup = :adminLevelId")
    fun findByAdminIdOrAdminGroup(@Param("adminId") adminId: String, @Param("adminLevelId") adminLevelId: String?, pageable: Pageable): Page<Notification>

    @Query("""
        SELECT n FROM Notification n 
        WHERE (n.adminId = :adminId OR n.adminGroup = :adminLevelId) 
        AND (n.subject LIKE %:search% OR n.content LIKE %:search%)
    """)
    fun findByAdminIdOrAdminGroupAndSearch(
        @Param("adminId") adminId: String, @Param("adminLevelId") adminLevelId: String?, @Param("search") search: String, pageable: Pageable
    ): Page<Notification>

}