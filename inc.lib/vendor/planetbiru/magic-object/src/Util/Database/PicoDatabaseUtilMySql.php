<?php

namespace MagicObject\Util\Database;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoPageData;
use MagicObject\Database\PicoTableInfo;
use MagicObject\MagicObject;

class PicoDatabaseUtilMySql
{
    /**
     * Get column list
     *
     * @param PicoDatabase $database
     * @param string $picoTableName
     * @return array
     */
    public static function getColumnList($database, $picoTableName)
    {
        $sql = "SHOW COLUMNS FROM $picoTableName";
        return $database->fetchAll($sql);
    }

    /**
     * Get auto increment keys
     * 
     * @param PicoTableInfo $tableInfo Table information
     * @return array
     */
    public static function getAutoIncrementKey($tableInfo)
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
     * Dump database structure
     *
     * @param PicoTableInfo $tableInfo Table information
     * @param string $picoTableName
     * @return string
     */
    public static function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4')
    {
        $query = array();
        $columns = array();
        if($dropIfExists)
        {
            $query[] = "-- DROP TABLE IF EXISTS `$picoTableName`;";
            $query[] = "";
        }
        $createStatement = "";

        $createStatement = "CREATE TABLE";
        if($createIfNotExists)
        {
            $createStatement .= " IF NOT EXISTS";
        }

        $autoIncrementKeys = self::getAutoIncrementKey($tableInfo);

        $query[] = "$createStatement `$picoTableName` (";
        
        foreach($tableInfo->getColumns() as $column)
        {
            $columns[] = self::createColumn($column);
        }
        $query[] = implode(",\r\n", $columns);
        $query[] = ") ENGINE=$engine DEFAULT CHARSET=$charset;";
        
        $pk = $tableInfo->getPrimaryKeys();
        if(isset($pk) && is_array($pk) && !empty($pk))
        {
            $query[] = "";
            $query[] = "ALTER TABLE `$picoTableName`";
            foreach($pk as $primaryKey)
            {
                $query[] = "\tADD PRIMARY KEY (`$primaryKey[name]`)";
            } 
            $query[] = ";";       
        }
        
        foreach($tableInfo->getColumns() as $column)
        {
            if(isset($autoIncrementKeys) && is_array($autoIncrementKeys) && in_array($column['name'], $autoIncrementKeys))
            {
                $query[] = "";
                $query[] = "ALTER TABLE `$picoTableName` \r\n\tMODIFY ".trim(self::createColumn($column), " \r\n\t ")." AUTO_INCREMENT";
                $query[] = ";";
            }
        }
        
        return implode("\r\n", $query);
    }
    
    /**
     * Create column
     *
     * @param array $column
     * @return string
     */
    public static function createColumn($column)
    {
        $col = array();
        $col[] = "\t";
        $col[] = "`".$column['name']."`";
        $col[] = $column['type'];
        if(isset($column['nullable']) && strtolower(trim($column['nullable'])) == 'true')
        {
            $col[] = "NULL";
        } 
        else
        {
            $col[] = "NOT NULL";
        }
        if(isset($column['default_value']))
        {
            $defaultValue = $column['default_value'];
            $defaultValue = self::fixDefaultValue($defaultValue, $column['type']);
            $col[] = "DEFAULT $defaultValue";
        }
        return implode(" ", $col);
    }
    
    /**
     * Fixing default value
     *
     * @param string $defaultValue
     * @param string $type
     * @return string
     */
    public static function fixDefaultValue($defaultValue, $type)
    {
        if(strtolower($defaultValue) == 'true' || strtolower($defaultValue) == 'false' || strtolower($defaultValue) == 'null')
        {
            return $defaultValue;
        }
        if(stripos($type, 'char') !== false || stripos($type, 'text') !== false || stripos($type, 'int') !== false || stripos($type, 'float') !== false || stripos($type, 'double') !== false)
        {
            return "'".$defaultValue."'";
        }
        return $defaultValue;
    }
    
    /**
     * Dump data
     *
     * @param array $columns
     * @param string $picoTableName
     * @param MagicObject|PicoPageData $data
     * @return string
     */
    public static function dumpData($columns, $picoTableName, $data)
    {
        if($data instanceof PicoPageData && isset($data->getResult()[0]))
        {
            return self::dumpRecords($columns, $picoTableName, $data->getResult());
        }
        else if($data instanceof MagicObject)
        {
            return self::dumpRecords($columns, $picoTableName, array($data));
        }
        else if(is_array($data) && isset($data[0]) && $data[0] instanceof MagicObject)
        {
            return self::dumpRecords($columns, $picoTableName, $data);
        }
        return null;
    }
    
    /**
     * Dump records
     *
     * @param array $columns
     * @param string $picoTableName
     * @param MagicObject[] $data
     * @return string
     */
    public static function dumpRecords($columns, $picoTableName, $data)
    {
        $result = "";
        foreach($data as $record)
        {
            $result .= self::dumpRecord($columns, $picoTableName, $record).";\r\n";
        }
        return $result;
    }
    
    /**
     * Dump records
     *
     * @param array $columns
     * @param string $picoTableName
     * @param MagicObject $record
     * @return string
     */
    public static function dumpRecord($columns, $picoTableName, $record)
    {
        $value = $record->valueArray();
        $rec = array();
        foreach($value as $key=>$val)
        {
            if(isset($columns[$key]))
            {
                $rec[$columns[$key]['name']] = $val;
            }
        }
        $queryBuilder = new PicoDatabaseQueryBuilder(PicoDatabaseType::DATABASE_TYPE_MYSQL);
        $queryBuilder->newQuery()
            ->insert()
            ->into($picoTableName)
            ->fields(array_keys($rec))
            ->values(array_values($rec));
            
        return $queryBuilder->toString();
    }
}