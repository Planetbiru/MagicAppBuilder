<?php

namespace MagicObject\Generator;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

/**
 * Column generator
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoColumnGenerator
{
    /**
     * Get column list
     *
     * @param PicoDatabase $database Database connection
     * @param string $picoTableName Database name
     * @return array
     */
    public static function getColumnList($database, $picoTableName)
    {
        if($database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MARIADB || $database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MYSQL)
        {
            return PicoDatabaseUtilMySql::getColumnList($database, $picoTableName);
        }
        else
        {
            return array();
        }
    }
}