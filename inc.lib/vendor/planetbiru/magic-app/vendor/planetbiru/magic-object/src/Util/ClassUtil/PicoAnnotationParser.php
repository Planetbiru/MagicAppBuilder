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
 * Annotation parser
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoAnnotationParser
{
    const METHOD = "method";
    const PROPERTY = "property";

    /**
     * Raw docblock
     * @var string
     */
    private $rawDocBlock;

    /**
     * Parameters
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
     * Parsed
     *
     * @var boolean
     */
    private $parsedAll = false;

    /**
     * Reflection object
     * @var ReflectionClass|ReflectionMethod|ReflectionProperty
     */
    private $reflection;

    /**
     * Constructor
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
     * Get properties
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->reflection->getProperties();
    }

    /**
     * Check if value is null or empty
     *
     * @param string $value
     * @return boolean
     */
    private function isNullOrEmpty($value)
    {
        return !isset($value) || empty($value);
    }

    /**
     * Parse single annotation
     *
     * @param string $key Key
     * @return array|null
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
     * Parse annotation
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
     * fixing duplicated annotation
     * if any duplication, use last one instead
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
     * Get declared variables
     *
     * @param string $name Name
     * @return string[]
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
     * Get declared variable
     *
     * @param mixed $declaration Declaration
     * @param string $name Name
     * @return string[]
     * @throws InvalidArgumentException
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
     * @return array
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
     * Get parameters
     *
     * @return array
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
     * Get parameters
     *
     * @return PicoGenericObject
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
     * Get parameter
     *
     * @param string $key Key
     * @return array
     */
    public function getParameter($key)
    {
        return $this->parseSingle($key);
    }

    /**
     * Get first parameter
     *
     * @param string $key Key
     * @return string
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
     * Combine and merge array
     *
     * @param array $matches Matched
     * @param array $pair Pair
     * @return array
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
     * Parse parameters. Note that all numeric attributes will be started with underscore (_). Do not use it as is
     *
     * @param string $queryString Query string
     * @return string[]
     * @throws InvalidQueryInputException
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
     * Check if argument is match
     *
     * @param array $keys Keys
     * @param string $val Value
     * @return boolean
     */
    private function matchArgs($keys, $val)
    {
        return stripos($val, '=') === false && stripos($val, '"') === false && stripos($val, "'") === false && !in_array($val, $keys);
    }
    /**
     * Parse parameters as object. Note that all numeric attributes will be started with underscore (_). Do not use it as is
     *
     * @param string $queryString Query string
     * @return PicoGenericObject
     * @throws InvalidAnnotationException
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
