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
 * 
 * This class provides mechanisms for managing properties with encryption 
 * and decryption capabilities, using annotations to specify which properties
 * should be secured.
 * 
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
     * List of properties to be encrypted when calling SET.
     *
     * @var string[]
     */
    private $_encryptInProperties = array(); //NOSONAR

    /**
     * Class parameters.
     *
     * @var array
     */
    protected $_classParams = array(); //NOSONAR

    /**
     * NULL properties.
     *
     * @var array
     */
    protected $_nullProperties = array(); //NOSONAR

    /**
     * List of properties to be decrypted when calling GET.
     *
     * @var string[]
     */
    private $_decryptOutProperties = array(); //NOSONAR

    /**
     * List of properties to be encrypted when calling GET.
     *
     * @var string[]
     */
    private $_encryptOutProperties = array(); //NOSONAR

    /**
     * List of properties to be decrypted when calling SET.
     *
     * @var string[]
     */
    private $_decryptInProperties = array(); //NOSONAR

    /**
     * Indicates if the object is read-only.
     *
     * @var boolean
     */
    private $_readonly = false; //NOSONAR

    /**
     * Secure function to get encryption key
     *
     * @var callable
     */
    private $_secureFunction = null; //NOSONAR

    /**
     * Constructor
     *
     * @param self|array|object $data The initial data for the object.
     * @param callable|null $secureCallback A callback function for secure key generation.
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
     * Process object information.
     *
     * This method retrieves and processes class parameters and properties 
     * to determine which need to be encrypted or decrypted.
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
     * Secure key generation.
     *
     * @return string The secure key for encryption/decryption.
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
     * Magic method called when invoking undefined methods.
     *
     * This method handles dynamic method calls for property management.
     *
     * Supported methods:
     *
     * - `isset<PropertyName>`: Checks if the property is set.
     *   - Example: `$obj->issetFoo()` returns true if property `foo` is set.
     *
     * - `is<PropertyName>`: Checks if the property is set and equals 1 (truthy).
     *   - Example: `$obj->isFoo()` returns true if property `foo` is set and is equal to 1.
     *
     * - `get<PropertyName>`: Retrieves the value of the property.
     *   - Example: `$value = $obj->getFoo()` gets the value of property `foo`.
     *
     * - `set<PropertyName>`: Sets the value of the property.
     *   - Example: `$obj->setFoo($value)` sets the property `foo` to `$value`.
     *
     * - `unset<PropertyName>`: Unsets the property.
     *   - Example: `$obj->unsetFoo()` removes the property `foo`.
     *
     * - `push<PropertyName>`: Pushes a value onto an array property.
     *   - Example: `$obj->pushFoo($value)` adds `$value` to the array property `foo`.
     *
     * - `pop<PropertyName>`: Pops a value from an array property.
     *   - Example: `$value = $obj->popFoo()` removes and returns the last value from the array property `foo`.
     *
     * @param string $method Method name.
     * @param array $params Parameters for the method.
     * @return mixed|null The result of the method call or null if not applicable.
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
     * Set a value for the specified property.
     *
     * This method sets the value of a property and applies encryption or decryption
     * if necessary based on the defined property rules.
     *
     * @param string $var The name of the property.
     * @param mixed $value The value to set.
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
     * Get the value of the specified property.
     *
     * This method retrieves the value of a property and applies encryption or decryption
     * if necessary based on the defined property rules.
     *
     * @param string $var The name of the property.
     * @return mixed The value of the property.
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
     * Get the raw value of the specified property.
     *
     * This method retrieves the raw value of a property without any encryption or decryption.
     *
     * @param string $var The name of the property.
     * @return mixed The raw value of the property, or null if not set.
     */
    private function _getValue($var)
    {
        return isset($this->$var) ? $this->$var : null;
    }

    /**
     * Check if the given data is an instance of MagicObject or PicoGenericObject.
     *
     * @param mixed $data The data to check.
     * @return boolean True if the data is an instance, otherwise false.
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
     * Check if the given data is an instance of self or stdClass.
     *
     * @param mixed $data The data to check.
     * @return boolean True if the data is an instance, otherwise false.
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
     * Encrypt data recursively.
     *
     * @param MagicObject|PicoGenericObject|self|array|stdClass|string|number $data The data to encrypt, which can be an object, array, or scalar value.
     * @param string|null $hexKey The encryption key in hexadecimal format. If null, a secure key will be generated.
     * @return mixed The encrypted data.
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
     * Encrypt a string
     *
     * @param string $plaintext The plain text to be encrypted.
     * @param string|null $hexKey The key in hexadecimal format. If null, a secure key will be generated.
     * @return string The encrypted string in base64 format.
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
     * @param MagicObject|PicoGenericObject|self|array|stdClass|string $data The ciphertext to decrypt.
     * @param string|null $hexKey The key in hexadecimal format. If null, a secure key will be generated.
     * @return string|null The decrypted string or null if decryption fails.
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
     * Modify null properties.
     *
     * This method keeps track of properties that have been set to null.
     *
     * @param string $propertyName The name of the property.
     * @param mixed $propertyValue The value of the property.
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
     * Get the encrypted value of the object.
     *
     * @return array An array representation of the encrypted values.
     */
    public function encryptedValue()
    {
        $obj = clone $this;
        $obj = $this->encryptValueRecorsive($obj);
        $array = json_decode(json_encode($obj->value($this->_snakeJson())), true);
        return $this->encryptValueRecursive($array);
    }

    /**
     * Encrypt values recursively.
     *
     * This method encrypts each string value in the provided array. 
     * Nested arrays are also processed.
     *
     * @param array $array The array of values to be encrypted.
     * @return array The array with encrypted values.
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
     * This method attempts to convert an array into a friendly YAML format.
     *
     * @param int|null $inline The level where to switch to inline YAML. If set to NULL, 
     *                         MagicObject will use the maximum value of array depth.
     * @param int $indent The number of spaces to use for indentation of nested nodes.
     * @param int $flags A bit field of DUMP_* constants to customize the dumped YAML string.
     *
     * @return string A YAML string representing the original PHP value.
     */
    public function dumpYaml($inline = null, $indent = 4, $flags = 0)
    {
        $snake = $this->_snakeYaml();
        $input = $this->valueArray($snake);
        return PicoYamlUtil::dump($input, $inline, $indent, $flags);
    }

    /**
     * Magic method to convert the object to a string.
     *
     * This method returns a JSON representation of the object.
     *
     * @return string A JSON representation of the object.
     */
    public function __toString()
    {
        $obj = clone $this;
        return json_encode($obj->value($this->_snakeJson()), JSON_PRETTY_PRINT);
    }
}