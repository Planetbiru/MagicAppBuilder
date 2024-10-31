<?php

namespace MagicObject\Database;

/**
 * Class representing different database types.
 *
 * This class provides constants for various database types supported by the MagicObject framework.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseType
{
    /**
     * MySQL database type.
     */
    const DATABASE_TYPE_MYSQL = "mysql";

    /**
     * MariaDB database type.
     */
    const DATABASE_TYPE_MARIADB = "mariadb";

    /**
     * PostgreSQL database type.
     */
    const DATABASE_TYPE_POSTGRESQL = "postgresql";

    /**
     * SQLite database type.
     */
    const DATABASE_TYPE_SQLITE = "sqlite";
}
