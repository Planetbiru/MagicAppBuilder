<?php

namespace MagicObject\Database;
use MagicObject\Pagination\PicoPagination;
use MagicObject\SecretObject;

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
     * Template for rendering a specific page number in pagination.
     *
     * @var string
     */
    private $formatPageNumber = '<span class="page-selector page-selector-number%s" data-page-number="%d"><a href="%s">%s</a></span>';

    /**
     * Template for rendering navigation buttons like "next" or "prev" in pagination.
     *
     * @var string
     */
    private $formatStepOne = '<span class="page-selector page-selector-step-one%s" data-page-number="%d"><a href="%s">%s</a></span>';

    /**
     * Template for rendering navigation buttons like "first" or "last" in pagination.
     *
     * @var string
     */
    private $formatStartEnd = '<span class="page-selector page-selector-end%s" data-page-number="%d"><a href="%s">%s</a></span>';

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
        if($this->isNotEmpty($parameterName))
        {
            $this->parameterName = $parameterName;
        }
        if($this->isNotEmpty($path))
        {
            $this->path = $path;
        }
        $this->prev = '<i class="fa-solid fa-angle-left"></i>';
        $this->next = '<i class="fa-solid fa-angle-right"></i>';
        $this->first = '<i class="fa-solid fa-angles-left"></i>';
        $this->last = '<i class="fa-solid fa-angles-right"></i>';  
    }

    /**
     * Checks if a value is set and not empty.
     *
     * @param mixed $value The value to check.
     * @return bool Returns `true` if the value is set and not empty, otherwise `false`.
     */
    private function isNotEmpty($value)
    {
        return isset($value) && !empty($value);
    }

    /**
     * Sets custom navigation symbols for pagination buttons.
     *
     * This method allows you to define custom symbols for navigation buttons,
     * including the previous, next, first, and last page buttons. Only non-null
     * values will be assigned to their respective properties.
     *
     * @param string|null $prev The symbol to display for the "previous" page button (optional).
     * @param string|null $next The symbol to display for the "next" page button (optional).
     * @param string|null $first The symbol to display for the "first" page button (optional).
     * @param string|null $last The symbol to display for the "last" page button (optional).
     * @return self Returns the current instance for method chaining.
     */
    public function setNavigation($prev = null, $next = null, $first = null, $last = null)
    {
        if ($this->isNotEmpty($prev)) {
            $this->prev = $prev;
        }
        if ($this->isNotEmpty($next)) {
            $this->next = $next;
        }
        if ($this->isNotEmpty($first)) {
            $this->first = $first;
        }
        if ($this->isNotEmpty($last)) {
            $this->last = $last;
        }
        return $this;
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
        if(isset($margin) && $margin > 0)
        {
            $this->pageData->generatePagination($margin);
        }
        return $this;
    }

    /**
     * Sets the range for pagination controls.
     *
     * This defines how many pages to show before and after the current page.
     *
     * @param int $range Range (number of pages before and after the current page).
     * @return self Returns the current instance for method chaining.
     */
    public function setRange($range)
    {
        if(isset($range) && $range > 0)
        {
            $this->pageData->generatePagination($range);
        }
        return $this;
    }

    /**
     * Gets the template for rendering specific page numbers.
     *
     * @return string The current format for rendering page numbers.
     */
    public function getFormatPageNumber()
    {
        return $this->formatPageNumber;
    }

    /**
     * Sets the template for rendering specific page numbers.
     *
     * This format is used to generate the HTML for individual page numbers in the pagination.
     * It includes a span with the `page-selector-number` class and a link (`<a>`) to the page.
     * 
     * Placeholders:
     * - `%s`: Additional CSS classes, e.g., `page-selected`.
     * - `%d`: Page number for the `data-page-number` attribute.
     * - `%s`: URL for the link (`href` attribute).
     * - `%s`: Text content of the link (usually the page number).
     *
     * **Example:**
     * ```html
     * <span class="page-selector page-selector-number%s" data-page-number="%d"><a href="%s">%s</a></span>
     * ```
     * will be
     * ```html
     * <span class="page-selector page-selector-number page-selected" data-page-number="3">
     *   <a href="/path?page=3">3</a>
     * </span>
     * ```
     * 
     * @param string $formatPageNumber The new format for rendering page numbers.
     * @return self Returns the current instance for method chaining.
     */
    public function setFormatPageNumber($formatPageNumber)
    {
        if(isset($formatPageNumber) && !empty($formatPageNumber))
        {
            $this->formatPageNumber = $formatPageNumber;
        }
        return $this;
    }

    /**
     * Gets the template for rendering step navigation buttons.
     *
     * @return string The current format for step navigation buttons.
     */
    public function getFormatStepOne()
    {
        return $this->formatStepOne;
    }

    /**
     * Sets the template for rendering step navigation buttons.
     *
     * This format generates the HTML for step navigation buttons, such as "previous" or "next."
     * It includes a span with the `page-selector-step-one` class and a link (`<a>`) to the target page.
     * 
     * Placeholders:
     * - `%s`: Additional CSS classes (e.g., active state).
     * - `%d`: Page number for the `data-page-number` attribute.
     * - `%s`: URL for the link (`href` attribute).
     * - `%s`: Symbol or text for the button (e.g., "Next" or "Prev").
     *
     * **Example:**
     * ```html
     * <span class="page-selector page-selector-step-one%s" data-page-number="%d"><a href="%s">%s</a></span>
     * ```
     * will be
     * ```html
     * <span class="page-selector page-selector-step-one" data-page-number="4">
     *   <a href="/path?page=4">Next</a>
     * </span>
     * ```
     *
     * @param string $formatStepOne The new format for step navigation buttons.
     * @return self Returns the current instance for method chaining.
     */
    public function setFormatStepOne($formatStepOne)
    {
        if(isset($formatStepOne) && !empty($formatStepOne))
        {
            $this->formatStepOne = $formatStepOne;
        }
        return $this;
    }

    /**
     * Gets the template for rendering end navigation buttons.
     *
     * @return string The current format for end navigation buttons.
     */
    public function getFormatStartEnd()
    {
        return $this->formatStartEnd;
    }

    /**
     * Sets the template for rendering end navigation buttons.
     *
     *
     * This format generates the HTML for step navigation buttons, such as "previous" or "next."
     * It includes a span with the `page-selector-step-one` class and a link (`<a>`) to the target page.
     * 
     * Placeholders:
     * - `%s`: Additional CSS classes (e.g., active state).
     * - `%d`: Page number for the `data-page-number` attribute.
     * - `%s`: URL for the link (`href` attribute).
     * - `%s`: Symbol or text for the button (e.g., "Start" or "End").
     *
     * **Example:**
     * ```html
     * <span class="page-selector page-selector-end%s" data-page-number="%d"><a href="%s">%s</a></span>
     * ```
     * will be
     * ```html
     * <span class="page-selector page-selector-end" data-page-number="4">
     *   <a href="/path?page=4">End</a>
     * </span>
     * ```
     *
     * @param string $formatStartEnd The new format for end navigation buttons.
     * @return self Returns the current instance for method chaining.
     */
    public function setFormatStartEnd($formatStartEnd)
    {
        if(isset($formatStartEnd) && !empty($formatStartEnd))
        {
            $this->formatStartEnd = $formatStartEnd;
        }
        return $this;
    }

    /**
     * Sets the button format templates for pagination controls.
     *
     * This method allows you to set custom templates for page numbers, step buttons 
     * and start/end buttons.
     *
     * @param string $pageNumberFormat The format template for rendering page numbers.
     * 
     * **Example:**
     * ```html
     * <span class="page-selector page-selector-number%s" data-page-number="%d"><a href="%s">%s</a></span>
     * ```
     * @param string $stepOneFormat The format template for rendering step buttons.
     * 
     * **Example:**
     * ```html
     * <span class="page-selector page-selector-step-one%s" data-page-number="%d"><a href="%s">%s</a></span>
     * ```
     * @param string $startEndFormat The format template for rendering start and end buttons.
     * 
     * **Example:**
     * ```html
     * <span class="page-selector page-selector-end%s" data-page-number="%d"><a href="%s">%s</a></span>
     * ```
     * @return self Returns the current instance for method chaining.
     */
    public function setButtonFormat($pageNumberFormat, $stepOneFormat, $startEndFormat)
    {
        $this->formatPageNumber = $pageNumberFormat;
        $this->formatStepOne = $stepOneFormat;
        $this->formatStartEnd = $startEndFormat;
        return $this;
    }

    /**
     * Applies the pagination configuration to the current instance.
     *
     * This method accepts a configuration object, typically sourced from a Yaml file, 
     * and applies its settings to the pagination control. The object contains the following properties:
     *
     * @param SecretObject $paginationConfig The configuration object containing pagination settings.
     *   - `button_prev`: Symbol for the "previous" button.
     *   - `button_next`: Symbol for the "next" button.
     *   - `button_first`: Symbol for the "first" button.
     *   - `button_last`: Symbol for the "last" button.
     *   - `template_page_number`: Format for page number buttons.
     *   - `template_step_one`: Format for step-one navigation buttons.
     *   - `template_start_end`: Format for start/end navigation buttons.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setPaginationConfig($paginationConfig)
    {
        return $this
            ->setNavigation(
                $paginationConfig->getButtonPrev(),
                $paginationConfig->getButtonNext(),
                $paginationConfig->getButtonFirst(),
                $paginationConfig->getButtonLast()
            )
            ->setButtonFormat(
                $paginationConfig->getTemplatePageNumber(),
                $paginationConfig->getTemplateStepOne(),
                $paginationConfig->getTemplateStartEnd()
            );
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

        $lastNavPg = 1;

        if(isset($this->first) && $this->pageData->getPageNumber() > 2)
        {
            $lines[] = sprintf($this->formatStartEnd, '', 1, PicoPagination::getPageUrl(1, $this->parameterName, $this->path), $this->first);
        }

        if(isset($this->prev) && $this->pageData->getPageNumber() > 1)
        {
            $prevPg = $this->pageData->getPageNumber() - 1;
            $lines[] = sprintf($this->formatStepOne, '', $prevPg, htmlspecialchars(PicoPagination::getPageUrl($prevPg, $this->parameterName, $this->path)), $this->prev);
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
            $lines[] = sprintf($this->formatPageNumber, $selected, $lastNavPg, htmlspecialchars(PicoPagination::getPageUrl($lastNavPg, $this->parameterName, $this->path)), $lastNavPg);
            $i++;
        }

        if(isset($this->next) && $this->pageData->getPageNumber() < ($this->pageData->getTotalPage()))
        {
            $nextPg = $this->pageData->getPageNumber() + 1;
            $lines[] = sprintf($this->formatStepOne, '', $nextPg, htmlspecialchars(PicoPagination::getPageUrl($nextPg, $this->parameterName, $this->path)), $this->next);
        }

        if(isset($this->last) && $this->pageData->getPageNumber() < ($this->pageData->getTotalPage() - 1))
        {
            $lastPg = $this->pageData->getTotalPage();
            $lines[] = sprintf($this->formatStartEnd, '', $lastPg, htmlspecialchars(PicoPagination::getPageUrl($lastPg, $this->parameterName, $this->path)), $this->last);
        }

        return implode('', $lines);
    }
}