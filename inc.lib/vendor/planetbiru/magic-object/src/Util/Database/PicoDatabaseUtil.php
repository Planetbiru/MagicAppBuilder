<?php

namespace MagicObject\Util\Database;

use DateTime;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use PDOStatement;

/**
 * Class PicoDatabaseUtil
 *
 * A utility class for handling database operations and specifications within the framework.
 * This class provides methods to retrieve specifications, handle SQL query formatting,
 * and manage data types, ensuring safe and efficient interactions with the database.
 *
 * @author Kamshory
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseUtil
{
    const INLINE_TRIM = " \r\n\t ";

    private function __construct()
    {
        // prevent object construction from outside the class
    }

    /**
     * Retrieve a PicoSpecification instance from the given parameters.
     *
     * This method iterates through the provided parameters to find and return
     * the first instance of PicoSpecification. If none is found, null is returned.
     *
     * @param array $params An array of parameters to search.
     * @return PicoSpecification|null Returns the PicoSpecification instance or null if not found.
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
     * Retrieve a PicoPageable instance from the given parameters.
     *
     * This method searches through the parameters for an instance of PicoPageable.
     *
     * @param array $params An array of parameters to search.
     * @return PicoPageable|null Returns the PicoPageable instance or null if not found.
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
     * Retrieve a PicoSortable instance from the given parameters.
     *
     * This method looks for the first instance of PicoSortable in the parameters.
     *
     * @param array $params An array of parameters to search.
     * @return PicoSortable|null Returns the PicoSortable instance or null if not found.
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
     * Retrieve values from the parameters until a PicoPageable instance is found.
     *
     * This method collects and returns all parameters up to the first
     * PicoPageable instance, effectively filtering the parameters.
     *
     * @param array $params An array of parameters to process.
     * @return array An array of values up to the first PicoPageable instance.
     */
    public static function valuesFromParams($params)
    {
        $ret = [];
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
     * Fix a value based on its expected type.
     *
     * This method normalizes various representations of values (e.g., "true", "false", "null")
     * into their appropriate PHP types based on the expected type.
     *
     * @param string $value The value to fix.
     * @param string $type The expected data type.
     * @return mixed The fixed value, converted to the appropriate type.
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
     * Check if a value is null.
     *
     * This method checks if the provided value is null or represents "null" as a string.
     *
     * @param mixed $value The value to check.
     * @param bool $importFromString Indicates if the input is from a string.
     * @return bool Returns true if the value is null or the string "null".
     */
    public static function isNull($value, $importFromString)
    {
        return $value === null || $value == 'null' && $importFromString;
    }

    /**
     * Check if a value is numeric.
     *
     * This method checks if the value is a numeric string, specifically when
     * the input is treated as a string.
     *
     * @param mixed $value The value to check.
     * @param bool $importFromString Indicates if the input is from a string.
     * @return bool Returns true if the value is a numeric string and input is from a string.
     */
    public static function isNumeric($value, $importFromString)
    {
        return is_string($value) && is_numeric($value) && $importFromString;
    }

    /**
     * Escape a value for SQL.
     *
     * This method prepares a value for safe SQL insertion by escaping it,
     * handling various types including nulls, booleans, strings, and arrays.
     *
     * @param mixed $value The value to escape.
     * @param bool $importFromString Indicates if the input is from a string.
     * @return string The escaped value suitable for SQL.
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
			$ret = self::toList($value, true);
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
     * Convert an array to a list format for SQL queries.
     *
     * This method converts an array into a comma-separated string representation,
     * optionally enclosing the result in parentheses.
     *
     * @param array $array The array to convert.
     * @param bool $bracket Indicates if the result should be enclosed in parentheses.
     * @param bool $escape Indicates if the values should be escaped for SQL.
     * @return string The list representation of the array.
     */
    public static function toList($array, $bracket = false, $escape = false)
    {
        foreach($array as $key=>$value)
        {
            $type = gettype($value);
            if($value instanceof DateTime)
            {
                $array[$key] = "'".$value->format('Y-m-d H:i:s')."'";
            }
            else if(is_string($value))
            {
                if($escape)
                {
                    $array[$key] = "'".self::escapeSQL(self::fixValue($value, $type))."'";
                }
                else
                {
                    $array[$key] = "'".self::fixValue($value, $type)."'";
                }
                
            }
            else
            {
                $array[$key] = self::fixValue($value, $type);
            }
        }
        if($bracket)
        {
            return "(".implode(", ", $array).")";
        }
        return implode(", ", $array);
    }

    /**
     * Escape a SQL value to prevent SQL injection.
     *
     * This method escapes special characters in a string to ensure safe SQL execution.
     *
     * @param string $value The value to escape.
     * @return string The escaped value.
     */
    public static function escapeSQL($value)
    {
        return addslashes($value);
    }

   /**
     * Trim a WHERE clause by removing unnecessary characters.
     *
     * This method cleans up a raw WHERE clause by trimming whitespace
     * and removing common redundant patterns.
     *
     * @param string $where The raw WHERE clause to be trimmed.
     * @return string The cleaned WHERE clause.
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
     * Generate a UUID.
     *
     * @return string A generated UUID.
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
     * Split a SQL string into separate queries.
     *
     * This method takes a raw SQL string and splits it into individual
     * queries based on delimiters, handling comments and whitespace.
     *
     * @param string $sqlText The raw SQL string containing one or more queries.
     * @return array An array of queries with their respective delimiters.
     */
    public function splitSql($sqlText)
    {
        // Normalize newlines and clean up any redundant line breaks
        $sqlText = str_replace("\r\r\n", "\r\n", str_replace("\n", "\r\n", $sqlText));
        
        // Split the SQL text by newlines
        $lines = explode("\r\n", $sqlText);
        
        // Clean up lines, remove comments, and empty lines
        $cleanedLines = array_filter(array_map('ltrim', $lines), function ($line) {
            return !(empty($line) || stripos($line, "-- ") === 0 || $line == "--");
        });
        
        // Initialize state variables
        $queries = [];
        $currentQuery = '';
        $isAppending = false;
        $delimiter = ';';
        $skip = false;

        foreach ($cleanedLines as $line) {
            // Skip lines if needed
            if ($skip) {
                $skip = false;
                continue;
            }

            // Handle "delimiter" statements
            if (stripos(trim($line), 'delimiter ') === 0) {
                $parts = explode(' ', trim($line));
                $delimiter = $parts[1] ?? ';';
                continue;
            }

            // Start a new query if necessary
            if (!$isAppending) {
                if (!empty($currentQuery)) {
                    // Store the previous query and reset for the next one
                    $queries[] = ['query' => rtrim($currentQuery, self::INLINE_TRIM), 'delimiter' => $delimiter];
                }
                $currentQuery = '';
                $isAppending = true;
            }

            // Append current line to the current query
            $currentQuery .= $line . "\r\n";

            // Check if the query ends with the delimiter
            if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $isAppending = false; // End of query, so we stop appending
            }
        }

        // Add the last query if any
        if (!empty($currentQuery)) {
            $queries[] = ['query' => rtrim($currentQuery, self::INLINE_TRIM), 'delimiter' => $delimiter];
        }

        return $queries;
    }

    /**
     * Check if a parameter is an array.
     *
     * This method checks if the provided parameter is an array.
     *
     * @param mixed $params The parameter to check.
     * @return bool Returns true if the parameter is an array, false otherwise.
     */
    public static function isArray($params)
    {
        return isset($params) && is_array($params);
    }

    /**
     * Find an instance of a specified class in an array of parameters.
     *
     * This method iterates through the parameters to find an instance
     * of the specified class, returning it if found, or null otherwise.
     *
     * @param array $params An array of parameters to search.
     * @param string $className The name of the class to find.
     * @return object|null Returns the instance of the specified class or null if not found.
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
    
    /**
     * Retrieves the final query to be executed by the PDOStatement.
     *
     * This function replaces the placeholders in the query with the bound parameter values.
     *
     * @param PDOStatement $stmt The PDOStatement containing the original query.
     * @param array $params An array of parameter values to replace the placeholders.
     * @return string The final query with parameter values substituted.
     */
    public static function getFinalQuery($stmt, $params) {
        $query = $stmt->queryString; // Get the original query
        foreach ($params as $key => $value) {
            // Replace placeholder with parameter value
            $query = str_replace(":$key", self::escapeValue($value), $query);
        }
        return $query;
    }

}