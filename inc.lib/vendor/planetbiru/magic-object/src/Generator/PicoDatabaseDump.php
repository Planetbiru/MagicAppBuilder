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

/**
 * Database dump
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
     * Dump table structure
     *
     * @param MagicObject $entity Entity to be dump
     * @param string $databaseType Target database type
     * @param boolean $createIfNotExists Add DROP TABLE IF EXISTS before create table
     * @param boolean $dropIfExists Add IF NOT EXISTS on create table
     * @param string $engine Storage engine (for MariaDB and MySQL)
     * @param string $charset Default charset
     * @return string
     */
    public function dumpStructure($entity, $databaseType, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $databasePersist = new PicoDatabasePersistence(null, $entity);
        $tableInfo = $databasePersist->getTableInfo();
        $picoTableName = $tableInfo->getTableName();

        if($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL)
        {
            return PicoDatabaseUtilMySql::dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        else
        {
            return "";
        }
    }

    /**
     * Dump strcuture of table
     *
     * @param PicoTableInfo $tableInfo Table information
     * @param string $databaseType Database type
     * @param boolean $createIfNotExists Flag to add create if not exists
     * @param boolean $dropIfExists Flag to add drop if exists
     * @param string $engine Database engine
     * @param string $charset Charset
     * @return string
     */
    public function dumpStructureTable($tableInfo, $databaseType, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $picoTableName = $tableInfo->getTableName();

        if($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL)
        {
            return PicoDatabaseUtilMySql::dumpStructure($tableInfo, $picoTableName, $createIfNotExists, $dropIfExists, $engine, $charset);
        }
        else
        {
            return "";
        }
    }

    /**
     * Get entity table info
     *
     * @param MagicObject $entity Entity
     * @return PicoTableInfo|null
     */
    public function getTableInfo($entity)
    {
        if($entity != null)
        {
            return $entity->tableInfo();
        }
        else
        {
            return null;
        }
    }

    /**
     * Update query alter table add column
     *
     * @param string $query Query string
     * @param string $lastColumn Last column
     * @param string $databaseType Database type
     * @return string
     */
    public function updateQueryAlterTableAddColumn($query, $lastColumn, $databaseType)
    {
        if($lastColumn != null && ($databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB))
        {
            $query .= " AFTER ".$lastColumn;
        }
        return $query;
    }

    /**
     * Update query alter table nullable
     *
     * @param string $query Query string
     * @param array $entityColumn Entity name
     * @return string
     */
    public function updateQueryAlterTableNullable($query, $entityColumn)
    {
        if($entityColumn['nullable'])
        {
            $query .= " NULL";
        }
        return $query;
    }

    /**
     * Update query alter table default value
     *
     * @param string $query Query string
     * @param array $entityColumn Entity column
     * @return string
     */
    public function updateQueryAlterTableDefaultValue($query, $entityColumn)
    {
        if(isset($entityColumn['default_value']))
        {
            if($entityColumn['default_value'] == 'NULL' || $entityColumn['default_value'] == 'null')
            {
                $query .= " DEFAULT NULL";
            }
            else
            {
                $query .= " DEFAULT ".PicoDatabaseUtil::escapeValue($entityColumn['default_value'], true);
            }
        }
        return $query;
    }

    /**
     * Create query ALTER TABLE ADD COLUMN
     *
     * @param MagicObject|MagicObject[] $entity Entity
     * @param PicoDatabase $database Database connection
     * @return string[]
     */
    public function createAlterTableAdd($entity, $database = null)
    {
        if(is_array($entity))
        {
            return $this->createAlterTableAddFromEntities($entity, $database);
        }
        else
        {
            return $this->createAlterTableAddFromEntity($entity);
        }
    }

    /**
     * Get database
     *
     * @param PicoDatabase $database Database connection
     * @param MagicObject[] $entities Entities
     * @return PicoDatabase
     */
    private function getDatabase($database, $entities)
    {
        if(!isset($database))
        {
            $database = $entities[0]->currentDatabase();
        }
        return $database;
    }

    /**
     *
     * Get database type
     * @param PicoDatabase $database Database connection
     * @return string
     */
    public function getDatabaseType($database)
    {
        if(isset($database))
        {
            $databaseType = $database->getDatabaseType();
        }
        else
        {
            $databaseType = PicoDatabaseType::DATABASE_TYPE_MYSQL;
        }
        return $databaseType;
    }

    /**
     *
     * Get table name
     * @param string $tableName Table name
     * @param PicoTableInfo $tableInfo Table information
     * @return string
     */
    private function getTableName($tableName, $tableInfo)
    {
        if(!isset($tableName))
        {
            $tableName = $tableInfo->getTableName();
        }
        return $tableName;
    }

    /**
     * Get create alter table query
     *
     * @param string $tableName Table name
     * @param string $columnName Column name
     * @param string $columnType Column type
     * @return string
     */
    public function createQueryAlterTable($tableName, $columnName, $columnType)
    {
        $format = "ALTER TABLE %s ADD COLUMN %s %s";
        return sprintf($format, $tableName, $columnName, $columnType);
    }

    /**
     * Create query ALTER TABLE ADD COLUMN
     *
     * @param MagicObject[] $entity Entity
     * @param PicoDatabase $database Database connection
     * @return string[]
     */
    public function createAlterTableAddFromEntities($entities, $tableName = null, $database = null)
    {
        $tableInfo = $this->getMergedTableInfo($entities);
        $tableName = $this->getTableName($tableName, $tableInfo);
        $database = $this->getDatabase($database, $entities);

        $queryAlter = array();
        $numberOfColumn = count($tableInfo->getColumns());
        if(!empty($tableInfo->getColumns()))
        {
            $dbColumnNames = array();
            $rows = PicoColumnGenerator::getColumnList($database, $tableInfo->getTableName());
            $createdColumns = array();
            if(is_array($rows) && !empty($rows))
            {
                foreach($rows as $row)
                {
                    $columnName = $row['Field'];
                    $dbColumnNames[] = $columnName;
                }
                $lastColumn = null;
                foreach($tableInfo->getColumns() as $entityColumn)
                {
                    if(!in_array($entityColumn['name'], $dbColumnNames))
                    {
                        $createdColumns[] = $entityColumn['name'];
                        $query = $this->createQueryAlterTable($tableName, $entityColumn['name'], $entityColumn['type']);
                        $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                        $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn);
                        $query = $this->updateQueryAlterTableAddColumn($query, $lastColumn, $database->getDatabaseType());
                        $queryAlter[]  = $query.";";
                    }
                    $lastColumn = $entityColumn['name'];
                }
                $queryAlter = $this->addPrimaryKey($queryAlter, $tableInfo, $tableName, $createdColumns);
                $queryAlter = $this->addAutoIncrement($queryAlter, $tableInfo, $tableName, $createdColumns, $database->getDatabaseType());
            }
            else if($numberOfColumn > 0)
            {
                $queryAlter[] = $this->dumpStructureTable($tableInfo, $database->getDatabaseType());
            }
        }
        return $queryAlter;
    }

    /**
     * Create query ALTER TABLE ADD COLUMN
     *
     * @param MagicObject|MagicObject[] $entity Entity
     * @param PicoDatabase $database Database connection
     * @return string[]
     */
    public function createAlterTableAddFromEntity($entity)
    {
        $tableInfo = $this->getTableInfo($entity);
        $tableName = $tableInfo->getTableName();
        $database = $entity->currentDatabase();

        $queryAlter = array();
        $numberOfColumn = count($tableInfo->getColumns());
        if(!empty($tableInfo->getColumns()))
        {
            $dbColumnNames = array();
            $rows = PicoColumnGenerator::getColumnList($database, $tableInfo->getTableName());
            $createdColumns = array();
            if(is_array($rows) && !empty($rows))
            {
                foreach($rows as $row)
                {
                    $columnName = $row['Field'];
                    $dbColumnNames[] = $columnName;
                }
                $lastColumn = null;
                foreach($tableInfo->getColumns() as $entityColumn)
                {
                    if(!in_array($entityColumn['name'], $dbColumnNames))
                    {
                        $createdColumns[] = $entityColumn['name'];
                        $query = $this->createQueryAlterTable($tableName, $entityColumn['name'], $entityColumn['type']);
                        $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                        $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn);
                        $query = $this->updateQueryAlterTableAddColumn($query, $lastColumn, $database->getDatabaseType());
                        $queryAlter[]  = $query.";";
                    }
                    $lastColumn = $entityColumn['name'];
                }
                $queryAlter = $this->addPrimaryKey($queryAlter, $tableInfo, $tableName, $createdColumns);
                $queryAlter = $this->addAutoIncrement($queryAlter, $tableInfo, $tableName, $createdColumns, $database->getDatabaseType());
            }
            else if($numberOfColumn > 0)
            {
                $queryAlter[] = $this->dumpStructure($entity, $database->getDatabaseType());
            }
        }
        return $queryAlter;
    }

    /**
     * Add primary key
     * @param string[] $queryAlter Query alter
     * @param PicoTableInfoExtended $tableInfo Table information
     * @param string $tableName Table name
     * @param string[] $createdColumns Create column
     * @return string[]
     */
    private function addPrimaryKey($queryAlter, $tableInfo, $tableName, $createdColumns)
    {
        $pk = $tableInfo->getPrimaryKeys();
        $queries = array();
        if(isset($pk) && is_array($pk) && !empty($pk))
        {

            foreach($pk as $primaryKey)
            {
                if(in_array($primaryKey['name'], $createdColumns))
                {
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
     * Add auto increment
     *
     * @param string[] $queryAlter  Query alter
     * @param PicoTableInfoExtended $tableInfo Table information
     * @param string $tableName Table name
     * @param string[] $createdColumns Create column
     * @param string $databaseType Database type
     * @return string[]
     */
    private function addAutoIncrement($queryAlter, $tableInfo, $tableName, $createdColumns, $databaseType)
    {
        $queries = array();
        $aik = $this->getAutoIncrementKey($tableInfo);
        foreach($tableInfo->getColumns() as $entityColumn)
        {
            if(isset($aik) && is_array($aik) && in_array($entityColumn['name'], $aik) && in_array($entityColumn['name'], $createdColumns))
            {
                $query = sprintf("%s %s", $entityColumn['name'], $entityColumn['type']);
                $query = $this->updateQueryAlterTableNullable($query, $entityColumn);
                $query = $this->updateQueryAlterTableDefaultValue($query, $entityColumn);

                if($databaseType == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL)
                {
                    $columnName = $entityColumn['name'];
                    $sequenceName = $tableName."_".$columnName;
                    $queries[] = "";
                    $queries[] = "DROP SEQUENCE IF EXISTS $sequenceName;";
                    $queries[] = "CREATE SEQUENCE $sequenceName MINVALUE 1;";
                    $queries[] = "ALTER TABLE $tableName \r\n\tALTER $columnName SET DEFAULT nextval('$sequenceName')";
                    $queries[] = ";";
                }
                else
                {
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
     * Get auto increment keys
     *
     * @param PicoTableInfo $tableInfo Table information
     * @return array
     */
    public function getAutoIncrementKey($tableInfo)
    {
        $autoIncrement = $tableInfo->getAutoIncrementKeys();
        $autoIncrementKeys = array();
        if(is_array($autoIncrement) && !empty($autoIncrement))
        {
            foreach($autoIncrement as $col)
            {
                if($col["strategy"] == 'GenerationType.IDENTITY')
                {
                    $autoIncrementKeys[] = $col["name"];
                }
            }
        }
        return $autoIncrementKeys;
    }

    /**
     * Merge entities
     * @param mixed $entities
     * @return PicoTableInfoExtended
     * @deprecated deprecated since version 1.13
     */
    public function getMergedTableInfoOld($entities)
    {
        $mergedTableInfo = PicoTableInfoExtended::getInstance();
        foreach($entities as $entity)
        {
            $tableInfo = $this->getTableInfo($entity);
            $mergedTableInfo->setTableName($tableInfo->getTableName());

            $mergedTableInfo->setColumns(array_merge($mergedTableInfo->getColumns(), $tableInfo->getColumns()));
            $mergedTableInfo->setJoinColumns(array_merge($mergedTableInfo->getJoinColumns(), $tableInfo->getJoinColumns()));
            $mergedTableInfo->setPrimaryKeys(array_merge($mergedTableInfo->getPrimaryKeys(), $tableInfo->getPrimaryKeys()));
            $mergedTableInfo->setAutoIncrementKeys(array_merge($mergedTableInfo->getAutoIncrementKeys(), $tableInfo->getAutoIncrementKeys()));
            $mergedTableInfo->setDefaultValue(array_merge($mergedTableInfo->getDefaultValue(), $tableInfo->getDefaultValue()));
            $mergedTableInfo->setNotNullColumns(array_merge($mergedTableInfo->getNotNullColumns(), $tableInfo->getNotNullColumns()));
        }

        $mergedTableInfo->uniqueColumns();
        $mergedTableInfo->uniqueJoinColumns();
        $mergedTableInfo->uniquePrimaryKeys();
        $mergedTableInfo->uniqueAutoIncrementKeys();
        $mergedTableInfo->uniqueDefaultValue();
        $mergedTableInfo->uniqueNotNullColumns();

        return $mergedTableInfo;
    }

    /**
     * Get merged table info
     *
     * @param MagicObject[] $entities Entities
     * @return PicoTableInfoExtended
     */
    public function getMergedTableInfo($entities)
    {
        $mergedTableInfo = PicoTableInfoExtended::getInstance();
        foreach($entities as $entity)
        {
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
     * Dump data to SQL.
     * WARNING!!! Use different instance to dump different entity
     *
     * @param MagicObject|PicoPageData $data Data to be dump
     * @param string $databaseType Target database type
     * @return string
     */
    public function dumpData($data, $databaseType)
    {
        if(!isset($this->tableInfo))
        {
            $entity = null;
            if($data instanceof PicoPageData && isset($data->getResult()[0]))
            {
                $entity = $data->getResult()[0];
            }
            else if($data instanceof MagicObject)
            {
                $entity = $data;
            }
            else if(is_array($data) && isset($data[0]) && $data[0] instanceof MagicObject)
            {
                $entity = $data[0];
            }
            if($entity == null)
            {
                return "";
            }

            $databasePersist = new PicoDatabasePersistence(null, $entity);
            $this->tableInfo = $databasePersist->getTableInfo();
            $this->picoTableName = $this->tableInfo->getTableName();
            $this->columns = $this->tableInfo->getColumns();
        }

        if($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL)
        {
            return PicoDatabaseUtilMySql::dumpData($this->columns, $this->picoTableName, $data);
        }
        else
        {
            return "";
        }
    }
}