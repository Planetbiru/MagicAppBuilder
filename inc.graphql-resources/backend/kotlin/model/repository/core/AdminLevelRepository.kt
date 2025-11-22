package com.planetbiru.graphqlapplication.model.repository.core

import com.planetbiru.graphqlapplication.model.entity.core.AdminLevel
import org.springframework.data.jpa.repository.JpaRepository
import java.util.Optional

interface AdminLevelRepository : JpaRepository<AdminLevel, String> {
    fun findByAdminLevelId(adminLevelId: String): Optional<AdminLevel>
    fun findByActive(active: Boolean): List<AdminLevel>
}