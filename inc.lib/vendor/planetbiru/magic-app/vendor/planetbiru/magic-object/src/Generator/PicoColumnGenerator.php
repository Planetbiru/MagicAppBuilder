<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

/**
 * Class for generating column information from a database.
 *
 * This class provides methods to retrieve a list of columns for a specified database table.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoColumnGenerator
{
    /**
     * Get the list of columns from a specified database table.
     *
     * @param PicoDatabase $database The database connection instance.
     * @param string $picoTableName The name of the table to retrieve columns from.
     * @return array An array of column names or an empty array if not applicable.
     */
    public static function getColumnList($database, $picoTableName)
    {
        // Check the database type and retrieve column list accordingly
        if ($database->getDatabaseType() === PicoDatabaseType::DATABASE_TYPE_MARIADB || 
            $database->getDatabaseType() === PicoDatabaseType::DATABASE_TYPE_MYSQL) 
        {
            return PicoDatabaseUtilMySql::getColumnList($database, $picoTableName);
        }
        
        // Return an empty array if the database type is not supported
        return array();
    }
}
