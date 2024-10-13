<?php

namespace MagicObject\Database;
use MagicObject\Pagination\PicoPagination;

/**
 * Page control
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPageControl
{
    /**
     * Page data
     *
     * @var PicoPageData
     */
    private $pageData;

    /**
     * Parameter name
     *
     * @var string
     */
    private $parameterName;

    /**
     * Path
     *
     * @var string
     */
    private $path;

    /**
     * Prev
     *
     * @var string
     */
    private $prev;

    /**
     * Next
     *
     * @var string
     */
    private $next;

    /**
     * First
     *
     * @var string
     */
    private $first;

    /**
     * Last
     *
     * @var string
     */
    private $last;

    /**
     * Constructor
     *
     * @param PicoPageData $pageData Page data
     * @param string $parameterName Parameter name for page
     * @param string $path Full path
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
     * Set margin to pagination
     *
     * @param int $margin Margin (previous and next) from current page
     * @return self
     */
    public function setMargin($margin)
    {
        $this->pageData->generatePagination($margin);
        return $this;
    }

    /**
     * Set navigation
     *
     * @param string $prev Button symbol for previous page
     * @param string $next Button symbol for next page
     * @param string $first Button symbol for first page
     * @param string $last Button symbol for last page
     * @return self
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
     * To HTML
     *
     * @return string
     */
    public function toHTML()
    {
        return $this->__toString();
    }

    /**
     * Create HTML
     *
     * @return string
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