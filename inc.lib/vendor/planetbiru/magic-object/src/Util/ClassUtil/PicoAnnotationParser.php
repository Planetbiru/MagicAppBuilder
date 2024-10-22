<?php

namespace MagicObject\Util\ClassUtil;

use InvalidArgumentException;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidParameterException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Exceptions\ZeroArgumentException;
use MagicObject\Util\PicoGenericObject;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Annotation parser for handling and processing annotations in PHP classes.
 *
 * This class is designed to read, parse, and manage annotations present in
 * the doc comments of classes, methods, and properties. It provides functionalities
 * to retrieve annotations as arrays or objects, handle key-value pairs from query
 * strings, and validate input parameters.
 *
 * The `PicoAnnotationParser` is particularly useful in frameworks or libraries
 * that rely on annotations for configuration, routing, or metadata purposes.
 * 
 * @author Kamshory
 * @package MagicObject\Util\ClassUtil
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoAnnotationParser
{
    const METHOD = "method";
    const PROPERTY = "property";

    /**
     * Raw docblock
     *
     * @var string
     */
    private $rawDocBlock;

    /**
     * Parameters
     *
     * @var array
     */
    private $parameters;

    /**
     * Key pattern
     *
     * @var string
     */
    private $keyPattern = "[A-z0-9\_\-]+";

    /**
     * End pattern
     *
     * @var string
     */
    private $endPattern = "[ ]*(?:@|\r\n|\n)";

    /**
     * Parsed state
     *
     * @var bool
     */
    private $parsedAll = false;

    /**
     * Reflection object
     *
     * @var ReflectionClass|ReflectionMethod|ReflectionProperty
     */
    private $reflection;

    /**
     * Constructor
     *
     * @param mixed ...$args
     * @throws ZeroArgumentException|InvalidParameterException
     */
    public function __construct()
    {
        $arguments = func_get_args();
        $count = count($arguments);

        // get reflection from class or class/method
        // (depends on constructor arguments)
        if ($count === 0) {
            throw new ZeroArgumentException("No zero argument constructor allowed");
        } else if ($count === 1) {
            $reflection = new ReflectionClass($arguments[0]);
        } else {
            $type = $count === 3 ? $arguments[2] : self::METHOD;
            if ($type === self::METHOD) {
                $reflection = new ReflectionMethod($arguments[0], $arguments[1]);
            } else if ($type === self::PROPERTY) {
                $reflection = new ReflectionProperty($arguments[0], $arguments[1]);
            } else {
                throw new InvalidParameterException("Invalid type for $type");
            }
        }
        $this->reflection = $reflection;
        $this->rawDocBlock = $reflection->getDocComment();
        $this->parameters = array();
    }

    /**
     * Retrieves all properties of the reflected class or method.
     *
     * @return ReflectionProperty[] An array of ReflectionProperty objects.
     */
    public function getProperties()
    {
        return $this->reflection->getProperties();
    }

    /**
     * Checks if the given value is null or empty.
     *
     * @param string $value The value to check.
     * @return bool True if the value is null or empty, otherwise false.
     */
    private function isNullOrEmpty($value)
    {
        return !isset($value) || empty($value);
    }

    /**
     * Parses a single annotation based on the provided key.
     *
     * @param string $key The annotation key to parse.
     * @return array|null The parsed value(s) of the annotation or null if not found.
     */
    private function parseSingle($key)
    {
        $ret = null;
        if($this->isNullOrEmpty($key))
        {
            return array();
        }
        if (isset($this->parameters[$key])) {
            $ret = $this->parameters[$key];
        } else {
            if (preg_match("/@" . preg_quote($key) . $this->endPattern . "/", $this->rawDocBlock, $match)) {
                $ret = true;
            } else {
                preg_match_all("/@" . preg_quote($key) . "(.*)" . $this->endPattern . "/U", $this->rawDocBlock, $matches);
                $size = sizeof($matches[1]);

                // not found
                if ($size === 0) {
                    $ret = null;
                }
                // found one, save as scalar
                elseif ($size === 1) {
                    $ret = $this->parseValue($matches[1][0]);
                }
                // found many, save as array
                else {
                    $this->parameters[$key] = array();
                    foreach ($matches[1] as $elem) {
                        $this->parameters[$key][] = $this->parseValue($elem);
                    }
                    $ret = $this->parameters[$key];
                }
            }
        }
        return $ret;
    }

    /**
     * Parses all annotations found in the raw docblock.
     *
     * This method should not be called directly; use `getParameters()` to access
     * parsed parameters instead.
     *
     * @return void
     */
    private function parse()
    {
        $pattern = "/@(?=(.*)" . $this->endPattern . ")/U";
        preg_match_all($pattern, $this->rawDocBlock, $matches);

        foreach ($matches[1] as $rawParameter) {
            if (preg_match("/^(" . $this->keyPattern . ")(.*)$/", $rawParameter, $match)) {
                $parsedValue = $this->parseValue($match[2]);
                if (isset($this->parameters[$match[1]])) {
                    $this->parameters[$match[1]] = array_merge((array)$this->parameters[$match[1]], (array)$parsedValue);
                } else {
                    if($parsedValue == null)
                    {
                        $this->parameters[$match[1]] = new PicoEmptyParameter();
                    }
                    else
                    {
                        $this->parameters[$match[1]] = $parsedValue;
                    }
                }
            } else if (preg_match("/^" . $this->keyPattern . "$/", $rawParameter, $match)) {
                $this->parameters[$rawParameter] = true;
            } else {
                $this->parameters[$rawParameter] = new PicoEmptyParameter();
            }
        }
        $this->fixDuplication();
    }

    /**
     * Fixes duplicated annotations by keeping only the last occurrence.
     *
     * This method is called during the parsing process.
     *
     * @return void
     */
    private function fixDuplication()
    {
        foreach($this->parameters as $key=>$value)
        {
            if(is_array($value))
            {
                $end = end($value);
                $this->parameters[$key] = $end;
            }
        }
    }

    /**
     * Retrieves declared variables from a specified annotation.
     *
     * @param string $name The name of the annotation to retrieve variables from.
     * @return string[] An array of declared variables.
     */
    public function getVariableDeclarations($name)
    {
        $declarations = (array)$this->getParameter($name);
        foreach ($declarations as &$declaration) {
            $declaration = $this->parseVariableDeclaration($declaration, $name);
        }
        return $declarations;
    }

    /**
     * Parses a variable declaration from an annotation.
     *
     * @param mixed $declaration The raw declaration string.
     * @param string $name The name of the annotation for error context.
     * @return string[] An array containing 'type' and 'name'.
     * @throws InvalidArgumentException if the declaration is not a string or is empty.
     */
    private function parseVariableDeclaration($declaration, $name)
    {
        $type = gettype($declaration);

        if ($type !== 'string') {
            throw new InvalidArgumentException(
                "Raw declaration must be string, $type given. Key='$name'."
            );
        }

        if (strlen($declaration) === 0) {
            throw new InvalidArgumentException(
                "Raw declaration cannot have zero length. Key='$name'."
            );
        }

        $declaration = explode(" ", $declaration);
        if (sizeof($declaration) == 1) {
            // string is default type
            array_unshift($declaration, "string");
        }

        // take first two as type and name
        $declaration = array(
            'type' => $declaration[0],
            'name' => $declaration[1]
        );

        return $declaration;
    }

    /**
     * Parse value
     *
     * @param string $originalValue Original value
     * @return mixed
     */
    private function parseValue($originalValue)
    {
        if ($originalValue && $originalValue !== 'null') {
            // try to json decode, if cannot then store as string
            if (($json = json_decode($originalValue, true)) === null) {
                $value = $originalValue;
            } else {
                $value = $json;
            }
        } else {
            $value = null;
        }
        return $value;
    }

    /**
     * Retrieves all parameters from the parsed annotations.
     *
     * If the annotations have not been parsed yet, this method will trigger parsing.
     *
     * @return array An associative array of parsed annotation parameters.
     */
    public function getParameters()
    {
        if (!$this->parsedAll) {
            $this->parse();
            $this->parsedAll = true;
        }
        return $this->parameters;
    }

    /**
     * Retrieves all parameters as an object of type PicoGenericObject.
     *
     * If the annotations have not been parsed yet, this method will trigger parsing.
     *
     * @return PicoGenericObject An object containing the parsed annotation parameters.
     */
    public function getParametersAsObject()
    {
        if (!$this->parsedAll) {
            $this->parse();
            $this->parsedAll = true;
        }
        return new PicoGenericObject($this->parameters);
    }

    /**
     * Retrieves a specific parameter by its key.
     *
     * @param string $key The key of the parameter to retrieve.
     * @return mixed The value of the specified parameter or null if not found.
     */
    public function getParameter($key)
    {
        return $this->parseSingle($key);
    }

    /**
     * Get the first parameter for a given key from the parsed annotations.
     *
     * This method retrieves the first value associated with the specified key.
     * If the parameter does not exist or is null, it returns null. 
     * If the parameter is an array, it returns the first string element. 
     * Otherwise, it returns the value directly.
     *
     * @param string $key The key for which to retrieve the first parameter.
     * @return string|null The first parameter value, or null if not found.
     */
    public function getFirstParameter($key)
    {
        $parameters = $this->parseSingle($key);
        if($parameters == null)
        {
            return null;
        }
        if(is_array($parameters) && is_string($parameters[0]))
        {
            return $parameters[0];
        }
        else
        {
            return $parameters;
        }
    }

    /**
     * Combine and merge two arrays, where the first array contains keys and the second contains values.
     *
     * This method checks if both arrays are set and are of the correct type. 
     * It combines them into a new associative array and returns the merged result.
     *
     * @param array $matches An array of matched keys and values.
     * @param array $pair An associative array to merge with.
     * @return array The merged array containing keys and values from both input arrays.
     */
    private function combineAndMerge($matches, $pair)
    {
        if(isset($matches[1]) && isset($matches[2]) && is_array($matches[1]) && is_array($matches[2]))
        {
            $pair2 = array_combine($matches[1], $matches[2]);
            // merge $pair and $pair2 into $pair3
            return array_merge($pair, $pair2);
        }
        else
        {
            return $pair;
        }
    }

    /**
     * Parse key-value pairs from a query string.
     *
     * This method extracts key-value pairs from a query string, which may contain 
     * attributes with or without quotes. Numeric attributes will have an underscore 
     * prefix. Throws an exception if the input is invalid.
     *
     * @param string $queryString The query string to parse.
     * @return string[] An associative array of parsed key-value pairs.
     * @throws InvalidQueryInputException If the input is not a valid query string.
     */
    public function parseKeyValue($queryString)
    {
        if(!isset($queryString) || empty($queryString) || $queryString instanceof PicoEmptyParameter)
        {
            return array();
        }
        if(!is_string($queryString))
        {
            throw new InvalidAnnotationException("Invalid query string");
        }

        // For every modification, please test regular expression with https://regex101.com/

        // parse attributes with quotes
        $pattern1 = '/([_\-\w+]+)\=\"([a-zA-Z0-9\-\+ _,.\(\)\{\}\`\~\!\@\#\$\%\^\*\\\|\<\>\[\]\/&%?=:;\'\t\r\n|\r|\n]+)\"/m'; // NOSONAR
        preg_match_all($pattern1, $queryString, $matches1);
        $pair1 = array_combine($matches1[1], $matches1[2]);

        // parse attributes without quotes
        $pattern2 = '/([_\-\w+]+)\=([a-zA-Z0-9._]+)/m'; // NOSONAR
        preg_match_all($pattern2, $queryString, $matches2);

        $pair3 = $this->combineAndMerge($matches2, $pair1);

        // parse attributes without any value
        $pattern3 = '/([\w\=\-\_"]+)/m'; // NOSONAR
        preg_match_all($pattern3, $queryString, $matches3);

        $pair4 = array();
        if(isset($matches3) && isset($matches3[0]) && is_array($matches3[0]))
        {
            $keys = array_keys($pair3);
            foreach($matches3[0] as $val)
            {
                if($this->matchArgs($keys, $val))
                {
                    if(is_numeric($val))
                    {
                        // prepend attribute with underscore due unexpected array key
                        $pair4["_".$val] = true;
                    }
                    else
                    {
                        $pair4[$val] = true;
                    }
                }
            }
        }

        // merge $pair3 and $pair4 into result
        return array_merge($pair3, $pair4);
    }

    /**
     * Check if the provided value matches the expected criteria.
     *
     * This method checks if the given value does not contain an equals sign, quotes,
     * and is not present in the provided keys array.
     *
     * @param array $keys The array of valid keys.
     * @param string $val The value to check.
     * @return bool True if the value matches the criteria, otherwise false.
     */
    private function matchArgs($keys, $val)
    {
        return stripos($val, '=') === false && stripos($val, '"') === false && stripos($val, "'") === false && !in_array($val, $keys);
    }
    
    /**
     * Parse parameters from a query string and return them as a PicoGenericObject.
     *
     * This method transforms the key-value pairs parsed from the query string
     * into an instance of PicoGenericObject. All numeric attributes will be 
     * prefixed with an underscore. 
     *
     * @param string $queryString The query string to parse.
     * @return PicoGenericObject An object containing the parsed key-value pairs.
     * @throws InvalidAnnotationException If the input is not a valid query string.
     */
    public function parseKeyValueAsObject($queryString)
    {
        if(PicoStringUtil::isNullOrEmpty($queryString))
        {
            return new PicoGenericObject();
        }
        if(!is_string($queryString))
        {
            throw new InvalidAnnotationException("Invalid query string");
        }
        return new PicoGenericObject($this->parseKeyValue($queryString));
    }
}
