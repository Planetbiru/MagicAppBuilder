<?php

namespace MagicApp;

use MagicObject\MagicObject;

/**
 * Class AppFormSelect
 *
 * Represents a select form element that can contain multiple options.
 * Provides methods to add options, set formatting, and generate the
 * HTML representation of the select element.
 *
 * This class helps in managing and rendering a `<select>` HTML element with various options. It allows you to
 * dynamically add options, format the text of the options, and generate the HTML representation of the select element.
 */
class AppFormSelect
{
    /**
     * Array of options for the select element.
     *
     * @var AppFormOption[]
     */
    private $options = array();

    /**
     * Add an option to the select element.
     *
     * This method adds a new option to the select element, which consists of display text, a value, a selected status,
     * optional HTML attributes, and associated data.
     *
     * @param string $label The display text for the option
     * @param string|null $value The value of the option
     * @param boolean $selected Indicates if the option is selected
     * @param string[]|null $attributes Additional HTML attributes for the option
     * @param MagicObject|null $data Associated data for the option
     * @return self The current instance, allowing method chaining
     */
    public function add($label, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->options[] = new AppFormOption($label, $value, $selected, $attributes, $data);
        return $this;
    }

    /**
     * Set the text node format for all options.
     *
     * This method allows you to set a format for the display text (text node) of each option in the select element. 
     * You can provide a format string or a callable function that will be applied to the associated data for each option.
     *
     * @param callable|string $format A callable function or a format string
     * @return self The current instance, allowing method chaining
     */
    public function setTextNodeFormat($format)
    {
        if (isset($format)) {
            if (is_callable($format)) {
                foreach ($this->options as $option) {
                    $option->setTextNode(call_user_func($format, $option->getData()));
                }
            } else {
                $this->setTextNodeFormatFromString($format);
            }
        }
        return $this;
    }

    /**
     * Set the text node format using a format string.
     *
     * This method allows you to set the format for the text of each option by using a format string.
     * The format string can contain placeholders like `%s` and `%d` to be replaced with values from the option's data.
     *
     * @param string $format The format string
     * @return self The current instance, allowing method chaining
     */
    public function setTextNodeFormatFromString($format)
    {
        $separator = ",";
        $params = array();
        $args = preg_split('/' . $separator . '(?=(?:[^\"])*(?![^\"]))/', $format, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($args as $i => $arg) {
            $arg = trim($arg);
            if ($i > 0 && !empty($arg)) {
                $params[] = $arg;
            }
        }

        preg_match_all('`"([^"]*)"`', $args[0], $results);
        $format2 = isset($results[1]) && isset($results[1][0]) && !empty($results[1][0]) ? $results[1][0] : $args[0];
        $numPlaceholders = preg_match_all('/%[sd]/', $format2, $matches);

        while ($numPlaceholders > count($params)) {
            $params[] = '';
        }
        if ($numPlaceholders < count($params)) {
            array_pop($params);
        }

        if (!empty($params)) {
            foreach ($this->options as $option) {
                $option->textNodeFormat($format2, $params);
            }
        }
        return $this;
    }

    /**
     * Set indentation for the options.
     *
     * This method sets the level of indentation for each option's HTML representation.
     * The `indent` parameter determines how many tab characters will be used for indentation.
     *
     * @param integer $indent The level of indentation (default is 1)
     * @return self The current instance, allowing method chaining
     */
    public function setIndent($indent = 1)
    {
        $pad = str_pad('', $indent, "\t", STR_PAD_LEFT);
        foreach ($this->options as $option) {
            $option->setPad($pad);
        }
        return $this;
    }

    /**
     * Get the HTML representation of the select element as a string.
     *
     * This method generates the complete HTML representation of the `<select>` element, including all of its options.
     * The options are rendered using their respective `toString()` method.
     *
     * @return string The HTML representation of the select element, including options
     */
    public function __toString()
    {
        $opt = array();
        foreach ($this->options as $option) {
            $opt[] = $option->toString();
        }
        return implode("\r\n", $opt);
    }
}
