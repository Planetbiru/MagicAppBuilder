<?php

namespace AppBuilder\Module;

/**
 * Class SpecificationConfig
 *
 * Represents a filtering or query specification applied to a data column.
 * Defines the column to filter, the value to compare against, and the comparison operator.
 */
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
     * The comparison operator (e.g., "equals", "like", ">", "<").
     *
     * @var string
     */
    public $comparison;

    /**
     * SpecificationConfig constructor.
     *
     * Initializes a specification using the provided configuration array.
     *
     * @param array $data Configuration data for the specification.
     */
    public function __construct($data) {
        $this->column = $data['column'];
        $this->value = $data['value'];
        $this->comparison = $data['comparison'];
    }
}
