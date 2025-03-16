<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoUserFormInputInsert
 *
 * Represents a collection of input fields for a user form during an insert operation. 
 * This class is used to manage multiple `InputFieldInsert` objects, allowing 
 * for the definition and insertion of multiple fields in a form.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserFormInputInsert extends PicoEntityData
{
    
    /**
     * An array of input fields to be inserted into the form.
     * Each field is represented by an InputFieldInsert object.
     *
     * @var InputFieldInsert[]
     */
    protected $fields;
    
    /**
     * Add an allowed action to the input.
     *
     * This method adds an `InputFieldInsert` object to the list of input that can be performed on the form fields. 
     *
     * @param InputFieldInsert $input The `InputFieldInsert` object to be added.
     */
    public function addInput($input)
    {
        if (!isset($this->fields)) {
            $this->fields = [];
        }
        $this->fields[] = $input;
    }

    /**
     * Get primary key
     *
     * @return  string[] Primary key
     */ 
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set primary key
     *
     * @param string[]  $primaryKey  Primary key
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }
}
