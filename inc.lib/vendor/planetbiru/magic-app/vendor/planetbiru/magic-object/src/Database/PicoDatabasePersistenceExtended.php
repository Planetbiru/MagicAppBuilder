<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\MagicObject;

class PicoDatabasePersistenceExtended extends PicoDatabasePersistence
{
    public function __call($method, $params)
    {
        if (strncasecmp($method, "set", 3) === 0 && isset($params) && isset($params[0])){
            $var = lcfirst(substr($method, 3));
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