<?php

namespace MagicApp;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class AppFormOption
 *
 * Represents an option within a form element, encapsulating the necessary attributes
 * and behaviors associated with that option, including its display text, value,
 * selection state, and any additional data attributes.
 */
class AppFormOption
{
    /**
     * Text node for the option.
     *
     * @var string
     */
    public $label;

    /**
     * Value associated with the option.
     *
     * @var string
     */
    public $value;

    /**
     * Indicates whether the option is selected.
     *
     * @var boolean
     */
    public $selected;

    /**
     * Additional attributes for the option.
     *
     * @var string[]
     */
    public $attributes;

    /**
     * Format for the text node, allowing dynamic content.
     *
     * @var string
     */
    protected $format;

    /**
     * Parameters for dynamic formatting of the text node.
     *
     * @var string[]
     */
    protected $params;

    /**
     * Data associated with the option, typically from a MagicObject.
     *
     * @var MagicObject
     */
    protected $data;

    /**
     * Padding to format the output, e.g., for nested options.
     *
     * @var string
     */
    protected $pad = "";

    /**
     * Constructor to initialize the option with text, value, selected state, attributes, and data.
     *
     * @param string $label The display text for the option
     * @param string|null $value The value of the option
     * @param boolean $selected Indicates if the option is selected
     * @param string[]|null $attributes Additional HTML attributes for the option
     * @param MagicObject|null $data Associated data for dynamic value retrieval
     */
    public function __construct($label, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->label = $label;
        $this->value = $value;
        $this->selected = $selected;
        $this->attributes = $attributes;
        $this->data = $data;
    }

    /**
     * Create HTML data attributes for the option.
     *
     * This method generates a string of HTML data attributes from the attributes array.
     * It formats each key into a data- attribute (e.g., `data-attribute-name="value"`).
     *
     * @return string Formatted string of data attributes for HTML
     */
    public function createAttributes()
    {
        $attrs = array();
        if (isset($this->attributes) && is_array($this->attributes)) {
            foreach ($this->attributes as $attr => $val) {
                $attrs[] = 'data-' . str_replace('_', '-', PicoStringUtil::snakeize($attr)) . '="' . $this->encode($val) . '"';
            }
            return ' ' . implode(' ', $attrs);
        }
        return '';
    }

    /**
     * Set a format for the text node with parameters for dynamic content.
     *
     * This method allows you to set a dynamic format for the text of the option.
     * The format string can contain placeholders that will be replaced with the provided parameters.
     *
     * @param string $format The format string
     * @param string[] $params The parameters for the format
     * @return self The current instance, allowing method chaining
     */
    public function textNodeFormat($format, $params)
    {
        $this->format = $format;
        $this->params = $params;
        return $this;
    }

    /**
     * Retrieve the values of the parameters used in the format.
     *
     * This method returns an array of parameter values by evaluating each one 
     * through the `getValue()` method, which may fetch data from the associated `MagicObject`.
     *
     * @return string[] Array of parameter values
     */
    public function getValues()
    {
        $values = array();
        if (isset($this->params) && is_array($this->params)) {
            foreach ($this->params as $param) {
                $values[] = $this->getValue($param);
            }
        }
        return $values;
    }

    /**
     * Get the value associated with a given parameter.
     *
     * This method retrieves the value of a parameter, which could be a property 
     * of the associated `MagicObject` (if it exists) or simply the parameter name.
     *
     * @param string $param The parameter name
     * @return string|null The value associated with the parameter, or null if not found
     */
    public function getValue($param)
    {
        if ($this->data == null) {
            return null;
        }
        $param = trim($param);
        $value = null;
        if (stripos($param, '.') !== false) {
            $param = str_replace(' ', '', $param);
            $arr = explode(".", $param, 2);
            if ($this->data->get($arr[0]) != null && $this->data->get($arr[0]) instanceof MagicObject) {
                $value = $this->data->get($arr[0])->get($arr[1]);
            }
        } else {
            $value = $this->data->get($param);
        }
        return $value;
    }

    /**
     * Set padding for the option, typically for nested structures.
     *
     * This method allows you to specify padding to be applied to the option, 
     * typically useful for nested option elements.
     *
     * @param string $pad The padding string (default is a tab character)
     * @return self The current instance, allowing method chaining
     */
    public function setPad($pad = "\t")
    {
        $this->pad = $pad;
        return $this;
    }
    
    /**
     * Ensures any pre-encoded HTML entities are re-encoded.
     *
     * This function can be used to ensure that a string is safely encoded for output in HTML,
     * potentially after some prior decoding or modification of HTML entities.
     *
     * @param string $string The string to encode.
     * @return string The encoded string with HTML entities.
     */
    private function encode($string)
    {
        return htmlspecialchars(htmlspecialchars_decode($string));
    }

    /**
     * Get the HTML representation of the option as a string.
     *
     * This method generates the HTML markup for the option element. It includes
     * the value, display text, selection state, and any additional attributes.
     *
     * @return string The HTML option element
     */
    public function __toString()
    {
        $selected = $this->selected ? ' selected' : '';
        $attrs = $this->createAttributes();
        if (isset($this->format) && isset($this->params)) {
            $values = $this->getValues();
            $label = vsprintf($this->format, $values);
            return $this->pad . '<option value="' . $this->encode($this->value) . '"' . $attrs . $selected . '>' . $label . '</option>';
        } else {
            return $this->pad . '<option value="' . $this->encode($this->value) . '"' . $attrs . $selected . '>' . $this->encode($this->label) . '</option>';
        }
    }

    /**
     * Alias for the __toString() method.
     *
     * This method is an alias for `__toString()` to provide a more intuitive 
     * method name for rendering the option as a string.
     *
     * @return string The HTML option element
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Get the text node for the option.
     *
     * This method retrieves the text content associated with the option.
     *
     * @return string The text node
     */ 
    public function getTextNode()
    {
        return $this->label;
    }

    /**
     * Set the text node for the option.
     *
     * This method allows setting a new text value for the option.
     *
     * @param string $label The text node to set
     * @return self The current instance, allowing method chaining
     */ 
    public function setTextNode($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get the associated data for the option.
     *
     * This method retrieves the associated `MagicObject` that holds additional data
     * for dynamic value retrieval.
     *
     * @return MagicObject|null The associated data object, or null if not set
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get additional attributes for the option.
     *
     * This method returns the additional attributes that are associated with the option. 
     * These attributes could be HTML attributes or any custom metadata assigned to the option.
     *
     * @return array|null An associative array of attributes, or null if no attributes are set.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set additional attributes for the option.
     *
     * This method allows you to assign additional attributes to the option. 
     * These can be any valid HTML attributes or custom data attributes you wish to associate with the option.
     *
     * @param array|null $attributes An associative array of attributes to set, or null to clear attributes.
     *
     * @return self The current instance of the class, allowing for method chaining.
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

}
