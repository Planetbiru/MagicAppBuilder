<?php

namespace AppBuilder\Module;


/**
 * Class EntityItem
 *
 * Represents metadata for an entity used in a module, including the entity's
 * name, associated database table, and primary key column.
 */
class EntityItem {

    /**
     * The name of the entity.
     *
     * @var string
     */
    public $entityName;

    /**
     * The name of the database table associated with the entity.
     *
     * @var string
     */
    public $tableName;

    /**
     * The primary key column of the database table.
     *
     * @var string
     */
    public $primaryKey;

    /**
     * EntityItem constructor.
     *
     * @param array $data Configuration data for the entity item.
     */
    public function __construct($data) {
        $this->entityName = $data['entityName'];
        $this->tableName = $data['tableName'];
        $this->primaryKey = $data['primaryKey'];
    }
}