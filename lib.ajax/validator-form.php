<?php

use AppBuilder\Util\Error\ErrorChecker;
use AppBuilder\Util\ValidatorUtil;
use MagicObject\Request\InputPost;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
header("Content-type: text/html"); // Set header for HTML output


if ($inputPost->getValidator() != '')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validatorClassName = $inputPost->getValidator(); 
    
    if (isset($validatorClassName) && !empty($validatorClassName)) {
        $content = $inputPost->getContent(); 
        $path = ValidatorUtil::getPath($appConfig, $inputPost);
        

        if (!file_exists($path)) {
            echo 'Validator file not found at: ' . htmlspecialchars($path);
            exit(); 
        }

        $phpError = ErrorChecker::errorCheck($databaseBuilder, $path, $applicationId);
        $returnVar = intval($phpError->errorCode);
        $errorMessage = implode("\r\n", $phpError->errors);
        $lineNumber = $phpError->lineNumber;
        
        if ($lineNumber == -1 && $returnVar == 0)
        {
            require_once $path;

            $rootNamespace = rtrim(dirname(dirname($appConfig->getApplication()->getBaseEntityDataNamespace())), "\\");
            $fullValidatorClassName = $rootNamespace . "\\Validator\\" . $validatorClassName;

            if (class_exists($fullValidatorClassName)) {
                try {
                    $validatorInstance = new $fullValidatorClassName(); 
                    $values = $validatorInstance->valueArray();
                    $keys = array_keys($values);

                    // --- Start Building HTML using DOMDocument ---
                    $dom = new DOMDocument('1.0', 'UTF-8');
                    $dom->formatOutput = true; 

                    // --- Start building the table here ---
                    $table = $dom->createElement('table');
                    $table->setAttribute('class', 'config-table'); // Bootstrap table classes

                    foreach ($keys as $key) {
                        $label = $key; 
                        $value = $values[$key]; 

                        $tr = $dom->createElement('tr');
                        $table->appendChild($tr);

                        $tdLabel = $dom->createElement('td');
                        $labelElement = $dom->createElement('label', htmlspecialchars($label));
                        $labelElement->setAttribute('for', htmlspecialchars($key));
                        $tdLabel->appendChild($labelElement);
                        $tr->appendChild($tdLabel);

                        $tdInput = $dom->createElement('td');
                        $tr->appendChild($tdInput);

                        $inputType = 'text'; 

                        if (strlen(strval($value)) > 50 || strpos($key, 'description') !== false || strpos($key, 'address') !== false || strpos($key, 'aboutMe') !== false) {
                             $inputField = $dom->createElement('textarea', htmlspecialchars($value));
                             $inputField->setAttribute('name', htmlspecialchars($key));
                             $inputField->setAttribute('rows', '4');
                             $inputField->setAttribute('class', 'form-control'); // Bootstrap class for form controls
                        } else {
                            $inputField = $dom->createElement('input');
                            $inputField->setAttribute('type', $inputType);
                            $inputField->setAttribute('name', htmlspecialchars($key));
                            $inputField->setAttribute('value', htmlspecialchars($value));
                            $inputField->setAttribute('class', 'form-control'); // Bootstrap class for form controls
                        }
                        $tdInput->appendChild($inputField);
                    }

                    $div = $dom->createElement('div');
                    $div->setAttribute('class', 'validator-test-message');
                    $dom->appendChild($div);

                    // Output the generated HTML
                    $dom->appendChild($table);
                    echo $dom->saveHTML();

                } catch (Throwable $e) {
                    echo 'Failed to create validator class instance or build form: ' . htmlspecialchars($e->getMessage());
                }
            } else {
                echo 'Validator class "' . htmlspecialchars($fullValidatorClassName) . '" not found after file loaded.';
            }
        } else {
            echo 'PHP error detected in validator file: ' . htmlspecialchars($errorMessage);
        }
    } else {
        echo 'Validator name cannot be empty.';
    }
} else {
    echo 'Validator parameter not found or is empty. Please select one of validator.';
}

exit();