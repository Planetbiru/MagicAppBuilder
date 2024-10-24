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

        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) 
        {
            $tool = new PicoDatabaseUtilMySql();
            return $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        } 
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) 
        {
            $tool = new PicoDatabaseUtilPostgreSql();
            return $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
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

        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            $tool = new PicoDatabaseUtilMySql();
            return $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        } 
        else if($databaseType == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) 
        {
            $tool = new PicoDatabaseUtilPostgreSql();
            return $tool->dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
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
        if (isset($entityColumn['default_value'])) {
            if ($entityColumn['default_value'] == 'NULL' || $entityColumn['default_value'] == 'null') {
                $query .= " DEFAULT NULL";
            } else {
                $query .= " DEFAULT " . PicoDatabaseUtil::escapeValue($entityColumn['default_value'], true);
            }
        }
        return $query;
    }

    /**
     * Create an ALTER TABLE ADD COLUMN query for the specified entity or entities.
     *
     * @param MagicObject|MagicObject[] $entity Entity or array of entities
     * @param PicoDatabase|null $database Database connection
     * @return string[] Array of SQL ALTER TABLE queries
     */
    public function createAlterTableAdd($entity, $database = null)
    {
        if (is_array($entity)) {
            return $this->createAlterTableAddFromEntities($entity, $database);
        } else {
            return $this->createAlterTableAddFromEntity($entity);
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
     * @param MagicObject[] $entities Entities
     * @param string|null $tableName Table name
     * @param PicoDatabase|null $database Database connection
     * @return string[] List of SQL ALTER TABLE queries
     */
    public function createAlterTableAddFromEntities($entities, $tableName = null, $database = null)
    {
        $tableInfo = $this->getMergedTableInfo($entities);
        $tableName = $this->getTableName($tableName, $tableInfo);
        $database = $this->getDatabase($database, $entities);

        $queryAlter = [];
        $numberOfColumn = count($tableInfo->getColumns());

        if (!empty($tableInfo->getColumns())) {
            $dbColumnNames = [];
            $rows = PicoColumnGenerator::getColumnList($database, $tableInfo->getTableName());
            $createdColumns = [];
            if (is_array($rows) && !empty($rows)) {
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
                $queryAlter[] = $this->dumpStructureTable($tableInfo, $database->getDatabaseType());
            }
        }
        return $queryAlter;
    }

    /**
     * Create a list of ALTER TABLE ADD COLUMN queries from a single entity.
     *
     * @param MagicObject $entity Entity
     * @return string[] List of SQL ALTER TABLE queries
     */
    public function createAlterTableAddFromEntity($entity)
    {
        $tableInfo = $this->getTableInfo($entity);
        $tableName = $tableInfo->getTableName();
        $database = $entity->currentDatabase();

        $queryAlter = [];
        $numberOfColumn = count($tableInfo->getColumns());

        if (!empty($tableInfo->getColumns())) {
            $dbColumnNames = [];
            $rows = PicoColumnGenerator::getColumnList($database, $tableInfo->getTableName());
            $createdColumns = [];
            if (is_array($rows) && !empty($rows)) {
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
        $queries = [];
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
        $queries = [];
        $aik = $this->getAutoIncrementKey($tableInfo);
        
        foreach ($tableInfo->getColumns() as $entityColumn) {
            if (isset($aik) && is_array($aik) && in_array($entityColumn['name'], $aik) && in_array($entityColumn['name'], $createdColumns)) {
                $query = sprintf("%s %s", $entityColumn['name'], $entityColumn['type']);
                $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn);

                if ($databaseType == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) {
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
        $autoIncrementKeys = [];
        
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