<?php

namespace MagicObject\Request;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use stdClass;


/**
 * Base class for handling HTTP requests, including input sanitization, data manipulation, 
 * and request type checking (GET, POST, AJAX, etc.).
 *
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/Request
 */
class PicoRequestBase extends stdClass //NOSONAR
{
    /**
     * Class parameters parsed from annotations.
     *
     * @var array
     */
    private $classParams = array();

    /**
     * Flag to force input data to be scalar only.
     *
     * @var bool
     */
    protected $forceScalar = false;

    /**
     * Flag for recursive data processing.
     *
     * @var bool
     */
    protected $_recursive = false;

    /**
     * Constructor to initialize the request handler and process class annotations.
     *
     * @param bool $forceScalar Indicates whether to accept only scalar values for data input.
     * @throws InvalidAnnotationException If there are invalid annotations in the class.
     */
    public function __construct($forceScalar = false)
    {
        $this->forceScalar = $forceScalar;
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            if(is_array($paramValue))
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
            $vals = $jsonAnnot->parseKeyValue($paramValue);
            $this->classParams[$paramName] = $vals;
        }
    }

    /**
     * Load data into the object, transforming keys to camelCase (optional).
     *
     * @param mixed $data Data to be loaded (can be an array or object).
     * @param bool $tolower Flag indicating whether to convert keys to lowercase before loading.
     */
    public function loadData($data, $tolower = false)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if($tolower)
                {
                    $key = strtolower($key);
                }
                $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                $this->{$key2} = $value;
            }
        }
    }

    /**
     * Set a property value dynamically on the object using camelCase notation.
     *
     * @param string $propertyName Name of the property to set.
     * @param mixed $propertyValue Value to assign to the property.
     * @return self
     */
    public function set($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->{$var} = $propertyValue;
        return $this;
    }

    /**
     * Get a property value dynamically from the object.
     *
     * @param string $propertyName Name of the property to retrieve.
     * @param array|null $params Optional parameters for filtering the value.
     * @return mixed|null
     */
    public function get($propertyName, $params = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $value = isset($this->{$var}) ? $this->{$var} : null;
        if(isset($params) && !empty($params))
        {
            $filter = $params[0];
            if(!isset($params[1]))
            {
                $params[1] = false;
            }
            if(!isset($params[2]))
            {
                $params[2] = false;
            }
            if(!isset($params[3]))
            {
                $params[3] = false;
            }
            return $this->filterValue($value, $filter, $params[1], $params[2], $params[3]);
        }
        else
        {
            return $value;
        }
    }

    /**
     * Get the values of all properties as an object (optionally in snake_case).
     *
     * @param bool $snakeCase Flag to convert property names to snake_case.
     * @return stdClass
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val)
        {
            if(!in_array($key, $parentProps))
            {
                $value->{$key} = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val)
            {
                $key2 = PicoStringUtil::snakeize($key);
                $value2->{$key2} = $val;
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Retrieve a list of properties defined in the class, optionally as an array of property names.
     *
     * @param bool $reflectSelf Flag to indicate whether to include only properties of the current class (not inherited).
     * @param bool $asArrayProps Flag to return properties as an array of names.
     * @return array
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        $properties = array_filter(
            $class->getProperties(),
            function($property) use($class)
            {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );

        if($asArrayProps)
        {
            $result = array();
            foreach ($properties as $key)
            {
                $prop = $key->name;
                $result[] = $prop;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }

    /**
     * Filter input data from global variables (GET, POST, etc.) according to the specified filter type.
     *
     * @param int $type The type of input (e.g., INPUT_GET, INPUT_POST).
     * @param string $variableName The name of the variable to filter.
     * @param int $filter The filter type to apply (e.g., FILTER_SANITIZE_EMAIL).
     * @param bool $escapeSQL Flag to escape SQL-specific characters.
     * @return mixed
     */
    public function filterInput($type, $variableName, $filter = PicoFilterConstant::FILTER_DEFAULT, $escapeSQL=false) // NOSONAR
    {
        $var = array();
        switch ($type) {
            case INPUT_GET:
                $var = $_GET;
                break;
            case INPUT_POST:
                $var = $_POST;
                break;
            case INPUT_COOKIE:
                $var = $_COOKIE;
                break;
            case INPUT_SERVER:
                $var = $_SERVER;
                break;
            case INPUT_ENV:
                $var = $_ENV;
                break;
            default:
                $var = $_GET;
        }
        return $this->filterValue(isset($var[$variableName])?$var[$variableName]:null, $filter, $escapeSQL);
    }

    /**
     * Filter a value (or nested values) based on the specified filter type and optional flags.
     *
     * @param mixed $val The value to be filtered.
     * @param int $filter The filter type to apply (e.g., FILTER_SANITIZE_URL).
     * @param bool $escapeSQL Flag to escape SQL-specific characters.
     * @param bool $nullIfEmpty Flag to return null if the value is empty.
     * @param bool $requireScalar Flag to require scalar values.
     * @return mixed|null
     */
    public function filterValue($val, $filter = PicoFilterConstant::FILTER_DEFAULT, $escapeSQL = false, $nullIfEmpty = false, $requireScalar = false)
    {
        $ret = null;

        if(($requireScalar || $this->forceScalar) && (isset($val) && !is_scalar($val)))
        {
            // If application require scalar but user give non-scalar, MagicObject will return null
            // It mean that application will not process invalid input type
            return null;
        }
        if(!isset($val) || is_scalar($val))
        {
            return $this->filterValueSingle($val, $filter, $escapeSQL, $nullIfEmpty);
        }
        else if(is_array($val))
        {
            $ret = array();
            foreach($val as $k=>$v)
            {
                $ret[$k] = $this->filterValueSingle($v, $filter, $escapeSQL, $nullIfEmpty);
            }
        }
        else if(is_object($val))
        {
            $ret = new stdClass();
            foreach($val as $k=>$v)
            {
                $ret->{$k} = $this->filterValueSingle($v, $filter, $escapeSQL, $nullIfEmpty);
            }
        }
        return $ret;
    }

    /**
     * Filter a single value based on the specified filter type, applying specific sanitization rules.
     *
     * @param mixed $val The value to be filtered.
     * @param int $filter The filter type to apply (e.g., FILTER_SANITIZE_NUMBER_INT).
     * @param bool $escapeSQL Flag to escape SQL-specific characters.
     * @param bool $nullIfEmpty Flag to return null if the value is empty.
     * @return mixed
     */
    public function filterValueSingle($val, $filter = PicoFilterConstant::FILTER_DEFAULT, $escapeSQL = false, $nullIfEmpty = false) //NOSONAR
    {
        // add filter
        if($filter == PicoFilterConstant::FILTER_SANITIZE_EMAIL)
        {
            $val = trim(strtolower($val));
            $val = filter_var($val, PicoFilterConstant::FILTER_VALIDATE_EMAIL);
            if($val === false)
            {
                $val = "";
            }
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_URL)
        {
            // filter url
            $val = trim($val);
            if(stripos($val, "://") === false && strlen($val)>2)
            {
                $val = "http://".$val;
            }
            $val = filter_var($val, FILTER_VALIDATE_URL);
            if($val === false)
            {
                $val = "";
            }
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_ALPHA){
            $val = preg_replace("/[^A-Za-z]/i", "", $val); // NOSONAR
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERIC){

            $val = preg_replace("/[^A-Za-z\d]/i", "", $val); // NOSONAR
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERICPUNC){
            $val = preg_replace("/[^A-Za-z\.\-\d_]/i", "", $val); // NOSONAR
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT){
            $val = preg_replace("/[^Ee\+\-\.\d]/i", "", $val); // NOSONAR
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT){
            $val = preg_replace("/[^\+\-\d]/i", "", $val); // NOSONAR
            if(empty($val))
            {
                $val = 0;
            }
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_NUMBER_UINT){
            $val = preg_replace("/[^\+\-\d]/i", "", $val); // NOSONAR
            if(empty($val))
            {
                $val = 0;
            }
            $val = abs($val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_NUMBER_OCTAL){
            $val = preg_replace("/[^0-7]/i", "", $val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_NUMBER_HEXADECIMAL){
            $val = preg_replace("/[^A-Fa-f\d]/i", "", $val); // NOSONAR
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_COLOR){
            $val = preg_replace("/[^A-Fa-f\d]/i", "", $val); // NOSONAR
            if(strlen($val) < 3){
                $val = "";
            }
            else if(strlen($val) > 3 && strlen($val) != 3 && strlen($val) < 6){
                $val = substr($val, 0, 3);
            }
            else if(strlen($val) > 6){
                $val = substr($val, 0, 6);
            }
            if(strlen($val) >= 3){
                $val = strtoupper("#".$val);
            }
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_NO_DOUBLE_SPACE){
            $val = trim(preg_replace("/\s+/"," ",$val)); // NOSONAR
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_PASSWORD){
            $val = trim(preg_replace("/\s+/"," ",$val)); // NOSONAR
            $val = str_ireplace(array('"',"'","`","\\","\0","\r","\n","\t"), "", $val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS){
            $val = htmlspecialchars($val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_ENCODED){
            $val = rawurlencode($val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_STRING_NEW){
            $val = trim(strip_tags($val),"\r\n\t ");
            $val = str_replace(array('<','>','"'), array('&lt;','&gt;','&quot;'), $val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_STRING_INLINE){
            $val = trim(strip_tags($val),"\r\n\t ");
            $val = str_replace(array('<','>','"'), array('&lt;','&gt;','&quot;'), $val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_STRING_BASE64){
            $val = preg_replace("/[^A-Za-z0-9\+\/\=]/", "", $val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_POINT){
            $val = preg_replace("/[^0-9\-\+\/\.,]/", "", $val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_IP){
            $val = filter_var($val, FILTER_VALIDATE_IP);
            if($val === false)
            {
                $val = "";
            }
        }
        if(
            $escapeSQL &&
            (
                $filter == PicoFilterConstant::FILTER_SANITIZE_EMAIL ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_ENCODED ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_IP ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_NO_DOUBLE_SPACE ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_STRING_NEW ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_STRING_INLINE ||
                $filter == PicoFilterConstant::FILTER_SANITIZE_URL
            )
            )
        {
            $val = $this->addslashes($val);
        }
        if($filter == PicoFilterConstant::FILTER_SANITIZE_BOOL){
            $val = trim(preg_replace("/\s+/"," ",$val)); // NOSONAR
            return ($val != null && !empty($val)) && ($val === true || $val == 1 || $val == "1");
        }

        if(is_string($val) && empty($val) && $nullIfEmpty)
        {
            return null;
        }
        return $val;
    }

    /**
     * Add escape slashes to a string to protect against SQL injection or special character issues.
     *
     * @param string $input The input string to escape.
     * @return string
     */
    public function addslashes($input)
    {
        return addslashes($input);
    }

    /**
     * Format and return a numeric value by considering application-specific settings for decimal 
     * and thousand separators.
     *
     * @param stdClass|MagicObject $cfg Configuration object containing separators.
     * @param mixed $input The input value to format.
     * @return float
     */
    public function _getValue($cfg, $input)
    {
        if($input === null || $input == '' || $input == 'undefined')
        {
            return 0;
        }
        $output = $input;

        $decimal_separator = $cfg->getAppDecimalSeparator();
        $thousand_separator = $cfg->getAppThousandSeparator();

        if($thousand_separator != "")
        {
            $output = str_replace($thousand_separator, '', $output);
        }
        if($decimal_separator != ".")
        {
            $output = str_replace(".", "_", $output);
            $output = str_replace(",", ".", $output);
            $output = str_replace("_", "", $output);
        }
        if(empty($output))
        {
            return 0;
        }
        // force value to numeric
        return $output * 1;
    }

    /**
     * Check if the request is a GET request.
     *
     * @return bool True if the request method is GET, false otherwise.
     */
    public function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     * Check if the request is a POST request.
     *
     * @return bool True if the request method is POST, false otherwise.
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Check if the request is an AJAX request.
     *
     * @return bool True if the request is an AJAX request, false otherwise.
     */
    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Retrieve the HTTP method used for the current request.
     *
     * @return string The HTTP method (e.g., GET, POST).
     */
    public function getHttpMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Retrieve the user agent string from the request headers.
     *
     * @return string The user agent string.
     */
    public function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Retrieve the client's IP address from the request headers.
     *
     * @return string The client's IP address.
     */
    public function getClientIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Magic method to handle dynamic method calls.
     *
     * This method is invoked when an undefined method is called on the object.
     * It supports various dynamic operations based on method names, allowing for 
     * flexible interaction with object properties.
     *
     * Supported method patterns:
     * 
     * 1. **Countable Check**: 
     *    - `countable<propertyName>()`: Checks if the specified property is set and is an array.
     *      ```php
     *      $instance->countableItems(); // Returns true if $items is an array.
     *      ```
     * 
     * 2. **Existence Check**: 
     *    - `isset<propertyName>()`: Checks if the specified property is set.
     *      ```php
     *      $instance->issetUsername(); // Returns true if $username is set.
     *      ```
     * 
     * 3. **Boolean Check**: 
     *    - `is<propertyName>()`: Returns true if the specified property is set and evaluates to true (1 or 'true').
     *      ```php
     *      $instance->isActive(); // Returns true if $active is true or 1.
     *      ```
     * 
     * 4. **Getter Method**: 
     *    - `get<propertyName>()`: Retrieves the value of the specified property using the `get()` method.
     *      ```php
     *      $value = $instance->getAge(); // Returns the value of $age.
     *      ```
     * 
     * 5. **Setter Method**: 
     *    - `set<propertyName>($value)`: Sets the specified property to the provided value.
     *      ```php
     *      $instance->setUsername('newUsername'); // Sets $username to 'newUsername'.
     *      ```
     * 
     * 6. **Equality Check**: 
     *    - `equals<propertyName>($value)`: Compares the specified value with the property and returns true if they are equal.
     *      ```php
     *      $isEqual = $instance->equalsUsername('newUsername'); // Returns true if $username is 'newUsername'.
     *      ```
     * 
     * 7. **Checkbox Handling**: 
     *    - `checkbox<propertyName>($value)`: Sets the property to the provided value if it is not already set.
     *      ```php
     *      $instance->checkboxTermsAccepted(true); // Sets $termsAccepted to true if it wasn't already.
     *      ```
     * 
     * 8. **Filter Application**: 
     *    - `filter<propertyName>($filter)`: Applies a filter to the specified property value if it is set.
     *      ```php
     *      $instance->filterEmail('sanitize'); // Applies 'sanitize' filter to $email if set.
     *      ```
     * 
     * 9. **Selected Attribute Creation**: 
     *    - `createSelected<propertyName>($value)`: Returns ' selected="selected"' if the property value matches the provided value.
     *      ```php
     *      $selected = $instance->createSelectedCountry('US'); // Returns ' selected="selected"' if $country is 'US'.
     *      ```
     * 
     * 10. **Checked Attribute Creation**: 
     *    - `createChecked<propertyName>($value)`: Returns ' checked="checked"' if the property value matches the provided value.
     *      ```php
     *      $checked = $instance->createCheckedNewsletter(true); // Returns ' checked="checked"' if $newsletter is true.
     *      ```
     * 
     * 11. **Unset Method**: 
     *    - `unset<propertyName>()`: Unsets specified property value.
     *      ```php
     *      $instance->unsetTags(); // Unsets the property 'tags'.
     *      ```
     *
     * If the method does not match any of the patterns above, the method will return null.
     *
     * @param string $method Name of the method being called.
     * @param array $params Parameters passed to the method.
     * @return mixed|null The result of the method call, or null if the method is not recognized.
     */
    public function __call($method, $params) //NOSONAR
    {
        if (strncasecmp($method, "countable", 9) === 0)
        {
            $var = lcfirst(substr($method, 9));
            return isset($this->{$var}) && is_array($this->{$var});
        }
        else if (strncasecmp($method, "isset", 5) === 0)
        {
            $var = lcfirst(substr($method, 5));
            return isset($this->{$var});
        }
        else if (strncasecmp($method, "is", 2) === 0)
        {
            $var = lcfirst(substr($method, 2));
            return isset($this->{$var}) && ($this->{$var} == 1 || strtolower($this->{$var}) == 'true');
        }
        else if (strncasecmp($method, "get", 3) === 0)
        {
            $var = lcfirst(substr($method, 3));
            return $this->get($var, $params);
        }
        else if (strncasecmp($method, "set", 3) === 0)
        {
            $var = lcfirst(substr($method, 3));
            $this->{$var} = $params[0];
            return $this;
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            $value = isset($this->{$var}) ? $this->{$var} : null;
            return isset($params[0]) && $params[0] == $value;
        }
        else if (strncasecmp($method, "checkbox", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            $this->{$var} = isset($this->{$var}) ? $this->{$var} : $params[0];
            return $this;
        }
        else if (strncasecmp($method, "filter", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            if(isset($this->{$var}))
            {
                $this->{$var} = $this->applyFilter($this->{$var}, $params[0]);
            }
            return $this;
        }
        else if (strncasecmp($method, "createSelected", 14) === 0) {
            $var = lcfirst(substr($method, 14));
            if(isset($this->{$var}))
            {
                return $this->{$var} == $params[0] ? ' selected="selected"' : '';
            }
        }
        else if (strncasecmp($method, "createChecked", 13) === 0) {
            $var = lcfirst(substr($method, 13));
            if(isset($this->{$var}))
            {
                return $this->{$var} == $params[0] ? ' checked="checked"' : '';
            }
        }
        else if (strncasecmp($method, "unset", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            unset($this->{$var});
            return $this;
        }
    }

    /**
     * Apply a filter to the given value based on the specified filter type.
     *
     * This method sanitizes the input value according to the filter type.
     * If the filter type is `FILTER_SANITIZE_SPECIAL_CHARS`, it converts special characters
     * to HTML entities. If the filter type is `FILTER_SANITIZE_BOOL`, it evaluates the
     * value as a boolean. Otherwise, it returns the value unchanged.
     *
     * @param string|null $value The value to be filtered.
     * @param string $filterType The type of filter to apply.
     * @return string|boolean|null The filtered value, a boolean for FILTER_SANITIZE_BOOL, or null if the value is not set.
     */
    private function applyFilter($value, $filterType)
    {
        if(isset($value))
        {
            if($filterType == PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS)
            {
                $ret = htmlspecialchars($value);
            }
            else if($filterType == PicoFilterConstant::FILTER_SANITIZE_BOOL)
            {
                $ret = $value === true || $value == 1 || $value == "1";
            }
            else
            {
                $ret = $value;
            }
            return $ret;
        }
        return null;
    }

    /**
     * Check if the JSON naming strategy is set to snake case.
     *
     * This method determines if the property naming strategy for JSON serialization
     * is using snake case by checking the relevant configuration in class parameters.
     *
     * @return bool True if the naming strategy is snake case, false otherwise.
     */
    private function isSnake()
    {
        return isset($this->classParams['JSON'])
            && isset($this->classParams['JSON']['property-naming-strategy'])
            && strcasecmp($this->classParams['JSON']['property-naming-strategy'], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if the JSON naming strategy is set to camel case.
     *
     * This method returns true if the JSON naming strategy is not snake case,
     * indicating that camel case is used instead.
     *
     * @return bool True if the naming strategy is camel case, false otherwise.
     */
    protected function isCamel()
    {
        return !$this->isSnake();
    }

    /**
     * Check if the JSON output should be prettified.
     *
     * This method determines if the prettification option is enabled in the JSON configuration,
     * indicating whether the output should be formatted for readability.
     *
     * @return bool True if the prettify option is enabled, false otherwise.
     */
    private function isPretty()
    {
        return isset($this->classParams['JSON'])
            && isset($this->classParams['JSON']['prettify'])
            && strcasecmp($this->classParams['JSON']['prettify'], 'true') == 0
            ;
    }

    /**
     * Check if the request is empty.
     *
     * This method checks whether the current request has no values set,
     * indicating that it is considered empty.
     *
     * @return bool True if the request is empty, false otherwise.
     */
    public function isEmpty()
    {
        return empty($this->value(false));
    }

    /**
     * Convert the object to a JSON string representation.
     *
     * This method serializes the object to JSON format, with options for pretty printing
     * based on the configuration. It uses the appropriate naming strategy for properties
     * as specified in the class parameters.
     *
     * @return string The JSON string representation of the object.
     */
    public function __toString()
    {
        $obj = clone $this;
        $json_flag = 0;
        if($this->isPretty())
        {
            $json_flag |= JSON_PRETTY_PRINT;
        }
        return json_encode($obj->value($this->isSnake()), $json_flag);
    }
}