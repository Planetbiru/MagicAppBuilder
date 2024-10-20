<?php

namespace AppBuilder;

/**
 * Class AppSection
 *
 * Represents a collection of sections that can be concatenated into a single string.
 * This class provides methods to add, clear, and print sections, with customizable separators.
 */
class AppSection
{
    /**
     * Separator for 'if-else' constructs.
     */
    const SEPARATOR_IF_ELSE = "\r\nelse ";

    /**
     * Separator for new lines.
     */
    const SEPARATOR_NEW_LINE = "\r\n";

    /**
     * Separator for white spaces.
     */
    const SEPARATOR_WHITE_SPACE = " ";

    /**
     * No separator.
     */
    const SEPARATOR_NONE = "";

    /**
     * Array of sections.
     *
     * @var string[]
     */
    private $sections = array();

    /**
     * Separator used for joining sections.
     *
     * @var string
     */
    private $separator;

    /**
     * Constructor to initialize the section separator.
     *
     * @param string $separator The separator to use when joining sections.
     */
    public function __construct($separator = self::SEPARATOR_NONE)
    {
        $this->separator = $separator;
    }
    
    /**
     * Clear all sections.
     *
     * This method resets the sections array to an empty state.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function clearSections()
    {
        $this->sections = array();
        return $this;
    }
    
    /**
     * Add a new section to the collection.
     *
     * This method appends a section string to the sections array, 
     * ensuring it is not empty.
     *
     * @param string $section The section to add.
     * @return self Returns the current instance for method chaining.
     */
    public function add($section)
    {
        if (isset($section) && !empty($section)) {
            $this->sections[] = $section;
        }
        return $this;
    }
    
    /**
     * Print all sections as a single string.
     *
     * This method concatenates the sections using the specified separator.
     *
     * @return string A single string containing all sections.
     */
    public function printSections()
    {
        return implode($this->separator, $this->sections);
    }
    
    /**
     * Convert the object to a string.
     *
     * This magic method allows the object to be represented as a string
     * by calling printSections().
     *
     * @return string A single string containing all sections.
     */
    public function __toString()
    {
        return $this->printSections();
    }
}
