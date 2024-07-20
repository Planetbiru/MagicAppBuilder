<?php

namespace MagicApp;

use MagicObject\MagicObject;

class AppFormSelect
{
    /**
     * Options
     *
     * @var AppFormOption[]
     */
    private $options = array();

    /**
     * Add option
     *
     * @param string $textNode
     * @param string $value
     * @param boolean $selected
     * @param string[] $attributes
     * @param MagicObject $data
     * @return self
     */
    public function add($textNode, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->options[] = new AppFormOption($textNode, $value, $selected, $attributes, $data);
        return $this;
    }

    /**
     * Set text node format
     *
     * @param callable|string $format
     * @return self
     */
    public function setTextNodeFormat($format)
    {
        if(isset($format))
        {
            if(is_callable($format))
            {
                for($i = 0; $i < count($this->options); $i++)
                {
                    $this->options[$i]->setTextNode(call_user_func($format, $this->options[$i]->getData()));
                }
            }
            else
            {
                $this->setTextNodeFormatFromString($format);
            }
        }
        return $this;
    }

    /**
     * Add text node format
     *
     * @param string $format
     * @return self
     */
    public function setTextNodeFormatFromString($format)
    {
        $seperator = ",";
        $params = array();
        $args = preg_split('/'.$seperator.'(?=(?:[^\"])*(?![^\"]))/', $format, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($args as $i=>$arg) 
        {
            $arg = trim($arg);
            if($i > 0 && !empty($arg))
            {
                $params[] = $arg;
            }
        }
        preg_match_all('`"([^"]*)"`', $args[0], $results);
        $format2 = isset($results[1]) && isset($results[1][0]) && !empty($results[1][0]) ? $results[1][0] : $args[0];
        $numPlaceholders = preg_match_all('/%[sd]/', $format2, $matches);
        while($numPlaceholders > count($params))
        {
            $params[] = '';
        }
        if($numPlaceholders < count($params))
        {
            array_pop($params);
        }
        if(!empty($params))
        {
            for($i = 0; $i < count($this->options); $i++)
            {
                $this->options[$i]->textNodeFormat($format2, $params);
            }
        }
        return $this;
    }

    /**
     * Set indent
     *
     * @param integer $indent
     * @return self
     */
    public function setIndent($indent = 1)
    {
        $pad =  str_pad('', $indent, "\t", STR_PAD_LEFT);
        for($i = 0; $i < count($this->options); $i++)
        {
            $this->options[$i]->setPad($pad);
        }
        return $this;
    }

    /**
     * Get object as tring
     *
     * @return string
     */
    public function __tostring()
    {
        $opt = array();
        foreach($this->options as $option)
        {
            $opt[] = $option->toString();
        }
        return implode("\r\n", $opt);
    }
}