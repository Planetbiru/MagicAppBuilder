package com.planetbiru.graphqlapplication.config

import org.springframework.security.crypto.password.PasswordEncoder
import org.springframework.stereotype.Component
import java.math.BigInteger
import java.security.MessageDigest

@Component
class Sha1PasswordEncoder : PasswordEncoder {

    override fun encode(rawPassword: CharSequence): String {
        return sha1(sha1(rawPassword.toString()))
    }

    override fun matches(rawPassword: CharSequence, encodedPassword: String): Boolean {
        return encodedPassword == encode(rawPassword)
    }

    fun sha1(input: String): String {
        val md = MessageDigest.getInstance("SHA-1")
        val messageDigest = md.digest(input.toByteArray())
        val no = BigInteger(1, messageDigest)
        var hashtext = no.toString(16)
        while (hashtext.length < 40) {
            hashtext = "0$hashtext"
        }
        return hashtext
    }
}