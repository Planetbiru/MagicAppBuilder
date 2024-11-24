<?php
namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\MandatoryTableNameException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;

/**
 * Represents the structure of a database table.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseStructure
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_ID = "Id";
    const KEY_NAME = "name";
    const KEY_TYPE = "type";
    const KEY_NULL = "null";
    const KEY_NOT_NULL = "notnull";
    const KEY_NULLABLE = "nullable";
    const KEY_PRIMARY = "primary";
    const DATABASE_TYPE_MYSQL = "mysql";
    const DATABASE_TYPE_MARIADB = "mariadb";

    /**
     * The associated MagicObject instance.
     *
     * @var MagicObject
     */
    private $object;

    /**
     * The name of the class representing the table.
     *
     * @var string
     */
    private $className = "";

    /**
     * Constructor to initialize the PicoDatabaseStructure with a MagicObject.
     *
     * @param MagicObject $object The MagicObject representing the database structure.
     */
    public function __construct($object)
    {
        $this->className = get_class($object);
        $this->object = $object;
    }

    /**
     * Generates a CREATE TABLE statement based on the object's metadata.
     *
     * @param string $databaseType The type of database (e.g., MySQL, MariaDB).
     * @param string|null $tableName Optional name of the table. If not provided, it will be inferred.
     * @return string The SQL CREATE TABLE statement.
     * @throws MandatoryTableNameException If no table name is provided and cannot be inferred.
     */
    public function showCreateTable($databaseType, $tableName = null)
    {
        $info = $this->getObjectInfo();
        if (!isset($tableName) || $info->getTableName() == null) {
            throw new MandatoryTableNameException("Table name is mandatory");
        } else {
            $tableName = $info->getTableName();
        }
        $createStrArr = array();
        $createStrArr[] = "CREATE TABLE IF NOT EXISTS $tableName (";
        $createStrArr[] = $this->showCreateTableByType($databaseType, $info);
        $createStrArr[] = ");";
        return implode("\r\n", $createStrArr);
    }

    /**
     * Generates the CREATE TABLE syntax based on the database type and table information.
     *
     * @param string $databaseType The type of database (e.g., MySQL).
     * @param PicoTableInfo $info The table information containing column definitions.
     * @return string The SQL column definitions for the CREATE TABLE statement.
     */
    private function showCreateTableByType($databaseType, $info)
    {
        $createStrArr = array();
        $pk = array();
        
        if ($databaseType == self::DATABASE_TYPE_MYSQL) {
            foreach ($info->getColumns() as $column) {
                $createStrArr[] = $column[self::KEY_NAME] . " " . $column[self::KEY_TYPE] . " " . $this->nullable($column[self::KEY_NULLABLE]);
            }
            foreach ($info->getColumns() as $column) {
                if (isset($column[self::KEY_PRIMARY]) && $column[self::KEY_PRIMARY] === true) {
                    $pk[] = $column[self::KEY_NAME];
                }
            }
            if (!empty($pk)) {
                $createStrArr[] = "PRIMARY KEY (" . implode(", ", $pk) . ")";
            }
        }

        return implode(",\r\n", $createStrArr);
    }

    /**
     * Returns the NULL/NOT NULL declaration based on the nullable setting.
     *
     * @param mixed $nullable Indicates if the column is nullable.
     * @return string The corresponding NULL or NOT NULL declaration.
     */
    private function nullable($nullable)
    {
        return ($nullable === true || strtolower($nullable) == "true") ? "NULL" : "NOT NULL";
    }

    /**
     * Parses a key-value string from an annotation.
     *
     * @param PicoAnnotationParser $reflexClass The reflection of the class containing the annotation.
     * @param string $queryString The string to be parsed.
     * @param string $parameter The parameter name for error reporting.
     * @return array The parsed key-value pairs.
     * @throws InvalidAnnotationException If the annotations are invalid or cannot be parsed.
     */
    private function parseKeyValue($reflexClass, $queryString, $parameter)
    {
        try {
            return $reflexClass->parseKeyValue($queryString);
        } catch (InvalidAnnotationException $e) {
            throw new InvalidAnnotationException("Invalid annotation @" . $parameter);
        }
    }

    /**
     * Retrieves metadata about the object, including table name and column definitions.
     *
     * @return PicoTableInfo An instance containing the table name and column information.
     */
    public function getObjectInfo()
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
        $values = $this->parseKeyValue($reflexClass, $table, self::ANNOTATION_TABLE);
        $picoTableName = $values[self::KEY_NAME];
        $columns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();
        $notNullColumns = array();
        $props = $reflexClass->getProperties();
        $defaultValue = array();

        // Iterate through the properties of the class
        foreach ($props as $prop) {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, PicoAnnotationParser::PROPERTY);
            $parameters = $reflexProp->getParameters();

            // Get column name from the parameters
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_COLUMN) == 0) {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    $columns[$prop->name] = $values;
                }
            }
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name])) {
                    $columns[$prop->name][self::KEY_PRIMARY] = true;
                }
            }
        }
        return new PicoTableInfo($picoTableName, $columns, [], $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns);
    }
}
