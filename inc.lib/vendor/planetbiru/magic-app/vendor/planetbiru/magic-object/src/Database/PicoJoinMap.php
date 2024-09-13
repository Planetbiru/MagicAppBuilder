<?php

namespace MagicObject\Database;

/**
 * Join map
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoJoinMap
{
    /**
     * Property name name
     *
     * @var string
     */
    private $propertyName;

    /**
     * Column name
     *
     * @var string
     */
    private $columnName;

    /**
     * Entity name
     *
     * @var string
     */
    private $entity;

    /**
     * Table name
     *
     * @var string
     */
    private $joinTable;

    /**
     * Table alias
     *
     * @var string
     */
    private $joinTableAlias;

    /**
     * Constructor
     *
     * @param string $propertyName Property name
     * @param string $columnName Column name
     * @param string $entity Entity name
     * @param string $joinTable Join table name
     * @param string $joinTableAlias Join table alias
     */
    public function __construct($propertyName, $columnName, $entity, $joinTable, $joinTableAlias)
    {
        $this->propertyName = $propertyName;
        $this->columnName = $columnName;
        $this->entity = $entity;
        $this->joinTable = $joinTable;
        $this->joinTableAlias = $joinTableAlias;
    }

    /**
     * Get property name name
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Get column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getJoinTable()
    {
        return $this->joinTable;
    }

    /**
     * Get table alias
     *
     * @return string
     */
    public function getJoinTableAlias()
    {
        return $this->joinTableAlias;
    }

    /**
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            array(
                'propertyName'   => $this->propertyName,
                'columnName'     => $this->columnName,
                'entity'         => $this->entity,
                'joinTable'      => $this->joinTable,
                'joinTableAlias' => $this->joinTableAlias
                )
            );
    }
}