<?php

namespace MagicObject\Util\Database;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoTableInfoExtended;
use MagicObject\Exceptions\ErrorConnectionException;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use PDO;

/**
 * Class PicoDatabaseUtilPostgreSql
 *
 * This class extends the PicoDatabaseUtilBase and implements the PicoDatabaseUtilInterface specifically 
 * for PostgreSQL database operations. It provides specialized utility methods tailored to leverage PostgreSQL's 
 * features and syntax while ensuring compatibility with the general database utility interface.
 *
 * Key functionalities include:
 *
 * - **Retrieve and display column information for tables:** Methods to fetch detailed column data, 
 *   including types and constraints, from PostgreSQL tables.
 * - **Generate SQL statements to create tables based on existing structures:** Automated generation 
 *   of CREATE TABLE statements to replicate existing table schemas.
 * - **Dump data from various sources into SQL insert statements:** Convert data from different formats 
 *   into valid SQL INSERT statements for efficient data insertion.
 * - **Facilitate the import of data between source and target databases:** Streamlined processes for 
 *   transferring data, including handling pre and post-import scripts to ensure smooth operations.
 * - **Ensure data integrity by fixing types during the import process:** Validation and correction of 
 *   data types to match PostgreSQL's requirements, enhancing data quality during imports.
 *
 * This class is designed for developers who are working with PostgreSQL databases and need a robust set of tools 
 * to manage database operations efficiently. By adhering to the PicoDatabaseUtilInterface, it provides 
 * a consistent API for database utilities while taking advantage of PostgreSQL-specific features.
 *
 * Usage:
 * To use this class, instantiate it with a PostgreSQL database connection and utilize its methods to perform 
 * various database tasks, ensuring efficient data management and manipulation.
 *
 * @author Kamshory
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseUtilPostgreSql extends PicoDatabaseUtilBase implements PicoDatabaseUtilInterface // NOSONAR
{

    /**
     * Retrieves a list of columns for a specified table in the database.
     *
     * This method queries the information schema to obtain details about the columns 
     * of the specified table, including their names, data types, nullability, 
     * and default values.
     *
     * @param PicoDatabase $database The database connection instance.
     * @param string $tableName The name of the table to retrieve column information from.
     * @return array An array of associative arrays containing details about each column,
     *               where each associative array includes 'column_name', 'data_type',
     *               'is_nullable', and 'column_default'.
     * @throws Exception If the database connection fails or the query cannot be executed.
     */
    public function getColumnList($database, $tableName)
    {
        $schema = $database->getDatabaseCredentials()->getDatabaseSchema();
        if(!isset($schema) || empty($schema))
        {
            $schema = "public";
        }
        $sql = "SELECT 
        column_name AS \"Field\", 
        data_type AS \"Type\", 
        is_nullable AS \"Null\", 
        CASE 
            WHEN column_default IS NOT NULL THEN 'DEFAULT' 
            ELSE '' 
        END AS \"Key\", 
        column_default AS \"Default\", 
        CASE 
            WHEN is_identity = 'YES' THEN 'AUTO_INCREMENT' 
            ELSE '' 
        END AS \"Extra\"
        FROM information_schema.columns
        WHERE table_name = '$tableName'
        AND table_schema = '$schema'";
        return $database->fetchAll($sql);
    }

    /**
     * Dumps the structure of a table as a SQL statement.
     *
     * This method generates a SQL `CREATE TABLE` statement based on the provided table information,
     * including options to add or omit specific clauses such as "IF NOT EXISTS" and 
     * "DROP TABLE IF EXISTS". It also handles the definition of primary keys if present.
     *
     * @param PicoTableInfoExtended $tableInfo         The information about the table, including column details and primary keys.
     * @param string                $tableName         The name of the table for which the structure is being generated.
     * @param bool                  $createIfNotExists Whether to include "IF NOT EXISTS" in the `CREATE` statement (default is false).
     * @param bool                  $dropIfExists      Whether to include "DROP TABLE IF EXISTS" before the `CREATE` statement (default is false).
     * @param string|null           $engine            The storage engine to use for the table (optional, default is null).
     * @param string|null           $charset           The character set to use for the table (optional, default is null).
     *
     * @return string                                  The SQL statement to create the table, including column definitions and primary keys.
     */
    public function dumpStructure($tableInfo, $tableName, $createIfNotExists = false, $dropIfExists = false, $engine = null, $charset = null)
    {
        $query = array();
        if ($dropIfExists) {
            $query[] = "-- DROP TABLE IF EXISTS \"$tableName\";";
            $query[] = "";
        }

        $createStatement = "CREATE TABLE";
        if ($createIfNotExists) {
            $createStatement .= " IF NOT EXISTS";
        }

        $autoIncrementKeys = $this->getAutoIncrementKey($tableInfo);

        $query[] = "$createStatement \"$tableName\" (";

        $cols = $tableInfo->getColumns();
        $columns = [];

        foreach($tableInfo->getSortedColumnName() as $columnName)
        {
            if(isset($cols[$columnName]))
            {
                $columns[] = $this->createColumnPostgre($cols[$columnName], $autoIncrementKeys, $tableInfo->getPrimaryKeys());
            }
        }

        $query[] = implode(",\r\n", $columns);
        $query[] = ");";

        return implode("\r\n", $query);
    }

    /**
     * Creates a column definition for a SQL statement.
     *
     * This method constructs a SQL column definition based on the provided column details,
     * including the column name, data type, nullability, and default value. The resulting 
     * definition is formatted for use in a CREATE TABLE statement.
     *
     * @param array $column An associative array containing details about the column:
     *                      - string name: The name of the column.
     *                      - string type: The data type of the column (e.g., VARCHAR, INT).
     *                      - bool|string nullable: Indicates if the column allows NULL values (true or 'true' for NULL; otherwise, NOT NULL).
     *                      - mixed default_value: The default value for the column (optional).
     *
     * @return string The SQL column definition formatted as a string, suitable for inclusion in a CREATE TABLE statement.
     */
    public function createColumn($column, $autoIncrementKeys = null)
    {
        $col = array();
        $col[] = "\t";
        $col[] = "\"" . $column[parent::KEY_NAME] . "\"";
        
        $type = $this->fixAutoIncrementType($column, $column['type'], $autoIncrementKeys);
        
        $col[] = $type;

        if (isset($column['nullable']) && strtolower(trim($column['nullable'])) == 'true') {
            $col[] = "NULL";
        } else {
            $col[] = "NOT NULL";
        }

        if (isset($column['default_value'])) {
            $defaultValue = $column['default_value'];
            $defaultValue = $this->fixDefaultValue($defaultValue, $column['type']);
            $col[] = "DEFAULT $defaultValue";
        }

        return implode(" ", $col);
    }
    
    /**
     * Adjusts the SQL data type for auto-increment columns.
     *
     * This method checks if the given column is designated as an auto-increment key
     * and modifies its SQL type accordingly. If the column is a big integer, it 
     * will return "BIGSERIAL"; otherwise, it will return "SERIAL" for standard 
     * integer types. If the column is not an auto-increment key, it returns 
     * the original type.
     *
     * @param array $column An associative array containing details about the column,
     *                      including its name and type.
     * 
     * @param string $type The original data type of the column.
     * 
     * @param array $autoIncrementKeys An array of column names that are designated as 
     *                                 auto-increment keys.
     * 
     * @return string The adjusted SQL data type for the column, suitable for use 
     *                in a CREATE TABLE statement.
     */
    private function fixAutoIncrementType($column, $type, $autoIncrementKeys)
    {
        if(isset($autoIncrementKeys) && is_array($autoIncrementKeys) && in_array($column, $autoIncrementKeys))
        {
            if(stripos($type, "big"))
            {
                return "BIGSERIAL";
            }
            else
            {
                return "SERIAL";
            }
        }
        else
        {
            return $type;
        }
    }

    /**
     * Creates a column definition for a PostgreSQL SQL statement.
     *
     * This method constructs a SQL column definition based on the provided column details,
     * including the column name, data type, nullability, default value, and whether the column 
     * should be auto-incrementing. If the column is specified as auto-increment, it will use 
     * PostgreSQL's SERIAL or BIGSERIAL data types, depending on the column type.
     *
     * @param array $column An associative array containing details about the column:
     *                      - string 'name': The name of the column.
     *                      - string 'type': The data type of the column (e.g., VARCHAR, INT).
     *                      - bool|string 'nullable': Indicates if the column allows NULL values 
     *                        ('true' or true for NULL; otherwise, NOT NULL).
     *                      - mixed 'default_value': The default value for the column (optional).
     *
     * @param array|null $autoIncrementKeys An optional array of column names that should 
     *                                       be treated as auto-incrementing.
     *
     * @param array $primaryKeys An array of primary key columns, each being an associative 
     *                            array with at least a 'name' key. This is used to identify 
     *                            if the column is a primary key.
     *
     * @return string The SQL column definition formatted as a string, suitable for inclusion 
     *                in a CREATE TABLE statement.
     */
    public function createColumnPostgre($column, $autoIncrementKeys, $primaryKeys)
    {
        $pkCols = array();
        foreach ($primaryKeys as $col) {
            $pkCols[] = $col['name']; // Collect primary key column names.
        }

        $col = array();
        $columnName = $column[parent::KEY_NAME]; // Get the column name.

        // Check if the column should be auto-incrementing.
        if (isset($autoIncrementKeys) && is_array($autoIncrementKeys) && in_array($column[parent::KEY_NAME], $autoIncrementKeys)) {
            // Determine the appropriate serial type based on the column's type.
            if (stripos($column['type'], 'big') !== false) {
                $columnType = "BIGSERIAL"; // Use BIGSERIAL for large integers.
            } else {
                $columnType = "SERIAL"; // Use SERIAL for standard integers.
            }
        } else {
            $columnType = $this->getColumnType($column['type']); // Use the specified type if not auto-incrementing.
        }

        $col[] = "\t";  // Add tab indentation for readability.
        $col[] = $columnName;  // Add the column name.
        $col[] = $columnType;  // Add the column type (SERIAL or BIGSERIAL, or custom type).

        // Add PRIMARY KEY constraint if the column is part of the primary keys.
        if (in_array($columnName, $pkCols)) {
            $col[] = 'PRIMARY KEY';
        }

        // Determine nullability and add it to the definition.
        if (isset($column['nullable']) && strtolower(trim($column['nullable'])) == 'true') {
            $col[] = "NULL"; // Allow NULL values.
        } else {
            $col[] = "NOT NULL"; // Disallow NULL values.
        }

        // Handle default value if provided, using a helper method to format it.
        if (isset($column['default_value'])) {
            $defaultValue = $column['default_value'];
            $defaultValue = $this->fixDefaultValue($defaultValue, $columnType); // Format the default value.
            $col[] = "DEFAULT $defaultValue";
        }

        // Join all parts into a single string to form the complete column definition.
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
        if(stripos($type, 'bool') !== false)
        {
            return $defaultValue != 0 ? 'true' : 'false';
        }
        else if (strtolower($defaultValue) == 'true' || strtolower($defaultValue) == 'false' || strtolower($defaultValue) == 'null') {
            return $defaultValue;
        }     
        else if (stripos($type, 'enum') !== false || stripos($type, 'varchar') !== false || stripos($type, 'char') !== false || stripos($type, 'text') !== false) {
            return "'" . addslashes($defaultValue) . "'";
        }
        else if(stripos($type, 'int') !== false 
        || stripos($type, 'real') !== false 
        || stripos($type, 'float') !== false 
        || stripos($type, 'double') !== false)
        {
            return $defaultValue + 0;
        }
        return $defaultValue;
    }

    /**
     * Dumps a single record into an SQL INSERT statement.
     *
     * This method takes a data record and constructs an SQL INSERT statement 
     * for the specified table. It maps the values of the record to the corresponding 
     * columns based on the provided column definitions.
     *
     * @param array $columns An associative array where keys are column names and values are column details.
     * @param string $tableName The name of the table where the record will be inserted.
     * @param MagicObject $record The data record to be inserted, which provides a method to retrieve values.
     *
     * @return string The generated SQL INSERT statement.
     * @throws Exception If the record cannot be processed or if there are no values to insert.
     */
    public function dumpRecord($columns, $tableName, $record)
    {
        $value = $record->valueArray();
        $rec = array();
        foreach ($value as $key => $val) {
            if (isset($columns[$key])) {
                $rec[$columns[$key][parent::KEY_NAME]] = $val;
            }
        }

        $queryBuilder = new PicoDatabaseQueryBuilder(PicoDatabaseType::DATABASE_TYPE_PGSQL);
        $queryBuilder->newQuery()
            ->insert()
            ->into($tableName)
            ->fields(array_keys($rec))
            ->values(array_values($rec));

        return $queryBuilder->toString();
    }

    /**
     * Retrieves the columns of a specified table from the database.
     *
     * This method executes a SQL query to show the columns of the given table and returns 
     * an associative array where the keys are column names and the values are their respective types.
     *
     * @param PicoDatabase $database Database connection object.
     * @param string $tableName Name of the table whose columns are to be retrieved.
     * @return array An associative array mapping column names to their types.
     * @throws Exception If the query fails or the table does not exist.
     */
    public function showColumns($database, $tableName)
    {
        $schema = $database->getDatabaseCredentials()->getDatabaseSchema();
        if(!isset($schema) || empty($schema))
        {
            $schema = "public";
        }
        $sql = "SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_schema = '$schema' AND table_name = '$tableName'";
        $result = $database->fetchAll($sql, PDO::FETCH_ASSOC);

        $columns = array();
        foreach ($result as $row) {
            $columns[$row['column_name']] = $row['data_type'];
        }
        return $columns;
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

        $schemaSource = $databaseConfigSource->getDatabaseSchema();
        if(!isset($schemaSource) || empty($schemaSource))
        {
            $schemaSource = "public";
        }
        $schemaTarget = $databaseConfigTarget->getDatabaseSchema();
        if(!isset($schemaTarget) || empty($schemaTarget))
        {
            $schemaTarget = "public";
        }
        try {
            $databaseSource->connect();
            $databaseTarget->connect();
            $tables = $config->getTable();

            $existingTables = array();
            foreach ($tables as $tb) {
                $existingTables[] = $tb->getTarget();
            }

            $sourceTableList = $databaseSource->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema='$schemaSource'", PDO::FETCH_NUM);
            $targetTableList = $databaseTarget->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema='$schemaTarget'", PDO::FETCH_NUM);

            foreach ($sourceTableList as $sourceTable) {
                if (!in_array($sourceTable[0], $existingTables) && in_array($sourceTable[0], $targetTableList)) {
                    $config->addTable($sourceTable[0], $sourceTable[0]);
                }
            }

            return $config;
        } catch (Exception $e) {
            throw new ErrorConnectionException("Error during database connection: " . $e->getMessage());
        }
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
        foreach($data as $name=>$value)
        {
            if(isset($columns[$name]))
            {
                $type = $columns[$name];
                if(strtolower($type) == 'tinyint(1)' || strtolower($type) == 'boolean' || strtolower($type) == 'bool')
                {
                    $data = $this->fixBooleanData($data, $name, $value);
                }
                else if(stripos($type, 'integer') !== false || stripos($type, 'int(') !== false)
                {
                    $data = $this->fixIntegerData($data, $name, $value);
                }
                else if(stripos($type, 'float') !== false || stripos($type, 'double') !== false || stripos($type, 'decimal') !== false)
                {
                    $data = $this->fixFloatData($data, $name, $value);
                }
            }
        }
        return $data;
    }

    /**
     * Converts the given column type from MySQL format to PostgreSQL format.
     *
     * This method calls the `convertMySqlToPostgreSql` function to perform the conversion of 
     * the column type from MySQL to PostgreSQL syntax. The method provides an abstraction layer 
     * for converting column types, ensuring compatibility when migrating between MySQL and PostgreSQL.
     *
     * @param string $columnType The MySQL column type to be converted.
     * @return string The equivalent PostgreSQL column type.
     * @throws InvalidArgumentException If the column type is not recognized or unsupported.
     */
    public function getColumnType($columnType)
    {
        if(stripos($columnType, 'tinyint(1)') === 0)
        {
            return 'BOOLEAN';
        }
        else if(stripos($columnType, 'biginteger(') === 0 
        || stripos($columnType, 'smallinteger') === 0 
        || stripos($columnType, 'integer') === 0 
        || stripos($columnType, 'bigint') === 0 
        || stripos($columnType, 'smallint') === 0 
        || stripos($columnType, 'int') === 0)
        {
            return 'INTEGER';
        }
        $type = $this->convertMySqlToPostgreSql($columnType);
        if (stripos($type, 'enum(') === 0) {
            // Extract the enum values between the parentheses
            if (preg_match('/^enum\((.+)\)$/i', $type, $matches)) {
                // Get the enum values as an array by splitting the string
                $enumValues = array_map('trim', explode(',', $matches[1]));
                // Find the maximum length of the enum values
                $maxLength = max(array_map('strlen', $enumValues));
                // Set the NVARCHAR length to the max length of enum values + 2
                $type = 'CHARACTER VARYING(' . ($maxLength + 2) . ')';
            }
        } else if (stripos($type, 'varchar(') === 0) {
            $type = str_ireplace('varchar', 'CHARACTER VARYING', $columnType);
        } else if (stripos($type, 'year(') === 0) {
            // Extract the enum values between the parentheses
            $type = "INTEGER";
        } 
        return $type;
    }

}
