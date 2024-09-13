<?php

namespace AppBuilder;

class AppSection
{
    const SEPARATOR_IF_ELSE = "\r\nelse ";
    const SEPARATOR_NEW_LINE = "\r\n";
    const SEPARATOR_WHITE_SPACE = " ";
    const SEPARATOR_NONE = "";
    
    private $sections = array();

    /**
     * Separator
     *
     * @var string
     */
    private $separator = self::SEPARATOR_NONE;

    /**
     * Separator
     *
     * @param string $separator
     */
    public function __construct($separator)
    {
        $this->separator = $separator;
    }
    
    /**
     * Clear section
     *
     * @return self
     */
    public function clearSession()
    {
        $this->sections = array();
        return $this;
    }
    
    /**
     * Add section
     *
     * @param string $section
     * @return self
     */
    public function add($section)
    {
        if(isset($section) && !empty($section))
        {
            $this->sections[] = $section;
        }
        return $this;
    }
    
    /**
     * Print all sections
     *
     * @return string
     */
    public function printSections()
    {
        return implode($this->separator, $this->sections);
    }
    
    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->printSections();
    }
}