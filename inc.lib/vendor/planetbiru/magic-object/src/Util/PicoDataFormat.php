<?php

namespace MagicObject\Util;

use DateTime;

class PicoDataFormat
{
    /**
     * Convert decimal to DMS (Degrees, Minutes, Seconds) format.
     *
     * @param float $decimal The decimal value to convert.
     * @param array $params An associative array containing the following optional parameters:
     *     - bool $inSeconds Whether the input is in seconds. If true, the input is treated as seconds.
     *                       If false, the input is treated as decimal degrees. Default is false.
     *     - string $separator The separator between degree, minute, and second. Default is ":".
     *     - int $decimalPlaces The number of decimal places for seconds. Default is 0.
     *     - bool $withSign Whether to include a sign ('+' or '-') for negative values. Default is false.
     *     - int $zeroPadding The number of leading zeros to pad for degree, minute, and second. Default is 0.
     *     - bool $trimDegreeMinute Whether to trim degree and minute if they are zero. Default is false.
     *
     * @return string The formatted DMS string.
     */
    public static function convertDecimalToDMS($decimal, $params = array()) // NOSONAR
    {
        $inSeconds = isset($params[0]) ? (bool) $params[0] : false; // bool - whether to convert to seconds, default is false
        $separator = isset($params[1]) ? $params[1] : ":"; // string - separator, default is ":"
        $decimalPlaces = isset($params[2]) ? (int) $params[2] : 0; // int - number of decimal places, default is 0
        $withSign = isset($params[3]) ? (bool) $params[3] : false; // bool - whether to include sign, default is false
        $zeroPadding = isset($params[4]) ? (int) $params[4] : 0; // int - number of zero padding, default is 0
        $trimDegreeMinute = isset($params[5]) ? (bool) $params[5] : false; // bool - whether to trim degree and minute if they are 0, default is false
            
        if($inSeconds) {
            $decimal = $decimal / 3600; // Convert seconds to decimal degrees
        }
        // Check if the number is negative
        $isNegative = $decimal < 0;
        $decimal = abs($decimal);

        // Calculate degrees
        $degrees = floor($decimal);

        // Calculate minutes
        $minutes = floor(($decimal - $degrees) * 60);

        // Calculate seconds
        $seconds = round(($decimal - $degrees - $minutes / 60) * 3600, $decimalPlaces); // rounded to the specified decimal places
        
        // Determine sign for North/South or East/West
        $direction = $isNegative && $withSign ? '-' : '';

        // Apply zero padding if needed
        $degreeStr = str_pad($degrees, $zeroPadding, '0', STR_PAD_LEFT);
        $minuteStr = str_pad($minutes, $zeroPadding, '0', STR_PAD_LEFT);
        if($decimalPlaces > 0 && $seconds - floor($seconds) > 0) {
            // If there are decimal places, format seconds with decimal
            $seconds = number_format($seconds, $decimalPlaces, '.', '');
            $secondStr = str_pad($seconds, $zeroPadding + $decimalPlaces + 1, '0', STR_PAD_LEFT); // Adding extra padding for decimal places
        }
        else {
            $secondStr = str_pad($seconds, $zeroPadding, '0', STR_PAD_LEFT); // No decimal places, so just pad normally
        }

        // If the trimDegreeMinute parameter is enabled, remove degree and minute if they are zero
        if ($trimDegreeMinute) {
            $degreeStr = ($degrees == 0) ? '' : $degreeStr;
            $minuteStr = ($minutes == 0) ? '' : $minuteStr;
        }

        // Format the output according to the separator
        return ltrim($direction . $degreeStr . $separator . $minuteStr . $separator . $secondStr, $separator);        
    }
    
    /**
     * Format a given date value into a specified format.
     *
     * This method accepts various types of date input, including:
     * - `DateTime` object: Directly formatted using its `format` method.
     * - `int` (timestamp): Formatted using `date()`.
     * - `float` (timestamp with microseconds): Cast to `int` and formatted using `date()`.
     * - `string` (date representation): Parsed with `strtotime()` and formatted if valid.
     *
     * If the provided string value is `null`, empty, or an invalid date 
     * (such as '0000-00-00' or '0000-00-00 00:00:00'), the function returns `null`.
     *
     * @param string $format The desired date format (e.g., 'Y-m-d H:i:s').
     * @param DateTime|string|int|float $value The date value to format.
     * @return string|null Formatted date string or `null` if the value is invalid.
     */
    public static function dateFormat($format, $value) // NOSONAR
    {
        if ($value instanceof DateTime) {
            return $value->format($format);
        } elseif (is_int($value)) {
            return date($format, $value);
        } elseif (is_float($value)) {
            return date($format, (int) $value);
        } elseif (is_string($value)) {
            if($value != null && $value != '' && $value != '0000-00-00' && $value != '0000-00-00 00:00:00')
            {
                $dateTime = strtotime($value);
                if ($dateTime !== false) {
                    return date($format, $dateTime);
                }
            }
        }
        return null;
    }

    /**
     * Formats a string using a specified format pattern.
     *
     * The format string consists of ordinary characters (except `%`, which 
     * introduces a conversion specification). Each conversion specification 
     * fetches and formats a corresponding parameter. This behavior applies 
     * to both `sprintf` and `printf`.
     *
     * @param string $format The format string containing conversion specifications.
     * @param mixed $value The value to be formatted.
     * @return string The formatted string.
     */
    public static function format($format, $value)
    {
        return sprintf($format, $value);
    }
}