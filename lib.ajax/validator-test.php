<?php

use AppBuilder\Util\Error\ErrorChecker;
use AppBuilder\Util\ResponseUtil;
use AppBuilder\Util\ValidatorUtil;
use MagicObject\Exceptions\InvalidValueException;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

if ($inputGet->getValidator() != '')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validatorClassName = $inputGet->getValidator(); // Rename variable for clearer representation of the class name
    
    if (isset($validatorClassName) && !empty($validatorClassName)) {
        $content = $inputPost->getContent(); // This content seems unused in this block; ensure it's not needed
        $path = ValidatorUtil::getPath($appConfig, $inputGet);

        // Ensure the validator file exists before attempting to check or load it
        if (!file_exists($path)) {
            ResponseUtil::sendJSON([
                'success' => false,
                'message' => 'Validator file not found at: ' . $path
            ]);
            exit(); // Important to stop execution after JSON response
        }

        $phpError = ErrorChecker::errorCheck($databaseBuilder, $path, $applicationId);
        $returnVar = intval($phpError->errorCode);
        $errorMessage = implode("\r\n", $phpError->errors);
        $lineNumber = $phpError->lineNumber;
        
        // If there are no PHP syntax errors (errorCode -1 or 0 depending on ErrorChecker implementation)
        // And assuming lineNumber -1 means no specific line errors
        if ($lineNumber == -1 && $returnVar == 0) // Add $returnVar == 0 for error code check
        {
            require_once $path;

            // Declare object from class $validatorClassName
            // This is the section you asked to complete

            // 1. Get the Full Namespace of the Validator Class
            // Assumption: If the validator is in 'App\Validator\MyValidator',
            // then $validatorClassName is 'MyValidator'.
            // We need to get the root namespace 'App\'
            $rootNamespace = rtrim(dirname(dirname($appConfig->getApplication()->getBaseEntityDataNamespace())), "\\");
            $fullValidatorClassName = $rootNamespace . "\\Validator\\" . $validatorClassName;

            // 2. Check if the class exists
            if (class_exists($fullValidatorClassName)) {
                try {
                    // Create an instance of the validator class
                    // You might need to pass MagicObject or other data for validation
                    // Example: $dummyData = new MagicObject(['some_property' => 'test']);
                    // $validatorInstance = new $fullValidatorClassName($dummyData); // Depends on your validator's constructor
                    $validatorInstance = new $fullValidatorClassName(); // Basic example if constructor takes no arguments
                    
                    // Load data to validator
                    $validatorInstance->loadData($_POST);

                    // Validate data
                    $validatorInstance->validate();

                    // If you just want to ensure the class can be instantiated:
                    ResponseUtil::sendJSON([
                        'success' => true,
                        'message' => 'Validator class successfully loaded and instantiated.',
                        'className' => $fullValidatorClassName
                    ]);

                } catch (InvalidValueException $e) {
                    // Handle error if there's an issue during class instantiation
                    ResponseUtil::sendJSON([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'propertyName' => $e->getPropertyName()
                    ]);
                }
            } else {
                ResponseUtil::sendJSON([
                    'success' => false,
                    'message' => 'Validator class "' . $fullValidatorClassName . '" not found after file loaded.'
                ]);
            }
        } else {
            // There is a PHP error in the validator file
            ResponseUtil::sendJSON([
                'success' => false,
                'message' => 'PHP error detected in validator file: ' . $errorMessage,
                'lineNumber' => $lineNumber,
                'errorCode' => $returnVar
            ]);
        }
    } else {
        ResponseUtil::sendJSON([
            'success' => false,
            'message' => 'Validator name cannot be empty.'
        ]);
    }
} else {
    ResponseUtil::sendJSON([
        'success' => false,
        'message' => 'Validator parameter not found or is empty.'
    ]);
}

exit(); // Ensure the script stops after sending the JSON response