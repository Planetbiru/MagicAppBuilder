<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Data Transfer Object (DTO) representing a value with display and raw data.
 * 
 * The ValueDto class is designed to encapsulate a piece of data that 
 * consists of a displayable representation and its underlying raw value. 
 * This class is particularly useful in scenarios where data needs 
 * to be presented in a user-friendly format while retaining access 
 * to the original, unprocessed data.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ValueDto extends ToString
{
    /**
     * Data to be displayed.
     *
     * @var mixed
     */
    protected $display;

    /**
     * Raw data.
     *
     * @var mixed
     */
    protected $raw;

    /**
     * Constructor to initialize properties of the ValueDto class.
     *
     * If the raw data is not provided, it defaults to the display data.
     *
     * @param mixed $display The data to be displayed.
     * @param mixed|null $raw The raw data.
     */
    public function __construct($display = null, $raw = null)
    {
        $this->display = $display;
        $this->raw = $raw !== null ? $raw : $display; // Default raw to display if not provided
    }

    /**
     * Get the data to be displayed.
     *
     * @return mixed The data to be displayed.
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Set the data to be displayed and return the current instance for method chaining.
     *
     * @param mixed $display The data to set for display.
     * @return self The current instance for method chaining.
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the raw data.
     *
     * @return mixed The raw data.
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Set the raw data and return the current instance for method chaining.
     *
     * @param mixed $raw The raw data to set.
     * @return self The current instance for method chaining.
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
        return $this; // Return current instance for method chaining.
    }
}
