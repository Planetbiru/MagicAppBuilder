<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\FindOptionException;
use MagicObject\MagicObject;
use PDO;
use PDOStatement;
use stdClass;

class PicoPageData
{
    const RESULT = 'result';
    const PAGABLE = 'pagable';

    /**
     * Result
     *
     * @var MagicObject[]
     */
    private $result = array();

    /**
     * Pagable
     *
     * @var PicoPageable
     */
    private $pageable;

    /**
     * Total match
     *
     * @var integer
     */
    private $totalResult = 0;

    /**
     * Total page
     *
     * @var integer
     */
    private $totalPage = 0;
    /**
     * Page number
     * @var integer
     */
    private $pageNumber = 0;

    /**
     * Page size
     * @var integer
     */
    private $pageSize = 0;

    /**
     * Data offset
     *
     * @var integer
     */
    private $dataOffset = 0;
    
    /**
     * Start time
     *
     * @var float
     */
    private $startTime = 0.0;
    
    /**
     * End time
     *
     * @var float
     */
    private $endTime = 0.0;
    
    /**
     * Execution time
     *
     * @var float
     */
    private $executionTime = 0.0;

    /**
     * Pagination
     *
     * @var array
     */
    private $pagination = array();

    /**
     * PDO statement
     *
     * @var PDOStatement
     */
    private $stmt = null;
    
    /**
     * Class name
     *
     * @var string
     */
    private $className;
    
    /**
     * Subquery info
     *
     * @var array
     */
    private $subqueryMap;
    
    /**
     * By count result
     *
     * @var boolean
     */
    private $byCountResult = false;
    
    /**
     * Entity
     *
     * @var MagicObject
     */
    private $entity;

    /**
     * Constructor
     *
     * @param MagicObject[] $result
     * @param integer $startTime
     * @param integer $totalResult
     * @param PicoPageable $pageable
     */
    public function __construct($result, $startTime, $totalResult = 0, $pageable = null, $stmt = null, $entity = null, $subqueryMap = null)
    {
        $this->startTime = $startTime;
        $this->result = $result;
        $countResult = $this->countData($result);
        if($totalResult != 0)
        {
            $this->totalResult = $totalResult;
        }
        else
        {
            $this->byCountResult = true;
            $this->totalResult = $countResult;
        }
        if($pageable != null && $pageable instanceof PicoPageable)
        {
            $this->pageable = $pageable;
            $this->calculateContent();
        }
        else
        {
            $this->pageNumber = 1;
            $this->totalPage = 1;
            $this->pageSize = $countResult;
            $this->dataOffset = 0;
        }
        $this->endTime = microtime(true);
        $this->executionTime = $this->endTime - $this->startTime;
        if($stmt != null)
        {
            $this->stmt = $stmt;
        }
        if($entity != null)
        {
            $this->entity = $entity;
            $this->className = get_class($entity);
        }
        if($subqueryMap != null)
        {
            $this->subqueryMap = $subqueryMap;
        }
    }
    
    /**
     * Count data
     *
     * @param array $result
     * @return integer
     */
    private function countData($result)
    {
        if(isset($result) && is_array($result))
        {
            return count($result);
        }
        return 0;
    }


    /**
     * Calculate content
     *
     * @return void
     */
    private function calculateContent()
    {
        $this->pageNumber = $this->pageable->getPage()->getPageNumber();
        $this->totalPage = ceil($this->totalResult / $this->pageable->getPage()->getPageSize());
        
        $this->pageSize = $this->pageable->getPage()->getPageSize();
        $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;

        $curPage = $this->pageNumber;
        $totalPage = $this->totalPage;

        $minPage = $curPage - 3;
        if($minPage < 1)
        {
            $minPage = 1;
        }
        $maxPage = $curPage + 3;
        if(!$this->byCountResult && $maxPage > $totalPage)
        {
            $maxPage = $totalPage;
        }
        $this->pagination = array();
        for($i = $minPage; $i <= $maxPage; $i++)
        {
            $this->pagination[] = array('page'=>$i, 'selected'=>$i == $curPage);
        }
    }

    /**
     * Get result
     *
     * @return MagicObject[]
     */ 
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get page number
     *
     * @return integer
     */ 
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Get total page
     *
     * @return integer
     */ 
    public function getTotalPage()
    {
        return $this->totalPage;
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
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        $obj = new stdClass;
        $exposedProps = array(
            "pageable",
            "totalResult",
            "totalPage",
            "pageNumber",
            "pageSize", 
            "dataOffset",
            "startTime",
            "endTime",
            "executionTime",
            "pagination"
        );
        foreach($this as $key=>$value)
        {
            if($key != self::RESULT && $key != self::PAGABLE && in_array($key, $exposedProps))
            {
                $obj->{$key} = $value;
            }
        }
        return json_encode($obj);
    }

    /**
     * Get execution time
     *
     * @return float
     */ 
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * Get the value of pagination
     */ 
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Get page control
     *
     * @param string $parameterName
     * @param string $path
     * @return PicoPageControl
     */
    public function getPageControl($parameterName = 'page', $path = null)
    {
        return new PicoPageControl($this, $parameterName, $path);
    }

    /**
     * Get total match
     *
     * @return integer
     */ 
    public function getTotalResult()
    {
        return $this->totalResult;
    }

    /**
     * Get pagable
     *
     * @return PicoPageable
     */ 
    public function getPagable()
    {
        return $this->pageable;
    }

    /**
     * Get data offset
     *
     * @return  integer
     */ 
    public function getDataOffset()
    {
        return $this->dataOffset;
    }

    /**
     * Get PDO statement
     *
     * @return  PDOStatement
     */ 
    public function getPDOStatement()
    {
        if($this->stmt == null)
        {
            throw new FindOptionException("Statement is null. See MagicObject::FIND_OPTION_NO_FETCH_DATA option");
        }
        return $this->stmt;
    }
    
    /**
     * Fetch data
     *
     * @return MagicObject|mixed
     */
    public function fetch()
    {
        if($this->stmt == null)
        {
            throw new FindOptionException("Statement is null. See MagicObject::FIND_OPTION_NO_FETCH_DATA option");
        }
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
        if($result === false)
        {
            return false;
        }
        return $this->applySubqueryResult($result);
    }
    
    /**
     * Apply subquery result
     *
     * @param array $row
     * @return MagicObject
     */
    public function applySubqueryResult($row)
    {
        $data = $row;
        if(isset($this->subqueryMap) && is_array($this->subqueryMap))
        { 
            foreach($this->subqueryMap as $info)
            {
                $objectName = $info['objectName'];
                $objectNameSub = $info['objectName'];
                if(isset($row[$objectNameSub]))
                {
                    $data[$objectName] = (new MagicObject())
                        ->set($info['primaryKey'], $row[$info['columnName']])
                        ->set($info['propertyName'], $row[$objectNameSub])
                    ;
                }
                else
                {
                    $data[$objectName] = new MagicObject();
                }
            }
        }
        else
        {
            $persist = new PicoDatabasePersistence($this->entity->currentDatabase(), $this->entity);
            $info = $this->entity->tableInfo();
            $data = $persist->fixDataType($row, $info);
            $data = $persist->join($data, $row, $info);
        }
        return new $this->className($data);
    }
}