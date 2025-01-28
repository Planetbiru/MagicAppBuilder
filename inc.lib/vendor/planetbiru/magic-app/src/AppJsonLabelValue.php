<?php

namespace MagicApp;

/**
 * Class AppJsonLabelValue
 *
 * Represents a collection of options for a select element in JSON format.
 * This class allows adding options, retrieving them, and serializing the
 * collection to JSON. Each option is represented by an instance of the
 * `AppLabelValueData` class, which encapsulates details such as label, value,
 * selected status, and additional data or attributes.
 *
 * @package MagicApp
 */
class AppJsonLabelValue
{
    /**
     * Array of AppLabelValueData objects representing each option.
     * 
     * @var AppLabelValueData[] 
     */
    private $options = array();

    /**
     * Adds a new option to the options array.
     *
     * Creates a new instance of `AppLabelValueData` with the provided details
     * and appends it to the options array. This method supports method chaining.
     *
     * @param string $label The label text to display for the option.
     * @param mixed|null $value The value associated with the option (can be null).
     * @param bool $selected Whether the option should be pre-selected (default: false).
     * @param mixed|null $attributes Additional attributes for the option (optional).
     * @param mixed|null $data Any extra data associated with the option (optional).
     *
     * @return self Returns the current instance for method chaining.
     */
    public function add($label, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->options[] = new AppLabelValueData($label, $value, $selected, $attributes, $data);
        return $this;
    }

    /**
     * Converts the options collection to a JSON-encoded string.
     *
     * Iterates over the options array, calling the `toJson` method on each
     * `AppLabelValueData` object, and compiles the results into a JSON string.
     *
     * @return string A JSON-encoded string representing the options array.
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
     * Retrieves the current array of options.
     *
     * Returns the list of options as an array of `AppLabelValueData` objects.
     *
     * @return AppLabelValueData[] The array of options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options array directly.
     *
     * Replaces the current options array with the provided array of
     * `AppLabelValueData` objects.
     *
     * @param AppLabelValueData[] $options An array of `AppLabelValueData` objects.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}
