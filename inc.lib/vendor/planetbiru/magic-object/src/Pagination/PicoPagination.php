<?php
namespace MagicObject\Pagination;

use MagicObject\Database\PicoSort;
use MagicObject\Exceptions\NullPointerException;
use MagicObject\Util\PicoStringUtil;

/**
 * Class for handling pagination functionality.
 * 
 * @author Kamshory
 * @package MagicObject\Pagination
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPagination
{
    /**
     * Current page number.
     *
     * @var integer
     */
    private $currentPage = 0;

    /**
     * Number of items per page.
     *
     * @var integer
     */
    private $pageSize = 0;

    /**
     * Offset for the current page.
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * Column name to order by.
     *
     * @var string|null
     */
    private $orderBy = null;

    /**
     * Type of order (ASC or DESC).
     *
     * @var string
     */
    private $orderType = "";

    /**
     * Constructor for initializing pagination parameters.
     *
     * @param int $pageSize Number of items per page (default is 20).
     * @param string $orderby Name of the parameter used to retrieve the ordering value (default is 'orderby').
     * @param string $ordertype Name of the parameter used to retrieve the order type (ASC or DESC, default is 'ordertype').
     */
    public function __construct($pageSize = 20, $orderby = 'orderby', $ordertype = 'ordertype')
    {
        $this->pageSize = $pageSize;
        $this->currentPage = $this->parseCurrentPage();
        $this->offset = $this->pageSize * ($this->currentPage - 1);
        if (isset($_GET[$orderby])) {
            $this->orderBy = @$_GET[$orderby];
        }
        if (isset($_GET[$ordertype])) {
            $this->orderType = @$_GET[$ordertype];
        }
    }

    /**
     * Parse the current page from the request parameters.
     *
     * @param string $parameterName Name of the parameter used for the page (default is 'page').
     * @return int Current page number, at least 1.
     */
    private function parseCurrentPage($parameterName = 'page')
    {
        if (isset($_GET[$parameterName])) {
            $pageStr = preg_replace("/\D/", "", $_GET[$parameterName]);
            $page = ($pageStr === "") ? 0 : abs(intval($pageStr));
            return max($page, 1); // Ensure page number is at least 1
        }
        return 1; // Default to first page
    }

    /**
     * Create an ORDER BY clause based on the provided parameters.
     *
     * @param array|null $map Mapping of order by columns
     * @param array|null $filter Filter for order by
     * @param string|null $defaultOrderBy Default column name to order by
     * @return string The generated ORDER BY clause
     * @throws NullPointerException if order by is null
     */
    public function createOrder($map = null, $filter = null, $defaultOrderBy = null)
    {
        $orderBy = $this->getOrderBy($filter, $defaultOrderBy);
        $orderByFixed = $orderBy;
        
        // Map if provided
        if ($map !== null && is_array($map) && isset($map[$orderBy])) {
            $orderByFixed = $map[$orderBy];
        }

        if ($orderByFixed === null) {
            throw new NullPointerException("ORDER BY cannot be null");
        }
        
        return $orderByFixed . " " . $this->getOrderType();
    }

    /**
     * Get the order by column name.
     *
     * @param array|null $filter Filter for order by
     * @param string|null $defaultOrderBy Default column name to order by
     * @return string|null The order by column name
     */
    public function getOrderBy($filter = null, $defaultOrderBy = null)
    {
        $orderBy = PicoStringUtil::camelize($this->orderBy);
        if ($filter !== null && is_array($filter)) {
            $orderBy = isset($filter[$orderBy]) ? $filter[$orderBy] : null;
        }
        
        if (($orderBy === null || empty($this->orderBy)) && $defaultOrderBy !== null) {
            $orderBy = PicoStringUtil::camelize($defaultOrderBy);
        }

        return empty($orderBy) ? null : $orderBy;
    }

    /**
     * Get the order type.
     *
     * @param string|null $defaultOrderType Default order type (ASC or DESC)
     * @return string|null The order type
     */
    public function getOrderType($defaultOrderType = null)
    {
        $orderType = $this->orderType;

        if (strcasecmp($orderType, PicoSort::ORDER_TYPE_DESC) === 0) {
            $orderType = PicoSort::ORDER_TYPE_DESC;
        } elseif (strcasecmp($orderType, PicoSort::ORDER_TYPE_ASC) === 0) {
            $orderType = PicoSort::ORDER_TYPE_ASC;
        } else {
            $orderType = null;
        }

        return $orderType === null && $defaultOrderType !== null ? $defaultOrderType : $orderType;
    }

    /**
     * Get the current page number.
     *
     * @return int The current page number
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the page size.
     *
     * @return int The number of items per page
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Get the offset for the current page.
     *
     * @return int The offset for pagination
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Generate a URL for a specific page.
     *
     * @param int $page The page number to generate the URL for
     * @param string $parameterName The name of the parameter for the page number
     * @param string|null $path The base path for the URL
     * @return string The generated URL
     */
    public static function getPageUrl($page, $parameterName = 'page', $path = null)
    {
        $urls = array();
        $paths = explode("?", $_SERVER['REQUEST_URI']);
        $path = $path === null ? trim($paths[0]) : $path;
        $urls[] = $path;

        $urlParameters = isset($_GET) ? $_GET : [];
        $urlParameters[$parameterName] = $page; // Replace the page parameter
        
        // Build the query string
        if (!empty($urlParameters)) {
            $urls[] = http_build_query($urlParameters);
        }

        return implode("?", $urls);
    }
}
