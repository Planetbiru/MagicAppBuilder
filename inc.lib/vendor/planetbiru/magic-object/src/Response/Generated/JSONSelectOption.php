<?php
namespace MagicObject\Response\Generated;

use PDO;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;

/**
 * Class JSONSelectOption
 *
 * This class fetches data from a database and prepares it for use in JSON select options.
 * @link https://github.com/Planetbiru/MagicObject
 */
class JSONSelectOption
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * Constructor
     *
     * Initializes the JSONSelectOption with data fetched from the database.
     *
     * @param PicoDatabase $database Database connection
     * @param PicoDatabaseQueryBuilder $query Query builder for fetching data
     * @param mixed $defaultValue Default value to mark as selected
     */
    public function __construct($database, $query, $defautValue)
    {
        $rows = $database->fetchAll($query, PDO::FETCH_OBJ);
        $this->updateData($rows, $defautValue);
    }

    /**
     * Update data with selected value.
     *
     * Marks the row with the specified default value as selected.
     *
     * @param array|null $rows Fetched rows from the database
     * @param mixed $defaultValue Default value to mark as selected
     */
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
     *
     * @return array Data with selection information
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Convert the data to JSON format
     *
     * @return string JSON representation of the data
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}