<?php

namespace MagicObject\Util\Database;

use MagicObject\Database\PicoDatabaseType;

/**
 * Class PicoColumnTypeConverter
 * 
 * Responsible for converting column data types between different database systems:
 * MySQL/MariaDB, PostgreSQL, and SQLite.
 * 
 * This class provides static utility methods to convert a column type from a source database
 * type to a target database type by applying appropriate mapping logic for each supported platform.
 * 
 * @package MagicObject\Util\Database
 */
class PicoColumnTypeConverter
{
    /**
     * Converts a column data type from one database type to another.
     *
     * @param string $type     The original column data type.
     * @param string $typeFrom The source database type (e.g., 'mysql', 'pgsql', 'sqlite').
     * @param string $typeTo   The target database type.
     * 
     * @return string          The converted column data type.
     */
    public static function convertType($type, $typeFrom, $typeTo) // NOSONAR
    {
        // Return original type if source and target are the same
        if ($typeFrom == $typeTo) {
            return $type;
        }

        // Convert from MySQL/MariaDB
        if ($typeFrom == PicoDatabaseType::DATABASE_TYPE_MARIADB || $typeFrom == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            return self::convertTypeFromMySql($type, $typeTo);
        }
        // Convert from PostgreSQL
        else if ($typeFrom == PicoDatabaseType::DATABASE_TYPE_PGSQL || $typeFrom == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) {
            return self::convertTypeFromPortgreSql($type, $typeTo);
        }
        // Convert from SQLite
        else if ($typeFrom == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
            return self::convertTypeFromSqlite($type, $typeTo);
        }

        // Default: return original type if no match
        return $type;
    }

    /**
     * Converts a column type from MySQL/MariaDB to another database type.
     *
     * @param string $type     The MySQL column data type.
     * @param string $typeTo   The target database type.
     * 
     * @return string          The converted column data type.
     */
    public static function convertTypeFromMySql($type, $typeTo)
    {
        if ($typeTo == PicoDatabaseType::DATABASE_TYPE_PGSQL || $typeTo == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) {
            $type = (new PicoDatabaseUtilMySql())->convertMySqlToPostgreSql($type);
        } else if ($typeTo == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
            $type = (new PicoDatabaseUtilSqlite())->getColumnType($type);
        }
        return $type;
    }

    /**
     * Converts a column type from PostgreSQL to another database type.
     * First converts it to MySQL, then delegates to MySQL conversion.
     *
     * @param string $type     The PostgreSQL column data type.
     * @param string $typeTo   The target database type.
     * 
     * @return string          The converted column data type.
     */
    public static function convertTypeFromPortgreSql($type, $typeTo)
    {
        // Convert PostgreSQL type to MySQL as an intermediate step
        $type = (new PicoDatabaseUtilBase())->convertPostgreSqlToMySql($type);

        // Convert from MySQL to the final target type
        return self::convertTypeFromMySql($type, $typeTo);
    }

    /**
     * Converts a column type from SQLite to another database type.
     * First converts it to MySQL, then delegates to MySQL conversion.
     *
     * @param string $type     The SQLite column data type.
     * @param string $typeTo   The target database type.
     * 
     * @return string          The converted column data type.
     */
    public static function convertTypeFromSqlite($type, $typeTo)
    {
        // Convert SQLite type to MySQL as an intermediate step
        $type = (new PicoDatabaseUtilSqlite())->sqliteToMysqlType($type);

        // Convert from MySQL to the final target type
        return self::convertTypeFromMySql($type, $typeTo);
    }
}
