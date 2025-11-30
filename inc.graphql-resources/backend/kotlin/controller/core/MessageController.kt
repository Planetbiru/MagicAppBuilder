package com.planetbiru.graphqlapplication.controller.core

import com.planetbiru.graphqlapplication.model.entity.core.Message
import com.planetbiru.graphqlapplication.model.repository.core.MessageRepository
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
class MessageController(
    private val messageRepository: MessageRepository,
    private val i18n: I18nUtil
) {

    private fun getCurrentAdminId(session: HttpSession): String? {
        return session.getAttribute("adminId") as? String
    }

    @GetMapping("/message")
    fun handleMessageGet(
        @RequestParam(required = false) messageId: String?,
        @RequestParam(required = false) search: String?,
        @RequestParam(defaultValue = "1") page: Int,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?,
        session: HttpSession
    ): ResponseEntity<String> {
        val currentAdminId = getCurrentAdminId(session)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized", lang))

        val htmlContent = if (messageId != null) {
            buildDetailView(messageId, currentAdminId, lang)
        } else {
            buildListView(search, page, currentAdminId, lang)
        }

        return ResponseEntity.ok().contentType(MediaType.TEXT_HTML).body(htmlContent)
    }

    @PostMapping("/message")
    fun handleMessagePost(
        session: HttpSession,
        request: HttpServletRequest,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<*> {
        val currentAdminId = getCurrentAdminId(session)
            ?: return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(mapOf("success" to false, "message" to i18n.t("unauthorized", lang)))

        val params = request.parameterMap.mapValues { it.value.firstOrNull() ?: "" }
        val action = params["action"]
        val messageId = params["messageId"] ?: return ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("message_id_required", lang)))

        return try {
            when (action) {
                "mark_as_unread" -> markAsUnread(messageId, currentAdminId, lang)
                "delete" -> deleteMessage(messageId, currentAdminId, lang)
                else -> ResponseEntity.badRequest().body(mapOf("success" to false, "message" to i18n.t("invalid_action", lang)))
            }
        } catch (e: Exception) {
            ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(mapOf("success" to false, "message" to e.message))
        }
    }

    private fun markAsUnread(messageId: String, currentAdminId: String, lang: String?): ResponseEntity<*> {
        val message = messageRepository.findById(messageId).orElseThrow { RuntimeException(i18n.t("message_not_found", lang)) }
        if (message.receiverId == currentAdminId) {
            message.isRead = false
            message.timeRead = null
            messageRepository.save(message)
            return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("message_marked_as_unread", lang)))
        }
        return ResponseEntity.status(HttpStatus.FORBIDDEN).body(mapOf("success" to false, "message" to i18n.t("forbidden", lang)))
    }

    private fun deleteMessage(messageId: String, currentAdminId: String, lang: String?): ResponseEntity<*> {
        val message = messageRepository.findById(messageId).orElseThrow { RuntimeException(i18n.t("message_not_found", lang)) }
        if (message.senderId == currentAdminId || message.receiverId == currentAdminId) {
            messageRepository.delete(message)
            return ResponseEntity.ok(mapOf("success" to true, "message" to i18n.t("message_deleted_successfully", lang)))
        }
        return ResponseEntity.status(HttpStatus.FORBIDDEN).body(mapOf("success" to false, "message" to i18n.t("forbidden", lang)))
    }

    private fun buildListView(search: String?, page: Int, currentAdminId: String, lang: String?): String {
        val pageSize = 20
        val pageable = PageRequest.of(page - 1, pageSize, Sort.by("timeCreate").descending())

        val messagePage = if (!search.isNullOrBlank()) {
            messageRepository.findBySenderIdOrReceiverIdAndSearch(currentAdminId, search, pageable)
        } else {
            messageRepository.findBySenderIdOrReceiverId(currentAdminId, currentAdminId, pageable)
        }

        val messages = messagePage.content

        val messageItems = if (messages.isNotEmpty()) {
            messages.joinToString("") { message ->
                val isReadClass = if (message.isRead) "read" else "unread"
                val contentSnippet = message.content?.take(150)?.let { if (it.length == 150) "$it..." else it } ?: ""
                val actionButtons = if (message.receiverId == currentAdminId && message.isRead) {
                    """
                    <button class="btn btn-sm btn-secondary" onclick="markMessageAsUnread('${message.messageId}', 'list')">${i18n.t("mark_as_unread", lang)}</button>
                    <button class="btn btn-sm btn-danger" onclick="handleMessageDelete('${message.messageId}')">${i18n.t("delete", lang)}</button>
                    """
                } else ""

                """
                <div class="message-item $isReadClass">
                    <span class="message-status-indicator"></span>
                    <div class="message-header">
                        <div class="message-link-wrapper">
                            <a href="#message?messageId=${message.messageId}" class="message-link">
                                <span class="message-sender">${message.sender?.name ?: i18n.t("system", lang)}</span>
                                <span class="message-subject">${message.subject ?: ""}</span>
                            </a>
                            <span class="message-time">${message.timeCreate}</span>
                            $actionButtons
                        </div>
                    </div>
                    <div class="message-content">${contentSnippet}</div>
                </div>
                """
            }
        } else {
            "<p>${i18n.t("no_message", lang)}</p>"
        }

        // Simplified pagination for brevity
        return """
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="message-search-form" class="search-form" onsubmit="handleMessageSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_message">${i18n.t("search", lang)}</label>
                        <input type="text" name="search" id="search_message" placeholder="${i18n.t("search", lang)}" value="${search ?: ""}">
                    </div>
                    <button type="submit" class="btn btn-primary">${i18n.t("search", lang)}</button>
                </div>
            </form>
        </div>
        <div class="message-list-container">
            $messageItems
        </div>
        """.trimIndent()
    }

    private fun buildDetailView(messageId: String, currentAdminId: String, lang: String?): String {
        val messageOpt = messageRepository.findByMessageIdAndUser(messageId, currentAdminId)
        if (messageOpt.isEmpty) {
            return """<div class="table-container detail-view">${i18n.t("no_message", lang)}</div>"""
        }

        val message = messageOpt.get()

        // Mark as read
        if (message.receiverId == currentAdminId && !message.isRead) {
            message.isRead = true
            message.timeRead = LocalDateTime.now()
            messageRepository.save(message)
        }

        val actionButtons = if (message.receiverId == currentAdminId && message.isRead) {
            """
            <button class="btn btn-primary" onclick="markMessageAsUnread('${message.messageId}', 'detail')">${i18n.t("mark_as_unread", lang)}</button>
            <button class="btn btn-danger" onclick="handleMessageDelete('${message.messageId}')">${i18n.t("delete", lang)}</button>
            """
        } else ""

        val statusHtml = if (message.isRead) {
            """<span class="status-read">${i18n.t("read_at", lang)} ${message.timeRead}</span>"""
        } else {
            """<span class="status-unread">${i18n.t("unread", lang)}</span>"""
        }

        return """
        <div class="back-controls">
            <button id="back-to-list" class="btn btn-secondary" onclick="backToList('message')">${i18n.t("back_to_list", lang)}</button>
            $actionButtons
        </div>
        <div class="message-container">
            <div class="message-header">
                <h3>${message.subject ?: ""}</h3>
                <div class="message-meta">
                    <div><strong>${i18n.t("from", lang)}:</strong> ${message.sender?.name ?: i18n.t("system", lang)}</div>
                    <div><strong>${i18n.t("to", lang)}:</strong> ${message.receiver?.name ?: i18n.t("system", lang)}</div>
                    <div><strong>${i18n.t("time", lang)}:</strong> ${message.timeCreate}</div>
                    <div><strong>${i18n.t("status", lang)}:</strong> $statusHtml</div>
                </div>
            </div>
            <div class="message-body">
                ${message.content?.replace("\n", "<br>") ?: ""}
            </div>
        </div>
        """.trimIndent()
    }
}