<?php

namespace AppBuilder\Util;

use MagicObject\Request\PicoRequestBase;

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
}