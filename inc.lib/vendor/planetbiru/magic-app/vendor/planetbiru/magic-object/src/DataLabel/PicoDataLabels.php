<?php

namespace MagicObject\PicoDataLabel;

use MagicObject\DataLabel\PicoDataLabel;

/**
 * Data labels
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDataLabels
{
    /**
     * Data
     *
     * @var PicoDataLabel[]
     */
    private $data = array();

    /**
     * Append data
     *
     * @param PicoDataLabel $data New data
     * @return self
     */
    public function append($data)
    {
        $this->data[] = $data;
        return $this;
    }

    public function generate()
    {
        foreach($this->data as $data)
        {

        }
    }
}