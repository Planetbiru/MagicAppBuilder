package com.planetbiru.graphqlapplication.service

import com.planetbiru.graphqlapplication.model.repository.core.AdminRepository
import org.springframework.security.core.userdetails.User
import org.springframework.security.core.userdetails.UserDetails
import org.springframework.security.core.userdetails.UserDetailsService
import org.springframework.security.core.userdetails.UsernameNotFoundException
import org.springframework.stereotype.Service

/**
 * Service to load user-specific data from the database for Spring Security.
 * This class implements the [UserDetailsService] interface to integrate with Spring Security's authentication mechanism.
 *
 * @property adminRepository The repository for accessing admin user data.
 */
@Service
class JpaUserDetailsService(private val adminRepository: AdminRepository) : UserDetailsService {

    /**
     * Locates the user based on the username.
     *
     * @param username the username identifying the user whose data is required.
     * @return a fully populated user record (never `null`).
     * @throws UsernameNotFoundException if the user could not be found.
     */
    override fun loadUserByUsername(username: String): UserDetails {
        val admin = adminRepository.findByUsername(username)
            .orElseThrow { UsernameNotFoundException("Username not found: $username") }

        return User.withUsername(admin.username!!)
            .password(admin.password!!)
            .authorities("USER").build()
    }
}