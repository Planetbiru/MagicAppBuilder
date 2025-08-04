<?php

namespace MagicApp\AppDto\MocroServices;

use DOMDocument;
use MagicObject\Util\PicoStringUtil;
use SimpleXMLElement;
use stdClass;

/**
 * Class PicoObjectToString
 *
 * This class provides functionality to convert an object into a JSON string representation. 
 * It overrides the `__toString()` method to return a JSON-encoded string of the object. 
 * The `toArray()` method is used to convert the object into an associative array, 
 * excluding any properties with a `null` value and ignoring the `__caseFormat` property. 
 * If any properties are objects themselves, they are recursively converted into arrays 
 * (by calling their `toArray()` method) before being included in the final JSON string.
 * Additionally, this class supports converting property names between camelCase and snake_case.
 * 
 * @package MagicApp\AppDto\MocroServices
 */
class PicoObjectToString
{
    /**
     * @var string Flag to determine the case format (camelCase or snake_case).
     * 
     * This property controls the case format used in the class, allowing for either camelCase or snake_case. 
     * It helps manage how the property names are formatted, depending on the desired case style.
     */
    protected $__caseFormat = 'camelCase'; //NOSONAR

    /**
     * @var bool Flag to indicate whether to prettify the output.
     * 
     * This property is a boolean flag that determines if the output should be formatted in a prettier, more readable manner.
     * When set to `true`, the output will be prettified; otherwise, it will remain in its default format.
     */
    protected $__prettify = false; //NOSONAR

    /**
     * @var string Format of the output, either 'json' or 'xml'.
     *
     * This property determines the format of the serialized output.
     * It can be set to 'json' for JSON serialization (default) or 'xml' for XML serialization.
     * Depending on the value of this property, the object will be converted to the corresponding format.
     */
    protected $__outputFormat = 'json'; //NOSONAR

    /**
     * @var string The root element name for XML serialization.
     *
     * This property defines the name of the root element when the object is serialized to XML format.
     * By default, it is set to 'data', but it can be customized to any valid XML element name.
     * This property is only used when the output format is set to 'xml'.
     */
    protected $__root = 'data'; //NOSONAR

    /**
     * Sets the output format to JSON.
     *
     * This method allows the user to specify that the output should be in JSON format.
     * It updates the `$__outputFormat` property to 'json' and returns the current instance for method chaining.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function toJsonFormat()
    {
        $this->__outputFormat = 'json';
        return $this;
    }

    /**
     * Sets the output format to XML.
     *
     * This method allows the user to specify that the output should be in XML format.
     * It updates the `$__outputFormat` property to 'xml' and returns the current instance for method chaining.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function toXmlFormat()
    {
        $this->__outputFormat = 'xml';
        return $this;
    }

    /**
     * Sets the case format to camelCase.
     * This method allows switching the format for property names to camelCase.
     * @return self Returns the current instance for method chaining.
     */
    public function toCamelCase()
    {
        $this->__caseFormat = 'camelCase';
        return $this;
    }

    /**
     * Sets the case format to snake_case.
     * This method allows switching the format for property names to snake_case.
     * @return self Returns the current instance for method chaining.
     */
    public function toSnakeCase()
    {
        $this->__caseFormat = 'snakeCase';
        return $this;
    }

    /**
     * Sets the output format to the specified format ('json' or 'xml').
     *
     * This method allows the user to specify the desired output format by passing either 'json' or 'xml'.
     * It updates the `$__outputFormat` property with the given format.
     * If an unsupported format is provided, it will still set the property, but the user should ensure
     * that the format is valid before calling this method.
     *
     * @param string $format The output format. Expected values are 'json' or 'xml'.
     * @return self Returns the current instance for method chaining.
     */
    public function outputFormat($format)
    {
        $this->__outputFormat = $format;
        return $this;
    }

    /**
     * Sets the root element name for XML serialization.
     *
     * This method allows the user to specify a custom root element name for XML output.
     * The `$__root` property is updated with the given value, which will be used as the root element
     * when the object is serialized to XML format.
     *
     * @param string $root The custom root element name for the XML output.
     * @return self Returns the current instance for method chaining.
     */
    public function xmlRoot($root)
    {
        $this->__root = $root;
        return $this;
    }

    /**
     * Set the prettify flag for formatting the output.
     * 
     * @param bool $pretty Flag to enable or disable prettifying the output. If `true`, the output will be prettified.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function prettify($pretty = true)
    {
        $this->__prettify = $pretty;
        return $this;
    }
    
    /**
     * Sets the case format to specified format.
     * This method allows switching the format for property names to specified format.
     * @return self Returns the current instance for method chaining.
     */
    public function switchCaseTo($caseFormat)
    {
        $this->__caseFormat = $caseFormat;
        return $this;
    }

    /**
     * Converts a string to camelCase.
     *
     * @param string $string The string to convert.
     * @return string The converted string in camelCase format.
     */
    private function toCamelCaseString($string)
    {
        return PicoStringUtil::camelize($string);
    }

    /**
     * Converts a string to snake_case.
     *
     * @param string $string The string to convert.
     * @return string The converted string in snake_case format.
     */
    private function toSnakeCaseString($string)
    {
        return PicoStringUtil::snakeize($string);
    }
    
    /**
     * Helper method to determine if a property should be skipped in the conversion.
     *
     * @param string $key The property name.
     * @param mixed $value The property value.
     * @return bool True if the property should be skipped, false otherwise.
     */
    private function shouldBeSkipped($key, $value)
    {
        return $key === '__caseFormat' 
            || $key === '__prettify' 
            || $key === '__outputFormat' 
            || $key === '__root' 
            || $value === null
            ;
    }

    /**
     * Converts the object to an associative array, ignoring properties with a null value.
     * This method iterates over all public and protected properties of the object, excluding private properties,
     * and checks each property to exclude those with a value of `null`. 
     * If the property is an array, it recursively converts any objects within the array to arrays using their `toArray()` method.
     * If the property is an object, the `toArray()` method of the object will be called if it exists.
     * 
     * @return array An associative array representation of the object, excluding null values and private properties.
     */
    public function toArray()
    {
        $data = [];

        // Get public and protected properties, excluding private properties
        $properties = get_object_vars($this);

        // Iterate through all properties to handle parent properties first
        foreach ($properties as $key => $value) {
            // Skip the __caseFormat property as well as properties with a null value
            if ($this->shouldBeSkipped($key, $value)) {
                continue;
            }

            // Check if the property belongs to the parent class
            $isParentProperty = property_exists(get_parent_class($this), $key);
            if ($isParentProperty) {
                // If the property is from the parent, process it separately
                $data = $this->appendData($data, $key, $value);
            }
        }

        // Now iterate through properties to handle own class properties
        foreach ($properties as $key => $value) {
            // Skip the __caseFormat property as well as properties with a null value
            if ($this->shouldBeSkipped($key, $value)) {
                continue;
            }

            // Check if the property belongs to the parent class
            $isParentProperty = property_exists(get_parent_class($this), $key);
            if (!$isParentProperty) {
                // If the property is not from the parent, process it for the current class
                $data = $this->appendData($data, $key, $value);
            }
        }

        return $data;
    }

    /**
     * Appends the property data to the associative array, handling array and object properties.
     * This method checks if the property value is an array or object and processes it accordingly.
     *
     * @param array $data The existing array to append data to.
     * @param string $key The property name.
     * @param mixed $value The property value.
     * @return array The updated array with the appended property data.
     */
    private function appendData($data, $key, $value)
    {
        // If the property is an array, process each item inside it
        if (is_array($value)) {
            // If an item in the array is an object, call toArray() on each object
            $data[$this->applyCaseFormat($key)] = array_map(function ($item) {
                if (is_object($item) && $item instanceof self) {
                    $item->switchCaseTo($this->__caseFormat);
                    return $this->toObject($item->toArray(), $item);
                } else {
                    return $item;
                }
            }, $value);
        }
        // If the property is an object, call toArray() on that object
        elseif (is_object($value)) {
            // If the object has a toArray method, call it
            $value->switchCaseTo($this->__caseFormat);
            $data[$this->applyCaseFormat($key)] = method_exists($value, 'toArray') ? $value->toArray() : $this->toObject($value, $value);
        } else {
            // If not an object or array, store the value directly
            $data[$this->applyCaseFormat($key)] = $value;
        }

        return $data;
    }
    
    /**
     * Converts an object to a standard PHP object if necessary, handling objects of type `self`.
     *
     * @param mixed $value The value to convert.
     * @param object $obj The object to check.
     * @return mixed The converted value.
     */
    public function toObject($value, $obj)
    {
        // If the object is of type 'self' and contains no data, return an empty stdClass object
        if (is_object($obj) && $obj instanceof self && (string)$obj == "[]") {
            return new stdClass;
        }
        // If the value is an instance of 'self', return an empty stdClass object
        if ($value instanceof self) {
            return new stdClass;
        }

        return $value;
    }

    /**
     * Apply the chosen case format (camelCase or snake_case) to the given property name.
     * This method applies the selected format (camelCase or snake_case) to the property name.
     *
     * @param string $key The property name.
     * @return string The property name in the selected case format.
     */
    private function applyCaseFormat($key)
    {
        if (stripos($this->__caseFormat, 'snake') !== false) {
            return $this->toSnakeCaseString($key);
        }
        else if(stripos($this->__caseFormat, 'camel') !== false) {
            return $this->toCamelCaseString($key);
        }
        return $key; // Default, if no case format set
    }

    /**
     * Converts an array or JSON data to an XML string.
     *
     * @param mixed $data The data to be converted to XML. It can be an associative array or an object.
     * @return string The XML representation of the data as a string.
     */
    public function toXml($data)
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$this->__root}/>");
        $this->arrayToXml($data, $xml);
        $xmlString = $xml->asXML();

        if ($this->__prettify) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmlString);
            return $dom->saveXML();
        } else {
            return $xmlString;
        }
    }

    /**
     * Recursively converts an array to XML format.
     *
     * @param mixed $data The data to be converted. It can be a nested array.
     * @param SimpleXMLElement &$xml The XML element to append data to.
     * @return void
     */
    private function arrayToXml($data, &$xml) // NOSONAR
    {
        if(isset($data) && (is_array($data) || is_object($data)))
        {
            foreach ($data as $key => $value) {
                $key = is_numeric($key) ? "item{$key}" : $key;

                if (is_array($value)) {
                    if ($this->isSequentialArray($value)) {
                        foreach ($value as $item) {
                            $child = $xml->addChild($key);
                            $this->arrayToXml($item, $child);
                        }
                    } else {
                        $child = $xml->addChild($key);
                        $this->arrayToXml($value, $child);
                    }
                } else {
                    $xml->addChild($key, htmlspecialchars($value));
                }
            }
        }
    }

    /**
     * Checks if an array is a sequential (indexed) array.
     *
     * @param array $array The array to be checked.
     * @return bool Returns true if the array is sequential, otherwise false.
     */
    private function isSequentialArray($array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Overrides the __toString method to serialize the object into a JSON or XML string.
     * 
     * This method calls the `toArray()` method to convert the object into an array.
     * The output format is determined by the `__outputFormat` property.
     * If the format contains 'xml', the object is converted to an XML string using `toXml()`.
     * Otherwise, it is converted to a JSON string, with optional pretty-print formatting if `__prettify` is enabled.
     * 
     * @return string A string representation of the object in either JSON or XML format.
     */
    public function __toString()
    {
        // Convert the object into an array using the toArray() method
        $data = $this->toArray();

        // Check the output format
        if (stripos($this->__outputFormat, 'xml') !== false) {
            // Convert to XML format if 'xml' is found in the output format
            return $this->toXml($data);
        } else {
            // Convert to JSON format with optional pretty-printing
            if ($this->__prettify) {
                return json_encode($data, JSON_PRETTY_PRINT);
            } else {
                return json_encode($data);
            }
        }
    }

}
