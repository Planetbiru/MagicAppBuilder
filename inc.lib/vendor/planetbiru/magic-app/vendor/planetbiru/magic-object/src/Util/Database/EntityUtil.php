<?php

namespace MagicObject\Util\Database;

use MagicObject\MagicObject;
use stdClass;

class EntityUtil
{
    /**
     * Table property column
     * @param MagicObject $entity Input entity
     * @return string[]|array
     */
    public static function getPropertyColumn($entity)
    {
        $tableInfo = $entity->tableInfo();
        if($tableInfo == null)
        {
            return array();
        }
        $columns = $tableInfo->getColumns();
        $propertyColumns = array();
        foreach($columns as $prop=>$column)
        {
            $propertyColumns[$prop] = $column['name'];
        }
        return $propertyColumns;
    }

    /**
     * Table property join column
     * @param MagicObject $entity Input entity
     * @return string[]|array
     */
    public static function getPropertyJoinColumn($entity)
    {
        $tableInfo = $entity->tableInfo();
        if($tableInfo == null)
        {
            return array();
        }
        $joinColumns = $tableInfo->getJoinColumns();
        $propertyColumns = array();
        foreach($joinColumns as $prop=>$column)
        {
            $propertyColumns[$prop] = $column['name'];
        }
        return $propertyColumns;
    }

    /**
     * Get entity data
     * @param array|stdClass|MagicObject $data Data
     * @param string[] $map Map
     * @return array
     */
    public static function getEntityData($data, $map)
    {
        $newData = array();
        if(isset($data))
        {
            if(is_array($data))
            {
                $newData = self::fromArray($data, $map);
            }
            if($data instanceof stdClass)
            {
                $newData = self::fromStdClass($data, $map);
            }
            if($data instanceof MagicObject)
            {
                $newData = self::fromMagicObject($data, $map);
            }
        }
        return $newData;
    }

    /**
     * From array
     * @param array $data Data
     * @param string[] $map Map
     * @return array
     */
    private static function fromArray($data, $map)
    {
        $newData = array();
        foreach($map as $key=>$value)
        {
            if(isset($data[$value]))
            {
                $newData[$key] = $data[$value];
            }
        }
        return $newData;
    }

    /**
     * From stdClass
     * @param stdClass $data Data
     * @param string[] $map Map
     * @return array
     */
    private static function fromStdClass($data, $map)
    {
        $newData = array();
        foreach($map as $key=>$value)
        {
            if(isset($data->{$value}))
            {
                $newData[$key] = $data->{$value};
            }
        }
        return $newData;
    }

    /**
     * From MagicObject
     * @param MagicObject $data Input entity
     * @param string[] $map Map
     * @return array
     */
    private static function fromMagicObject($data, $map)
    {
        $newData = array();
        foreach($map as $key=>$value)
        {
            $newData[$key] = $data->get($value);
        }
        return $newData;
    }
}