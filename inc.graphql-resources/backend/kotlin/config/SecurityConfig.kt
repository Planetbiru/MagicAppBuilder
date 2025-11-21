package com.planetbiru.graphqlapplication.config

import com.planetbiru.graphqlapplication.service.JpaUserDetailsService
import org.springframework.beans.factory.annotation.Value
import org.springframework.boot.autoconfigure.condition.ConditionalOnProperty
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.Configuration
import org.springframework.http.HttpStatus
import org.springframework.security.config.annotation.method.configuration.EnableMethodSecurity
import org.springframework.security.config.annotation.web.builders.HttpSecurity
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity
import org.springframework.security.config.annotation.web.configurers.LogoutConfigurer
import org.springframework.security.config.annotation.web.invoke
import org.springframework.security.web.SecurityFilterChain
import org.springframework.security.web.util.matcher.AntPathRequestMatcher
import org.springframework.session.data.redis.config.annotation.web.http.EnableRedisHttpSession

@Configuration
@EnableWebSecurity
@EnableMethodSecurity(prePostEnabled = true)
class SecurityConfig(
    private val jpaUserDetailsService: JpaUserDetailsService,
) {

    @Value("\${app.security.require-login}")
    private val requireLogin: Boolean = true

    @Bean
    fun securityFilterChain(http: HttpSecurity): SecurityFilterChain {

        http {
            csrf {
                disable() // Nonaktifkan CSRF untuk login manual
            }

            sessionManagement {
                sessionFixation { migrateSession() } // Pastikan session tetap aktif
            }

            logout {
                logoutUrl = "/logout"
                logoutSuccessHandler = org.springframework.security.web.authentication.logout.HttpStatusReturningLogoutSuccessHandler(HttpStatus.OK)
            }

            authorizeHttpRequests {
                authorize(anyRequest, permitAll)
            }
        }

        return http.build()
    }

    @Configuration
    @ConditionalOnProperty(value = ["spring.session.store-type"], havingValue = "redis")
    @EnableRedisHttpSession
    class RedisSessionConfig
}