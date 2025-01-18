<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoInputFieldInsert
 *
 * Represents the configuration of an input field to be inserted into a form. This class allows 
 * the definition of various properties for the input field, such as its name, label, type, data type, 
 * and additional attributes like validation patterns, option sources, and input options mapping.
 *
 * It supports pattern matching for input validation, specifies the data types (e.g., string, integer, boolean),
 * handles option sources for select/dropdown fields, and allows the mapping of input options.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoInputFieldInsert extends PicoObjectToString
{
    /**
     * The name or identifier for the input field.
     *
     * @var string
     */
    protected $field;
    
    /**
     * The label to be displayed alongside the input field, typically describing the purpose of the field.
     *
     * @var string
     */
    protected $label;
    
    /**
     * The type of the input field (e.g., text, number, email, etc.), determining how the input is handled.
     *
     * @var string
     */
    protected $inputType;
    
    /**
     * The data type of the input field (e.g., string, integer, boolean, etc.), defining the expected type of the input.
     *
     * @var string
     */
    protected $dataType;
    
    /**
     * The source for options when the input type requires them, such as a list of options for a select dropdown.
     * This could be a URL that provides the options dynamically.
     *
     * @var string
     */
    protected $optionSource;
    
    /**
     * URL that provides options for the input field (e.g., data source for dynamic select options).
     *
     * @var string
     */
    protected $optionUrl;
    
    /**
     * An array of InputFieldOption objects representing the available options for the input field. 
     * Used primarily for fields like select/dropdown, where users can choose from a list of predefined options.
     *
     * @var InputFieldOption[]
     */
    protected $optionMap;

    /**
     * A regular expression pattern used to validate the input (optional). 
     * It can be used for enforcing format restrictions, such as for email or phone number fields.
     *
     * @var string
     */
    protected $pattern;
    
    /**
     * Constructor for PicoInputFieldInsert.
     * Initializes the properties of the input field with the provided values.
     *
     * @param InputField $inputField The input field object containing field values and label.
     * @param string $inputType The type of the input field (e.g., text, number, email).
     * @param string $dataType The data type of the input field (e.g., string, integer, boolean).
     * @param string|null $optionSource Optional source for dynamically populated options (e.g., URL).
     * @param InputFieldOption[]|string|null $map Optional array of available options for the input field.
     * @param string|null $pattern Optional regular expression pattern for validating the input.
     */
    public function __construct(
        $inputField,
        $inputType,
        $dataType,
        $optionSource = null,
        $map = null,
        $pattern = null
    ) {
        if (isset($inputField)) {
            $this->field = $inputField->getValue();
            $this->label = $inputField->getLabel();
        }
        
        $this->inputType = $inputType;
        $this->dataType = $dataType;
        
        if (isset($optionSource)) {
            $this->optionSource = $optionSource;
        }
        
        if (isset($map)) {
            if(is_array($map) && isset($map[0]) && $map[0] instanceof PicoInputFieldOption)
            {
                $this->optionMap = $map;
            }
            else
            {
                $this->optionUrl = $map;
            }
        }
        
        if (isset($pattern)) {
            $this->pattern = $pattern;
        }
    }
}
