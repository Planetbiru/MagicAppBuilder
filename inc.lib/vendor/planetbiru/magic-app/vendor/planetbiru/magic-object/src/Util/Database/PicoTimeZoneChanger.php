<?php

namespace MagicObject\Util\Database;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;

class PicoTimeZoneChanger
{
    /**
     * Changes the time from the source timezone to the target timezone, 
     * only if the timezones are different.
     *
     * @param string|DateTime|int $datetime The time in string format, DateTime object, or Unix timestamp (integer).
     * @param string|DateTimeZone $from The source timezone (string or DateTimeZone object).
     * @param string|DateTimeZone $to The target timezone (string or DateTimeZone object).
     * @return string|DateTime|int The time adjusted to the target timezone, in the same format as the input.
     */
    public static function changeTimeZone($datetime, $from, $to) // NOSONAR
    {
        // If $from is a string or offset, convert it to DateTimeZone object
        if (is_string($from) || $from instanceof DateTimeZone === false) {
            $from = new DateTimeZone($from);
        }

        // If $to is a string or offset, convert it to DateTimeZone object
        if (is_string($to) || $to instanceof DateTimeZone === false) {
            $to = new DateTimeZone($to);
        }

        // Check if the source and target timezones are the same
        if ($from->getName() === $to->getName()) {
            // If the timezones are the same, return the original value without conversion
            return $datetime;
        }

        // If $datetime is a string, convert it to a DateTime object
        if (is_string($datetime)) {
            // Create DateTime object with the source timezone
            $datetimeObj = new DateTime($datetime, $from);
            // Change the timezone of the DateTime object to the target timezone
            $datetimeObj->setTimezone($to);
            // Return the result as a string
            return $datetimeObj->format('Y-m-d H:i:s');
        }
        // If $datetime is a DateTime object, adjust the timezone
        elseif ($datetime instanceof DateTime) {
            // Change the timezone of the DateTime object to the target timezone
            $datetime->setTimezone($to);
            // Return the DateTime object
            return $datetime;
        }
        // If $datetime is a Unix timestamp (integer), convert it to DateTime
        elseif (is_int($datetime)) {
            // Create DateTime object from timestamp, using the source timezone
            $datetimeObj = new DateTime('@' . $datetime, $from); // '@' indicates Unix timestamp
            // Change the timezone of the DateTime object to the target timezone
            $datetimeObj->setTimezone($to);
            // Return the timestamp after conversion back to the target timezone
            return $datetimeObj->getTimestamp(); // Return as Unix timestamp
        } else {
            throw new InvalidArgumentException('The $datetime parameter must be a string, DateTime object, or Unix timestamp (integer)');
        }
    }

    
    /**
     * Adjusts the given time according to the timezone settings from the database configuration
     * before saving to the database.
     * The time is adjusted only if the database driver is either "sqlsrv" or "sqlite", and the source 
     * and target timezones are different.
     *
     * This method is useful for ensuring that the time is correctly adjusted before being saved 
     * to the database, based on the application's time zone and the database's time zone settings.
     *
     * @param string|DateTime|int $datetime The time to be adjusted. Can be a string, DateTime object, or Unix timestamp (integer).
     * @param PicoDatabase $database The database object containing the database configuration with timezones.
     * @return string|DateTime|int The time adjusted to the target timezone, or the original datetime if no conversion is needed.
     */
    public static function changeTimeZoneBeforeSave($datetime, $database)
    {
        // Retrieve the database configuration credentials
        $databaseConfig = $database->getDatabaseCredentials();
        
        // Get the database driver (e.g., sqlsrv, mysql, sqlite, etc.)
        $driver = $databaseConfig->getDriver();
        
        // Get the source timezone (from application settings)
        $timeZoneFrom = $databaseConfig->getTimeZone();
        
        // Get the target timezone (from database settings)
        $timeZoneTo = $databaseConfig->getTimeZoneSystem();

        // Check if the driver is SQL Server (sqlsrv) or SQLite (sqlite) and if the timezones are different
        if ((stripos($driver, PicoDatabaseType::DATABASE_TYPE_SQLSERVER) !== false || stripos($driver, PicoDatabaseType::DATABASE_TYPE_SQLITE) !== false) 
            && isset($timeZoneTo) && !empty($timeZoneTo) 
            && $timeZoneTo != $timeZoneFrom) {
            // If the conditions are met, change the timezone of the datetime value
            return self::changeTimeZone($datetime, $timeZoneFrom, $timeZoneTo);
        } else {
            // If no conversion is needed, return the original datetime value
            return $datetime;
        }
    }

    /**
     * Reverses the time zone conversion after reading data from the database.
     * This function is useful for converting the time from the target timezone back to the original source timezone 
     * after the data has been fetched from the database.
     * The time is adjusted only if the database driver is either "sqlsrv" or "sqlite", and the source 
     * and target timezones are different.
     *
     * This method ensures that the time is returned to the correct timezone as per the application's settings,
     * after being retrieved from the database.
     *
     * @param string|DateTime|int $datetime The time to be adjusted. Can be a string, DateTime object, or Unix timestamp (integer).
     * @param PicoDatabase $database The database object containing the database configuration with timezones.
     * @return string|DateTime|int The time adjusted to the source timezone, or the original datetime if no conversion is needed.
     */
    public static function changeTimeZoneAfterRead($datetime, $database)
    {
        // Retrieve the database configuration credentials
        $databaseConfig = $database->getDatabaseCredentials();
        
        // Get the database driver (e.g., sqlsrv, mysql, sqlite, etc.)
        $driver = $databaseConfig->getDriver();
        
        // Get the source timezone (from database settings)
        $timeZoneFrom = $databaseConfig->getTimeZoneSystem();
        
        // Get the target timezone (from application settings)
        $timeZoneTo = $databaseConfig->getTimeZone();

        // Check if the driver is SQL Server (sqlsrv) or SQLite (sqlite) and if the timezones are different
        if ((stripos($driver, PicoDatabaseType::DATABASE_TYPE_SQLSERVER) !== false || stripos($driver, PicoDatabaseType::DATABASE_TYPE_SQLITE) !== false) 
            && isset($timeZoneTo) && !empty($timeZoneTo) 
            && $timeZoneTo != $timeZoneFrom) {
            // If the conditions are met, reverse the timezone conversion of the datetime value
            return self::changeTimeZone($datetime, $timeZoneFrom, $timeZoneTo);
        } else {
            // If no conversion is needed, return the original datetime value
            return $datetime;
        }
    }


}
