<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\FindOptionException;
use MagicObject\MagicObject;
use PDO;
use PDOStatement;
use stdClass;

/**
 * Class representing paginated data for database queries.
 *
 * The `PicoPageData` class encapsulates the results of a database query along with pagination details,
 * execution timing, and other metadata. It provides methods to manage and retrieve paginated results
 * effectively, allowing for easy integration into applications that require data manipulation and display.
 *
 * ## Key Features
 * - Encapsulates query results in a paginated format.
 * - Supports execution time tracking for performance monitoring.
 * - Provides easy access to pagination controls and metadata.
 * - Facilitates fetching and processing of data with subquery mapping.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPageData //NOSONAR
{
    const RESULT = 'result';
    const PAGEABLE = 'pageable';

    /**
     * Result data from the query.
     *
     * @var MagicObject[]
     */
    private $result = array();

    /**
     * Pageable object that defines pagination settings.
     *
     * @var PicoPageable
     */
    private $pageable;

    /**
     * Total number of matching results from the query.
     *
     * @var int
     */
    private $totalResult = 0;

    /**
     * Total number of pages based on pagination settings.
     *
     * @var int
     */
    private $totalPage = 0;

    /**
     * Current page number in the pagination context.
     *
     * @var int
     */
    private $pageNumber = 1;

    /**
     * Number of results per page.
     *
     * @var int
     */
    private $pageSize = 0;

    /**
     * Offset for retrieving data in the current pagination context.
     *
     * @var int
     */
    private $dataOffset = 0;

    /**
     * Start time of the query execution.
     *
     * @var float
     */
    private $startTime = 0.0;

    /**
     * End time of the query execution.
     *
     * @var float
     */
    private $endTime = 0.0;

    /**
     * Total execution time for the query in seconds.
     *
     * @var float
     */
    private $executionTime = 0.0;

    /**
     * Array holding pagination details for display.
     *
     * @var array
     */
    private $pagination = array();

    /**
     * PDO statement associated with the query execution.
     *
     * @var PDOStatement
     */
    private $stmt = null;

    /**
     * Class name of the entity being managed.
     *
     * @var string
     */
    private $className;

    /**
     * Mapping information for subqueries.
     *
     * @var array
     */
    private $subqueryMap;

    /**
     * Flag indicating whether the result was derived from a count query.
     *
     * @var bool
     */
    private $byCountResult = false;

    /**
     * Entity associated with the results.
     *
     * @var MagicObject
     */
    private $entity;

    /**
     * Flags for controlling find options in the query.
     *
     * @var int
     */
    private $findOption = 0;

    /**
     * Constructor for the PicoPageData class.
     *
     * Initializes a new instance of the class with the specified parameters.
     *
     * @param MagicObject[]|null $result Array of MagicObject instances or null.
     * @param float $startTime Timestamp when the query was initiated.
     * @param int $totalResult Total count of results, defaults to 0.
     * @param PicoPageable|null $pageable Pageable object for pagination settings.
     * @param PDOStatement|null $stmt PDO statement associated with the query.
     * @param MagicObject|null $entity Entity associated with the query results.
     * @param array|null $subqueryMap Mapping for subquery results.
     */
    public function __construct(
        $result = null,
        $startTime = null,
        $totalResult = 0,
        PicoPageable $pageable = null,
        PDOStatement $stmt = null,
        MagicObject $entity = null,
        $subqueryMap = null
    ) {
        // Set the start time
        if (isset($startTime)) {
            $this->startTime = $startTime;
        } else {
            $this->startTime = time();
        }

        // Initialize result data
        $this->result = $result === null ? [] : $result;

        // Calculate total results
        $this->totalResult = $totalResult === 0 ? $this->countData($this->result) : $totalResult;
        $this->byCountResult = $totalResult === 0;

        // Handle pageable settings
        if ($pageable instanceof PicoPageable) {
            $this->pageable = $pageable;
            $this->calculateContent();
        } else {
            $this->initializeDefaultPagination($this->totalResult);
        }

        // Set execution timing
        $this->endTime = microtime(true);
        $this->executionTime = $this->endTime - $this->startTime;

        // Store additional parameters
        $this->stmt = $stmt;
        $this->entity = $entity;
        $this->className = $entity !== null ? get_class($entity) : null;
        $this->subqueryMap = $subqueryMap !== null ? $subqueryMap : [];
    }

    /**
     * Count the number of items in the result set.
     *
     * @param array $result Result set to count.
     * @return int Count of items in the result.
     */
    private function countData($result)
    {
        return is_array($result) ? count($result) : 0;
    }

    /**
     * Calculate pagination content based on the pageable settings.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function calculateContent()
    {
        // Extract pagination details
        $this->pageNumber = $this->pageable->getPage()->getPageNumber();
        $this->pageSize = $this->pageable->getPage()->getPageSize();
        $this->totalPage = (int) ceil($this->totalResult / $this->pageSize);
        $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;
        $this->generatePagination(3);
        return $this;
    }

    /**
     * Initialize default pagination settings.
     *
     * This method is called when no pageable object is provided.
     *
     * @param int $countResult Total count of results.
     */
    private function initializeDefaultPagination($countResult)
    {
        $this->pageNumber = 1;
        $this->totalPage = 1;
        $this->pageSize = $countResult;
        $this->dataOffset = 0;
    }

    /**
     * Generate pagination details for display.
     *
     * This method constructs an array of pagination controls based on the current page number and total pages.
     *
     * @param int $margin Number of pages to show before and after the current page.
     * @return self Returns the current instance for method chaining.
     */
    public function generatePagination($margin = 3)
    {
        $margin = max(1, $margin);
        $curPage = $this->pageNumber;
        $totalPage = $this->totalPage;

        $minPage = max(1, $curPage - $margin);
        $maxPage = $this->byCountResult ? $totalPage : min($curPage + $margin, $totalPage);

        $this->pagination = array();
        for ($i = $minPage; $i <= $maxPage; $i++) {
            $this->pagination[] = ['page' => $i, 'selected' => $i === $curPage];
        }
        return $this;
    }

    /**
     * Get result data from the query.
     *
     * @return MagicObject[] Array of MagicObject instances.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get the current page number in the pagination context.
     *
     * @return int Current page number.
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Get the total number of pages based on pagination settings.
     *
     * @return int Total page count.
     */
    public function getTotalPage()
    {
        return $this->totalPage;
    }

    /**
     * Get the size of each page (number of results per page).
     *
     * @return int Page size.
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Convert the object to a JSON string representation for debugging.
     *
     * This method is intended for debugging purposes only and provides 
     * a JSON representation of the object's state.
     *
     * @return string The JSON representation of the object.
     */
    public function __toString()
    {
        $obj = new stdClass;
        $exposedProps = [
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
        ];
        
        foreach ($exposedProps as $key) {
            if (property_exists($this, $key)) {
                $obj->{$key} = $this->{$key};
            }
        }

        $obj->findOption = [
            "FIND_OPTION_NO_COUNT_DATA" => $this->findOption & MagicObject::FIND_OPTION_NO_COUNT_DATA,
            "FIND_OPTION_NO_FETCH_DATA" => $this->findOption & MagicObject::FIND_OPTION_NO_FETCH_DATA,
        ];

        return json_encode($obj);
    }

    /**
     * Get the execution time of the query in seconds.
     *
     * @return float Execution time.
     */
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * Get the pagination details for the current query.
     *
     * @return array Pagination details.
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Get the pagination control object for managing page navigation.
     *
     * @param string $parameterName Parameter name for the page.
     * @param string|null $path Optional link path.
     * @return PicoPageControl Pagination control object.
     */
    public function getPageControl($parameterName = 'page', $path = null)
    {
        return new PicoPageControl($this, $parameterName, $path);
    }

    /**
     * Get the total result count from the query.
     *
     * @return int Total result count.
     */
    public function getTotalResult()
    {
        return $this->totalResult;
    }

    /**
     * Get the pageable object associated with this data.
     *
     * @return PicoPageable|null Pageable object or null if not set.
     */
    public function getPageable()
    {
        return $this->pageable;
    }

    /**
     * Get the data offset for the current pagination context.
     *
     * @return int Data offset.
     */
    public function getDataOffset()
    {
        return $this->dataOffset;
    }

    /**
     * Get the PDO statement associated with the query.
     *
     * @return PDOStatement
     * @throws FindOptionException if the statement is null.
     */
    public function getPDOStatement()
    {
        if ($this->stmt === null) {
            throw new FindOptionException("Statement is null. See MagicObject::FIND_OPTION_NO_FETCH_DATA option.");
        }
        return $this->stmt;
    }

    /**
     * Fetch the next row from the result set.
     *
     * @return MagicObject|mixed Next row data as a MagicObject or false on failure.
     * @throws FindOptionException if the statement is null.
     */
    public function fetch()
    {
        if ($this->stmt === null) {
            throw new FindOptionException("Statement is null. See MagicObject::FIND_OPTION_NO_FETCH_DATA option.");
        }
        
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
        return $result !== false ? $this->applySubqueryResult($result) : false;
    }

    /**
     * Apply subquery results to the row data.
     *
     * This method processes the row data and integrates results from subqueries as defined by the mapping.
     *
     * @param array $row Data row from the query result.
     * @return MagicObject Processed MagicObject instance containing the merged data.
     */
    public function applySubqueryResult($row)
    {
        $data = $row;

        if (!empty($this->subqueryMap) && is_array($this->subqueryMap)) {
            foreach ($this->subqueryMap as $info) {
                $objectName = $info['objectName'];
                $objectNameSub = $info['objectName'];

                $data[$objectName] = isset($row[$objectNameSub])
                    ? (new MagicObject())->set($info['primaryKey'], $row[$info['columnName']])->set($info['propertyName'], $row[$objectNameSub])
                    : new MagicObject();
            }
        } else {
            $persist = new PicoDatabasePersistence($this->entity->currentDatabase(), $this->entity);
            $info = $this->entity->tableInfo();
            $data = $persist->fixDataType($row, $info);
            $data = $persist->join($data, $row, $info);
        }

        return new $this->className($data);
    }

    /**
     * Get find option flags indicating query behavior.
     *
     * @return int Find option flags.
     */
    public function getFindOption()
    {
        return $this->findOption;
    }

    /**
     * Set find option flags to control query behavior.
     *
     * @param int $findOption Flags indicating the desired query options.
     * @return self Returns the current instance for method chaining.
     */
    public function setFindOption($findOption)
    {
        $this->findOption = $findOption;
        return $this;
    }
}
