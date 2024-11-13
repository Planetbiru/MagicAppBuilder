<?php

namespace MagicObject\Database;

use MagicObject\MagicObject;

/**
 * Class PicoDatabaseEntity
 *
 * Represents a database entity that manages multiple database connections.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseEntity
{
    /**
     * An associative array of databases indexed by entity class name.
     * 
     * @var PicoDatabase[] 
     */
    private $databases = [];
    
    /**
     * Default database connection
     *
     * @var PicoDatabase
     */
    private $defaultDatabase;
    
    /**
     * Adds an entity to the database.
     *
     * @param MagicObject $entity The entity to add.
     * @param PicoDatabase|null $database The database to associate with the entity. If null, 
     *                                    the current database of the entity will be used.
     * @return self Returns the current instance for method chaining.
     */
    public function add($entity, $database = null)
    {
        if ($database === null) {
            $database2 = $entity->currentDatabase();
            if ($database2 !== null && $database2->isConnected()) {
                $database = $database2;
            }
        }
        if ($database !== null) {
            $className = get_class($entity);
            $this->databases[$className] = $database;
        }
        return $this;
    }
    
    /**
     * Gets the database associated with an entity.
     *
     * @param MagicObject $entity The entity whose database is to be retrieved.
     * @return PicoDatabase|null Returns the associated database or null if not found.
     */
    public function getDatabase($entity)
    {
        $className = get_class($entity);
        if (isset($this->databases[$className])) {
            return $this->databases[$className];
        }
        return $this->defaultDatabase;
    }

    /**
     * Get default database connection
     *
     * @return PicoDatabase
     */ 
    public function getDefaultDatabase()
    {
        return $this->defaultDatabase;
    }

    /**
     * Set default database connection
     *
     * @param PicoDatabase $defaultDatabase Default database connection
     *
     * @return self
     */ 
    public function setDefaultDatabase($defaultDatabase)
    {
        $this->defaultDatabase = $defaultDatabase;

        return $this;
    }
}
