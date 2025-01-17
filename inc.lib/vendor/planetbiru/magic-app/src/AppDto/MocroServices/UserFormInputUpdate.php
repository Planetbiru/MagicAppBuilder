<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class UserFormInputUpdate
 *
 * Represents a collection of input fields for a user form during an update operation.
 * This class manages multiple `InputFieldUpdate` objects, each representing a field 
 * to be updated in the form. It also includes an array of allowed actions (e.g., 
 * `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`) that define 
 * the possible operations that can be performed on the form fields.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class UserFormInputUpdate extends ObjectToString
{
    /**
     * Primary key
     *
     * @var string[]
     */
    protected $primaryKey;

    /**
     * Primary key value
     *
     * @var PrimaryKeyValue[]
     */
    protected $primaryKeyValue;
    
    /**
     * An array of input fields to be updated into the form.
     * Each field is represented by an InputFieldUpdate object.
     *
     * @var InputFieldUpdate[]
     */
    protected $input;
    
    /**
     * Add an allowed action to the input.
     *
     * This method adds an `InputFieldUpdate` object to the list of input that can be performed on the form fields. 
     *
     * @param InputFieldUpdate $input The `InputFieldUpdate` object to be added.
     */
    public function addInput($input)
    {
        if (!isset($this->input)) {
            $this->input = [];
        }
        $this->input[] = $input;
    }
}
