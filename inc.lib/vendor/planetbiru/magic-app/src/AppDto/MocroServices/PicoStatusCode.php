<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoStatusCode
 *
 * Provides standard response messages and constants based on response codes.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoStatusCode
{
    // Success & General Codes
    const SUCCESS                = '000';
    const FAILURE                = '999'; // General failure
    const GENERAL_ERROR          = '001';
    const UNKNOWN_ERROR          = '098';

    // Request & Validation Errors
    const INVALID_REQUEST        = '002';
    const VALIDATION_FAILED      = '006';
    const MISSING_REQUIRED_FIELD = '011';
    const INVALID_FORMAT         = '012';
    const INVALID_PARAMETER      = '013';
    const UNSUPPORTED_OPERATION  = '014';

    // Auth & Access
    const AUTHENTICATION_FAILED  = '003';
    const ACCESS_DENIED          = '004';
    const TOKEN_EXPIRED          = '015';
    const TOKEN_INVALID          = '016';

    // Resource Errors
    const DATA_NOT_FOUND         = '005';
    const DUPLICATE_DATA         = '017';
    const CONFLICT               = '009';

    // System/Server Errors
    const DATABASE_ERROR         = '007';
    const TIMEOUT                = '008';
    const SERVICE_UNAVAILABLE    = '010';
    const DEPENDENCY_FAILURE     = '018';

    /**
     * List of standard response messages mapped by response code.
     *
     * @var array
     */
    protected static $messages = array(
        self::SUCCESS               => 'Success',
        self::FAILURE               => 'Failure',
        self::GENERAL_ERROR         => 'General Error',
        self::UNKNOWN_ERROR         => 'Unknown Error',
        self::INVALID_REQUEST       => 'Invalid Request',
        self::VALIDATION_FAILED     => 'Validation Failed',
        self::MISSING_REQUIRED_FIELD => 'Missing Required Field',
        self::INVALID_FORMAT        => 'Invalid Format',
        self::INVALID_PARAMETER     => 'Invalid Parameter',
        self::UNSUPPORTED_OPERATION => 'Unsupported Operation',
        self::AUTHENTICATION_FAILED => 'Authentication Failed',
        self::ACCESS_DENIED         => 'Access Denied',
        self::TOKEN_EXPIRED         => 'Token Expired',
        self::TOKEN_INVALID         => 'Token Invalid',
        self::DATA_NOT_FOUND        => 'Data Not Found',
        self::DUPLICATE_DATA        => 'Duplicate Data',
        self::CONFLICT              => 'Conflict',
        self::DATABASE_ERROR        => 'Database Error',
        self::TIMEOUT               => 'Timeout',
        self::SERVICE_UNAVAILABLE   => 'Service Unavailable',
        self::DEPENDENCY_FAILURE    => 'Dependency Failure'
    );

    /**
     * Get the response message for a given response code.
     *
     * @param string $code Response code to look up.
     * @return string The corresponding response message, or the code itself if unknown.
     */
    public static function getMessage($code)
    {
        if (isset(self::$messages[$code])) {
            return self::$messages[$code];
        }
        return $code;
    }
}
