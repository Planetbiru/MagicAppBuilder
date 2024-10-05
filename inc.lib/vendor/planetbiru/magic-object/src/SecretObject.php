<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\PicoEnvironmentVariable;
use MagicObject\Secret\PicoSecret;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoSecretParser;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoGenericObject;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;
use ReflectionClass;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Secret object
 * @link https://github.com/Planetbiru/MagicObject
 */
class SecretObject extends stdClass //NOSONAR
{
    const JSON = 'JSON';
    const YAML = 'Yaml';
    const KEY_NAME = "name";
    const KEY_VALUE = "value";
    const PROPERTY_NAMING_STRATEGY = "property-naming-strategy";
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_DEFAULT_VALUE = "default_value";
    const ANNOTATION_ENCRYPT_IN = "EncryptIn";
    const ANNOTATION_DECRYPT_IN = "DecryptIn";
    const ANNOTATION_ENCRYPT_OUT = "EncryptOut";
    const ANNOTATION_DECRYPT_OUT = "DecryptOut";

    /**
     * List of propertis to be encrypted when call SET
     *
     * @var string[]
     */
    private $_encryptInProperties = array(); //NOSONAR

    /**
     * Class parameters
     *
     * @var array
     */
    protected $_classParams = array(); //NOSONAR

    /**
     * NULL properties
     *
     * @var array
     */
    protected $_nullProperties = array(); //NOSONAR

    /**
     * List of propertis to be decrypted when call GET
     *
     * @var string[]
     */
    private $_decryptOutProperties = array(); //NOSONAR

    /**
     * List of propertis to be encrypted when call GET
     *
     * @var string[]
     */
    private $_encryptOutProperties = array(); //NOSONAR

    /**
     * List of propertis to be decrypted when call SET
     *
     * @var string[]
     */
    private $_decryptInProperties = array(); //NOSONAR

    /**
     * Read only
     *
     * @var boolean
     */
    private $_readonly = false; //NOSONAR

    private $_secureFunction = null; //NOSONAR

    /**
     * Constructor
     *
     * @param self|array|object $data
     */
    public function __construct($data = null, $secureCallback = null)
    {
        $this->_objectInfo();
        // set callback secure before load default data
        if($secureCallback != null && is_callable($secureCallback))
        {
            $this->_secureFunction = $secureCallback;
        }
        if($data != null)
        {
            if(is_array($data))
            {
                $data = PicoArrayUtil::camelize($data);
            }
            $this->loadData($data);
        }
    }

    /**
     * Process object information
     *
     * @return void
     */
    private function _objectInfo()
    {
        $className = get_class($this);
        $reflexClass = new PicoAnnotationParser($className);
        $params = $reflexClass->getParameters();
        $props = $reflexClass->getProperties();

        foreach($params as $paramName=>$paramValue)
        {
            try
            {
                $vals = $reflexClass->parseKeyValue($paramValue);
                $this->_classParams[$paramName] = $vals;
            }
            catch(InvalidQueryInputException $e)
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
        }

        // iterate each properties of the class
        foreach($props as $prop)
        {
            $reflexProp = new PicoAnnotationParser($className, $prop->name, 'property');
            $parameters = $reflexProp->getParameters();

            // add property list to be encryped or decrypted
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_ENCRYPT_IN) == 0)
                {
                    $this->_encryptInProperties[] = $prop->name;
                }
                else if(strcasecmp($param, self::ANNOTATION_DECRYPT_OUT) == 0)
                {
                    $this->_decryptOutProperties[] = $prop->name;
                }
                else if(strcasecmp($param, self::ANNOTATION_ENCRYPT_OUT) == 0)
                {
                    $this->_encryptOutProperties[] = $prop->name;
                }
                else if(strcasecmp($param, self::ANNOTATION_DECRYPT_IN) == 0)
                {
                    $this->_decryptInProperties[] = $prop->name;
                }
            }
        }
    }

    /**
     * Secure key
     *
     * @return string
     */
    private function secureKey()
    {
        if($this->_secureFunction != null && is_callable($this->_secureFunction))
        {
            return call_user_func($this->_secureFunction);
        }
        else
        {
            return PicoSecret::RANDOM_KEY_1.PicoSecret::RANDOM_KEY_2;
        }
    }

    /**
     * Magic method
     *
     * @param string $method
     * @param mixed $params
     * @return self|boolean|mixed|null
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "isset", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            return isset($this->$var);
        }
        else if (strncasecmp($method, "is", 2) === 0) {
            $var = lcfirst(substr($method, 2));
            return isset($this->$var) ? $this->$var == 1 : false;
        } else if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return $this->_get($var);
        }
        else if (strncasecmp($method, "set", 3) === 0 && isset($params) && isset($params[0]) && !$this->_readonly) {
            $var = lcfirst(substr($method, 3));
            $this->_set($var, $params[0]);
            $this->modifyNullProperties($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0)
        {
            $var = lcfirst(substr($method, 5));
            unset($this->{$var});
            return $this;
        }
        else if (strncasecmp($method, "push", 4) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 4));
            if(!isset($this->$var))
            {
                $this->$var = array();
            }
            if(is_array($this->$var))
            {
                array_push($this->$var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
            }
            return $this;
        }
        else if (strncasecmp($method, "pop", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            if(isset($this->$var) && is_array($this->$var))
            {
                return array_pop($this->$var);
            }
            return null;
        }
    }

    /**
     * Set value
     *
     * @param string $var
     * @param mixed $value
     * @return self
     */
    private function _set($var, $value)
    {
        if($this->needInputEncryption($var))
        {
            $value = $this->encryptValue($value, $this->secureKey());
        }
        else if($this->needInputDecryption($var))
        {
            $value = $this->decryptValue($value, $this->secureKey());
        }
        $this->$var = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @param string $var
     * @return mixed
     */
    private function _get($var)
    {
        $value = $this->_getValue($var);
        if($this->needOutputEncryption($var))
        {
            $value = $this->encryptValue($value, $this->secureKey());
        }
        else if($this->needOutputDecryption($var))
        {
            $value = $this->decryptValue($value, $this->secureKey());
        }
        return $value;
    }

    /**
     * Get value
     *
     * @param string $var
     * @return mixed
     */
    private function _getValue($var)
    {
        return isset($this->$var) ? $this->$var : null;
    }

    /**
     * Check data if instaceof MagicObject or instanceof PicoGenericObject
     *
     * @param mixed $data
     * @return boolean
     */
    private function typeObject($data)
    {
        if($data instanceof MagicObject || $data instanceof PicoGenericObject)
        {
            return true;
        }
        return false;
    }

    /**
     * Check data if instanceof self or instanceof stdClass
     *
     * @param mixed $data Tada to be tested
     * @return boolean
     */
    private function typeStdClass($data)
    {
        if($data instanceof self || $data instanceof stdClass)
        {
            return true;
        }
        return false;
    }

    /**
     * Encrypt data recursive
     *
     * @param MagicObject|PicoGenericObject|self|array|stdClass|string|number $data Data
     * @param string $hexKey Key in hexadecimal format
     * @return mixed
     */
    public function encryptValue($data, $hexKey = null)
    {
        if($hexKey == null)
        {
            $hexKey = $this->secureKey();
        }
        if($this->typeObject($data))
        {
            $values = $data->value();
            foreach($values as $key=>$value)
            {
                $data->set($key, $this->encryptValue($value, $hexKey));
            }
        }
        else if($this->typeStdClass($data))
        {
            foreach($data as $key=>$value)
            {
                $data->$key = $this->encryptValue($value, $hexKey);
            }
        }
        else if(is_array($data))
        {
            foreach($data as $key=>$value)
            {
                $data[$key] = $this->encryptValue($value, $hexKey);
            }
        }
        else
        {
            $data = $data."";
            return $this->encryptString($data, $hexKey);
        }
        return $data;
    }

    /**
     * Encrypt string
     *
     * @param string $plaintext Plain text
     * @param string $hexKey Key in hexadecimal format
     * @return string
     */
    public function encryptString($plaintext, $hexKey = null)
    {
        if($hexKey == null)
        {
            $hexKey = $this->secureKey();
        }
        $key = $hexKey;
        $method = "AES-256-CBC";
        $iv = openssl_random_pseudo_bytes(16);
        $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);
        return base64_encode($iv . $hash . $ciphertext);
    }

    /**
     * Decrypt data recursive
     *
     * @param MagicObject|PicoGenericObject|self|array|stdClass|string $data Data
     * @param string $hexKey Key in hexadecimal format
     * @return mixed
     */
    public function decryptValue($data, $hexKey = null)
    {
        if($hexKey == null)
        {
            $hexKey = $this->secureKey();
        }
        if($this->typeObject($data))
        {
            $values = $data->value();
            foreach($values as $key=>$value)
            {
                $data->set($key, $this->decryptValue($value, $hexKey));
            }
        }
        else if($this->typeStdClass($data))
        {
            foreach($data as $key=>$value)
            {
                $data->$key = $this->decryptValue($value, $hexKey);
            }
        }
        else if(is_array($data))
        {
            foreach($data as $key=>$value)
            {
                $data[$key] = $this->decryptValue($value, $hexKey);
            }
        }
        else
        {
            $data = $data."";
            return $this->decryptString($data, $hexKey);
        }
        return $data;
    }

    /**
     * Decrypt string
     *
     * @param string $data Data
     * @param string $hexKey Key in hexadecimal format
     * @return string
     */
    public function decryptString($ciphertext, $hexKey = null)
    {
        if($hexKey == null)
        {
            $hexKey = $this->secureKey();
        }
        if(!isset($ciphertext) || empty($ciphertext))
        {
            return null;
        }
        $ivHashCiphertext = base64_decode($ciphertext);
        $key = $hexKey;
        $method = "AES-256-CBC";
        $iv = substr($ivHashCiphertext, 0, 16);
        $hash = substr($ivHashCiphertext, 16, 32);
        $ciphertext = substr($ivHashCiphertext, 48);
        if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash))
        {
            return null;
        }
        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Check if value is required to be encrypted before stored
     *
     * @param string $var Variable
     * @return boolean
     */
    private function needInputEncryption($var)
    {
        return in_array($var, $this->_encryptInProperties);
    }

    /**
     * Check if value is required to be decrypted after read
     *
     * @param string $var Variable
     * @return boolean
     */
    private function needOutputDecryption($var)
    {
        return in_array($var, $this->_decryptOutProperties);
    }

    /**
     * Check if value is required to be encrypted after read
     *
     * @param string $var Variable
     * @return boolean
     */
    private function needOutputEncryption($var)
    {
        return in_array($var, $this->_encryptOutProperties);
    }

    /**
     * Check if value is required to be decrypted before stored
     *
     * @param string $var Variable
     * @return boolean
     */
    private function needInputDecryption($var)
    {
        return in_array($var, $this->_decryptInProperties);
    }

    /**
     * Load data to object
     * @param mixed $data Data
     * @return self
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof self || $data instanceof MagicObject || $data instanceof PicoGenericObject)
            {
                $values = $data->value();
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->_set($key2, $value);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->_set($key2, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Load data from INI string
     *
     * @param string $rawData Raw data
     * @param boolean $systemEnv Flag to use environment variable
     * @return self
     */
    public function loadIniString($rawData, $systemEnv = false)
    {
        // Parse without sections
        $data = parse_ini_string($rawData);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        $this->loadData($data);
        return $this;
    }

    /**
     * Load data from INI file
     *
     * @param string $path File path
     * @param boolean $systemEnv Flag to use environment variable
     * @return self
     */
    public function loadIniFile($path, $systemEnv = false)
    {
        // Parse without sections
        $data = parse_ini_file($path);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        $this->loadData($data);
        return $this;
    }

    /**
     * Load data from Yaml string
     *
     * @param string $rawData String of Yaml
     * @param boolean $systemEnv Replace all environment variable value
     * @param boolean $asObject Result is object instead of array
     * @param boolean $recursive Convert all object to MagicObject
     * @return self
     */
    public function loadYamlString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = Yaml::parse($rawData);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);

        if($recursive)
        {
            $this->loadData(PicoSecretParser::parseRecursiveObject($data));
        }
        else
        {
            $this->loadData($data);
        }

        return $this;
    }

    /**
     * Load data from Yaml file
     *
     * @param string $path File path
     * @param boolean $systemEnv Replace all environment variable value
     * @param boolean $asObject Result is object instead of array
     * @param boolean $recursive Convert all object to MagicObject
     * @return self
     */
    public function loadYamlFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = Yaml::parseFile($path);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);

        if($recursive)
        {
            $this->loadData(PicoSecretParser::parseRecursiveObject($data));
        }
        else
        {
            $this->loadData($data);
        }

        return $this;
    }

    /**
     * Load data from JSON string
     *
     * @param string $rawData Raw data
     * @param boolean $systemEnv Flag to use environment variable
     * @param boolean $recursive Flag to create recursive object
     * @return self
     */
    public function loadJsonString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode($rawData);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);

        if($recursive)
        {
            $this->loadData(PicoSecretParser::parseRecursiveObject($data));
        }
        else
        {
            $this->loadData($data);
        }

        return $this;
    }

    /**
     * Load data from JSON file
     *
     * @param string $path File path
     * @param boolean $systemEnv Flag to use environment variable
     * @param boolean $recursive Flag to create recursive object
     * @return self
     */
    public function loadJsonFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode(file_get_contents($path));
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);

        if($recursive)
        {
            $this->loadData(PicoSecretParser::parseRecursiveObject($data));
        }
        else
        {
            $this->loadData($data);
        }

        return $this;
    }

    /**
     * Set readonly. When object is set to readonly, setter will not change value of its properties but loadData still works fine
     *
     * @param boolean $readonly Flag to set object to be readonly
     * @return self
     */
    protected function readOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    /**
     * Set property value
     *
     * @param string $propertyName Property name
     * @param mixed|null $propertyValue Property value
     * @return self
     */
    public function set($propertyName, $propertyValue)
    {
        return $this->_set($propertyName, $propertyValue);
    }

    /**
     * Unset property
     *
     * @param string $propertyName
     * @return self
     */
    public function unset($propertyName)
    {
        $this->__unset($propertyName);
        return $this;
    }

    /**
     * Add array element of property
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return self
     */
    public function push($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->$var))
        {
            $this->$var = array();
        }
        array_push($this->$var, $propertyValue);
        return $this;
    }

    /**
     * Remove last array element of property
     *
     * @param string $propertyName
     * @return mixed
     */
    public function pop($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->$var) && is_array($this->$var))
        {
            return array_pop($this->$var);
        }
        return null;
    }

    /**
     * Get property value
     *
     * @param string $propertyName Property name
     * @return mixed|null $propertyValue Property value
     */
    public function get($propertyName)
    {
        return $this->_get($propertyName);
    }

    /**
     * Get property value
     *
     * @param string $propertyName Property name
     * @return mixed|null $propertyValue Property value
     */
    public function getOrDefault($propertyName, $defaultValue = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : $defaultValue;
    }

    /**
     * Copy value from other object
     *
     * @param self|mixed $source Source
     * @param array $filter Filter
     * @param boolean $includeNull Flag to include null
     * @return void
     */
    public function copyValueFrom($source, $filter = null, $includeNull = false)
    {
        if($filter != null)
        {
            $tmp = array();
            $index = 0;
            foreach($filter as $val)
            {
                $tmp[$index] = trim(PicoStringUtil::camelize($val));
                $index++;
            }
            $filter = $tmp;
        }
        $values = $source->value();
        foreach($values as $property=>$value)
        {
            if(
                ($filter == null || (is_array($filter) && !empty($filter) && in_array($property, $filter)))
                &&
                ($includeNull || $value != null)
                )
            {
                $this->set($property, $value);
            }
        }
    }

    /**
     * Get object value
     * @param boolean $snakeCase Flag to snake case property
     * @return stdClass
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                // get decripted or encrypted value
                $value->$key = $this->_get($key);
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = PicoStringUtil::snakeize($key);
                // get decripted or encrypted value
                $value2->$key2 = PicoStringUtil::snakeizeObject($val);
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Get object value
     * @param boolean $snakeCase Flag to snake case property
     * @return stdClass
     */
    public function valueObject($snakeCase = false)
    {
        return $this->value($snakeCase);
    }

    /**
     * Get object value as associative array
     * @param boolean $snakeCase Flag to snake case property
     * @return array
     */
    public function valueArray($snakeCase = false)
    {
        $value = $this->value($snakeCase);
        return json_decode(json_encode($value), true);
    }

    /**
     * Get object value as associated array with upper case first
     *
     * @return array
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
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    protected function _snakeJson()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if Yaml naming strategy is snake case or not
     *
     * @return boolean
     */
    protected function _snakeYaml()
    {
        return isset($this->_classParams[self::YAML])
            && isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }

    /**
     *  Check if JSON naming strategy is upper camel case or not
     *
     * @return boolean
     */
    protected function isUpperCamel()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'UPPER_CAMEL_CASE') == 0
            ;
    }

    /**
     * Check if JSON naming strategy is camel case or not
     *
     * @return boolean
     */
    protected function _camel()
    {
        return !$this->_snakeJson();
    }

    /**
     * Property list
     * @var boolean $reflectSelf Flag to reflect self
     * @var boolean $asArrayProps Flag to convert properties as array
     * @return array
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
     * Modify null properties
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return void
     */
    private function modifyNullProperties($propertyName, $propertyValue)
    {
        if($propertyValue === null && !isset($this->_nullProperties[$propertyName]))
        {
            $this->_nullProperties[$propertyName] = true;
        }
        if($propertyValue != null && isset($this->_nullProperties[$propertyName]))
        {
            unset($this->_nullProperties[$propertyName]);
        }
    }

    /**
     * Get encrypted value
     *
     * @return array
     */
    public function encryptedValue()
    {
        $obj = clone $this;
        $obj = $this->encryptValueRecorsive($obj);
        $array = json_decode(json_encode($obj->value($this->_snakeJson())), true);
        return $this->encryptValueRecursive($array);
    }

    /**
     * Encrypt value recursively
     *
     * @param array $array Value to be encrypted in array
     * @return array
     */
    private function encryptValueRecursive($array)
    {
        foreach($array as $key=>$val)
        {
            if(is_array($val))
            {
                $array[$key] = $this->encryptValueRecursive($val);
            }
            else if(is_string($val))
            {
                $array[$key] = $this->encryptValue($val, $this->secureKey());
            }
        }
        return $array;
    }

    /**
     * Dumps a PHP value to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param int|null   $inline The level where you switch to inline YAML. If $inline set to NULL, MagicObject will use maximum value of array depth
     * @param int   $indent The amount of spaces to use for indentation of nested nodes
     * @param int   $flags  A bit field of DUMP_* constants to customize the dumped YAML string
     *
     * @return string A YAML string representing the original PHP value
     */
    public function dumpYaml($inline = null, $indent = 4, $flags = 0)
    {
        $snake = $this->_snakeYaml();
        $input = $this->valueArray($snake);
        return PicoYamlUtil::dump($input, $inline, $indent, $flags);
    }

    /**
     * Magic method to stringify object
     *
     * @return string
     */
    public function __toString()
    {
        $obj = clone $this;
        return json_encode($obj->value($this->_snakeJson()), JSON_PRETTY_PRINT);
    }
}