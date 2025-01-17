<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PrimaryKeyValue
 *
 * Represents a primary key and its value for an entity.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PrimaryKeyValue
{
    /**
     * Name of the primary key
     *
     * @var string
     */
    protected $name;

    /**
     * Value of the primary key
     *
     * @var mixed
     */
    protected $value;

    /**
     * Constructor to initialize the primary key name and value.
     *
     * @param string $name The primary key name
     * @param mixed $value The primary key value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets the name of the primary key.
     *
     * @return string The primary key name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the primary key.
     *
     * @param string $name The primary key name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the value of the primary key.
     *
     * @return mixed The primary key value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the primary key.
     *
     * @param mixed $value The primary key value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
