<?php

namespace MagicObject\Util\Database;

use DateTime;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
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
     * Replaces array parameters in the query string and applies pagination and sorting if necessary.
     *
     * This method iterates over the provided caller parameters and their values, replacing any array-type 
     * parameters with their string equivalents in the query string. Additionally, if any pagination or sorting 
     * objects (e.g., `PicoPageable` or `PicoSortable`) are detected in the parameters, it modifies the query 
     * string to include pagination and sorting clauses.
     *
     * @param string $databaseType Database type
     * @param string $queryString The SQL query string that may contain placeholders for parameters.
     * @param ReflectionParameter[] $callerParams The parameters of the calling method (reflection objects).
     * @param array $callerParamValues The actual values of the parameters passed to the calling method.
     * @return string The modified query string with array parameters replaced and pagination/sorting applied.
     * @throws InvalidArgumentException If the provided parameters are not in the expected format.
     */
    public function applyQueryParameters($databaseType, $queryString, $callerParams, $callerParamValues)
    {
        $pageable = null;
        $sortable = null;
        
        // Replace array
        foreach ($callerParamValues as $index => $paramValue) {
            if($paramValue instanceof PicoPageable)
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
                if(is_array($paramValue))
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
     * Handles the return of data based on the specified return type.
     *
     * This method processes the data returned from a PDO statement and returns it in the format
     * specified by the caller's `@return` docblock annotation. It supports various return types 
     * such as `void`, `PDOStatement`, `int`, `object`, `array`, `string`, or any specific class 
     * name (including array-type hinting).
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @param string $returnType The return type as specified in the caller function's docblock.
     * @return mixed The processed return data, which can be a single value, object, array, 
     *               PDOStatement, or a JSON string, based on the return type.
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

        // Handle array-type hinting (e.g., MagicObject[], MyClass[])
        if (strpos($returnType, "[") !== false) {
            return $this->handleArrayReturnType($stmt, $returnType);
        }

        // Handle single class-type return (e.g., MagicObject, MyClass)
        return $this->handleSingleClassReturnType($stmt, $returnType);
    }

    /**
     * Handles return types with array hinting (e.g., `MagicObject[]`, `MyClass[]`).
     *
     * @param PDOStatement $stmt The executed PDO statement.
     * @param string $returnType The array-type return type (e.g., `MagicObject[]`).
     * @return array The processed result as an array of objects.
     * @throws InvalidReturnTypeException If the return type is invalid or unrecognized.
     */
    private function handleArrayReturnType($stmt, $returnType)
    {
        $className = trim(explode("[", $returnType)[0]);

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
     * The method processes the `@return` annotation in the docblock of the caller function,
     * and adjusts for `self` to return the actual caller class name. It also handles array type 
     * return values.
     *
     * @param string $docComment The docblock comment of the caller function.
     * @param string $callerClassName The name of the class where the caller function is defined.
     * @return string The processed return type, which could be a class name, `self`, or `void`.
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
     * Extracts the query string from the docblock of the caller function.
     *
     * The method looks for the `@query` annotation in the docblock and extracts the query string.
     * It tries to handle different formats for the annotation, throwing an exception if no query is found.
     *
     * @param string $docComment The docblock comment of the caller function.
     * @return string The SQL query string extracted from the `@query` annotation.
     * @throws InvalidQueryInputException If no query string is found in the docblock.
     */
    public function extractQueryString($docComment)
    {
        // Get the query from the @query annotation
        preg_match('/@query\s*\("([^"]+)"\)/', $docComment, $matches);
        $queryString = $matches ? $matches[1] : '';
        
        // Trim the query string of whitespace and line breaks
        $queryString = trim($queryString, " \r\n\t ");
        
        if (empty($queryString)) {
            // Try reading the query in another way
            preg_match('/@query\s*\(\s*"(.*?)"\s*\)/s', $docComment, $matches);
            $queryString = $matches ? $matches[1] : '';
            
            if (empty($queryString)) {
                throw new InvalidQueryInputException("No query found.\r\n" . $docComment);
            }
        }
        
        return $queryString;
    }


    /**
     * Maps PHP types to the corresponding PDO parameter types.
     *
     * This method determines the appropriate PDO parameter type based on the type of the given value.
     * It handles various PHP data types, such as `null`, `boolean`, `integer`, `string`, `DateTime`, 
     * and objects like `PicoSqlJson`, and maps them to the appropriate PDO parameter types required 
     * for executing prepared statements in PDO.
     *
     * @param mixed $value The value to determine the PDO parameter type for. This can be of any type,
     *                     including `null`, `boolean`, `integer`, `string`, `DateTime`, or other types.
     * 
     * @return stdClass An object containing the following properties:
     *                  - `type`: The corresponding PDO parameter type (e.g., `PDO::PARAM_STR`, 
     *                            `PDO::PARAM_NULL`, `PDO::PARAM_BOOL`, `PDO::PARAM_INT`).
     *                  - `value`: The value formatted as required for the PDO parameter.
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
     * Debugs an SQL query by sending it to a logger callback function.
     * 
     * This method retrieves the callback function for debugging queries from the database object
     * and invokes it with the final SQL query string, which is generated by combining the SQL 
     * statement with the provided parameters.
     *
     * @param PicoDatabase $database The database instance that contains the callback function.
     * @param PDOStatement $stmt The PDO statement object that holds the prepared query.
     * @param array $params The parameters that are bound to the SQL statement.
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