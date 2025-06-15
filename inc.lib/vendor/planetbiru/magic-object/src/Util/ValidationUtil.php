<?php
namespace MagicObject\Util;

use DateTime;
use DateTimeInterface;
use Exception;
use MagicObject\Exceptions\InvalidValueException;
use MagicObject\MagicObject;
use ReflectionClass;

/**
 * Utility class for validating object properties based on annotations defined in their docblocks.
 * This class provides a set of common validation rules, inspired by Jakarta Bean Validation (JSR 380)
 * as used in Spring Boot, by parsing PHP docblock comments.
 */
class ValidationUtil // NOSONAR
{
    /**
     * Default validation message templates.
     *
     * @var array
     */
    private $validationMessageTemplate = array();
    
    /**
     * Get a new instance of ValidationUtil.
     *
     * @param array $customTemplates Optional. An associative array of custom message templates.
     * These templates will override or extend the default messages.
     * @return self A new instance of ValidationUtil.
     */
    public static function getInstance($customTemplates = array())
    {
        if(!isset($customTemplates) || !is_array($customTemplates)) {
            $customTemplates = array();
        }   
        return new self($customTemplates);
    }
    
    /**
     * ValidationUtil constructor.
     *
     * Initializes the ValidationUtil instance and sets up default or custom validation message templates.
     *
     * @param array $customTemplates Optional. An associative array of custom message templates to override or extend the defaults.
     */
    public function __construct($customTemplates = array())
    {
        if(!isset($customTemplates) || !is_array($customTemplates)) {
            $customTemplates = array();
        } 
        $this->init($customTemplates);
    }
    
    /**
     * Initializes default validation message templates.
     *
     * This method sets up the default validation message templates and allows overriding or extending them
     * with custom templates provided as an argument.
     *
     * @param array $customTemplates Optional. An associative array of custom message templates to override or extend the defaults.
     * @return self Returns the current instance for method chaining.
     */
    public function init($customTemplates = array())
    {
        $this->validationMessageTemplate = array(
            'required' => "Field '\${property}' cannot be null",
            'notEmpty' => "Field '\${property}' cannot be empty",
            'notBlank' => "Field '\${property}' cannot be blank",
            'size' => "Field '\${property}' must be between \${min} and \${max} characters",
            'min' => "Field '\${property}' must be at least \${min}",
            'max' => "Field '\${property}' must be less than \${max}",
            'pattern' => "Invalid format for field '\${property}'",
            'email' => "Invalid email address for field '\${property}'",
            'past' => "Date for field '\${property}' must be in the past",
            'future' => "Date for field '\${property}' must be in the future",
            'decimalMin' => "Value for field '\${property}' must be at least \${min}",
            'decimalMax' => "Value for field '\${property}' must be less than \${max}",
            'digits' => "Value for field '\${property}' must have at most \${integer} integer digits and \${fraction} fractional digits",
            'assertTrue' => "Field '\${property}' must be true",
            'futureOrPresent' => "Date for field '\${property}' cannot be in the past",
            'length' => "Field '\${property}' must be between \${min} and \${max} characters",
            'range' => "Value for field '\${property}' must be between \${min} and \${max}",
            'noHtml' => "Field '\${property}' contains HTML tags and must be removed",
            'positive' => "Field '\${property}' must be a positive number",
            'positiveOrZero' => "Field '\${property}' must be zero or a positive number",
            'negative' => "Field '\${property}' must be a negative number",
            'negativeOrZero' => "Field '\${property}' must be zero or a negative number",
            'pastOrPresent' => "Date for field '\${property}' must be in the past or present",
            'url' => "Field '\${property}' must be a valid URL",
            'ip' => "Field '\${property}' must be a valid IP address",
            'dateFormat' => "Field '\${property}' must match the date format '\${format}'",
            'phone' => "Field '\${property}' must be a valid phone number",
            'enum' => "Field '\${property}' has an invalid value. Allowed values: \${allowedValues}.",
            'alpha' => "Field '\${property}' must contain only alphabetic characters",
            'alphaNumeric' => "Field '\${property}' must contain only alphanumeric characters",
            'startsWith' => "Field '\${property}' must start with '\${prefix}'",
            'endsWith' => "Field '\${property}' must end with '\${suffix}'",
            'contains' => "Field '\${property}' must contain '\${substring}'",
            'beforeDate' => "Field '\${property}' must be before '\${date}'",
            'afterDate' => "Field '\${property}' must be after '\${date}'",
        );

        // Process custom templates to camelize keys
        if (!empty($customTemplates)) {
            $camelizedCustomTemplates = array();
            foreach ($customTemplates as $key => $value) {
                // Make sure PicoStringUtil is imported or use the full namespace
                $camelizedKey = PicoStringUtil::camelize($key); 
                $camelizedCustomTemplates[$camelizedKey] = $value;
            }
            $this->validationMessageTemplate = array_merge($this->validationMessageTemplate, $camelizedCustomTemplates);
        }
        return $this; // Allow method chaining
    }
    
    /**
     * Attempts to convert the given value to a DateTime object.
     *
     * Supports values of type:
     * - DateTimeInterface: returned as-is.
     * - int: treated as a UNIX timestamp.
     * - string: parsed using PHP's DateTime constructor.
     *
     * If the value cannot be converted (e.g., invalid date string), null is returned.
     *
     * @param mixed $value The value to convert (DateTimeInterface, int, or string).
     * @return DateTime|null The corresponding DateTime object, or null on failure.
     */
    public function convertToDateTime($value) // NOSONAR
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_int($value)) {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($value);
            return $dateTime;
        }

        if (is_string($value)) {
            try {
                return new DateTime($value);
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Validates an object's properties against defined annotations in their docblocks.
     * If any validation rule fails, an InvalidValueException is thrown.
     *
     * @param object $object The object to be validated. It's expected that properties might be
     * accessed via Reflection, so protected/private properties are handled.
     * @param string|null $parentPropertyName Optional. The name of the parent property, used for nested validation messages.
     * @throws InvalidValueException If any validation constraint is violated.
     */
    public function validate($object, $parentPropertyName = null) // NOSONAR
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();
        $package = $reflectionClass->getNamespaceName();

        foreach ($properties as $property) {
            $docComment = $property->getDocComment();
            if ($docComment === false || strpos($property->getName(), '_') === 0) {
                continue;
            }

            $propertyName = $property->getName();
            $fullPropertyName = isset($parentPropertyName) ? $parentPropertyName . '.' . $propertyName : $propertyName;

            $property->setAccessible(true); // NOSONAR
            $propertyValue = $property->getValue($object); // NOSONAR           

            if ($this->validateValidAnnotation($fullPropertyName, $propertyValue, $docComment, $package)) {
                continue;
            }
            $this->validateRequiredAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNotEmptyAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNotBlankAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateAlphaAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateAlphaNumericAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateStartsWithAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateEndsWithAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateContainsAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateBeforeDateAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateAfterDateAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateSizeAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateMinAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateMaxAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePatternAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateEmailAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePastAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateFutureAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDecimalMinAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDecimalMaxAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDigitsAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateAssertTrueAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateFutureOrPresentAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateLengthAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateRangeAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNoHtmlAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePositiveAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePositiveOrZeroAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNegativeAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateNegativeOrZeroAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePastOrPresentAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateUrlAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateIpAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateDateFormatAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validatePhoneAnnotation($fullPropertyName, $propertyValue, $docComment);
            $this->validateEnumAnnotation($fullPropertyName, $propertyValue, $docComment);
        }
    }
    
    /**
     * Creates a validation message using a predefined template.
     *
     * This method replaces placeholders like ${property}, ${min}, etc. in the template
     * with the corresponding values from the provided associative array.
     *
     * @param string $validationType The type of validation (e.g., 'required', 'size').
     * @param array $values An associative array of values to be inserted into the message template (e.g., ['property' => $propertyName, 'min' => $min, 'max' => $max]).
     * @return string The formatted validation message.
     */
    private function createMessage($validationType, $values)
    {
        if (!isset($this->validationMessageTemplate[$validationType])) {
            return "Invalid value for unknown validation type: " . $validationType;
        }

        $template = $this->validationMessageTemplate[$validationType];

        // Replace placeholders like ${property}, ${min}, etc. with actual values
        $replace = array();
        foreach ($values as $key => $val) {
            $replace['${' . $key . '}'] = $val;
        }
        return str_ireplace(array_keys($replace), array_values($replace), $template);
    }

    /**
     * Retrieves an annotation parameter value and converts it to the specified type.
     *
     * @param array $params The array of annotation parameters.
     * @param string $name The name of the parameter to retrieve.
     * @param string $type The expected type of the parameter ('string', 'int', or 'bool').
     * @param mixed $default The default value to return if the parameter is not set.
     * @return mixed The value of the parameter, converted to the specified type, or the default value.
     */
    public function getAnnotationParam($params, $name, $type = 'string', $default = null) // NOSONAR
    {
        if (!isset($params[$name])) {
            return $default;
        }

        $value = $params[$name];

        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return strtolower($value) === 'true' || $value === '1';
            case 'string':
            default:
                return trim((string) $value); // Ensure it's a string, trimmed of whitespace
        }
    }

    /**
     * Parses annotation parameters from a string into an associative array.
     *
     * @param string $annotationParams The annotation parameters as a string.
     * @return array The parsed parameters as an associative array.
     */
    public function parseAnnotationParams($annotationParams)
    {
        $result = [];

        preg_match_all('/(\w+)\s*=\s*(?:"([^"]*)"|([\d.]+)|\b(true|false)\b)/i', $annotationParams, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[1];

            if (isset($match[2]) && $match[2] !== '') {
                // String value
                $value = $match[2];
            } elseif (isset($match[3]) && $match[3] !== '') {
                // Numeric value: detect float or int
                $value = strpos($match[3], '.') !== false ? (float)$match[3] : (int)$match[3];
            } elseif (isset($match[4])) {
                // Boolean literal
                $value = strtolower($match[4]) === 'true';
            } else {
                $value = null;
            }

            $result[$key] = $value;
        }

        return $result;
    }


    /**
     * Validates the **`Valid`** annotation for nested objects.
     * This annotation triggers recursive validation for MagicObject instances.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @param string $package The namespace of the parent object.
     * @throws InvalidValueException If a nested object validation fails.
     */
    private function validateValidAnnotation($propertyName, $propertyValue, $docComment, $package = '') // NOSONAR
    {
        if (preg_match('/@Valid/', $docComment)) {
            if (is_object($propertyValue) && $propertyValue instanceof MagicObject) {
                // Pass the current full property name to the recursive call

                // Get @var 
                $reference = null;
                if (preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
                    $varType = $matches[1];
                    // If $varType does not contain namespace separator, prepend $package
                    if (strpos($varType, '\\') === false && !empty($package)) {
                        $varType = $package . '\\' . $varType;
                    }
                    if(class_exists($varType)) {
                        $reference = new $varType(); // Create a new instance if needed
                        if ($reference instanceof MagicObject) {
                            $reference->loadData($propertyValue); // Load data from the current property value
                        }
                        $this->validate($reference, $propertyName);
                        return true; // Indicates that @Valid was processed
                    }
                }
                $this->validate($propertyValue, $propertyName);
            }
            return true; // Indicates that @Valid was processed
        }
        return false;
    }

    /**
     * Validates the **`Required`** annotation.
     * Ensures the property value is not strictly null.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the property value is null.
     */
    private function validateRequiredAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Required(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('required', array('property' => $propertyName));
            }

            if ($propertyValue === null) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
        elseif (preg_match('/@Required(?: vigilance)?\b/', $docComment)) {
            if ($propertyValue === null) {
                $message = $this->createMessage('required', array('property' => $propertyName));
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`NotEmpty`** annotation.
     * Ensures a string is not empty or an array is not empty.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string or array property value is empty.
     */
    private function validateNotEmptyAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@NotEmpty(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('notEmpty', array('property' => $propertyName));
            }
        } elseif (preg_match('/@NotEmpty(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('notEmpty', array('property' => $propertyName));
        } else {
            return;
        }

        if ((is_string($propertyValue) && trim($propertyValue) === '') || (is_array($propertyValue) && empty($propertyValue))) {
            throw new InvalidValueException($propertyName, $message);
        }
    }

    /**
     * Validates the **`NotBlank`** annotation.
     * Ensures a string is not empty and not just whitespace.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string property value is blank.
     */
    private function validateNotBlankAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@NotBlank(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('notBlank', array('property' => $propertyName));
            }
        } elseif (preg_match('/@NotBlank(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('notBlank', array('property' => $propertyName));
        } else {
            return;
        }

        if (is_string($propertyValue) && trim($propertyValue) === '') {
            throw new InvalidValueException($propertyName, $message);
        }
    }

    /**
     * Validates the **`Size`** annotation.
     * Ensures the size of a string (length) or an array (count) is within a specified range.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the size is outside the specified range.
     */
    private function validateSizeAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Size(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $min = $this->getAnnotationParam($params, 'min', 'int');
            $max = $this->getAnnotationParam($params, 'max', 'int');
            $message = $this->getAnnotationParam($params, 'message', 'string');
            if ($this->isBlank($message)) {
                $message = $this->createMessage('size', array('property' => $propertyName, 'min' => $min, 'max' => $max));
            }

            if (($min !== null && $max !== null) &&
                (
                    (is_string($propertyValue) && (strlen($propertyValue) < $min || strlen($propertyValue) > $max)) ||
                    (is_array($propertyValue) && (count($propertyValue) < $min || count($propertyValue) > $max))
                )
            ) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Min`** annotation.
     * Ensures a numeric value is greater than or equal to a specified minimum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is less than the minimum.
     */
    private function validateMinAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Min(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $min = $this->getAnnotationParam($params, 'value', 'float');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('min', array('property' => $propertyName, 'min' => $min));
            }

            if (is_numeric($propertyValue) && $propertyValue < $min) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Max`** annotation.
     * Ensures a numeric value is less than or equal to a specified maximum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is greater than the maximum.
     */
    private function validateMaxAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Max(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $max = $this->getAnnotationParam($params, 'value', 'float');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('max', array('property' => $propertyName, 'max' => $max));
            }

            if (is_numeric($propertyValue) && $propertyValue > $max) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Pattern`** annotation.
     * Ensures a string matches a specified regular expression.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string does not match the pattern.
     */
    private function validatePatternAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Pattern(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $regexp = $this->getAnnotationParam($params, 'regexp', 'string');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('pattern', array('property' => $propertyName));
            }

            // Unescape double backslashes (e.g. \\d) into single backslash for PHP regex
            $regexp = str_replace('\\\\', '\\', $regexp);

            if (is_string($propertyValue) && !preg_match("/{$regexp}/", $propertyValue)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Email`** annotation.
     * Ensures a string is a well-formed email address.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string is not a valid email address.
     */
    private function validateEmailAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Email(?: vigilance)?(?:\(([^)]*)\))?/', $docComment, $matches)) {
            $params = [];
            if (isset($matches[1])) {
                $params = $this->parseAnnotationParams($matches[1]);
            }

            $message = $this->getAnnotationParam($params, 'message', 'string');
            if ($this->isBlank($message)) {
                $message = $this->createMessage('email', array('property' => $propertyName));
            }

            if (is_string($propertyValue) && !filter_var($propertyValue, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Past`** annotation.
     * Ensures a date is in the past. Supports DateTimeInterface, int (timestamp), and string date formats.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the date is not in the past.
     */
    private function validatePastAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Past(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('past', array('property' => $propertyName));
            }

            // Convert the property value to a DateTime object
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface) {
                $now = new DateTime();
                if ($date->getTimestamp() >= $now->getTimestamp()) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
        }
    }

    /**
     * Validates the **`Future`** annotation.
     * Ensures a date is in the future. Supports DateTimeInterface, int (timestamp), and string date formats.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the date is not in the future.
     */
    private function validateFutureAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Future(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $message = $this->getAnnotationParam($params, 'message', 'string');
            if ($this->isBlank($message)) {
                $message = $this->createMessage('future', array('property' => $propertyName));
            }

            // Convert the property value to a DateTime object
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface) {
                $now = new DateTime();
                if ($date->getTimestamp() <= $now->getTimestamp()) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
        }
    }

    /**
     * Validates the **`DecimalMin`** annotation.
     * Ensures a numeric value is greater than or equal to a specified decimal minimum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is less than the specified decimal minimum.
     */
    private function validateDecimalMinAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@DecimalMin(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $min = $this->getAnnotationParam($params, 'value', 'float');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($min === null) {
                // Bisa lempar exception atau set default min 0
                $min = 0.0;
            }

            if ($this->isBlank($message)) {
                $message = $this->createMessage('decimalMin', array('property' => $propertyName, 'min' => $min));
            }

            if (is_numeric($propertyValue) && (float)$propertyValue < $min) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`DecimalMax`** annotation.
     * Ensures a numeric value is less than or equal to a specified decimal maximum.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is greater than the specified decimal maximum.
     */
    private function validateDecimalMaxAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@DecimalMax(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $max = $this->getAnnotationParam($params, 'value', 'float');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($max === null) {
                $max = 0.0; // atau lempar exception jika perlu
            }

            if ($this->isBlank($message)) {
                $message = $this->createMessage('decimalMax', array('property' => $propertyName, 'max' => $max));
            }

            if (is_numeric($propertyValue) && (float)$propertyValue > $max) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }


    /**
     * Validates the **`Digits`** annotation.
     * Ensures a numeric value has at most a specified number of integer and fractional digits.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the number of digits exceeds the specified limits.
     */
    private function validateDigitsAnnotation($propertyName, $propertyValue, $docComment) // NOSONAR
    {
        if (preg_match('/@Digits(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);

            $integer = $this->getAnnotationParam($params, 'integer', 'int');
            $fraction = $this->getAnnotationParam($params, 'fraction', 'int');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($integer === null) {
                $integer = 0;
            }
            if ($fraction === null) {
                $fraction = 0;
            }

            if ($this->isBlank($message)) {
                $message = $this->createMessage('digits', array('property' => $propertyName, 'integer' => $integer, 'fraction' => $fraction));
            }

            if (is_numeric($propertyValue)) {
                $parts = explode('.', (string)$propertyValue);
                $integerPart = $parts[0];
                $fractionalPart = isset($parts[1]) ? $parts[1] : '';

                if (strlen($integerPart) > $integer || strlen($fractionalPart) > $fraction) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
        }
    }

    /**
     * Validates the **`AssertTrue`** annotation.
     * Ensures a boolean property value is strictly true.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the boolean value is not true.
     */
    private function validateAssertTrueAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@AssertTrue(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('assertTrue', array('property' => $propertyName));
            }

            if ($propertyValue !== true) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`FutureOrPresent`** annotation.
     * Ensures a date is in the future or the present. Supports DateTimeInterface, int (timestamp), and string date formats.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the date is in the past.
     */
    private function validateFutureOrPresentAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@FutureOrPresent(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('futureOrPresent', array('property' => $propertyName));
            }

            // Convert the property value to a DateTime object
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface && $date->getTimestamp() < (new DateTime())->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Length`** annotation.
     * Ensures the length of a string is within a specified range. This is specific to strings.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string length is outside the specified range.
     */
    private function validateLengthAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Length(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $min = $this->getAnnotationParam($params, 'min', 'int');
            $max = $this->getAnnotationParam($params, 'max', 'int');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('length', array('property' => $propertyName, 'min' => $min, 'max' => $max));
            }

            if (is_string($propertyValue) && ($min !== null && strlen($propertyValue) < $min || $max !== null && strlen($propertyValue) > $max)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Range`** annotation.
     * Ensures a numeric value is within a specified inclusive range.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the numeric value is outside the specified range.
     */
    private function validateRangeAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Range(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $min = $this->getAnnotationParam($params, 'min', 'float');
            $max = $this->getAnnotationParam($params, 'max', 'float');
            $message = $this->getAnnotationParam($params, 'message', 'string');

            if ($this->isBlank($message)) {
                $message = $this->createMessage('range', array('property' => $propertyName, 'min' => $min, 'max' => $max));
            }

            if (is_numeric($propertyValue) && (($min !== null && $propertyValue < $min) || ($max !== null && $propertyValue > $max))) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Parses annotation parameters for a specific annotation from a docblock comment.
     *
     * @param string $annotation The annotation name (without '@').
     * @param string $docComment The docblock comment string.
     * @return array The parsed parameters as an associative array.
     */
    private function parseAnnotationParameters($annotation, $docComment)
    {
        $pattern = sprintf('/@%s(?: vigilance)?\(([^)]*)\)/', preg_quote($annotation, '/'));
        if (preg_match($pattern, $docComment, $matches)) {
            $paramsString = $matches[1];
            $params = [];
            // parse key="value" pairs, supports more than one parameter if present
            preg_match_all('/(\w+)="([^"]*)"/', $paramsString, $paramMatches, PREG_SET_ORDER);
            foreach ($paramMatches as $pm) {
                $params[$pm[1]] = $pm[2];
            }
            return $params;
        }
        return [];
    }


    /**
     * Validates the **`NoHtml`** annotation.
     * Ensures a string does not contain any HTML tags.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the string contains HTML tags.
     */
    private function validateNoHtmlAnnotation($propertyName, $propertyValue, $docComment)
    {
        $params = $this->parseAnnotationParameters('NoHtml', $docComment);
        $message = isset($params['message']) ? $params['message'] : $this->createMessage('noHtml', array('property' => $propertyName));

        if (is_string($propertyValue) && strip_tags($propertyValue) !== $propertyValue) {
            throw new InvalidValueException($propertyName, $message);
        }
    }


    /**
     * Validates the **`Enum`** annotation.
     * Ensures a property value is one of the allowed values.
     * Supports string and numeric values, with optional case-sensitive or case-insensitive matching.
     *
     * @param string $propertyName The name of the property being validated, potentially including parent path.
     * @param mixed $propertyValue The current value of the property.
     * @param string $docComment The docblock comment of the property.
     * @throws InvalidValueException If the value is not found in the allowed values list.
     */
    private function validateEnumAnnotation($propertyName, $propertyValue, $docComment) // NOSONAR
    {
        if (preg_match('/@Enum(?: vigilance)?\(([^)]+)\)/', $docComment, $matches)) {
            $paramsString = $matches[1];

            // Parse key="value", allowedValues={...}, caseSensitive=true|false
            $params = [];

            // Parsing allowedValues separately because the format is {value1,value2,...}
            if (preg_match('/allowedValues=\{([^}]*)\}/', $paramsString, $avMatches)) {
                $allowedValuesRaw = explode(',', $avMatches[1]);
                $allowedValues = array_map(function($v) {
                    return trim($v, ' "');
                }, $allowedValuesRaw);
                $params['allowedValues'] = $allowedValues;
                // Remove the allowedValues part so it is not duplicated when parsing key="value"
                $paramsString = str_replace($avMatches[0], '', $paramsString);
            }

            // Parsing key="value" and caseSensitive=true|false
            preg_match_all('/(\w+)="([^"]*)"|(\w+)=(true|false)/', $paramsString, $paramMatches, PREG_SET_ORDER);
            foreach ($paramMatches as $pm) {
                if (!empty($pm[1])) {
                    $params[$pm[1]] = $pm[2];
                } elseif (!empty($pm[3])) {
                    $params[$pm[3]] = ($pm[4] === 'true');
                }
            }

            $allowedValuesArr = isset($params['allowedValues']) ? $params['allowedValues'] : array();
            $allowedValue = '';
            if (!empty($allowedValuesArr)) {
                $allowedValueParts = array();
                foreach ($allowedValuesArr as $val) {
                    if (is_numeric($val)) {
                        $allowedValueParts[] = $val;
                    } else {
                        $allowedValueParts[] = "'" . $val . "'";
                    }
                }
                $allowedValue = implode(', ', $allowedValueParts);
            }
            $message = isset($params['message']) ? $params['message'] : "";
            if($this->isBlank($message)) {
                $message =  $this->createMessage('enum', array('property' => $propertyName, 'allowedValues' => $allowedValue));
            }
            $allowedValues = $allowedValuesArr;
            $caseSensitive = isset($params['caseSensitive']) ? $params['caseSensitive'] : true;

            $isValid = false;
            if (is_string($propertyValue)) {
                if ($caseSensitive) {
                    $isValid = in_array($propertyValue, $allowedValues, true);
                } else {
                    $lowerPropertyValue = strtolower($propertyValue);
                    foreach ($allowedValues as $allowedValue) {
                        if (strtolower($allowedValue) == $lowerPropertyValue) {
                            $isValid = true;
                            break;
                        }
                        // If numeric, compare numerically
                        if (is_numeric($lowerPropertyValue) && is_numeric($allowedValue) && (float)$lowerPropertyValue === (float)$allowedValue) {
                            $isValid = true;
                            break;
                        }
                    }
                }
            }
            elseif (is_numeric($propertyValue)) {
                // If the property value is numeric, check against numeric allowed values
                $isValid = in_array((float)$propertyValue, array_map('floatval', $allowedValues), true);
            }

            if (!$isValid) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Positive`** annotation.
     * Ensures a numeric value is positive (> 0).
     */
    private function validatePositiveAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Positive(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@Positive(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('positive', array('property' => $propertyName));
            if (is_numeric($propertyValue) && $propertyValue <= 0) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`PositiveOrZero`** annotation.
     * Ensures a numeric value is positive or zero (>= 0).
     */
    private function validatePositiveOrZeroAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@PositiveOrZero(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@PositiveOrZero(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('positiveOrZero', array('property' => $propertyName));
            if (is_numeric($propertyValue) && $propertyValue < 0) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Negative`** annotation.
     * Ensures a numeric value is negative (< 0).
     */
    private function validateNegativeAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Negative(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@Negative(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('negative', array('property' => $propertyName));
            if (is_numeric($propertyValue) && $propertyValue >= 0) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`NegativeOrZero`** annotation.
     * Ensures a numeric value is negative or zero (<= 0).
     */
    private function validateNegativeOrZeroAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@NegativeOrZero(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@NegativeOrZero(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('negativeOrZero', array('property' => $propertyName));
            if (is_numeric($propertyValue) && $propertyValue > 0) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`PastOrPresent`** annotation.
     * Ensures a date/time is in the past or present.
     */
    private function validatePastOrPresentAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@PastOrPresent(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@PastOrPresent(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('pastOrPresent', array('property' => $propertyName));
            $date = $this->convertToDateTime($propertyValue);
            if ($date instanceof DateTimeInterface && $date->getTimestamp() > (new DateTime())->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Url`** annotation.
     * Ensures a string is a valid URL.
     */
    private function validateUrlAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Url(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@Url(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('url', array('property' => $propertyName));
            if (is_string($propertyValue) && !filter_var($propertyValue, FILTER_VALIDATE_URL)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`Ip`** annotation.
     * Ensures a string is a valid IP address.
     */
    private function validateIpAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Ip(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@Ip(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('ip', array('property' => $propertyName));
            if (is_string($propertyValue) && !filter_var($propertyValue, FILTER_VALIDATE_IP)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`DateFormat`** annotation.
     * Ensures a string matches a specific date format.
     */
    private function validateDateFormatAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@DateFormat(?: vigilance)?\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $format = isset($params['format']) ? $params['format'] : 'Y-m-d';
            $message = $this->createMessage('dateFormat', array('property' => $propertyName, 'format' => $format));
            if (is_string($propertyValue)) {
                $dt = DateTime::createFromFormat($format, $propertyValue);
                $errors = DateTime::getLastErrors();
                if (!$dt || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
                    throw new InvalidValueException($propertyName, $message);
                }
            }
        }
    }

    /**
     * Validates the **`Phone`** annotation.
     * Ensures a string is a valid phone number (basic pattern).
     */
    private function validatePhoneAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Phone(?: vigilance)?\(([^)]*)\)/', $docComment, $matches) || preg_match('/@Phone(?: vigilance)?\b/', $docComment)) {
            $message = $this->createMessage('phone', array('property' => $propertyName));
            // Basic phone regex: allows +, numbers, spaces, dashes, parentheses, min 8 digits
            if (is_string($propertyValue) && !preg_match('/^\+?[0-9\s\-\(\)]{8,}$/', $propertyValue)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Checks if a value is blank.
     *
     * @param string $value The value to check.
     * Blank is defined as null, an empty string, or a string containing only whitespace.
     * @return boolean True if the value is blank, false otherwise.
     */
    public function isBlank($value)
    {
        // Check if the value is null, empty string, or contains only whitespace
        return $value === null || trim($value) === '';
    }

    /**
     * Validates the **`Alpha`** annotation.
     * Ensures a string contains only alphabetic characters.
     */
    private function validateAlphaAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@Alpha(?:\(([^)]*)\))?/', $docComment, $matches)) {
            $message = null;
            if (isset($matches[1])) {
                $params = $this->parseAnnotationParams($matches[1]);
                $message = isset($params['message']) ? $params['message'] : null;
            }
            if ($this->isBlank($message)) {
                $message = $this->createMessage('alpha', array('property' => $propertyName));
            }
            if (is_string($propertyValue) && !preg_match('/^[a-zA-Z]+$/', $propertyValue)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`AlphaNumeric`** annotation.
     * Ensures a string contains only alphanumeric characters.
     */
    private function validateAlphaNumericAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@AlphaNumeric(?:\(([^)]*)\))?/', $docComment, $matches)) {
            $message = null;
            if (isset($matches[1])) {
                $params = $this->parseAnnotationParams($matches[1]);
                $message = isset($params['message']) ? $params['message'] : null;
            }
            if ($this->isBlank($message)) {
                $message = $this->createMessage('alphaNumeric', array('property' => $propertyName));
            }
            if (is_string($propertyValue) && !preg_match('/^[a-zA-Z0-9]+$/', $propertyValue)) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`StartsWith`** annotation.
     * Ensures a string starts with a specified prefix.
     * Supports caseSensitive=true|false.
     */
    private function validateStartsWithAnnotation($propertyName, $propertyValue, $docComment) // NOSONAR
    {
        if (preg_match('/@StartsWith\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $prefix = isset($params['prefix']) ? $params['prefix'] : '';
            $caseSensitive = isset($params['caseSensitive']) ? $params['caseSensitive'] : true;
            $message = isset($params['message']) ? $params['message'] : null;
            if ($this->isBlank($message)) {
                $message = $this->createMessage('startsWith', array('property' => $propertyName, 'prefix' => $prefix));
            }
            if (is_string($propertyValue) && $prefix !== '') {
                if ($caseSensitive) {
                    if (strpos($propertyValue, $prefix) !== 0) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                } else {
                    if (stripos($propertyValue, $prefix) !== 0) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                }
            }
        }
    }

    /**
     * Validates the **`EndsWith`** annotation.
     * Ensures a string ends with a specified suffix.
     * Supports caseSensitive=true|false.
     */
    private function validateEndsWithAnnotation($propertyName, $propertyValue, $docComment) // NOSONAR
    {
        if (preg_match('/@EndsWith\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $suffix = isset($params['suffix']) ? $params['suffix'] : '';
            $caseSensitive = isset($params['caseSensitive']) ? $params['caseSensitive'] : true;
            $message = isset($params['message']) ? $params['message'] : null;
            if ($this->isBlank($message)) {
                $message = $this->createMessage('endsWith', array('property' => $propertyName, 'suffix' => $suffix));
            }
            if (is_string($propertyValue) && $suffix !== '') {
                $len = strlen($suffix);
                if ($caseSensitive) {
                    if (substr($propertyValue, -$len) !== $suffix) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                } else {
                    if (strtolower(substr($propertyValue, -$len)) !== strtolower($suffix)) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                }
            }
        }
    }

    /**
     * Validates the **`Contains`** annotation.
     * Ensures a string contains a specified substring.
     * Supports caseSensitive=true|false.
     */
    private function validateContainsAnnotation($propertyName, $propertyValue, $docComment) // NOSONAR
    {
        if (preg_match('/@Contains\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $substring = isset($params['substring']) ? $params['substring'] : '';
            $caseSensitive = isset($params['caseSensitive']) ? $params['caseSensitive'] : true;
            $message = isset($params['message']) ? $params['message'] : null;
            if ($this->isBlank($message)) {
                $message = $this->createMessage('contains', array('property' => $propertyName, 'substring' => $substring));
            }
            if (is_string($propertyValue) && $substring !== '') {
                if ($caseSensitive) {
                    if (strpos($propertyValue, $substring) === false) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                } else {
                    if (stripos($propertyValue, $substring) === false) {
                        throw new InvalidValueException($propertyName, $message);
                    }
                }
            }
        }
    }

    /**
     * Validates the **`BeforeDate`** annotation.
     * Ensures a date is before a specified date.
     */
    private function validateBeforeDateAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@BeforeDate\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $date = isset($params['date']) ? $params['date'] : null;
            $message = isset($params['message']) ? $params['message'] : null;
            if ($this->isBlank($message)) {
                $message = $this->createMessage('beforeDate', array('property' => $propertyName, 'date' => $date));
            }
            $valueDate = $this->convertToDateTime($propertyValue);
            $compareDate = $this->convertToDateTime($date);
            if ($valueDate instanceof DateTimeInterface 
            && $compareDate instanceof DateTimeInterface 
            && $valueDate->getTimestamp() >= $compareDate->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }

    /**
     * Validates the **`AfterDate`** annotation.
     * Ensures a date is after a specified date.
     */
    private function validateAfterDateAnnotation($propertyName, $propertyValue, $docComment)
    {
        if (preg_match('/@AfterDate\(([^)]*)\)/', $docComment, $matches)) {
            $params = $this->parseAnnotationParams($matches[1]);
            $date = isset($params['date']) ? $params['date'] : null;
            $message = isset($params['message']) ? $params['message'] : null;
            if ($this->isBlank($message)) {
                $message = $this->createMessage('afterDate', array('property' => $propertyName, 'date' => $date));
            }
            $valueDate = $this->convertToDateTime($propertyValue);
            $compareDate = $this->convertToDateTime($date);
            if ($valueDate instanceof DateTimeInterface 
            && $compareDate instanceof DateTimeInterface 
            && $valueDate->getTimestamp() <= $compareDate->getTimestamp()) {
                throw new InvalidValueException($propertyName, $message);
            }
        }
    }
}