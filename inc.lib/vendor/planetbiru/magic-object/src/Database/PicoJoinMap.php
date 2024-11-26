<?php

namespace MagicObject\Database;

/**
 * Class representing a join mapping in a database.
 *
 * Contains information about how an entity is joined with another table.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoJoinMap
{
    /**
     * The property name in the entity.
     *
     * @var string
     */
    private $propertyName;

    /**
     * The column name in the join table.
     *
     * @var string
     */
    private $columnName;

    /**
     * The name of the entity being joined.
     *
     * @var string
     */
    private $entity;

    /**
     * The name of the join table.
     *
     * @var string
     */
    private $joinTable;

    /**
     * The alias for the join table.
     *
     * @var string
     */
    private $joinTableAlias;

    /**
     * Constructor for PicoJoinMap.
     *
     * @param string $propertyName The property name.
     * @param string $columnName The column name.
     * @param string $entity The entity name.
     * @param string $joinTable The join table name.
     * @param string $joinTableAlias The join table alias.
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
     * Get the property name.
     *
     * @return string The property name.
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Get the column name.
     *
     * @return string The column name.
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Get the entity name.
     *
     * @return string The entity name.
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get the join table name.
     *
     * @return string The join table name.
     */
    public function getJoinTable()
    {
        return $this->joinTable;
    }

    /**
     * Get the join table alias.
     *
     * @return string The join table alias.
     */
    public function getJoinTableAlias()
    {
        return $this->joinTableAlias;
    }

    /**
     * Convert the object to a JSON string representation for debugging.
     *
     * This method is intended for debugging purposes only and provides 
     * a JSON representation of the object's state.
     *
     * @return string The JSON representation of the object.
     */
    public function __toString()
    {
        return json_encode(array(
            'propertyName'   => $this->propertyName,
            'columnName'     => $this->columnName,
            'entity'         => $this->entity,
            'joinTable'      => $this->joinTable,
            'joinTableAlias' => $this->joinTableAlias,
        ));
    }
}
