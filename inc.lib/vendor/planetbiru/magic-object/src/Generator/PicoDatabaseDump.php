<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabasePersistence;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoPageData;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Database\PicoTableInfoExtended;
use MagicObject\MagicObject;
use MagicObject\Util\Database\PicoDatabaseUtil;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;
use MagicObject\Util\Database\PicoDatabaseUtilPostgreSql;
use MagicObject\Util\Database\PicoDatabaseUtilSqlite;

/**
 * Database dump class for managing and generating SQL statements
 * for table structures.
 * 
 * @author Kamshory
 * @package MagicObject\Generator
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseDump
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
     * Update the query to set a default value for a column.
     *
     * @param string $query Query string
     * @param array $entityColumn Entity column information
     * @return string Updated query string
     */
    public function updateQueryAlterTableDefaultValue($query, $entityColumn)
    {
        if (isset($entityColumn[MagicObject::KEY_DEFAULT_VALUE])) {
            if ($entityColumn[MagicObject::KEY_DEFAULT_VALUE] == 'NULL' || $entityColumn[MagicObject::KEY_DEFAULT_VALUE] == 'null') {
                $query .= " DEFAULT NULL";
            } else {
                $query .= " DEFAULT " . PicoDatabaseUtil::escapeValue($entityColumn[MagicObject::KEY_DEFAULT_VALUE], true);
            }
        }
        return $query;
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
                        $query = $this->createQueryAlterTable($tableName, $entityColumn['name'], $entityColumn['type']);
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
                $queryAlter[] = $this->dumpStructureTable($tableInfo, $database->getDatabaseType(), $createIfNotExists, $dropIfExists);
            }
        }
        return $queryAlter;
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
                        $query = $this->createQueryAlterTable($tableName, $entityColumn['name'], $entityColumn['type']);
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