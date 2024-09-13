<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\MagicObject;

/**
 * Database persistence extended
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabasePersistenceExtended extends PicoDatabasePersistence
{
    /**
     * Magic object to handle undefined methods
     *
     * @param string $method Method name
     * @param mixed[] $params Parameters
     * @return void
     */
    public function __call($method, $params)
    {
        if (strlen($method) > 3 && strncasecmp($method, "set", 3) === 0 && isset($params) && is_array($params)){
            $var = lcfirst(substr($method, 3));
            if(empty($params))
            {
                $params[0] = null;
            }
            $this->object->set($var, $params[0]);
            return $this;
        }
    }

    /**
     * Select one record
     *
     * @return MagicObject
     */
    public function select()
    {
        $result = parent::select();
        if($result == null)
        {
            throw new NoRecordFoundException(parent::MESSAGE_NO_RECORD_FOUND);
        }
        $entity = new $this->className(null, $this->database);
        $entity->loadData($result);
        return $entity;
    }

    /**
     * Select all record
     *
     * @return MagicObject[]
     */
    public function selectAll()
    {
        $collection = array();
        $result = parent::selectAll();

        if($result == null || empty($result))
        {
            throw new NoRecordFoundException(parent::MESSAGE_NO_RECORD_FOUND);
        }
        foreach($result as $data)
        {
            $entity = new $this->className(null, $this->database);
            $entity->loadData($data);
            $collection[] = $entity;
        }
        return $collection;
    }
}