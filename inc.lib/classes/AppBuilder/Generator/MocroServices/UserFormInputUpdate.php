<?php

namespace AppBuilder\Generator\MocroServices;

/**
 * Class UserFormInputUpdate
 *
 * Represents a collection of input fields for a user form during an update operation.
 * This class manages multiple `InputFieldUpdate` objects, each representing a field 
 * to be updated in the form. It also includes an array of allowed actions (e.g., 
 * `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`) that define 
 * the possible operations that can be performed on the form fields.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class UserFormInputUpdate
{
    /**
     * An array of input fields to be updated in the form.
     * Each field is represented by an InputFieldUpdate object.
     *
     * @var InputFieldUpdate[]
     */
    protected $input;
    
    /**
     * A list of allowed actions that can be performed on the form fields.
     * Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @var string[]
     */
    protected $allowedAction;
}
