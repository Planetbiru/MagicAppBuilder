<?php

namespace MagicObject\Database;

/**
 * Class representing extended information about a database table.
 *
 * This class extends the functionality of PicoTableInfo by providing methods
 * for managing unique columns, join columns, primary keys, auto-increment keys,
 * default values, and not-null columns.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoTableInfoExtended extends PicoTableInfo
{
    const NAME      = "name"; // Key for the column name
    const PREV_NAME = "prevColumnName"; // Key for the previous column name
    const ELEMENT   = "element"; // Key for the element

    /**
     * Gets an instance of PicoTableInfoExtended.
     *
     * @return self A new instance of PicoPredicate.
     */
    public static function getInstance()
    {
        return new self(null, array(), array(), array(), array(), array(), array());
    }

    /**
     * Removes duplicate columns based on their names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function uniqueColumns()
    {
        $tmp = array();
        $test = array();
        foreach ($this->columns as $elem) {
            if (!in_array($elem[self::NAME], $test)) {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->columns = $tmp;
        return $this;
    }

    /**
     * Removes duplicate join columns based on their names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function uniqueJoinColumns()
    {
        $tmp = array();
        $test = array();
        foreach ($this->joinColumns as $elem) {
            if (!in_array($elem[self::NAME], $test)) {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->joinColumns = $tmp;
        return $this;
    }

    /**
     * Removes duplicate primary keys based on their names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function uniquePrimaryKeys()
    {
        $tmp = array();
        $test = array();
        foreach ($this->primaryKeys as $elem) {
            if (!in_array($elem[self::NAME], $test)) {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->primaryKeys = $tmp;
        return $this;
    }

    /**
     * Removes duplicate auto-increment keys based on their names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function uniqueAutoIncrementKeys()
    {
        $tmp = array();
        $test = array();
        foreach ($this->autoIncrementKeys as $elem) {
            if (!in_array($elem[self::NAME], $test)) {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->autoIncrementKeys = $tmp;
        return $this;
    }

    /**
     * Removes duplicate default value keys based on their names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function uniqueDefaultValue()
    {
        $tmp = array();
        $test = array();
        foreach ($this->defaultValue as $elem) {
            if (!in_array($elem[self::NAME], $test)) {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->defaultValue = $tmp;
        return $this;
    }

    /**
     * Removes duplicate not-null columns based on their names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function uniqueNotNullColumns()
    {
        $tmp = array();
        $test = array();
        foreach ($this->notNullColumns as $elem) {
            if (!in_array($elem[self::NAME], $test)) {
                $tmp[] = $elem;
                $test[] = $elem[self::NAME];
            }
        }
        $this->notNullColumns = $tmp;
        return $this;
    }

    /**
     * Merges a new list of items into an existing temporary list.
     *
     * @param array $tmp The temporary list.
     * @param array $oldListCheck The old list to check against.
     * @param array $newList The new list to merge.
     * @return array The updated temporary list.
     */
    private function mergeList($tmp, $oldListCheck, $newList)
    {
        $prevColumName = "";
        $listToInsert = array();
        foreach ($newList as $prop => $elem) {
            if (!in_array($elem[self::NAME], $oldListCheck)) {
                $listToInsert[$prop] = array(self::ELEMENT => $elem, self::PREV_NAME => $prevColumName);
            }
            $prevColumName = $elem[self::NAME];
        }
        foreach ($listToInsert as $prop => $toInsert) {
            if (empty($toInsert[self::PREV_NAME])) {
                // Insert to the end of the list
                $tmp[$prop] = $toInsert[self::ELEMENT];
            } else {
                $tmp2 = array();
                foreach ($tmp as $prop2 => $elem2) {
                    $tmp2[$prop2] = $elem2;
                    if ($elem2[self::NAME] == $toInsert[self::PREV_NAME]) {
                        // Insert after prevColumnName
                        $tmp2[$prop] = $toInsert[self::ELEMENT];
                    }
                }
                // Update temporary list
                $tmp = $tmp2;
            }
        }
        return $tmp;
    }

    /**
     * Retrieves the old list for checking against.
     *
     * @param array $oldList The old list to retrieve.
     * @return array An array of column names from the old list.
     */
    private function getOldListCheck($oldList)
    {
        $oldListCheck = array();
        foreach ($oldList as $elem) {
            $oldListCheck[] = $elem[self::NAME];
        }
        return $oldListCheck;
    }

    /**
     * Merges a new list of columns into the existing columns, ensuring uniqueness.
     *
     * @param array $newList The new list of columns to merge.
     * @return self Returns the current instance for method chaining.
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
     * Merges a new list of join columns into the existing join columns, ensuring uniqueness.
     *
     * @param array $newList The new list of join columns to merge.
     * @return self Returns the current instance for method chaining.
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
     * Merges a new list of primary keys into the existing primary keys, ensuring uniqueness.
     *
     * @param array $newList The new list of primary keys to merge.
     * @return self Returns the current instance for method chaining.
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
     * Merges a new list of auto-increment keys into the existing auto-increment keys, ensuring uniqueness.
     *
     * @param array $newList The new list of auto-increment keys to merge.
     * @return self Returns the current instance for method chaining.
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
     * Merges a new list of default value keys into the existing default value keys, ensuring uniqueness.
     *
     * @param array $newList The new list of default value keys to merge.
     * @return self Returns the current instance for method chaining.
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
     * Merges a new list of not-null columns into the existing not-null columns, ensuring uniqueness.
     *
     * @param array $newList The new list of not-null columns to merge.
     * @return self Returns the current instance for method chaining.
     */
    public function mergeNotNullColumns($newList)
    {
        $tmp = $this->notNullColumns;
        $oldListCheck = $this->getOldListCheck($this->notNullColumns);
        $prevColumName = "";
        $listToInsert = array();
        foreach ($newList as $elem) {
            if (!in_array($elem[self::NAME], $oldListCheck)) {
                $listToInsert[] = array(self::ELEMENT => $elem, self::PREV_NAME => $prevColumName);
            }
            $prevColumName = $elem[self::NAME];
        }
        // Merging logic for not-null columns can be added here
        $this->notNullColumns = $tmp;
        return $this;
    }
}
