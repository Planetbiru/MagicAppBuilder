<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabasePersistence;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoPageData;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Database\PicoTableInfoExtended;
use MagicObject\MagicObject;
use MagicObject\Util\Database\DatabaseTypeConverter;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;
use MagicObject\Util\Database\PicoDatabaseUtilPostgreSql;
use MagicObject\Util\Database\PicoDatabaseUtilSqlite;
use MagicObject\Util\Database\PicoDatabaseUtilSqlServer;
use stdClass;

/**
 * Database dump class for managing and generating SQL statements
 * for table structures.
 * 
 * @author Kamshory
 * @package MagicObject\Generator
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseDump // NOSONAR
{
    /**
     * Table information
     *
     * @var PicoTableInfo
     */
    protected $tableInfo;

    /**
     * Table name
     *
     * @var string
     */
    protected $picoTableName = "";

    /**
     * Columns
     *
     * @var array
     */
    protected $columns = array();
    
    /**
     * Instantiates the appropriate database dump utility based on the database type.
     *
     * This factory method maps specific database engines to their respective 
     * utility classes (MySQL, PostgreSQL, SQLite, or SQL Server) to handle 
     * database-specific dumping operations.
     *
     * @param string $databaseType The type of database (e.g., MySQL, PostgreSQL, SQLite).
     * @return PicoDatabaseUtilBase|string Returns an instance of the database utility tool 
     * or an empty string if the database type is not supported.
     */
    private function getDatabaseDumpTool($databaseType)
    {
        switch ($databaseType) {
            case PicoDatabaseType::DATABASE_TYPE_MARIADB:
            case PicoDatabaseType::DATABASE_TYPE_MYSQL:
                $tool = new PicoDatabaseUtilMySql();
                break;
            case PicoDatabaseType::DATABASE_TYPE_PGSQL:
            case PicoDatabaseType::DATABASE_TYPE_POSTGRESQL:
                $tool = new PicoDatabaseUtilPostgreSql();
                break;
            case PicoDatabaseType::DATABASE_TYPE_SQLITE:
                $tool = new PicoDatabaseUtilSqlite();
                break;
            case PicoDatabaseType::DATABASE_TYPE_SQLSERVER:
                $tool = new PicoDatabaseUtilSqlServer();
                break;
            default:
                return "";
        }
        return $tool;
    }
    
    /**
     * Generates a SQL CREATE TABLE statement based on the provided entity schema.
     * * This method detects the database type and utilizes the appropriate utility 
     * class to format columns, primary keys, and auto-increment constraints. 
     * It supports MySQL, MariaDB, PostgreSQL, SQLite, and SQL Server.
     *
     * @param array $entity The entity schema containing 'name' and 'columns' (an array of column definitions).
     * @param string $databaseType The type of database (e.g., PicoDatabaseType::DATABASE_TYPE_MARIADB).
     * @param bool $createIfNotExists Whether to add the "IF NOT EXISTS" clause to the CREATE statement.
     * @param bool $dropIfExists Whether to prepend a commented-out "DROP TABLE IF EXISTS" statement.
     * @param string $engine The storage engine to use (default is 'InnoDB', primarily for MySQL/MariaDB).
     * @param string $charset The character set for the table (default is 'utf8mb4').
     * @return string The generated SQL DDL statement or an empty string if the database type is unsupported.
     */
    public function dumpStructureFromSchema($entity, $databaseType, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $tableName = $entity['name'];
        
        // 1. Initialize Tool based on Database Type
        $tool = $this->getDatabaseDumpTool($databaseType);

        $columns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();

        // 2. Mapping Key Constraints
        foreach($entity['columns'] as $col) {
            if($col['primaryKey']) {
                $primaryKeys[] = $col['name'];
            }
            if($col['autoIncrement']) {
                $autoIncrementKeys[] = $col['name'];
            }
        }

        // 3. Generate Column Definitions
        foreach($entity['columns'] as $col) {
            if(!empty($col['length']))
            {
                $col['type'] = $col['type']."(".$col['length'].")";
            }
            else if(stripos($col['type'], 'enum') === 0 && !empty($col['values']))
            {
                $col['type'] = $col['type']."(".$col['values'].")";
            }
            $columns[] = $tool->createColumn($col, $autoIncrementKeys, $primaryKeys);
        }

        // 4. Only append table-level PRIMARY KEY if it's a composite key (more than 1)
        if(count($primaryKeys) > 1)
        {
            $columns[] = "\tPRIMARY KEY (" . implode(", ", $primaryKeys) . ")";
        }

        $query = array();

        // 5. Add DROP TABLE with comment
        if ($dropIfExists) {
            $query[] = "-- DROP TABLE IF EXISTS $tableName;";
            $query[] = "";
        }

        // 6. Create Statement
        $createStatement = "CREATE TABLE" . ($createIfNotExists ? " IF NOT EXISTS" : "");
        $query[] = "$createStatement $tableName (";
        $query[] = implode(",\r\n", $columns);
        
        // 7. Handle Engine & Charset only for MySQL/MariaDB
        $tableOptions = "";
        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            $tableOptions = " ENGINE=$engine DEFAULT CHARSET=$charset";
        }
        
        $query[] = ")" . $tableOptions . ";";

        return implode("\r\n", $query)."\r\n\r\n";
    }
    
    /**
     * Dumps data from a schema entity into batched SQL INSERT statements.
     *
     * @param array $entity The entity containing table name, columns, and data.
     * @param string $databaseType The database type (e.g., MySQL, PostgreSQL).
     * @param int $batchSize Number of records per INSERT statement.
     * @return string The generated SQL script.
     */
    public function dumpDataFromSchema($entity, $databaseType, $batchSize = 100)
    {
        // Check if the target database is PostgreSQL
        $isPgSql = $databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL || $databaseType == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL;

        // Check if the target database is SQLite
        $isSqlite = $databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE;

        // Check if the target database is SQL Server
        $isSqlServer = $databaseType == PicoDatabaseType::DATABASE_TYPE_SQLSERVER;
        
        $tableName = $entity['name'];

        // 1. Prepare Column Information for type-casting
        $columnInfo = $this->prepareColumnInfo($entity);

        $validColumnNames = array_keys($columnInfo);

        $allSql = "";

        // 2. Process Data with Batching
        if (isset($entity['data']) && is_array($entity['data']) && !empty($entity['data'])) {
            // Split the data into smaller chunks based on the batch size
            $batches = array_chunk($entity['data'], $batchSize);

            foreach ($batches as $batch) {
                $sqlInsert = array();

                // 1. Filter setiap row agar hanya kolom valid
                $filteredBatch = array();
                foreach ($batch as $data) {
                    $filteredBatch[] = array_intersect_key(
                        $data,
                        array_flip($validColumnNames)
                    );
                }

                if (empty($filteredBatch)) {
                    continue;
                }

                // 2. Ambil column names dari hasil filter
                $columnNames = implode(", ", array_keys($filteredBatch[0]));
                $sqlInsert[] = "INSERT INTO $tableName ($columnNames) VALUES";

                $rows = array();
                foreach ($filteredBatch as $data) {
                    $rows[] = "(" . implode(", ", $this->fixData($data, $columnInfo, $isPgSql, $isSqlite, $isSqlServer)) . ")";
                }

                $allSql .= implode("\r\n", $sqlInsert)
                    . "\r\n"
                    . implode(",\r\n", $rows)
                    . ";\r\n\r\n";
            }
        }

        return $allSql;
    }
    
    /**
     * Prepares and normalizes column metadata from the entity schema.
     *
     * This method iterates through the column definitions of an entity and
     * transforms them into a structured associative array of column information,
     * indexed by the column names.
     *
     * @param array $entity The entity schema containing the 'columns' definition.
     * @return array<string, \stdClass> An associative array where keys are column names 
     * and values are column metadata objects.
     */
    private function prepareColumnInfo($entity)
    {
        $columnInfo = array();
        if (isset($entity['columns']) && is_array($entity['columns'])) {
            foreach ($entity['columns'] as $column) {
                // Assuming getColumnInfo returns an object/stdClass based on previous code
                $columnInfo[$column['name']] = $this->getColumnInfo($column);
            }
        }
        return $columnInfo;
    }
    
    /**
     * Formats raw data values based on column metadata and database requirements.
     *
     * @param array $data Associative array of column => value.
     * @param array $columnInfo Metadata for each column.
     * @param bool $isPgSql Whether the target database is PostgreSQL.
     * @param bool $isSqlite Whether the target database is SQLite.
     * @param bool $isSqlServer Whether the target database is SQL Server.
     * @return array The formatted data array.
     */
    public function fixData($data, $columnInfo, $isPgSql, $isSqlite, $isSqlServer)
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                // Handle NULL values
                $data[$key] = 'null';
            } else if (isset($columnInfo[$key]) && in_array($columnInfo[$key]->normalizedType, ['integer', 'float'])) {
                // Keep numeric values as they are (no quotes)
                $data[$key] = $value;
            } else if (isset($columnInfo[$key]) && in_array($columnInfo[$key]->normalizedType, ['boolean', 'bool'])) {
                // Handle boolean values
                if($isPgSql)
                {
                    // Force to boolean
                    $data[$key] = $this->toBoolean($value) ? 'true' : 'false';
                }
                else if($isSqlite || $isSqlServer)
                {
                    // Force to integer
                    $data[$key] = $this->toBoolean($value) ? '1' : '0';
                }
                else
                {
                    // MySQL and MariaDB
                    $data[$key] = $this->toBoolean($value) ? 'true' : 'false';
                }
            } else {
                // Treat as string: escape single quotes and wrap in quotes
                $data[$key] = "'" . str_replace("'", "''", $value) . "'";
            }
        }
        return $data;
    }
    
    /**
     * Converts a value to a boolean representation.
     *
     * @param mixed $value The value to check.
     * @return bool
     */
    public function toBoolean($value)
    {
        return $value === 1 || $value === 'true' || $value === 'TRUE' || $value === '1' || $value === true;
    }
    
    /**
     * Normalizes column metadata into a standard object.
     *
     * @param array $column The column definition from schema.
     * @return stdClass
     */
    public function getColumnInfo($column)
    {
        $ret = new stdClass;
        $ret->type = $column['type'];
        $ret->length = isset($column['length']) ? $column['length'] : null;
        $ret->normalizedType = $this->normalizeDbType($column['type'], $ret->length);
        return $ret;
    }

    /**
     * Dump the structure of a table for the specified entity.
     *
     * @param MagicObject $entity Entity to be dumped
     * @param string $databaseType Target database type
     * @param bool $createIfNotExists Add DROP TABLE IF EXISTS before create table
     * @param bool $dropIfExists Add IF NOT EXISTS on create table
     * @param string $engine Storage engine (for MariaDB and MySQL)
     * @param string $charset Default charset
     * @return string SQL statement for creating table structure
     */
    public function dumpStructure($entity, $databaseType, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $databasePersist = new PicoDatabasePersistence(null, $entity);
        $tableInfo = $databasePersist->getTableInfo();
        $picoTableName = $tableInfo->getTableName();

        $result = "";
        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) 
        {
            $tool = new PicoDatabaseUtilMySql();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        } 
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL) 
        {
            $tool = new PicoDatabaseUtilPostgreSql();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) 
        {
            $tool = new PicoDatabaseUtilSqlite();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLSERVER) 
        {
            $tool = new PicoDatabaseUtilSqlServer();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        return $result;
    }

    /**
     * Dump the structure of a specified table.
     *
     * @param PicoTableInfo $tableInfo Table information
     * @param string $databaseType Database type
     * @param bool $createIfNotExists Flag to add CREATE IF NOT EXISTS
     * @param bool $dropIfExists Flag to add DROP IF EXISTS
     * @param string $engine Database engine
     * @param string $charset Charset
     * @return string SQL statement for creating table structure
     */
    public function dumpStructureTable($tableInfo, $databaseType, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $picoTableName = $tableInfo->getTableName();

        $result = "";
        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            $tool = new PicoDatabaseUtilMySql();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        } 
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL) 
        {
            $tool = new PicoDatabaseUtilPostgreSql();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) 
        {
            $tool = new PicoDatabaseUtilSqlite();
            $result = $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        return $result;
    }
    
    /**
     * Normalize a database-specific column type into a generic type.
     *
     * Supported DBMS: MySQL, MariaDB, PostgreSQL, SQLite, SQL Server
     *
     * Possible return values:
     * - string
     * - integer
     * - float
     * - boolean
     * - date
     * - time
     * - datetime
     * - binary
     * - json
     * - uuid
     * - enum
     * - geometry
     * - unknown
     *
     * @param string $dbType The raw column type from the database (e.g., VARCHAR(255), INT, NUMERIC(10,2), TEXT, etc.)
     * @return string One of the normalized type names listed above.
     */
    public function normalizeDbType($dbType, $length = null)
    {
        $normalized = 'string'; // default fallback
        $rawType = strtolower(trim($dbType));

        // Special case: MySQL TINYINT(1) → boolean
        if ($rawType === 'tinyint' && isset($length) && (string) $length === '1') {
            return 'boolean'; // acceptable early return for explicit edge-case
        }

        // Remove size & precision: varchar(255) → varchar
        $type = preg_replace('/\(.+\)/', '', $rawType);

        $map = [
            'integer' => [
                'int', 'integer', 'smallint', 'mediumint',
                'bigint', 'serial', 'bigserial', 'tinyint'
            ],
            'float' => [
                'float', 'double', 'decimal', 'numeric',
                'real', 'money', 'smallmoney'
            ],
            'boolean' => [
                'boolean', 'bool', 'bit'
            ],
            'string' => [
                'char', 'varchar', 'text', 'tinytext',
                'mediumtext', 'longtext', 'nchar',
                'nvarchar', 'citext'
            ],
            'uuid' => [
                'uuid'
            ],
            'json' => [
                'json', 'jsonb'
            ],
            'binary' => [
                'blob', 'binary', 'varbinary',
                'image', 'bytea'
            ],
            'date' => [
                'date'
            ],
            'time' => [
                'time'
            ],
            'datetime' => [
                'datetime', 'timestamp', 'year'
            ]
        ];

        foreach ($map as $result => $types) {
            if (in_array($type, $types, true)) {
                $normalized = $result;
                break;
            }
        }

        return $normalized;
    }

    /**
     * Get the table information for the specified entity.
     *
     * @param MagicObject $entity Entity
     * @return PicoTableInfo|null Table information or null if entity is null
     */
    public function getTableInfo($entity)
    {
        if ($entity != null) {
            return $entity->tableInfo();
        } else {
            return null;
        }
    }

    /**
     * Update the query for adding a column to a table.
     *
     * @param string $query Query string
     * @param string $lastColumn Last column name
     * @param string $databaseType Database type
     * @return string Updated query string
     */
    public function updateQueryAlterTableAddColumn($query, $lastColumn, $databaseType)
    {
        if ($lastColumn != null && ($databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB)) {
            $query .= " AFTER " . $lastColumn;
        }
        return $query;
    }

    /**
     * Update the query to set a column as nullable.
     *
     * @param string $query Query string
     * @param array $entityColumn Entity column information
     * @return string Updated query string
     */
    public function updateQueryAlterTableNullable($query, $entityColumn)
    {
        if ($entityColumn['nullable']) {
            $query .= " NULL";
        }
        return $query;
    }

    /**
     * Updates an ALTER TABLE query to set a default value for a specific column.
     *
     * This method appends a DEFAULT clause to the given query string based on the
     * provided entity column configuration. If the default value is 'NULL' (case-sensitive)
     * or 'null', it will explicitly set the default to NULL. Otherwise, it will pass
     * the value through {@see fixDefaultValue()} to ensure proper formatting based
     * on the column type and database type.
     *
     * @param string $query         The base ALTER TABLE query string to be updated.
     * @param array  $entityColumn  The entity column definition array, which should
     *                              contain the {@see MagicObject::KEY_DEFAULT_VALUE} key
     *                              if a default value is defined.
     * @param string|null $columnType   The column's data type (e.g., 'VARCHAR', 'INT', etc.).
     * @param string|null $databaseType The target database type (e.g., MySQL, PostgreSQL, SQLite, SQL Server).
     *
     * @return string The updated query string including the DEFAULT clause (if applicable).
     */
    public function updateQueryAlterTableDefaultValue($query, $entityColumn, $columnType = null, $databaseType = null)
    {
        if (isset($entityColumn[MagicObject::KEY_DEFAULT_VALUE])) {
            if ($entityColumn[MagicObject::KEY_DEFAULT_VALUE] == 'NULL' || $entityColumn[MagicObject::KEY_DEFAULT_VALUE] == 'null') {
                $query .= " DEFAULT NULL";
            } else {
                $query .= " DEFAULT " . $this->fixDefaultValue($entityColumn[MagicObject::KEY_DEFAULT_VALUE], $columnType, $databaseType);
            }
        }
        return $query;
    }

    /**
     * Formats and escapes a default value for use in a database schema query.
     *
     * This method ensures the default value is correctly formatted depending on
     * the column's data type and the target database type, using the appropriate
     * database utility class.
     *
     * @param mixed       $defaultValue The raw default value to be fixed.
     * @param string      $type         The column's data type.
     * @param string|null $databaseType The target database type (e.g., MySQL, PostgreSQL, SQLite, SQL Server).
     *
     * @return string The properly formatted default value for inclusion in a query.
     */
    public function fixDefaultValue($defaultValue, $type, $databaseType = null)
    {
        if($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL)
        {
            $util = new PicoDatabaseUtilPostgreSql();
        }
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            $util = new PicoDatabaseUtilSqlite();
        }
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLSERVER)
        {
            $util = new PicoDatabaseUtilSqlServer();
        }
        else
        {
            $util = new PicoDatabaseUtilMySql();
        }
        return $util->fixDefaultValue($defaultValue, $type);
    }

    /**
     * Create an ALTER TABLE ADD COLUMN query for the specified entity or entities.
     *
     * This method generates SQL queries to add new columns to a table. It supports adding columns 
     * from either a single entity or an array of entities. Depending on whether a single entity or 
     * multiple entities are provided, the method delegates query generation to different helper methods.
     * If the `$forceCreateNewTable` flag is set to true, the method will generate a `CREATE TABLE` query 
     * instead of an `ALTER TABLE` query, effectively creating a new table with the specified columns.
     *
     * @param MagicObject|MagicObject[] $entity A single entity or an array of entities representing the columns to be added. 
     *                                          Each entity contains the column name, type, and other related information.
     * @param PicoDatabase|null $database The database connection used to fetch the current table schema. If null, 
     *                                    the default database will be used.
     * @param bool $createIfNotExists Flag indicating whether to generate a CREATE TABLE query if the table does not exist. 
     *                                Default is false.
     * @param bool $dropIfExists Flag indicating whether to generate a DROP TABLE query if the table already exists, 
     *                           before the CREATE TABLE query. Default is false.
     * @param bool $forceCreateNewTable Flag indicating whether to generate a `CREATE TABLE` query instead of `ALTER TABLE`, 
     *                                  effectively creating a new table. Default is false.
     * 
     * @return string[] An array of SQL queries (either ALTER TABLE or CREATE TABLE) to add the columns.
     */
    public function createAlterTableAdd($entity, $database = null, $createIfNotExists = false, $dropIfExists = false, $forceCreateNewTable = false)
    {
        if (is_array($entity)) {
            return $this->createAlterTableAddFromEntities($entity, null, $database, $createIfNotExists, $dropIfExists, $forceCreateNewTable);
        } else {
            return $this->createAlterTableAddFromEntity($entity, $forceCreateNewTable);
        }
    }

    /**
     * Get the database connection.
     *
     * @param PicoDatabase|null $database Database connection
     * @param MagicObject[] $entities Entities
     * @return PicoDatabase Database connection
     */
    private function getDatabase($database, $entities)
    {
        if (!isset($database)) {
            $database = $entities[0]->currentDatabase();
        }
        return $database;
    }

    /**
     * Get the database type.
     *
     * @param PicoDatabase $database Database connection
     * @return string Database type
     */
    public function getDatabaseType($database)
    {
        return isset($database) ? $database->getDatabaseType() : PicoDatabaseType::DATABASE_TYPE_MYSQL;
    }

    /**
     * Get the table name.
     *
     * @param string|null $tableName Table name
     * @param PicoTableInfo $tableInfo Table information
     * @return string Table name
     */
    private function getTableName($tableName, $tableInfo)
    {
        return isset($tableName) ? $tableName : $tableInfo->getTableName();
    }

    /**
     * Create an ALTER TABLE query to add a column.
     *
     * @param string $tableName Table name
     * @param string $columnName Column name
     * @param string $columnType Column type
     * @return string SQL ALTER TABLE query
     */
    public function createQueryAlterTable($tableName, $columnName, $columnType)
    {
        $format = "ALTER TABLE %s ADD COLUMN %s %s";
        return sprintf($format, $tableName, $columnName, $columnType);
    }

    /**
     * Get a list of column names from multiple entities.
     *
     * This method retrieves the table information for each entity, extracts the columns,
     * and merges them into a single list.
     *
     * @param MagicObject[] $entities Array of entities to process.
     * @return string[] List of column names from all entities.
     */
    public function getColumnNameList($entities)
    {
        $res = array();
        foreach ($entities as $entity) {
            $tableInfo = $this->getTableInfo($entity);
            $columns = $tableInfo->getColumns();
            $res = array_merge($res, array_keys($columns));
        }
        $res = array_unique($res);
        return $res;
    }

    /**
     * Create a list of ALTER TABLE ADD COLUMN queries from multiple entities.
     *
     * This method generates SQL queries to add new columns to an existing table based on the provided entities. 
     * It checks the current database schema and compares it with the provided entity information, generating the 
     * necessary ALTER TABLE queries. If columns already exist in the database, they will be skipped unless the 
     * `forceCreateNewTable` flag is set to true, in which case a `CREATE TABLE` query will be generated to create 
     * the table (and columns) from scratch, even if the columns already exist.
     *
     * @param MagicObject[] $entities An array of entity objects representing the columns to be added. Each entity contains 
     *                                the column name, type, and other related information.
     * @param string|null $tableName The name of the table to alter. If null, the table name will be derived from the entities.
     * @param PicoDatabase|null $database The database connection used to fetch the current table schema. If null, it will 
     *                                    be inferred from the entities.
     * @param bool $createIfNotExists Flag indicating whether a `CREATE TABLE` query should be generated if the table does not exist. 
     *                                Default is false.
     * @param bool $dropIfExists Flag indicating whether a `DROP TABLE` query should be generated if the table already exists, 
     *                           before the `CREATE TABLE` query. Default is false.
     * @param bool $forceCreateNewTable Flag indicating whether to generate a `CREATE TABLE` query instead of an `ALTER TABLE` query. 
     *                                  If true, the table (and its columns) will be created from scratch, even if the columns 
     *                                  already exist. Default is false.
     * 
     * @return string[] An array of SQL queries (either ALTER TABLE or CREATE TABLE) to add the columns. Each query is a string 
     *                  representing a complete SQL statement.
     */
    public function createAlterTableAddFromEntities($entities, $tableName = null, $database = null, $createIfNotExists = false, $dropIfExists = false, $forceCreateNewTable = false)
    {
        $tableInfo = $this->getMergedTableInfo($entities);
        $columnNameList = $this->getColumnNameList($entities);
        $tableInfo->setSortedColumnName($columnNameList);
        $tableName = $this->getTableName($tableName, $tableInfo);
        $database = $this->getDatabase($database, $entities);

        $queryAlter = array();
        $numberOfColumn = count($tableInfo->getColumns());

        $dataTypeConverter = new DatabaseTypeConverter();

        if (!empty($tableInfo->getColumns())) {
            $dbColumnNames = array();
            $rows = PicoColumnGenerator::getColumnList($database, $tableInfo->getTableName());
            $createdColumns = array();
            if (is_array($rows) && !empty($rows) && !$forceCreateNewTable) {
                foreach ($rows as $row) {
                    $dbColumnNames[] = $row['Field'];
                }
                $lastColumn = null;
                foreach ($tableInfo->getColumns() as $entityColumn) {
                    if (!in_array($entityColumn['name'], $dbColumnNames)) {
                        $createdColumns[] = $entityColumn['name'];
                        $columnType = $dataTypeConverter->convertType($entityColumn['type'], $database->getDatabaseType());
                        $query = $this->createQueryAlterTable($tableName, $entityColumn['name'], $columnType);
                        $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                        $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn, $columnType, $database->getDatabaseType());
                        $query = $this->updateQueryAlterTableAddColumn($query, $lastColumn, $database->getDatabaseType());
                        $queryAlter[] = $query . ";";
                    }
                    $lastColumn = $entityColumn['name'];
                }
                $queryAlter = $this->addPrimaryKey($queryAlter, $tableInfo, $tableName, $createdColumns);
                $queryAlter = $this->addAutoIncrement($queryAlter, $tableInfo, $tableName, $createdColumns, $database->getDatabaseType());
            } else if ($numberOfColumn > 0) {
                $queryAlter[] = $this->dumpStructureTable($tableInfo, $database->getDatabaseType(), $createIfNotExists, $dropIfExists);
            }
        }
        return $queryAlter;
    }

    /**
     * Create a list of ALTER TABLE ADD COLUMN queries or a CREATE TABLE query from a single entity.
     *
     * This method generates SQL queries to add new columns to a table based on the provided entity. 
     * It compares the current database schema with the entity's column definitions. If the columns do not already exist,
     * it generates the necessary ALTER TABLE queries. However, if the `forceCreateNewTable` flag is set to true,
     * a CREATE TABLE query will be generated instead of ALTER TABLE, effectively creating the table and columns from scratch 
     * even if the columns already exist in the database.
     *
     * @param MagicObject $entity The entity representing the table and its columns to be added.
     * @param bool $forceCreateNewTable Flag indicating whether to generate a CREATE TABLE query, even if the table and 
     *                                  columns already exist. When true, a CREATE TABLE query will be generated to create 
     *                                  the table and its columns from scratch. Default is false.
     * 
     * @return string[] An array of SQL queries (either ALTER TABLE or CREATE TABLE) to add the columns. Each query 
     *                  is a string representing a complete SQL statement.
     */
    public function createAlterTableAddFromEntity($entity, $forceCreateNewTable = false)
    {
        $tableInfo = $this->getTableInfo($entity);
        $tableName = $tableInfo->getTableName();
        $database = $entity->currentDatabase();

        $queryAlter = array();
        $numberOfColumn = count($tableInfo->getColumns());

        $dataTypeConverter = new DatabaseTypeConverter();

        if (!empty($tableInfo->getColumns())) {
            $dbColumnNames = array();
            $rows = PicoColumnGenerator::getColumnList($database, $tableInfo->getTableName());
            $createdColumns = array();
            if (is_array($rows) && !empty($rows) && !$forceCreateNewTable) {
                foreach ($rows as $row) {
                    $dbColumnNames[] = $row['Field'];
                }
                $lastColumn = null;
                foreach ($tableInfo->getColumns() as $entityColumn) {
                    if (!in_array($entityColumn['name'], $dbColumnNames)) {
                        $createdColumns[] = $entityColumn['name'];
                        $columnType = $dataTypeConverter->convertType($entityColumn['type'], $database->getDatabaseType());
                        $query = $this->createQueryAlterTable($tableName, $entityColumn['name'], $columnType);
                        $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                        $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn);
                        $query = $this->updateQueryAlterTableAddColumn($query, $lastColumn, $database->getDatabaseType());
                        $queryAlter[] = $query . ";";
                    }
                    $lastColumn = $entityColumn['name'];
                }
                $queryAlter = $this->addPrimaryKey($queryAlter, $tableInfo, $tableName, $createdColumns);
                $queryAlter = $this->addAutoIncrement($queryAlter, $tableInfo, $tableName, $createdColumns, $database->getDatabaseType());
            } else if ($numberOfColumn > 0) {
                $queryAlter[] = $this->dumpStructure($entity, $database->getDatabaseType());
            }
        }
        return $queryAlter;
    }

    /**
     * Add primary key constraints to the ALTER TABLE queries.
     *
     * @param string[] $queryAlter Existing ALTER TABLE queries
     * @param PicoTableInfoExtended $tableInfo Table information
     * @param string $tableName Table name
     * @param string[] $createdColumns List of created columns
     * @return string[] Updated ALTER TABLE queries
     */
    private function addPrimaryKey($queryAlter, $tableInfo, $tableName, $createdColumns)
    {
        $pk = $tableInfo->getPrimaryKeys();
        $queries = array();
        if (isset($pk) && is_array($pk) && !empty($pk)) {
            foreach ($pk as $primaryKey) {
                if (in_array($primaryKey['name'], $createdColumns)) {
                    $queries[] = "";
                    $queries[] = "ALTER TABLE $tableName";
                    $queries[] = "\tADD PRIMARY KEY ($primaryKey[name])";
                    $queries[] = ";";
                }
            }
            $queryAlter[] = implode("\r\n", $queries);
        }
        return $queryAlter;
    }

    /**
     * Add auto-increment functionality to specified columns.
     *
     * @param string[] $queryAlter Existing ALTER TABLE queries
     * @param PicoTableInfoExtended $tableInfo Table information
     * @param string $tableName Table name
     * @param string[] $createdColumns List of created columns
     * @param string $databaseType Database type
     * @return string[] Updated ALTER TABLE queries
     */
    private function addAutoIncrement($queryAlter, $tableInfo, $tableName, $createdColumns, $databaseType)
    {
        $queries = array();
        $aik = $this->getAutoIncrementKey($tableInfo);
        
        foreach ($tableInfo->getColumns() as $entityColumn) {
            if (isset($aik) && is_array($aik) && in_array($entityColumn['name'], $aik) && in_array($entityColumn['name'], $createdColumns)) {
                $query = sprintf("%s %s", $entityColumn['name'], $entityColumn['type']);
                $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn);

                if ($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
                    $columnName = $entityColumn['name'];
                    $sequenceName = $tableName . "_" . $columnName;
                    $queries[] = "";
                    $queries[] = "DROP SEQUENCE IF EXISTS $sequenceName;";
                    $queries[] = "CREATE SEQUENCE $sequenceName MINVALUE 1;";
                    $queries[] = "ALTER TABLE $tableName \r\n\tALTER $columnName SET DEFAULT nextval('$sequenceName')";
                    $queries[] = ";";
                } else {
                    $queries[] = "";
                    $queries[] = "ALTER TABLE $tableName \r\n\tMODIFY $query AUTO_INCREMENT";
                    $queries[] = ";";
                }

                $queryAlter[] = implode("\r\n", $queries);
            }
        }
        return $queryAlter;
    }

    /**
     * Get the auto-increment keys from table information.
     *
     * @param PicoTableInfo $tableInfo Table information
     * @return string[] List of auto-increment keys
     */
    public function getAutoIncrementKey($tableInfo)
    {
        $autoIncrement = $tableInfo->getAutoIncrementKeys();
        $autoIncrementKeys = array();
        
        if (is_array($autoIncrement) && !empty($autoIncrement)) {
            foreach ($autoIncrement as $col) {
                if ($col["strategy"] == 'GenerationType.IDENTITY') {
                    $autoIncrementKeys[] = $col["name"];
                }
            }
        }
        return $autoIncrementKeys;
    }

    /**
     * Merge multiple entities' table information.
     *
     * @param MagicObject[] $entities Entities
     * @return PicoTableInfoExtended Merged table information
     * @deprecated deprecated since version 1.13
     */
    public function getMergedTableInfoOld($entities)
    {
        $mergedTableInfo = PicoTableInfoExtended::getInstance();
        
        foreach ($entities as $entity) {
            $tableInfo = $this->getTableInfo($entity);
            $mergedTableInfo->setTableName($tableInfo->getTableName());

            $mergedTableInfo->setColumns(array_merge($mergedTableInfo->getColumns(), $tableInfo->getColumns()));
            $mergedTableInfo->setJoinColumns(array_merge($mergedTableInfo->getJoinColumns(), $tableInfo->getJoinColumns()));
            $mergedTableInfo->setPrimaryKeys(array_merge($mergedTableInfo->getPrimaryKeys(), $tableInfo->getPrimaryKeys()));
            $mergedTableInfo->setAutoIncrementKeys(array_merge($mergedTableInfo->getAutoIncrementKeys(), $tableInfo->getAutoIncrementKeys()));
            $mergedTableInfo->setDefaultValue(array_merge($mergedTableInfo->getDefaultValue(), $tableInfo->getDefaultValue()));
            $mergedTableInfo->setNotNullColumns(array_merge($mergedTableInfo->getNotNullColumns(), $tableInfo->getNotNullColumns()));
        }

        // Ensure uniqueness of the attributes
        $mergedTableInfo->uniqueColumns();
        $mergedTableInfo->uniqueJoinColumns();
        $mergedTableInfo->uniquePrimaryKeys();
        $mergedTableInfo->uniqueAutoIncrementKeys();
        $mergedTableInfo->uniqueDefaultValue();
        $mergedTableInfo->uniqueNotNullColumns();

        return $mergedTableInfo;
    }

    /**
     * Get merged table information from multiple entities.
     *
     * @param MagicObject[] $entities Entities
     * @return PicoTableInfoExtended Merged table information
     */
    public function getMergedTableInfo($entities)
    {
        $mergedTableInfo = PicoTableInfoExtended::getInstance();
        
        foreach ($entities as $entity) {
            $tableInfo = $this->getTableInfo($entity);
            $mergedTableInfo->setTableName($tableInfo->getTableName());

            $mergedTableInfo->mergeColumns($tableInfo->getColumns());
            $mergedTableInfo->mergeJoinColumns($tableInfo->getJoinColumns());
            $mergedTableInfo->mergePrimaryKeys($tableInfo->getPrimaryKeys());
            $mergedTableInfo->mergeAutoIncrementKeys($tableInfo->getAutoIncrementKeys());
            $mergedTableInfo->mergeDefaultValue($tableInfo->getDefaultValue());
            $mergedTableInfo->mergeNotNullColumns($tableInfo->getNotNullColumns());
        }

        return $mergedTableInfo;
    }

    /**
     * Dumps data into SQL format.
     *
     * This method processes the provided data and converts it into SQL format suitable for the specified 
     * database type. It requires a model instance to retrieve table information if not already set.
     *
     * WARNING: Use a different instance to dump different entities to avoid conflicts.
     *
     * @param MagicObject|PicoPageData $data Data to be dumped into SQL format.
     * @param string $databaseType Target database type (e.g., MySQL, MariaDB).
     * @param MagicObject|null $entity Optional model instance used to retrieve table information 
     *                                   if it is not already set.
     * @param int $maxRecord Maximum number of records to process in a single query (default is 100).
     * @param callable|null $callbackFunction Optional callback function to process the generated SQL 
     *                                         statements. The function should accept a single string parameter 
     *                                         representing the SQL statement.
     * @return string SQL dump as a string; returns an empty string for unsupported database types.
     */
    public function dumpData($data, $databaseType, $entity = null, $maxRecord = 100, $callbackFunction = null)
    {
        // Initialize table information if it hasn't been set yet
        if (!isset($this->tableInfo)) {
            $databasePersist = new PicoDatabasePersistence(null, $entity);
            $this->tableInfo = $databasePersist->getTableInfo();
            $this->picoTableName = $this->tableInfo->getTableName();
            $this->columns = $this->tableInfo->getColumns();
        }

        // Check the database type and call the appropriate utility for dumping data
        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            $tool = new PicoDatabaseUtilMySql();
            return $tool->dumpData($this->columns, $this->picoTableName, $data, $maxRecord, $callbackFunction);
        } else {
            $tool = new PicoDatabaseUtilPostgreSql();
            return $tool->dumpData($this->columns, $this->picoTableName, $data, $maxRecord, $callbackFunction);
        }
    }
}