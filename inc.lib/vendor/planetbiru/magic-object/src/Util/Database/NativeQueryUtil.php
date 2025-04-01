<?php

namespace MagicObject\Util\Database;

use DateTime;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseQueryTemplate;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSqlJson;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Exceptions\InvalidReturnTypeException;
use MagicObject\MagicObject;
use PDO;
use PDOStatement;
use stdClass;

/**
 * Utility class for working with SQL queries in the context of MagicObject's database operations.
 *
 * The `NativeQueryUtil` class provides methods for handling SQL queries with dynamic parameters,
 * pagination, and sorting. It includes functionality for generating modified query strings with 
 * array-type parameters, handling return types (e.g., `PDOStatement`, objects, arrays), 
 * extracting return types and queries from docblocks, and mapping PHP values to PDO parameter types.
 * Additionally, it supports debugging by logging generated SQL queries.
 *
 * Key responsibilities include:
 * - Extracting SQL queries and return types from docblocks.
 * - Converting PHP types into appropriate PDO parameter types.
 * - Modifying query strings to handle array parameters and apply pagination/sorting.
 * - Processing data returned from PDO statements and converting it to the expected return types.
 * - Debugging SQL queries by sending them to a logger function.
 */
class NativeQueryUtil
{
    /**
     * Replaces array parameters and applies pagination and sorting to the query string.
     *
     * This method processes the caller's parameters, replacing array-type parameters with string equivalents. 
     * It also adds pagination and sorting clauses if `PicoPageable` or `PicoSortable` objects are detected.
     *
     * @param string $databaseType The database type.
     * @param string $queryString The SQL query string with placeholders.
     * @param ReflectionParameter[] $callerParams The parameters of the calling method (reflection objects).
     * @param array $callerParamValues The actual values of the parameters.
     * @return string The modified query string with array parameters replaced and pagination/sorting applied.
     * @throws InvalidArgumentException If parameters are in an unexpected format.
     */
    public function applyQueryParameters($databaseType, $queryString, $callerParams, $callerParamValues)
    {
        $pageable = null;
        $sortable = null;
        
        // Replace array
        foreach ($callerParamValues as $index => $paramValue) {
            if($paramValue instanceof PicoDatabaseQueryTemplate)
            {
                // Do nothing
            }
            else if($paramValue instanceof PicoPageable)
            {
                $pageable = $paramValue;
            }
            else if($paramValue instanceof PicoSortable)
            {
                $sortable = $paramValue;
            }
            else if (isset($callerParams[$index])) {
                // Format parameter name according to the query
                $paramName = $callerParams[$index]->getName();
                if(PicoDatabaseUtil::isArray($paramValue))
                {
                    $queryString = str_replace(":".$paramName, PicoDatabaseUtil::toList($paramValue, true, true), $queryString);
                }
            }
        }

        // Apply pagination and sorting if needed
        if(isset($pageable) || isset($sortable))
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($databaseType);
            $queryString = $queryBuilder->addPaginationAndSorting($queryString, $pageable, $sortable);
        }
        
        return $queryString;
    }

    /**
     * Processes and returns data based on the specified return type.
     *
     * This method handles various return types like `void`, `PDOStatement`, `int`, `object`, `array`, 
     * `string`, or any specific class name (including array-type hinting).
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @param string $returnType The return type from the caller's docblock annotation.
     * @return mixed The processed return data (e.g., value, object, array, PDOStatement, or JSON string).
     * @throws InvalidReturnTypeException If the return type is invalid or unrecognized.
     */
    public function handleReturnObject($stmt, $returnType) // NOSONAR
    {
        // Handle basic return types
        switch ($returnType) {
            case 'void':
                return null;

            case 'PDOStatement':
                return $stmt;

            case 'int':
            case 'integer':
                return $stmt->rowCount();

            case 'object':
            case 'stdClass':
                return $stmt->fetch(PDO::FETCH_OBJ);

            case 'array':
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            case 'string':
                return json_encode($stmt->fetchAll(PDO::FETCH_OBJ));
            default:
                break;
        }

        // Handle array-type hinting (supports both classic and PHP 7+ styles)
        if (stripos($returnType, '<') !== false && preg_match('/^(array<([\w\\\\]+)>|([\w\\\\]+)\[\])$/i', $returnType, $matches)) 
        {
            $className = isset($matches[2]) ? $matches[2] : $matches[3];
            return $this->handleArrayReturnType($stmt, $className);
        }
        else if(stripos($returnType, '[') !== false)
        {
            return $this->handleArrayReturnType($stmt, $returnType);
        }
        // Handle single class-type return (e.g., MagicObject, MyClass)
        return $this->handleSingleClassReturnType($stmt, $returnType);
    }

    /**
     * Handles return types with array hinting (e.g., `MagicObject[]`, `MyClass[]`).
     *
     * Supports both classic (`stdClass[]`) and PHP 7.0+ (`array<stdClass>`) annotation styles.
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @param string $returnType The array-type return type (e.g., `MagicObject[]`, `array<MyClass>`).
     * @return stdClass[]|array<stdClass> The processed result as an array of objects.
     * @throws InvalidReturnTypeException If the return type is invalid or unrecognized.
     */
    private function handleArrayReturnType($stmt, $returnType)
    {
        $className = trim(str_replace(array('[', ']', 'array<', '>'), '', $returnType));

        if ($className === 'stdClass') {
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } elseif ($className === 'MagicObject') {
            return $this->mapRowsToMagicObject($stmt);
        } elseif (class_exists($className)) {
            return $this->mapRowsToClass($stmt, $className);
        } else {
            throw new InvalidReturnTypeException("Invalid return type for array of $className");
        }
    }

    /**
     * Handles return types that are a single object (e.g., `MagicObject`, `MyClass`).
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @param string $returnType The single-class return type (e.g., `MagicObject`).
     * @return mixed The processed result as a single object.
     * @throws InvalidReturnTypeException If the return type is invalid or unrecognized.
     */
    private function handleSingleClassReturnType($stmt, $returnType)
    {
        $className = trim($returnType);

        // Check if the return type is 'MagicObject'
        if ($className === 'MagicObject') {
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            return new MagicObject($row);
        }

        // Check if the class exists
        if (class_exists($className)) {
            $obj = new $className();
            
            // If the class is an instance of MagicObject, load data
            if ($obj instanceof MagicObject) {
                $row = $stmt->fetch(PDO::FETCH_OBJ);
                return $obj->loadData($row);
            }

            // Return the class instance (assuming it's valid)
            return $obj;
        }

        // Throw an exception if the class does not exist or the return type is invalid
        throw new InvalidReturnTypeException("Invalid return type for $className");
    }

    /**
     * Maps rows from the PDO statement to an array of MagicObject instances.
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @return MagicObject[] An array of MagicObject instances.
     */
    private function mapRowsToMagicObject($stmt)
    {
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $objects = array();

        foreach ($result as $row) {
            $objects[] = new MagicObject($row);
        }

        return $objects;
    }

    /**
     * Maps rows from the PDO statement to an array of instances of a specified class.
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @param string $className The class name to map rows to.
     * @return object[] An array of instances of the specified class.
     * @throws InvalidReturnTypeException If the class does not exist.
     */
    private function mapRowsToClass($stmt, $className)
    {
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $objects = array();

        foreach ($result as $row) {
            $objects[] = new $className($row);
        }

        return $objects;
    }
    
    /**
     * Extracts the return type from the docblock of the caller function.
     *
     * This method processes the `@return` annotation, handling `self` and array types, 
     * and returns the appropriate type, such as the caller class name or `void`.
     *
     * @param string $docComment The docblock of the caller function.
     * @param string $callerClassName The name of the class where the caller function is defined.
     * @return string The processed return type (class name, `self`, or `void`).
     */
    public function extractReturnType($docComment, $callerClassName)
    {
        // Get return type from the caller function
        preg_match('/@return\s+([^\s]+)/', $docComment, $matches);
        $returnType = $matches ? $matches[1] : 'void';
        
        // Trim return type
        $returnType = trim($returnType);
        
        // Change self to callerClassName
        if ($returnType == "self[]") {
            $returnType = $callerClassName . "[]";
        } else if ($returnType == "self") {
            $returnType = $callerClassName;
        }
        
        return $returnType;
    }

    /**
     * Extracts the SQL query string from the docblock or the caller's parameters.
     *
     * This method first checks the caller's parameters for a `PicoDatabaseQueryTemplate` object
     * to obtain the query string. If no such object is found, it attempts to extract the query 
     * from the `@query` annotation in the docblock using different parsing methods.
     *
     * The extraction process follows these steps:
     * 1. Attempts to retrieve a single-line query from the `@query` annotation.
     * 2. If unsuccessful, attempts to extract a multi-line query.
     * 3. If still unsuccessful, tries parsing a multi-line query with additional attributes.
     * 4. Finally, trims and formats the extracted query string.
     *
     * If no query string is found, an `InvalidQueryInputException` is thrown.
     *
     * @param string $docComment The docblock of the caller function.
     * @param array $callerParamValues The parameters passed to the caller function.
     * @return string The extracted SQL query string.
     * @throws InvalidQueryInputException If no query string is found.
     */
    public function extractQueryString($docComment, $callerParamValues)
    {
        $queryString = $this->getQueryStringFromCallerParams($callerParamValues);
        
        if (empty($queryString)) {
            // Attempts to retrieve a single-line query from the `@query` annotation.
            $queryString = $this->parseSingleLine($docComment);
        }
        
        if (empty($queryString)) {
            // If unsuccessful, attempts to extract a multi-line query.
            $queryString = $this->parseMultiline($docComment);
        }

        if (empty($queryString)) {
            // If still unsuccessful, tries parsing a multi-line query with additional attributes.
            $queryString = $this->parseMultilineWithAttributes($docComment);
        }

        // Finally, trims and formats the extracted query string.
        $queryString = $this->trimQueryString($docComment, $queryString);

        if (empty($queryString)) {
            throw new InvalidQueryInputException("No query found.\r\n" . $docComment);
        }
        return $queryString;
    }

    /**
     * Parses and extracts an SQL query from the `@query` annotation in the docblock.
     *
     * This method looks for a query pattern within the annotation and extracts it.
     *
     * @param string $docComment The docblock containing the `@query` annotation.
     * @return string The extracted SQL query or an empty string if not found.
     */
    private function parseSingleLine($docComment)
    {
        preg_match('/@query\s*\("([^"]+)"\)/', $docComment, $matches);
        return $matches ? $matches[1] : '';
    }

    /**
     * An alternative method to parse and extract an SQL query from the `@query` annotation.
     *
     * This approach supports multi-line queries inside the annotation.
     *
     * @param string $docComment The docblock containing the `@query` annotation.
     * @return string The extracted SQL query or an empty string if not found.
     */
    private function parseMultiline($docComment)
    {
        preg_match('/@query\s*\(\s*"(.*?)"\s*\)/s', $docComment, $matches);
        return $matches ? $matches[1] : '';
    }

    /**
     * A third approach to extract an SQL query from the `@query` annotation.
     *
     * This method handles different variations of query formatting.
     *
     * @param string $docComment The docblock containing the `@query` annotation.
     * @return string The extracted SQL query or an empty string if not found.
     */
    private function parseMultilineWithAttributes($docComment)
    {
        preg_match('/@query\s*\(\s*"([\s\S]+?)"\s*(?:,|$)/', $docComment, $matches);
        return $matches ? $matches[1] : '';
    }

    /**
     * Cleans and trims the extracted SQL query string.
     *
     * If the `trim` parameter is set in the `@query` annotation, leading `*` 
     * and spaces are removed from each line of the query.
     *
     * @param string $docComment The docblock containing the `@query` annotation.
     * @param string $queryString The extracted SQL query string.
     * @return string The cleaned and properly formatted SQL query.
     */
    private function trimQueryString($docComment, $queryString)
    {
        $params = [];

        preg_match_all('/,\s*([\w-]+)\s*=\s*([\w-]+)/', $docComment, $paramMatches, PREG_SET_ORDER);
        if (isset($paramMatches)) {
            foreach ($paramMatches as $match) {
                $params[$match[1]] = $match[2];
            }
        }

        if (!isset($params['trim']) || strtolower($params['trim']) !== 'false') {
            $lines = explode("\n", $queryString);
            foreach ($lines as $idx => $ln) {
                // Fix regex to remove spaces before * and one space after *
                $lines[$idx] = preg_replace('/^\s*\*\s?/', '', $ln);
            }
            $queryString = implode("\n", $lines);
        }

        return $queryString;
    }

    /**
     * Extracts the SQL query string from the caller's parameters.
     *
     * This method searches the caller's parameters for a `PicoDatabaseQueryTemplate` object.
     * If found, it returns the query string representation of the object.
     *
     * @param array $callerParamValues The parameters passed to the caller function.
     * @return string The SQL query string or an empty string if no valid object is found.
     */
    private function getQueryStringFromCallerParams($callerParamValues)
    {
        $queryString = "";
        if (isset($callerParamValues) && is_array($callerParamValues) && !empty($callerParamValues)) {
            foreach ($callerParamValues as $param) {
                if ($param instanceof PicoDatabaseQueryTemplate) {
                    $queryString = (string) $param;
                    break;
                }
            }
        }
        return $queryString;
    }


    /**
     * Maps a value to the corresponding PDO parameter type.
     *
     * This method determines the appropriate PDO parameter type based on the given value's type 
     * (e.g., `null`, `boolean`, `integer`, `string`, `DateTime`, or `PicoSqlJson`).
     *
     * @param mixed $value The value to determine the PDO parameter type for.
     *
     * @return stdClass An object with:
     *                  - `type`: The corresponding PDO parameter type (e.g., `PDO::PARAM_STR`).
     *                  - `value`: The formatted value for the PDO parameter.
     */
    public function mapToPdoParamType($value)
    {
        $type = PDO::PARAM_STR; // Default type is string
        $finalValue = $value; // Initialize final value to the original value
        if ($value instanceof PicoSqlJson) {
            $type = PDO::PARAM_STR; // Treat as string
            $finalValue = $value; // Keep the PicoSqlJson object itself
        }
        else if ($value instanceof DateTime) {
            $type = PDO::PARAM_STR; // DateTime should be treated as a string
            $finalValue = $value->format("Y-m-d H:i:s");
        } else if (is_null($value)) {
            $type = PDO::PARAM_NULL; // NULL type
            $finalValue = null; // Set final value to null
        } else if (is_bool($value)) {
            $type = PDO::PARAM_BOOL; // Boolean type
            $finalValue = $value; // Keep the boolean value
        } else if (is_int($value)) {
            $type = PDO::PARAM_INT; // Integer type
            $finalValue = $value; // Keep the integer value
        }

        // Create and return an object with the type and value
        $result = new stdClass();
        $result->type = $type;
        $result->value = $finalValue;
        return $result;
    }
    
    /**
     * Sends an SQL query to a logger callback for debugging.
     *
     * This method invokes the callback function defined in the database object,
     * passing the final SQL query generated by combining the statement and parameters.
     *
     * @param PicoDatabase $database The database instance containing the callback.
     * @param PDOStatement $stmt The prepared PDO statement.
     * @param array $params The parameters bound to the query.
     *
     * @return void
     */
    public function debugQuery($database, $stmt, $params)
    {
        // Send query to logger
        $debugFunction = $database->getCallbackDebugQuery();
        if (isset($debugFunction) && is_callable($debugFunction)) {
            call_user_func($debugFunction, PicoDatabaseUtil::getFinalQuery($stmt, $params));
        }
    }

}