<?php

namespace MagicObject\Util;

use DateTime;

/**
 * Class PicoDateTimeUtil
 *
 * A utility class for parsing date and time strings into DateTime objects.
 * This class provides methods to convert various date formats into a 
 * standardized DateTime object for easier manipulation and formatting.
 *
 * It supports multiple date formats, including ISO 8601 and RFC 2822,
 * as well as local and custom formats and Unix timestamps.
 */
class PicoDateTimeUtil
{
    /**
     * Parse DateTime from a string or Unix timestamp.
     *
     * This method attempts to parse a given date string or Unix timestamp 
     * into a DateTime object using multiple predefined formats. If the parsing 
     * is successful, it returns the corresponding DateTime object; otherwise, 
     * it returns null.
     *
     * @param string|int $dateString The date string or Unix timestamp to be parsed.
     * @return DateTime|null Returns a DateTime object if parsing is successful, 
     *                       or null if no formats matched.
     */
    public static function parseDateTime($dateString) 
    {
        // Check if the input is a valid Unix timestamp
        if (is_numeric($dateString) && (int)$dateString == $dateString) {
            return (new DateTime())->setTimestamp((int)$dateString);
        }

        // List of formats to parse
        $formats = [
            'Y-m-d',              // ISO 8601: 2024-10-24
            'Y-m-d H:i:s',        // ISO 8601: 2024-10-24 15:30:00
            'Y-m-d\TH:i:s',       // ISO 8601: 2024-10-24T15:30:00
            'Y-m-d\TH:i:s\Z',     // ISO 8601: 2024-10-24T15:30:00Z
            'D, d M Y H:i:s O',   // RFC 2822: Thu, 24 Oct 2024 15:30:00 +0000
            'd/m/Y',              // Local format: 24/10/2024
            'd F Y',              // Format with month name: 24 October 2024
            'l, d F Y'            // Format with day of the week: Thursday, 24 October 2024
        ];

        // Iterate over each format and attempt to create a DateTime object
        foreach ($formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $dateString);
            if ($dateTime !== false) {
                return $dateTime; // Return the DateTime object if successful
            }
        }

        return null; // Return null if no formats matched
    }
}
