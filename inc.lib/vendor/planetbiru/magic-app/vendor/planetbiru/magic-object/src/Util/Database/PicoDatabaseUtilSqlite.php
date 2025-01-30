<?php

namespace MagicObject\Util\Database;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoTableInfoExtended;
use MagicObject\Exceptions\InvalidParameterException;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use PDO;

/**
 * Class PicoDatabaseUtilSqlite
 *
 * Provides utility methods for SQLite database operations, extending PicoDatabaseUtilBase 
 * and implementing PicoDatabaseUtilInterface. This class includes functions for retrieving 
 * column information, generating CREATE TABLE statements, dumping data to SQL insert statements, 
 * facilitating data imports, and ensuring data integrity during the import process.
 *
 * Key features:
 * - Retrieve column info from SQLite tables.
 * - Generate CREATE TABLE statements.
 * - Convert data to SQL INSERT statements.
 * - Facilitate data import between databases.
 * - Ensure data integrity during imports.
 *
 * Designed for developers working with SQLite to streamline database management tasks.
 *
 * @author Kamshory
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseUtilSqlite extends PicoDatabaseUtilBase implements PicoDatabaseUtilInterface // NOSONAR
{

    /**
     * Generates a SQL CREATE TABLE query based on the provided class annotations.
     *
     * This function inspects the given class for its properties and their annotations
     * to construct a SQL statement that can be used to create a corresponding table in a database.
     * It extracts the table name from the `@Table` annotation and processes each property 
     * to determine the column definitions from the `@Column` annotations.
     *
     * @param MagicObject $entity     The instance of the class whose properties will be used
     *                                to generate the table structure.
     * @param bool $createIfNotExists If true, the query will include an "IF NOT EXISTS" clause.
     * @param bool $dropIfExists      Whether to add "DROP TABLE IF EXISTS" before the CREATE statement (default is false).
     * @return string The generated SQL CREATE TABLE query.
     * 
     * @throws ReflectionException If the class does not exist or is not accessible.
     */
    public function showCreateTable($entity, $createIfNotExists = false, $dropIfExists = false) {        
        $tableInfo = $entity->tableInfo();
        $tableName = $tableInfo->getTableName();
    
        // Start building the CREATE TABLE query
        $condition = $this->createIfNotExists($createIfNotExists);

        $autoIncrementKeys = $this->getAutoIncrementKey($tableInfo);

        $query = "";
        if($dropIfExists)
        {
            $query .= "-- DROP TABLE IF EXISTS $tableName;\r\n\r\n";
        }
        $query .= "CREATE TABLE$condition $tableName (\n";
    
        // Define primary key
        $primaryKey = null;

        $pKeys = $tableInfo->getPrimaryKeys();

        $pKeyArr = array();
        $pKeyArrUsed = array();
        if(self::isArray($pKeys) && !empty($pKeys))
        {
            $pkVals = array_values($pKeys);
            foreach($pkVals as $pk)
            {
                $pKeyArr[] = $pk['name'];
            }
        }

        foreach ($tableInfo->getColumns() as $column) {
        
            $columnName = $column[MagicObject::KEY_NAME];
            $columnType = $column[MagicObject::KEY_TYPE];
            $length = isset($column[MagicObject::KEY_LENGTH]) ? $column[MagicObject::KEY_LENGTH] : null;
            $nullable = (isset($column[self::KEY_NULLABLE]) && $column[self::KEY_NULLABLE] === 'true') ? ' NULL' : ' NOT NULL';
            $defaultValue = isset($column[MagicObject::KEY_DEFAULT_VALUE]) ? " DEFAULT ".$column[MagicObject::KEY_DEFAULT_VALUE] : '';

            // Convert column type for SQL
            $columnType = strtolower($columnType); // Convert to lowercase for case-insensitive comparison

            $attr = $this->determineSqlType($column, $autoIncrementKeys, $length, $pKeyArrUsed);
            
            
            $sqlType = $attr['sqlType'];
            $pKeyArrUsed = $attr['pKeyArrUsed'];
            
            // Add to query
            $query .= "\t$columnName $sqlType$nullable$defaultValue,\n";
            
        }
    
        // Remove the last comma and add primary key constraint
        $query = rtrim($query, ",\n") . "\n";
    
        $pKeyArrFinal = $this->getPkeyArrayFinal($pKeyArr, $pKeyArrUsed);

        if (!empty($pKeyArrFinal)) {
            $primaryKey = implode(", ", $pKeyArrFinal);
            $query = rtrim($query, ",\n");
            $query .= ",\n\tPRIMARY KEY ($primaryKey)\n";
        }
    
        $query .= ");";
    
        return str_replace("\n", "\r\n", $query);
    }
    
    /**
     * Returns "IF NOT EXISTS" if specified, otherwise an empty string.
     *
     * @param bool $createIfNotExists Flag indicating whether to include "IF NOT EXISTS".
     * @return string The "IF NOT EXISTS" clause if applicable.
     */
    private function createIfNotExists($createIfNotExists) {
        return $createIfNotExists ? " IF NOT EXISTS" : "";
    }
    
    /**
     * Filter the primary key array to exclude used primary keys.
     *
     * @param array $pKeyArr Array of primary key names.
     * @param array $pKeyArrUsed Array of used primary key names.
     * @return array Filtered array of primary key names.
     */
    private function getPkeyArrayFinal($pKeyArr, $pKeyArrUsed)
    {
        $pKeyArrFinal = array();
        foreach($pKeyArr as $v)
        {
            if(!in_array($v, $pKeyArrUsed))
            {
                $pKeyArrFinal[] = $v;
            }
        }
        return $pKeyArrFinal;
    }
    
    /**
     * Determine the SQL data type based on the given column information and auto-increment keys.
     *
     * @param array $column The column information, expected to include the column name and type.
     * @param array|null $autoIncrementKeys The array of auto-increment keys, if any.
     * @param int $length The length for VARCHAR types.
     * @param array $pKeyArrUsed The array to store used primary key names.
     * @return array An array containing the determined SQL data type and the updated primary key array.
     */
    private function determineSqlType($column, $autoIncrementKeys = null, $length = 255, $pKeyArrUsed = array())
    {
        $columnName = $column[MagicObject::KEY_NAME];
        $columnType = strtolower($column[MagicObject::KEY_TYPE]); // Assuming 'type' holds the column type
        $sqlType = '';

        // Check for auto-increment primary key
        if (self::isArray($autoIncrementKeys) && in_array($columnName, $autoIncrementKeys)) {
            $sqlType = 'INTEGER PRIMARY KEY';
            $pKeyArrUsed[] = $columnName; // Add to used primary keys
        } else {
            // Default mapping of column types to SQL types
            $typeMapping = array(
                'varchar' => "NVARCHAR($length)",
                'tinyint(1)' => 'BOOLEAN', // NOSONAR
                'float' => 'REAL',
                'text' => 'TEXT',
                'longtext' => 'TEXT',
                'datetime' => 'DATETIME',
                'date' => 'DATE',
                'timestamp' => 'TIMESTAMP',
                'time' => 'TIME',
                'blob' => 'BLOB',
            );

            // Check if the column type exists in the mapping
            if (array_key_exists($columnType, $typeMapping)) {
                $sqlType = $typeMapping[$columnType];
            } else if(stripos($columnType, 'int(') === 0) {
                $sqlType = strtoupper($columnType);
            } else {
                $sqlType = strtoupper($columnType);
                if ($sqlType !== 'TINYINT(1)' && $sqlType !== 'FLOAT' && $sqlType !== 'TEXT' && 
                    $sqlType !== 'LONGTEXT' && $sqlType !== 'DATE' && $sqlType !== 'TIMESTAMP' && 
                    $sqlType !== 'BLOB') 
                {
                    $sqlType = 'NVARCHAR(255)'; // Fallback type for unknown types
                }
            }
        }

        return array('sqlType' => $sqlType, 'pKeyArrUsed' => $pKeyArrUsed);
    }


    /**
     * Retrieves a list of columns for a specified table in the database.
     *
     * This method queries the information schema to obtain details about the columns 
     * of the specified table, including their names, data types, nullability, 
     * default values, and any additional attributes such as primary keys and auto-increment.
     *
     * @param PicoDatabase $database The database connection instance.
     * @param string $tableName The name of the table to retrieve column information from.
     * @return array An array of associative arrays containing details about each column,
     *               where each associative array includes:
     *               - 'Field': The name of the column.
     *               - 'Type': The data type of the column.
     *               - 'Null': Indicates if the column allows NULL values ('YES' or 'NO').
     *               - 'Key': Indicates if the column is a primary key ('PRI' or null).
     *               - 'Default': The default value of the column, or 'None' if not set.
     *               - 'Extra': Additional attributes of the column, such as 'auto_increment'.
     * @throws Exception If the database connection fails or the query cannot be executed.
     */
    public function getColumnList($database, $tableName)
    {
        $stmt = $database->query("PRAGMA table_info($tableName)");

        // Fetch and display the column details
        $rows = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = array(
                "Field"   => $row['name'],
                "Type"    => $row['type'],
                "Null"    => $row['notnull'] == 1 ? 'NO' : 'YES',
                "Key"     => $row['pk'] ? 'PRI' : null,
                "Default" => $row['dflt_value'] ? $row['dflt_value'] : null,
                "Extra"   => ($row['pk'] == 1 && strtoupper($row['type']) === 'INTEGER') ? 'auto_increment' : null
            );
        }
        return $rows;
    }

    /**
     * Dumps the structure of a table as a SQL statement.
     *
     * This method generates a SQL CREATE TABLE statement based on the provided table information,
     * including the option to include or exclude specific clauses such as "IF NOT EXISTS" and 
     * "DROP TABLE IF EXISTS". It also handles the definition of primary keys if present.
     *
     * @param PicoTableInfoExtended $tableInfo The information about the table, including column details and primary keys.
     * @param string        $tableName         The name of the table for which the structure is being generated.
     * @param bool          $createIfNotExists Whether to add "IF NOT EXISTS" in the CREATE statement (default is false).
     * @param bool          $dropIfExists      Whether to add "DROP TABLE IF EXISTS" before the CREATE statement (default is false).
     * @param string|null   $engine            The storage engine to use for the table (optional, default is null).
     * @param string|null   $charset           The character set to use for the table (optional, default is null).
     * @return string                          The SQL statement to create the table, including column definitions and primary keys.
     */
    public function dumpStructure($tableInfo, $tableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $query = array();
        $columns = array();
        if($dropIfExists)
        {
            $query[] = "-- DROP TABLE IF EXISTS $tableName;";
            $query[] = "";
        }
        $createStatement = "";
        $createStatement = "CREATE TABLE";
        if($createIfNotExists)
        {
            $createStatement .= " IF NOT EXISTS";
        }

        $query[] = "$createStatement $tableName (";

        $cols = $tableInfo->getColumns();

        $pk = $tableInfo->getPrimaryKeys();
        
        if(self::isArray($pk) && !empty($pk))
        {
            foreach($pk as $prop=>$primaryKey)
            {
                $cols[$prop][self::KEY_PRIMARY_KEY] = true;
            }
        }

        $autoIncrementKeys = $this->getAutoIncrementKey($tableInfo);
        foreach($tableInfo->getColumns() as $k=>$column)
        {
            if(self::isArray($autoIncrementKeys) && in_array($column[MagicObject::KEY_NAME], $autoIncrementKeys))
            {
                $cols[$k]['auto_increment'] = true;
            }
        }

        foreach($tableInfo->getSortedColumnName() as $columnName)
        {
            if(isset($cols[$columnName]))
            {
                $columns[] = $this->createColumn($cols[$columnName]);
            }
        }

        $query[] = implode(",\r\n", $columns);
        $query[] = "); ";
        
        return implode("\r\n", $query);
    }

    /**
     * Converts MySQL data types to SQLite-compatible types.
     *
     * This function maps MySQL data types to their equivalent SQLite types. It supports:
     * - `tinyint(1)` to `BOOLEAN`,
     * - `integer` to `INTEGER`,
     * - `enum()` to `NVARCHAR(N)` with a length based on the maximum length of the enum values plus 2,
     * - `varchar(N)` to `NVARCHAR(N)` (or `varchar` to `NVARCHAR` without a length),
     * - `float`, `double` to `REAL`,
     * - `decimal` to `NUMERIC`,
     * - `text` and `longtext` to `TEXT`,
     * - `date`, `datetime`, `timestamp` to `TEXT` (as SQLite does not have dedicated date/time types).
     *
     * @param string $type The MySQL data type to convert (e.g., 'tinyint(1)', 'varchar(255)', 'enum(', 'decimal(10,2)').
     * 
     * @return string The corresponding SQLite data type (e.g., 'BOOLEAN', 'NVARCHAR(255)', 'REAL', 'NUMERIC', 'TEXT').
     */
    private function mysqlToSqliteType($type) // NOSONAR
    {
        // Trim any whitespace and convert to lowercase for easier comparison
        $typeCheck = trim(strtolower($type));
        
        // Define a mapping of common MySQL types to SQLite types
        $map = array(
            'tinyint(1)' => 'BOOLEAN',  // MySQL 'tinyint(1)' maps to SQLite 'BOOLEAN'
            'smallint' => 'INT',        // MySQL 'smallint' maps to SQLite 'INT'
            'mediumint' => 'INT',       // MySQL 'mediumint' maps to SQLite 'INT'
            'integer' => 'INTEGER',     // MySQL 'integer' maps to SQLite 'INTEGER'
            'int' => 'INT',             // MySQL 'int' maps to SQLite 'INT'
            'float' => 'REAL',          // MySQL 'float' maps to SQLite 'REAL'
            'double' => 'REAL',         // MySQL 'double' maps to SQLite 'REAL'
            'decimal' => 'NUMERIC',     // MySQL 'decimal' maps to SQLite 'NUMERIC'
            'tinytext' => 'TEXT',       // MySQL 'tinytext' maps to SQLite 'TEXT'
            'smalltext' => 'TEXT',      // MySQL 'smalltext' maps to SQLite 'TEXT'
            'mediumtext' => 'TEXT',     // MySQL 'mediumtext' maps to SQLite 'TEXT'
            'longtext' => 'TEXT',       // MySQL 'longtext' maps to SQLite 'TEXT'
            'text' => 'TEXT',           // MySQL 'text' maps to SQLite 'TEXT'
            'datetime' => 'DATETIME',   // MySQL 'datetime' maps to SQLite 'DATETIME'
            'date' => 'DATE',           // MySQL 'date' maps to SQLite 'DATE'
            'timestamp' => 'TIMESTAMP', // MySQL 'timestamp' maps to SQLite 'TIMESTAMP'
            'time' => 'TIME',           // MySQL 'time' maps to SQLite 'TIME'
        );

        // Handle 'enum' types and convert them to 'NVARCHAR' with length based on max enum value length + 2
        if (stripos($typeCheck, 'enum(') === 0) {
            // Extract the enum values between the parentheses
            if (preg_match('/^enum\((.+)\)$/i', $typeCheck, $matches)) {
                // Get the enum values as an array by splitting the string
                $enumValues = array_map('trim', explode(',', $matches[1]));
                // Find the maximum length of the enum values
                $maxLength = max(array_map('strlen', $enumValues));
                // Set the NVARCHAR length to the max length of enum values + 2
                $type = 'NVARCHAR(' . ($maxLength + 2) . ')';
            }
        } elseif (stripos($typeCheck, 'varchar') === 0) {
            // Handle 'varchar' types and convert them to 'nvarchar' in SQLite
            if (preg_match('/^varchar\((\d+)\)$/i', $typeCheck, $matches)) {
                $type = 'NVARCHAR(' . $matches[1] . ')';
            } else {
                // If it's just 'varchar', convert it to 'NVARCHAR' without a length
                $type = 'NVARCHAR';
            }
        } elseif (stripos($typeCheck, 'char(') === 0) {
            // Convert 'char()' to uppercase (MySQL int type conversion)
            $type = strtoupper($type);
        } elseif (stripos($typeCheck, 'int(') === 0) {
            // Convert 'int()' to uppercase (MySQL int type conversion)
            $type = strtoupper($type);
        } elseif (stripos($typeCheck, 'bigint(') === 0) {
            // Convert 'bigint()' to INTEGER
            $type = 'INTEGER';
        } else {
            // For all other types, check the predefined map
            foreach ($map as $key => $val) {
                if (stripos($typeCheck, $key) === 0) {
                    // If a match is found, use the mapped SQLite type
                    $type = $val;
                    break;
                }
            }
        }
        
        // Return the mapped SQLite data type
        return $type;
    }

    /**
     * Creates a column definition for a SQL statement (SQLite).
     *
     * This method constructs a SQL column definition based on the provided column details,
     * including the column name, data type, nullability, default value, and primary key constraints.
     * The resulting definition is formatted for use in a CREATE TABLE statement, suitable for SQLite.
     * 
     * If the column is specified as a primary key with auto-increment, the column type is set to INTEGER,
     * and the PRIMARY KEY constraint is added with auto-increment behavior (SQLite uses INTEGER PRIMARY KEY AUTOINCREMENT).
     * 
     * @param array $column An associative array containing details about the column:
     *                      - string 'name': The name of the column.
     *                      - string 'type': The data type of the column (e.g., VARCHAR, INT).
     *                      - bool|string 'nullable': Indicates if the column allows NULL values
     *                        ('true' or true for NULL; otherwise, NOT NULL).
     *                      - mixed 'defaultValue': The default value for the column (optional).
     *                      - bool 'primary_key': Whether the column is a primary key (optional).
     *                      - bool 'auto_increment': Whether the column is auto-incrementing (optional).
     * 
     * @return string The SQL column definition formatted as a string, suitable for inclusion 
     *                in a CREATE TABLE statement.
     */
    public function createColumn($column)
    {
        $columnType = $this->mysqlToSqliteType($column[MagicObject::KEY_TYPE]);  // Convert MySQL type to SQLite type
        $col = array();
        $col[] = "\t";  // Indentation for readability
        $col[] = "" . $column[MagicObject::KEY_NAME] . "";  // Column name
        
        // Handle primary key and auto-increment columns
        if (isset($column[self::KEY_PRIMARY_KEY]) && isset($column[self::KEY_AUTO_INCREMENT]) && $column[self::KEY_PRIMARY_KEY] && $column[self::KEY_AUTO_INCREMENT]) {
            $columnType = 'INTEGER';  // Use INTEGER for auto-incrementing primary keys in SQLite
            $col[] = $columnType;
            $col[] = 'PRIMARY KEY';
        }
        // Handle primary key only
        else if (isset($column[self::KEY_PRIMARY_KEY]) && $column[self::KEY_PRIMARY_KEY]) {
            $col[] = $columnType;
            $col[] = 'PRIMARY KEY';
        }
        // Handle regular column (non-primary key)
        else {
            $col[] = $columnType;
        }

        // Handle nullability
        if (isset($column[self::KEY_NULLABLE]) && strtolower(trim($column[self::KEY_NULLABLE])) == 'true') {
            $col[] = "NULL";  // Allow NULL values
        } else {
            $col[] = "NOT NULL";  // Disallow NULL values
        }

        // Handle default value if provided
        if (isset($column[MagicObject::KEY_DEFAULT_VALUE])) {
            $defaultValue = $column[MagicObject::KEY_DEFAULT_VALUE];
            $defaultValue = $this->fixDefaultValue($defaultValue, $columnType);  // Format the default value
            $col[] = "DEFAULT $defaultValue";
        }

        // Join all parts into a single string for the final SQL column definition
        return implode(" ", $col);
    }

    /**
     * Fixes the default value for SQL insertion based on its type.
     *
     * This method processes the given default value according to the specified data type,
     * ensuring that it is correctly formatted for SQL insertion. For string-like types,
     * the value is enclosed in single quotes, while boolean and null values are returned 
     * as is.
     *
     * @param mixed $defaultValue The default value to fix, which can be a string, boolean, or null.
     * @param string $type The data type of the column (e.g., ENUM, CHAR, TEXT, INT, FLOAT, DOUBLE).
     *
     * @return mixed The fixed default value formatted appropriately for SQL insertion.
     */
    public function fixDefaultValue($defaultValue, $type)
    {
        $result = $defaultValue;
        if(self::isTypeBoolean($type))
        {
            $result = ($defaultValue != 0 || strtolower($defaultValue) == 'true') ? '1' : '0';
        }
        else if(self::isNativeValue($defaultValue))
        {
            $result = $defaultValue;
        }
        else if(self::isTypeText($type))
        {
            $result = "'".$defaultValue."'";
        }
        else if(self::isTypeInteger($type))
        {
            $defaultValue = preg_replace('/[^\d]/', '', $defaultValue);
            $result = (int)$defaultValue;
        }
        else if(self::isTypeFloat($type))
        {
            $defaultValue = preg_replace('/[^\d.]/', '', $defaultValue);
            $result = (float)$defaultValue;
        }
        return $result;
    }

    /**
     * Fixes imported data based on specified column types.
     *
     * This method processes the input data array and adjusts the values 
     * according to the expected types defined in the columns array. It 
     * supports boolean, integer, and float types.
     *
     * @param mixed[] $data The input data to be processed.
     * @param string[] $columns An associative array mapping column names to their types.
     * @return mixed[] The updated data array with fixed types.
     */
    public function fixImportData($data, $columns)
    {
        // Iterate through each item in the data array
        foreach($data as $name=>$value)
        {
            // Check if the column exists in the columns array
            if(isset($columns[$name]))
            {
                $type = $columns[$name];
                
                if(strtolower($type) == 'tinyint(1)' 
                || strtolower($type) == 'boolean' 
                || strtolower($type) == 'bool'
                )
                {
                    // Process boolean types
                    $data = $this->fixBooleanData($data, $name, $value);
                }
                else if(stripos($type, 'integer') !== false 
                || stripos($type, 'int(') !== false
                )
                {
                    // Process integer types
                    $data = $this->fixIntegerData($data, $name, $value);
                }
                else if(stripos($type, 'float') !== false 
                || stripos($type, 'double') !== false 
                || stripos($type, 'decimal') !== false
                )
                {
                    // Process float types
                    $data = $this->fixFloatData($data, $name, $value);
                }
            }
        }
        return $data;
    }

    /**
     * Automatically configures the import data settings based on the source and target databases.
     *
     * This method connects to the source and target databases, retrieves the list of existing 
     * tables, and updates the configuration for each target table by checking its presence in the 
     * source database. It handles exceptions and logs any errors encountered during the process.
     *
     * @param SecretObject $config The configuration object containing database and table information.
     * @return SecretObject The updated configuration object with modified table settings.
     */
    public function autoConfigureImportData($config)
    {
        $databaseConfigSource = $config->getDatabaseSource();
        $databaseConfigTarget = $config->getDatabaseTarget();

        $databaseSource = new PicoDatabase($databaseConfigSource);
        $databaseTarget = new PicoDatabase($databaseConfigTarget);
        try
        {
            $databaseSource->connect();
            $databaseTarget->connect();
            $tables = $config->getTable();

            $existingTables = array();
            foreach($tables as $tb)
            {
                $existingTables[] = $tb->getTarget();
            }

            $sourceTableList = $databaseSource->fetchAll("SELECT name FROM sqlite_master WHERE type='table'", PDO::FETCH_NUM);
            $targetTableList = $databaseTarget->fetchAll("SELECT name FROM sqlite_master WHERE type='table'", PDO::FETCH_NUM);

            $sourceTables = call_user_func_array('array_merge', $sourceTableList);
            $targetTables = call_user_func_array('array_merge', $targetTableList);

            foreach($targetTables as $target)
            {
                $tables = $this->updateConfigTable($databaseSource, $databaseTarget, $tables, $sourceTables, $target, $existingTables);
            }
            $config->setTable($tables);
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
        }
        return $config;
    }
    
    /**
     * Check if a table exists in the database.
     *
     * This method queries the database to determine if a specified table exists by checking 
     * the SQLite master table. It throws an exception if the table name is null or empty.
     *
     * @param PicoDatabase $database The database instance to check.
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     * @throws InvalidParameterException If the table name is null or empty.
     */
    public function tableExists($database, $tableName)
    {
        if(!isset($tableName) || empty($tableName))
        {
            throw new InvalidParameterException("Table name can't be null or empty.");
        }
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name=:tableName";
        $stmt = $database->getDatabaseConnection()->prepare($query);
        $stmt->bindValue(':tableName', $tableName);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
    
    /**
     * Converts a MySQL column type to its equivalent SQLite column type.
     *
     * This method uses the `mysqlToSqliteType` function to convert a MySQL column type 
     * to the appropriate SQLite column type. It helps facilitate database migration or 
     * compatibility between MySQL and SQLite.
     *
     * @param string $columnType The MySQL column type to be converted.
     * @return string The equivalent SQLite column type.
     * @throws InvalidArgumentException If the column type is not recognized or unsupported.
     */
    public function getColumnType($columnType)
    {
        $columnType = $this->mysqlToSqliteType($columnType);
        if(stripos($columnType, 'int') === 0)
        {
            $columnType = strtoupper($columnType);
        }
        return $columnType;
    }

}