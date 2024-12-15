<?php

namespace MagicObject\Database;

/**
 * Class PicoDatabaseQueryTemplate
 * 
 * This class represents a query template or builder that can either hold a
 * pre-defined query template (as a string) or an instance of a query builder
 * (PicoDatabaseQueryBuilder). It is designed to facilitate the construction
 * and conversion of queries to a string format, either directly or through 
 * a query builder.
 *
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseQueryTemplate
{
    /**
     * The query template as a string.
     * 
     * This property holds the template for a database query in string format,
     * or null if no template is provided.
     *
     * @var string|null
     */
    private $template = null;

    /**
     * The query builder instance.
     * 
     * This property holds an instance of PicoDatabaseQueryBuilder, which is used
     * to build and manipulate database queries programmatically.
     *
     * @var PicoDatabaseQueryBuilder|null
     */
    private $builder = null;

    /**
     * PicoDatabaseQueryTemplate constructor.
     *
     * The constructor accepts either a query builder object or a query template string.
     * It initializes the appropriate property based on the type of the provided argument.
     *
     * @param PicoDatabaseQueryBuilder|string|null $query The query builder object or query template string.
     */
    public function __construct($query)
    {
        if (isset($query)) {
            if ($query instanceof PicoDatabaseQueryBuilder) {
                $this->builder = $query;
            } else if (is_string($query)) {
                $this->template = $query;
            }
        }
    }

    /**
     * Converts the object to a string representation.
     *
     * This method returns the string representation of the query. If a query builder
     * instance is set, it will return the string representation of the builder. If
     * a query template string is provided, it will return that template string.
     * If neither is set, it will return an empty string.
     *
     * @return string The string representation of the query, either from the builder or template.
     */
    public function __toString()
    {
        if (isset($this->builder)) {
            return $this->builder->toString();
        } else if (isset($this->template)) {
            return $this->template;
        } else {
            return "";
        }
    }
}
