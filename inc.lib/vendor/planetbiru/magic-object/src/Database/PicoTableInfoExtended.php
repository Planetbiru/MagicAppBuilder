<?php

namespace MagicObject\Database;

/**
 * Table info extended
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoTableInfoExtended extends PicoTableInfo
{
    const NAME      = "name";
    const PREV_NAME = "prevColumnName";
    const ELEMENT   = "element";

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self(null, array(), array(), array(), array(), array(), array());
    }

    /**
     * Unique column
     *
     * @return self
     */
    public function uniqueColumns()
    {
        $tmp = array();
        $test = array();
        foreach($this->columns as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->columns = $tmp;
        return $this;
    }

    /**
     * Unique join column
     *
     * @return self
     */
    public function uniqueJoinColumns()
    {
        $tmp = array();
        $test = array();
        foreach($this->joinColumns as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->joinColumns = $tmp;
        return $this;
    }

    /**
     * Unique primary key
     *
     * @return self
     */
    public function uniquePrimaryKeys()
    {
        $tmp = array();
        $test = array();
        foreach($this->primaryKeys as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->primaryKeys = $tmp;
        return $this;
    }

    /**
     * Unique auto increment
     *
     * @return self
     */
    public function uniqueAutoIncrementKeys()
    {
        $tmp = array();
        $test = array();
        foreach($this->autoIncrementKeys as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->autoIncrementKeys = $tmp;
        return $this;
    }

    /**
     * Unique default value
     *
     * @return self
     */
    public function uniqueDefaultValue()
    {
        $tmp = array();
        $test = array();
        foreach($this->defaultValue as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->defaultValue = $tmp;
        return $this;
    }

    /**
     * Unique not null column
     *
     * @return self
     */
    public function uniqueNotNullColumns()
    {
        $tmp = array();
        $test = array();
        foreach($this->notNullColumns as $elem)
        {
            if(!in_array($elem[self::NAME], $test))
            {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->notNullColumns = $tmp;
        return $this;
    }

    /**
     * Merge list
     *
     * @param array $tmp Temporary list
     * @param array $oldListCheck Old list
     * @param array $newList New list
     * @return array
     */
    private function mergeList($tmp, $oldListCheck, $newList)
    {
        $prevColumName = "";
        $listToInsert = array();
        foreach($newList as $prop=>$elem)
        {
            if(!in_array($elem[self::NAME], $oldListCheck))
            {
                $listToInsert[$prop] = array(self::ELEMENT=>$elem, self::PREV_NAME=>$prevColumName);
            }
            $prevColumName = $elem[self::NAME];
        }
        foreach($listToInsert as $prop=>$toInsert)
        {
            if(empty($toInsert[self::PREV_NAME]))
            {
                // insert to the end of table
                $tmp[$prop] = $toInsert[self::ELEMENT];
            }
            else
            {
                $tmp2 = array();
                foreach($tmp as $prop2=>$elem2)
                {
                    $tmp2[$prop2] = $elem2;
                    if($elem2[self::NAME] == $toInsert[self::PREV_NAME])
                    {
                        // insert after prevColumnName
                        $tmp2[$prop] = $toInsert[self::ELEMENT];
                    }
                }
                // update temporary list
                $tmp = $tmp2;
            }
        }
        return $tmp;
    }

    /**
     * Get oldlist check
     *
     * @param array $oldList Old list
     * @return array
     */
    private function getOldListCheck($oldList)
    {
        $oldListCheck = array();
        foreach($oldList as $elem)
        {
            $oldListCheck[] = $elem[self::NAME];
        }
        return $oldListCheck;
    }

    /**
     * Unique column
     *
     * @param array $newList New list
     * @return self
     */
    public function mergeColumns($newList)
    {
        $tmp = $this->columns;
        $oldListCheck = $this->getOldListCheck($this->columns);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->columns = $tmp;
        return $this;
    }

    /**
     * Unique join column
     *
     * @param array $newList New list
     * @return self
     */
    public function mergeJoinColumns($newList)
    {
        $tmp = $this->joinColumns;
        $oldListCheck = $this->getOldListCheck($this->joinColumns);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->joinColumns = $tmp;
        return $this;
    }

    /**
     * Unique primary key
     *
     * @param array $newList New list
     * @return self
     */
    public function mergePrimaryKeys($newList)
    {
        $tmp = $this->primaryKeys;
        $oldListCheck = $this->getOldListCheck($this->primaryKeys);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->primaryKeys = $tmp;
        return $this;
    }

    /**
     * Unique auto increment
     *
     * @param array $newList New list
     * @return self
     */
    public function mergeAutoIncrementKeys($newList)
    {
        $tmp = $this->autoIncrementKeys;
        $oldListCheck = $this->getOldListCheck($this->autoIncrementKeys);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->autoIncrementKeys = $tmp;
        return $this;
    }

    /**
     * Unique default value
     *
     * @param array $newList New list
     * @return self
     */
    public function mergeDefaultValue($newList)
    {
        $tmp = $this->defaultValue;
        $oldListCheck = $this->getOldListCheck($this->defaultValue);
        $tmp = $this->mergeList($tmp, $oldListCheck, $newList);
        $this->defaultValue = $tmp;
        return $this;
    }

    /**
     * Unique not null column
     *
     * @param array $newList New list
     * @return self
     */
    public function mergeNotNullColumns($newList)
    {
        $tmp = $this->notNullColumns;
        $oldListCheck = $this->getOldListCheck($this->notNullColumns);
        $prevColumName = "";
        $listToInsert = array();
        foreach($newList as $elem)
        {
            if(!in_array($elem[self::NAME], $oldListCheck))
            {
                $listToInsert[] = array(self::ELEMENT=>$elem, self::PREV_NAME=>$prevColumName);
            }
            $prevColumName = $elem[self::NAME];
        }
        $this->notNullColumns = $tmp;
        return $this;
    }
}