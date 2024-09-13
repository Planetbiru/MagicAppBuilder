<?php

namespace MagicObject\Util;

class PicoTestValueUtil
{

    /**
     * Value to be return
     *
     * @var mixed
     */
    private $returnValue;

    /**
     * Condition
     *
     * @var boolean
     */
    private $condition;

    /**
     * Do return
     *
     * @param mixed $returnValue Return value
     * @return self|mixed
     */
    public function doReturn($returnValue)
    {
        $this->returnValue = $returnValue;
        if(isset($this->condition) && $this->condition === true)
        {
            return $returnValue;
        }
        $this->returnValue = $returnValue;
        return $this;
    }

    /**
     * Do return selected
     *
     * @param mixed $returnValue Return value
     * @return self|mixed
     */
    public function doReturnSelected()
    {
        $returnValue = 'selected';
        $this->returnValue = $returnValue;
        if(isset($this->condition) && $this->condition === true)
        {
            return $returnValue;
        }
        $this->returnValue = $returnValue;
        return $this;
    }

    /**
     * Do return selected=selected
     *
     * @param mixed $returnValue Return value
     * @return self|mixed
     */
    public function doReturnAttributeSelected()
    {
        $returnValue = ' selected=selected';
        $this->returnValue = $returnValue;
        if(isset($this->condition) && $this->condition === true)
        {
            return $returnValue;
        }
        $this->returnValue = $returnValue;
        return $this;
    }

    /**
     * Do return checked
     *
     * @param mixed $returnValue Return value
     * @return self|mixed
     */
    public function doReturnChecked()
    {
        $returnValue = 'checked';
        $this->returnValue = $returnValue;
        if(isset($this->condition) && $this->condition === true)
        {
            return $returnValue;
        }
        $this->returnValue = $returnValue;
        return $this;
    }

    /**
     * Do return checked=checked
     *
     * @param mixed $returnValue Return value
     * @return self|mixed
     */
    public function doReturnAttributeChecked()
    {
        $returnValue = ' checked=checked';
        $this->returnValue = $returnValue;
        if(isset($this->condition) && $this->condition === true)
        {
            return $returnValue;
        }
        $this->returnValue = $returnValue;
        return $this;
    }

    /**
     * Then return
     *
     * @param mixed $returnValue Return value
     * @return self|mixed
     */
    public function thenReturn($returnValue)
    {
        $this->returnValue = $returnValue;
        if(isset($this->condition) && $this->condition === true)
        {
            return $returnValue;
        }
        return null;
    }

    /**
     * When
     *
     * @param boolean $condition Contition
     * @return self|mixed
     */
    public function when($condition)
    {
        if(isset($this->returnValue))
        {
            if($condition)
            {
                return $this->returnValue;
            }
            else
            {
                return null;
            }
        }
        else
        {
            $this->condition = $condition;
            return $this;
        }
    }

    /**
     * When equals
     *
     * @param mixed $param1 Parameter 1
     * @param mixed $param2 Parameter 2
     * @return self|mixed
     */
    public function whenEquals($param1, $param2)
    {
        if(isset($this->returnValue))
        {
            if($param1 == $param2)
            {
                return $this->returnValue;
            }
            else
            {
                return null;
            }
        }
        else
        {
            $this->condition = $param1 == $param2;
            return $this;
        }
    }
}