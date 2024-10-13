<?php

namespace MagicObject\Util;

/**
 * Class Dms
 *
 * This class provides methods to convert between Decimal Degrees and Degrees/Minutes/Seconds (DMS) formats.
 *
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
     * @param int $deg Degree component.
     * @param int $min Minute component.
     * @param float $sec Second component.
     * @return self
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
     * @param float $dec Decimal degree value.
     * @return self
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
     * @param bool $trim Flag to indicate whether to trim leading zeros.
     * @param bool $rounded Flag to indicate whether to round the seconds.
     * @return string The DMS representation.
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
     * @return string The decimal degree representation.
     */
    public function printDd()
    {
        return (string) $this->dd;
    }
}
