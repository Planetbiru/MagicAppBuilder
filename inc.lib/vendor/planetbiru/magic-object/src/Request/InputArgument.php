<?php

namespace MagicObject\Request;

/**
 * Class InputArgument
 *
 * A utility class for handling command-line arguments in PHP CLI applications.
 */
class InputArgument // NOSONAR
{
    /**
     * Constructor for InputArgument.
     */
    public function __construct()
    {
        // You can initialize things here if needed.
    }

    /**
     * Get all command-line arguments.
     *
     * @return array List of CLI arguments, including script name as index 0.
     */
    public function getArguments()
    {
        return $_SERVER['argv'];
    }

    /**
     * Get argument by index.
     *
     * @param int $index Argument index.
     * @return string|null The argument value or null if not set.
     */
    public function getArgument($index)
    {
        $args = $this->getArguments();
        return isset($args[$index]) ? $args[$index] : null;
    }

    /**
     * Get the total number of arguments.
     *
     * @return int Total count of CLI arguments.
     */
    public function getArgumentCount()
    {
        return count($this->getArguments());
    }

    /**
     * Get the argument name (basename of the file) by index.
     *
     * @param int $index Argument index.
     * @return string|null The argument basename or null if not set.
     */
    public function getArgumentName($index)
    {
        $args = $this->getArguments();
        return isset($args[$index]) ? basename($args[$index]) : null;
    }

    /**
     * Get argument value by index.
     *
     * @param int $index Argument index.
     * @return string|null The argument value or null if not set.
     */
    public function getArgumentValue($index)
    {
        return $this->getArgument($index);
    }

    /**
     * Get argument value by name prefix.
     *
     * @param string $name Argument name (without "--").
     * @return string|null The matched value or null if not found.
     */
    public function getArgumentValueByName($name)
    {
        $args = $this->getArguments();
        foreach ($args as $arg) {
            if (strpos($arg, $name) === 0) {
                return substr($arg, strlen($name) + 1);
            }
        }
        return null;
    }

    /**
     * Get argument value by name or return default.
     *
     * @param string $name Argument name.
     * @param mixed $default Default value.
     * @return mixed The value or the default.
     */
    public function getArgumentValueByNameOrDefault($name, $default)
    {
        $value = $this->getArgumentValueByName($name);
        return $value !== null ? $value : $default;
    }

    /** @return int */
    public function getArgumentValueByNameOrDefaultInt($name, $default)
    {
        return (int) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return float */
    public function getArgumentValueByNameOrDefaultFloat($name, $default)
    {
        return (float) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return bool */
    public function getArgumentValueByNameOrDefaultBool($name, $default)
    {
        return (bool) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return string */
    public function getArgumentValueByNameOrDefaultString($name, $default)
    {
        return (string) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return array */
    public function getArgumentValueByNameOrDefaultArray($name, $default)
    {
        return (array) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return object */
    public function getArgumentValueByNameOrDefaultObject($name, $default)
    {
        return (object) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return array */
    public function getArgumentValueByNameOrDefaultArrayObject($name, $default) // NOSONAR
    {
        return (array) $this->getArgumentValueByNameOrDefault($name, $default);
    }

    /** @return array|null */
    public function getArgumentValueByNameOrDefaultJson($name, $default)
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default), true);
    }

    /** @return object|null */
    public function getArgumentValueByNameOrDefaultJsonObject($name, $default)
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default));
    }

    /** @return array|null */
    public function getArgumentValueByNameOrDefaultJsonArray($name, $default) // NOSONAR
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default), true);
    }

    /** @return object|null */
    public function getArgumentValueByNameOrDefaultJsonObjectArray($name, $default) // NOSONAR
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default));
    }

    /** @return array|null */
    public function getArgumentValueByNameOrDefaultJsonArrayObject($name, $default) // NOSONAR
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default), true);
    }

    /** @return object|null */
    public function getArgumentValueByNameOrDefaultJsonObjectArrayObject($name, $default) // NOSONAR
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default));
    }

    /** @return array|null */
    public function getArgumentValueByNameOrDefaultJsonArrayObjectArray($name, $default) // NOSONAR
    {
        return json_decode($this->getArgumentValueByNameOrDefault($name, $default), true);
    }
}
