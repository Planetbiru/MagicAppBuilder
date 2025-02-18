<?php

namespace MagicApp;

use MagicObject\MagicObject;

/**
 * Class AppFormSelect
 *
 * Represents an HTML `<select>` form element that can contain multiple `<option>` elements.
 * This class provides methods to manage options, apply formatting, group options, 
 * and generate the HTML representation of the `<select>` element.
 *
 * It supports adding options, setting the text format of options, grouping options using `<optgroup>`, 
 * and rendering the final HTML output for the `<select>` element.
 */
class AppFormSelect
{
    const SOURCE_ENTITY = "entity";
    const SOURCE_MAP = "map";
    
    /**
     * Array of options for the select element.
     *
     * @var AppFormOption[]
     */
    private $options = array();

    /**
     * Indicates whether the <select> element contains an <optgroup>.
     *
     * @var bool
     */
    private $withGroup = false;

    /**
     * The name of the entity class referenced as an object for grouping.
     *
     * @var string
     */
    private $groupObjectName;

    /**
     * The property of the referenced entity used for the <option> value.
     *
     * @var string
     */
    private $groupColumnValue;

    /**
     * The property of the referenced entity used for the <option> label.
     *
     * @var string
     */
    private $groupColumnLabel;

    /**
     * Source of the group label, which can be either 'entity' or 'map'.
     * Determines where the group labels are sourced from: either from an entity or a predefined map.
     *
     * @var string Can be either 'entity' or 'map'.
     */
    private $groupLabelSource;

    /**
     * A map containing group labels.
     *
     * This associative array maps the entity values (used as the group) to the corresponding label text 
     * that will be displayed in the `<optgroup>` element.
     *
     * @var array An associative array where the key is the entity value and the value is the label text for the group.
     */
    private $groupMap;

    /**
     * Add an option to the select element.
     *
     * This method adds a new option to the select element, which consists of display text, a value, 
     * a selected status, optional HTML attributes, and associated data.
     *
     * @param string $label The display text for the option.
     * @param string|null $value The value of the option.
     * @param bool $selected Indicates if the option is selected.
     * @param string[]|null $attributes Additional HTML attributes for the option.
     * @param MagicObject|null $data Associated data for the option.
     * @return self The current instance, allowing method chaining.
     */
    public function add($label, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->options[] = new AppFormOption($label, $value, $selected, $attributes, $data);
        return $this;
    }

    /**
     * Set the text format for all options.
     *
     * This method allows you to set a format for the display text (text node) of each option. 
     * You can provide a format string or a callable function that will be applied to the associated data of each option.
     *
     * @param callable|string $format A callable function or a format string.
     * @return self The current instance, allowing method chaining.
     */
    public function setTextNodeFormat($format)
    {
        if (isset($format)) {
            if (is_callable($format)) {
                foreach ($this->options as $option) {
                    $option->setTextNode(call_user_func($format, $option->getData()));
                }
            } else {
                $this->setTextNodeFormatFromString($format);
            }
        }
        return $this;
    }

    /**
     * Set the text format for options using a format string.
     *
     * This method allows you to set the format for the text of each option using a format string.
     * The format string can contain placeholders like `%s` and `%d`, which will be replaced by values from the option's data.
     *
     * @param string $format The format string.
     * @return self The current instance, allowing method chaining.
     */
    public function setTextNodeFormatFromString($format)
    {
        $separator = ",";
        $params = array();
        $args = preg_split('/' . $separator . '(?=(?:[^\"])*(?![^\"]))/', $format, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($args as $i => $arg) {
            $arg = trim($arg);
            if ($i > 0 && !empty($arg)) {
                $params[] = $arg;
            }
        }

        preg_match_all('`"([^"]*)"`', $args[0], $results);
        $format2 = isset($results[1]) && isset($results[1][0]) && !empty($results[1][0]) ? $results[1][0] : $args[0];
        $numPlaceholders = preg_match_all('/%[sd]/', $format2, $matches);

        while ($numPlaceholders > count($params)) {
            $params[] = '';
        }
        if ($numPlaceholders < count($params)) {
            array_pop($params);
        }

        if (!empty($params)) {
            foreach ($this->options as $option) {
                $option->textNodeFormat($format2, $params);
            }
        }
        return $this;
    }

    /**
     * Set the indentation level for option HTML elements.
     *
     * This method sets how many tab characters will be used for indentation when rendering each option in HTML.
     *
     * @param int $indent The level of indentation (default is 1).
     * @return self The current instance, allowing method chaining.
     */
    public function setIndent($indent = 1)
    {
        $pad = str_pad('', $indent, "\t", STR_PAD_LEFT);
        foreach ($this->options as $option) {
            $option->setPad($pad);
        }
        return $this;
    }

    /**
     * Set the grouping properties for the select options.
     *
     * This method configures how options will be grouped, using properties from an entity or a map.
     *
     * @param string $groupColumnValue The property of the referenced entity used for the <option> value.
     * @param string $groupColumnLabel The property of the referenced entity used for the <option> label.
     * @param string|array $groupObject Source of group label, which can be an entity class name or a map.
     * @return self The current instance, allowing method chaining.
     */
    public function setGroup($groupColumnValue, $groupColumnLabel, $groupObject = null)
    {
        $this->withGroup = true;
        if(isset($groupObject))
        {
            if(is_string($groupObject) && !empty($groupObject))
            {
                $this->groupObjectName = $groupObject;
                $this->groupLabelSource = self::SOURCE_ENTITY;
            }
            else if(is_array($groupObject) && !empty($groupObject))
            {
                $this->groupLabelSource = self::SOURCE_MAP;
                $this->groupMap = $groupObject;
            }
        }
        if(isset($groupColumnValue))
        {
            if(!isset($groupColumnValue) || empty($groupColumnValue))
            {
                $groupColumnLabel = $groupColumnValue;
            }
            $this->groupColumnValue = $groupColumnValue;
            $this->groupColumnLabel = $groupColumnLabel;
            $this->withGroup = true;            
        }
        
        return $this;
    }

    /**
     * Creates a group of options based on the selected group label source.
     *
     * This method determines the grouping logic, either based on the entity or the provided map.
     *
     * @return array The groups of options, with each group containing an array of value-label pairs.
     */
    private function createGroup()
    {
        $group = array();
        if($this->groupLabelSource == self::SOURCE_ENTITY)
        {
            foreach ($this->options as $option) {
                $info = $this->getGroupLabel($option);
                if (isset($info)) {
                    $group[$info[0]] = $info;
                }
            }
        }
        else if($this->groupLabelSource == self::SOURCE_MAP)
        {
            foreach ($this->groupMap as $value => $label) {
                $group[$value] = array($value, $label);
            }
        }
        else 
        {
            foreach ($this->options as $option) {
                $value = $option->getData()->get($this->groupColumnValue);
                $label = $option->getData()->get($this->groupColumnLabel);
                $group[$value] = array($value, $label);
            }
        }
        return $group;
    }

    /**
     * Retrieves the group label for a given option.
     *
     * This method fetches the group label for an option based on the entity data or other sources.
     *
     * @param AppFormOption $option The option for which the group label is being retrieved.
     * @return array|null An array containing the group value and label, or null if no group is found.
     */
    private function getGroupLabel($option)
    {
        $data = $option->getData();
        if (isset($data)) {
            $result = null;
            if($data->hasValue($this->groupObjectName))
            {
                if($data->get($this->groupObjectName) instanceof MagicObject)
                {
                    $ref = $data->get($this->groupObjectName);
                    if ($ref->hasValue($this->groupColumnValue) && $ref->hasValue($this->groupColumnLabel)) {
                        $result = array($ref->get($this->groupColumnValue), $ref->get($this->groupColumnLabel));
                    }
                }
                else if ($data->hasValue($this->groupColumnValue) && $data->hasValue($this->groupColumnLabel)) {
                    $result = array($data->get($this->groupColumnValue), $data->get($this->groupColumnLabel));
                }
            }
            else if ($data->hasValue($this->groupColumnValue) && $data->hasValue($this->groupColumnLabel)) {
                $result = array($data->get($this->groupColumnValue), $data->get($this->groupColumnLabel));
            }
            return $result;
        }
        return null;
    }
    
    /**
     * Renders the select options without grouping.
     *
     * This method generates the HTML for the options in the select element when no grouping is applied.
     *
     * @return string The HTML representation of the ungrouped options.
     */
    private function renderWithoutGroup()
    {
        $opt = array();
        foreach ($this->options as $option) {
            $opt[] = $option->toString();
        }
        return implode("\r\n", $opt);
    }

    /**
     * Renders the select options with grouping.
     *
     * This method generates the HTML for the options in the select element, including `<optgroup>` elements for grouping.
     *
     * @return string The HTML representation of the grouped options.
     */
    private function renderWithGroup()
    {
        $groupedOption = array();
        $ungroupedOption = array();
        
        $group = $this->createGroup();
    
        foreach ($this->options as $option) {
            $info = $this->getGroupLabel($option);
            if (isset($info)) {
                if(!isset($groupedOption[$info[0]]))
                {
                    $groupedOption[$info[0]] = array();
                }
                $groupedOption[$info[0]][] = $option;
            } else {
                $ungroupedOption[] = $option->toString();
            }
        }

        $grouped = array();
        $ungrouped = array();
        $inGroup = array();

        foreach ($group as $info) {
            $label = htmlspecialchars(htmlspecialchars_decode($info[1]));
            $grouped[] = '<optgroup label="' . $label . '">';
            $collection = $groupedOption[$info[0]];
            foreach ($collection as $option) {
                $grouped[] = $option->toString();
                $inGroup[] = $option->getValue();     
            }
            
            $grouped[] = '</optgroup>';
        }

        foreach ($this->options as $option) {
            if (!in_array($option->getValue(), $inGroup)) {
                $ungrouped[] = $option->toString();
            }
        }
        
        return implode("\r\n", $grouped) . rtrim("\r\n" . implode("\r\n", $ungrouped), "\r\n");
    }

    /**
     * Gets the HTML representation of the select element as a string.
     *
     * This method generates the complete HTML representation of the `<select>` element, including all of its options.
     * Options are rendered either in grouped or ungrouped format based on the configuration.
     *
     * @return string The HTML representation of the select element.
     */
    public function __toString()
    {
        return $this->withGroup ? $this->renderWithGroup() : $this->renderWithoutGroup();
    }

}
