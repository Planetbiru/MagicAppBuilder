<?php

namespace MagicApp\XLSX;

use MagicApp\AppLanguage;
use MagicObject\Database\PicoPageData;
use MagicObject\MagicObject;

class DocumentWriter
{
    /**
     * Header format
     * @var array
     */
    protected $headerFormat = array();

    /**
     * Application language
     * @var AppLanguage
     */
    protected $appLanguage;

    /**
     * App languange
     * @param AppLanguage $appLanguage
     */
    public function __construct($appLanguage)
    {
        $this->appLanguage = $appLanguage;
    }
    
    /**
     * Check if never fetch data
     *
     * @param PicoPageData $pageData Page data
     * @return boolean
     */
    protected function noFetchData($pageData)
    {
        return $pageData->getFindOption() & MagicObject::FIND_OPTION_NO_FETCH_DATA;
    }

    /**
     * Get app language
     *
     * @return AppLanguage
     */ 
    public function getAppLanguage()
    {
        return $this->appLanguage;
    }

    /**
     * Set app language
     *
     * @param AppLanguage  $appLanguage  Application language
     *
     * @return self
     */ 
    public function setAppLanguage($appLanguage)
    {
        $this->appLanguage = $appLanguage;

        return $this;
    }

    /**
     * Application language
     * @param AppLanguage $appLanguage Application language
     * @return XLSXDocumentWriter
     */
    public static function getXLSXDocumentWriter($appLanguage)
    {
        return new XLSXDocumentWriter($appLanguage);
    }

    /**
     * Application language
     * @param AppLanguage $appLanguage
     * @return CSVDocumentWriter
     */
    public static function getCSVDocumentWriter($appLanguage)
    {
        return new CSVDocumentWriter($appLanguage);
    }
}