<?php
namespace MagicObject\Response\Generated;

use PDO;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;

class JSONSelectOption
{
    private $data = array();

    /**
     * Constructor
     *
     * @param PicoDatabase $database
     * @param PicoDatabaseQueryBuilder $query
     * @param mixed $defautValue
     */
    public function __construct($database, $query, $defautValue)
    {
        $rows = $database->fetchAll($query, PDO::FETCH_OBJ);
        $this->updateData($rows, $defautValue);
    }

    private function updateData($rows, $defautValue = null)
    {
        if($rows != null)
        {
            foreach($rows as $key=>$row)
            {
                if($defautValue != null && $defautValue == $row->id)
                {
                    $rows[$key]->selected = true;
                }
            }
        }
        $this->data = $rows;
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}