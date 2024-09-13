<?php

namespace MagicObject\Util;

/**
 * Decimal Degree Minute Second cobersion
 * @link https://github.com/Planetbiru/MagicObject
 */
class Dms
{
    /**
     * Degree
     *
     * @var integer
     */
    private $deg = 0;

    /**
     * Minute
     *
     * @var integer
     */
    private $min = 0;

    /**
     * Second
     *
     * @var float
     */
    private $sec = 0.0;

    /**
     * Decimal
     *
     * @var float
     */
    private $dd = 0.0;

    /**
     * Converting DMS ( Degrees / minutes / seconds ) to decimal format
     *
     * @param integer $deg Degree
     * @param integer $min Minute
     * @param float $sec Second
     * @return self
     */
    public function dmsToDd($deg, $min, $sec)
    {
        // Converting DMS ( Degrees / minutes / seconds ) to decimal format
        $dec = $deg+((($min*60)+$sec)/3600);

        $this->deg = $deg;
        $this->min = $min;
        $this->sec = $sec;
        $this->dd = $dec;
        return $this;
    }

    /**
     * Converts decimal format to DMS ( Degrees / minutes / seconds )
     *
     * @param float $dec Decimal
     * @return self
     */
    public function ddToDms($dec)
    {
        // Converts decimal format to DMS ( Degrees / minutes / seconds )
        if(stripos($dec, ".") !== false)
        {
            $vars = explode(".",$dec);
            $deg = $vars[0];

            $tempma = "0.".$vars[1];
        }
        else
        {
            $tempma = 0;
            $deg = $dec;
        }

        $tempma = $tempma * 3600;
        $min = floor($tempma / 60);
        $sec = $tempma - ($min*60);

        $this->deg = $deg;
        $this->min = $min;
        $this->sec = $sec;
        $this->dd = $dec;
        return $this;
    }

    /**
     * Print Dms
     *
     * @param boolean $trim Flag to trim 0
     * @param boolean $rounded Flag to round value
     * @return string
     */
    public function printDms($trim = false, $rounded = false)
    {
        $sec = $this->sec;
        if($rounded)
        {
            $sec = (int) $sec;
        }
        $result = $this->deg.":".$this->min.":".$sec;
        if($trim)
        {
            $result = ltrim($result, '0:');
        }
        return $result;
    }

    /**
     * Print Dms
     *
     * @return string
     */
    public function printDd()
    {
        return $this->dd."";
    }
}