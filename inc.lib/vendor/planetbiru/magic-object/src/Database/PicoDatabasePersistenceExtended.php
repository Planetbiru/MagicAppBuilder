<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidValueException;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\MagicObject;
use MagicObject\Util\ValidationUtil;

/**
 * Database persistence extended
 *
 * This class extends the functionality of the PicoDatabasePersistence
 * by adding dynamic property setting through magic methods and enhanced
 * record selection capabilities.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabasePersistenceExtended extends PicoDatabasePersistence
{
    /**
     * @var array An array of property-value pairs where each entry contains the name of a property and its corresponding value.
     */
    private $map = array();

    /**
     * Begin a fluent setter chain.
     *
     * This method is primarily used to start a method chaining sequence for
     * setting properties on the object, enhancing readability and allowing
     * multiple setters to be called sequentially. It simply returns the
     * current object instance.
     *
     * @return self Returns the current instance of the object, allowing for method chaining.
     */
    public function with()
    {
        return $this;
    }

    /**
     * Sets a property value and adds it to the internal map.
     *
     * This method sets a value to a property of the associated object and adds the property name and value 
     * to an internal map for further processing.
     *
     * @param string $propertyName The name of the property to set.
     * @param mixed $propertyValue The value to assign to the property.
     * @return self Returns the current instance for method chaining.
     */
    public function set($propertyName, $propertyValue)
    {
        $this->object->set($propertyName, $propertyValue);
        $this->addToMap($propertyName, $propertyValue);
        return $this;
    }

    /**
     * Adds the property name and value to the internal map.
     *
     * This method adds the given property name and value as an entry in the internal `$map` array.
     *
     * @param string $propertyName The name of the property.
     * @param mixed $propertyValue The value of the property.
     * @return self Returns the current instance for method chaining.
     */
    private function addToMap($propertyName, $propertyValue)
    {
        $this->map[] = array(
            'property' => $propertyName, 
            'value' => $propertyValue
        );
        return $this;
    }

    /**
     * Magic method to handle dynamic method calls for property setting.
     *
     * This method dynamically handles method calls that start with "set".
     *
     * Supported dynamic method pattern:
     * - `set<PropertyName>($value)`: Sets the value of the specified property and adds it to the internal map.
     *   - Example: `$obj->setFoo($value)` sets the property `foo` to `$value`.
     *   - If no value is provided, it sets the property to null.
     *
     * @param string $method The name of the method that was called.
     * @param array $params The parameters passed to the method.
     * @return self Returns the current instance for method chaining.
     */
    public function __call($method, $params)
    {
        if (strlen($method) > 3 && strncasecmp($method, "set", 3) === 0 && isset($params) && is_array($params)){
            $var = lcfirst(substr($method, 3));
            if(empty($params))
            {
                $params[0] = null;
            }
            $this->object->set($var, $params[0]);
            $this->addToMap($var, $params[0]);
        }
        return $this;
    }

    /**
     * Validate the current object based on property annotations.
     *
     * This method checks the properties of the current object against validation annotations.
     * If any validation rule fails, an InvalidValueException will be thrown.
     *
     * @param string|null      $parentPropertyName        The name of the parent property, if applicable (for nested validation).
     * @param array|null       $messageTemplate           Optional custom message templates for validation errors.
     * @param MagicObject|null $reference                 Optional reference object. If provided and is an instance of MagicObject,
     * validation will use the property annotations from the reference class
     * (not from the validated object's class), but the data to validate is taken from the current object.
     * @param bool             $validateIfReferenceEmpty  If true, and a reference object is provided but empty,
     * validation will proceed using the current object's properties.
     * If false, validation is skipped if the reference object is empty.
     * Defaults to true.
     * @throws InvalidValueException If validation fails.
     * @return self Returns the current instance for method chaining.
     */
    public function validate(
        $parentPropertyName = null,
        $messageTemplate = null,
        $reference = null,
        $validateIfReferenceEmpty = true // Changed default to true and type hint to bool
    ) {
        $objectToValidate = $this->object; // Default: validate this object
        $shouldValidate = true; // Flag to determine if validation should proceed

        // Check if a reference object is provided and is an instance of MagicObject
        if (isset($reference) && $reference instanceof MagicObject) {
            // A reference object exists. Now determine if it has properties.
            if ($reference->hasProperties()) {
                // The reference has properties, so use annotations from the reference.
                // The data being validated remains from $this->object.
                $objectToValidate = $reference->loadData($this->object);
            } else {
                // The reference has no properties (it's empty).
                // Determine if validation should still proceed based on $validateIfReferenceEmpty.
                if (!$validateIfReferenceEmpty) {
                    $shouldValidate = false; // Skip validation
                }
                // If $validateIfReferenceEmpty is true, $objectToValidate remains $this->object (default)
                // and $shouldValidate remains true.
            }
        }
        // If no reference ($reference == null), $shouldValidate remains true,
        // and $objectToValidate remains $this->object, which is the desired behavior.


        if ($shouldValidate) {
            // Call the main validation utility only once
            ValidationUtil::getInstance($messageTemplate)->validate($objectToValidate, $parentPropertyName);
        }
        
        return $this;
    }
    
    /**
     * Get the current database for the specified entity.
     *
     * This method retrieves the database connection associated with the 
     * provided entity. If the entity does not have an associated database 
     * or if the connection is not valid, it defaults to the object's 
     * primary database connection.
     *
     * @param MagicObject $entity The entity for which to get the database.
     * @return PicoDatabase The database connection for the entity.
     */
    private function currentDatabase($entity)
    {
        $dbEnt = $this->object->databaseEntity($entity);
        $db = null;
        if(isset($dbEnt))
        {
            $db = $dbEnt->getDatabase(get_class($entity));
        }
        if(!isset($db) || !$db->isConnected())
        {
            $db = $this->object->_database;
        }
        return $db;
    }

    /**
     * Select one record.
     *
     * This method retrieves a single record from the database.
     * If no record is found, a NoRecordFoundException is thrown.
     *
     * @return MagicObject The selected record as an instance of MagicObject.
     * @throws NoRecordFoundException If no record is found.
     */
    public function select()
    {
        $data = parent::select();
        if($data == null)
        {
            throw new NoRecordFoundException(parent::MESSAGE_NO_RECORD_FOUND);
        }
        $entity = new $this->className($data);
        $entity->currentDatabase($this->currentDatabase($entity));
        $entity->databaseEntity($this->object->databaseEntity());
        return $entity;
    }

    /**
     * Select all records.
     *
     * This method retrieves all records from the database.
     * If no records are found, a NoRecordFoundException is thrown.
     *
     * @return MagicObject[] An array of MagicObject instances representing all records.
     * @throws NoRecordFoundException If no records are found.
     */
    public function selectAll()
    {
        $collection = array();
        $result = parent::selectAll();

        if($result == null || empty($result))
        {
            throw new NoRecordFoundException(parent::MESSAGE_NO_RECORD_FOUND);
        }
        foreach($result as $data)
        {
            $entity = new $this->className($data);
            $entity->databaseEntity($this->object->databaseEntity());
            $collection[] = $entity;
        }
        return $collection;
    }

    /**
     * Convert the object to a JSON string representation for debugging.
     *
     * This method is intended for debugging purposes only. It converts the object
     * to a JSON string that represents the current state of the object, including
     * the 'where' specification and the 'set' mapping, making it easier to inspect
     * the internal data during development.
     *
     * @return string The JSON string representation of the object, containing:
     *                - 'where': The string representation of the object's specification (as generated by __toString).
     *                - 'set': The current mapping of the object.
     */
    public function __toString()
    {
        return json_encode(array(
            'where' => (string) $this->specification, 
            'set' => $this->map
        ), JSON_PRETTY_PRINT);
    }
}