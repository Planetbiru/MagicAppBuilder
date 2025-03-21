<?php

namespace AppBuilder\Module;

class SpecificationConfig {

    /**
     * The database column to apply the specification to.
     *
     * @var string
     */
    public $column;

    /**
     * The value to compare against the column.
     *
     * @var string
     */
    public $value;

    /**
     * The comparison operator (e.g., equals, greater than).
     *
     * @var string
     */
    public $comparison;

    /**
     * SpecificationConfig constructor.
     *
     * @param array $data Configuration data for the specification.
     */
    public function __construct($data) {
        $this->column = $data['column'];
        $this->value = $data['value'];
        $this->comparison = $data['comparison'];
    }
}