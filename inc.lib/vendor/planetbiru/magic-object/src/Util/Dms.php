<?php

namespace MagicObject\Util;

/**
 * Class Dms
 *
 * This class provides methods to convert between Decimal Degrees 
 * and Degrees/Minutes/Seconds (DMS) formats. It also allows 
 * printing of these representations in a specified format.
 * 
 * @package MagicObject\Util
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class Dms
{
    /**
     * Degree component.
     *
     * @var int
     */
    private $deg = 0;

    /**
     * Minute component.
     *
     * @var int
     */
    private $min = 0;

    /**
     * Second component.
     *
     * @var float
     */
    private $sec = 0.0;

    /**
     * Decimal degree value.
     *
     * @var float
     */
    private $dd = 0.0;

    /**
     * Converts DMS (Degrees/Minutes/Seconds) to decimal format.
     *
     * This method takes degree, minute, and second components 
     * and converts them to a decimal degree value.
     *
     * **Example:**
     * ```php
     * <?php
     * $dms = new Dms();
     * $dms->dmsToDd(34, 15, 30);
     * echo $dms->printDd(); // Outputs: 34.258333
     * ```
     *
     * @param int $deg Degree component.
     * @param int $min Minute component.
     * @param float $sec Second component.
     * @return self Returns the current instance for method chaining.
     */
    public function dmsToDd($deg, $min, $sec)
    {
        // Convert DMS to decimal format
        $dec = $deg + (($min * 60) + $sec) / 3600;

        $this->deg = $deg;
        $this->min = $min;
        $this->sec = $sec;
        $this->dd = $dec;
        return $this;
    }

    /**
     * Converts decimal format to DMS (Degrees/Minutes/Seconds).
     *
     * This method takes a decimal degree value and converts it to 
     * its DMS representation, storing the results in the instance 
     * variables.
     *
     * **Example:**
     * ```php
     * <?php
     * $dms = new Dms();
     * $dms->ddToDms(34.258333);
     * echo $dms->printDms(); // Outputs: 34:15:30
     * ```
     *
     * @param float $dec Decimal degree value.
     * @return self Returns the current instance for method chaining.
     */
    public function ddToDms($dec)
    {
        // Convert decimal format to DMS
        if (stripos($dec, ".") !== false) {
            $vars = explode(".", $dec);
            $deg = $vars[0];

            $tempma = "0." . $vars[1];
        } else {
            $tempma = 0;
            $deg = $dec;
        }

        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = $tempma - ($min * 60);

        $this->deg = $deg;
        $this->min = $min;
        $this->sec = $sec;
        $this->dd = $dec;
        return $this;
    }

    /**
     * Prints the DMS (Degrees/Minutes/Seconds) representation.
     *
     * This method outputs the DMS format as a string.
     * 
     * @param bool $trim Flag to indicate whether to trim leading zeros.
     * @param bool $rounded Flag to indicate whether to round the seconds.
     * @return string The DMS representation in "deg:min:sec" format.
     */
    public function printDms($trim = false, $rounded = false)
    {
        $sec = $this->sec;
        if ($rounded) {
            $sec = (int) $sec;
        }
        $result = $this->deg . ":" . $this->min . ":" . $sec;
        if ($trim) {
            $result = ltrim($result, '0:');
        }
        return $result;
    }

    /**
     * Prints the decimal degree representation.
     *
     * This method outputs the decimal degree format as a string.
     * 
     * @return string The decimal degree representation.
     */
    public function printDd()
    {
        return (string) $this->dd;
    }
}
