<?php
namespace MagicObject\Pagination;

use MagicObject\Database\PicoSort;
use MagicObject\Exceptions\NullPointerException;
use MagicObject\Util\PicoStringUtil;

/**
 * Pagination
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPagination
{
    /**
     * Current page
     *
     * @var integer
     */
    private $currentPage = 0;

    /**
     * Page size
     *
     * @var integer
     */
    private $pageSize = 0;

    /**
     * Offset
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * Order by
     *
     * @var string
     */
    private $orderBy = null;

    /**
     * Order type
     *
     * @var string
     */
    private $orderType = "";

    /**
     * Constructor
     *
     * @param integer $pageSize Page suze
     * @param string $orderby Order by
     * @param string $ordertype Order type
     */
    public function __construct($pageSize = 20, $orderby = 'orderby', $ordertype = 'ordertype')
    {
        $this->pageSize = $pageSize;
        $this->currentPage = $this->parseCurrentPage();
        $this->offset = $this->pageSize * ($this->currentPage - 1);
        if(isset($_GET[$orderby]))
        {
            $this->orderBy = @$_GET[$orderby];
        }
        if(isset($_GET[$ordertype]))
        {
            $this->orderType = @$_GET[$ordertype];
        }
    }

    /**
     * Parse offset
     *
     * @param string $parameterName Parameter name
     * @return integer
     */
    private function parseCurrentPage($parameterName = 'page')
    {
        if(isset($_GET[$parameterName]))
        {
            $pageStr = preg_replace("/\D/", "", $_GET[$parameterName]);
            if($pageStr == "")
            {
                $page = 0;
            }
            else
            {
                $page = abs(intval($pageStr));
            }
            if($page < 1)
            {
                $page = 1;
            }
            return $page;
        }
        return 1;
    }

    /**
     * Create order
     *
     * @param array $map ORDER BY map
     * @param array $filter ORDER BY filter
     * @param string $defaultOrderBy Default ORDER BY
     * @return string
     */
    public function createOrder($map = null, $filter = null, $defaultOrderBy = null)
    {
        $orderBy = $this->getOrderBy($filter, $defaultOrderBy);
        $orderByFixed = $orderBy;
        // mapping if any
        if($map != null && is_array($map) && isset($map[$orderBy]))
        {
            $orderByFixed = $map[$orderBy];
        }
        if($orderByFixed == null)
        {
            throw new NullPointerException("ORDER BY can not be null");
        }
        return $orderByFixed." ".$this->getOrderType();
    }

    /**
     * Get order by
     *
     * @var array $filter ORDER BY filter
     * @var string $defaultOrderBy Default ORDER BY
     * @return string
     */
    public function getOrderBy($filter = null, $defaultOrderBy = null)
    {
        $orderBy = PicoStringUtil::camelize($this->orderBy);
        if($filter != null && is_array($filter))
        {
            if(isset($filter[$orderBy]))
            {
                $orderBy = $filter[$orderBy];
            }
            else
            {
                $orderBy = null;
            }
        }
        if(($orderBy == null || empty($this->orderBy)) && $defaultOrderBy != null)
        {
            $orderBy = PicoStringUtil::camelize($defaultOrderBy);
        }
        if(empty($orderBy))
        {
            $orderBy = null;
        }
        return $orderBy;
    }

    /**
     * Get order type
     *
     * @var string $defaultOrderType Default order type
     * @return string
     */
    public function getOrderType($defaultOrderType = null)
    {
        $orderType = $this->orderType;
        if(strcasecmp($orderType, PicoSort::ORDER_TYPE_DESC) == 0)
        {
            $orderType = PicoSort::ORDER_TYPE_DESC;
        }
        else if(strcasecmp($orderType, PicoSort::ORDER_TYPE_ASC) == 0)
        {
            $orderType = PicoSort::ORDER_TYPE_ASC;
        }
        else
        {
            $orderType = null;
        }
        if($orderType == null && $defaultOrderType != null)
        {
            $orderType = $defaultOrderType;
        }
        return $orderType;
    }

    /**
     * Get current page
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get page size
     *
     * @return integer
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Get offset
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Get page URL
     *
     * @param integer $page Page number
     * @param string $parameterName Parameter name for page number
     * @param string $path Path
     * @return string
     */
    public static function getPageUrl($page, $parameterName = 'page', $path = null)
    {
        $urls = array();
        $paths = explode("?", $_SERVER['REQUEST_URI']);
        if($path === null)
        {
            $path = trim($paths[0]);
        }
        $urls[] = $path;
        $urlParameters = isset($_GET) ? $_GET : array();
        foreach($urlParameters as $paramName=>$paramValue)
        {
            if($paramName == $parameterName)
            {
                $urlParameters[$paramName] = $page;
            }
        }
        // replace value
        $urlParameters[$parameterName] = $page;
        if(!empty($urlParameters))
        {
            $urls[] = http_build_query($urlParameters);
        }
        return implode("?", $urls);
    }
}
