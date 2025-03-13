<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Util\PicoEnvironmentVariable;
use MagicObject\Secret\PicoSecret;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoSecretParser;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoGenericObject;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;
use ReflectionClass;
use stdClass;

/**
 * SecretObject class
 *
 * This class provides mechanisms to manage properties that require encryption 
 * and decryption during their lifecycle. It uses annotations to specify which 
 * properties should be encrypted or decrypted when they are set or retrieved. 
 * These annotations help identify when to apply encryption or decryption, 
 * either before saving (SET) or before fetching (GET).
 *
 * The class supports flexibility in data initialization, allowing data to be 
 * passed as an array, an object, or even left empty. Additionally, a secure 
 * callback function can be provided to handle key generation for encryption 
 * and decryption operations.
 *
 * Key features:
 * - Encryption and decryption of object properties based on annotations.
 * - Support for customizing property naming strategies.
 * - Option to provide a secure function for key generation.
 *
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class SecretObject extends stdClass // NOSONAR
{
    const JSON = 'JSON';
    const YAML = 'Yaml';
    const PROPERTY_NAMING_STRATEGY = "property-naming-strategy";
    const PROPERTY_NAMING_STRATEGY_CAMEL = "propertyNamingStrategy";
    const KEY_NAME = "name";
    const KEY_VALUE = "value";
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_DEFAULT_VALUE = "defaultValue";
    const ANNOTATION_ENCRYPT_IN = "EncryptIn";
    const ANNOTATION_DECRYPT_IN = "DecryptIn";
    const ANNOTATION_ENCRYPT_OUT = "EncryptOut";
    const ANNOTATION_DECRYPT_OUT = "DecryptOut";

    const SNAKE_CASE = 'SNAKE_CASE';
    const UPPER_CAMEL_CASE = 'UPPER_CAMEL_CASE';

    /**
     * List of properties to be encrypted when calling SET.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var string[]
     */
    private $_encryptInProperties = array(); // NOSONAR

    /**
     * Class parameters.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    protected $_classParams = array(); // NOSONAR

    /**
     * NULL properties.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    protected $_nullProperties = array(); // NOSONAR

    /**
     * List of properties to be decrypted when calling GET.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var string[]
     */
    private $_decryptOutProperties = array(); // NOSONAR

    /**
     * List of properties to be encrypted when calling GET.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var string[]
     */
    private $_encryptOutProperties = array(); // NOSONAR

    /**
     * List of properties to be decrypted when calling SET.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var string[]
     */
    private $_decryptInProperties = array(); // NOSONAR

    /**
     * Indicates if the object is read-only.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var boolean
     */
    private $_readonly = false; // NOSONAR

    /**
     * Secure function to get encryption key.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var callable
     */
    private $_secureFunction = null; // NOSONAR

    /**
     * Constructor for initializing the object with data.
     *
     * This constructor accepts initial data in various formats (array or object) and 
     * allows the optional specification of a callback function for secure key generation. 
     * The data is processed and loaded into the object upon instantiation.
     *
     * @param self|array|object|null $data The initial data for the object. Can be an 
     *                                     associative array, an object, or null.
     * @param callable|null $secureCallback An optional callback function for generating 
     *                                       secure keys. If provided, it must be callable.
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
     * Analyzes the class's parameters and properties to determine which should be 
     * encrypted or decrypted based on annotations.
     *
     * This method uses reflection to retrieve the class's parameters and properties. 
     * It then parses annotations associated with these members to identify which 
     * properties should undergo encryption or decryption during specific stages 
     * (before storage or before retrieval). The appropriate lists of properties 
     * are populated accordingly.
     *
     * @return void
     *
     * @throws InvalidAnnotationException If the annotations are invalid or cannot be parsed.
     */
    private function _objectInfo()
    {
        $className = get_class($this);
        $reflexClass = new PicoAnnotationParser($className);
        $params = $reflexClass->getParameters();
        $props = $reflexClass->getProperties();

        // Process each class parameter
        foreach ($params as $paramName => $paramValue) {
            try {
                $vals = $reflexClass->parseKeyValue($paramValue);
                $this->_classParams[$paramName] = $vals;
            } catch (InvalidAnnotationException $e) {
                throw new InvalidAnnotationException("Invalid annotation @" . $paramName);
            }
        }

        // Process each class property
        foreach ($props as $prop) {
            $reflexProp = new PicoAnnotationParser($className, $prop->name, 'property');
            $parameters = $reflexProp->getParameters();

            // Check each property for encryption/decryption annotations
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_ENCRYPT_IN) == 0) {
                    // Property should be encrypted before storing
                    $this->_encryptInProperties[] = $prop->name;
                } else if (strcasecmp($param, self::ANNOTATION_DECRYPT_OUT) == 0) {
                    // Property should be decrypted before retrieval
                    $this->_decryptOutProperties[] = $prop->name;
                } else if (strcasecmp($param, self::ANNOTATION_ENCRYPT_OUT) == 0) {
                    // Property should be encrypted before retrieval
                    $this->_encryptOutProperties[] = $prop->name;
                } else if (strcasecmp($param, self::ANNOTATION_DECRYPT_IN) == 0) {
                    // Property should be decrypted before storing
                    $this->_decryptInProperties[] = $prop->name;
                }
            }
        }
    }

    /**
     * Generates a secure key for encryption and decryption.
     *
     * This method checks for a user-defined secure key generation function. If a valid
     * function is provided, it calls that function to generate the key. Otherwise, it 
     * returns a concatenation of predefined random keys.
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
            $var = PicoStringUtil::camelize(substr($method, 5));
            return isset($this->{$var});
        }
        else if (strncasecmp($method, "is", 2) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 2));
            return isset($this->{$var}) ? $this->{$var} == 1 : false;
        } else if (strncasecmp($method, "get", 3) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 3));
            return $this->_get($var);
        }
        else if (strncasecmp($method, "set", 3) === 0 && isset($params) && is_array($params) && !empty($params) && !$this->_readonly) {
            $var = PicoStringUtil::camelize(substr($method, 3));
            $this->_set($var, $params[0]);
            $this->modifyNullProperties($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 5));
            unset($this->{$var});
            return $this;
        }
        else if (strncasecmp($method, "push", 4) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = PicoStringUtil::camelize(substr($method, 4));
            if(!isset($this->{$var}))
            {
                $this->{$var} = array();
            }
            if(is_array($this->{$var}))
            {
                array_push($this->{$var}, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
            }
            return $this;
        }
        else if (strncasecmp($method, "pop", 3) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 3));
            if(isset($this->{$var}) && is_array($this->{$var}))
            {
                return array_pop($this->{$var});
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
     * @return self Returns the current instance for method chaining.
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
        $this->{$var} = $value;
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
        return isset($this->{$var}) ? $this->{$var} : null;
    }

    /**
     * Check if the given data is an instance of MagicObject or PicoGenericObject.
     *
     * @param mixed $data The data to check.
     * @return bool True if the data is an instance, otherwise false.
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
     * @return bool True if the data is an instance, otherwise false.
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
     * This method encrypts the provided data, which can be an object, array, or scalar value.
     * It handles nested structures by encrypting each value individually.
     *
     * @param MagicObject|PicoGenericObject|self|array|stdClass|string|number $data The data to encrypt.
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
                $data->{$key} = $this->encryptValue($value, $hexKey);
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
            $data = (string) $data;
            return $this->encryptString($data, $hexKey);
        }
        return $data;
    }

    /**
     * Encrypt a string.
     *
     * This method encrypts a plain text string using a specified or generated secure key.
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
     * Decrypt data recursively.
     *
     * This method decrypts the provided ciphertext, which can be an object, array, or scalar value.
     * It handles nested structures by decrypting each value individually.
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
                $data->{$key} = $this->decryptValue($value, $hexKey);
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
            $data = (string) $data;
            return $this->decryptString($data, $hexKey);
        }
        return $data;
    }

    /**
     * Decrypt a string.
     *
     * This method decrypts a given ciphertext string using a specified or generated secure key.
     *
     * @param string $ciphertext The encrypted data to be decrypted.
     * @param string|null $hexKey The key in hexadecimal format. If null, a secure key will be generated.
     * @return string|null The decrypted string or null if decryption fails.
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
        if (!$hash || !hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash))
        {
            return null;
        }
        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Check if a value requires encryption before being stored.
     *
     * @param string $var The variable name.
     * @return bool True if the value needs to be encrypted, otherwise false.
     */
    private function needInputEncryption($var)
    {
        return in_array($var, $this->_encryptInProperties);
    }

    /**
     * Check if a value requires decryption after being read.
     *
     * @param string $var The variable name.
     * @return bool True if the value needs to be decrypted, otherwise false.
     */
    private function needOutputDecryption($var)
    {
        return in_array($var, $this->_decryptOutProperties);
    }

    /**
     * Check if a value requires encryption after being read.
     *
     * @param string $var The variable name.
     * @return bool True if the value needs to be encrypted, otherwise false.
     */
    private function needOutputEncryption($var)
    {
        return in_array($var, $this->_encryptOutProperties);
    }

    /**
     * Check if a value requires decryption before being stored.
     *
     * @param string $var The variable name.
     * @return bool True if the value needs to be decrypted, otherwise false.
     */
    private function needInputDecryption($var)
    {
        return in_array($var, $this->_decryptInProperties);
    }

    /**
     * Load data into the object.
     *
     * This method populates the object's properties from the provided data, which can be an object, 
     * array, or scalar value.
     *
     * @param mixed $data The data to load.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof self || $data instanceof MagicObject || $data instanceof PicoGenericObject)
            {
                $values = $data->value();
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->_set($key2, $value);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->_set($key2, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Load data from an INI string.
     *
     * This method parses an INI formatted string and loads the data into the object.
     *
     * @param string $rawData The raw INI data as a string.
     * @param bool $systemEnv Flag to indicate whether to use environment variable replacement.
     * @return self Returns the current instance for method chaining.
     */
    public function loadIniString($rawData, $systemEnv = false)
    {
        // Parse without sections
        $data = PicoIniUtil::parseIniString($rawData);
        if(isset($data) && !empty($data))
        {
            $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
            if($systemEnv)
            {
                $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
            }
            $data = PicoArrayUtil::camelize($data);
            $this->loadData($data);
        }
        return $this;
    }

    /**
     * Load data from an INI file.
     *
     * This method reads an INI file and loads the data into the object.
     *
     * @param string $path The path to the INI file.
     * @param bool $systemEnv Flag to indicate whether to use environment variable replacement.
     * @return self Returns the current instance for method chaining.
     */
    public function loadIniFile($path, $systemEnv = false)
    {
        // Parse without sections
        $data = PicoIniUtil::parseIniFile($path);
        if(isset($data) && !empty($data))
        {
            $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
            if($systemEnv)
            {
                $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
            }
            $data = PicoArrayUtil::camelize($data);
            $this->loadData($data);
        }
        return $this;
    }

    /**
     * Load data from a YAML string.
     *
     * This method parses a YAML formatted string and loads the data into the object.
     *
     * @param string $rawData The YAML data as a string.
     * @param bool $systemEnv Flag to indicate whether to replace environment variables.
     * @param bool $asObject Flag to indicate whether to return results as an object.
     * @param bool $recursive Flag to indicate whether to convert nested objects to MagicObject.
     * @return self Returns the current instance for method chaining.
     */
    public function loadYamlString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = PicoYamlUtil::parse($rawData);
        if(isset($data) && !empty($data))
        {
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
        }
        return $this;
    }

    /**
     * Load data from a YAML file.
     *
     * This method reads a YAML file and loads the data into the object.
     *
     * @param string $path The path to the YAML file.
     * @param bool $systemEnv Flag to indicate whether to replace environment variables.
     * @param bool $asObject Flag to indicate whether to return results as an object.
     * @param bool $recursive Flag to indicate whether to convert nested objects to MagicObject.
     * @return self Returns the current instance for method chaining.
     */
    public function loadYamlFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = PicoYamlUtil::parseFile($path);
        if(isset($data) && !empty($data))
        {
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
        }
        return $this;
    }

    /**
     * Load data from a JSON string.
     *
     * This method parses a JSON formatted string and loads the data into the object.
     *
     * @param string $rawData The JSON data as a string.
     * @param bool $systemEnv Flag to indicate whether to replace environment variables.
     * @param bool $recursive Flag to create recursive object.
     * @return self Returns the current instance for method chaining.
     */
    public function loadJsonString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode($rawData);
        if(isset($data) && !empty($data))
        {
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
        }
        return $this;
    }

    /**
     * Load data from a JSON file.
     *
     * This method reads a JSON file and loads the data into the object.
     *
     * @param string $path The path to the JSON file.
     * @param bool $systemEnv Flag to indicate whether to replace environment variables.
     * @param bool $recursive Flag to create recursive object.
     * @return self Returns the current instance for method chaining.
     */
    public function loadJsonFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode(file_get_contents($path));
        if(isset($data) && !empty($data))
        {
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
        }
        return $this;
    }

    /**
     * Set the object to read-only mode.
     *
     * When in read-only mode, setters will not change the value of the object's properties,
     * but the loadData method will still work.
     *
     * @param bool $readonly Flag to set the object to read-only.
     * @return self Returns the current instance for method chaining.
     */
    protected function readOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    /**
     * Set a property value.
     *
     * @param string $propertyName The name of the property to set.
     * @param mixed|null $propertyValue The value to set for the property.
     * @return self Returns the current instance for method chaining.
     */
    public function set($propertyName, $propertyValue)
    {
        return $this->_set($propertyName, $propertyValue);
    }

    /**
     * Add an element to a property array.
     *
     * @param string $propertyName The name of the property.
     * @param mixed $propertyValue The value to add.
     * @return self Returns the current instance for method chaining.
     */
    public function push($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->{$var}))
        {
            $this->{$var} = array();
        }
        array_push($this->{$var}, $propertyValue);
        return $this;
    }

    /**
     * Remove the last element from a property array.
     *
     * @param string $propertyName The name of the property.
     * @return mixed|null The removed value or null if the property is not an array.
     */
    public function pop($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->{$var}) && is_array($this->{$var}))
        {
            return array_pop($this->{$var});
        }
        return null;
    }

    /**
     * Get a property value.
     *
     * @param string $propertyName The name of the property.
     * @return mixed|null The value of the property or null if not set.
     */
    public function get($propertyName)
    {
        return $this->_get($propertyName);
    }

    /**
     * Get a property value or return a default value if not set.
     *
     * @param string $propertyName The name of the property.
     * @param mixed|null $defaultValue The default value to return if the property is not set.
     * @return mixed The property value or the default value.
     */
    public function getOrDefault($propertyName, $defaultValue = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->{$var}) ? $this->{$var} : $defaultValue;
    }

    /**
     * Copies values from another object to the current object based on specified filters.
     *
     * This method allows selective copying of property values from a source object to the 
     * current object. You can specify which properties to copy using a filter, and you can 
     * choose whether to include properties with null values.
     *
     * @param self|mixed $source The source object from which to copy values.
     * @param array|null $filter An optional array of property names to filter which values 
     *                           should be copied. If null, all properties will be considered.
     * @param bool $includeNull A flag indicating whether to include properties with null 
     *                          values. Defaults to false, meaning null values will be excluded.
     * @return self Returns the current instance for method chaining.
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
        return $this;
    }

    /**
     * Get object value.
     *
     * This method retrieves the values of the object's properties, optionally converting 
     * the property names to snake case.
     *
     * @param bool $snakeCase Flag to convert property names to snake case.
     * @return stdClass An object containing the values of the properties.
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                // get decripted or encrypted value
                $value->{$key} = $this->_get($key);
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = PicoStringUtil::snakeize($key);
                // get decripted or encrypted value
                $value2->{$key2} = PicoStringUtil::snakeizeObject($val);
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Get object value as an object.
     *
     * This method is an alias for the value() method, allowing for retrieval of 
     * object values, optionally in snake case.
     *
     * @param bool $snakeCase Flag to convert property names to snake case.
     * @return stdClass An object containing the values of the properties.
     */
    public function valueObject($snakeCase = false)
    {
        return $this->value($snakeCase);
    }

    /**
     * Get object value as an associative array.
     *
     * This method retrieves the object values and converts them to an associative array,
     * optionally converting property names to snake case.
     *
     * @param bool $snakeCase Flag to convert property names to snake case.
     * @return array An associative array containing the values of the properties.
     */
    public function valueArray($snakeCase = false)
    {
        $value = $this->value($snakeCase);
        return json_decode(json_encode($value), true);
    }

    /**
     * Get object value as an associative array with upper camel case keys.
     *
     * This method retrieves the object values and converts them to an associative array,
     * with keys formatted in upper camel case.
     *
     * @return array An associative array containing the values of the properties with upper camel case keys.
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
     * Check if JSON naming strategy is snake case.
     *
     * @return bool True if the naming strategy is snake case, otherwise false.
     */
    protected function _snakeJson()
    {
        return isset($this->_classParams[self::JSON])
            && (
                isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY]) &&
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], self::SNAKE_CASE) == 0
            || isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL]) && 
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL], self::SNAKE_CASE) == 0
            );
    }

    /**
     * Check if YAML naming strategy is snake case.
     *
     * @return bool True if the naming strategy is snake case, otherwise false.
     */
    protected function _snakeYaml()
    {
        return isset($this->_classParams[self::YAML])
            && (
                isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY]) &&
                strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY], self::SNAKE_CASE) == 0
            || isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY_CAMEL]) && 
                strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY_CAMEL], self::SNAKE_CASE) == 0
            );
    }

    /**
     * Check if JSON naming strategy is upper camel case.
     *
     * @return bool True if the naming strategy is upper camel case, otherwise false.
     */
    protected function isUpperCamel()
    {
        return isset($this->_classParams[self::JSON])
            && (
                isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY]) &&
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], self::UPPER_CAMEL_CASE) == 0
            || isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL]) && 
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL], self::UPPER_CAMEL_CASE) == 0
            );
    }

    /**
     * Check if the JSON output should be prettified
     *
     * @return bool True if JSON output is set to be prettified; otherwise, false
     */
    protected function _pretty()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON]['prettify'])
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Check if JSON naming strategy is camel case.
     *
     * @return bool True if the naming strategy is camel case, otherwise false.
     */
    protected function _camel()
    {
        return !$this->_snakeJson();
    }

    /**
     * Get the list of properties of the class.
     *
     * This method returns an array of properties defined in the class, optionally 
     * reflecting the self class or converting the properties to an array.
     *
     * @param bool $reflectSelf Flag to include properties defined in the current class.
     * @param bool $asArrayProps Flag to return properties as an array.
     * @return array An array of ReflectionProperty objects or property names.
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
     * This method keeps track of properties that have been set to null, allowing for 
     * tracking changes to properties.
     *
     * @param string $propertyName The name of the property.
     * @param mixed $propertyValue The value of the property.
     * @return self Returns the current instance for method chaining.
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
        return $this;
    }

    /**
     * Get the encrypted value of the object.
     *
     * This method returns an array representation of the object's encrypted values.
     *
     * @return array An array containing the encrypted values.
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
        $pretty = $this->_pretty();
        $flag = $pretty ? JSON_PRETTY_PRINT : 0;
        return json_encode($obj->value($this->_snakeJson()), $flag);
    }
}