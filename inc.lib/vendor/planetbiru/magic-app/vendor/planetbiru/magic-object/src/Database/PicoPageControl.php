<?php

namespace MagicObject\Database;
use MagicObject\Pagination\PicoPagination;

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
     * @param PicoPageData $pagination
     * @param string $parameterName
     * @param string $path
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
     * Set navigation
     *
     * @param string $prev
     * @param string $next
     * @param string $first
     * @param string $last
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

        $pn = '';
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