package com.planetbiru.graphqlapplication.controller.core

import com.planetbiru.graphqlapplication.config.Sha1PasswordEncoder
import com.planetbiru.graphqlapplication.controller.dto.LoginResponse
import com.planetbiru.graphqlapplication.util.I18nUtil
import com.planetbiru.graphqlapplication.model.repository.core.AdminRepository
import jakarta.servlet.http.HttpServletRequest
import jakarta.servlet.http.HttpSession
import org.springframework.beans.factory.annotation.Value
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken
import org.springframework.security.core.authority.SimpleGrantedAuthority
import org.springframework.security.core.context.SecurityContextHolder
import org.springframework.web.bind.annotation.RequestHeader
import org.springframework.web.bind.annotation.*


@RestController
class AuthController(
    private val adminRepository: AdminRepository,
    private val passwordEncoder: Sha1PasswordEncoder,
    private val i18n: I18nUtil,
    @Value("\${app.security.require-login}")
    private val requireLogin: Boolean
) {

    @PostMapping("/login")
    fun login(
        @RequestParam username: String,
        @RequestParam password: String,
        session: HttpSession,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<LoginResponse> {

        if (!requireLogin) {
            return ResponseEntity.ok(LoginResponse(true, i18n.t("success", lang)))
        }

        val singleHashedPassword = passwordEncoder.sha1(password)
        val adminOptional = adminRepository.findByUsername(username)

        if (adminOptional.isPresent) {
            val admin = adminOptional.get()
            if (admin.password == passwordEncoder.encode(password)) {
                session.setAttribute("username", admin.username!!)
                session.setAttribute("password", singleHashedPassword)
                session.setAttribute("adminId", admin.adminId)

                val authorities = listOf(SimpleGrantedAuthority("ROLE_ADMIN"))
                val auth = UsernamePasswordAuthenticationToken(admin.username, null, authorities)
                SecurityContextHolder.getContext().authentication = auth

                return ResponseEntity.ok(LoginResponse(true, i18n.t("login_successful", lang)))
            }
        }

        return ResponseEntity.status(HttpStatus.UNAUTHORIZED)
            .body(LoginResponse(false, i18n.t("invalid_credentials", lang)))
    }

    @RequestMapping(value = ["/logout"], method = [RequestMethod.GET, RequestMethod.POST])
    fun logout(
        request: HttpServletRequest,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<LoginResponse> {
        if (!requireLogin) {
            return ResponseEntity.ok(LoginResponse(true, i18n.t("success", lang)))
        }
        request.session.invalidate()
        SecurityContextHolder.clearContext()
        return ResponseEntity.ok(LoginResponse(true, i18n.t("logout_successful", lang)))
    }
}