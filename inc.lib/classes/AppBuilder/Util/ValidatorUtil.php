<?php

namespace AppBuilder\Util;

use MagicObject\Request\PicoRequestBase;
use MagicObject\Util\PicoStringUtil;

class ValidatorUtil
{
    /**
     * Get file path
     *
     * @param SecretObject $appConfig Application config
     * @param PicoRequestBase $inputPost Input post
     * @return string
     */
    public static function getPath($appConfig, $inputPost)
    {
        // Retrieve the base application directory from the configuration
        $baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory()."/inc.lib/classes";
        
        // Build the base path for validators, converting namespace to path
        // Assumption: getBaseEntityDataNamespace() returns a namespace like 'App\Entity\Data'
        // So dirname(dirname(...)) will lead to 'App\'
        // Then it's combined with '\Validator' to become 'App\Validator'
        $baseValidatorNamespace = dirname(dirname($appConfig->getApplication()->getBaseEntityDataNamespace()))."\\Validator";
        $baseValidatorNamespace = str_replace("\\\\", "\\", $baseValidatorNamespace); // Clean up double backslashes
        
        // Combine the base physical directory with the validator namespace converted to a path
        $baseDir = rtrim($baseDirectory, "\\/") . "/" . str_replace("\\", "/", trim($baseValidatorNamespace, "\\/"));
        
        $inputValidator = $inputPost->getValidator();
        $inputValidator = trim($inputValidator);
        
        // Combine baseDir with the validator file name
        return $baseDir."/".$inputValidator.".php";
    }

    /**
     * Parses a PHP validator class and extracts metadata into a structured JSON format.
     *
     * Extracted information includes:
     * - Table name from @Table(name="...")
     * - Class name
     * - Validated properties (public/protected) with their validators and parameters
     *
     * Each validator's parameters are safely parsed, including:
     * - Strings
     * - Braced arrays (e.g., {"en", "id"})
     * - Escaped stringified arrays (e.g., "{\"en\", \"id\"}")
     *
     * @param string $code The PHP code containing the validator class.
     * @return array Result with tableName, className, and properties.
     */
    public static function parseValidatorClass($code) // NOSONAR
    {
        $result = [
            'tableName' => null,
            'className' => null,
            'properties' => []
        ];

        // Extract the table name from the @Table annotation
        if (preg_match('/@Table\s*\(\s*name\s*=\s*"([^"]+)"\s*\)/', $code, $match)) {
            $result['tableName'] = $match[1];
        }

        // Extract the class name
        if (preg_match('/class\s+(\w+)\s+extends/', $code, $match)) {
            $result['className'] = $match[1];
        }

        // Match all properties with docblocks (public or protected visibility)
        $propertyPattern = '/\/\*\*(.*?)\*\/\s+(public|protected)\s+\$(\w+);/s';
        preg_match_all($propertyPattern, $code, $propertyMatches, PREG_SET_ORDER);

        foreach ($propertyMatches as $propertyMatch) {
            $docBlock = $propertyMatch[1];
            $propertyName = $propertyMatch[3];
            $validators = [];

            // Match all annotation lines, e.g., @NotBlank(...), @Length(...)
            preg_match_all('/@(\w+)\((.*?)\)/s', $docBlock, $validatorMatches, PREG_SET_ORDER);

            foreach ($validatorMatches as $validatorMatch) {
                $validatorName = $validatorMatch[1];
                $rawParams = $validatorMatch[2];
                $attributes = [];

                // Split raw parameters by comma, respecting quotes and nested braces
                $params = [];
                $length = strlen($rawParams);
                $buffer = '';
                $depth = 0;
                $inQuotes = false;

                for ($i = 0; $i < $length; $i++) {
                    $char = $rawParams[$i];
                    if ($char === '"' && ($i === 0 || $rawParams[$i - 1] !== '\\')) {
                        $inQuotes = !$inQuotes;
                    } elseif (!$inQuotes) {
                        if ($char === '{') {
                            $depth++;
                        } elseif ($char === '}') {
                            $depth--;
                        } elseif ($char === ',' && $depth === 0) {
                            $params[] = trim($buffer);
                            $buffer = '';
                            continue;
                        }
                    }
                    $buffer .= $char;
                }

                if (trim($buffer) !== '') {
                    $params[] = trim($buffer);
                }

                // Parse each key=value pair safely
                foreach ($params as $param) {
                    if (preg_match('/(\w+)\s*=\s*(.+)/', $param, $pm)) {
                        $key = $pm[1];
                        $val = trim($pm[2]);

                        // Quoted string
                        if (preg_match('/^"(.*)"$/s', $val, $vm)) {
                            $val = stripslashes($vm[1]);

                            // Handle string that looks like an array
                            if (preg_match('/^\{.*\}$/s', $val)) {
                                $val = trim($val, '{}');
                                $items = array_map('trim', explode(',', $val));
                                $items = array_map(fn($v) => trim($v, '"\''), $items);
                                $attributes[$key] = $items;
                            } else {
                                $attributes[$key] = $val;
                            }

                        } elseif (preg_match('/^\{.*\}$/s', $val)) {
                            // Unquoted array syntax
                            $val = trim($val, '{}');
                            $items = array_map('trim', explode(',', $val));
                            $items = array_map(fn($v) => trim($v, '"\''), $items);
                            $attributes[$key] = $items;

                        } else {
                            // Raw fallback
                            $attributes[$key] = trim($val, '"\'');
                        }
                    }
                }

                if ($validatorName !== 'Table') {
                    $val = [
                        'type' => $validatorName
                    ];
                    // Manipulate for Validation Editor
                    foreach($attributes as $k=>$v)
                    {
                        if($k == 'allowedValues')
                        {
                            $attributes[$k] = '{"'.implode('", "', $v).'"}';
                        }
                    }

                    $val = array_merge($val, $attributes);
                    $validators[] = $val;
                }
            }

            $result['properties'][PicoStringUtil::snakeize($propertyName)] = $validators;
        }

        return $result;
    }
}