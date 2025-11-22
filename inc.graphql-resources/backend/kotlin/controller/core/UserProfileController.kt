package com.planetbiru.graphqlapplication.controller.core

import com.planetbiru.graphqlapplication.config.Sha1PasswordEncoder
import com.planetbiru.graphqlapplication.util.I18nUtil
import jakarta.servlet.http.HttpServletRequest
import jakarta.servlet.http.HttpSession
import org.springframework.http.HttpStatus
import org.springframework.http.ResponseEntity
import com.planetbiru.graphqlapplication.model.repository.core.AdminRepository
import org.springframework.beans.factory.annotation.Value
import org.springframework.graphql.data.method.annotation.QueryMapping
import org.springframework.security.access.prepost.PreAuthorize
import org.springframework.security.core.userdetails.UsernameNotFoundException
import org.springframework.http.MediaType
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.PostMapping
import org.springframework.web.bind.annotation.RequestHeader
import org.springframework.web.bind.annotation.RequestBody
import org.springframework.web.bind.annotation.RequestParam
import org.springframework.web.bind.annotation.RestController
import java.time.LocalDate
import java.time.LocalDateTime
import java.time.format.DateTimeFormatter
import org.slf4j.Logger
import org.slf4j.LoggerFactory

data class UserProfile(val username: String)

data class UserProfileDto(
    val adminId: String,
    val name: String?,
    val username: String?,
    val gender: String?,
    val birthDay: LocalDate?,
    val phone: String?,
    val email: String?,
    val adminLevelName: String?,
    val languageId: String?,
    val lastResetPassword: String?,
    val blocked: Boolean,
    val active: Boolean
)

data class UpdateUserProfileDto(val name: String?, val email: String?, val phone: String?, val gender: String?, val birthDay: LocalDate?)


@RestController
class UserProfileController(
    private val adminRepository: AdminRepository,
    private val passwordEncoder: Sha1PasswordEncoder,
    @Value("\${app.security.require-login}") private val requireLogin: Boolean,
    private val i18n: I18nUtil
) {

    private val logger: Logger = LoggerFactory.getLogger(UserProfileController::class.java)
    
    @QueryMapping
    @PreAuthorize("isAuthenticated()")
    fun userProfile(session: HttpSession): UserProfile? {
        val username = session.getAttribute("username") as? String
        if (username != null) {
            val adminOptional = adminRepository.findByUsername(username)
            if (adminOptional.isPresent) {
                val admin = adminOptional.get()
                return UserProfile(username = admin.username!!)
            }
        }
        return null
    }

    @QueryMapping
    fun me(session: HttpSession): Map<String, Any?> {
        return mapOf("username" to session.getAttribute("username"))
    }

    @GetMapping("/user-profile")
    fun getUserProfile(
        session: HttpSession,
        @RequestParam(required = false) action: String?,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<String> {
        logger.info("Getting user profile")
        if (requireLogin && session.getAttribute("username") == null) {
            logger.info("Unauthorized: User not found in session")
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized", lang))
        }
        logger.info("User found in session")
        val username = session.getAttribute("username") as? String
        if (username == null) {
            logger.info("Unauthorized: User not found in session")
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized_session", lang))
        }
        logger.info("User found in database")
        val admin = adminRepository.findByUsername(username)
            .orElseThrow { UsernameNotFoundException(i18n.t("user_not_found_with_username", lang, username)) }
        logger.info("User found")

        val htmlContent = if (action == "update") {
            val birthDayValue = admin.birthDay?.format(DateTimeFormatter.ISO_LOCAL_DATE) ?: ""
            """
            <div class="table-container detail-view">
                <form id="profile-update-form" class="form-group" onsubmit="handleProfileUpdate(event); return false;">
                    <table class="table table-borderless">
                        <tr>
                            <td>${i18n.t("name", lang)}</td>
                            <td><input type="text" name="name" class="form-control" value="${admin.name ?: ""}"></td>
                        </tr>
                        <tr>
                            <td>${i18n.t("username", lang)}</td>
                            <td><input type="text" name="username" class="form-control" value="${admin.username ?: ""}" autocomplete="off" readonly></td>
                        </tr>
                        <tr>
                            <td>${i18n.t("gender", lang)}</td>
                            <td>
                                <select name="gender" class="form-control">
                                    <option value="M" ${if (admin.gender == "M") "selected" else ""}>${i18n.t("male", lang)}</option>
                                    <option value="F" ${if (admin.gender == "F") "selected" else ""}>${i18n.t("female", lang)}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>${i18n.t("birthday", lang)}</td>
                            <td><input type="date" name="birth_day" class="form-control" value="$birthDayValue" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td>${i18n.t("phone", lang)}</td>
                            <td><input type="text" name="phone" class="form-control" value="${admin.phone ?: ""}" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td>${i18n.t("email", lang)}</td>
                            <td><input type="email" name="email" class="form-control" value="${admin.email ?: ""}" autocomplete="off"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="submit" class="btn btn-success">${i18n.t("update", lang)}</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'">${i18n.t("cancel", lang)}</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            """.trimIndent()
        } else {
            val genderDisplay = when (admin.gender) {
                "M" -> i18n.t("male", lang)
                "F" -> i18n.t("female", lang)
                else -> ""
            }
            """
            <div class="table-container detail-view">
                <form action="" class="form-group">
                    <table class="table table-borderless">
                        <tr><td>${i18n.t("admin_id", lang)}</td><td>${admin.adminId ?: ""}</td></tr>
                        <tr><td>${i18n.t("name", lang)}</td><td>${admin.name ?: ""}</td></tr>
                        <tr><td>${i18n.t("username", lang)}</td><td>${admin.username ?: ""}</td></tr>
                        <tr><td>${i18n.t("gender", lang)}</td><td>${genderDisplay}</td></tr>
                        <tr><td>${i18n.t("birthday", lang)}</td><td>${admin.birthDay?.toString() ?: ""}</td></tr>
                        <tr><td>${i18n.t("phone", lang)}</td><td>${admin.phone ?: ""}</td></tr>
                        <tr><td>${i18n.t("email", lang)}</td><td>${admin.email ?: ""}</td></tr>
                        <tr><td>${i18n.t("admin_level", lang)}</td><td>${admin.adminLevel?.name ?: ""}</td></tr>
                        <tr><td>${i18n.t("language_id", lang)}</td><td>${admin.languageId ?: ""}</td></tr>
                        <tr><td>${i18n.t("last_reset_password", lang)}</td><td>${admin.lastResetPassword?.toString() ?: ""}</td></tr>
                        <tr><td>${i18n.t("blocked", lang)}</td><td>${if (admin.blocked) i18n.t("yes", lang) else i18n.t("no", lang)}</td></tr>
                        <tr><td>${i18n.t("active", lang)}</td><td>${if (admin.active) i18n.t("yes", lang) else i18n.t("no", lang)}</td></tr>
                        <tr>
                            <td></td>
                            <td>
                                <button type="button" class="btn btn-primary" onclick="window.location='#user-profile?action=update'">${i18n.t("edit", lang)}</button>
                                <button type="button" class="btn btn-warning" onclick="window.location='#update-password'">${i18n.t("update_password", lang)}</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            """.trimIndent()
        }

        return ResponseEntity.ok().contentType(MediaType.TEXT_HTML).body(htmlContent)
    }

    @PostMapping("/user-profile")
    fun updateUserProfile(
        session: HttpSession,
        request: HttpServletRequest,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<*> {
        if (requireLogin && session.getAttribute("username") == null) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("message" to i18n.t("unauthorized", lang)))
        }
        val username = session.getAttribute("username") as? String
        if (username == null) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("message" to i18n.t("unauthorized_session", lang)))
        }
        val admin = adminRepository.findByUsername(username)
            .orElseThrow { UsernameNotFoundException(i18n.t("user_not_found_with_username", lang, username)) }

        // Manually get parameters from the request
        val name = request.getParameter("name")
        val email = request.getParameter("email")
        val phone = request.getParameter("phone")
        val gender = request.getParameter("gender")
        val birthDayStr = request.getParameter("birth_day")
        val birthDay = if (!birthDayStr.isNullOrBlank()) LocalDate.parse(birthDayStr) else null

        admin.name = name
        admin.email = email
        admin.phone = phone
        admin.gender = gender
        admin.birthDay = birthDay


        adminRepository.save(admin)

        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("profile_updated_successfully", lang)))
    }

    @GetMapping("/update-password")
    fun getUpdatePasswordForm(
        session: HttpSession,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<String> {
        if (requireLogin && session.getAttribute("username") == null) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized", lang))
        }

        val htmlContent = """
        <div class="table-container detail-view">
            <form id="password-update-form" class="form-group" onsubmit="handlePasswordUpdate(event); return false">
                <table class="table table-borderless">
                    <tr>
                        <td>${i18n.t("current_password", lang)}</td>
                        <td><input type="password" name="current_password" class="form-control" autocomplete="off" required></td>
                    </tr>
                    <tr>
                        <td>${i18n.t("new_password", lang)}</td>
                        <td><input type="password" name="new_password" class="form-control" autocomplete="off" required></td>
                    </tr>
                    <tr>
                        <td>${i18n.t("confirm_password", lang)}</td>
                        <td><input type="password" name="confirm_password" class="form-control" autocomplete="off" required></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" class="btn btn-success">${i18n.t("update", lang)}</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'">${i18n.t("cancel", lang)}</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        """.trimIndent()

        return ResponseEntity.ok().contentType(MediaType.TEXT_HTML).body(htmlContent)
    }

    @PostMapping("/update-password")
    fun updatePassword(
        session: HttpSession,
        request: HttpServletRequest,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<*> {
        val username = session.getAttribute("username") as? String
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("success" to false, "message" to i18n.t("user_not_logged_in", lang)))

        val currentPasswordRaw = request.getParameter("current_password")
        val newPasswordRaw = request.getParameter("new_password")
        val confirmPasswordRaw = request.getParameter("confirm_password")

        if (newPasswordRaw.isBlank()) {
            return ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("new_password_required", lang)))
        }

        if (newPasswordRaw != confirmPasswordRaw) {
            return ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("password_mismatch", lang)))
        }

        val admin = adminRepository.findByUsername(username).orElse(null)
            ?: return ResponseEntity.status(HttpStatus.NOT_FOUND).body(mapOf("success" to false, "message" to i18n.t("user_not_found", lang)))

        if (currentPasswordRaw == null || !passwordEncoder.matches(currentPasswordRaw, admin.password!!)) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("success" to false, "message" to i18n.t("incorrect_current_password", lang)))
        }

        admin.password = passwordEncoder.encode(newPasswordRaw)
        admin.lastResetPassword = LocalDateTime.now()
        adminRepository.save(admin)

        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("password_updated_successfully", lang)))
    }
}