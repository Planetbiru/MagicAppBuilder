<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class InputFieldInsert
 *
 * Represents an input field configuration for insertion into a form. This class allows 
 * the definition of field properties such as the name, label, input type, and more.
 * It also supports pattern matching, data types, option sources, and mapping input options.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class InputFieldInsert extends ObjectToString
{
    /**
     * The name or identifier for the input field.
     *
     * @var string
     */
    protected $field;
    
    /**
     * The label to be displayed alongside the input field.
     *
     * @var string
     */
    protected $label;
    
    /**
     * The type of the input field (e.g., text, number, email, etc.).
     *
     * @var string
     */
    protected $inputType;
    
    /**
     * The data type of the input field (e.g., string, integer, boolean, etc.).
     *
     * @var string
     */
    protected $dataType;
    
    /**
     * The source for options when the input type requires them (e.g., a list of options for a select dropdown).
     *
     * @var string
     */
    protected $optionSource;
    
    /**
     * An array of InputFieldOption objects representing the available options for the input field.
     *
     * @var InputFieldOption[]
     */
    protected $map;

    /**
     * A regular expression pattern to validate the input (optional).
     *
     * @var string
     */
    protected $pattern;
    
    /**
     * Constructor for InputFieldInsert.
     * Initializes the input field properties with provided values.
     *
     * @param InputField $inputField Input field
     * @param string $inputType The type of the input field.
     * @param string $dataType The data type of the input field.
     * @param string|null $optionSource Optional source for options (e.g., for a select dropdown).
     * @param InputFieldOption[]|null $map Optional array of available options for the input field.
     * @param string|null $pattern Optional regular expression pattern for validation.
     */
    public function __construct(
        $inputField,
        $inputType,
        $dataType,
        $optionSource = null,
        $map = null,
        $pattern = null
    ) {
        if(isset($inputField))
        {
            $this->field = $inputField->getValue();
            $this->label = $inputField->getLabel();
        }
        
        $this->inputType = $inputType;
        $this->dataType = $dataType;
        if(isset($optionSource))
        {
            $this->optionSource = $optionSource;
        }
        if(isset($map))
        {
            $this->map = $map;
        }
        if(isset($pattern))
        {
            $this->pattern = $pattern;
        }
    }
}
