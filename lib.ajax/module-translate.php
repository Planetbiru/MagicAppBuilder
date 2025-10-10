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
        'Button Activate' => 'Activate',
        'Button Add' => 'Add',
        'Button Approve Tiny' => 'Approve',
        'Button Approval Tiny' => 'Approval',
        'Button Approve' => 'Approve',
        'Button Approval' => 'Approval',
        'Button Reject Tiny' => 'Reject',
        'Button Rejection Tiny' => 'Rejection',
        'Button Rejection' => 'Rejection',
        'Button Reject' => 'Reject',
        'Button Back To List' => 'Back To List',
        'Button Ok' => 'OK',
        'Button Cancel' => 'Cancel',
        'Button Cancel Approval' => 'Cancel Approval',
        'Button Cancel Approval Tiny' => 'Cancel Approval',
        'Button Deactivate' => 'Deactivate',
        'Button Delete' => 'Delete',
        'Button Export' => 'Export',
        'Button Generate Parent' => 'Generate Parent',
        'Button Generate' => 'Generate',
        'Button Print' => 'Print',
        'Button Reply' => 'Reply',
        'Button Unread' => 'Unread',
        'Button Save' => 'Save',
        'Button Save Current Order' => 'Save Current Order',
        'Button Search' => 'Search',
        'Button Send' => 'Send',
        'Button Send Email' => 'Send Email',
        'Button Send Link' => 'Send Link',
        'Button Show' => 'Show',
        'Button Show All' => 'Show All',
        'Button Show Require Approval' => 'Show Require Approval',
        'Button Show Waiting Approval' => 'Show Waiting Approval',
        'Button Show Waiting Approval Only' => 'Show Waiting Approval Only',
        'Button Update' => 'Update',
        'Button Login' => 'Login',
        'Button Restore' => 'Restore',
        'Button Reset Password' => 'Reset Password',
        'Label Option Select One Or Leave It Blank' => 'Select One or Leave It Blank',
        'Label Option Select One' => 'Select One', // NOSONAR
        'Label Option Show All' => 'Show All',
        'Label Option Root Menu' => 'Root Menu',
        'Label Option Show Waiting Approval Only' => 'Show Waiting Approval Only',
        'Label Select All' => 'Select All',
        'Label Select All Items' => 'Select All Items',
        'Label Select Items' => 'Select Items',
        'Label Select None' => 'Select None',
        'Label Select One' => 'Select One', // NOSONAR
        'Label Selected' => 'Selected',
        'Message Data Not Found' => 'Data not found',
        'Message No Data Require Approval' => 'No data require approval',
        'Message Noneditable Data Waiting Approval' => 'Data waiting approval',
        'Message Select Filter' => 'Select filter',
        'Message Waiting For Activate' => 'This data is waiting for approval to be activated',
        'Message Waiting For Create' => 'This is new data and is waiting for approval',
        'Message Waiting For Deactivate' => 'This data is waiting for approval to be deactivated',
        'Message Waiting For Delete' => 'This data is waiting for approval before deletion',
        'Message Waiting For Sort Order' => 'This data is waiting for approval to be sorted',
        'Message Waiting For Update' => 'This data is waiting for approval before modification',
        'Message Password Has Been Used' => 'Password has been used. Please create a new one.',
        'Message Reset Password Failed' => 'Reset password failed',
        'Message Remember Me' => 'Remember me',
        'Message Entity Trash Not Found' => 'Trash Not Found',
        'No' => 'No',
        'Numero' => 'No',
        'Option Select All' => 'Select All',
        'Option Select None' => 'Select None',
        'Option Select One' => 'Select One', // NOSONAR
        'Placeholder Search' => 'Search',
        'Placeholder Search By' => 'Search By',
        'Placeholder Search By Code' => 'Search By Code',
        'Placeholder Search By Name' => 'Search By Name',
        'Placeholder Enter Password' => 'Enter Password',
        'Placeholder Type Password' => 'Type Password',
        'Placeholder Retype Password' => 'Retype Password',
        'Placeholder Enter Username' => 'Enter Username',
        'Placeholder Enter Email' => 'Enter Email',
        'Placeholder Search Menu' => 'Search Menu',
        'Profile' => 'Profile',
        'Setting' => 'Setting',
        'Short Waiting For Activate' => 'Activate',
        'Short Waiting For Create' => 'Create',
        'Short Waiting For Deactivate' => 'Deactivate',
        'Short Waiting For Delete' => 'Delete',
        'Short Waiting For Sort Order' => 'Sort',
        'Short Waiting For Update' => 'Update',
        'Title Delete Confirmation'             => 'Confirm Deletion',
        'Title Delete Permanent Confirmation'   => 'Confirm Permanent Deletion',
        'Title Restore Confirmation'            => 'Confirm Restoration',
        'Title Activate Confirmation'           => 'Confirm Activation',
        'Title Deactivate Confirmation'         => 'Confirm Deactivation',
        'Title Sort Order Confirmation'         => 'Confirm Sort Order Change',
        'Title Approve Confirmation'            => 'Confirm Approval',
        'Title Reject Confirmation'             => 'Confirm Rejection',
        'Warning Delete Confirmation'           => 'Are you sure you want to delete the selected item(s)?',
        'Warning Restore Confirmation'          => 'Are you sure you want to restore the selected item(s)?',
        'Warning Delete Permanent Confirmation' => 'Are you sure you want to permanently delete the selected item(s)?',
        'Warning Activate Confirmation'         => 'Are you sure you want to activate the selected item(s)?',
        'Warning Deactivate Confirmation'       => 'Are you sure you want to deactivate the selected item(s)?',
        'Warning Sort Order Confirmation'       => 'Are you sure you want to save the new sort order?',
        'Warning Approve Confirmation'          => 'Are you sure you want to approve the selected item(s)?',
        'Warning Reject Confirmation'           => 'Are you sure you want to reject the selected item(s)?',
        'Yes' => 'Yes',
        'Session Expired' => 'Session Expired',
        'Button Close' => 'Close',
        'Session Expired Message' => 'Your session has expired. Please log in again.',
        'Invalid Credentials' => 'Invalid username or password.',
        'Label Username' => 'Username',
        'Label Password' => 'Password',        
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
        'all',
        'numero',
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
        'button_approve_tiny',
        'button_approval',
        'button_approval_tiny',
        'button_reject',
        'button_reject_tiny',
        'button_rejection',
        'button_rejection_tiny',
        'button_save',
        'button_cancel',
        'button_update',
        'button_back_to_list',
        'button_search',
        'button_add',
        'button_delete',
        'button_ok',
        'button_save_current_order',
        'button_send_email',
        'button_send_link',
        'button_activate',
        'button_deactivate',
        'button_generate_parent',
        'button_generate',
        'button_print',
        'button_send',
        'button_export',
        'button_show_require_approval',
        'button_show_all',
        'button_show',
        'button_reply',
        'button_unread',
        'button_login',
        'button_reset_password',
        'button_restore',
        'column_name',
        'message_no_data_require_approval',
        'message_noneditable_data_waiting_approval',
        'approval',
        'value_before',
        'value_after',
        'forgot_password',
        'warning_delete_confirmation',
        'label_option_select_one',
        'message_select_filter',
        'message_password_has_been_used',
        'message_reset_password_failed',
        'message_remember_me',
        'profile',
        'setting',
        'index',
        'login',
        'login_form',
        'logout',
        'label_select_one',
        'label_selected',
        'label_select_all',
        'label_select_items',
        'label_select_none',
        'label_select_all_items',
        'placeholder_search',
        'placeholder_search_by',
        'placeholder_search_by_name',
        'placeholder_search_by_code',
        'placeholder_enter_password',
        'placeholder_type_password',
        'placeholder_retype_password',
        'placeholder_enter_username',
        'placeholder_enter_email',
        'placeholder_search_menu',
        'reset_password',
        'username',
        'show_all',
        'title_delete_confitmation',
        'session_expired',
        'close',
        'session_expired_message',
        'password',
        'invalid_credentials',
        'cancel',
        'button_close',
        'session_expired',
        'session_expired_message',
        'invalid_credentials',
        'label_username',
        'label_password',

        'title_deactivate_confirmation',
        'warning_deactivate_confirmation',
        'title_delete_confirmation',
        'title_sort_order_confirmation',
        'warning_sort_order_confirmation'
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