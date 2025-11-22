package com.planetbiru.graphqlapplication.controller.core

import com.planetbiru.graphqlapplication.util.I18nUtil
import jakarta.servlet.http.HttpSession
import org.springframework.beans.factory.annotation.Value
import org.springframework.core.io.Resource
import org.springframework.core.io.ResourceLoader
import org.springframework.core.io.support.ResourcePatternResolver
import org.springframework.http.HttpStatus
import org.springframework.http.MediaType
import org.springframework.http.ResponseEntity
import org.springframework.util.StreamUtils
import org.springframework.web.bind.annotation.GetMapping
import org.springframework.web.bind.annotation.RequestHeader
import org.springframework.web.bind.annotation.RequestParam
import java.io.FileNotFoundException
import java.io.IOException
import java.nio.charset.StandardCharsets
import java.util.Locale

data class ThemeDto(val name: String, val title: String)

/**
 * REST controller for serving frontend configuration.
 */
@org.springframework.web.bind.annotation.RestController
class FrontendConfigController(
    private val resourceLoader: ResourceLoader,
    private val resourcePatternResolver: ResourcePatternResolver,
    @Value("\${app.security.require-login}") private val requireLogin: Boolean,
    private val i18n: I18nUtil
) {

    /**
     * {@code GET /frontend-config} : get the frontend configuration.
     *
     * @return the [ResponseEntity] with status `200 (OK)` and the configuration in body,
     * or with status `401 (Unauthorized)` if login is required,
     * or with status `500 (Internal Server Error)` if the configuration file could not be read.
     */
    @GetMapping("/frontend-config")
    fun getFrontendConfig(
        session: HttpSession,
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<String> {
        if (requireLogin && session.getAttribute("username") == null) {
            return ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(i18n.t("unauthorized", lang))
        }

        return try {
            val resource = resourceLoader.getResource("classpath:static/config/frontend-config.json")
            val config = StreamUtils.copyToString(resource.inputStream, StandardCharsets.UTF_8)
            ResponseEntity.ok()
                .header("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0")
                .header("Pragma", "no-cache")
                .header("Expires", "0")
                .contentType(MediaType.APPLICATION_JSON)
                .body(config)
        } catch (e: IOException) {
            ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(i18n.t("error_reading_frontend_config", lang))
        }
    }

    /**
     * {@code GET /available-language.json} : get the available languages configuration.
     *
     * @return the [ResponseEntity] with status `200 (OK)` and the configuration in body,
     * or with status `500 (Internal Server Error)` if the configuration file could not be read.
     */
    @GetMapping("/available-language")
    fun getAvailableLanguages(
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<String> {
        try {
            val resource = resourceLoader.getResource("classpath:static/langs/available-language.json")
            val config = StreamUtils.copyToString(resource.inputStream, StandardCharsets.UTF_8)
            return ResponseEntity.ok()
                .header("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0")
                .header("Pragma", "no-cache")
                .header("Expires", "0")
                .contentType(MediaType.APPLICATION_JSON).body(config)
        } catch (e: IOException) {
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(i18n.t("error_reading_language_config", lang))
        }
    }

    /**
     * {@code GET /available-theme.json} : get the list of available themes.
     *
     * @return the [ResponseEntity] with status `200 (OK)` and the list of themes in body,
     * or with status `500 (Internal Server Error)` if an error occurs.
     */
    @GetMapping("/available-theme")
    fun getAvailableThemes(
        @RequestHeader(value = "X-Language-Id", required = false) lang: String?
    ): ResponseEntity<Any> {
        try {
            val resources: Array<Resource> = resourcePatternResolver.getResources("classpath:static/assets/themes/*/style.min.css")
            val themes = resources.mapNotNull { resource ->
                try {
                    val themeDir = resource.file.parentFile
                    if (themeDir.isDirectory) {
                        val themeName = themeDir.name
                        val title = themeName.replace('-', ' ').replace('_', ' ')
                            .split(' ')
                            .joinToString(" ") { it.replaceFirstChar { char -> if (char.isLowerCase()) char.titlecase(Locale.getDefault()) else char.toString() } }
                        ThemeDto(name = themeName, title = title)
                    } else {
                        null
                    }
                } catch (e: IOException) {
                    // Ignore resources that are not file-based (e.g., inside a JAR)
                    null
                }
            }
            return ResponseEntity.ok()
                .header("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0")
                .header("Pragma", "no-cache")
                .header("Expires", "0")
                .contentType(MediaType.APPLICATION_JSON).body(themes)
        } catch (e: IOException) {
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(i18n.t("error_scanning_themes", lang))
        }
    }
}
