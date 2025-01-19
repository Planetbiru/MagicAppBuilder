<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoEntityData
 *
 * Represents the data associated with an entity, including its primary key and primary key values.
 *
 * @package MagicApp\AppDto\MicroServices
 */
class PicoEntityData extends PicoObjectToString
{
    /**
     * Primary key
     *
     * @var string[] The list of primary keys
     */
    protected $primaryKey;

    /**
     * Primary key values
     *
     * @var PrimaryKeyValue[] The list of primary key values
     */
    protected $primaryKeyValue;

    /**
     * Get the primary key(s).
     *
     * @return string[] The primary key(s)
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key(s).
     *
     * @param string[] $primaryKey The primary key(s) to set
     * @return self Returns the current instance for method chaining
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * Get the primary key values.
     *
     * @return PrimaryKeyValue[] The primary key values
     */
    public function getPrimaryKeyValue()
    {
        return $this->primaryKeyValue;
    }

    /**
     * Set the primary key values.
     *
     * @param PrimaryKeyValue[] $primaryKeyValue The primary key values to set
     * @return self Returns the current instance for method chaining
     */
    public function setPrimaryKeyValue($primaryKeyValue)
    {
        $this->primaryKeyValue = $primaryKeyValue;

        return $this;
    }

    /**
     * Adds a primary key to the collection.
     *
     * This method adds a primary key to the internal array of primary keys.
     * If the collection is not initialized, it initializes it as an empty array before adding the key.
     *
     * @param string $primaryKey The primary key to add to the collection
     */
    public function addPrimaryKey($primaryKey)
    {
        if (!isset($this->primaryKey)) {
            $this->primaryKey = [];
        }
        $this->primaryKey[] = $primaryKey;
    }

    /**
     * Adds a primary key value to the collection.
     *
     * This method adds a primary key value to the internal array of primary key values.
     * If the collection is not initialized, it initializes it as an empty array before adding the value.
     *
     * @param string $name The primary key name to add to the collection
     * @param mixed $value The primary key value to add to the collection
     */
    public function addPrimaryKeyValue($name, $value)
    {
        if (!isset($this->primaryKeyValue)) {
            $this->primaryKeyValue = [];
        }
        $this->primaryKeyValue[] = new PicoPrimaryKeyValue($name, $value);
    }
}
