<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;
use MagicObject\Util\Database\PicoDatabaseUtilPostgreSql;
use MagicObject\Util\Database\PicoDatabaseUtilSqlite;

/**
 * Class for generating column information from a database.
 *
 * This class provides methods to retrieve a list of columns for a specified database table.
 * 
 * @author Kamshory
 * @package MagicObject\Generator
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
        $columns = array();
        if ($database->getDatabaseType() === PicoDatabaseType::DATABASE_TYPE_MARIADB || 
            $database->getDatabaseType() === PicoDatabaseType::DATABASE_TYPE_MYSQL) 
        {
            $columns = (new PicoDatabaseUtilMySql())->getColumnList($database, $picoTableName);
        }
        else if($database->getDatabaseType() === PicoDatabaseType::DATABASE_TYPE_PGSQL)
        {
            $columns = (new PicoDatabaseUtilPostgreSql())->getColumnList($database, $picoTableName);
        }
        else if($database->getDatabaseType() === PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            $columns = (new PicoDatabaseUtilSqlite())->getColumnList($database, $picoTableName);
        }
        
        // Return an empty array if the database type is not supported
        return $columns;
    }
}
