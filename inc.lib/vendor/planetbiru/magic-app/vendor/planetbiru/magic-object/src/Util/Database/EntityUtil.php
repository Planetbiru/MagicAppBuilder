<?php

namespace MagicObject\Util\Database;

use MagicObject\MagicObject;
use stdClass;

/**
 * Class EntityUtil
 *
 * A utility class for managing database entities, providing methods to retrieve column names
 * and map entity data to new keys. This class is designed to work with MagicObject instances
 * and can handle various data formats, including arrays and stdClass objects.
 *
 * @author Kamshory
 * @package MagicObject\Util\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class EntityUtil
{
    /**
     * Get the property column names from the entity.
     *
     * @param MagicObject $entity The input entity.
     * @return string[] An array of property column names.
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
     * Get the property join column names from the entity.
     *
     * @param MagicObject $entity The input entity.
     * @return string[] An array of property join column names.
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
     * Get entity data mapped to new keys.
     *
     * @param array|stdClass|MagicObject $data Data to be mapped.
     * @param string[] $map An array mapping of keys.
     * @return array An array of mapped data.
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
     * Map data from an array.
     *
     * @param array $data Data to map.
     * @param string[] $map An array mapping of keys.
     * @return array An array of mapped data.
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
     * Map data from a stdClass.
     *
     * @param stdClass $data Data to map.
     * @param string[] $map An array mapping of keys.
     * @return array An array of mapped data.
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
     * Map data from a MagicObject.
     *
     * @param MagicObject $data The input entity.
     * @param string[] $map An array mapping of keys.
     * @return array An array of mapped data.
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