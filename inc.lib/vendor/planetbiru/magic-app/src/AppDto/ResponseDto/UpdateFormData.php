<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class UpdateFormData
 *
 * Represents the data structure for updating form filters or input fields in the UI.
 * This class defines the configuration for a form element intended to update
 * data in the backend. It includes properties related to form inputs, such as the columns
 * or input fields that are displayed in the form for the user to interact with.
 *
 * In this class, the `column` property holds an array of `InputFormData` objects, which represent
 * the individual fields in the update form.
 *
 * Properties:
 * - `column`: An array of `InputFormData` objects, each representing a column or input field in the form.
 * - `button`: An array of `ButtonFormData` objects, representing buttons in the form (optional).
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class UpdateFormData extends ToString
{
    /**
     * The columns or input fields associated with the update form.
     * This property holds an array of `InputFormData` objects, each representing a column
     * or input field used in updating data.
     *
     * @var InputFormData[] Array of `InputFormData` objects.
     */
    protected $column;

    /**
     * The buttons associated with the update form.
     * This property holds an array of `ButtonFormData` objects, each representing a button within the form.
     * Buttons are typically used for submitting the form or triggering specific actions.
     *
     * @var ButtonFormData[] Array of `ButtonFormData` objects.
     */
    protected $button;

    /**
     * UpdateFormData constructor.
     *
     * Initializes the `column` property as an empty array.
     * Initializes the `button` property as an empty array.
     */
    public function __construct()
    {
        $this->column = [];
        $this->button = []; 
    }

    /**
     * Gets the array of `InputFormData` objects representing the columns or input fields.
     *
     * This method retrieves the form columns (input fields) that the user will interact with.
     *
     * @return InputFormData[] The array of `InputFormData` objects.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Sets the array of `InputFormData` objects representing the columns or input fields.
     *
     * This method sets the `column` property to a new array of `InputFormData` objects.
     * It allows the entire array of columns to be updated at once.
     * 
     * @param InputFormData[] $column The array of `InputFormData` objects to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setColumn($column)
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Adds a single `InputFormData` object to the `column` array.
     *
     * This method appends a single `InputFormData` object to the `column` property, which is an array.
     *
     * @param InputFormData $inputFormData The `InputFormData` object to add to the column array.
     * @return self Returns the current instance for method chaining.
     */
    public function addColumn($inputFormData)
    {
        $this->column[] = $inputFormData;
        return $this;
    }

    /**
     * Gets the array of `ButtonFormData` objects associated with the form.
     *
     * This method retrieves the array of `ButtonFormData` objects that represent the buttons
     * in the form, such as "Submit" or "Reset" buttons.
     *
     * @return ButtonFormData[] The array of `ButtonFormData` objects.
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * Sets the array of `ButtonFormData` objects representing the buttons in the form.
     *
     * This method sets the `button` property to an array of `ButtonFormData` objects. It allows
     * you to update the buttons in the form.
     *
     * @param ButtonFormData[] $button The array of `ButtonFormData` objects to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setButton($button)
    {
        $this->button = $button;
        return $this;
    }

    /**
     * Adds a single `ButtonFormData` object to the `button` array.
     *
     * This method appends a single `ButtonFormData` object to the `button` property.
     *
     * @param ButtonFormData $buttonFormData The `ButtonFormData` object to add to the button array.
     * @return self Returns the current instance for method chaining.
     */
    public function addButton($buttonFormData)
    {
        $this->button[] = $buttonFormData;
        return $this;
    }
}
