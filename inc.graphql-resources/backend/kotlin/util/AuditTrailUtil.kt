package com.planetbiru.graphqlapplication.util

import jakarta.servlet.http.HttpServletRequest
import org.springframework.web.context.request.RequestContextHolder
import org.springframework.web.context.request.ServletRequestAttributes

/**
 * Utility object for retrieving audit trail information from the current web request.
 * This includes details like language ID, user IP address, and user ID from the request context.
 */
object AuditTrailUtil {

    /**
     * Retrieves the language ID from the current request's headers.
     * It looks for the 'X-LANGUAGE-ID' header. If not present or empty, it defaults to "en".
     * @return The language ID as a [String], or `null` if the request context is not available.
     */
    fun getLanguageId(): String? {
        val request = (RequestContextHolder.getRequestAttributes() as? ServletRequestAttributes)?.request ?: return null
        var languageId = request.getHeader("X-LANGUAGE-ID")
        if (languageId.isNullOrEmpty()) {
            languageId = "en"
        }
        return languageId
    }

    /**
     * Retrieves the client's IP address from the current request.
     * It checks for the 'X-FORWARDED-FOR' header first to support proxies,
     * otherwise falls back to the remote address from the request.
     * @return The client's IP address as a [String], or `null` if the request context is not available.
     */
    fun getUserIp(): String? {
        val request = (RequestContextHolder.getRequestAttributes() as? ServletRequestAttributes)?.request ?: return null
        var remoteAddr = request.getHeader("X-FORWARDED-FOR")
        if (remoteAddr.isNullOrEmpty()) {
            remoteAddr = request.remoteAddr
        } else {
            remoteAddr = remoteAddr.split(",")[0].trim()
        }
        return remoteAddr
    }

    /**
     * Retrieves the ID of the currently authenticated admin user from the session.
     * @return The admin user's ID as a [String] from the session attribute "adminId",
     * or `null` if the session or attribute does not exist.
     */
    fun getUserId(): String? {
        val request = (RequestContextHolder.getRequestAttributes() as? ServletRequestAttributes)?.request ?: return null
        val session = request.getSession(false)
        return session?.getAttribute("adminId") as? String
    }
}