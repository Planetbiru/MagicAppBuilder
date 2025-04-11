<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

/**
 * Fixes the default label values by replacing them with their corresponding user-friendly labels.
 *
 * This function searches for predefined labels in the input string and replaces them
 * with simplified or user-friendly values based on an internal mapping.
 *
 * @param string $original The original label(s) from the application that need to be fixed.
 * @return string The modified label(s) with the correct values to be displayed to the user.
 */
function fixValue($original)
{
    $labels = array(
        'Message Data Not Found' => 'Data Not Found',
        'Yes' => 'Yes',
        'No' => 'No',
        'Button Save' => 'Save',
        'Button Cancel' => 'Cancel',
        'Button Update' => 'Update',
        'Button Back To List' => 'Back To List',
        'Button Search' => 'Search',
        'Button Add' => 'Add',
        'Button Delete' => 'Delete',
        'Button Save Current Order' => 'Save Current Order',
        'Button Activate' => 'Activate',
        'Button Deactivate' => 'Deactivate',
        'Button Generate' => 'Generate',
        'Button Print' => 'Print',
        'Button Send' => 'Send',
        'Button Send Email' => 'Send Email',
        'Button Approve' => 'Approve',
        'Button Reject' => 'Reject',
        'Button Cancel Approval' => 'Cancel Approval',
        'Button Cancel Approval Tiny' => 'Cancel Approval',
        'Numero' => 'Numero',
        'Warning Delete Confirmation' => 'Delete Confirmation',
        'Label Option Select One' => 'Option Select One',
        'Message Noneditable Data Waiting Approval' => 'Data Waiting Approval',
        'Message Select Filter' => 'Select Filter',
        'Message Waiting For Create' => 'This is new data and is waiting for approval',
        'Message Waiting For Update' => 'This data is waiting for approval before modification',
        'Message Waiting For Delete' => 'This data is waiting for approval before deletion',
        'Message Waiting For Activate' => 'This data is waiting for approval to be activated',
        'Message Waiting For Deactivate' => 'This data is waiting for approval to be deactivated',
        'Message Waiting For Sort Order' => 'This data is waiting for approval to be sorted',  
        'Short Waiting For Create' => 'Create',
        'Short Waiting For Update' => 'Update',
        'Short Waiting For Delete' => 'Delete',
        'Short Waiting For Activate' => 'Activate',
        'Short Waiting For Deactivate' => 'Deactivate',
        'Short Waiting For Sort Order' => 'Sort',
        'Button Show All' => 'Show All',
        'Button Show Waiting Approval' => 'Show Waiting Approval',
        'Button Show Waiting Approval Only' => 'Show Waiting Approval Only',
        'Button Show Require Approval' => 'Show Require Approval',
        'Button Export' => 'Export',
        'Button Approve Tiny' => 'Approve',
        'Button Approve' => 'Approve',
        'Message No Data Require Approval' => 'No Data Require Approval',
        'Option Select One' => 'Select One',
        'Option Select All' => 'Select All',
        'Option Select None' => 'Select None',
    );
    return str_replace(
        array_keys($labels), 
        array_values($labels), 
        $original
    );
}

/**
 * Extracts and converts language keys from a given file path.
 *
 * This function reads the contents of a file, searches for occurrences of `$appLanguage->`,
 * and extracts the language keys by processing the string. The extracted keys are then
 * converted to snake_case format using the `PicoStringUtil::snakeize` method.
 *
 * @param string $path The path to the file from which language keys will be extracted.
 * @return array An array of language keys in snake_case format.
 */
function getKeys($path)
{
    $result = array();
    $content = file_get_contents($path);
    $p2 = 0;
    do {
        $p1 = strpos($content, '$appLanguage->', $p2);
        if($p1 !== false)
        {
            $p2 = strpos($content, '(', $p1);
            $sub = substr($content, $p1 + 17, $p2 - $p1 - 17);
            $sub = PicoStringUtil::snakeize($sub);
            $result[] = $sub;
        }
        else
        {
            $p2 = false;
        }
    }
    while($p1 !== false && $p2 !== false);
    return $result;
}

$inputPost = new InputPost();

if($inputPost->getUserAction() == 'get')
{
    $allKeys = array(
        'short_waiting_for_create',
        'short_waiting_for_update',
        'short_waiting_for_delete',
        'short_waiting_for_activate',
        'short_waiting_for_deactivate',
        'short_waiting_for_sort_order',
        'message_data_not_found',
        'message_waiting_for_create',
        'message_waiting_for_update',
        'message_waiting_for_delete',
        'message_waiting_for_activate',
        'message_waiting_for_deactivate',
        'message_waiting_for_sort_order',
        'yes',
        'no',
        'button_approve',
        'button_reject',
        'button_save',
        'button_cancel',
        'button_update',
        'button_back_to_list',
        'button_search',
        'button_add',
        'button_delete',
        'button_save_current_order',
        'button_activate',
        'button_deactivate',
        'button_generate',
        'button_print',
        'button_send',
        'button_send_email',
        'numero',
        'warning_delete_confirmation',
        'label_option_select_one',
        'message_noneditable_data_waiting_approval',
        'message_select_filter',
        'profile',
        'setting',
        'login',
        'logout',
    );

    $response = array();
    try
    {
        $baseDir = $activeApplication->getBaseApplicationDirectory();
        $targetLanguage = $inputPost->getTargetLanguage();
        $filter = $inputPost->getFilter();

        if($inputPost->countableModules())
        {
            foreach($inputPost->getModules() as $module)
            {
                $module = trim($module);
                $path = $baseDir."/".$module;               
                if(file_exists($path))
                {  
                    $keys = getKeys($path); 
                    $allKeys = array_merge($allKeys, $keys);
                }
            }
        
            $allKeys = array_unique($allKeys);        
            $parsed = array();
            foreach($allKeys as $key)
            {
                $camel = PicoStringUtil::camelize($key);
                $parsed[$camel] = PicoStringUtil::camelToTitle($camel);
            }
            
            $parsedLanguage = new MagicObject($parsed);   
            $pathTrans = $appConfig->getApplication()->getBaseLanguageDirectory()."/$targetLanguage/app.ini";
            $langs = new MagicObject();
            if(file_exists($pathTrans))
            {
                $langs->loadData(PicoIniUtil::parseIniFile($pathTrans));
            }
            
            $keys = array_merge(array_keys($parsed));
            
            if(!$langs->empty())
            {
                foreach($keys as $key)
                {
                    $original = $parsedLanguage->get($key);
                    $original = fixValue($original);
                    $translated = $langs->get($key);
                    if($translated == null)
                    {
                        $translated = $original;
                        $response[] = array(
                            'original' => $original, 
                            'translated' => $translated, 
                            'propertyName' => $key
                        );
                    }  
                    else if($filter == 'all') 
                    {
                        $response[] = array(
                            'original' => $original, 
                            'translated' => $translated, 
                            'propertyName' => $key
                        );
                    }
                }
            }
        }
    }
    catch(Exception $e)
    {
        // do nothing
    }

    ResponseUtil::sendJSON($response);
}

else if($inputPost->getUserAction() == 'set')
{
    $allKeys = array();
    $response = array();
    
    $translated = $inputPost->getTranslated();
    $propertyNames = $inputPost->getPropertyNames();
    $targetLanguage = $inputPost->getTargetLanguage();
    $keys = explode("|", $propertyNames);
    $values = explode("\n", str_replace("\r", "", $translated));
    $keysLength = count($keys);
    
    while(count($values) > $keysLength)
    {
        unset($values[count($values) - 1]);
    }

    $valuesLength = count($values);
    
    while(count($keys) > $valuesLength)
    {
        unset($keys[count($keys) - 1]);
    }
    
    $values = array_map('trim', $values);
    $translatedLabel = array_combine($keys, $values);

    foreach($keys as $i => $key)
    {
        $keys[$i] = PicoStringUtil::snakeize($key);
    }
    
    $baseDir = $activeApplication->getBaseApplicationDirectory();
    $targetLanguage = $inputPost->getTargetLanguage();
    $pathTrans = $appConfig->getApplication()->getBaseLanguageDirectory()."/$targetLanguage/app.ini";
    $dirname = dirname($pathTrans);
    if (!file_exists($dirname)) { 
        mkdir($dirname, 0755, true); 
    }
    if(file_exists($pathTrans))
    {
        $storedTranslatedLabel = PicoIniUtil::parseIniFile($pathTrans);
    }
    if(!isset($storedTranslatedLabel) || !is_array($storedTranslatedLabel))
    {
        $storedTranslatedLabel = array();
    }
    $storedTranslatedLabel = array_merge($storedTranslatedLabel, $translatedLabel);
    $storedTranslatedLabel = PicoArrayUtil::snakeize($storedTranslatedLabel);
    
    PicoIniUtil::writeIniFile($storedTranslatedLabel, $pathTrans);
    ResponseUtil::sendJSON(new stdClass);
}
else
{
    ResponseUtil::sendJSON(new stdClass);
}