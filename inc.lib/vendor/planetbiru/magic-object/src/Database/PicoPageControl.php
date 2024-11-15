<?php

namespace MagicObject\Database;
use MagicObject\Pagination\PicoPagination;

/**
 * Class PicoPageControl
 *
 * This class manages pagination controls for displaying pages of data. 
 * It generates navigation elements such as previous, next, first, and last 
 * page buttons, allowing users to navigate through pages seamlessly. 
 * The pagination links are generated based on the provided page data and 
 * can be customized with parameter names and paths.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPageControl
{
    /**
     * Page data object containing pagination information.
     *
     * @var PicoPageData
     */
    private $pageData;

    /**
     * Parameter name used for pagination in the URL.
     *
     * @var string
     */
    private $parameterName;

    /**
     * Base path for pagination links.
     *
     * @var string
     */
    private $path;

    /**
     * Symbol for the previous page button.
     *
     * @var string
     */
    private $prev;

    /**
     * Symbol for the next page button.
     *
     * @var string
     */
    private $next;

    /**
     * Symbol for the first page button.
     *
     * @var string
     */
    private $first;

    /**
     * Symbol for the last page button.
     *
     * @var string
     */
    private $last;

    /**
     * Constructor for the PicoPageControl class.
     *
     * Initializes pagination control with page data and optional parameter name and path.
     *
     * @param PicoPageData $pageData Page data object for pagination.
     * @param string $parameterName Parameter name for the page (default is 'page').
     * @param string|null $path Full path for generating pagination links (optional).
     */
    public function __construct($pageData, $parameterName = 'page', $path = null)
    {
        $this->pageData = $pageData;
        if(isset($parameterName))
        {
            $this->parameterName = $parameterName;
        }
        if(isset($path))
        {
            $this->path = $path;
        }
    }

    /**
     * Sets the margin for pagination controls.
     *
     * This defines how many pages to show before and after the current page.
     *
     * @param int $margin Margin (number of pages before and after the current page).
     * @return self Returns the current instance for method chaining.
     */
    public function setMargin($margin)
    {
        $this->pageData->generatePagination($margin);
        return $this;
    }

    /**
     * Sets custom navigation symbols for pagination buttons.
     *
     * @param string|null $prev Button symbol for the previous page (optional).
     * @param string|null $next Button symbol for the next page (optional).
     * @param string|null $first Button symbol for the first page (optional).
     * @param string|null $last Button symbol for the last page (optional).
     * @return self Returns the current instance for method chaining.
     */
    public function setNavigation($prev = null, $next = null, $first = null, $last = null)
    {
        $this->prev = $prev;
        $this->next = $next;
        $this->first = $first;
        $this->last = $last;
        return $this;
    }

    /**
     * Converts the pagination control to HTML format.
     *
     * @return string HTML representation of the pagination controls.
     */
    public function toHTML()
    {
        return $this->__toString();
    }

    /**
     * Generates the HTML for pagination controls.
     *
     * @return string HTML representation of the pagination controls.
     */
    public function __toString()
    {
        $lines = array();
        $format1 = '<span class="page-selector page-selector-number%s" data-page-number="%d"><a href="%s">%s</a></span>';
        $format2 = '<span class="page-selector page-selector-step-one%s" data-page-number="%d"><a href="%s">%s</a></span>';
        $format3 = '<span class="page-selector page-selector-end%s" data-page-number="%d"><a href="%s">%s</a></span>';
        $lastNavPg = 1;

        if(isset($this->first) && $this->pageData->getPageNumber() > 2)
        {
            $lines[] = sprintf($format3, '', 1, PicoPagination::getPageUrl(1, $this->parameterName, $this->path), $this->first);
        }

        if(isset($this->prev) && $this->pageData->getPageNumber() > 1)
        {
            $prevPg = $this->pageData->getPageNumber() - 1;
            $lines[] = sprintf($format2, '', $prevPg, PicoPagination::getPageUrl($prevPg, $this->parameterName, $this->path), $this->prev);
        }

        $i = 0;
        $max = count($this->pageData->getPagination());
        foreach($this->pageData->getPagination() as $pg)
        {
            $lastNavPg = $pg['page'];
            $selected = $pg['selected'] ? ' page-selected' : '';
            if($i == 0)
            {
                $selected = ' page-first'.$selected;
            }
            if($i == ($max - 1))
            {
                $selected = ' page-last'.$selected;
            }
            $lines[] = sprintf($format1, $selected, $lastNavPg, PicoPagination::getPageUrl($lastNavPg, $this->parameterName, $this->path), $lastNavPg);
            $i++;
        }

        if(isset($this->next) && $this->pageData->getPageNumber() < ($this->pageData->getTotalPage()))
        {
            $nextPg = $this->pageData->getPageNumber() + 1;
            $lines[] = sprintf($format2, '', $nextPg, PicoPagination::getPageUrl($nextPg, $this->parameterName, $this->path), $this->next);
        }

        if(isset($this->last) && $this->pageData->getPageNumber() < ($this->pageData->getTotalPage() - 1))
        {
            $lastPg = $this->pageData->getTotalPage();
            $lines[] = sprintf($format3, '', $lastPg, PicoPagination::getPageUrl($lastPg, $this->parameterName, $this->path), $this->last);
        }

        return implode('', $lines);
    }
}