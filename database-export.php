<?php

/**
 * Class DatabaseExporter
 * 
 * A class to export both the structure (CREATE TABLE) and data (INSERT INTO) of all tables 
 * from different database types: SQLite, MySQL, and PostgreSQL using PDO.
 */
class DatabaseExporter
{
    /**
     * Database connection (SQLite, MySQL, or PostgreSQL)
     * 
     * @var PDO
     */
    private $db; 

    /**
     * Buffer to store the SQL export data
     * 
     * @var string
     */
    private $outputBuffer; 

    /**
     * Database type (sqlite, mysql, or postgresql)
     * 
     * @var string
     */
    private $dbType; 

    /**
     * DatabaseExporter constructor.
     * 
     * Initializes the database connection based on the specified database type.
     *
     * @param string $dbType The type of the database ('sqlite', 'mysql', 'postgresql').
     * @param array  $config The database configuration.
     */
    public function __construct($dbType, $config)
    {
        $this->dbType = strtolower($dbType);
        $this->outputBuffer = '';

        switch ($this->dbType) {
            case 'sqlite':
                $dsn = "sqlite:{$config['database']}";
                $this->db = new PDO($dsn);
                break;
            case 'mysql':
                $dsn = "mysql:host={$config['host']};dbname={$config['database']}";
                $this->db = new PDO($dsn, $config['username'], $config['password']);
                break;
            case 'postgresql':
                $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";
                $this->db = new PDO($dsn, $config['username'], $config['password']);
                break;
            default:
                die("Unsupported database type: " . $this->dbType);
        }

        // Set PDO error mode to exception
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Exports both table structure (CREATE TABLE) and data (INSERT INTO).
     */
    public function export()
    {
        $this->exportTableStructure();
        $this->exportTableData();
    }

    /**
     * Exports the structure of all tables (CREATE TABLE).
     */
    private function exportTableStructure()
    {
        switch ($this->dbType) {
            case 'sqlite':
                $this->exportSQLiteTableStructure();
                break;
            case 'mysql':
                $this->exportMySQLTableStructure();
                break;
            case 'postgresql':
                $this->exportPostgreSQLTableStructure();
                break;
            default:
                break;
        }
    }

    /**
     * Exports the structure of SQLite tables (CREATE TABLE).
     */
    private function exportSQLiteTableStructure()
    {
        $result = $this->db->query('SELECT name FROM sqlite_master WHERE type="table";');
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['name'];
            $createTable = $this->db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$tableName';");
            $createTableSql = $createTable->fetch(PDO::FETCH_ASSOC)['sql'];
            $this->outputBuffer .= "$createTableSql;\n\n";
        }
    }

    /**
     * Exports the structure of MySQL tables (CREATE TABLE).
     */
    private function exportMySQLTableStructure()
    {
        $result = $this->db->query('SHOW TABLES');
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['Tables_in_' . $this->dbType];
            $createTable = $this->db->query("SHOW CREATE TABLE $tableName");
            $createTableSql = $createTable->fetch(PDO::FETCH_ASSOC)['Create Table'];
            $this->outputBuffer .= "$createTableSql;\n\n";
        }
    }

    /**
     * Exports the structure of PostgreSQL tables (CREATE TABLE).
     */
    private function exportPostgreSQLTableStructure()
    {
        $result = $this->db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';");
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['table_name'];
            $createTable = $this->db->query("SELECT pg_get_tabledef('$tableName');");
            $createTableSql = $createTable->fetch(PDO::FETCH_ASSOC)['pg_get_tabledef'];
            $this->outputBuffer .= "$createTableSql;\n\n";
        }
    }

    /**
     * Exports the data (INSERT INTO) of all tables.
     */
    private function exportTableData()
    {
        switch ($this->dbType) {
            case 'sqlite':
                $this->exportSQLiteTableData();
                break;
            case 'mysql':
                $this->exportMySQLTableData();
                break;
            case 'postgresql':
                $this->exportPostgreSQLTableData();
                break;
            default:
                break;
        }
    }

    /**
     * Exports the data (INSERT INTO) of SQLite tables.
     */
    private function exportSQLiteTableData()
    {
        $result = $this->db->query('SELECT name FROM sqlite_master WHERE type="table";');
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['name'];
            $rows = $this->db->query("SELECT * FROM $tableName;");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_keys($row);
                $values = array_values($row);
                $columnsList = implode(", ", $columns);
                $valuesList = implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
                }, $values));
                $insertSql = "INSERT INTO $tableName ($columnsList) VALUES ($valuesList);\n";
                $this->outputBuffer .= $insertSql;
            }
            $this->outputBuffer .= "\n";
        }
    }

    /**
     * Exports the data (INSERT INTO) of MySQL tables.
     */
    private function exportMySQLTableData()
    {
        $result = $this->db->query('SHOW TABLES');
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['Tables_in_' . $this->dbType];
            $rows = $this->db->query("SELECT * FROM $tableName");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_keys($row);
                $values = array_values($row);
                $columnsList = implode(", ", $columns);
                $valuesList = implode(", ", array_map(function ($value) {
                    return "'" . $this->db->quote($value) . "'";
                }, $values));
                $insertSql = "INSERT INTO $tableName ($columnsList) VALUES ($valuesList);\n";
                $this->outputBuffer .= $insertSql;
            }
            $this->outputBuffer .= "\n";
        }
    }

    /**
     * Exports the data (INSERT INTO) of PostgreSQL tables.
     */
    private function exportPostgreSQLTableData()
    {
        $result = $this->db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';");
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['table_name'];
            $rows = $this->db->query("SELECT * FROM $tableName");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_keys($row);
                $values = array_values($row);
                $columnsList = implode(", ", $columns);
                $valuesList = implode(", ", array_map(function ($value) {
                    return "'" . $this->db->quote($value) . "'";
                }, $values));
                $insertSql = "INSERT INTO $tableName ($columnsList) VALUES ($valuesList);\n";
                $this->outputBuffer .= $insertSql;
            }
            $this->outputBuffer .= "\n";
        }
    }


    /**
     * Returns the exported SQL data from the buffer.
     * 
     * @return string The SQL export data.
     */
    public function getExportData()
    {
        return $this->outputBuffer;
    }

    /**
     * Saves the exported SQL data to a specified file.
     * 
     * @param string $filePath The path where the exported SQL file should be saved.
     */
    public function saveToFile($filePath)
    {
        file_put_contents($filePath, $this->outputBuffer);
    }

    /**
     * Closes the database connection.
     */
    public function close()
    {
        $this->db = null; // For PDO, we can just set the connection to null
    }

    /**
     * Get buffer to store the SQL export data
     *
     * @return  string
     */ 
    public function getOutputBuffer()
    {
        return $this->outputBuffer;
    }
}

// Usage example for MySQL
$mysqlConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'your_database'
];

/*
$exporter = new DatabaseExporter('mysql', $mysqlConfig);
$exporter->export();
$exporter->saveToFile('mysql_export.sql');
$exporter->close();
*/

// Usage example for PostgreSQL
$pgsqlConfig = [
    'host' => 'localhost',
    'username' => 'postgres',
    'password' => 'your_password',
    'database' => 'your_database'
];

/*
$exporter = new DatabaseExporter('postgresql', $pgsqlConfig);
$exporter->export();
$exporter->saveToFile('pgsql_export.sql');
$exporter->close();
*/

// Usage example for SQLite
$sqliteConfig = [
    'database' => 'D:\\xampp\\htdocs\\MagicAppBuilder\\Chinook_Sqlite.sqlite'
];
$exporter = new DatabaseExporter('sqlite', $sqliteConfig);
$exporter->export();
header("Content-type: text/plain");
echo $exporter->getOutputBuffer();
//$exporter->saveToFile('sqlite_export.sql');
$exporter->close();

