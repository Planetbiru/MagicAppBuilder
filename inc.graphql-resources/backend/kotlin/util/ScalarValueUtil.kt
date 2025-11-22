package com.planetbiru.graphqlapplication.util

import java.time.LocalDate
import java.time.LocalDateTime
import java.time.format.DateTimeFormatter

object ScalarValueUtil {

    private const val DATE_TIME_FORMAT = "yyyy-MM-dd HH:mm:ss"
    private const val DATE_FORMAT = "yyyy-MM-dd"

    private val dateTimeFormatter = DateTimeFormatter.ofPattern(DATE_TIME_FORMAT)
    private val dateFormatter = DateTimeFormatter.ofPattern(DATE_FORMAT)

    fun localDateTimeToString(datetime: LocalDateTime?): String? {
        return datetime?.format(dateTimeFormatter)
    }

    fun localDateToString(date: LocalDate?): String? {
        return date?.format(dateFormatter)
    }

    fun stringToLocalDateTime(datetime: String?): LocalDateTime? {
        return if (datetime.isNullOrEmpty()) null else LocalDateTime.parse(datetime, dateTimeFormatter)
    }

    fun stringToLocalDate(date: String?): LocalDate? {
        return if (date.isNullOrEmpty()) null else LocalDate.parse(date, dateFormatter)
    }
}