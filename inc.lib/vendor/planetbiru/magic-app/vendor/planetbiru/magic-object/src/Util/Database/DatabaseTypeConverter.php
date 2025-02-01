<?php

namespace MagicObject\Util\Database;

use MagicObject\Database\PicoDatabaseType;

/**
 * Class DatabaseTypeConverter
 *
 * Provides methods for converting database schema types between MySQL, PostgreSQL, and SQLite. 
 * It maps column data types from one database system to another to facilitate schema migration.
 * 
 * Conversion mappings are provided for:
 * - MySQL to PostgreSQL and SQLite
 * - PostgreSQL to MySQL and SQLite
 * - SQLite to MySQL and PostgreSQL
 *
 * Use the conversion methods (`mysqlToPostgresql`, `postgresqlToSQLite`, etc.) to convert schema dumps 
 * from one format to another.
 *
 * **Example:**
 * ```php
 * <?php
 * $converter = new DatabaseTypeConverter();
 * $postgresqlSchema = $converter->mysqlToPostgresql($mysqlSchema);
 * ```
 *
 * @package MagicObject\Util\Database
 */
class DatabaseTypeConverter
{
    /**
     * MySQL type "tinyint(1)" mapped to PostgreSQL and SQLite boolean.
     *
     * @var string
     */
    const TYPE_TINYINT_1 = "tinyint(1)";
    
    /**
     * MySQL type "double precision" mapped to PostgreSQL and SQLite "double precision".
     *
     * @var string
     */
    const TYPE_DOUBLE_PRECISION = "double precision";
    
    /**
     * MySQL type "character varying" mapped to PostgreSQL and SQLite "character varying".
     *
     * @var string
     */
    const TYPE_CHARACTER_VARYING = "character varying";

    /**
     * Map of MySQL types to PostgreSQL types.
     *
     * @var array
     */
    private $mysqlToPostgresql = [
        self::TYPE_TINYINT_1 => "boolean",  // MySQL tinyint(1) to PostgreSQL boolean
        "tinyint" => "smallint",    // MySQL tinyint to PostgreSQL smallint
        "smallint" => "smallint",   // MySQL smallint to PostgreSQL smallint
        "int" => "integer",         // MySQL int to PostgreSQL integer
        "bigint" => "bigint",       // MySQL bigint to PostgreSQL bigint
        "float" => "real",          // MySQL float to PostgreSQL real
        "double" => "double precision", // MySQL double to PostgreSQL double precision
        "decimal" => "numeric",     // MySQL decimal to PostgreSQL numeric
        "varchar" => self::TYPE_CHARACTER_VARYING, // MySQL varchar to PostgreSQL character varying
        "char" => "character",      // MySQL char to PostgreSQL character
        "text" => "text",           // MySQL text to PostgreSQL text
        "longtext" => "text",       // MySQL longtext to PostgreSQL text
        "tinytext" => "text",       // MySQL tinytext to PostgreSQL text
        "datetime" => "timestamp",  // MySQL datetime to PostgreSQL timestamp
        "timestamp" => "timestamp with time zone", // MySQL timestamp to PostgreSQL timestamp with time zone
        "date" => "date",           // MySQL date to PostgreSQL date
        "time" => "time",           // MySQL time to PostgreSQL time
        "year" => "smallint",       // MySQL year to PostgreSQL smallint
        "json" => "jsonb",          // MySQL json to PostgreSQL jsonb
        "uuid" => "uuid"            // MySQL uuid to PostgreSQL uuid
    ];    

    /**
     * Map of MySQL types to SQLite types.
     *
     * @var array
     */
    private $mysqlToSQLite = [
        self::TYPE_TINYINT_1 => "INTEGER",  // MySQL tinyint(1) to SQLite INTEGER (boolean)
        "tinyint" => "INTEGER",     // MySQL tinyint to SQLite INTEGER
        "smallint" => "INTEGER",    // MySQL smallint to SQLite INTEGER
        "int" => "INTEGER",         // MySQL int to SQLite INTEGER
        "bigint" => "INTEGER",      // MySQL bigint to SQLite INTEGER
        "float" => "REAL",          // MySQL float to SQLite REAL
        "double" => "REAL",         // MySQL double to SQLite REAL
        "decimal" => "REAL",        // MySQL decimal to SQLite REAL (SQLite does not have a specific decimal type)
        "varchar" => "NVARCHAR",    // MySQL varchar to SQLite TEXT
        "char" => "TEXT",           // MySQL char to SQLite TEXT
        "longtext" => "TEXT",       // MySQL longtext to SQLite TEXT
        "text" => "TEXT",           // MySQL text to SQLite TEXT
        "tinytext" => "TEXT",       // MySQL tinytext to SQLite TEXT
        "datetime" => "DATETIME",   // MySQL datetime to SQLite TEXT (SQLite stores datetime as text), but we need DATETIME
        "timestamp" => "TIMESTAMP", // MySQL timestamp to SQLite TEXT, but we need TIMESTAMP
        "date" => "DATE",           // MySQL date to SQLite TEXT, but we need DATE
        "time" => "TIME",           // MySQL time to SQLite TEXT, but we need TIME
        "year" => "INTEGER",        // MySQL year to SQLite INTEGER (SQLite stores years as INTEGER)
        "json" => "TEXT",           // MySQL json to SQLite TEXT
        "uuid" => "TEXT"            // MySQL uuid to SQLite TEXT
    ];    

    /**
     * Map of PostgreSQL types to MySQL types.
     *
     * @var array
     */
    private $postgresqlToMySQL = [
        "boolean" => self::TYPE_TINYINT_1,  // PostgreSQL boolean to MySQL tinyint(1)
        "smallint" => "smallint",   // PostgreSQL smallint to MySQL smallint
        "integer" => "int",         // PostgreSQL integer to MySQL int
        "bigint" => "bigint",       // PostgreSQL bigint to MySQL bigint
        "real" => "float",          // PostgreSQL real to MySQL float
        self::TYPE_DOUBLE_PRECISION => "double", // PostgreSQL double precision to MySQL double
        self::TYPE_CHARACTER_VARYING => "varchar", // PostgreSQL character varying to MySQL varchar
        "character" => "char",      // PostgreSQL character to MySQL char
        "text" => "text",           // PostgreSQL text to MySQL text
        "timestamp" => "datetime",  // PostgreSQL timestamp to MySQL datetime
        "timestamp with time zone" => "timestamp",  // PostgreSQL timestamp with time zone to MySQL timestamp
        "timestamptz" => "timestamp",  // PostgreSQL timestamptz to MySQL timestamp
        "date" => "date",           // PostgreSQL date to MySQL date
        "time" => "time",           // PostgreSQL time to MySQL time
        "jsonb" => "json",          // PostgreSQL jsonb to MySQL json
        "uuid" => "uuid"            // PostgreSQL uuid to MySQL uuid
    ];

    /**
     * Map of PostgreSQL types to SQLite types.
     *
     * @var array
     */
    private $postgresqlToSQLite = [
        "boolean" => "INTEGER",     // PostgreSQL boolean to SQLite INTEGER (boolean)
        "smallint" => "INTEGER",    // PostgreSQL smallint to SQLite INTEGER
        "integer" => "INTEGER",     // PostgreSQL integer to SQLite INTEGER
        "bigint" => "INTEGER",      // PostgreSQL bigint to SQLite INTEGER
        "real" => "REAL",           // PostgreSQL real to SQLite REAL
        self::TYPE_DOUBLE_PRECISION => "REAL", // PostgreSQL double precision to SQLite REAL
        self::TYPE_CHARACTER_VARYING => "TEXT", // PostgreSQL character varying to SQLite TEXT
        "character" => "TEXT",      // PostgreSQL character to SQLite TEXT
        "text" => "TEXT",           // PostgreSQL text to SQLite TEXT
        "timestamp" => "TIMESTAMP", // PostgreSQL timestamp to SQLite TEXT
        "datetime" => "DATETIME",   // PostgreSQL date to SQLite TEXT
        "date" => "DATE",           // PostgreSQL date to SQLite TEXT
        "time" => "TIME",           // PostgreSQL time to SQLite TEXT
        "jsonb" => "TEXT",          // PostgreSQL jsonb to SQLite TEXT
        "uuid" => "TEXT"            // PostgreSQL uuid to SQLite TEXT
    ];

    /**
     * Map of SQLite types to MySQL types.
     *
     * @var array
     */
    private $sqliteToMySQL = [
        "NVARCHAR" => "varchar",    // SQLite NVARCHAR to MySQL varchar
        "INTEGER" => "int",         // SQLite INTEGER to MySQL int
        "REAL" => "float",          // SQLite REAL to MySQL float
        "TEXT" => "text",           // SQLite TEXT to MySQL text
        "BLOB" => "blob",           // SQLite BLOB to MySQL blob
    ];

    /**
     * Map of SQLite types to PostgreSQL types.
     *
     * @var array
     */
    private $sqliteToPostgresql = [
        "NVARCHAR" => "character varying", // SQLite NVARCHAR to PostgreSQL character varying
        "INTEGER" => "integer",     // SQLite INTEGER to PostgreSQL integer
        "REAL" => "real",           // SQLite REAL to PostgreSQL real
        "TEXT" => "text",           // SQLite TEXT to PostgreSQL text
        "BLOB" => "bytea",          // SQLite BLOB to PostgreSQL bytea
    ];

    /**
     * Convert MySQL schema to PostgreSQL schema.
     *
     * @param string $mysqlDump MySQL schema dump
     * @return string PostgreSQL schema
     */
    public function mysqlToPostgresql($mysqlDump)
    {
        return $this->convertSchema($mysqlDump, $this->mysqlToPostgresql);
    }

    /**
     * Convert MySQL schema to SQLite schema.
     *
     * @param string $mysqlDump MySQL schema dump
     * @return string SQLite schema
     */
    public function mysqlToSQLite($mysqlDump)
    {
        return $this->convertSchema($mysqlDump, $this->mysqlToSQLite);
    }

    /**
     * Convert PostgreSQL schema to MySQL schema.
     *
     * @param string $postgresqlDump PostgreSQL schema dump
     * @return string MySQL schema
     */
    public function postgresqlToMySQL($postgresqlDump)
    {
        return $this->convertSchema($postgresqlDump, $this->postgresqlToMySQL);
    }

    /**
     * Convert PostgreSQL schema to SQLite schema.
     *
     * @param string $postgresqlDump PostgreSQL schema dump
     * @return string SQLite schema
     */
    public function postgresqlToSQLite($postgresqlDump)
    {
        return $this->convertSchema($postgresqlDump, $this->postgresqlToSQLite);
    }

    /**
     * Convert SQLite schema to MySQL schema.
     *
     * @param string $sqliteDump SQLite schema dump
     * @return string MySQL schema
     */
    public function sqliteToMySQL($sqliteDump)
    {
        return $this->convertSchema($sqliteDump, $this->sqliteToMySQL);
    }

    /**
     * Convert SQLite schema to PostgreSQL schema.
     *
     * @param string $sqliteDump SQLite schema dump
     * @return string PostgreSQL schema
     */
    public function sqliteToPostgresql($sqliteDump)
    {
        return $this->convertSchema($sqliteDump, $this->sqliteToPostgresql);
    }

    /**
     * Convert schema based on given type mapping.
     *
     * @param string $dump The schema dump
     * @param array $typeMap The type mapping to apply
     * @return string Converted schema
     */
    private function convertSchema($dump, $typeMap)
    {
        $lines = explode("\n", $dump);
        $convertedSchema = "";

        foreach ($lines as $line) {
            // Skip comments
            if (strpos($line, "--") === 0 || empty(trim($line))) {
                continue;
            }

            // Process the CREATE TABLE line
            if (strpos($line, "CREATE TABLE") !== false) {
                $convertedSchema .= $this->processCreateTable($line, $lines, $typeMap);
            }
        }

        return $convertedSchema;
    }

    /**
     * Process CREATE TABLE line and apply type conversions.
     *
     * @param string $createTableLine The CREATE TABLE statement
     * @param array $lines The schema lines
     * @param array $typeMap The type mapping to apply
     * @return string Converted CREATE TABLE statement
     */
    private function processCreateTable($createTableLine, $lines, $typeMap)
    {
        preg_match("/CREATE TABLE `?(\w+)`?\s?\(/", $createTableLine, $matches);
        $tableName = $matches[1];

        $createTableSql = "CREATE TABLE $tableName (\n";
        $columns = [];

        // Process the columns
        foreach ($lines as $line) {
            if (strpos($line, ")") !== false) {
                break;  // End of CREATE TABLE
            }

            $columns[] = $this->processColumn($line, $typeMap);
        }

        $createTableSql .= implode(",\n", $columns);
        $createTableSql .= "\n);";

        return $createTableSql . "\n";
    }

    /**
     * Process each column and apply type conversion.
     *
     * @param string $columnDef Column definition line
     * @param array $typeMap Type mapping to apply
     * @return string Converted column definition
     */
    private function processColumn($columnDef, $typeMap)
    {
        preg_match("/`?(\w+)`?\s+(\w+)(.*)/", $columnDef, $matches);
        $columnName = $matches[1];
        $columnType = $matches[2];

        // Apply type conversion based on mapping
        if (array_key_exists(strtolower($columnType), $typeMap)) {
            $columnType = $typeMap[strtolower($columnType)];
        }

        return "`$columnName` $columnType";
    }

    /**
     * Converts a column type from MySQL to the target database type (PostgreSQL or SQLite).
     *
     * This method takes a column type (e.g., "varchar", "int") and converts it
     * to the appropriate data type for the specified target database (PostgreSQL or SQLite).
     * It uses predefined mappings for MySQL to PostgreSQL and MySQL to SQLite conversions.
     *
     * @param string $columnType The column type to be converted (e.g., "varchar", "int").
     * @param string $databaseType The target database type. This can be one of the following:
     *                             - PicoDatabaseType::DATABASE_TYPE_POSTGRESQL
     *                             - PicoDatabaseType::DATABASE_TYPE_SQLITE
     *
     * @return string The corresponding column type for the target database, in uppercase.
     *                If no match is found in the predefined mappings, the original column type is returned.
     */
    public function convertType($columnType, $databaseType)
    {
        $columnType = strtolower($columnType);
        if($databaseType == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL && isset($this->mysqlToPostgresql[$columnType]))
        {
            return strtoupper($this->mysqlToPostgresql[$columnType]);
        }
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE && isset($this->mysqlToSQLite[$columnType]))
        {
            return strtoupper($this->mysqlToSQLite[$columnType]);
        }
        return strtoupper($columnType);
    }
}
