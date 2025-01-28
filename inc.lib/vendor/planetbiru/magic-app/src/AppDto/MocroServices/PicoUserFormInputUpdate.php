<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoUserFormInputUpdate
 *
 * Represents a collection of input fields for a user form during an update operation.
 * This class manages multiple `PicoInputFieldUpdate` objects, each representing a field 
 * to be updated in the form. It also includes an array of allowed actions (e.g., 
 * `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`) that define 
 * the possible operations that can be performed on the form fields.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserFormInputUpdate extends PicoEntityData
{
    
    /**
     * An array of input fields to be updated into the form.
     * Each field is represented by an PicoInputFieldUpdate object.
     *
     * @var PicoInputFieldUpdate[]
     */
    protected $fields;
    
    /**
     * Add an allowed action to the input.
     *
     * This method adds an `PicoInputFieldUpdate` object to the list of input that can be performed on the form fields. 
     *
     * @param PicoInputFieldUpdate $input The `PicoInputFieldUpdate` object to be added.
     */
    public function addInput($input)
    {
        if (!isset($this->fields)) {
            $this->fields = [];
        }
        $this->fields[] = $input;
    }
}
