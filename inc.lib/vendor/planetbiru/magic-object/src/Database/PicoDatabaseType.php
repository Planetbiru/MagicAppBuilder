<?php

namespace MagicObject\Database;

/**
 * Class PicoDatabaseType
 *
 * Defines constants for various database types supported by the MagicObject framework.
 * This class provides a centralized reference to these types, enhancing code clarity
 * and maintainability.
 *
 * Supported database types include:
 * - MySQL
 * - MariaDB
 * - PostgreSQL
 * - SQLite
 *
 * @package MagicObject\Database
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseType
{
    /**
     * Constant for MySQL database type.
     *
     * @var string
     */
    const DATABASE_TYPE_MYSQL = "mysql";

    /**
     * Constant for MariaDB database type.
     *
     * @var string
     */
    const DATABASE_TYPE_MARIADB = "mariadb";

    /**
     * Constant for PostgreSQL database type.
     *
     * @var string
     */
    const DATABASE_TYPE_POSTGRESQL = "postgresql";

    /**
     * Constant for SQLite database type.
     *
     * @var string
     */
    const DATABASE_TYPE_SQLITE = "sqlite";
}
