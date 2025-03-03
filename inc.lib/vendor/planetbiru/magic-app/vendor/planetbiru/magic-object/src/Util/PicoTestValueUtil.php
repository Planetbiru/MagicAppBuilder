<?php

namespace MagicObject\Util;

namespace MagicObject\Util;

/**
 * Class PicoTestValueUtil
 *
 * A utility class for managing return values based on specified conditions.
 * This class allows you to set a return value and control whether it should
 * be returned based on conditions. It is useful for testing and mocking
 * scenarios where specific outputs are required based on input conditions.
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoTestValueUtil
{
    /**
     * Value to be returned.
     *
     * @var mixed
     */
    private $returnValue;

    /**
     * Condition for determining return value.
     *
     * @var bool
     */
    private $condition;

    /**
     * Set a return value.
     *
     * @param mixed $returnValue The value to return based on the condition.
     * @return self|mixed Returns the return value if the condition is true; otherwise, returns the instance.
     */
    public function doReturn($returnValue)
    {
        $this->returnValue = $returnValue;
        return $this->condition ? $returnValue : $this;
    }

    /**
     * Set return value to 'selected'.
     *
     * @return self|mixed Returns 'selected' if the condition is true; otherwise, returns the instance.
     */
    public function doReturnSelected()
    {
        return $this->doReturn('selected');
    }

    /**
     * Set return value to ' selected=selected'.
     *
     * @return self|mixed Returns ' selected=selected' if the condition is true; otherwise, returns the instance.
     */
    public function doReturnAttributeSelected()
    {
        return $this->doReturn(' selected="selected"');
    }

    /**
     * Set return value to 'checked'.
     *
     * @return self|mixed Returns 'checked' if the condition is true; otherwise, returns the instance.
     */
    public function doReturnChecked()
    {
        return $this->doReturn('checked');
    }

    /**
     * Set return value to ' checked=checked'.
     *
     * @return self|mixed Returns ' checked=checked' if the condition is true; otherwise, returns the instance.
     */
    public function doReturnAttributeChecked()
    {
        return $this->doReturn(' checked="checked"');
    }

    /**
     * Set a return value and determine if it should be returned.
     *
     * @param mixed $returnValue The value to return if the condition is true.
     * @return mixed|null Returns the return value if the condition is true; otherwise, returns null.
     */
    public function thenReturn($returnValue)
    {
        $this->returnValue = $returnValue;
        return $this->condition ? $returnValue : null;
    }

    /**
     * Set the condition for returning the value.
     *
     * @param bool $condition The condition that determines the return value.
     * @return self|mixed Returns the return value if set and the condition is true; otherwise, returns the instance.
     */
    public function when($condition)
    {
        if (isset($this->returnValue)) {
            return $condition ? $this->returnValue : null;
        }
        $this->condition = $condition;
        return $this;
    }

    /**
     * Set the condition based on equality of two parameters.
     *
     * @param mixed $param1 The first parameter to compare.
     * @param mixed $param2 The second parameter to compare.
     * @return self|mixed Returns the return value if set and the parameters are equal; otherwise, returns the instance.
     */
    public function whenEquals($param1, $param2)
    {
        return $this->when($param1 == $param2);
    }
}
