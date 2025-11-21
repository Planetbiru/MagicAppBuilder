package com.planetbiru.graphqlapplication.util

import com.fasterxml.jackson.core.type.TypeReference
import com.fasterxml.jackson.databind.ObjectMapper
import org.springframework.beans.factory.annotation.Value
import org.springframework.core.io.ClassPathResource
import org.springframework.stereotype.Component
import java.io.IOException
import java.util.concurrent.ConcurrentHashMap

@Component
class I18nUtil(private val objectMapper: ObjectMapper) {

    private val translationsCache: MutableMap<String, Map<String, String>> = ConcurrentHashMap()

    @Value("\${app.i18n.default-language:en}")
    private lateinit var defaultLanguage: String

    private fun loadTranslations(lang: String): Map<String, String> {
        return translationsCache.computeIfAbsent(lang) { l ->
            try {
                val resource = ClassPathResource("static/langs/i18n/$l.json")
                if (!resource.exists()) {
                    // If the requested language file doesn't exist, try loading the default
                    if (l != defaultLanguage) {
                        return@computeIfAbsent loadTranslations(defaultLanguage)
                    }
                    return@computeIfAbsent emptyMap()
                }
                resource.inputStream.use { inputStream ->
                    objectMapper.readValue(inputStream, object : TypeReference<Map<String, String>>() {})
                }
            } catch (e: IOException) {
                // Log the error
                e.printStackTrace()
                emptyMap()
            }
        }
    }

    fun t(code: String, lang: String?, vararg args: Any): String {
        val language = if (lang.isNullOrBlank()) defaultLanguage else lang
        val translations = loadTranslations(language)
        val message = translations.getOrDefault(code, snakeCaseToTitleCase(code))

        return if (args.isNotEmpty()) {
            String.format(message, *args)
        } else {
            message
        }
    }

    fun snakeCaseToTitleCase(input: String): String {
        val result = StringBuilder()
        var capitalizeNext = true

        for (c in input.toCharArray()) {
            if (c == '_') {
                capitalizeNext = true
                result.append(" ")
            } else {
                if (capitalizeNext) {
                    result.append(c.uppercaseChar())
                    capitalizeNext = false
                } else {
                    result.append(c.lowercaseChar())
                }
            }
        }

        return result.toString().trim()
    }
}