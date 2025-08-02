<?php

namespace MagicApp\AppDto\MocroServices;

use MagicObject\Database\PicoPageable;

/**
 * Data Transfer Object (DTO) for representing pagination and sorting settings.
 *
 * This class extracts relevant pagination information from a {@see PicoPageable}
 * object and transforms it into a simplified structure with page number, page size,
 * and sort criteria.
 */
class PicoListPage extends PicoObjectToString
{
    /**
     * Current page number (0-based index).
     *
     * @var int
     */
    protected $pageNumber;

    /**
     * Number of items per page.
     *
     * @var int
     */
    protected $pageSize;

    /**
     * Array of sort criteria for the result list.
     *
     * @var PicoListSort[]
     */
    protected $sortBy;

    /**
     * Constructs a new PicoListPage from a PicoPageable object.
     *
     * Extracts pagination (page number and size) and sorting metadata
     * and stores it in the current DTO structure.
     *
     * @param PicoPageable $pageable The pageable source object.
     */
    public function __construct($pageable)
    {
        $page = $pageable->getPage();
        $sortable = $pageable->getSortable();

        if (isset($page)) {
            $this->pageNumber = $page->getPageNumber();
            $this->pageSize = $page->getPageSize();
        }

        if (isset($sortable)) {
            $sortable2 = $sortable->getSortable();
            if (isset($sortable2)) {
                $this->sortBy = array();
                foreach ($sortable2 as $sort) {
                    $this->sortBy[] = new PicoListSort($sort);
                }
            }
        }
    }
}
