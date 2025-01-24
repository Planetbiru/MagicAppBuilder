<?php

namespace MagicApp;

/**
 * Class AppJsonLabelValue
 *
 * This class is used to represent a collection of options for a select element in JSON format.
 * It allows the addition of options (each with a label, value, selected status, and additional data)
 * and provides methods to retrieve and serialize the options to JSON format.
 *
 * The options are represented as instances of the AppLabelValueData class, which encapsulate
 * the data for each option.
 *
 * @package MagicApp
 */
class AppJsonLabelValue
{
    /**
     * Array of options for the select element.
     *
     * @var AppLabelValueData[] Array of AppLabelValueData objects representing each option.
     */
    private $options = array();

    /**
     * Add a new option to the list.
     *
     * This method creates a new AppLabelValueData object with the provided parameters
     * and adds it to the options array. The new option represents a single item in the select dropdown.
     *
     * @param string $label The label text to be displayed for the option.
     * @param mixed|null $value The value associated with the option.
     * @param bool $selected Whether the option should be marked as selected.
     * @param mixed|null $attributes Additional attributes for the option.
     * @param mixed|null $data Additional data associated with the option.
     *
     * @return self The current instance of the class to allow method chaining.
     */
    public function add($label, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->options[] = new AppLabelValueData($label, $value, $selected, $attributes, $data);
        return $this;
    }

    /**
     * Convert the options to JSON format.
     *
     * This method iterates over the options array and converts each AppLabelValueData object
     * into its JSON representation using the toJson method. The resulting array is then
     * encoded into a JSON string and returned.
     *
     * @return string A JSON-encoded string representing all options.
     */
    public function __toString()
    {
        $options = array();
        foreach ($this->options as $option) {
            $options[] = $option->toJson();
        }
        return json_encode($options);
    }

    /**
     * Get the array of options for the select element.
     *
     * This method returns the current array of options, where each option is represented
     * by an instance of the AppLabelValueData class.
     *
     * @return AppLabelValueData[] Array of AppLabelValueData objects.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the array of options for the select element.
     *
     * This method allows you to set the options array directly. It replaces any existing options
     * with the provided array.
     *
     * @param AppLabelValueData[] $options Array of AppLabelValueData objects to be set as options.
     *
     * @return self The current instance of the class to allow method chaining.
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}