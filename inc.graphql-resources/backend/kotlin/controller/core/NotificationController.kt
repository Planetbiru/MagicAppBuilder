package com.planetbiru.graphqlapplication.controller.core

import com.planetbiru.graphqlapplication.model.repository.core.AdminRepository
import com.planetbiru.graphqlapplication.model.repository.core.NotificationRepository
import com.planetbiru.graphqlapplication.util.I18nUtil
import jakarta.servlet.http.HttpServletRequest
import jakarta.servlet.http.HttpSession
import org.springframework.data.domain.PageRequest
import org.springframework.data.domain.Sort
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.stereotype.Controller
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.PostMapping
import org.springframework.web.bind.annotation.RequestHeader
import org.springframework.web.bind.annotation.RequestParam
import java.time.LocalDateTime

@Controller
class NotificationController(
    private val notificationRepository: NotificationRepository,
    private val adminRepository: AdminRepository,
    private val i18n: I18nUtil
) {

    private fun getCurrentAdminId(session: HttpSession): String? {
        return session.getAttribute("adminId") as? String
    }

    @GetMapping("/notification")
    fun handleNotificationGet(
        @RequestParam(required = false) notificationId: String?,
        @RequestParam(required = false) search: String?,
        @RequestParam(defaultValue = "1") page: Int,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?,
        session: HttpSession,
        request: HttpServletRequest
    ): ResponseEntity<String> {
        val currentAdminId = getCurrentAdminId(session)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized", lang))

        val admin = adminRepository.findById(currentAdminId).orElse(null)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("admin_not_found", lang))

        val htmlContent = if (notificationId != null) {
            buildDetailView(notificationId, admin.adminId, admin.adminLevelId, request.remoteAddr, lang)
        } else {
            buildListView(search, page, admin.adminId, admin.adminLevelId, lang)
        }

        return ResponseEntity.ok().contentType(MediaType.TEXT_HTML).body(htmlContent)
    }

    @PostMapping("/notification")
    fun handleNotificationPost(
        session: HttpSession,
        request: HttpServletRequest,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<*> {
        val currentAdminId = getCurrentAdminId(session)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("success" to false, "message" to i18n.t("unauthorized", lang)))

        val admin = adminRepository.findById(currentAdminId).orElse(null)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("success" to false, "message" to i18n.t("admin_not_found", lang)))

        val params = request.parameterMap.mapValues { it.value.firstOrNull() ?: "" }
        val action = params["action"]
        val notificationId = params["notificationId"] ?: return ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("notification_id_required", lang)))

        return try {
            when (action) {
                "mark_as_unread" -> markAsUnread(notificationId, admin.adminId, admin.adminLevelId, lang)
                "delete" -> deleteNotification(notificationId, admin.adminId, admin.adminLevelId, lang)
                else -> ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("invalid_action", lang)))
            }
        } catch (e: Exception) {
            ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(mapOf("success" to false, "message" to e.message))
        }
    }

    private fun markAsUnread(notificationId: String, adminId: String, adminLevelId: String?, lang: String?): ResponseEntity<*> {
        val notification = notificationRepository.findById(notificationId).orElseThrow { RuntimeException(i18n.t("notification_not_found", lang)) }
        if (notification.adminId == adminId || notification.adminGroup == adminLevelId) {
            notification.isRead = false
            notification.timeRead = null
            notificationRepository.save(notification)
            return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("notification_marked_as_unread", lang)))
        }
        return ResponseEntity.status(HttpStatus.FORBIDDEN).body(mapOf("success" to false, "message" to i18n.t("forbidden", lang)))
    }

    private fun deleteNotification(notificationId: String, adminId: String, adminLevelId: String?, lang: String?): ResponseEntity<*> {
        val notification = notificationRepository.findById(notificationId).orElseThrow { RuntimeException(i18n.t("notification_not_found", lang)) }
        if (notification.adminId == adminId || notification.adminGroup == adminLevelId) {
            notificationRepository.delete(notification)
            return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("notification_deleted_successfully", lang)))
        }
        return ResponseEntity.status(HttpStatus.FORBIDDEN).body(mapOf("success" to false, "message" to i18n.t("forbidden", lang)))
    }

    private fun buildListView(search: String?, page: Int, adminId: String, adminLevelId: String?, lang: String?): String {
        val pageSize = 20
        val pageable = PageRequest.of(page - 1, pageSize, Sort.by("timeCreate").descending())

        val notificationPage = if (!search.isNullOrBlank()) {
            notificationRepository.findByAdminIdOrAdminGroupAndSearch(adminId, adminLevelId, search, pageable)
        } else {
            notificationRepository.findByAdminIdOrAdminGroup(adminId, adminLevelId, pageable)
        }

        val notifications = notificationPage.content

        val notificationItems = if (notifications.isNotEmpty()) {
            notifications.joinToString("") { notification ->
                val isReadClass = if (notification.isRead) "read" else "unread"
                val contentSnippet = notification.content?.take(150)?.let { if (it.length == 150) "$it..." else it } ?: ""
                val actionButtons = if (notification.isRead) {
                    """
                    <button class="btn btn-sm btn-secondary" onclick="markNotificationAsUnread('${notification.notificationId}', 'list')">${i18n.t("mark_as_unread", lang)}</button>
                    <button class="btn btn-sm btn-danger" onclick="handleNotificationDelete('${notification.notificationId}')">${i18n.t("delete", lang)}</button>
                    """
                } else ""

                """
                <div class="message-item $isReadClass">
                    <span class="message-status-indicator"></span>
                    <div class="notification-header">
                        <div class="message-link-wrapper">
                            <a href="#notification?notificationId=${notification.notificationId}" class="message-link">
                                <span class="message-subject">${notification.subject ?: ""}</span>
                            </a>
                            <span class="message-time">${notification.timeCreate}</span>
                            $actionButtons
                        </div>
                    </div>
                    <div class="message-content">${contentSnippet}</div>
                </div>
                """
            }
        } else {
            "<p>No notification</p>"
        }

        // Simplified pagination for brevity
        return """
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="notification-search-form" class="search-form" onsubmit="handleNotificationSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_notification">${i18n.t("search", lang)}</label>
                        <input type="text" name="search" id="search_notification" placeholder="${i18n.t("search", lang)}" value="${search ?: ""}">
                    </div>
                    <button type="submit" class="btn btn-primary">${i18n.t("search", lang)}</button>
                </div>
            </form>
        </div>
        <div class="message-list-container">
            $notificationItems
        </div>
        """.trimIndent()
    }

    private fun buildDetailView(notificationId: String, adminId: String, adminLevelId: String?, ipRead: String, lang: String?): String {
        val notificationOpt = notificationRepository.findByIdAndAdmin(notificationId, adminId, adminLevelId)
        if (notificationOpt.isEmpty) {
            return """<div class="table-container detail-view">${i18n.t("no_notification", lang)}</div>"""
        }

        val notification = notificationOpt.get()

        // Mark as read
        if (!notification.isRead) {
            notification.isRead = true
            notification.timeRead = LocalDateTime.now()
            notification.ipRead = ipRead
            notificationRepository.save(notification)
        }

        val actionButtons = if (notification.isRead) {
            """
            <button class="btn btn-primary" onclick="markNotificationAsUnread('${notification.notificationId}', 'detail')">${i18n.t("mark_as_unread", lang)}</button>
            <button class="btn btn-danger" onclick="handleNotificationDelete('${notification.notificationId}')">${i18n.t("delete", lang)}</button>
            """
        } else ""

        val statusHtml = if (notification.isRead) {
            """<span class="status-read">${i18n.t("read_at", lang)} ${notification.timeRead}</span>"""
        } else {
            """<span class="status-unread">${i18n.t("unread", lang)}</span>"""
        }

        val linkButton = if (!notification.link.isNullOrBlank()) {
            """<p><a href="${notification.link}" target="_blank" class="btn btn-primary mt-3">${i18n.t("more_info", lang)}</a></p>"""
        } else ""

        return """
        <div class="back-controls">
            <button id="back-to-list" class="btn btn-secondary" onclick="backToList('notification')">${i18n.t("back_to_list", lang)}</button>
            $actionButtons
        </div>
        <div class="notification-container">
            <div class="notification-header">
                <h3>${notification.subject ?: ""}</h3>
                <div class="message-meta">
                    <div><strong>${i18n.t("time", lang)}:</strong> ${notification.timeCreate}</div>
                    <div><strong>${i18n.t("status", lang)}:</strong> $statusHtml</div>
                </div>
            </div>
            <div class="message-body">
                ${notification.content?.replace("\n", "<br>") ?: ""}
                $linkButton
            </div>
        </div>
        """.trimIndent()
    }
}