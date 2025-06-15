<?php

namespace AppBuilder;

/**
 * Represents metadata about validator classes used for insert and update operations.
 *
 * This class is typically used to hold the namespace and base class names of validator
 * classes that are generated or used by the application for validation purposes.
 *
 * @package AppBuilder
 */
class AppValidatorInfo
{
    /**
     * The namespace of the validator classes.
     *
     * @var string
     */
    public $namespace = "";

    /**
     * The base name of the class used for insert validation (without namespace).
     *
     * @var string
     */
    public $insertValidationClass = "";

    /**
     * The base name of the class used for update validation (without namespace).
     *
     * @var string
     */
    public $updateValidationClass = "";
}
