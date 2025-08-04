<?php

namespace MagicApp\AppDto\MocroServices;

use MagicApp\AppLabelValueData;

/**
 * Class PicoInputFieldInsert
 *
 * Represents the configuration of an input field to be inserted into a form.
 * This class allows definition of field name, label, input type, data type,
 * and optionally the option source and mapped options.
 *
 * It supports:
 * - Pattern matching for input validation
 * - Option sources (static/dynamic)
 * - Mapping for select/dropdown options
 * - Single or multiple value selection
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoInputFieldInsert extends PicoObjectToString
{
    /**
     * The name or identifier of the input field.
     *
     * @var string
     */
    protected $field;

    /**
     * The label of the input field, typically shown in the UI.
     *
     * @var string
     */
    protected $label;

    /**
     * The input type (e.g., text, number, email).
     *
     * @var string
     */
    protected $inputType;

    /**
     * The data type of the field value (e.g., string, int, bool).
     *
     * @var string
     */
    protected $dataType;

    /**
     * The source for the options, if any (e.g., fixed string or URL).
     *
     * @var string
     */
    protected $optionSource;

    /**
     * A URL that provides option data for select/dropdown fields.
     *
     * @var string
     */
    protected $optionUrl;

    /**
     * Array of options (objects) for the input field.
     *
     * @var PicoInputFieldOption[]|AppLabelValueData[]
     */
    protected $optionMap;

    /**
     * Whether this field accepts multiple values.
     *
     * @var bool
     */
    protected $multipleValue;

    /**
     * Constructor for PicoInputFieldInsert.
     *
     * @param PicoInputField                  $inputField     Source input field object.
     * @param string                          $inputType      The type of the input field.
     * @param string                          $dataType       The data type of the input field.
     * @param string|null                     $optionSource   Optional source for dynamic options.
     * @param PicoInputFieldOption[]|AppLabelValueData[]|string|null $map Options or URL.
     * @param bool                            $multipleValue  Whether multiple values are allowed.
     */
    public function __construct(
        $inputField,
        $inputType,
        $dataType,
        $optionSource = null,
        $map = null,
        $multipleValue = false
    ) {
        if (isset($inputField) && $inputField instanceof PicoInputField) {
            $this->field = $inputField->getValue();
            $this->label = $inputField->getLabel();
        }

        $this->inputType = $inputType;
        $this->dataType = $dataType;

        if (isset($optionSource)) {
            $this->optionSource = $optionSource;

            if (isset($map)) {
                if (is_array($map) && isset($map[0]) &&
                    ($map[0] instanceof PicoInputFieldOption || $map[0] instanceof AppLabelValueData)) {
                    $this->optionMap = $map;
                } else {
                    $this->optionUrl = $map;
                }
            }
        }

        $this->multipleValue = $multipleValue;
    }

    /**
     * Returns whether multiple values are allowed.
     *
     * @return bool
     */
    public function isMultipleValue()
    {
        return $this->multipleValue;
    }

    /**
     * Sets whether multiple values are allowed.
     *
     * @param bool $multipleValue
     * @return self Returns the current instance for method chaining.
     */
    public function setMultipleValue($multipleValue)
    {
        $this->multipleValue = $multipleValue;

        return $this;
    }
}
