<?php

use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$_POST = [
    'name' => 'John Doe Sebastian',
    'email' => 'any@any.any',
    'age' => '41',
    'gender' => 'Male'
];


class RegistrationForm extends InputPost
{
    /**
     * Undocumented variable
     *
     * @Required
     * @Size(min=15, max=50)
     * @var string
     */
    protected $name;

    /**
     * Undocumented variable
     *
     * @Email
     * @var string
     */
    protected $email;

    /**
     * Undocumented variable
     *
     * @Range(min=22.6 max=80.5)
     * @var float
     */
    protected $age;

    /**
     * Undocumented variable
     *
     * @Enum(allowedValues={"male", "female", "other"} message="Gender must be 'male', 'female', or 'other'")
     * @var string
     */
    protected $gender;
}

$inputPost = new RegistrationForm();

$name = $inputPost->getName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, true, true);

try
{
    $inputPost->validate();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

