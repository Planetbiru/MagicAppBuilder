package com.planetbiru.graphqlapplication.model.repository.core

import com.planetbiru.graphqlapplication.model.entity.core.Admin
import org.springframework.data.domain.Page
import org.springframework.data.domain.Pageable
import org.springframework.data.jpa.repository.JpaRepository
import org.springframework.data.jpa.repository.JpaSpecificationExecutor
import java.util.Optional

interface AdminRepository : JpaRepository<Admin, String>, JpaSpecificationExecutor<Admin> {
    fun findByUsername(username: String): Optional<Admin>
    fun findByNameContainingOrUsernameContaining(name: String, username: String, pageable: Pageable): Page<Admin>
}