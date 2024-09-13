<?php

namespace MagicApp;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class AppFormOption
{

    /**
     * Text node
     *
     * @var string
     */
    private $textNode;

    /**
     * Value
     *
     * @var string
     */
    private $value;

    /**
     * Selected
     *
     * @var boolean
     */
    private $selected;

    /**
     * Format
     *
     * @var string
     */
    private $format;

    /**
     * Params
     *
     * @var string[]
     */
    private $params;

    /**
     * Data
     *
     * @var MagicObject
     */
    private $data;

    /**
     * Attributes
     *
     * @var string[]
     */
    private $attributes;

    /**
     * Pad
     *
     * @var string
     */
    private $pad = "";

    /**
     * Undocumented function
     *
     * @param string $textNode
     * @param string $value
     * @param boolean $selected
     * @param string[] $attributes
     * @param MagicObject $data
     */
    public function __construct($textNode, $value = null, $selected = false, $attributes = null, $data = null)
    {
        $this->textNode = $textNode;
        $this->value = $value;
        $this->selected = $selected;
        $this->attributes = $attributes;
        $this->data = $data;
    }

    /**
     * Create attributes
     *
     * @return string
     */
    public function createAttributes()
    {
        $attrs = array();
        if(isset($this->attributes) && is_array($this->attributes))
        {
            foreach($this->attributes as $attr=>$val)
            {
                $attrs[] = 'data-'.str_replace('_', '-', PicoStringUtil::snakeize($attr)).'="'.htmlspecialchars($val).'"';
            }
            return ' '.implode(' ', $attrs);
        }
        return '';
    }

    /**
     * Add format to text node
     *
     * @param string $format
     * @param string[] $params
     * @return self
     */
    public function textNodeFormat($format, $params)
    {
        $this->format = $format;
        $this->params = $params;
        return $this;
    }

    /**
     * Get values from parameters
     *
     * @return string[]
     */
    public function getValues()
    {
        $values = array();
        if(isset($this->params) && is_array($this->params))
        {
            foreach($this->params as $param)
            {
                $values[] = $this->getValue($param);
            }
        }
        return $values;
    }

    /**
     * Get value of parameter
     *
     * @param string $param
     * @return string
     */
    public function getValue($param)
    {
        if($this->data == null)
        {
            return null;
        }
        $param = trim($param);
        $value = null;
        if(stripos($param, '.') !== false)
        {
            $param = str_replace(' ', '', $param);
            $arr = explode(".", $param, 2);
            if($this->data->get($arr[0]) != null && $this->data->get($arr[0]) instanceof MagicObject)
            {
                $value = $this->data->get($arr[0])->get($arr[1]);
            }
        }
        else
        {
            $value = $this->data->get($param);
        }
        return $value;
    }

    /**
     * Set pad
     *
     * @param string $pad
     * @return self
     */
    public function setPad($pad = "\t")
    {
        $this->pad = $pad;
        return $this;
    }

    /**
     * Get object as tring
     *
     * @return string
     */
    public function __tostring()
    {
        $selected = $this->selected ? ' selected' : '';
        $attrs = $this->createAttributes();
        if(isset($this->format) && isset($this->params))
        {
            $values = $this->getValues();
            $textNode = vsprintf($this->format, $values);
            return $this->pad.'<option value="'.$this->value.'"'.$attrs.$selected.'>'.$textNode.'</option>';
        }
        else
        {
            return $this->pad.'<option value="'.$this->value.'"'.$attrs.$selected.'>'.$this->textNode.'</option>';
        }
    }

    /**
     * Alias toString
     *
     * @return string
     */
    public function toString()
    {
        return $this->__tostring();
    }

    /**
     * Get text node
     *
     * @return string
     */ 
    public function getTextNode()
    {
        return $this->textNode;
    }

    /**
     * Set text node
     *
     * @param string  $textNode  Text node
     *
     * @return self
     */ 
    public function setTextNode($textNode)
    {
        $this->textNode = $textNode;

        return $this;
    }

    /**
     * Get data
     *
     * @return MagicObject
     */ 
    public function getData()
    {
        return $this->data;
    }
}