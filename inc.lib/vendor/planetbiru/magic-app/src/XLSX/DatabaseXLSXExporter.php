<?php

namespace MagicApp\XLSX;

use MagicObject\Exceptions\InvalidDatabaseConfiguration;
use PDO;

/**
 * Class DatabaseXLSXExporter
 *
 * Responsible for exporting data from a database into XLSX format.
 * Each table will be saved as a separate sheet in the Excel file.
 *
 * Supported drivers: MySQL, PostgreSQL, SQLite, SQL Server
 */
class DatabaseXLSXExporter
{
    /**
     * @var PDO Database connection instance
     */
    protected $pdo;

    /**
     * @var XLSXWriter XLSX writer instance
     */
    protected $writer;

    /**
     * @var string Optional prefix to prepend to sheet names
     */
    protected $sheetPrefix = '';

    /**
     * Constructor.
     *
     * @param PDO $pdo PDO connection to the database
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->writer = new XLSXWriter();
    }

    /**
     * Set sheet prefix.
     *
     * @param string $prefix Prefix string for sheet names
     * @return self Returns the current instance for method chaining
     */
    public function setSheetPrefix($prefix)
    {
        $this->sheetPrefix = $prefix;
        return $this;
    }

    /**
     * Export all tables to an XLSX file.
     *
     * @param string $fileName The filename to download
     */
    public function export($fileName)
    {
        $tables = $this->getTableList();

        foreach ($tables as $tableName) {
            $this->exportTable($tableName);
        }

        // Output headers
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $this->writer->writeToStdOut();
    }

    /**
     * Export a single table to a sheet in the XLSX file.
     *
     * @param string $tableName Name of the table
     */
    protected function exportTable($tableName)
    {
        $columns = $this->getTableColumns($tableName);
        $this->writer->writeSheetHeader(
            $this->sheetPrefix . $tableName,
            $columns
        );

        $stmt = $this->pdo->query("SELECT * FROM \"$tableName\"");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->writer->writeSheetRow(
                $this->sheetPrefix . $tableName,
                array_values($row)
            );
        }
    }

    /**
     * Normalize the PDO driver name into a known alias.
     *
     * @param string $driverName Raw PDO driver name
     * @return string Normalized driver identifier
     * @throws InvalidDatabaseConfiguration
     */
    protected function normalizeDriver($driverName) // NOSONAR
    {
        $driverName = strtolower(trim($driverName));

        switch ($driverName) {
            case 'mysql':
            case 'mariadb':
            case 'mysqlnd':
                return 'mysql';

            case 'pgsql':
            case 'postgresql':
                return 'pgsql';

            case 'sqlite':
            case 'sqlite3':
                return 'sqlite';

            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                return 'sqlsrv';

            default:
                throw new InvalidDatabaseConfiguration("Unsupported or unknown PDO driver: " . $driverName);
        }
    }

    /**
     * Get the list of tables from the connected database.
     *
     * @return array List of table names
     * @throws InvalidDatabaseConfiguration
     */
    protected function getTableList() // NOSONAR
    {
        $driver = $this->normalizeDriver($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));

        switch ($driver) {
            case 'mysql':
                return $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            case 'pgsql':
                return $this->pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchAll(PDO::FETCH_COLUMN);

            case 'sqlite':
                return $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

            case 'sqlsrv':
                return $this->pdo->query("SELECT name FROM sysobjects WHERE xtype = 'U'")->fetchAll(PDO::FETCH_COLUMN);

            default:
                throw new InvalidDatabaseConfiguration("Unsupported database driver: " . $driver);
        }
    }

    /**
     * Get columns and inferred XLSX types for a given table.
     *
     * @param string $tableName Table name
     * @return array Associative array of column name => xlsx type
     * @throws InvalidDatabaseConfiguration
     */
    protected function getTableColumns($tableName)
    {
        $driver = $this->normalizeDriver($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $columnMap = array();

        switch ($driver) {
            case 'mysql':
                $stmt = $this->pdo->query("DESCRIBE `$tableName`");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columnMap[$row['Field']] = $this->mapToXlsxType($row['Type']);
                }
                break;

            case 'pgsql':
                $stmt = $this->pdo->query("
                    SELECT column_name, data_type FROM information_schema.columns 
                    WHERE table_name = '$tableName'
                ");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columnMap[$row['column_name']] = $this->mapToXlsxType($row['data_type']);
                }
                break;

            case 'sqlite':
                $stmt = $this->pdo->query("PRAGMA table_info('$tableName')");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columnMap[$row['name']] = $this->mapToXlsxType($row['type']);
                }
                break;

            case 'sqlsrv':
                $stmt = $this->pdo->query("
                    SELECT c.name, t.name AS type_name
                    FROM sys.columns c
                    JOIN sys.types t ON c.user_type_id = t.user_type_id
                    WHERE c.object_id = OBJECT_ID('$tableName')
                ");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columnMap[$row['name']] = $this->mapToXlsxType($row['type_name']);
                }
                break;

            default:
                throw new InvalidDatabaseConfiguration("Unsupported driver: " . $driver);
        }

        return $columnMap;
    }

    /**
     * Map native SQL data types to XLSX column types.
     *
     * Supports MySQL, PostgreSQL, SQLite, and SQL Server.
     *
     * @param string $type Native database column type
     * @return string XLSX type ('integer', 'date', or 'string')
     */
    protected function mapToXlsxType($type) // NOSONAR
    {
        $type = strtolower(trim($type));

        // Remove length/precision info, e.g., varchar(255), numeric(10,2)
        if (preg_match('/^([a-z ]+)/', $type, $matches)) {
            $type = trim($matches[1]);
        }

        // Normalize types
        switch ($type) // NOSONAR
        {
            // Integer types
            case 'int':
            case 'integer':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'tinyint':
            case 'serial':
            case 'bigserial':
            case 'counter':
                return 'integer';

            // Floating point / numeric
            case 'decimal':
            case 'numeric':
            case 'real':
            case 'float':
            case 'double':
            case 'double precision':
            case 'money':
            case 'smallmoney':
                return 'integer';

            // Date and time
            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'time':
            case 'timetz':
            case 'timestamptz':
            case 'smalldatetime':
                return 'date';

            // Boolean (treated as string or integer — XLSXWriter doesn't support boolean natively)
            case 'boolean':
            case 'bool':
                return 'integer';

            // Everything else
            case 'text':
            case 'varchar':
            case 'char':
            case 'nvarchar':
            case 'nchar':
            case 'bpchar':
            case 'string':
            case 'enum':
            case 'uuid':
            case 'json':
            case 'jsonb':
            case 'xml':
            case 'blob':
            case 'clob':
            case 'longtext':
            case 'mediumtext':
            case 'tinytext':
            case 'set':
                return 'string';

            default:
                // Fallback to string if unknown
                return 'string';
        }
    }

}
