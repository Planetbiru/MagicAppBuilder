<?php

namespace MagicObject\Database;

use PDO;
use PDOException;

/**
 * Class PicoSqlite
 *
 * A simple wrapper for SQLite database operations using PDO.
 */
class PicoSqlite extends PicoDatabase
{
    const LOGIC_AND = " and ";
    
    /**
     * Database file path
     *
     * @var string
     */
    private $databaseFilePath;

    /**
     * Constructor to initialize the SQLite database connection.
     *
     * @param string $databaseFilePath The path to the SQLite database file.
     * @param callable|null $callbackExecuteQuery Callback for executing modifying queries. Parameter 1 is SQL, parameter 2 is one of query type (PicoDatabase::QUERY_INSERT, PicoDatabase::QUERY_UPDATE, PicoDatabase::QUERY_DELETE, PicoDatabase::QUERY_TRANSACTION).
     * @param callable|null $callbackDebugQuery Callback for debugging queries. Parameter 1 is SQL.
     * @throws PDOException if the connection fails.
     */
    public function __construct($databaseFilePath, $callbackExecuteQuery = null, $callbackDebugQuery = null) {
        $this->databaseFilePath = $databaseFilePath;

        if ($callbackExecuteQuery !== null && is_callable($callbackExecuteQuery)) {
            $this->callbackExecuteQuery = $callbackExecuteQuery;
        }

        if ($callbackDebugQuery !== null && is_callable($callbackDebugQuery)) {
            $this->callbackDebugQuery = $callbackDebugQuery;
        }

        $this->databaseType = PicoDatabaseType::DATABASE_TYPE_SQLITE;
    }

    /**
     * Connect to the database.
     *
     * @param bool $withDatabase Flag to select the database when connected.
     * @return bool True if the connection is successful, false if it fails.
     */
    public function connect($withDatabase = true)
    {
        $connected = false;
        try {
            $this->databaseConnection = new PDO("sqlite:" . $this->databaseFilePath);
            $this->databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connected = true;
            $this->connected = true;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        return $connected;
    }

    /**
     * Check if a table exists in the database.
     *
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     */
    public function tableExists($tableName)
    {
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name=:tableName";
        $stmt = $this->databaseConnection->prepare($query);
        $stmt->bindValue(':tableName', $tableName);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Create a new table in the database.
     *
     * @param string $tableName The name of the table to create.
     * @param string[] $columns An array of columns in the format 'column_name TYPE'.
     * @return int|false Returns the number of rows affected or false on failure.
     */
    public function createTable($tableName, $columns) {
        $columnsStr = implode(", ", $columns);
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columnsStr)";
        return $this->databaseConnection->exec($sql);
    }

    /**
     * Insert a new record into the specified table.
     *
     * @param string $tableName The name of the table to insert into.
     * @param array $data An associative array of column names and values to insert.
     * @return bool Returns true on success or false on failure.
     */
    public function insert($tableName, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
        $stmt = $this->databaseConnection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Select records from the specified table with optional conditions.
     *
     * @param string $tableName The name of the table to select from.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return array Returns an array of fetched records as associative arrays.
     */
    public function select($tableName, $conditions = array()) {
        $sql = "SELECT * FROM $tableName";
        if (!empty($conditions)) {
            $conditionStr = implode(self::LOGIC_AND, array_map(function($key) {
                return "$key = :$key";
            }, array_keys($conditions)));
            $sql .= " WHERE $conditionStr";
        }

        $stmt = $this->databaseConnection->prepare($sql);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update existing records in the specified table based on conditions.
     *
     * @param string $tableName The name of the table to update.
     * @param array $data An associative array of column names and new values.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return bool Returns true on success or false on failure.
     */
    public function update($tableName, $data, $conditions) {
        $dataStr = implode(", ", array_map(function($key) {
            return "$key = :$key";
        }, array_keys($data)));

        $conditionStr = implode(self::LOGIC_AND, array_map(function($key) {
            return "$key = :$key";
        }, array_keys($conditions)));

        $sql = "UPDATE $tableName SET $dataStr WHERE $conditionStr";
        $stmt = $this->databaseConnection->prepare($sql);

        foreach (array_merge($data, $conditions) as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Delete records from the specified table based on conditions.
     *
     * @param string $tableName The name of the table to delete from.
     * @param array $conditions An associative array of conditions for the WHERE clause.
     * @return bool Returns true on success or false on failure.
     */
    public function delete($tableName, $conditions) {
        $conditionStr = implode(self::LOGIC_AND, array_map(function($key) {
            return "$key = :$key";
        }, array_keys($conditions)));

        $sql = "DELETE FROM $tableName WHERE $conditionStr";
        $stmt = $this->databaseConnection->prepare($sql);

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }
    

}
