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
    private $textNode;

    /**
     * Value associated with the option.
     *
     * @var string
     */
    private $value;

    /**
     * Indicates whether the option is selected.
     *
     * @var boolean
     */
    private $selected;

    /**
     * Format for the text node, allowing dynamic content.
     *
     * @var string
     */
    private $format;

    /**
     * Parameters for dynamic formatting of the text node.
     *
     * @var string[]
     */
    private $params;

    /**
     * Data associated with the option, typically from a MagicObject.
     *
     * @var MagicObject
     */
    private $data;

    /**
     * Additional attributes for the option.
     *
     * @var string[]
     */
    private $attributes;

    /**
     * Padding to format the output, e.g., for nested options.
     *
     * @var string
     */
    private $pad = "";

    /**
     * Constructor to initialize the option with text, value, selected state, attributes, and data.
     *
     * @param string $textNode The display text for the option
     * @param string|null $value The value of the option
     * @param boolean $selected Indicates if the option is selected
     * @param string[]|null $attributes Additional HTML attributes for the option
     * @param MagicObject|null $data Associated data for dynamic value retrieval
     */
    public function __construct($textNode, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->textNode = $textNode;
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
                $attrs[] = 'data-' . str_replace('_', '-', PicoStringUtil::snakeize($attr)) . '="' . htmlspecialchars($val) . '"';
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
            $textNode = vsprintf($this->format, $values);
            return $this->pad . '<option value="' . htmlspecialchars($this->value) . '"' . $attrs . $selected . '>' . $textNode . '</option>';
        } else {
            return $this->pad . '<option value="' . htmlspecialchars($this->value) . '"' . $attrs . $selected . '>' . htmlspecialchars($this->textNode) . '</option>';
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
        return $this->textNode;
    }

    /**
     * Set the text node for the option.
     *
     * This method allows setting a new text value for the option.
     *
     * @param string $textNode The text node to set
     * @return self The current instance, allowing method chaining
     */ 
    public function setTextNode($textNode)
    {
        $this->textNode = $textNode;
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
}
