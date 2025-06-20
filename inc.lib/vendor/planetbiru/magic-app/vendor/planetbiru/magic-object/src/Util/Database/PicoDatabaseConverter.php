<?php

namespace MagicObject\Util\Database;

use MagicObject\Exceptions\DatabaseConversionException;

/**
 * Class PicoDatabaseConverter
 *
 * This class provides robust conversion utilities for translating SQL table structures
 * and queries between different SQL dialects: MySQL, PostgreSQL, and SQLite.
 *
 * Key functionalities:
 * - Converts CREATE TABLE statements between MySQL, PostgreSQL, and SQLite, including:
 *   - Data type mapping and normalization
 *   - Identifier quoting and syntax adaptation
 *   - Handling of constraints, keys, and auto-increment fields
 *   - Keyword and function normalization
 * - Supports round-trip conversion (e.g., MySQL → PostgreSQL → MySQL)
 * - Can parse and split SQL column/constraint definitions, respecting nested parentheses
 * - Provides type translation utilities for mapping field types between dialects
 * - Offers value quoting, escaping, and PHP type conversion helpers for SQL literals
 * - Enables migration and data-dump scenarios between different RDBMS platforms
 *
 * This class is typically used for database migration, schema portability, and
 * interoperability between different database engines, without requiring entity definitions.
 *
 * @package MagicObject\Util\Database
 */
class PicoDatabaseConverter // NOSONAR
{
    /**
     * Array mapping of database field types to SQLite data types.
     *
     * @var array
     */
    private $dbToSqlite;

    /**
     * Array mapping of database field types to MySQL data types.
     *
     * @var array
     */
    private $dbToMySQL;

    /**
     * Array mapping of database field types to PostgreSQL data types.
     *
     * @var array
     */
    private $dbToPostgreSQL;

    /**
     * Array mapping of SQL data types to PHP types.
     *
     * @var array
     */
    private $sqlToPhpType;

    /**
     * PicoDatabaseConverter constructor.
     *
     * Initializes internal data type mappings used for converting SQL queries
     * between different database dialects (e.g., MySQL, PostgreSQL, SQLite).
     *
     * This method prepares internal arrays for:
     * - Mapping SQL types to target dialects
     * - Mapping SQL types to PHP types
     */
    public function __construct() // NOSONAR
    {
        $this->initTypes();

    }

    /**
     * Initializes internal mappings for type conversions between different database systems
     * (MySQL, SQLite, PostgreSQL) and PHP types.
     *
     * This function sets up:
     * - `$dbToSqlite`: maps other DB types to equivalent SQLite types.
     * - `$dbToMySQL`: maps other DB types to equivalent MySQL types.
     * - `$dbToPostgreSQL`: maps other DB types to equivalent PostgreSQL types.
     * - `$sqlToPhpType`: maps SQL types to corresponding PHP native types.
     *
     * These mappings are used during SQL translation and type inference to ensure
     * consistent cross-database behavior and compatibility.
     *
     * @return void
     */
    public function initTypes() // NOSONAR
    {
        $this->dbToSqlite = [
            "tinyint(1)" => "BOOLEAN", // NOSONAR
            "tinyint" => "INTEGER",
            "smallint" => "INTEGER",
            "mediumint" => "INTEGER",
            "bigint" => "INTEGER",
            "integer" => "INTEGER",
            "int" => "INTEGER",
            "bigserial" => "INTEGER",
            "serial" => "INTEGER",

            "real" => "REAL",
            "float" => "REAL",
            "double precision" => "REAL", // NOSONAR
            "double" => "REAL",
            "decimal" => "REAL",
            "numeric" => "REAL",
            "money" => "REAL",

            "bit" => "INTEGER",
            "boolean" => "INTEGER",

            "char" => "NVARCHAR",
            "nvarchar" => "NVARCHAR",
            "character varying" => "NVARCHAR", // NOSONAR
            "varchar" => "NVARCHAR",

            "tinytext" => "TEXT",
            "mediumtext" => "TEXT",
            "longtext" => "TEXT",
            "text" => "TEXT",
            "jsonb" => "TEXT",
            "json" => "TEXT",
            "uuid" => "TEXT",
            "xml" => "TEXT",
            "blob" => "BLOB",
            "binary" => "BLOB",
            "varbinary" => "BLOB",

            "timestamp with time zone" => "TIMESTAMP", // NOSONAR
            "timestamp without time zone" => "DATETIME", // NOSONAR
            "timestamptz" => "TIMESTAMP", // NOSONAR
            "datetime" => "DATETIME",
            "timestamptz" => "TIMESTAMP",
            "timestamp" => "TIMESTAMP",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "INTEGER"
        ];

        $this->dbToMySQL = [
            "bigint" => "BIGINT",
            "mediumint" => "MEDIUMINT",
            "smallint" => "SMALLINT",
            "tinyint(1)" => "TINYINT(1)", // NOSONAR
            "tinyint" => "TINYINT",
            "integer" => "BIGINT",
            
            "bigserial" => "BIGINT",
            "serial" => "INT",

            "int" => "INT",
            "float" => "FLOAT",
            "real" => "DOUBLE",
            "double precision" => "DOUBLE",
            "double" => "DOUBLE",
            "decimal" => "DECIMAL",
            "numeric" => "NUMERIC",
            "money" => "DECIMAL(19,4)",

            "bit" => "BIT",
            "boolean" => "TINYINT(1)",

            "char" => "CHAR",
            "nvarchar" => "VARCHAR",
            "varchar" => "VARCHAR",
            "character varying" => "VARCHAR",

            "tinytext" => "TINYTEXT",
            "mediumtext" => "MEDIUMTEXT",
            "longtext" => "LONGTEXT",
            "text" => "TEXT",
            "jsonb" => "JSON",
            "json" => "JSON",
            "uuid" => "CHAR(36)",
            "xml" => "TEXT",
            "binary" => "BINARY",
            "varbinary" => "VARBINARY",
            "blob" => "BLOB",

            "timestamp with time zone" => "TIMESTAMP", // NOSONAR
            "timestamp without time zone" => "DATETIME", // NOSONAR
            "timestamptz" => "TIMESTAMP", // NOSONAR
            "datetime" => "DATETIME",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "YEAR",

            "enum" => "ENUM",
            "set" => "SET"
        ];

        $this->dbToPostgreSQL = [
            "bigint" => "BIGINT",
            "mediumint" => "INTEGER",
            "smallint" => "INTEGER",
            "tinyint(1)" => "BOOLEAN",
            "tinyint" => "INTEGER",
            "integer" => "INTEGER",
            "int" => "INTEGER",
            "bigserial" => "BIGSERIAL",
            "serial" => "SERIAL",

            "float" => "REAL",
            "real" => "REAL",
            "double precision" => "DOUBLE PRECISION",
            "double" => "DOUBLE PRECISION",
            "decimal" => "DECIMAL",
            "numeric" => "NUMERIC",
            "money" => "MONEY",

            "bit" => "BIT",
            "boolean" => "BOOLEAN",

            "char" => "CHARACTER",
            "nvarchar" => "CHARACTER VARYING", // NOSONAR
            "varchar" => "CHARACTER VARYING",
            "character varying" => "CHARACTER VARYING", // NOSONAR
            "tinytext" => "TEXT",
            "mediumtext" => "TEXT",
            "longtext" => "TEXT",
            "smalltext" => "TEXT",
            "text" => "TEXT",
            "json" => "JSONB",
            "jsonb" => "JSONB",
            "uuid" => "UUID",
            "xml" => "XML",
            "blob" => "BYTEA",
            "binary" => "BYTEA",
            "varbinary" => "BYTEA",

            "datetime" => "TIMESTAMP WITHOUT TIME ZONE", // NOSONAR
            "timestamp without time zone" => "TIMESTAMP WITHOUT TIME ZONE",
            "timestamp with time zone" => "TIMESTAMP WITH TIME ZONE", // NOSONAR
            "timestamptz" => "TIMESTAMP WITH TIME ZONE",
            "timestamp" => "TIMESTAMP WITH TIME ZONE",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "INTEGER",

            "enum" => "TEXT", // PostgreSQL does support ENUM but requires definition
            "set" => "TEXT"   // no native SET, fallback to TEXT
        ];

        $this->sqlToPhpType = [
            // Integer types
            'tinyint' => 'int',
            'tinyint(1)' => 'bool', // special case often used for boolean
            'smallint' => 'int',
            'mediumint' => 'int',
            'int' => 'int',
            'integer' => 'int',
            'bigint' => 'int',
            'serial' => 'int',
            'bigserial' => 'int',
            'year' => 'int',
            'bit' => 'int',

            // Floating-point types
            'float' => 'float',
            'real' => 'float',
            'double' => 'float',
            'double precision' => 'float',
            'decimal' => 'float',
            'numeric' => 'float',
            'money' => 'float',

            // Boolean type
            'boolean' => 'bool',
            'bool' => 'bool',

            // String types
            'char' => 'string',
            'varchar' => 'string',
            'nvarchar' => 'string',
            'character varying' => 'string',
            'text' => 'string',
            'tinytext' => 'string',
            'mediumtext' => 'string',
            'longtext' => 'string',
            'smalltext' => 'string',
            'enum' => 'string',
            'set' => 'string',
            'uuid' => 'string',
            'xml' => 'string',

            // Date & Time types (usually stored as string in PHP)
            'datetime' => 'string',
            'timestamp' => 'string',
            'timestamp with time zone' => 'string',
            'timestamp without time zone' => 'string',
            'timestamptz' => 'string',
            'date' => 'string',
            'time' => 'string',

            // Binary types
            'blob' => 'string',
            'binary' => 'string',
            'varbinary' => 'string',
            'bytea' => 'string',

            // JSON types
            'json' => 'array',   // assuming it's decoded
            'jsonb' => 'array',  // assuming it's decoded
        ];
    }

    /**
     * Generates a value string for an SQL INSERT statement, excluding the `VALUES` keyword.
     *
     * Converts raw input values into appropriate SQL-formatted values based on their types and
     * the target SQL dialect (e.g., MySQL, PostgreSQL, SQLite).
     *
     * Example output: ('value1', 123, NULL)
     *
     * @param array $data         Associative array of data to insert. Keys are column names, values are raw values.
     * @param array $targetTypes  Associative array of target data types. Keys are column names, values are SQL types.
     * @param string $targetDialect Target SQL dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     *
     * @return string|null A comma-separated string of values inside parentheses, or null if input is empty.
     */
    public function createInsert($data, $targetTypes, $targetDialect)
    {
        $values = array();
        foreach($data as $columnName => $rawValue)
        {
            $sqlType = $this->getColumnType($targetTypes, $columnName);
            $value = $this->convertToPhpType($rawValue, $sqlType, $targetDialect);
            $values[] = $value;
        }
        if(!empty($values))
        {
            return "(".implode(", ", $values).")";
        }
        return null;
    }

    /**
     * Gets the SQL data type for a specific column name from the provided target type map.
     *
     * Defaults to 'text' if the column type is not found.
     *
     * @param array $targetTypes Associative array of column names to SQL types.
     * @param string $columnName The name of the column to look up.
     *
     * @return string The SQL data type associated with the column, or 'text' if undefined.
     */
    public function getColumnType($targetTypes, $columnName)
    {
        return isset($targetTypes[$columnName]) ? $targetTypes[$columnName] : 'text';
    }

    /**
     * Escapes and quotes a string value for safe use in an SQL statement.
     *
     * Returns `'NULL'` if the input is null. Otherwise, wraps the string in single quotes
     * and escapes internal quotes or special characters.
     *
     * @param string|null $value The raw string value to quote.
     *
     * @return string The SQL-safe quoted string or `'NULL'` if the value is null.
     */
    public function quoteString($value)
    {
        if(!isset($value) || $value === null)
        {
            return "NULL";
        }
        return "'".$this->escapeSqlString($value)."'";
    }

    /**
     * Converts a raw database value to its corresponding PHP representation
     * based on the provided SQL type and database dialect.
     *
     * This function is commonly used for safely handling and transforming
     * database field values into native PHP types before usage in application logic.
     *
     * Supported conversions:
     * - Integer types are cast to int.
     * - Boolean types are normalized to true/false or 0/1 (for SQLite).
     * - Float/decimal types are cast to float.
     * - JSON is decoded into an associative array and quoted.
     * - Binary/blob data is read and quoted.
     * - Date/time values are returned as quoted strings.
     * - Unknown or string types are returned as quoted strings.
     *
     * @param mixed  $value   The raw input value (e.g., string, int, stream resource).
     * @param string $sqlType The SQL type name (e.g., 'int', 'boolean', 'json', etc.).
     * @param string $dialect The database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     *
     * @return mixed The value converted to the appropriate PHP type or quoted string.
     */
    public function convertToPhpType($value, $sqlType, $dialect) // NOSONAR
    {
        // Normalize the SQL type: remove length/precision and convert to lowercase
        $normalizedType = strtolower(trim(preg_replace('/\s*\(.*\)/', '', $sqlType)));

        // If the value is already null, return it directly
        if ($value === null) {
            return "NULL";
        }

        // Convert based on the normalized SQL type
        switch ($normalizedType) // NOSONAR
        {
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'serial':
            case 'bigserial':
            case 'year':
            case 'bit':
                return (int) $value;

            case 'tinyint':
            case 'tinyint(1)':
            case 'boolean':
            case 'bool':
                // Convert to boolean; fall back to null if unrecognized
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!isset($value)) {
                    return null;
                }
                // For SQLite, return as integer 0/1
                if (stripos($dialect, 'sqlite') !== false) {
                    return $value === true ? 1 : 0;
                }
                return $value ? "TRUE" : "FALSE";

            case 'float':
            case 'real':
            case 'double':
            case 'double precision':
            case 'decimal':
            case 'numeric':
            case 'money':
                return (float) $value;

            case 'json':
            case 'jsonb':
                // Decode JSON into an associative array
                return $this->quoteString(json_decode($value, true));

            case 'blob':
            case 'binary':
            case 'varbinary':
            case 'bytea':
                // If the value is a resource (e.g., stream), read it; otherwise cast to string
                $result = is_resource($value) ? stream_get_contents($value) : (string) $value;
                return $this->quoteString($result);

            case 'date':
            case 'time':
            case 'datetime':
            case 'timestamp':
            case 'timestamp with time zone':
            case 'timestamp without time zone':
            case 'timestamptz':
                // Optionally return as DateTime object instead of string
                $result = (string) $value;
                return $this->quoteString($result);

            default:
                // Fallback: treat as string
                $result = (string) $value; // NOSONAR
                return $this->quoteString($result); // quote and escape the string
        }
    }

    /**
     * Converts a PHP value to a valid SQL literal string based on native PHP type.
     *
     * @param mixed  $value The PHP value (e.g. int, string, array, etc).
     * @param string $phpType The native PHP type (e.g. 'int', 'string', 'bool', etc).
     * @return string SQL literal (e.g. 123, 'text', NULL).
     */
    public function convertPhpValueToSqlLiteral($value, $phpType) // NOSONAR
    {
        // Normalize PHP type
        $phpType = strtolower(trim($phpType));

        // NULL
        if ($value === null || $phpType === 'null') {
            return 'NULL';
        }

        switch ($phpType) {
            case 'int':
            case 'integer':
                return (string)(int)$value;

            case 'float':
            case 'double':
                return (string)(float)$value;

            case 'bool':
            case 'boolean':
                return $value ? '1' : '0';

            case 'array':
                // Convert array to JSON string and escape
                $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return "'" . $this->escapeSqlString($json) . "'";

            case 'string':
            default:
                return "'" . $this->escapeSqlString((string)$value) . "'";
        }
    }

    /**
     * Converts a value and its SQL type to a valid SQL literal string
     * by first mapping the SQL type to a native PHP type.
     *
     * @param mixed  $value The input value to be converted.
     * @param string $type  The SQL type (e.g., 'int', 'varchar', 'boolean').
     * @return string A valid SQL literal (e.g., 123, 'text', NULL).
     */
    public function convertValueToSqlLiteral($value, $type)
    {
        $phpType = isset($this->sqlToPhpType[$type]) ? $this->sqlToPhpType[$type] : 'string';
        return $this->convertPhpValueToSqlLiteral($value, $phpType);
    }

    /**
     * Escapes a string for use in SQL queries by doubling single quotes.
     *
     * @param string $value The input string to be escaped.
     * @return string The escaped string with single quotes doubled.
     */
    protected function escapeSqlString($value)
    {
        return str_replace("'", "''", $value);
    }

    /**
     * Translates a database field type from a source dialect to a target dialect.
     * This function primarily maps the base type, while specific translation methods
     * handle modifiers like AUTO_INCREMENT, SERIAL, BOOLEAN defaults, etc.
     *
     * @param string $type The field type to translate (e.g., "VARCHAR(255)", "INT").
     * @param string $sourceDialect The source database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @param string $targetDialect The target database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The translated base field type.
     * @throws DatabaseConversionException If an unsupported target dialect is provided.
     */
    public function translateFieldType($type, $sourceDialect, $targetDialect) // NOSONAR
    {
        $type = strtolower(trim($type));
        // Normalize spaces in the type string
        $type = preg_replace('/\s+/', ' ', $type); // NOSONAR

        $targetMap = [];
        switch ($targetDialect) {
            case 'mysql': {
                $targetMap = $this->dbToMySQL;
                break;
            }
            case 'postgresql': {
                $targetMap = $this->dbToPostgreSQL;
                break;
            }
            case 'sqlite': {
                $targetMap = $this->dbToSqlite;
                break;
            }
            default: {
                throw new DatabaseConversionException("Unsupported target dialect: " . $targetDialect);
            }
        }

        // Extract base type and any parameters (e.g., "VARCHAR(255)" -> "varchar", "(255)")
        $baseType = $type;
        $params = '';
        if (preg_match('/^([a-zA-Z_]+)(\s*\(.*\))?$/', $type, $matches)) {
            $baseType = $matches[1];
            if (isset($matches[2])) {
                $params = $matches[2];
            }
        }

        // Handle specific type conversions that are not just direct map lookups
        if ($sourceDialect === 'mysql') {
            if ($baseType === 'enum' || $baseType === 'set') {
                return 'TEXT';
            }
            if ($baseType === 'tinyint' && $params === '(1)') {
                if ($targetDialect === 'postgresql') {
                    return 'BOOLEAN';
                }
                if ($targetDialect === 'sqlite') {
                    return 'INTEGER';
                }
            }
        } elseif ($sourceDialect === 'postgresql') {
            if ($baseType === 'serial' || $baseType === 'bigserial') {
                if ($targetDialect === 'mysql') {
                    return $baseType === 'bigserial' ? 'BIGINT' : 'INT';
                }
                if ($targetDialect === 'sqlite') {
                    return 'INTEGER';
                }
            }
            if ($baseType === 'boolean') {
                if ($targetDialect === 'mysql') {
                    return 'TINYINT(1)';
                }
                if ($targetDialect === 'sqlite') {
                    return 'INTEGER';
                }
            }
            if ($baseType === 'jsonb') {
                if ($targetDialect === 'mysql') {
                    return 'JSON';
                }
                if ($targetDialect === 'sqlite') {
                    return 'TEXT';
                }
            }
            if ($baseType === 'timestamp') {
                if ($this->strContains($type, 'with time zone')) {
                    if ($targetDialect === 'mysql') {
                        return 'TIMESTAMP';
                    }
                    if ($targetDialect === 'sqlite') {
                        return 'DATETIME';
                    }
                } elseif ($this->strContains($type, 'without time zone')) {
                    if ($targetDialect === 'mysql') {
                        return 'DATETIME';
                    }
                    if ($targetDialect === 'sqlite') {
                        return 'DATETIME';
                    }
                }
            }
        } elseif ($sourceDialect === 'sqlite') {
            if ($baseType === 'datetime') {
                if ($targetDialect === 'mysql') {
                    return 'DATETIME';
                }
                if ($targetDialect === 'postgresql') {
                    return 'TIMESTAMP WITHOUT TIME ZONE';
                }
            }
        }

        // Fallback to direct mapping of base type
        if (isset($targetMap[$baseType])) {
            $translatedBaseType = $targetMap[$baseType];

            if (
                $this->strContains(strtoupper($translatedBaseType), 'VARCHAR') ||
                $this->strContains(strtoupper($translatedBaseType), 'CHARACTER VARYING') ||
                $this->strContains(strtoupper($translatedBaseType), 'CHAR')
            ) {
                return $translatedBaseType . $params;
            }

            return $translatedBaseType;
        }

        return strtoupper($type);
    }

    /**
     * Normalizes and quotes an SQL identifier (e.g., table or column name) based on the database dialect.
     * 
     * Removes existing quotes and applies the appropriate quoting style for the target dialect.
     *
     * @param string $identifier The identifier to be quoted (e.g., column or table name).
     * @param string $dialect The SQL dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The properly quoted identifier.
     */
    public function quoteIdentifier($identifier, $dialect)
    {
        $identifier = trim($identifier, "`\"[]"); // Remove existing quotes
        switch ($dialect) {
            case 'mysql':
                return "`" . $identifier . "`";
            case 'postgresql':
            case 'sqlite':
                return "\"" . $identifier . "\"";
            default:
                return $identifier;
        }
    }

    /**
     * Translates a CREATE TABLE statement from MySQL to PostgreSQL.
     *
     * @param string $sql The MySQL CREATE TABLE statement.
     * @return string The translated PostgreSQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function mysqlToPostgreSQL($sql) // NOSONAR
    {
        $sql = trim($sql);

        // Extract table name and body using a custom parser to handle nested parentheses
        if (!preg_match('/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([^\s`(]+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }

        $ifNotExists = isset($matches[1][0]) ? 'IF NOT EXISTS ' : ''; // NOSONAR
        $tableName = $this->quoteIdentifier($matches[2][0], 'postgresql');
        $startPos = $matches[0][1] + strlen($matches[0][0]) - 1;

        $depth = 1;
        $i = $startPos + 1;
        $len = strlen($sql);
        while ($i < $len && $depth > 0) {
            if ($sql[$i] === '(') 
            {
                $depth++;
            }
            elseif ($sql[$i] === ')') 
            {
                $depth--;
            }
            $i++;
        }

        if ($depth !== 0) {
            throw new DatabaseConversionException("Unbalanced parentheses in CREATE TABLE statement.");
        }

        $columnsSection = substr($sql, $startPos + 1, $i - $startPos - 2);

        // Parse lines manually to preserve commas inside data types or defaults
        $lines = preg_split('/,(?![^\(\)]*\))/', $columnsSection);
        $newLines = [];
        $tableConstraints = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^`?([^`\s]+)`?\s+([a-zA-Z0-9_\(\)]+)(.*)$/i', $line, $colMatches)) /*M NOSONAR */ {
                $columnName = $this->quoteIdentifier($colMatches[1], 'postgresql');
                $columnType = strtolower(trim($colMatches[2]));
                $columnDefinition = trim($colMatches[3]);

                // Translate type
                $translatedType = $this->translateFieldType($columnType, 'mysql', 'postgresql');

                // Handle AUTO_INCREMENT
                if (stripos($columnDefinition, 'AUTO_INCREMENT') !== false) {
                    $translatedType = stripos($columnType, 'bigint') !== false ? 'BIGSERIAL' : 'SERIAL';
                    $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    if (stripos($columnDefinition, 'PRIMARY KEY') === false) // NOSONAR
                    {
                        $columnDefinition .= 'PRIMARY KEY';
                    }
                }

                // Handle BOOLEAN (tinyint(1))
                if (preg_match('/tinyint\s*\(\s*1\s*\)/i', $columnType)) {
                    $translatedType = 'BOOLEAN';
                    $columnDefinition = str_ireplace("DEFAULT '1'", 'DEFAULT TRUE', $columnDefinition); // NOSONAR
                    $columnDefinition = str_ireplace("DEFAULT '0'", 'DEFAULT FALSE', $columnDefinition); // NOSONAR
                }

                // Remove MySQL-specific ON UPDATE
                if (stripos($columnDefinition, 'ON UPDATE CURRENT_TIMESTAMP') !== false) /* NOSONAR */ {
                    $columnDefinition = str_ireplace('ON UPDATE CURRENT_TIMESTAMP', '', $columnDefinition);
                }

                // ENUM/SET to TEXT
                if (stripos($columnType, 'enum') !== false || stripos($columnType, 'set') !== false) {
                    $translatedType = 'TEXT';
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            }
            // Table constraints
            elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY)\s*`?([^`]+)?`?\s*\((.+)\)/i', $line, $keyMatches)) {
                $keyType = strtoupper($keyMatches[1]);
                $keyName = $keyMatches[2];
                $keyColumns = preg_replace('/`([^`]+)`/', '"$1"', $keyMatches[3]); // NOSONAR

                if ($keyType === 'PRIMARY KEY') {
                    $tableConstraints[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE KEY') {
                    $tableConstraints[] = 'CONSTRAINT ' . $this->quoteIdentifier($keyName, 'postgresql') . ' UNIQUE (' . $keyColumns . ')';
                }
            }
            // Other constraints
            else {
                $line = preg_replace_callback('/`([^`]+)`/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'postgresql');
                }, $line);
                $newLines[] = $line;
            }
        }

        if (!empty($tableConstraints)) {
            $newLines = array_merge($newLines, $tableConstraints);
        }

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)"; // NOSONAR

        // Clean up MySQL table options
        $finalSql = preg_replace('/ENGINE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/COLLATE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/COMMENT\s+\'.*?\'/i', '', $finalSql); // NOSONAR

        $finalSql = str_replace('`', '"', $finalSql); // Convert backticks to double quotes for PostgreSQL
        $finalSql = str_replace('"PRIMARY" KEY', 'PRIMARY KEY', $finalSql); // Fix PostgreSQL's PRIMARY KEY quoting
        $finalSql = str_replace('"UNIQUE" KEY', 'UNIQUE', $finalSql); // Fix PostgreSQL's UNIQUE KEY quoting
        $finalSql = str_replace('"FOREIGN" KEY', 'FOREIGN KEY', $finalSql); // Fix PostgreSQL's FOREIGN KEY quoting
        $finalSql = str_replace('"CHECK" (', 'CHECK (', $finalSql); // Fix PostgreSQL's CHECK constraint quoting
        $finalSql = str_replace('"DEFAULT" ', 'DEFAULT ', $finalSql); // Fix PostgreSQL's DEFAULT quoting
        $finalSql = str_replace('"NOT" NULL', 'NOT NULL', $finalSql); // Fix PostgreSQL's NOT NULL quoting
        $finalSql = str_replace('"NULL"', 'NULL', $finalSql); // Fix PostgreSQL's NULL quoting
        $finalSql = str_replace('"REFERENCES"', 'REFERENCES', $finalSql); // Fix PostgreSQL's REFERENCES quoting
        $finalSql = str_replace('"ON" DELETE', 'ON DELETE', $finalSql); // Fix PostgreSQL's ON DELETE quoting
        $finalSql = str_replace('"ON" UPDATE', 'ON UPDATE', $finalSql); // Fix PostgreSQL's ON UPDATE quoting
        $finalSql = str_replace('"USING"', 'USING', $finalSql); // Fix PostgreSQL's USING quoting
        $finalSql = str_replace('"WITH"', 'WITH', $finalSql); // Fix PostgreSQL's WITH quoting
        $finalSql = str_replace('"CONSTRAINT"', 'CONSTRAINT', $finalSql); // Fix PostgreSQL's CONSTRAINT quoting

        $finalSql = str_replace(' DEFAULT NULL', ' NULL DEFAULT NULL', $finalSql);
        $finalSql = str_replace(' NULL NULL DEFAULT NULL', ' NULL DEFAULT NULL', $finalSql);
        
        $finalSql = $this->fixLines($finalSql);

        return trim($finalSql) . ';';
    }

    /**
     * Translates a CREATE TABLE statement from MySQL to SQLite.
     *
     * @param string $sql The MySQL CREATE TABLE statement.
     * @return string The translated SQLite CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function mysqlToSQLite($sql) // NOSONAR
    {
        $sql = trim($sql);

        // Ambil nama tabel dan isi definisi kolom/constraint
        if (!preg_match('/CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([^\s`(]+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new DatabaseConversionException("Invalid MySQL CREATE TABLE statement format.");
        }

        $ifNotExists = isset($matches[1][0]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[2][0], 'sqlite');
        $startPos = $matches[0][1] + strlen($matches[0][0]) - 1;

        // Ambil isi kolom dan constraint dengan tracking tanda kurung
        $depth = 1;
        $i = $startPos + 1;
        $len = strlen($sql);
        while ($i < $len && $depth > 0) {
            if ($sql[$i] === '(') 
            {
                $depth++;
            }
            elseif ($sql[$i] === ')') 
            {
                $depth--;
            }
            $i++;
        }

        if ($depth !== 0) {
            throw new DatabaseConversionException("Unbalanced parentheses in CREATE TABLE statement.");
        }

        $columnsSection = substr($sql, $startPos + 1, $i - $startPos - 2);
        $lines = preg_split('/,(?![^\(]*\))/m', $columnsSection);

        $newLines = [];
        $primaryKeyColumnFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') 
            {
                continue;
            }

            // Column definition
            if (preg_match('/^`?([^`\s]+)`?\s+([a-zA-Z0-9_\(\)]+)(.*)$/i', $line, $colMatches)) /* NOSONAR */ {
                $columnName = $this->quoteIdentifier($colMatches[1], 'sqlite');
                $columnType = strtolower(trim($colMatches[2]));
                $columnDefinition = trim($colMatches[3]);

                $translatedType = $this->translateFieldType($columnType, 'mysql', 'sqlite');

                // AUTO_INCREMENT di SQLite hanya INTEGER PRIMARY KEY AUTOINCREMENT
                if (stripos($columnDefinition, 'AUTO_INCREMENT') !== false) {
                    if (stripos($columnType, 'int') !== false && stripos($columnDefinition, 'PRIMARY KEY') !== false && !$primaryKeyColumnFound) {
                        $translatedType = 'INTEGER';
                        $columnDefinition = str_ireplace('AUTO_INCREMENT', 'AUTOINCREMENT', $columnDefinition);
                        $primaryKeyColumnFound = true;
                    } else {
                        $columnDefinition = str_ireplace('AUTO_INCREMENT', '', $columnDefinition);
                    }
                }

                // BOOLEAN (TINYINT(1)) -> INTEGER
                if (preg_match('/tinyint\s*\(\s*1\s*\)/i', $columnType)) {
                    $translatedType = 'INTEGER';
                    $columnDefinition = str_ireplace("DEFAULT '1'", 'DEFAULT 1', $columnDefinition);
                    $columnDefinition = str_ireplace("DEFAULT '0'", 'DEFAULT 0', $columnDefinition);
                }

                // ENUM dan SET -> TEXT
                if (stripos($columnType, 'enum') !== false || stripos($columnType, 'set') !== false) {
                    $translatedType = 'TEXT';
                }

                // Hapus ON UPDATE CURRENT_TIMESTAMP
                if (stripos($columnDefinition, 'ON UPDATE CURRENT_TIMESTAMP') !== false) {
                    $columnDefinition = str_ireplace('ON UPDATE CURRENT_TIMESTAMP', '', $columnDefinition);
                }

                $newLines[] = $columnName . ' ' . $translatedType . ' ' . trim($columnDefinition);
            }
            // PRIMARY KEY atau UNIQUE KEY table-level
            elseif (preg_match('/^(PRIMARY KEY|UNIQUE KEY)\s*`?([^`]*)`?\s*\((.+)\)/i', $line, $keyMatches)) {
                $keyType = strtoupper($keyMatches[1]);
                $keyColumns = preg_replace('/`([^`]+)`/', '"$1"', $keyMatches[3]);

                if ($keyType === 'PRIMARY KEY' && !$primaryKeyColumnFound) {
                    $newLines[] = 'PRIMARY KEY (' . $keyColumns . ')';
                } elseif ($keyType === 'UNIQUE KEY') {
                    $newLines[] = 'UNIQUE (' . $keyColumns . ')';
                }
            }
            // Lainnya
            else {
                $line = preg_replace_callback('/`([^`]+)`/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'sqlite');
                }, $line);
                $newLines[] = $line;
            }
        }

        // Finalisasi statement CREATE TABLE
        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode(",\n    ", $newLines) . "\n)";

        // Bersihkan ENGINE, CHARSET, COLLATE, COMMENT
        $finalSql = preg_replace('/ENGINE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/DEFAULT\s+CHARSET\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/COLLATE\s*=\s*[a-zA-Z0-9_]+/i', '', $finalSql); // NOSONAR
        $finalSql = preg_replace('/COMMENT\s+\'.*?\'/i', '', $finalSql); // NOSONAR

        $finalSql = str_replace('"PRIMARY" KEY', 'PRIMARY KEY', $finalSql); // Fix PostgreSQL's PRIMARY KEY quoting
        $finalSql = str_replace('"UNIQUE" KEY', 'UNIQUE', $finalSql); // Fix PostgreSQL's UNIQUE KEY quoting
        $finalSql = str_replace('"FOREIGN" KEY', 'FOREIGN KEY', $finalSql); // Fix PostgreSQL's FOREIGN KEY quoting
        $finalSql = str_replace('"CHECK" (', 'CHECK (', $finalSql); // Fix PostgreSQL's CHECK constraint quoting
        $finalSql = str_replace('"DEFAULT" ', 'DEFAULT ', $finalSql); // Fix PostgreSQL's DEFAULT quoting
        $finalSql = str_replace('"NOT" NULL', 'NOT NULL', $finalSql); // Fix PostgreSQL's NOT NULL quoting
        $finalSql = str_replace('"NULL"', 'NULL', $finalSql); // Fix PostgreSQL's NULL quoting
        $finalSql = str_replace('"REFERENCES"', 'REFERENCES', $finalSql); // Fix PostgreSQL's REFERENCES quoting
        $finalSql = str_replace('"ON" DELETE', 'ON DELETE', $finalSql); // Fix PostgreSQL's ON DELETE quoting
        $finalSql = str_replace('"ON" UPDATE', 'ON UPDATE', $finalSql); // Fix PostgreSQL's ON UPDATE quoting
        $finalSql = str_replace('"USING"', 'USING', $finalSql); // Fix PostgreSQL's USING quoting
        $finalSql = str_replace('"WITH"', 'WITH', $finalSql); // Fix PostgreSQL's WITH quoting
        $finalSql = str_replace('"CONSTRAINT"', 'CONSTRAINT', $finalSql); // Fix PostgreSQL's CONSTRAINT quoting
        $finalSql = str_replace('`', '"', $finalSql); // Convert backticks to double quotes for SQLite

        $finalSql = $this->fixLines($finalSql);

        return trim($finalSql) . ';';
    }

    /**
     * Translates a PostgreSQL CREATE TABLE statement to MySQL,
     * focusing only on converting CHARACTER VARYING(n) to VARCHAR(n),
     * and preserving other attributes like NULL/NOT NULL/DEFAULT.
     *
     * @param string $sql The PostgreSQL CREATE TABLE statement.
     * @return string The translated MySQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function postgresqlToMySQL($sql) // NOSONAR
    {
        //$sql = trim(preg_replace('/\s+/', ' ', $sql)); // Normalize whitespace

        $posOpen = strpos(strtoupper($sql), '(');
        if ($posOpen === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: missing opening parenthesis.");
        }

        $len = strlen($sql);
        $parenCount = 0;
        $posClose = false;
        for ($i = $posOpen; $i < $len; $i++) {
            if ($sql[$i] === '(') 
            {
                $parenCount++;
            }
            elseif ($sql[$i] === ')') 
            {
                $parenCount--;
            }
            if ($parenCount === 0) {
                $posClose = $i;
                break;
            }
        }
        if ($posClose === false) {
            throw new DatabaseConversionException("Invalid CREATE TABLE: unbalanced parentheses.");
        }

        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)\2/i', substr($sql, 0, $posOpen), $matches)) {
            throw new DatabaseConversionException("Cannot parse table name.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'mysql');

        $columnsDef = trim(substr($sql, $posOpen + 1, $posClose - $posOpen - 1));

        // Parse column definitions considering nested parentheses
        $lines = [];
        $buffer = '';
        $parenLevel = 0;
        for ($i = 0; $i < strlen($columnsDef); $i++) {
            $char = $columnsDef[$i];
            if ($char === '(') 
            {
                $parenLevel++;
            }
            elseif ($char === ')') 
            {
                $parenLevel--;
            }

            if ($char === ',' && $parenLevel === 0) {
                $lines[] = trim($buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }
        if (trim($buffer) !== '') {
            $lines[] = trim($buffer);
        }

        $newLines = [];
        $primaryKeys = [];

        foreach ($lines as $line) {
            // Convert "character varying(n)" to VARCHAR(n)
            if (preg_match('/^("?)([^"\s]+)\1\s+character varying\s*\((\d+)\)(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $length = $colMatch[3];
                $rest = trim($colMatch[4]);
                $newLines[] = "{$colName} VARCHAR({$length}) {$rest}";
            }
            // Convert BIGSERIAL/serial to BIGINT/INT AUTO_INCREMENT and detect inline PK
            elseif (preg_match('/^("?)([^"\s]+)\1\s+(BIG)?SERIAL(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $isBig = !empty($colMatch[3]);
                $rest = strtoupper(trim($colMatch[4]));
                $hasPK = strpos($rest, 'PRIMARY KEY') !== false;
                $type = $isBig ? 'BIGINT' : 'INT';
                $mod = 'NOT NULL AUTO_INCREMENT';
                $rest = str_ireplace('PRIMARY KEY', '', $rest);
                $rest = trim($rest);
                $rest = preg_replace('/\bNOT NULL\b/i', '', $rest);
                $rest = trim($rest);
                $newLines[] = "{$colName} {$type} {$mod}" . ($rest ? " {$rest}" : "");
                if ($hasPK) {
                    $primaryKeys[] = $colMatch[2];
                }
            }
            // Table-level PRIMARY KEY
            elseif (preg_match('/^PRIMARY KEY\s*\((.*?)\)/i', $line, $pkMatch)) {
                $keyColumns = trim($pkMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) /* NOSONAR */ {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $keyColumns);
                $primaryKeys = array_merge($primaryKeys, array_map(function($v) {
                    return trim($v, '`" ');
                }, explode(',', $keyColumns)));
                $newLines[] = "PRIMARY KEY ($keyColumns)";
            }
            // Convert BOOLEAN to TINYINT(1)
            elseif (preg_match('/^("?)([^"\s]+)\1\s+BOOLEAN(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $rest = trim($colMatch[3]);
                $rest = preg_replace('/DEFAULT\s+TRUE/i', "DEFAULT TRUE", $rest);
                $rest = preg_replace('/DEFAULT\s+FALSE/i', "DEFAULT FALSE", $rest);
                $newLines[] = "{$colName} TINYINT(1) {$rest}";
            }
            // Convert TIMESTAMP WITH TIME ZONE/WITHOUT TIME ZONE
            elseif (preg_match('/^("?)([^"\s]+)\1\s+TIMESTAMP( WITH TIME ZONE| WITHOUT TIME ZONE)?(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $rest = trim($colMatch[4]);
                $type = (isset($colMatch[3]) && stripos($colMatch[3], 'WITHOUT') !== false) ? 'DATETIME' : 'TIMESTAMP';
                $newLines[] = "{$colName} {$type} {$rest}";
            }
            // Convert JSONB to JSON
            elseif (preg_match('/^("?)([^"\s]+)\1\s+JSONB(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $rest = trim($colMatch[3]);
                $newLines[] = "{$colName} JSON {$rest}";
            }
            // Convert TEXT, DATE, etc.
            elseif (preg_match('/^("?)([^"\s]+)\1\s+([A-Z ]+)(.*)$/i', $line, $colMatch)) {
                $colName = $this->quoteIdentifier($colMatch[2], 'mysql');
                $type = strtoupper(trim($colMatch[3]));
                $rest = trim($colMatch[4]);
                $rest = preg_replace('/\bNOT NULL\b\s*(?=.*\bNOT NULL\b)/i', '', $rest);
                $newLines[] = "{$colName} {$type} {$rest}";
            }
            // Other constraints or lines
            else {
                // Replace PostgreSQL-style identifiers with MySQL-style
                $line = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $line);
                // Add comma if not present and not last line
                $line = rtrim($line, ',');
                $newLines[] = $line;
            }
        }

        // Add commas between all lines except the last
        $count = count($newLines);
        foreach ($newLines as $i => &$l) {
            $l = rtrim($l, ',');
            $l = rtrim($l); // Remove trailing spaces
            if ($i < $count - 1) {
                $l .= ',';
            }
        }
        unset($l);

        $finalSql = "CREATE TABLE {$ifNotExists}{$tableName} (\n    " . implode("\n    ", $newLines) . "\n)";
        $finalSql .= "\nENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        // Type translations
        $replacements = [
            'TIMESTAMPTZ' => 'TIMESTAMP',
            'TIMESTAMP WITH TIME ZONE' => 'TIMESTAMP',
            'TIMESTAMP WITHOUT TIME ZONE' => 'DATETIME',
            'BOOLEAN' => 'TINYINT(1)',
            'JSONB' => 'JSON'
        ];
        $finalSql = str_ireplace(array_keys($replacements), array_values($replacements), $finalSql);

        // SERIAL and BIGSERIAL to BIGINT AUTO_INCREMENT — remove duplicate PRIMARY KEY if needed
        $finalSql = preg_replace_callback('/`(\w+)`\s+(BIG)?SERIAL\s+(.*)/i', function ($matches) {
            $col = "`{$matches[1]}`";
            $rest = strtoupper($matches[3]);
            $hasPK = strpos($rest, 'PRIMARY KEY') !== false;
            $type = 'BIGINT';
            $mod = 'NOT NULL AUTO_INCREMENT';
            $rest = str_ireplace('PRIMARY KEY', '', $rest); // remove if exists here
            $rest = trim($rest);
            return $hasPK
                ? "{$col} {$type} {$mod}" // no need to keep PRIMARY KEY inline
                : "{$col} {$type} {$mod} {$rest}";
        }, $finalSql);

        // Final formatting
        $finalSql = $this->fixLines($finalSql);
        return trim($finalSql) . ";";
    }

    /**
     * Translates a CREATE TABLE statement from SQLite to MySQL.
     *
     * @param string $sql The SQLite CREATE TABLE statement.
     * @return string The translated MySQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function sqliteToMySQL($sql) // NOSONAR
    {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces

        // Extract table name
        if (!preg_match('/CREATE TABLE (IF NOT EXISTS\s+)?("?)([^"\s]+)("?)\s*\((.*)\)/is', $sql, $matches)) {
            throw new DatabaseConversionException("Invalid SQLite CREATE TABLE statement format.");
        }
        $ifNotExists = isset($matches[1]) ? 'IF NOT EXISTS ' : '';
        $tableName = $this->quoteIdentifier($matches[3], 'mysql');
        $columnsAndConstraints = trim($matches[5]);

        // Split lines by commas outside parentheses
        $lines = $this->splitSqlByCommaRespectingParentheses($columnsAndConstraints);
        $newLines = [];
        $primaryKeyColumn = null;

        // Check if there is a PRIMARY KEY at the table level
        foreach ($lines as $line) {
            if (preg_match('/^PRIMARY KEY\s*\(\s*"([^"]+)"\s*\)/i', trim($line), $pkMatch)) {
                $primaryKeyColumn = $pkMatch[1];
                break;
            }
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) 
            {
                continue; // Skip empty lines
            }

            // Column definition
            if (preg_match('/^("?)([^"\s]+)\1\s+([a-zA-Z0-9_()]+)(.*)$/i', $line, $colMatches)) /* NOSONAR */ {
                $columnRaw = $colMatches[2];
                $columnName = $this->quoteIdentifier($columnRaw, 'mysql');
                $columnType = strtolower(trim($colMatches[3]));
                $columnDefinition = trim($colMatches[4]);

                $translatedType = $this->translateFieldType($columnType, 'sqlite', 'mysql');

                // INTEGER → BIGINT(20), REAL → DOUBLE
                $translatedType = str_ireplace('INTEGER', 'BIGINT(20)', $translatedType);
                $translatedType = str_ireplace('REAL', 'DOUBLE', $translatedType);

                // BOOLEAN
                if (preg_match('/BOOLEAN/i', $columnType) || preg_match('/DEFAULT\s+[01]/i', $columnDefinition)) {
                    $translatedType = 'TINYINT(1)';
                    $columnDefinition = str_ireplace('DEFAULT 1', 'DEFAULT TRUE', $columnDefinition);
                    $columnDefinition = str_ireplace('DEFAULT 0', 'DEFAULT FALSE', $columnDefinition);
                }

                // JSON detection
                if ($this->strContains(strtoupper($columnType), 'TEXT') && $this->strContains(strtoupper($columnRaw), 'JSON')) {
                    $translatedType = 'JSON';
                }

                // AUTO_INCREMENT for PRIMARY KEY column
                if ($columnRaw === $primaryKeyColumn && preg_match('/BIGINT\(20\)/i', $translatedType) && preg_match('/NOT NULL/i', $columnDefinition)) {
                    $columnDefinition = preg_replace('/\bNOT NULL\b/i', '', $columnDefinition);
                    $columnDefinition = trim($columnDefinition);
                    $columnDefinition .= ' AUTO_INCREMENT';
                }

                $definition = trim("$columnName $translatedType $columnDefinition");
                // Remove duplicate NOT NULL
                $definition = preg_replace('/\bNOT NULL\b\s*(?=.*\bNOT NULL\b)/i', '', $definition);
                $definition = preg_replace('/,+$/', '', $definition); // Remove trailing commas
                $newLines[] = preg_replace('/\s+/', ' ', $definition);
            } elseif (preg_match('/^PRIMARY KEY\s*\((.*?)\)/i', $line, $pkMatch)) {
                // Handle table-level PRIMARY KEY if not handled above
                $keyColumns = trim($pkMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $keyColumns);
                $newLines[] = "PRIMARY KEY ($keyColumns)";
            } elseif (preg_match('/^UNIQUE\s*\((.*?)\)/i', $line, $uqMatch)) {
                $keyColumns = trim($uqMatch[1]);
                $keyColumns = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $keyColumns);
                $newLines[] = "UNIQUE KEY ($keyColumns)";
            } else {
                // Other constraints
                $line = preg_replace_callback('/"([^"]+)"/', function ($m) {
                    return $this->quoteIdentifier($m[1], 'mysql');
                }, $line);
                $line = preg_replace('/,+$/', '', $line); // Remove trailing commas
                $newLines[] = $line;
            }
        }

        // Add commas between all lines except the last
        $count = count($newLines);
        foreach ($newLines as $i => &$l) {
            $l = rtrim($l, ',');
            $l = rtrim($l); // Remove trailing spaces
            if ($i < $count - 1) {
                $l .= ',';
            }
        }
        unset($l);

        $finalSql = "CREATE TABLE " . $ifNotExists . $tableName . " (\n    " . implode("\n    ", $newLines) . "\n)";
        $finalSql .= "\nENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $finalSql = preg_replace('/`PRIMARY` KEY/i', 'PRIMARY KEY', $finalSql); // NOSONAR
        $finalSql = str_replace('"', '`', $finalSql);

        return $this->fixLine(trim($finalSql));
    }

    /**
     * Translates a CREATE TABLE statement from PostgreSQL to SQLite.
     *
     * @param string $sql The PostgreSQL CREATE TABLE statement.
     * @return string The translated SQLite CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function postgresqlToSQLite($sql)
    {
        return $this->mysqlToSQLite($this->postgresqlToMySQL($sql));
    }
    
    /**
     * Translates a CREATE TABLE statement from SQLite to PostgreSQL.
     *
     * @param string $sql The SQLite CREATE TABLE statement.
     * @return string The translated PostgreSQL CREATE TABLE statement.
     * @throws DatabaseConversionException If the SQL format is invalid.
     */
    public function sqliteToPostgreSQL($sql)
    {
        return $this->mysqlToPostgreSQL($this->sqliteToMySQL($sql));
    }

    /**
     * Splits a SQL column/constraint string into separate parts using commas,
     * while respecting commas that are inside parentheses (e.g., data types or expressions).
     *
     * @param string $sql The part inside CREATE TABLE (...) to be split.
     * @return array An array of lines representing column or constraint definitions.
     */
    public function splitSqlByCommaRespectingParentheses($sql)
    {
        $result = [];
        $buffer = '';
        $parenLevel = 0;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            if ($char === '(') {
                $parenLevel++;
            } elseif ($char === ')') {
                $parenLevel--;
            } elseif ($char === ',' && $parenLevel === 0) {
                $result[] = $buffer;
                $buffer = '';
                continue;
            }
            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $result[] = $buffer;
        }

        return $result;
    }

    /**
     * Translates a CREATE TABLE statement from a source dialect to a target dialect.
     *
     * @param string $sql The CREATE TABLE statement.
     * @param string $sourceDialect The source database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @param string $targetDialect The target database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The translated CREATE TABLE statement.
     * @throws DatabaseConversionException If an unsupported translation is requested or SQL format is invalid.
     */
    public function translateCreateTable($sql, $sourceDialect, $targetDialect) // NOSONAR
    {
        $sourceDialect = $this->normalizeDialect($sourceDialect);
        $targetDialect = $this->normalizeDialect($targetDialect);

        if ($sourceDialect === $targetDialect) {
            return $sql; // No translation needed
        }

        // Perform the core dialect-to-dialect translation
        $result = $this->doTranslateCreateTable($sql, $sourceDialect, $targetDialect);

        // --- Applying common post-translation fixes ---
        // These fixes address general syntax inconsistencies that might arise across various conversions.
        // For more complex or dialect-specific transformations, it's better to implement them
        // within the dedicated `mysqlToPostgreSQL`, `postgresqlToMySQL`, etc., methods.

        // Ensures PRIMARY KEY is always followed by NOT NULL if it was implicitly NULL
        $result = str_ireplace(' PRIMARY KEY NULL', ' PRIMARY KEY NOT NULL', $result);

        // Removes redundant spaces around parentheses for data types like NVARCHAR
        $result = str_ireplace(' NVARCHAR (', ' NVARCHAR(', $result);
        $result = str_ireplace(' VARCHAR (', ' VARCHAR(', $result);
        $result = str_ireplace(' CHARACTER VARYING (', ' CHARACTER VARYING(', $result);
        $result = str_ireplace(' CHAR (', ' CHAR(', $result);
        $result = str_ireplace(' NCHAR (', ' NCHAR(', $result);

        $result = str_ireplace(' INT (', ' INT(', $result);
        $result = str_ireplace(' INTEGER (', ' INTEGER(', $result);
        $result = str_ireplace(' TINYINT (', ' TINYINT(', $result);
        $result = str_ireplace(' SMALLINT (', ' SMALLINT(', $result);
        $result = str_ireplace(' MEDIUMINT (', ' MEDIUMINT(', $result);
        $result = str_ireplace(' BIGINT (', ' BIGINT(', $result);

        $result = str_ireplace(' DECIMAL (', ' DECIMAL(', $result);
        $result = str_ireplace(' NUMERIC (', ' NUMERIC(', $result);
        $result = str_ireplace(' FLOAT (', ' FLOAT(', $result);
        $result = str_ireplace(' DOUBLE (', ' DOUBLE(', $result);
        $result = str_ireplace(' REAL (', ' REAL(', $result);

        $result = str_ireplace(' BOOLEAN (', ' BOOLEAN(', $result);

        // Specific conversion for BOOLEAN(11) which might come from MySQL TINYINT(1) export and needs to be INTEGER(11) for some targets
        $result = str_ireplace(' BOOLEAN(11)', ' INTEGER(11)', $result);
        
        // Add more similar patterns as needed.
        return $result;
    }

    /**
     * Translates a CREATE TABLE statement from a source dialect to a target dialect.
     *
     * @param string $sql The CREATE TABLE statement.
     * @param string $sourceDialect The source database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @param string $targetDialect The target database dialect (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The translated CREATE TABLE statement.
     * @throws DatabaseConversionException If an unsupported translation is requested or SQL format is invalid.
     */
    public function doTranslateCreateTable($sql, $sourceDialect, $targetDialect) // NOSONAR
    {
        switch ($sourceDialect . 'To' . ucfirst($targetDialect)) {
            case 'mysqlToPostgresql':
                return $this->mysqlToPostgreSQL($sql);
            case 'mysqlToSqlite':
                return $this->mysqlToSQLite($sql);
            case 'postgresqlToMysql':
                return $this->postgresqlToMySQL($sql);
            case 'postgresqlToSqlite':
                return $this->postgresqlToSQLite($sql);
            case 'sqliteToMysql':
                return $this->sqliteToMySQL($sql);
            case 'sqliteToPostgresql':
                return $this->sqliteToPostgreSQL($sql);
            default:
                throw new DatabaseConversionException("Unsupported CREATE TABLE translation: from " . $sourceDialect . " to " . $targetDialect);
        }
    }

    /**
     * Returns the canonical name for a given database dialect alias.
     *
     * This method normalizes various aliases for common database dialects
     * into their standard (canonical) names. For example, 'pgsql' and 'postgres'
     * both map to 'postgresql', and 'mariadb' maps to 'mysql'.
     *
     * Supported aliases:
     * - mysql, mariadb → mysql
     * - postgres, pgsql, postgresql → postgresql
     * - sqlite, sqlite3 → sqlite
     *
     * @param string $dialect The input dialect name (case-insensitive).
     * @return string The canonical dialect name.
     *
     * @throws DatabaseConversionException If the dialect is not supported.
     */
    public function normalizeDialect($dialect)
    {
        $dialect = strtolower(trim($dialect));

        $mapping = [
            'mysql'     => 'mysql',
            'mariadb'   => 'mysql',
            'postgres'  => 'postgresql',
            'pgsql'     => 'postgresql',
            'postgresql'=> 'postgresql',
            'sqlite'    => 'sqlite',
            'sqlite3'   => 'sqlite',
        ];

        if (isset($mapping[$dialect])) {
            return $mapping[$dialect];
        }

        throw new DatabaseConversionException("Unsupported database dialect: " . $dialect);
    }

    public function normalizeCreateTableSql($sql)
    {
        $sql = trim($sql);
        $sql = str_replace("`", "", $sql); // Remove backticks for standard SQL
        $sql = preg_replace('/\s+/', ' ', $sql); // Normalize spaces
        $sql = preg_replace('/\s*;\s*$/', '', $sql); // Remove trailing semicolon if present
        return $sql;
    }

    /**
     * Checks if a string contains a specific substring.
     *
     * @param string $haystack The string to search within.
     * @param string $needle   The substring to search for.
     * @return bool            True if the substring is found, false otherwise.
     */
    public function strContains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * Cleans up a multi-line SQL statement without altering its line structure.
     *
     * @param string $sql The SQL string to clean.
     * @return string The cleaned SQL string with preserved line breaks.
     */
    public function fixLines($sql)
    {
        // Normalize line endings (Windows/Linux/macOS compatibility)
        $sql = str_replace("\n", "\r\n", $sql);
        $sql = str_replace("\r\r\n", "\r\n", $sql);
        $sql = str_replace("\r", "\r\n", $sql);
        $sql = str_replace("\r\n\n", "\r\n", $sql);
        $sql = trim($sql);

        // Split into individual lines
        $lines = explode("\r\n", $sql);
        $fixedLines = [];

        foreach ($lines as $line) {
            $line = $this->fixLine($line);
            if (trim($line) !== '') {
                $fixedLines[] = $line;
            }
        }

        return implode("\r\n", $fixedLines);
    }

    /**
     * Cleans a single line of SQL by removing comments and excess whitespace.
     *
     * @param string $line A single line of SQL.
     * @return string The cleaned line.
     */
    public function fixLine($line)
    {
        // Remove single-line comments
        $line = preg_replace('/--.*$/', '', $line);

        // Remove inline multi-line comments
        $line = preg_replace('/\/\*.*?\*\//s', '', $line);

        // Remove spaces before commas
        $line = preg_replace('/\s+,/', ',', $line);

        return rtrim($line);
    }
}
