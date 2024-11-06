<?php

namespace MagicObject;

use DateTime;
use DOMDocument;
use InvalidArgumentException;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoObjectParser;
use MagicObject\Util\PicoDateTimeUtil;
use MagicObject\Util\PicoGenericObject;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use SimpleXMLElement;
use stdClass;

/**
 * Class MagicDto
 *
 * Represents a dynamic data transfer object that allows the user to create and manipulate 
 * properties on-the-fly. It can handle various data sources including INI, YAML, JSON, and 
 * databases. Users can perform CRUD operations on database records and manipulate properties 
 * as needed.
 *
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class MagicDto extends stdClass // NOSONAR
{
    // Format constants
    const JSON = 'JSON';
    const XML = 'XML';
    const PRETTIFY = 'prettify';

    /**
     * Class parameters.
     *
     * @var array
     */
    private $_classParams = []; // NOSONAR

    /**
     * Data source.
     *
     * @var mixed
     */
    private $_dataSource = null; //NOSONAR

    /**
     * Constructor.
     *
     * Initializes the object with provided data and database connection.
     *
     * @param self|array|stdClass|MagicObject|SetterGetter|SecretObject|PicoGenericObject|null $data Initial data to populate the object.
     */
    public function __construct($data = null)
    {
        $this->loadData($data);   
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            try
            {
                $vals = $jsonAnnot->parseKeyValue($paramValue);
                $this->_classParams[$paramName] = $vals;
            }
            catch(InvalidQueryInputException $e)
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
        }
    }

    /**
     * Before loading data.
     *
     * This method is called before loading data into the object. Users can override 
     * this method to manipulate the object as needed.
     *
     * @param mixed $object The object to manipulate before loading data.
     * @return mixed The manipulated object.
     */
    public function onLoadData($object)
    {
        return $object;
    }
    
    /**
     * Loads data into the object.
     *
     * This method accepts various data types, including:
     * - An instance of the class itself
     * - An array
     * - A standard object (stdClass)
     * - Other specific object types such as MagicObject, SetterGetter, 
     *   SecretObject, and PicoGenericObject. 
     * 
     * The method processes the input data and stores it in the internal 
     * data source of the object, ensuring that only non-scalar values are 
     * handled.
     *
     * @param self|array|stdClass|MagicObject|SetterGetter|SecretObject|PicoGenericObject|null $data 
     *        The data to load, which can be one of the specified types 
     *        or null.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        // Check if data is not a scalar value
        if (isset($data) && (is_object($data) || is_array($data))) {
            // Check if the data is one of the allowed object types
            if ($data instanceof self || $data instanceof MagicObject || 
                $data instanceof SetterGetter || $data instanceof SecretObject || 
                $data instanceof PicoGenericObject) {
                // Directly assign the data source if it is an allowed object type
                $this->_dataSource = $data;
                if($this->isMethodOverridden($this, "onLoadData"))
                {
                    $this->_dataSource = $this->onLoadData($this->_dataSource);
                }
            } else {
                // Parse the object or array recursively
                $this->_dataSource = PicoObjectParser::parseRecursiveObject($data);
                if($this->isMethodOverridden($this, "onLoadData"))
                {
                    $this->_dataSource = $this->onLoadData($this->_dataSource);
                }
            } 
        }
        return $this;
    }

    private function isMyChild($object) {
        $reflection = new ReflectionClass($object);
        $parentClass = $reflection->getParentClass();
        if(!$parentClass)
        {
            return false;
        }
        return get_class($parentClass) == get_class(new self());
    }
    

    /**
     * Loads XML data into the object.
     *
     * This method accepts an XML string, converts it to an object representation,
     * and then loads the resulting data into the internal data source of the object.
     * It processes the XML input, ensuring that only non-scalar values are handled 
     * appropriately. This method is useful for integrating with external XML data sources.
     *
     * @param string $xmlString The XML string to load into the object.
     * @return self Returns the current instance for method chaining.
     * @throws InvalidArgumentException If the XML string is invalid or cannot be parsed.
     */
    public function loadXml($xmlString)
    {
        $data = $this->xmlToObject($xmlString);
        $this->loadData($data);
        return $this;
    }
    
    /**
     * Retrieves an object containing the values of the properties of the current instance.
     *
     * This method iterates through the properties of the instance, excluding inherited properties,
     * and constructs an object where each property is mapped to its corresponding value.
     * The method handles various property types including self-references, magic objects,
     * DateTime instances, and standard class objects.
     *
     * @return stdClass An object containing the values of the properties, where each property 
     *                  name corresponds to the JSON property or the original key if no 
     *                  JSON property is defined.
     */
    public function value()
    {
        $parentProps = $this->propertyList(true, true);
        $returnValue = new stdClass;

        foreach ($this as $key => $val) {
            if (!in_array($key, $parentProps)) {
                $doc = $this->getPropertyDocComment($key);
                $source = $this->extractSource($doc);
                $jsonProperty = $this->extractJsonProperty($doc);
                $var = $this->extractVar($doc);
                $propertyName = $jsonProperty ? $jsonProperty : $key;

                $objectTest = $this->createTestObject($var);

                if ($this->isSelfInstance($objectTest)) {
                    $returnValue->{$propertyName} = $this->handleSelfInstance($source, $var, $propertyName);
                } elseif ($this->isMagicObjectInstance($objectTest)) {
                    $returnValue->{$propertyName} = $this->handleMagicObject($source, $propertyName);
                } elseif ($this->isDateTimeInstance($objectTest)) {
                    $returnValue->{$propertyName} = $this->formatDateTime($this->handleDateTimeObject($source, $propertyName), $this, $key);
                } else if($this->_dataSource instanceof stdClass || is_object($this->_dataSource)) {
                    $returnValue->{$propertyName} = $this->handleStdClass($source, $key);
                } else if(isset($this->_dataSource)) {
                    $returnValue->{$propertyName} = $this->handleDefaultCase($source, $key);
                }
            }
        }
        return $returnValue;
    }
    
    /**
     * Creates an instance of a class based on the provided variable name.
     *
     * This method checks if the class corresponding to the given variable name exists.
     * If it does, an instance of that class is created and returned; otherwise, null is returned.
     *
     * @param string $var The name of the class to instantiate.
     * @return mixed|null An instance of the class if it exists, or null if the class does not exist.
     */
    private function createTestObject($var)
    {
        return isset($var) && !empty($var) && class_exists($var) ? new $var() : null;
    }

    /**
     * Formats a DateTime object according to specified JSON format parameters.
     *
     * This method checks if the provided DateTime object is set and, if so, retrieves 
     * formatting parameters from the property's annotations. If a 'JsonFormat' 
     * parameter is present, its pattern is used; otherwise, a default format 
     * of 'Y-m-d H:i:s' is applied.
     *
     * @param DateTime|null $dateTime The DateTime object to format.
     * @param object $class The class instance from which the property originates.
     * @param string $property The name of the property being processed.
     * @return string|null The formatted date as a string, or null if the DateTime is not set.
     */
    private function formatDateTime($dateTime, $class, $property)
    {
        if(!isset($dateTime))
        {
            return null;
        }
        $reflexProp = new PicoAnnotationParser(get_class($class), $property, PicoAnnotationParser::PROPERTY);
        $parameters = $reflexProp->getParameters();
        if(isset($parameters['JsonFormat']))
        {
            $parsed = $reflexProp->parseKeyValueAsObject($parameters['JsonFormat']);
            $format = isset($parsed->pattern) ? $parsed->pattern : 'Y-m-d H:i:s';
        }
        else
        {
            $format = 'Y-m-d H:i:s';
        }
        return $dateTime->format($format);
    }

    /**
     * Retrieves the documentation comment for a specified property.
     *
     * @param string $key The name of the property.
     * @return string|null The documentation comment for the property, or null if not found.
     */
    private function getPropertyDocComment($key)
    {
        $propReflect = new ReflectionProperty($this, $key);
        return $propReflect->getDocComment();
    }

    /**
     * Extracts the source from the documentation comment.
     *
     * @param string $doc The documentation comment containing the source.
     * @return string|null The extracted source or null if not found.
     */
    private function extractSource($doc)
    {
        preg_match('/@Source\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    /**
     * Extracts the JSON property name from the documentation comment.
     *
     * @param string $doc The documentation comment containing the JSON property.
     * @return string|null The extracted JSON property name or null if not found.
     */
    private function extractJsonProperty($doc)
    {
        preg_match('/@JsonProperty\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    /**
     * Extracts the variable type from the documentation comment.
     *
     * @param string $doc The documentation comment containing the variable type.
     * @return string|null The extracted variable type or null if not found.
     */
    private function extractVar($doc)
    {
        preg_match('/@var\s+(\S+)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }
    
    /**
     * Extracts the label from the documentation comment.
     *
     * @param string $doc The documentation comment containing the label.
     * @return string|null The extracted label or null if not found.
     */
    private function extractLabel($doc) // NOSONAR
    {
        preg_match('/@Label\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    /**
     * Handles the case where the property is a self-instance.
     *
     * This method retrieves the value from the data source based on the provided
     * source or property name. If the source indicates a nested property, it 
     * retrieves that value instead. It constructs an instance of the specified 
     * class type and returns its stringified value.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $var The variable type (class name) to instantiate.
     * @param string $propertyName The name of the property to fall back on.
     * @return mixed The handled value for the self-instance.
     */
    private function handleSelfInstance($source, $var, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->_dataSource->get($source) : $this->_dataSource->get($propertyName);
            $objectValid = new $var($value);
            return $objectValid->value();
        } else {
            return $this->getNestedValue($source);
        }
    }
    
    /**
     * Checks if the given variable is a self-instance.
     *
     * @param mixed $objectTest The object to test against.
     * @return bool True if it's a self-instance, otherwise false.
     */
    private function isSelfInstance($objectTest)
    {
        return isset($objectTest) && $objectTest instanceof self;
    }

    /**
     * Checks if the given object is an instance of MagicObject or its derivatives.
     *
     * @param mixed $objectTest The object to test.
     * @return bool True if it is a MagicObject instance, otherwise false.
     */
    private function isMagicObjectInstance($objectTest)
    {
        return isset($objectTest) && ($objectTest instanceof MagicObject || 
            $objectTest instanceof SetterGetter || 
            $objectTest instanceof SecretObject || 
            $objectTest instanceof PicoGenericObject);
    }

    /**
     * Checks if the given object is an instance of DateTime or its derivatives.
     *
     * @param mixed $objectTest The object to test.
     * @return bool True if it is a MagicObject instance, otherwise false.
     */
    private function isDateTimeInstance($objectTest)
    {
        return isset($objectTest) && $objectTest instanceof DateTime;
    }

    /**
     * Handles the case where the property is an instance of MagicObject.
     *
     * This method retrieves the value from the data source and checks if it 
     * is an instance of MagicObject or its derivatives. If so, it returns 
     * the stringified value; otherwise, it returns a JSON-encoded version.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $propertyName The name of the property.
     * @return mixed The handled value for the MagicObject instance.
     */
    private function handleMagicObject($source, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->_dataSource->get($source) : $this->_dataSource->get($propertyName);
            return ($value instanceof MagicObject || $value instanceof SetterGetter || 
                    $value instanceof SecretObject || $value instanceof PicoGenericObject) 
                ? $value->value() 
                : json_decode(json_encode($value));
        } else {
            return $this->getNestedValue($source);
        }
    }

    /**
     * Handles the case where the property is an instance of DateTime.
     *
     * This method retrieves the value from the data source and parses it into
     * a DateTime object, either from the source or a nested value.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $propertyName The name of the property.
     * @return DateTime|null The handled DateTime value or null if not applicable.
     */
    private function handleDateTimeObject($source, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->_dataSource->get($source) : $this->_dataSource->get($propertyName);
            return PicoDateTimeUtil::parseDateTime($value);
        } else {
            return PicoDateTimeUtil::parseDateTime($this->getNestedValue($source));
        }
    }

    /**
     * Handles the default case when retrieving property values.
     *
     * This method delegates the handling of standard class properties to 
     * another method.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $key The key of the property.
     * @return mixed The handled default value.
     */
    private function handleDefaultCase($source, $key)
    {
        return $this->handleStdClass($source, $key);
    }

    /**
     * Handles the stdClass when retrieving property values.
     *
     * This method retrieves the value from the data source based on the given 
     * source or key, accounting for nested properties if necessary.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $key The key of the property.
     * @return mixed The retrieved value or null if not found.
     */
    private function handleStdClass($source, $key)
    {
        // Check if the source does not contain a nested property indicator
        if (strpos($source, "->") === false) {
            // If the source is set and exists in the data source, retrieve its value
            if (isset($source) && isset($this->_dataSource->{$source})) {
                $value = $this->_dataSource->{$source};
            // If the source is not available, check for the key in the data source
            } elseif (isset($this->_dataSource->{$key})) {
                $value = $this->_dataSource->{$key};
            // If neither is available, set value to null
            } else {
                $value = null;
            }
        // If the source indicates a nested property, retrieve its value using a different method
        } else {
            $value = $this->getNestedValue($source);
        }
        
        // Return the retrieved value
        return $value;
    }

    /**
     * Retrieves nested values from the data source based on a specified source string.
     *
     * This method splits the source string by the "->" delimiter to navigate through
     * nested properties in the data source, returning the final nested value found.
     *
     * @param string $source The source string indicating the path to the value.
     * @return mixed The nested value retrieved from the data source, or null if not found.
     */
    private function getNestedValue($source)
    {
        $currentVal = null;
        $arr = explode("->", $source);
        $fullKey = $arr[0];
        $currentVal = $this->_dataSource->get($fullKey);
        for ($i = 1; $i < count($arr); $i++) {
            if (isset($currentVal) && $currentVal->get($arr[$i]) != null) {
                $currentVal = $currentVal->get($arr[$i]);
            } else {
                break;
            }
        }
        return $currentVal;
    }

    /**
     * Get the object value as a specified format.
     *
     * This method creates a clone of the current object and transforms its properties
     * into a value representation, returning the object as an instance of stdClass.
     *
     * @return stdClass An object representing the value of the instance.
     */
    public function valueObject()
    {
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value);
                $obj->{$key} = $value;
            }
        }
        return $obj->value();
    }

    /**
     * Get the object value as an associative array.
     *
     * This method converts the object's value representation into an associative array.
     *
     * @return array An associative array representing the object values.
     */
    public function valueArray()
    {
        $value = $this->value();
        return json_decode(json_encode($value), true);
    }

    /**
     * Get the object value as an associative array with the first letter of each key in upper camel case.
     *
     * This method transforms the keys of the associative array representation of the
     * object values to be in upper camel case format.
     *
     * @return array An associative array with keys in upper camel case.
     */
    public function valueArrayUpperCamel()
    {
        $obj = clone $this;
        $array = (array) $obj->value();
        $renameMap = array();
        $keys = array_keys($array);
        foreach($keys as $key)
        {
            $renameMap[$key] = ucfirst($key);
        }
        $array = array_combine(array_map(function($el) use ($renameMap) {
            return $renameMap[$el];
        }, array_keys($array)), array_values($array));
        return $array;
    }

    /**
     * Check if the JSON output should be prettified.
     *
     * This method checks the class parameters to determine if JSON output
     * formatting should be applied to enhance readability.
     *
     * @return bool True if JSON output is set to be prettified; otherwise, false.
     */
    protected function _prettyJson()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PRETTIFY])
            && strcasecmp($this->_classParams[self::JSON][self::PRETTIFY], 'true') == 0
            ;
    }

    /**
     * Check if the XML output should be prettified.
     *
     * This method checks the class parameters to determine if XML output
     * formatting should be applied to enhance readability.
     *
     * @return bool True if XML output is set to be prettified; otherwise, false.
     */
    protected function _prettyXml()
    {
        return isset($this->_classParams[self::XML])
            && isset($this->_classParams[self::XML][self::PRETTIFY])
            && strcasecmp($this->_classParams[self::XML][self::PRETTIFY], 'true') == 0
            ;
    }

    /**
     * Retrieves a list of properties from the current class or its parent class.
     *
     * This method uses reflection to obtain the properties defined in the class.
     * It can return either the property names as an array or the ReflectionProperty
     * objects, depending on the specified flags.
     *
     * @param bool $reflectSelf Flag indicating whether to reflect properties of the current class (true) or the parent class (false).
     * @param bool $asArrayProps Flag indicating whether to return property names as an array (true) or as ReflectionProperty objects (false).
     * @return array An array of property names or ReflectionProperty objects, based on the provided flags.
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        // skip parent properties
        $properties = array_filter(
            $class->getProperties(),
            function($property) use($class) {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );
        if($asArrayProps)
        {
            $result = array();
            $index = 0;
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[$index] = $prop;

                $index++;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }

    /**
     * Recursively converts an object or an array of objects to their string representation.
     *
     * This method traverses through an object or an array of objects of the same class
     * and calls their respective stringify methods, if applicable. The final output 
     * is the stringified representation of the object or array.
     *
     * @param self|array $value The object or array of objects to stringify.
     * @return mixed The stringified object or array.
     */
    private function stringifyObject($value)
    {
        if(is_array($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {
                    $value[$key2] = $val2->stringifyObject($val2);
                }
            }
        }
        else if(is_object($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {

                    $value->{$key2} = $val2->stringifyObject($val2);
                }
            }
        }
        return $value->value();
    }

    /**
     * Convert XML to an object.
     *
     * This function takes an XML string as input and returning it as a stdClass object.
     *
     * @param string $xmlString The XML string to be converted.
     * @return stdClass An object representation of the XML data.
     * @throws InvalidArgumentException If the XML is invalid or cannot be parsed.
     */
    public function xmlToObject($xmlString) {
        // Suppress errors to handle them manually
        libxml_use_internal_errors(true);
        
        // Convert the XML string to a SimpleXMLElement
        $xmlObject = simplexml_load_string($xmlString);
        
        // Check for errors in XML parsing
        if ($xmlObject === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new InvalidArgumentException('Invalid XML provided: ' . implode(', ', array_map(function($error) {
                return $error->message;
            }, $errors)));
        }

        // Convert SimpleXMLElement to stdClass
        return json_decode(json_encode($xmlObject));
    }

    /**
     * Magic method to convert the object to a JSON string representation.
     *
     * This method recursively converts the object's properties into JSON format. 
     * If any property is an instance of the same class, it will also be stringified. 
     * The output can be formatted for readability based on the JSON annotation 
     * of the class.
     *
     * @return string A JSON representation of the object, potentially formatted for readability.
     */
    public function __toString()
    {
        $pretty = $this->_prettyJson();
        $flag = $pretty ? JSON_PRETTY_PRINT : 0;
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value);
                $obj->{$key} = $value;
            }
        }
        return json_encode($obj->value(), $flag);
    }

    /**
     * Magic method to convert the object to a JSON string representation.
     *
     * This method recursively converts the object's properties into JSON format. 
     * If any property is an instance of the same class, it will also be stringified. 
     * The output can be formatted for readability based on the JSON annotation 
     * of the class.
     *
     * @return string A JSON representation of the object, potentially formatted for readability.
     */
    public function toString()
    {
        return (string) $this;
    }

    /**
     * Convert the object to a JSON object.
     *
     * This method decodes the JSON string representation of the object 
     * (produced by the `__toString()` method) and returns it as a PHP 
     * object. This is useful for working with the data in a more 
     * structured format rather than as a JSON string.
     *
     * @return object|null A PHP object representation of the JSON data, or null if decoding fails.
     */
    public function toJson()
    {
        return json_decode((string) $this);
    }

    /**
     * Convert the object to an associative array.
     *
     * This method decodes the JSON string representation of the object 
     * (produced by the `__toString()` method) and returns it as an 
     * associative array. This is useful for accessing the object's 
     * properties in a more straightforward array format.
     *
     * @return array|null An associative array representation of the JSON data, or null if decoding fails.
     */
    public function toArray()
    {
        return json_decode((string) $this, true);
    }


    /**
     * Convert the object's properties to XML format.
     *
     * This method generates an XML representation of the object based on its properties. 
     * The XML structure is built from the object's properties, and the output can 
     * be formatted for readability based on the XML annotation of the class.
     *
     * @param string $root The name of the root element in the XML structure.
     * @return string XML representation of the object's properties, potentially formatted for readability.
     * @throws InvalidArgumentException If the JSON representation of the object is invalid.
     */
    public function toXml($root = "root") {
        // Decode the JSON string into an associative array
        $dataArray = $this->toArray();
        
        // Check if JSON was valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON provided.');
        }

        // Create the XML structure
        $xml = new SimpleXMLElement("<$root/>");
        
        // Recursive function to convert array to XML
        $this->arrayToXml($dataArray, $xml);

        $pretty = $this->_prettyXml();

        if($pretty)
        {      
            // Convert SimpleXMLElement to DOMDocument for prettifying
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            return $dom->saveXML();
        }
        else
        {
            return $xml->asXML();
        }
    }

    /**
     * Converts an array to XML format and appends it to a SimpleXMLElement.
     *
     * This function takes an associative or indexed array and recursively converts
     * it into XML elements. Keys in the array are sanitized to ensure they are valid
     * XML element names. Values that are not arrays are added as child elements.
     *
     * @param array $dataArray The array to convert to XML.
     * @param SimpleXMLElement $xml The XML element to which the converted data will be appended.
     * 
     * @return void This function does not return a value. It modifies the provided XML element.
     */
    public function arrayToXml($dataArray, $xml) {
        foreach ($dataArray as $key => $value) {
            // Replace spaces and special characters in key names
            $key = preg_replace('/[^a-z0-9_]/i', '_', $key);
            
            // If the value is an array, call this function recursively
            if (is_array($value)) {
                $subNode = $xml->addChild($key);
                $this->arrayToXml($value, $subNode);
            } else {
                // If the value is not an array, just add it as a child
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    private function isMethodOverridden($childClass, $methodName) {
        // Buat instance ReflectionClass untuk kelas anak
        $childReflection = new ReflectionClass($childClass);
        
        // Dapatkan kelas induk
        $parentClass = $childReflection->getParentClass();
        
        if ($parentClass) {
            // Dapatkan metode dari kelas anak
            $childMethods = $childReflection->getMethods(ReflectionMethod::IS_PUBLIC);
            
            // Dapatkan metode dari kelas induk
            $parentMethods = $parentClass->getMethods(ReflectionMethod::IS_PUBLIC);
            
            // Ambil nama metode dari metode induk
            $parentMethodNames = array_map(function($method) {
                return $method->getName();
            }, $parentMethods);
            
            // Cek apakah metode ada di kelas anak dan juga di kelas induk
            foreach ($childMethods as $method) {
                if ($method->getName() === $methodName && in_array($methodName, $parentMethodNames)) {
                    return true; // Metode telah di-override
                }
            }
        }
        
        return false; // Jika tidak ada kelas induk atau metode tidak di-override
    }

}
