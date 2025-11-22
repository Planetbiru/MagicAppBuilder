package com.planetbiru.graphqlapplication.controller.core

import com.planetbiru.graphqlapplication.config.Sha1PasswordEncoder
import com.planetbiru.graphqlapplication.util.I18nUtil
import com.planetbiru.graphqlapplication.model.entity.core.Admin
import com.planetbiru.graphqlapplication.model.repository.core.AdminLevelRepository
import com.planetbiru.graphqlapplication.model.repository.core.AdminRepository
import jakarta.servlet.http.HttpServletRequest
import jakarta.servlet.http.HttpSession
import org.springframework.data.domain.PageRequest
import org.springframework.data.domain.Sort
import org.springframework.data.jpa.domain.Specification
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.stereotype.Controller
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.RequestHeader
import org.springframework.web.bind.annotation.PostMapping
import org.springframework.web.bind.annotation.RequestParam
import com.planetbiru.graphqlapplication.model.entity.core.AdminLevel
import java.time.LocalDateTime
import java.util.UUID

@Controller
class AdminController(
    private val adminRepository: AdminRepository,
    private val adminLevelRepository: AdminLevelRepository,
    private val passwordEncoder: Sha1PasswordEncoder,
    private val i18n: I18nUtil
) {

    private fun getCurrentAdminId(session: HttpSession): String? {
        return session.getAttribute("adminId") as? String
    }

    @GetMapping("/admin")
    fun handleAdminGet(
        @RequestParam(required = false) action: String?,
        @RequestParam(required = false) adminId: String?,
        @RequestParam(required = false) search: String?,
        @RequestParam(defaultValue = "1") page: Int,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?,
        session: HttpSession,
        request: HttpServletRequest
    ): ResponseEntity<String> {
        val currentAdminId = getCurrentAdminId(session)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized", lang))

        val htmlContent = when (action) {
            "create" -> buildCreateEditForm(null, lang)
            "edit" -> adminRepository.findById(adminId ?: "").map { buildCreateEditForm(it, lang) }.orElse(i18n.t("admin_not_found", lang))
            "change-password" -> buildChangePasswordForm(adminId, lang)
            "detail" -> adminRepository.findById(adminId ?: "").map { buildDetailView(it, currentAdminId, lang) }.orElse(i18n.t("admin_not_found", lang))
            else -> buildListView(search, page, currentAdminId, lang)
        }

        return ResponseEntity.ok().contentType(MediaType.TEXT_HTML).body(htmlContent)
    }

    @PostMapping("/admin")
    fun handleAdminPost(
        @RequestParam action: String,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?,
        session: HttpSession,
        request: HttpServletRequest
    ): ResponseEntity<*> {
        val currentAdminId = getCurrentAdminId(session)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("success" to false, "message" to i18n.t("unauthorized", lang)))

        // Manually build the parameters map from the request to reliably handle multipart/form-data
        val params = request.parameterMap.mapValues { entry ->
            entry.value.firstOrNull() ?: ""
        }
        return try {
            when (params["action"]) {
                "create" -> createAdmin(params, currentAdminId, request.remoteAddr, lang)
                "update" -> updateAdmin(params, currentAdminId, request.remoteAddr, lang)
                "toggle_active" -> toggleAdminActive(params, currentAdminId, lang)
                "change_password" -> changeAdminPassword(params, lang)
                "delete" -> deleteAdmin(params, currentAdminId, lang)
                else -> ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("invalid_action", lang)))
            }
        } catch (e: Exception) {
            ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(mapOf("success" to false, "message" to e.message))
        }
    }

    private fun createAdmin(params: Map<String, String>, currentAdminId: String, ip: String, lang: String?): ResponseEntity<*> {
        val password = params["password"] ?: throw IllegalArgumentException(i18n.t("password_is_required", lang))
        if (password.isBlank()) throw IllegalArgumentException(i18n.t("password_is_required", lang))

        val username = params["username"] ?: throw IllegalArgumentException("Username is required.")
        val newAdmin = Admin(
            adminId = UUID.randomUUID().toString(),
            name = params["name"],
            username = username,
            email = params["email"],
            password = passwordEncoder.encode(password),
            adminLevelId = params["admin_level_id"],
            active = params["active"] == "on",
            timeCreate = LocalDateTime.now(),
            adminCreate = currentAdminId,
            ipCreate = ip
        )
        adminRepository.save(newAdmin)
        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("admin_created_successfully", lang)))
    }

    private fun updateAdmin(params: Map<String, String>, currentAdminId: String, ip: String, lang: String?): ResponseEntity<*> {
        val adminId = params["adminId"] ?: throw IllegalArgumentException(i18n.t("admin_id_required", lang))
        val adminToUpdate = adminRepository.findById(adminId).orElseThrow { RuntimeException(i18n.t("admin_not_found", lang)) }

        var active = params["active"] == "on"
        var adminLevelId = params["admin_level_id"]

        // Prevent user from deactivating or changing their own level
        if (adminId == currentAdminId) {
            active = true
            adminLevelId = adminToUpdate.adminLevelId
        }

        val username = params["username"] ?: adminToUpdate.username
        val updatedAdmin = adminToUpdate.copy(
            name = params["name"],
            username = username,
            email = params["email"],
            adminLevelId = adminLevelId,
            active = active,
            timeEdit = LocalDateTime.now(),
            adminEdit = currentAdminId, // This will now work
            ipEdit = ip // This will now work
        )
        adminRepository.save(updatedAdmin)
        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("admin_updated_successfully", lang)))
    }

    private fun toggleAdminActive(params: Map<String, String>, currentAdminId: String, lang: String?): ResponseEntity<*> {
        val adminId = params["adminId"] ?: throw IllegalArgumentException(i18n.t("admin_id_required", lang))
        if (adminId == currentAdminId) throw IllegalStateException(i18n.t("cannot_deactivate_self", lang))

        val admin = adminRepository.findById(adminId).orElseThrow { RuntimeException(i18n.t("admin_not_found", lang)) }
        admin.active = !admin.active
        adminRepository.save(admin)
        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("admin_status_updated", lang)))
    }

    private fun changeAdminPassword(params: Map<String, String>, lang: String?): ResponseEntity<*> {
        val adminId = params["adminId"] ?: throw IllegalArgumentException(i18n.t("admin_id_required", lang))
        val password = params["password"] ?: throw IllegalArgumentException(i18n.t("password_is_required", lang))
        if (password.isBlank()) throw IllegalArgumentException(i18n.t("password_is_required", lang))

        val admin = adminRepository.findById(adminId).orElseThrow { RuntimeException(i18n.t("admin_not_found", lang)) }
        admin.password = passwordEncoder.encode(password)
        adminRepository.save(admin)
        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("password_updated_successfully", lang)))
    }

    private fun deleteAdmin(params: Map<String, String>, currentAdminId: String, lang: String?): ResponseEntity<*> {
        val adminId = params["adminId"] ?: throw IllegalArgumentException(i18n.t("admin_id_required", lang))
        if (adminId == currentAdminId) throw IllegalStateException(i18n.t("cannot_delete_self", lang))

        adminRepository.deleteById(adminId)
        return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("admin_deleted_successfully", lang)))
    }

    // --- HTML View Builders ---
    
    private fun otherUsersController(admin: Admin, currentAdminId: String, lang: String?): String {
        if (admin.adminId == currentAdminId) {
            return ""
        }
        val toggleAction = if (admin.active) i18n.t("deactivate", lang) else i18n.t("activate", lang)
        val toggleClass = if (admin.active) "btn-warning" else "btn-success"
        return """
            <a href="javascript:;" onclick="handleAdminToggleActive('${admin.adminId}')" class="btn btn-sm ${toggleClass}">${toggleAction}</a>
            <a href="javascript:;" onclick="handleAdminDelete('${admin.adminId}')" class="btn btn-sm btn-danger">${i18n.t("delete", lang)}</a>
        """.trimIndent()
    }

    private fun buildListView(search: String?, page: Int, currentAdminId: String, lang: String?): String {
        val pageSize = 20
        val pageable = PageRequest.of(page - 1, pageSize, Sort.by("name"))

        val adminPage = if (!search.isNullOrBlank()) {
            adminRepository.findByNameContainingOrUsernameContaining(search, search, pageable)
        } else {
            adminRepository.findAll(pageable)
        }

        val admins = adminPage.content 
        

        val rows = if (admins.isNotEmpty()) {
            admins.joinToString("") { admin: Admin ->
                """
                <tr class="${if (!admin.active) "inactive" else "active"}">
                    <td>${admin.name ?: ""}</td>
                    <td>${admin.username ?: ""}</td>
                    <td>${admin.email ?: ""}</td>
                    <td>${admin.adminLevel?.name ?: ""}</td>
                    <td>${if (admin.active) i18n.t("active", lang) else i18n.t("inactive", lang)}</td>
                    <td class="actions">
                        <a href="#admin?action=detail&adminId=${admin.adminId}" class="btn btn-sm btn-info">${i18n.t("view", lang)}</a>
                        <a href="#admin?action=edit&adminId=${admin.adminId}" class="btn btn-sm btn-primary">${i18n.t("edit", lang)}</a>
                        ${otherUsersController(admin, currentAdminId, lang)}
                    </td>
                </tr>
                """.trimIndent()
            }
        } else {
            """<tr><td colspan="6">${i18n.t("no_admins_found", lang)}</td></tr>"""
        }

        // Simplified pagination for brevity
        return """
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="admin-search-form" class="search-form" onsubmit="handleAdminSearch(event)"> 
                <div class="filter-controls">
                    
                    <div class="form-group">
                        <label for="username">${i18n.t("name", lang)} or ${i18n.t("username", lang)}</label>
                        <input type="text" name="search" id="username" placeholder="${i18n.t("name", lang)} or ${i18n.t("username", lang)}" value="${search ?: ""}">
                    </div>
                    <button type="submit" class="btn btn-primary">${i18n.t("search", lang)}</button>
                    <a href="#admin?action=create" class="btn btn-primary">${i18n.t("add_new_admin", lang)}</a>
                    
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-data-list table-striped">
                <thead><tr><th>${i18n.t("name", lang)}</th><th>${i18n.t("username", lang)}</th><th>${i18n.t("email", lang)}</th><th>${i18n.t("admin_level", lang)}</th><th>${i18n.t("status", lang)}</th><th>${i18n.t("actions", lang)}</th></tr></thead>
                <tbody>$rows</tbody>
            </table>
        </div>
        """.trimIndent()
    }

    private fun buildDetailView(admin: Admin, currentAdminId: String, lang: String?): String {
        val actionButtons = if (admin.adminId != currentAdminId) {
            """
            <a href="#admin?action=change-password&adminId=${admin.adminId}" class="btn btn-warning">${i18n.t("update_password", lang)}</a>
            <button class="btn ${if (admin.active) "btn-warning" else "btn-success"}" onclick="handleAdminToggleActive('${admin.adminId}')">
                ${if (admin.active) i18n.t("deactivate", lang) else i18n.t("activate", lang)}
            </button>
            <button class="btn btn-danger" onclick="handleAdminDelete('${admin.adminId}')">${i18n.t("delete", lang)}</button>
            """.trimIndent()
        } else ""

        return """
        <div class="back-controls">
            <a href="#admin" class="btn btn-secondary">${i18n.t("back_to_list", lang)}</a>
            <a href="#admin?action=edit&adminId=${admin.adminId}" class="btn btn-primary">${i18n.t("edit", lang)}</a>
            $actionButtons
        </div>
        <div class="table-container detail-view">
            <table class="table">
                <tbody>
                    <tr><td><strong>${i18n.t("admin_id", lang)}</strong></td><td>${admin.adminId}</td></tr>
                    <tr><td><strong>${i18n.t("name", lang)}</strong></td><td>${admin.name ?: ""}</td></tr>
                    <tr><td><strong>${i18n.t("username", lang)}</strong></td><td>${admin.username ?: ""}</td></tr>
                    <tr><td><strong>${i18n.t("email", lang)}</strong></td><td>${admin.email ?: ""}</td></tr>
                    <tr><td><strong>${i18n.t("admin_level", lang)}</strong></td><td>${admin.adminLevel?.name ?: ""}</td></tr>
                    <tr><td><strong>${i18n.t("status", lang)}</strong></td><td>${if (admin.active) i18n.t("active", lang) else i18n.t("inactive", lang)}</td></tr>
                    <tr><td><strong>${i18n.t("time_create", lang)}</strong></td><td>${admin.timeCreate}</td></tr>
                    <tr><td><strong>${i18n.t("time_edit", lang)}</strong></td><td>${admin.timeEdit ?: ""}</td></tr>
                </tbody>
            </table>
        </div>
        """.trimIndent()
    }

    private fun buildCreateEditForm(admin: Admin?, lang: String?): String {
        val isCreate = admin == null
        val adminLevels = adminLevelRepository.findByActive(true)
        val levelOptions = adminLevels.joinToString("") { level: AdminLevel ->
            val selected = if (admin?.adminLevelId == level.adminLevelId) "selected" else ""
            """<option value="${level.adminLevelId}" $selected>${level.name}</option>"""
        }

        return """
        <div class="back-controls">
            <a href="#admin" class="btn btn-secondary">${i18n.t("back_to_list", lang)}</a>
        </div>
        <div class="table-container detail-view">
            <h3>${if (isCreate) i18n.t("add_new_admin", lang) else i18n.t("edit_admin", lang)}</h3>
            <form id="admin-form" class="form-group" onsubmit="handleAdminSave(event, '${admin?.adminId ?: ""}'); return false;">
                <input type="hidden" name="action" value="${if (isCreate) "create" else "update"}">
                <input type="hidden" name="adminId" value="${admin?.adminId ?: ""}">
                <table class="table table-borderless">
                    <tr>
                        <td>${i18n.t("name", lang)}</td>
                        <td><input type="text" name="name" value="${admin?.name ?: ""}" required autocomplete="off"></td>
                    </tr>
                    <tr>
                        <td>${i18n.t("username", lang)}</td>
                        <td><input type="text" name="username" value="${admin?.username ?: ""}" required autocomplete="off"></td>
                    </tr>
                    <tr>
                        <td>${i18n.t("email", lang)}</td>
                        <td><input type="email" name="email" value="${admin?.email ?: ""}" required autocomplete="off"></td>
                    </tr>
                    ${if (isCreate) """
                    <tr>
                        <td>${i18n.t("password", lang)}</td>
                        <td><input type="password" name="password" required autocomplete="new-password"></td>
                    </tr>
                    """ else ""}
                    <tr>
                        <td>${i18n.t("admin_level", lang)}</td>
                        <td>
                            <select name="admin_level_id" required>
                                <option value="">${i18n.t("select_option", lang)}</option>
                                $levelOptions
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>${i18n.t("active", lang)}</td>
                        <td><input type="checkbox" name="active" ${if (admin?.active != false) "checked" else ""} value="on"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" class="btn btn-success">${i18n.t("save", lang)}</button>
                            <a href="#admin" class="btn btn-secondary">${i18n.t("cancel", lang)}</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        """.trimIndent()
    }

    private fun buildChangePasswordForm(adminId: String?, lang: String?): String {
        if (adminId.isNullOrBlank()) return i18n.t("admin_id_required", lang)
        return """
        <div class="back-controls">
            <a href="#admin?action=detail&adminId=$adminId" class="btn btn-secondary">${i18n.t("back_to_detail", lang)}</a>
        </div>
        <div class="table-container detail-view">
            <h3>Change Password</h3>
            <form id="change-password-form" class="form-group" onsubmit="handleAdminChangePassword(event, '$adminId'); return false;">
                 <input type="hidden" name="action" value="change_password">
                 <input type="hidden" name="adminId" value="$adminId">
                <table class="table table-borderless">
                    <tr>
                        <td>${i18n.t("new_password", lang)}</td>
                        <td><input type="password" name="password" required autocomplete="new-password"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" class="btn btn-success">${i18n.t("update", lang)}</button>
                            <a href="#admin?action=detail&adminId=$adminId" class="btn btn-secondary">${i18n.t("cancel", lang)}</a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        """.trimIndent()
    }
}
