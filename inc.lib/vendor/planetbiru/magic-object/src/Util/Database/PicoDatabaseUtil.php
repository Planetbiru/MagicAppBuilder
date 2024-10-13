<?php

namespace MagicObject\Util\Database;

use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;

class PicoDatabaseUtil
{
    const INLINE_TRIM = " \r\n\t ";

    private function __construct()
    {
        // prevent object construction from outside the class
    }

    /**
     * Get specification from parameters
     * @param array $params Parameters
     * @return PicoSpecification|null
     */
    public static function specificationFromParams($params)
    {
        if(self::isArray($params))
        {
            foreach($params as $param)
            {
                if($param instanceof PicoSpecification)
                {
                    return $param;
                }
            }
        }
        return null;
    }

    /**
     * Get pageable from parameters.
     *
     * @param array $params Parameters
     * @return PicoPageable|null
     */
    public static function pageableFromParams($params)
    {
        if(self::isArray($params))
        {
            foreach($params as $param)
            {
                if($param instanceof PicoPageable)
                {
                    return $param;
                }
            }
        }
        return null;
    }

    /**
     * Get sortable from parameters.
     *
     * @param array $params Parameters
     * @return PicoSortable|null
     */
    public static function sortableFromParams($params)
    {
        if(self::isArray($params))
        {
            foreach($params as $param)
            {
                if($param instanceof PicoSortable)
                {
                    return $param;
                }
            }
        }
        return null;
    }

    /**
     * Get values from parameters until a PicoPageable instance is found.
     *
     * @param array $params Parameters
     * @return array
     */
    public static function valuesFromParams($params)
    {
        $ret = array();
        if(self::isArray($params))
        {
            foreach($params as $param)
            {
                if($param instanceof PicoPageable)
                {
                    break;
                }
                $ret[] = $param;
            }
        }
        return $ret;
    }

    /**
     * Fix value based on its expected type.
     *
     * @param string $value Value
     * @param string $type Data type
     * @return mixed
     */
    public static function fixValue($value, $type) // NOSONAR
    {
        if(strtolower($value) === 'true')
        {
            return true;
        }
        else if(strtolower($value) === 'false')
        {
            return false;
        }
        else if(strtolower($value) === 'null')
        {
            return false;
        }
        else if(is_numeric($value) && strtolower($type) != 'string')
        {
            return $value + 0;
        }
        else
        {
            return $value;
        }
    }

    /**
     * Check if value is null.
     *
     * @param mixed $value Value
     * @param bool $importFromString Flag indicating if input is from string
     * @return bool
     */
    public static function isNull($value, $importFromString)
    {
        return $value === null || $value == 'null' && $importFromString;
    }

    /**
     * Check if value is numeric.
     *
     * @param mixed $value Value
     * @param bool $importFromString Flag indicating if input is from string
     * @return bool
     */
    public static function isNumeric($value, $importFromString)
    {
        return is_string($value) && is_numeric($value) && $importFromString;
    }

    /**
     * Escape value for SQL.
     *
     * @param mixed $value Value
     * @param bool $importFromString Flag indicating if input is from string
     * @return string
     */
	public static function escapeValue($value, $importFromString = false)
	{
		if(self::isNull($value, $importFromString))
		{
			// null
			$ret = 'NULL';
		}
        else if(self::isNumeric($value, $importFromString))
        {
            $ret = $value."";
        }
		else if(is_string($value))
		{
			// escape the value
			$ret = "'".self::escapeSQL($value)."'";
		}
		else if(is_bool($value))
		{
			// true or false
			$ret = $value?'true':'false';
		}
		else if(is_numeric($value))
		{
			// convert number to string
			$ret = $value."";
		}
		else if(is_array($value))
		{
			// encode to JSON and escapethe value
			$ret = "(".self::toList($value).")";
		}
        else if(is_object($value))
		{
			// encode to JSON and escapethe value
			$ret = "'".self::escapeSQL(json_encode($value))."'";
		}
		else
		{
			// force convert to string and escapethe value
			$ret = "'".self::escapeSQL($value)."'";
		}
		return $ret;
	}

    /**
     * Convert array to list
     *
     * @param array $array Array
     * @return string
     */
    public static function toList($array)
    {
        foreach($array as $key=>$value)
        {
            $type = gettype($value);
            $array[$key] = self::fixValue($value, $type);
        }
        return implode(", ", $array);
    }

    /**
     * Escape SQL value.
     *
     * @param string $value Value
     * @return string
     */
    public static function escapeSQL($value)
    {
        return addslashes($value);
    }

    /**
     * Trim WHERE clause.
     *
     * @param string $where Raw WHERE clause
     * @return string
     */
    public static function trimWhere($where)
    {
        $where = trim($where, self::INLINE_TRIM);
        if($where != "(1=1)")
        {
            if(stripos($where, "(1=1)") === 0)
            {
                $where = trim(substr($where, 5), self::INLINE_TRIM);
            }
            if(stripos($where, "and ") === 0)
            {
                $where = substr($where, 4);
            }
            if(stripos($where, "or ") === 0)
            {
                $where = substr($where, 3);
            }
        }
        return $where;
    }

    /**
     * Generate UUID
     *
     * @return string
     */
    public static function uuid()
    {
        $uuid = uniqid();
		if ((strlen($uuid) % 2) == 1) {
			$uuid = '0' . $uuid;
		}
		$random = sprintf('%06x', mt_rand(0, 16777215));
		return sprintf('%s%s', $uuid, $random);
    }

    /**
     * Split SQL into queries.
     *
     * @param string $sqlText Raw SQL
     * @return array
     */
    public function splitSql($sqlText) //NOSONAR
    {
        $sqlText = str_replace("\n", "\r\n", $sqlText);
        $sqlText = str_replace("\r\r\n", "\r\n", $sqlText);
        $arr = explode("\r\n", $sqlText);
        $arr2 = array();
        foreach($arr as $key=>$val)
        {
            $arr[$key] = ltrim($val);
            if(stripos($arr[$key], "-- ") !== 0 && $arr[$key] != "--" && $arr[$key] != "")
            {
                $arr2[] = $arr[$key];
            }
        }
        $arr = $arr2;
        unset($arr2);

        $append = 0;
        $skip = 0;
        $start = 1;
        $nquery = -1;
        $delimiter = ";";
        $queryArray = array();
        $delimiterArray = array();

        foreach($arr as $line=>$text)
        {
            if($text == "" && $append == 1)
            {
                $queryArray[$nquery] .= "\r\n";
            }
            if($append == 0)
            {
                if(stripos(ltrim($text, " \t "), "--") === 0)
                {
                    $skip = 1;
                    $nquery++;
                    $start = 1;
                    $append = 0;
                }
                else
                {
                    $skip = 0;
                }
            }
            if($skip == 0)
            {
                if($start == 1)
                {
                    $nquery++;
                    $queryArray[$nquery] = "";
                    $delimiterArray[$nquery] = $delimiter;
                    $start = 0;
                }
                $queryArray[$nquery] .= $text."\r\n";
                $delimiterArray[$nquery] = $delimiter;
                $text = ltrim($text, " \t ");
                $start = strlen($text)-strlen($delimiter)-1;
                if(stripos(substr($text, $start), $delimiter) !== false || $text == $delimiter)
                {
                    $nquery++;
                    $start = 1;
                    $append = 0;
                }
                else
                {
                    $start = 0;
                    $append = 1;
                }
                $delimiterArray[$nquery] = $delimiter;
                if(stripos($text, "delimiter ") !== false)
                {
                    $text = trim(preg_replace("/\s+/"," ",$text));
                    $arr2 = explode(" ", $text);
                    $delimiter = $arr2[1];
                    $nquery++;
                    $delimiterArray[$nquery] = $delimiter;
                    $start = 1;
                    $append = 0;
                }
            }
        }
        $result = array();
        foreach($queryArray as $line=>$sql)
        {
            $delimiter = $delimiterArray[$line];
            if(stripos($sql, "delimiter ") !== 0)
            {
                $sql = rtrim($sql, self::INLINE_TRIM);
                $sql = substr($sql, 0, strlen($sql)-strlen($delimiter));
                $result[] = array("query"=> $sql, "delimiter"=>$delimiter);
            }
        }
        return $result;
    }

    /**
     * Check if parameter is an array.
     *
     * @param mixed $params Parameters
     * @return bool
     */
    public static function isArray($params)
    {
        return isset($params) && is_array($params);
    }

    /**
     * Find an instance of a specified class in an array.
     *
     * @param array $params Parameters
     * @param string $className Class name
     * @return object|null
     */
    public static function findInstanceInArray($params, $className)
    {
        foreach ($params as $param) {
            if ($param instanceof $className) {
                return $param;
            }
        }
        return null;
    }
}