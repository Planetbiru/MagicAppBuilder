<?php

namespace MagicApp;

use DateTime;
use DateTimeZone;
use MagicObject\MagicObject;
use MagicObject\Util\PicoDateTimeUtil;

/**
 * Class AppEntityDateTime
 *
 * This class is used to handle date and time values related to a specific entity.
 * It takes care of converting date and time values into a formatted string while considering
 * time zones and translation maps for the application. It also offers the ability to get 
 * various date and time properties dynamically from the entity.
 *
 * @package MagicApp
 */
class AppEntityDateTime
{
    /**
     * The entity that this class is associated with.
     *
     * @var MagicObject
     */
    private $entity;

    /**
     * The time zone used for converting and formatting dates and times.
     *
     * @var DateTimeZone
     */
    private $timeZone;

    /**
     * The default format used for formatting date and time.
     *
     * @var string
     */
    private $defaultDateTimeFormat;

    /**
     * The default format used for formatting dates (without time).
     *
     * @var string
     */
    private $defaultDateFormat;

    /**
     * A translation map used to replace certain substrings in the formatted date/time strings.
     *
     * @var array
     */
    private $translationMap;

    /**
     * AppEntityDateTime constructor.
     *
     * This constructor initializes the object with the necessary entity and options such as 
     * default date formats and the translation map for replacing parts of the formatted date/time.
     * It also sets the time zone based on the entity's database settings.
     *
     * @param MagicObject $entity The entity this class is responsible for.
     * @param array|null $translationMap Optional map for translating date/time strings.
     * @param string $defaultDateTimeFormat The default format for date and time.
     * @param string $defaultDateFormat The default format for date (without time).
     */
    public function __construct($entity, $translationMap = null, $defaultDateTimeFormat = 'Y-m-d H:i:s', $defaultDateFormat = 'Y-m-d')
    {
        $this->entity = $entity;
        $this->defaultDateTimeFormat = $defaultDateTimeFormat;
        $this->defaultDateFormat = $defaultDateFormat;
        $this->timeZone = new DateTimeZone($entity->currentDatabase()->getDatabaseTimeZone());
        if (isset($translationMap)) {
            $this->translationMap = $translationMap;
        }
    }

    /**
     * Converts a DateTime object or string into a formatted string.
     *
     * This method takes a DateTime object or a string and converts it into a formatted date/time 
     * string. The resulting string is also adjusted to the object's time zone if necessary.
     * If a translation map is provided, it will replace specific substrings in the formatted string.
     *
     * @param mixed $dateTime The DateTime object or string to be converted.
     * @param string|null $format The format in which the date/time should be returned. If not provided, defaults are used.
     * @return string|null The formatted date/time string.
     */
    private function convertToString($dateTime, $format = null)
    {
        if ($dateTime == null) {
            return null;
        }

        if ($dateTime instanceof DateTime) {
            if (!isset($format)) {
                $format = $this->defaultDateTimeFormat;
            }
            $parsedDateTime = $dateTime;
            // Set the timezone of the DateTime object to the object timezone
            $parsedDateTime->setTimezone($this->timeZone);
        } else {
            if (!isset($format)) {
                if (strlen($dateTime) > 10) {
                    $format = $this->defaultDateTimeFormat;
                } else {
                    $format = $this->defaultDateFormat;
                }
            }
            
            $parsedDateTime = PicoDateTimeUtil::parseDateTime($dateTime);
            // Set the timezone of the parsed DateTime object to the object timezone
            $parsedDateTime->setTimezone($this->timeZone);
        }

        $formatted = $parsedDateTime->format($format);

        if (isset($this->translationMap) && is_array($this->translationMap)) {
            $source = array_keys($this->translationMap);
            $destination = array_values($this->translationMap);
            $formatted = str_replace($source, $destination, $formatted);
        }

        return $formatted;
    }

    /**
     * Magic method to dynamically get properties from the entity.
     *
     * This method intercepts calls to getter methods for the entity's properties and formats
     * the result as a date/time string based on the available formats and time zone settings.
     * It will return the formatted date/time string for the corresponding property or null if 
     * the property is not set.
     *
     * @param string $method The name of the called method.
     * @param array $arguments The arguments passed to the method.
     * @return string|null The formatted date/time string or null.
     */
    public function __call($method, $arguments)
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            $format = isset($arguments[0]) ? $arguments[0] : null;
            return isset($this->entity->{$var}) ? $this->convertToString($this->entity->get($var), $format) : null;
        }
    }

    /**
     * Convert the object to a string representation.
     *
     * This method converts the object into a JSON-encoded string containing
     * key information about the object, including the time zone, date formats,
     * translation map, and the entity name.
     *
     * @return string JSON-encoded string containing object information.
     */
    public function __toString()
    {
        $info = array(
            'timeZone' => $this->timeZone->getName(),
            'defaultDateTimeFormat' => $this->defaultDateTimeFormat,
            'defaultDateFormat' => $this->defaultDateFormat,
            'translationMap' => $this->translationMap,
            'entityName' => get_class($this->entity)
        );

        return json_encode($info);
    }
}
