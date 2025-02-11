<?php

namespace MagicObject\Util\Database;

use Exception;

/**
 * Class PicoQueryTranslator
 *
 * This class is responsible for translating database query structures between different SQL dialects.
 * It takes SQL queries written in one dialect (e.g., MySQL) and converts them into another dialect (e.g., PostgreSQL, SQLite).
 * The class handles differences in syntax, keywords, functions, and other database-specific features.
 *
 * Key functionalities of this class include:
 * - Translating data types between different SQL flavors.
 * - Adjusting query syntax to match the conventions of different database systems.
 * - Converting SQL-specific expressions like `AUTO_INCREMENT` to equivalent expressions in other databases.
 * 
 * This class is typically used when migrating databases or working with systems that need to support multiple database engines.
 *
 * @package MagicObject\Util\Database
 */
class PicoQueryTranslator // NOSONAR
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

    public function __construct() {
        $this->dbToSqlite = [
            "int" => "INTEGER",
            "tinyint(1)" => "BOOLEAN", // NOSONAR
            "tinyint" => "INTEGER",
            "smallint" => "INTEGER",
            "mediumint" => "INTEGER",
            "bigint" => "INTEGER",
            "real" => "REAL",
            "float" => "REAL",
            "double" => "REAL",
            "decimal" => "REAL",
            "nvarchar" => "NVARCHAR",
            "varchar" => "NVARCHAR",
            "character varying" => "NVARCHAR", // NOSONAR
            "char" => "NVARCHAR",
            "tinytext" => "TEXT",
            "mediumtext" => "TEXT",
            "longtext" => "TEXT",
            "text" => "TEXT",
            "datetime" => "DATETIME",
            "timestamp" => "TIMESTAMP",
            "date" => "DATE",
            "time" => "TIME",
            "year" => "INTEGER",
            "boolean" => "INTEGER",
            "json" => "TEXT",
            "jsonb" => "TEXT",
            "integer" => "INTEGER",
            "serial" => "INTEGER",
            "bigserial" => "INTEGER",
            "double precision" => "REAL",
            "timestamptz" => "TIMESTAMP"
        ];

        $this->dbToMySQL = [
            "bigint" => "BIGINT",
            "mediumint" => "MEDIUMINT",
            "smallint" => "SMALLINT",
            "integer" => "INT",
            "double" => "DOUBLE",
            "float" => "FLOAT",
            "real" => "DOUBLE",
            "decimal" => "DECIMAL",
            "numeric" => "NUMERIC",
            "tinytext" => "TINYTEXT",
            "mediumtext" => "MEDIUMTEXT",
            "longtext" => "LONGTEXT",
            "text" => "TEXT",
            "nvarchar" => "VARCHAR",
            "varchar" => "VARCHAR",
            "character varying" => "VARCHAR",
            "tinyint(1)" => "TINYINT(1)", // NOSONAR
            "tinyint" => "TINYINT",
            "boolean" => "TINYINT(1)",
            "int" => "INT",
            "datetime" => "DATETIME",
            "date" => "DATE",
            "timestamptz" => "TIMESTAMP",
            "timestamp with time zone" => "TIMESTAMP",
            "timestamp without time zone" => "DATETIME",
            "timestamp" => "TIMESTAMPTZ",
            "json" => "JSON",
            "enum" => "ENUM",
            "set" => "SET",
            "char" => "CHAR"
        ];

        $this->dbToPostgreSQL = [
            "bigint" => "BIGINT",
            "mediumint" => "INTEGER",
            "smallint" => "INTEGER",
            "tinyint(1)" => "BOOLEAN",
            "tinyint" => "INTEGER",
            "integer" => "INTEGER",
            "real" => "REAL",
            "longtext" => "TEXT",
            "mediumtext" => "TEXT",
            "smalltext" => "TEXT",
            "tinytext" => "TEXT",
            "text" => "TEXT",
            "character varying" => "CHARACTER VARYING", // NOSONAR
            "nvarchar" => "CHARACTER VARYING",
            "varchar" => "CHARACTER VARYING",
            "char" => "CHARACTER",
            "boolean" => "BOOLEAN",
            "datetime" => "TIMESTAMP WITHOUT TIME ZONE",
            "date" => "DATE",
            "timestamptz" => "TIMESTAMP WITH TIME ZONE",
            "timestamp" => "TIMESTAMP WITH TIME ZONE",
            "time" => "TIME",
            "json" => "JSONB"
        ];
    }

    /**
     * Replaces all occurrences of a substring in the given string.
     * 
     * This function uses a regular expression to replace all matches of the search pattern with the specified replacement.
     * 
     * @param string|null $str The string to search and replace in. If null, returns null.
     * @param string $search The substring to search for.
     * @param string $replacement The substring to replace the search pattern with.
     * @return string|null The modified string, or null if the input string is null.
     */
    private function replaceAll($str, $search, $replacement) {
        return str_ireplace($search, $replacement, $str);
    }

    /**
     * Translates the provided SQL query to a specified target database format.
     * 
     * This method converts the provided SQL string into a format compatible with the specified target database (MySQL, PostgreSQL, or SQLite),
     * ensuring that all relevant data types and structures are adapted.
     *
     * @param string $value The SQL query to be translated.
     * @param string $targetType The target database type ('mysql', 'pgsql', 'sqlite').
     * @return string The translated SQL query in the target database format.
     */
    public function translate($value, $targetType) {
        $dropTables = [];
        $tableInfo = $this->extractDropTableQueries($value, $targetType);
        
        foreach ($tableInfo as $table) {
            $dropTables[] = "-- DROP TABLE IF EXISTS " . $table['table'] . ";";
        }

        $value = $this->replaceAll($value, '`', '');
        $value = $this->replaceAll($value, ' timestamp with time zone', ' timestamptz');
        $value = $this->replaceAll($value, ' timestamp without time zone', ' timestamp');
        $value = $this->replaceAll($value, ' character varying', ' varchar');
        $value = $this->replaceAll($value, ' COLLATE pg_catalog."default"', '');
        $value = $this->replaceAll($value, ' TINYINT(1)', ' boolean');
        
        $tableParser = new PicoTableParser(); // Assuming this is a predefined class in your code
        $tableParser->parseAll($value);
        $tables = $tableParser->getResult();
        
        $lines = [];
        foreach ($tables as $table) {
            $convertedTable = $this->convertQuery($table, $targetType);
            $lines[] = $convertedTable;
            $lines[] = '';
        }

        if (!empty($dropTables)) {
            $dropTables[] = "\r\n\r\n";
        }

        return implode("\r\n", $dropTables) . implode("\r\n", $lines);
    }

    /**
     * Converts a table schema to a query compatible with the specified database type.
     * 
     * This method takes the parsed table schema and converts it to the corresponding SQL syntax for the target database.
     * 
     * @param array $table The table schema to convert.
     * @param string $targetType The target database type ('mysql', 'pgsql', 'sqlite').
     * @return string The converted table schema in the target database format.
     */
    private function convertQuery($table, $targetType) {
        if ($this->isSQLite($targetType)) {
            return $this->toSqliteOut($table, $targetType);
        } elseif ($this->isMySQL($targetType)) {
            return $this->toMySQLOut($table, $targetType);
        } elseif ($this->isPGSQL($targetType)) {
            return $this->toPostgreSQLOut($table, $targetType);
        }
    }

    /**
     * Converts a table schema to SQLite-specific SQL syntax.
     * 
     * This method adapts the provided table schema to SQLite format, including data type conversions and primary key handling.
     * 
     * @param array $table The table schema to convert.
     * @param string $targetType The target database type ('sqlite').
     * @return string The converted table schema in SQLite format.
     */
    private function toSqliteOut($table, $targetType) {
        $sqliteTable = [
            'tableName' => $table['tableName'],
            'primaryKey' => $table['primaryKey'],
            'columns' => array_map(function($column) {
                $column['Type'] = $this->toSqliteType($column['Type'], $column['Length']);
                return $column;
            }, $table['columns'])
        ];
        return $this->toSqliteTable($sqliteTable, $targetType);
    }

    /**
     * Converts a table schema to MySQL-specific SQL syntax.
     * 
     * This method adapts the provided table schema to MySQL format, including data type conversions and primary key handling.
     * 
     * @param array $table The table schema to convert.
     * @param string $targetType The target database type ('mysql').
     * @return string The converted table schema in MySQL format.
     */
    private function toMySQLOut($table, $targetType) {
        $mysqlTable = [
            'tableName' => $table['tableName'],
            'primaryKey' => $table['primaryKey'],
            'columns' => array_map(function($column) {
                $column['Type'] = $this->toMySQLType($column['Type'], $column['Length']);
                return $column;
            }, $table['columns'])
        ];
        return $this->toMySQLTable($mysqlTable, $targetType);
    }

    /**
     * Converts a table schema to PostgreSQL-specific SQL syntax.
     * 
     * This method adapts the provided table schema to PostgreSQL format, including data type conversions and primary key handling.
     * 
     * @param array $table The table schema to convert.
     * @param string $targetType The target database type ('pgsql').
     * @return string The converted table schema in PostgreSQL format.
     */
    private function toPostgreSQLOut($table, $targetType) {
        $pgTable = [
            'tableName' => $table['tableName'],
            'primaryKey' => $table['primaryKey'],
            'columns' => array_map(function($column) {
                $column['Type'] = $this->toPostgreSQLType($column['Type'], $column['Length']);
                return $column;
            }, $table['columns'])
        ];
        return $this->toPostgreSQLTable($pgTable, $targetType);
    }

    
    /**
     * Converts a given table for SQLite target type.
     *
     * This method uses a common `toTable` function to convert a table for SQLite.
     *
     * @param string $sqliteTable The name of the SQLite table.
     * @param string $targetType The target database type (e.g., 'sqlite').
     * @return mixed The result of the `toTable` method.
     */
    private function toSqliteTable($sqliteTable, $targetType) {
        return $this->toTable($sqliteTable, $targetType);
    }

    /**
     * Converts a given table for MySQL target type.
     *
     * This method uses a common `toTable` function to convert a table for MySQL.
     *
     * @param string $mysqlTable The name of the MySQL table.
     * @param string $targetType The target database type (e.g., 'mysql').
     * @return mixed The result of the `toTable` method.
     */
    private function toMySQLTable($mysqlTable, $targetType) {
        return $this->toTable($mysqlTable, $targetType);
    }

    /**
     * Converts a given table for PostgreSQL target type.
     *
     * This method uses a common `toTable` function to convert a table for PostgreSQL.
     *
     * @param string $pgTable The name of the PostgreSQL table.
     * @param string $targetType The target database type (e.g., 'pgsql').
     * @return mixed The result of the `toTable` method.
     */
    private function toPostgreSQLTable($pgTable, $targetType) {
        return $this->toTable($pgTable, $targetType);
    }

    /**
     * Checks if the target type is MySQL or MariaDB.
     *
     * This method verifies if the target type matches MySQL or MariaDB.
     *
     * @param string $targetType The target database type.
     * @return bool Returns true if the target type is 'mysql' or 'mariadb', otherwise false.
     */
    private function isMySQL($targetType) {
        return $targetType === 'mysql' || $targetType === 'mariadb';
    }

    /**
     * Checks if the target type is PostgreSQL.
     *
     * This method verifies if the target type matches PostgreSQL or PGSQL.
     *
     * @param string $targetType The target database type.
     * @return bool Returns true if the target type is 'pgsql' or 'postgresql', otherwise false.
     */
    private function isPGSQL($targetType) {
        return $targetType === 'pgsql' || $targetType === 'postgresql';
    }

    /**
     * Checks if the target type is SQLite.
     *
     * This method verifies if the target type matches SQLite.
     *
     * @param string $targetType The target database type.
     * @return bool Returns true if the target type is 'sqlite', otherwise false.
     */
    private function isSQLite($targetType) {
        return $targetType === 'sqlite';
    }

    /**
     * Checks if the given column type is a real number (e.g., FLOAT, DOUBLE, REAL, DECIMAL).
     *
     * This method checks if the column type corresponds to a real number type.
     *
     * @param string $columnType The column type to check.
     * @return bool Returns true if the column type is a real number, otherwise false.
     */
    private function isReal($columnType) {
        return stripos($columnType, 'FLOAT') !== false ||
            stripos($columnType, 'DOUBLE') !== false ||
            stripos($columnType, 'REAL') !== false ||
            stripos($columnType, 'DECIMAL') !== false;
    }

    /**
     * Checks if the given column type is a boolean type (e.g., BOOLEAN, BOOL, TINYINT(1)).
     *
     * This method checks if the column type corresponds to a boolean type.
     *
     * @param string $columnType The column type to check.
     * @return bool Returns true if the column type is boolean, otherwise false.
     */
    private function isBoolean($columnType) {
        return strtoupper($columnType) == 'BOOLEAN' ||
            strtoupper($columnType) == 'BOOL' ||
            strtoupper($columnType) == 'TINYINT(1)';
    }

    /**
     * Converts a column type to the SQLite type format.
     * @param string $type The original column type.
     * @param int|null $length The column length (optional).
     * @return string The converted SQLite column type.
     */
    public function toSqliteType($type, $length = null) {
        $type = strtolower($type);

        if ($type === 'tinyint' && $length === 1) {
            return 'BOOLEAN';
        }

        $sqliteType = 'TEXT';
        foreach ($this->dbToSqlite as $key => $value) {
            if (strpos($type, strtolower($key)) === 0) {
                $sqliteType = $value;
                break;
            }
        }

        if (strpos(strtoupper($type), 'ENUM') !== false || strpos(strtoupper($type), 'SET') !== false) {
            $parsedEnum = $this->parseEnumValue($length);
            $sqliteType = 'NVARCHAR(' . ($parsedEnum['maxLength'] + 2) . ')';
        } elseif (($sqliteType === 'NVARCHAR' || $sqliteType === 'INT') && $length > 0) {
            $sqliteType .= "($length)";
        }

        return $sqliteType;
    }

    /**
     * Converts a column type to the MySQL type format.
     * @param string $type The original column type.
     * @param int|null $length The column length (optional).
     * @return string The converted MySQL column type.
     */
    public function toMySQLType($type, $length = null) {
        $type = strtolower($type);
        $mysqlType = 'TEXT';

        if ($this->isTinyInt1($type, $length)) {
            return 'TINYINT(1)';
        }

        if ($this->isInteger($type) && $length > 0) {
            return "{$type}($length)";
        }

        foreach ($this->dbToMySQL as $key => $value) {
            if (strpos($type, strtolower($key)) === 0) {
                $mysqlType = $value;
                break;
            }
        }

        $mysqlType = str_replace('TIMESTAMPTZ', 'TIMESTAMP', $mysqlType);

        if (strpos(strtoupper($type), 'ENUM') !== false) {
            $parsedEnum = $this->parseEnumValue($length);
            $mysqlType = 'ENUM(\'' . implode('\',\'', $parsedEnum['resultArray']) . '\')';
        } elseif (strpos(strtoupper($type), 'SET') !== false) {
            $parsedEnum = $this->parseEnumValue($length);
            $mysqlType = 'SET(\'' . implode('\',\'', $parsedEnum['resultArray']) . '\')';
        } elseif (strpos(strtoupper($type), 'DECIMAL') !== false) {
            $parsedNumeric = $this->parseNumericType($length);
            $mysqlType = 'DECIMAL(' . implode(', ', $parsedNumeric['resultArray']) . ')';
        } elseif (strpos(strtoupper($type), 'NUMERIC') !== false) {
            $parsedNumeric = $this->parseNumericType($length);
            $mysqlType = 'NUMERIC(' . implode(', ', $parsedNumeric['resultArray']) . ')';
        }

        if (($mysqlType === 'VARCHAR' || $mysqlType === 'CHAR') && $length > 0) {
            $mysqlType .= "($length)";
        }

        return $mysqlType;
    }

    /**
     * Converts a column type to the PostgreSQL type format.
     * @param string $type The original column type.
     * @param int|null $length The column length (optional).
     * @return string The converted PostgreSQL column type.
     */
    public function toPostgreSQLType($type, $length = null) {
        $type = strtolower($type);
        $pgType = 'TEXT';

        foreach ($this->dbToPostgreSQL as $key => $value) {
            if (strpos($type, strtolower($key)) === 0) {
                $pgType = $value;
                break;
            }
        }
        $pgType = strtoupper($pgType);
        if (strpos(strtoupper($type), 'TINYINT') !== false && $length == 1) {
            $pgType = 'BOOLEAN';
        } elseif (strpos(strtoupper($type), 'ENUM') !== false || strpos(strtoupper($type), 'SET') !== false) {
            $parsedEnum = $this->parseEnumValue($length);
            $pgType = 'CHARACTER VARYING(' . ($parsedEnum['maxLength'] + 2) . ')';
        } elseif (($pgType === 'CHARACTER VARYING' || $pgType === 'CHARACTER' || $pgType === 'CHAR') && $length > 0) {
            $pgType .= "($length)";
        }

        return $pgType;
    }

    /**
     * Parses an ENUM type value and extracts the values in single quotes, also calculating the maximum length.
     * @param string $inputString The ENUM values in a string format.
     * @return array An associative array containing the result array and maximum length of ENUM values.
     */
    private function parseEnumValue($inputString) {
        preg_match_all("/'([^']+)'/", $inputString, $matches);
        $resultArray = $matches[1];
        $maxLength = max(array_map('strlen', $resultArray));

        return ['resultArray' => $resultArray, 'maxLength' => $maxLength];
    }

    /**
     * Parses a numeric type value like DECIMAL(6,3), NUMERIC(10,2), etc.
     * @param string $inputString The numeric value in string format, like 'DECIMAL(6, 3)'.
     * @return array An associative array containing the type (e.g., DECIMAL) and the length (total digits) and scale (digits after the decimal point).
     */
    private function parseNumericType($inputString) {
        preg_match_all("/([A-Za-z0-9_]+)/", $inputString, $matches); // NOSONAR
        $resultArray = $matches[1];
        $maxLength = max(array_map('strlen', $resultArray));

        return ['resultArray' => $resultArray, 'maxLength' => $maxLength];
    }

    /**
     * Converts the table schema into a SQL CREATE TABLE statement for the specified database.
     * 
     * This method generates a SQL CREATE TABLE statement based on the provided table schema and target database type,
     * adapting column definitions, default values, and other relevant attributes.
     * 
     * @param array $table The table schema to convert.
     * @param string $targetType The target database type ('mysql', 'pgsql', 'sqlite').
     * @return string The SQL CREATE TABLE statement.
     */
    public function toTable($table, $targetType) {
        $tableName = $table['tableName'];
        
        $lines = [];

        // Fix table name if necessary
        $tableName = $this->fixTableName($tableName, $targetType);
        
        $lines[] = "CREATE TABLE IF NOT EXISTS $tableName (";
        $linesCol = [];

        foreach ($table['columns'] as $column) {
            $columnName = $this->fixColumnName($column['Field'], $targetType);
            $columnType = $column['Type'];
            $primaryKey = ($column['Field'] === $table['primaryKey']);
            $colDef = "\t$columnName $columnType";

            // Handle primary key
            if ($primaryKey) {
                $colDef .= " PRIMARY KEY";             
                if ($this->isAutoIncrement($column['AutoIncrement'], $targetType)) {
                    $colDef .= " AUTO_INCREMENT";
                }
                $column['Nullable'] = false;
            }
            // Handle nullability
            else if ($column['Nullable']) {
                $colDef .= " NULL";
            } else {
                $colDef .= " NOT NULL";
            }

            // Handle default values
            $defaultValue = $column['Default'];
            if ($this->hasDefaultValue($primaryKey, $defaultValue)) {
                $defaultValue = $this->replaceAll($defaultValue, '::character varying', '');
                $defaultValue = $this->fixDefaultValue($defaultValue, $targetType);
                if ($this->isNotEmpty($defaultValue)) {
                    $colDef .= $this->getDefaultData($defaultValue, $columnType);
                }
            }

            // Handle column comments
            if (isset($column['Comment'])) {
                $colDef .= " COMMENT '" . $this->addslashes($column['Comment']) . "'";
            }

            $linesCol[] = $colDef;
        }
        
        $lines[] = implode(",\r\n", $linesCol);
        $lines[] = ");";
        
        return implode("\r\n", $lines);
    }

    

    /**
     * Checks if the column type is TINYINT with a length of 1.
     *
     * This method checks if the given column type is 'TINYINT' and if its length
     * is exactly 1, which is commonly used to represent boolean values in certain databases.
     *
     * @param string $type The data type of the column (e.g., 'TINYINT').
     * @param int $length The length of the column (e.g., 1).
     * @return bool True if the type is 'TINYINT' and length is 1, otherwise false.
     */
    public function isTinyInt1($type, $length) {
        return strtoupper($type) === 'TINYINT' && $length == 1;
    }

    /**
     * Checks if the column type is an integer (e.g., TINYINT, SMALLINT, INT, BIGINT).
     *
     * @param string $type The column data type (e.g., 'INT', 'BIGINT').
     * @return bool True if the column type is an integer type, otherwise false.
     */
    public function isInteger($type) {
        $type = strtoupper($type);
        return $type === 'TINYINT' ||
               $type === 'SMALLINT' ||
               $type === 'MEDIUMINT' ||
               $type === 'BIGINT' ||
               $type === 'INTEGER' ||
               $type === 'INT';
    }

    /**
     * Determines if a column is auto-incremented for MySQL databases.
     *
     * @param bool $autoIncrement Whether the column is set to auto-increment.
     * @param string $targetType The target database type (e.g., 'mysql', 'mariadb').
     * @return bool True if the column is auto-incremented in MySQL or MariaDB, otherwise false.
     */
    public function isAutoIncrement($autoIncrement, $targetType) {
        return $this->isMySQL($targetType) && $autoIncrement;
    }

    /**
     * Checks if a value is not empty (not null or an empty string).
     *
     * @param string $value The value to check.
     * @return bool True if the value is not empty, otherwise false.
     */
    public function isNotEmpty($value) {
        return $value !== null && $value !== '';
    }

    /**
     * Determines if a column has a default value, excluding primary keys.
     *
     * @param bool $primaryKey Whether the column is a primary key.
     * @param string $defaultValue The default value of the column.
     * @return bool True if the column has a default value, otherwise false.
     */
    public function hasDefaultValue($primaryKey, $defaultValue) {
        return !$primaryKey && $defaultValue !== null && $defaultValue !== '';
    }

    /**
     * Fixes the table name according to the target database type.
     * 
     * This method adjusts the table name by removing any database prefix and applying
     * the appropriate syntax for the target database (e.g., quoting for MySQL or PostgreSQL).
     *
     * @param string $tableName The name of the table to fix.
     * @param string $targetType The target database type (e.g., 'mysql', 'pgsql').
     * @return string The fixed table name.
     */
    public function fixTableName($tableName, $targetType) {
        if (strpos($tableName, '.') !== false) {
            $tableName = explode('.', $tableName)[1];
        }
        
        if ($this->isMySQL($targetType)) {
            $tableName = '`' . $tableName . '`';
        } 
        else if ($this->isPGSQL($targetType)) {
            $tableName = '"' . $tableName . '"';
        }
        
        return $tableName;
    }

    /**
     * Fixes the column name according to the target database type.
     * 
     * This method applies proper quoting for column names based on the target database
     * (e.g., MySQL uses backticks for column names).
     *
     * @param string $columnName The name of the column to fix.
     * @param string $targetType The target database type (e.g., 'mysql').
     * @return string The fixed column name.
     */
    public function fixColumnName($columnName, $targetType) {
        if ($this->isMySQL($targetType)) {
            $columnName = '`' . $columnName . '`';
        }
        
        return $columnName;
    }

    /**
     * Generates the default value SQL for a column based on its type.
     * 
     * This method returns the appropriate default value syntax for the column's type,
     * handling different types such as BOOLEAN, INTEGER, and REAL.
     *
     * @param string $defaultValue The default value to apply to the column.
     * @param string $columnType The type of the column (e.g., 'BOOLEAN', 'INT').
     * @return string The default value SQL definition for the column.
     */
    public function getDefaultData($defaultValue, $columnType) {
        $colDef = "";
        
        if (strtoupper($defaultValue) == 'NULL') {
            $colDef .= ' DEFAULT NULL'; // NOSONAR
        } 
        else if ($this->isBoolean($columnType)) {
            $colDef .= ' DEFAULT ' . $this->convertToBoolean($defaultValue); // NOSONAR
        } 
        else if (strpos(strtoupper($columnType), 'INT') !== false) {
            $colDef .= ' DEFAULT ' . $this->convertToInteger($defaultValue); // NOSONAR
        } 
        else if ($this->isReal($columnType)) {
            $colDef .= ' DEFAULT ' . $this->convertToReal($defaultValue); // NOSONAR
        } 
        else {
            $colDef .= ' DEFAULT ' . $defaultValue;
        }
        
        return $colDef;
    }

    /**
     * Converts a value to a boolean format.
     *
     * @param mixed $value The value to convert.
     * @return string The converted boolean value ('TRUE' or 'FALSE').
     */
    public function convertToBoolean($value) {
        return (strtolower($value) == 'true' || $value === 1) ? 'TRUE' : 'FALSE';
    }

    /**
     * Converts a value to an integer format.
     *
     * @param mixed $value The value to convert.
     * @return int The converted integer value.
     */
    public function convertToInteger($value) {
        return (int) $value;
    }

    /**
     * Converts a value to a real number format.
     *
     * @param mixed $value The value to convert.
     * @return float The converted real number value.
     */
    public function convertToReal($value) {
        return (float) $value;
    }

    /**
     * Fixes default value for SQLite.
     * 
     * @param string $defaultValue The default value to fix.
     * @param string $targetType The target database type.
     * @return string The fixed default value.
     */
    public function fixDefaultValue($defaultValue, $targetType) {
        if ($this->isSQLite($targetType) && stripos($defaultValue, 'now(') !== false) {
            $defaultValue = '';
            
        }
        return $defaultValue;
    }

    /**
     * Escapes special characters in a string for use in SQL statements.
     *
     * This method wraps the PHP `addslashes()` function, which adds backslashes before characters
     * that need to be escaped in SQL, such as quotes, backslashes, and NULL characters.
     *
     * @param string $text The input string to escape.
     * @return string The escaped string with special characters properly handled.
     */
    public function addslashes($text)
    {
        return addslashes($text);
    }

    /**
     * Extracts the DROP TABLE IF EXISTS queries from the provided SQL string.
     * 
     * @param string $sql The SQL string to be processed.
     * @param string $targetType The type of database ('pgsql', 'mysql', or 'mariadb') to format the table names accordingly.
     * @return array An array of objects, each containing the name of a table to be dropped.
     */
    public function extractDropTableQueries($sql, $targetType) {

        // Remove backticks (`) from the entire SQL string before processing
        $sqlWithoutBackticks = str_replace('`', '', $sql);
        $result = [];
        try
        {
            // Regular expression to capture DROP TABLE IF EXISTS command
            $regex = '/DROP\s+TABLE\s+IF\s+EXISTS\s+([^\s]+)/i';
            preg_match_all($regex, $sqlWithoutBackticks, $matches);

            // Loop through all matches found
            foreach ($matches[1] as $match) {
                // Store the result in the desired format
                $tableName = $this->extractTableName($match);
                
                // Format the table name based on the target database type
                if ($this->isPGSQL($targetType)) {
                    $tableName = '"' . $tableName . '"';
                } else if ($this->isMySQL($targetType)) {
                    $tableName = '`' . $tableName . '`';
                }
                $result[] = [
                    'table' => $tableName    // Table name
                ];
            }
        }
        catch(Exception $e)
        {
            // Do nothing
        }

        return $result;
    }

    /**
     * Extracts the table name from the input string, removing schema if present.
     * 
     * @param string $input The input string (may contain schema.table or just table).
     * @return string The extracted table name without schema.
     */
    public function extractTableName($input) {
        // Check if the input contains a dot (indicating a schema)
        if (strpos($input, '.') !== false) {
            // If there is a dot, take the part after the dot as the table name
            $input = explode('.', $input)[1];
        }
        // If there is no dot, it means the input is just the table name
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input); // NOSONAR
    }

    
}
