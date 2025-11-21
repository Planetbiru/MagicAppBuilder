package com.planetbiru.graphqlapplication.service

import com.planetbiru.graphqlapplication.model.repository.core.AdminRepository
import org.springframework.security.core.userdetails.User
import org.springframework.security.core.userdetails.UserDetails
import org.springframework.security.core.userdetails.UserDetailsService
import org.springframework.security.core.userdetails.UsernameNotFoundException
import org.springframework.stereotype.Service

@Service
class JpaUserDetailsService(private val adminRepository: AdminRepository) : UserDetailsService {

    override fun loadUserByUsername(username: String): UserDetails {
        val admin = adminRepository.findByUsername(username)
            .orElseThrow { UsernameNotFoundException("Username not found: $username") }

        return User.withUsername(admin.username!!)
            .password(admin.password!!)
            .authorities("USER").build()
    }
}