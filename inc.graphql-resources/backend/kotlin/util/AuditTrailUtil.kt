package com.planetbiru.graphqlapplication.util

import jakarta.servlet.http.HttpServletRequest
import org.springframework.web.context.request.RequestContextHolder
import org.springframework.web.context.request.ServletRequestAttributes

object AuditTrailUtil {

    /**
     * Retrieves the client's IP address from the current request.
     * It checks for the 'X-FORWARDED-FOR' header first.
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
     */
    fun getUserId(): String? {
        val request = (RequestContextHolder.getRequestAttributes() as? ServletRequestAttributes)?.request ?: return null
        val session = request.getSession(false)
        return session?.getAttribute("adminId") as? String
    }
}