<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class CreateFormData
 *
 * Represents a data structure for creating form filters or input elements in the UI.
 * This class handles the configuration for a filter or form input, such as the filter's label, field name,
 * input type, default value, and other attributes.
 *
 * The class contains a `column` property, which is an array of `InputFormData` objects. Each `InputFormData`
 * represents a column or field in the form, such as a filter or search field.
 *
 * Properties:
 * - `column`: An array of `InputFormData` objects, each representing a column or input field in the form.
 * - `button`: An array of `ButtonFormData` objects, each representing a button in the form.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class CreateFormData extends ToString
{
    /**
     * The columns or input fields associated with the form.
     * This property holds an array of `InputFormData` objects, each representing a column or filter element.
     *
     * @var InputFormData[] Array of `InputFormData` objects.
     */
    protected $column;

    /**
     * The buttons associated with the form.
     * This property holds an array of `ButtonFormData` objects, each representing a button within the form.
     *
     * @var ButtonFormData[] Array of `ButtonFormData` objects.
     */
    protected $button;

    /**
     * CreateFormData constructor.
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
     * @return InputFormData[] The array of `InputFormData` objects representing the columns.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Sets the array of `InputFormData` objects representing the columns or input fields.
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
     * This method allows you to append one `InputFormData` object at a time to the `column` property.
     *
     * @param InputFormData $inputFormData The `InputFormData` object to add to the column.
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
     * @return ButtonFormData[] The array of `ButtonFormData` objects.
     */
    public function getButton()
    {
        return $this->button;
    }

    /**
     * Sets the array of `ButtonFormData` objects representing the buttons in the form.
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
     * @param ButtonFormData $buttonFormData The `ButtonFormData` object to add to the button array.
     * @return self Returns the current instance for method chaining.
     */
    public function addButton($buttonFormData)
    {
        $this->button[] = $buttonFormData;
        return $this;
    }
}
