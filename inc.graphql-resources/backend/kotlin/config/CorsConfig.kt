package com.planetbiru.graphqlapplication.config

import org.springframework.beans.factory.annotation.Value
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.web.servlet.config.annotation.CorsRegistry
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer

/**
 * Configuration class for setting up Cross-Origin Resource Sharing (CORS).
 * This class defines the global CORS configuration for the application, allowing
 * requests from specified origins.
 *
 * @property origins A comma-separated string of allowed origins, injected from the `app.cors.origins` application property.
 */
@Configuration
class CorsConfig(
    @Value("\${app.cors.origins}") private val origins: String
) {
    /**
     * Creates a [WebMvcConfigurer] bean that configures CORS mappings.
     * It applies the CORS settings to all endpoints (`/**`) and allows all HTTP methods.
     * The allowed origins are parsed from the `origins` property.
     *
     * @return A [WebMvcConfigurer] instance with the defined CORS configuration.
     */
    @Bean
    fun corsConfigurer(): WebMvcConfigurer {
        return object : WebMvcConfigurer {
            override fun addCorsMappings(registry: CorsRegistry) {
                registry.addMapping("/**")
                    .allowedOrigins(*origins.split(",").map { it.trim() }.toTypedArray())
                    .allowedMethods("*")
            }
        }
    }
}