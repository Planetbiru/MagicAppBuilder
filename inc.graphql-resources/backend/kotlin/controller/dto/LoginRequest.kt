package com.planetbiru.graphqlapplication.controller.dto

/**
 * Data transfer object for handling user login requests.
 * This class encapsulates the credentials (username and password) submitted during the login process.
 *
 * @property username The username provided by the user.
 * @property password The password provided by the user.
 */
data class LoginRequest(
    val username: String,
    val password: String
)