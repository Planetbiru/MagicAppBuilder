<?php

namespace MagicApp\XLSX;

use MagicObject\Database\PicoPageData;
use MagicObject\MagicObject;

/**
 * Class DocumentWriter
 *
 * Abstract class for writing documents in different formats (e.g., XLSX, CSV).
 * It provides methods for setting and retrieving application language and for 
 * checking data fetching options. Specific document writing logic should be 
 * implemented in subclasses such as XLSXDocumentWriter and CSVDocumentWriter.
 */
class DocumentWriter
{
    /**
     * Header format
     *
     * @var array
     */
    protected $headerFormat = array();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Check if no data has been fetched
     *
     * @param PicoPageData $pageData Page data
     * @return bool True if no data has been fetched, false otherwise
     */
    protected function noFetchData($pageData)
    {
        return $pageData->getFindOption() & MagicObject::FIND_OPTION_NO_FETCH_DATA;
    }

    /**
     * Create an instance of XLSXDocumentWriter
     *
     * @return XLSXDocumentWriter An instance of XLSXDocumentWriter
     */
    public static function getXLSXDocumentWriter()
    {
        return new XLSXDocumentWriter();
    }

    /**
     * Create an instance of CSVDocumentWriter
     *
     * @return CSVDocumentWriter An instance of CSVDocumentWriter
     */
    public static function getCSVDocumentWriter()
    {
        return new CSVDocumentWriter();
    }
}
