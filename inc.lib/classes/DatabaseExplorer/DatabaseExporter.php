<?php

namespace DatabaseExplorer;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseConverter;
use PDO;

/**
 * Class DatabaseExporter
 * 
 * A class to export both the structure (CREATE TABLE) and data (INSERT INTO) of all tables 
 * from different database types: SQLite, MySQL, and PostgreSQL using PDO.
 * 
 * @package DatabaseExplorer
 */
class DatabaseExporter // NOSONAR
{
    /**
     * Constant representing the SQLite database type.
     *
     * This constant is used to identify the SQLite database type in the application.
     */
    const DATABASE_TYPE_SQLITE = 'sqlite';

    /**
     * Constant representing the MySQL database type.
     *
     * This constant is used to identify the MySQL database type in the application.
     */
    const DATABASE_TYPE_MYSQL = 'mysql';

    /**
     * Constant representing the MariaDB database type.
     *
     * This constant is used to identify the MariaDB database type in the application.
     */
    const DATABASE_TYPE_MARIADB = 'mariadb';

    /**
     * Constant representing the PostgreSQL database type.
     *
     * This constant is used to identify the PostgreSQL database type in the application.
     */
    const DATABASE_TYPE_PGSQL = 'pgsql';
    
    const NEW_LINE = "\r\n";
    
    const DOUBLE_NEW_LINE = "\r\n\r\n";

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
     * The name of the database.
     *
     * @var string
     */
    private $dbName;

    /**
     * Constructor for DatabaseExporter.
     *
     * Initializes the DatabaseExporter instance with the necessary database
     * connection details and sets up the output buffer.
     *
     * @param string $databaseType The type of the database, expected to be one of the constants from `PicoDatabaseType` (e.g., `PicoDatabaseType::DATABASE_TYPE_SQLITE`).
     * @param PDO $pdo An active PDO instance connected to the database.
     * @param string|null $dbName The name of the database.
     * @param string|null $dbSchema The name of the schema, primarily used for PostgreSQL.
     */
    public function __construct($databaseType, $pdo, $dbName = null)
    {
        $this->dbName = $dbName;
        $this->outputBuffer = '';
        $this->db = $pdo;
        $databaseType = $this->fixDatabaseType($databaseType);
        $this->dbType = strtolower($databaseType);
    }

    /**
     * Ensures that the given database type is set.
     * If not provided, it retrieves the database driver name from the PDO instance
     * and maps it to a known database type using PicoDatabase::getDbType().
     *
     * @param string|null $databaseType The provided database type or null.
     * @return string The resolved database type.
     */
    private function fixDatabaseType($databaseType)
    {
        if(!isset($databaseType) || empty($databaseType))
        {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $databaseType = PicoDatabase::getDbType($driver);
        }
        return $databaseType;
    }

    /**
     * Exports both the table structure (CREATE TABLE) and data (INSERT INTO).
     * 
     * This method exports the structure of the tables (CREATE TABLE statements)
     * and their data (INSERT INTO statements) for the specified tables. If no
     * tables are specified, all tables in the database will be exported.
     *
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param string $schema The schema name to export from. Defaults to 'public'.
     * @param int $batchSize The number of rows per INSERT query (default is 100).
     * @param int $maxQuerySize The maximum allowed size of the query (default is 524288 bytes).
     */
    public function exportData($tables = null, $schema = 'public', $batchSize = 100, $maxQuerySize = 524288)
    {
        $this->outputBuffer .= "-- Database structure\r\n\r\n";
        $this->exportTableStructure($tables, $schema);
        $this->outputBuffer .= "-- Database content\r\n";
        $this->exportTableData($tables, $schema, null, $batchSize, $maxQuerySize);
    }

    /**
     * Exports only the structure (CREATE TABLE) of the specified tables.
     * 
     * This method generates the CREATE TABLE statements for the specified tables.
     * If no tables are specified, all tables in the database will be exported.
     *
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param string $schema The schema name to export from. Defaults to 'public'.
     */
    public function exportStructure($tables = null, $schema = 'public', $targetDatabaseType = null)
    {
        $targetDatabaseType = $this->fixDatabaseType($targetDatabaseType);
        $targetDatabaseType = $this->normalizeDatabaseType($targetDatabaseType);
        $this->outputBuffer .= "-- Database structure\r\n\r\n";
        $this->exportTableStructure($tables, $schema, $targetDatabaseType);
    }

    /**
     * Exports the structure of all tables (CREATE TABLE).
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param string $schema The schema where the tables reside (default is 'public').
     */
    public function exportTableStructure($tables, $schema = 'public', $targetDatabaseType = null)
    {
        $targetDatabaseType = $this->fixDatabaseType($targetDatabaseType);
        $targetDatabaseType = $this->normalizeDatabaseType($targetDatabaseType);
        $converter = new PicoDatabaseConverter();
        $targetDatabaseType = $converter->normalizeDialect($targetDatabaseType);
        switch ($this->dbType) {
            case PicoDatabaseType::DATABASE_TYPE_SQLITE:
                $this->exportSQLiteTableStructure($tables, $targetDatabaseType, $converter);
                break;
            case PicoDatabaseType::DATABASE_TYPE_MYSQL:
            case PicoDatabaseType::DATABASE_TYPE_MARIADB:
                $this->exportMySQLTableStructure($tables, $targetDatabaseType, $converter);
                break;
            case PicoDatabaseType::DATABASE_TYPE_PGSQL:
                $this->exportPostgreSQLTableStructure($tables, $schema, $targetDatabaseType, $converter);
                break;
            default:
                break;
        }
    }

    /**
     * Determines if a table should be exported based on the provided list.
     *
     * @param string $table The table to check.
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @return bool True if the table should be exported, false otherwise.
     */
    private function toBeExported($table, $tables)
    {
        // Trim whitespace from the table name and the provided table list
        $table = trim($table);
        
        if (!isset($tables) || !is_array($tables) || empty($tables)) {
            return true;
        }
        
        // Compare trimmed table names (case insensitive)
        return in_array(strtolower($table), array_map(function($t) { return strtolower(trim($t)); }, $tables));
    }

    /**
     * Exports the structure of SQLite tables (CREATE TABLE).
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     */
    private function exportSQLiteTableStructure($tables, $targetDatabaseType, $converter)
    {
        $result = $this->db->query('SELECT name FROM sqlite_master WHERE type="table";');
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['name'];
            if($this->toBeExported($tableName, $tables))
            {
                $createTable = $this->db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$tableName';");
                $createTableSql = $createTable->fetch(PDO::FETCH_ASSOC)['sql'];
                $createTableSql = $this->formatSQL($createTableSql);
                $createTableSql = $converter->trimColumnType($createTableSql);
                $createTableSql = str_replace(array("[", "]"), "", $createTableSql);
                
                $createTableSql = $this->ensureCreateTableIfNotExists($createTableSql, $targetDatabaseType);
                $createTableSql = $this->convertDatabaseStructure($createTableSql, $targetDatabaseType, $converter);

                $this->outputBuffer .= $createTableSql.self::DOUBLE_NEW_LINE;
            }
        }
    }

    /**
     * Exports the structure of MySQL tables (CREATE TABLE).
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     */
    private function exportMySQLTableStructure($tables, $targetDatabaseType, $converter)
    {
        $result = $this->db->query(ConstantText::SHOW_TABLES);
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = array_values($table)[0];
            if($this->toBeExported($tableName, $tables))
            {
                $createTable = $this->db->query("SHOW CREATE TABLE $tableName");
                $createTableSql = $createTable->fetch(PDO::FETCH_ASSOC)['Create Table'];
                $createTableSql = $this->ensureCreateTableIfNotExists($createTableSql, $targetDatabaseType);

                $createTableSql = $this->convertDatabaseStructure($createTableSql, $targetDatabaseType, $converter);

                $this->outputBuffer .= $createTableSql.self::DOUBLE_NEW_LINE;
            }
        }
    }

    /**
     * Exports the structure of PostgreSQL tables (CREATE TABLE).
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param string $schema The schema where the tables reside (default is 'public').
     */
    private function exportPostgreSQLTableStructure($tables, $schema = 'public', $targetDatabaseType = null, $converter) // NOSONAR
    {
        if(!isset($targetDatabaseType))
        {
            $targetDatabaseType = $this->dbType;
        }

        // Query to get all table names in PostgreSQL for the specified schema
        $result = $this->db->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = '$schema';
        ");
        
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['table_name'];
            if ($this->toBeExported($tableName, $tables)) {
                $primaryKeyColumns = [];
                $foreignKeys = [];

                $columns = $this->getPostgreSQLColumn($tableName, $schema);
                $tableName = str_replace("'", "", $tableName);
                $schema = str_replace("'", "", $schema);

                // Query to get primary key columns
                $primaryKeyQuery = $this->db->query("
                    SELECT 
                        tc.constraint_name, 
                        tc.table_name, 
                        kcu.column_name
                    FROM 
                        information_schema.table_constraints AS tc 
                    INNER JOIN 
                        information_schema.key_column_usage AS kcu 
                    ON 
                        tc.constraint_name = kcu.constraint_name
                    WHERE 
                        tc.constraint_type = 'PRIMARY KEY'
                        AND tc.table_name = '$tableName'
                        AND tc.table_schema = '$schema';
                ");

                while ($primaryKey = $primaryKeyQuery->fetch(PDO::FETCH_ASSOC)) {
                    $primaryKeyColumns[] = $primaryKey['column_name'];
                }

                // Add PRIMARY KEY constraint if applicable
                if (!empty($primaryKeyColumns)) {
                    $columns[] = 'PRIMARY KEY (' . implode(', ', $primaryKeyColumns) . ')';
                }

                // Query to get foreign keys
                $foreignKeyQuery = $this->db->query("
                    SELECT 
                        tc.constraint_name, 
                        tc.table_name, 
                        kcu.column_name, 
                        ccu.table_name AS referenced_table,
                        ccu.column_name AS referenced_column
                    FROM 
                        information_schema.table_constraints AS tc 
                    INNER JOIN 
                        information_schema.key_column_usage AS kcu 
                    ON 
                        tc.constraint_name = kcu.constraint_name
                    INNER JOIN 
                        information_schema.constraint_column_usage AS ccu 
                    ON 
                        tc.constraint_name = ccu.constraint_name
                    WHERE 
                        tc.constraint_type = 'FOREIGN KEY'
                        AND tc.table_name = '$tableName'
                        AND tc.table_schema = '$schema';
                ");

                while ($foreignKey = $foreignKeyQuery->fetch(PDO::FETCH_ASSOC)) {
                    $foreignKeys[] = 'FOREIGN KEY (' . $foreignKey['column_name'] . ') REFERENCES ' . $foreignKey['referenced_table'] . ' (' . $foreignKey['referenced_column'] . ')';
                }

                // Add FOREIGN KEY constraint if applicable
                if (!empty($foreignKeys)) {
                    $columns = array_merge($columns, $foreignKeys);
                }

                // Construct CREATE TABLE statement
                $createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (\r\n\t" . implode(", \r\n\t", $columns) . "\r\n);\n";

                $createTableSql = $this->convertDatabaseStructure($createTableSql, $targetDatabaseType, $converter);

                $this->outputBuffer .= $createTableSql.self::DOUBLE_NEW_LINE;
            }
        }
    }

    /**
     * Converts a CREATE TABLE SQL statement from a source database type to a target database type.
     *
     * This function utilizes a PicoDatabaseConverter to translate the SQL syntax.
     * If the target database type is not specified, empty, or the source and target
     * database types are considered equal, the original SQL statement is returned without conversion.
     *
     * @param string $createTableSql The CREATE TABLE SQL statement to convert.
     * @param string $targetDatabaseType The type of the target database (e.g., 'mysql', 'postgresql', 'sqlite').
     * @return string The converted CREATE TABLE SQL statement, or the original if no conversion is needed/possible.
     * @param PicoDatabaseConverter $converter An instance of a database converter responsible for type conversions.
     */
    public function convertDatabaseStructure($createTableSql, $targetDatabaseType, $converter)
    {
        if (!isset($targetDatabaseType) || empty($targetDatabaseType) || $this->equalsDatabaseType($this->dbType, $targetDatabaseType)) {
            return $createTableSql.";";
        } else {
            return $converter->translateCreateTable($createTableSql, $this->dbType, $targetDatabaseType);
        }
    }

    /**
     * Checks if two database types are considered equal after normalization.
     *
     * This function normalizes common database type aliases (e.g., 'mysql' and 'mariadb' both become 'mysql')
     * to ensure consistent comparison.
     *
     * @param string $sourceDatabaseType The first database type to compare.
     * @param string $targetDatabaseType The second database type to compare.
     * @return bool True if the normalized database types are equal, false otherwise.
     */
    public function equalsDatabaseType($sourceDatabaseType, $targetDatabaseType)
    {
        return $this->normalizeDatabaseType($sourceDatabaseType) === $this->normalizeDatabaseType($targetDatabaseType);
    }

    /**
     * Normalizes a given database type string to a standard form.
     *
     * This private helper function handles common aliases for database types,
     * ensuring that comparisons are consistent regardless of the input string's specific casing or alias.
     * For example, 'MariaDB' and 'MySQL' both normalize to 'mysql'.
     *
     * @param string $type The database type string to normalize.
     * @return string The normalized database type string.
     */
    private function normalizeDatabaseType($type) // NOSONAR
    {
        return (new PicoDatabaseConverter())->normalizeDialect($type);
    }

    /**
     * Ensures that the given CREATE TABLE query includes "IF NOT EXISTS" after "CREATE TABLE" and removes unnecessary whitespaces.
     * If the query does not contain "IF NOT EXISTS", it will add it after "CREATE TABLE".
     * It also retains the rest of the query (e.g., table columns and constraints) unchanged.
     *
     * @param string $createTableQuery The SQL query that creates a table (can be unformatted or missing "IF NOT EXISTS").
     * @param string $targetDatabaseType The target DB type to handle dialect differences ('sqlserver' disables IF NOT EXISTS).
     * @return string The processed SQL query with "IF NOT EXISTS" correctly inserted and proper formatting.
     */
    private function ensureCreateTableIfNotExists($createTableQuery, $targetDatabaseType) {
        // Normalize whitespaces to single spaces for easier parsing
        $cleanQuery = preg_replace('/\s+/', ' ', trim($createTableQuery));

        $searchCreateIfNotExists = 'CREATE TABLE IF NOT EXISTS ';
        $searchCreate = 'CREATE TABLE ';

        // Find positions in case-insensitive way
        $posCreateIfNotExists = stripos($cleanQuery, $searchCreateIfNotExists);
        $posCreate = stripos($cleanQuery, $searchCreate);

        // If "CREATE TABLE" not found, return query as is
        if ($posCreate === false) {
            return $createTableQuery;
        }

        // Determine position after CREATE TABLE / CREATE TABLE IF NOT EXISTS
        if ($posCreateIfNotExists === false) {
            // Only "CREATE TABLE" found, position right after it
            $posAfterCreate = $posCreate + strlen($searchCreate);
        } else {
            // "CREATE TABLE IF NOT EXISTS" found, position right after it
            $posAfterCreate = $posCreateIfNotExists + strlen($searchCreateIfNotExists);
        }

        // Find the end of the table name - could be space or '(' after posAfterCreate
        $posSpace = strpos($cleanQuery, ' ', $posAfterCreate);
        $posParen = strpos($cleanQuery, '(', $posAfterCreate);

        if ($posSpace === false && $posParen === false) {
            // No space or '(' found, take till end
            $posTableNameEnd = strlen($cleanQuery);
        } elseif ($posSpace === false) {
            $posTableNameEnd = $posParen;
        } elseif ($posParen === false) {
            $posTableNameEnd = $posSpace;
        } else {
            $posTableNameEnd = min($posSpace, $posParen);
        }

        // Extract table name from clean query
        $tableName = substr($cleanQuery, $posAfterCreate, $posTableNameEnd - $posAfterCreate);
        $tableName = trim($tableName);

        // Find the position of the table name in the original query (starting search from CREATE TABLE position)
        $posTableNameInOriginal = stripos($createTableQuery, $tableName, $posCreate);
        if ($posTableNameInOriginal === false) {
            // fallback: just after CREATE TABLE position
            $posTableNameInOriginal = $posCreate + strlen($searchCreate);
        }

        // Extract everything after the table name in original query, preserve formatting here
        $afterTableName = substr($createTableQuery, $posTableNameInOriginal + strlen($tableName));

        // Compose new query based on target DB type
        if (strtolower($targetDatabaseType) === 'sqlserver') {
            // SQL Server does not support IF NOT EXISTS in CREATE TABLE
            // Caller should handle existence check externally, so just return normal CREATE TABLE
            $result = "CREATE TABLE $tableName" . ltrim($afterTableName);
        } else {
            // For others, ensure "IF NOT EXISTS" is present after "CREATE TABLE"
            if ($posCreateIfNotExists === false) {
                // Insert IF NOT EXISTS after CREATE TABLE
                $result = "CREATE TABLE IF NOT EXISTS $tableName" . ltrim($afterTableName);
            } else {
                // Already has IF NOT EXISTS, return original query but trimmed and normalized whitespace
                // Or rebuild normalized version:
                $result = "CREATE TABLE IF NOT EXISTS $tableName" . ltrim($afterTableName);
            }
        }

        return $result;
    }

    /**
     * Formats an SQL string to ensure consistent indentation and spacing.
     * Specifically, it:
     * - Removes excess spaces between words and around symbols.
     * - Ensures "CREATE TABLE" is properly formatted with single spaces.
     * - Preserves and correctly formats the "IF NOT EXISTS" clause, if present.
     * - Positions parentheses correctly (removes spaces before opening parenthesis).
     * - Adds a new line after the opening parenthesis for better readability.
     * - Ensures proper indentation for each column in the table definition.
     * - Adds indentation after commas between column definitions for better readability.
     * - Moves the closing parenthesis to a new line, making the structure cleaner.
     * - Adds a semicolon at the end of the SQL query if it is missing.
     *
     * @param string $sql The raw SQL string to format.
     * @return string The formatted SQL string with consistent spacing and indentation.
     */
    private function formatSQL($sql) {
        // Remove excess whitespace throughout the entire string and replace with a single space
        $sql = preg_replace('/\s+/i', ' ', $sql);

        // Trim leading and trailing spaces or newlines from the string
        $sql = trim($sql, " \r\n\t ");

        // Ensure the query ends with a semicolon
        if (substr($sql, strlen($sql) - 1, 1) != ";") {
            $sql .= ";";  // Append semicolon if missing
        }

        // Ensure "CREATE TABLE" is consistently formatted with a single space after it
        $sql = preg_replace('/\bCREATE\s+TABLE\s+/i', 'CREATE TABLE ', $sql);

        // Handle "IF NOT EXISTS" correctly, preserving the spacing and formatting if it is present
        $sql = preg_replace('/\bIF\s+NOT\s+EXISTS\s+/i', 'IF NOT EXISTS ', $sql);

        // Remove spaces before the opening parenthesis to ensure proper positioning
        $sql = preg_replace('/\s*\(/', ' (', $sql);

        // Move the closing parenthesis to the next line, remove spaces after it, and ensure proper formatting
        $sql = preg_replace('/\s*\)\s*;/', "\n)", $sql);  // Close parenthesis and semicolon on a new line

        // Add a new line and indentation after the opening parenthesis, only for the first occurrence (table definition)
        $sql = preg_replace('/\(\s*/', "(\n\t", $sql, 1);  // Insert newline and tab indentation after '('

        // Ensure columns are separated by commas followed by new lines, and each column is indented properly
        $sql = preg_replace('/,\s*/', ",\n\t", $sql);  // Add newline and indentation after commas separating columns

        // Add a new line before "CREATE TABLE" to improve readability in the final formatted string
        $sql = str_replace("CREATE TABLE", "\nCREATE TABLE", $sql);  // Insert a newline before the "CREATE TABLE" statement

        return $sql;
    }

    /**
     * Retrieves the column details for a given PostgreSQL table.
     * 
     * This method queries the `information_schema.columns` to get the column names, 
     * data types, nullable constraints, and default values for a table in PostgreSQL.
     *
     * @param string $tableName The name of the table whose columns are to be retrieved.
     * @param string $schema The schema where the table resides (default is 'public').
     * 
     * @return array An array of column definitions, including name, data type, 
     *               nullable constraints, and default values.
     */
    private function getPostgreSQLColumn($tableName, $schema = 'public')
    {
        // Query to get column details (name, data type, nullable, default)
        $createTableQuery = $this->db->query("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = '$tableName' 
            AND table_schema = '$schema';
        ");

        $columns = [];

        // Collect column definitions
        while ($column = $createTableQuery->fetch(PDO::FETCH_ASSOC)) {
            $columnDefinition = $column['column_name'] . ' ' . $column['data_type'];

            // Add NOT NULL constraint if applicable
            if (strtoupper($column['is_nullable']) == 'NO') {
                $columnDefinition .= ' NOT NULL';
            }

            // Add default value if applicable
            if ($column['column_default'] !== null) {
                $columnDefinition .= ' DEFAULT ' . $column['column_default'];
            }

            $columns[] = $columnDefinition;
        }

        return $columns;
    }

    /**
     * Exports the data (INSERT INTO) of all tables.
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param string $schema The schema where the tables reside (default is 'public').
     * @param int $batchSize The number of rows per INSERT query (default is 100).
     * @param int $maxQuerySize The maximum allowed size of the query (default is 524288 bytes).
     */
    public function exportTableData($tables, $schema = 'public', $targetDatabaseType, $batchSize = 100, $maxQuerySize = 524288) // NOSONAR
    {
        $targetDatabaseType = $this->fixDatabaseType($targetDatabaseType);
        $targetDatabaseType = $this->normalizeDatabaseType($targetDatabaseType);

        switch ($this->dbType) {
            case PicoDatabaseType::DATABASE_TYPE_SQLITE:
                $this->exportSQLiteTableData($tables, $targetDatabaseType, $batchSize, $maxQuerySize);
                break;
            case PicoDatabaseType::DATABASE_TYPE_MYSQL:
            case PicoDatabaseType::DATABASE_TYPE_MARIADB:
                $this->exportMySQLTableData($tables, $targetDatabaseType, $targetDatabaseType, $batchSize, $maxQuerySize);
                break;
            case PicoDatabaseType::DATABASE_TYPE_PGSQL:
                $this->exportPostgreSQLTableData($tables, $targetDatabaseType, $targetDatabaseType, $schema, $batchSize, $maxQuerySize);
                break;
            default:
                break;
        }
    }

    /**
     * Determines if the batch limit or query size limit has been reached.
     * 
     * @param int $nrec The number of records processed so far in the current batch.
     * @param int $batchSize The maximum number of records allowed per query.
     * @param int $querySize The current size of the query in bytes.
     * @param int $maxQuerySize The maximum allowed query size in bytes.
     *
     * @return bool Returns `true` if the batch limit or query size limit has been reached, `false` otherwise.
     */
    private function reachBatchLimit($nrec, $batchSize, $querySize, $maxQuerySize)
    {
        $nrec = $this->positiveInteger($nrec);
        $batchSize = $this->positiveInteger($batchSize);
        $querySize = $this->positiveInteger($querySize);
        $maxQuerySize = $this->positiveInteger($maxQuerySize);
        return $nrec % $batchSize == 0 || $querySize > $maxQuerySize;
    }

    /**
     * Ensures a given value is a positive integer.
     *
     * This method converts the input value to an integer. If the converted
     * integer is less than or equal to zero, it defaults the value to 1.
     * This is useful for parameters that must be positive, such as sizes or counts.
     *
     * @param mixed $value The value to convert and validate.
     * @return int A positive integer representation of the input value.
     */
    private function positiveInteger($value)
    {
        $value = intval($value);
        if($value <= 0)
        {
            $value = 1;
        }
        return $value;
    }

    /**
     * Adds a new line to the output buffer if the number of records is greater than zero.
     *
     * This method appends a carriage return and line feed (CRLF) to the `outputBuffer` property 
     * of the class if the passed `$nrec` parameter is greater than zero.
     *
     * @param int $nrec The number of records. If greater than 0, a new line will be added to the output buffer.
     */
    private function addNewLine($nrec)
    {
        if ($nrec > 0) {
            $this->outputBuffer .= self::NEW_LINE;
        }
    }

    /**
     * Constructs an SQL INSERT query with provided table name, column names, and batch values.
     *
     * This method creates an SQL INSERT statement for the provided table, columns, and values.
     * The values are inserted in batches, with each set of values separated by commas and new lines.
     *
     * @param string $tableName The name of the table to insert data into.
     * @param array $columns An array of column names to be included in the INSERT query.
     * @param array $batchValues An array of value sets (each set of values corresponding to the columns).
     *                           Each set is a string containing values to be inserted.
     *
     * @return string The constructed SQL INSERT query.
     */
    private function constructInsertQuery($tableName, $columns, $batchValues)
    {
        return "INSERT INTO $tableName (" . implode(", ", $columns) . ") VALUES \r\n" . implode(", \r\n", $batchValues) . "\r\n;\r\n";
    }

    /**
     * Exports the data (INSERT INTO) of SQLite tables.
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param int $batchSize The number of rows per INSERT query.
     * @param int $maxQuerySize The maximum allowed size of the query (in bytes).
     */
    private function exportSQLiteTableData($tables, $targetDatabaseType, $batchSize = 100, $maxQuerySize = 524288)
    {
        $converter = new PicoDatabaseConverter(); 

        $result = $this->db->query('SELECT name FROM sqlite_master WHERE type="table";');
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['name'];
            if ($this->toBeExported($tableName, $tables)) {


                $columnTypes = $this->getSQLiteColumnTypes($tableName);
                $targetColumnTypes = $this->convertColumnTypes($columnTypes, $targetDatabaseType, $converter);
                $rows = $this->db->query("SELECT * FROM $tableName;");
                $nrec = 0;
                $batchValues = [];
                $querySize = 0;

                while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_keys($row);

                    $valuesList = $this->createInsertValue($row, $converter, $targetColumnTypes, $targetDatabaseType);

                    $batchValues[] = $valuesList;
                    $nrec++;

                    // Generate the INSERT statement up to the current batch
                    $insertSql = $this->constructInsertQuery($tableName, $columns, $batchValues);
                    $querySize = strlen($insertSql);

                    // If we hit either the batch size or max query size, execute and reset
                    if ($this->reachBatchLimit($nrec, $batchSize, $querySize, $maxQuerySize)) {
                        $this->outputBuffer .= $insertSql.self::NEW_LINE;
                        $batchValues = [];  // Clear the batch for the next set
                        $querySize = 0;     // Reset query size
                    }
                }

                // Insert any remaining rows after the loop
                if (!empty($batchValues)) {
                    $insertSql = $this->constructInsertQuery($tableName, $columns, $batchValues);
                    $this->outputBuffer .= $insertSql;
                }

                $this->addNewLine($nrec);
            }
        }
    }

    /**
     * Exports the data (INSERT INTO) of MySQL tables.
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param int $batchSize The number of rows per INSERT query.
     * @param int $maxQuerySize The maximum allowed size of the query (in bytes).
     */
    private function exportMySQLTableData($tables, $targetDatabaseType, $batchSize = 100, $maxQuerySize = 524288)
    {
        $converter = new PicoDatabaseConverter(); 

        $result = $this->db->query(ConstantText::SHOW_TABLES);
        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = array_values($table)[0];
            if ($this->toBeExported($tableName, $tables)) {

                $columnTypes = $this->getMySQLColumnTypes($tableName, $this->dbName);
                $targetColumnTypes = $this->convertColumnTypes($columnTypes, $targetDatabaseType, $converter);

                $rows = $this->db->query("SELECT * FROM $tableName");
                $nrec = 0;
                $batchValues = [];
                $querySize = 0;

                while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_keys($row);

                    $valuesList = $this->createInsertValue($row, $converter, $targetColumnTypes, $targetDatabaseType);

                    $batchValues[] = $valuesList;
                    $nrec++;

                    // Generate the INSERT statement up to the current batch
                    $insertSql = $this->constructInsertQuery($tableName, $columns, $batchValues);
                    $querySize = strlen($insertSql);

                    // If we hit either the batch size or max query size, execute and reset
                    if ($this->reachBatchLimit($nrec, $batchSize, $querySize, $maxQuerySize)) {
                        $this->outputBuffer .= $insertSql.self::NEW_LINE;
                        $batchValues = [];  // Clear the batch for the next set
                        $querySize = 0;     // Reset query size
                    }
                }

                // Insert any remaining rows after the loop
                if (!empty($batchValues)) {
                    $insertSql = $this->constructInsertQuery($tableName, $columns, $batchValues);
                    $this->outputBuffer .= $insertSql;
                }

                $this->addNewLine($nrec);
            }
        }
    }

    /**
     * Exports the data (INSERT INTO) of PostgreSQL tables.
     * 
     * @param array|null $tables Table names to be exported. If null, all tables will be exported.
     * @param string $schema The schema where the tables reside (default is 'public').
     * @param int $batchSize The number of rows per INSERT query.
     * @param int $maxQuerySize The maximum allowed size of the query (in bytes).
     */
    private function exportPostgreSQLTableData($tables, $targetDatabaseType, $schema = 'public', $batchSize = 100, $maxQuerySize = 524288)
    {
        $converter = new PicoDatabaseConverter(); 

        // Query to get all table names in the specified schema
        $result = $this->db->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = '$schema';
        ");

        while ($table = $result->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $table['table_name'];
            if ($this->toBeExported($tableName, $tables)) {

                $columnTypes = $this->getPostgreSQLColumnTypes($tableName, $schema);
                $targetColumnTypes = $this->convertColumnTypes($columnTypes, $targetDatabaseType, $converter);

                // Query to get all rows from the specified table
                $rows = $this->db->query("SELECT * FROM $schema.$tableName");
                $nrec = 0;
                $batchValues = [];
                $querySize = 0;

                // Iterate through the rows and generate INSERT INTO statements
                while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {

                    // Convert data type
                    $row = $this->convertData($row, $converter, $targetColumnTypes, $targetDatabaseType);

                    $columns = array_keys($row);

                    $valuesList = $this->createInsertValue($row, $converter, $targetColumnTypes, $targetDatabaseType);

                    $batchValues[] = $valuesList;
                    
                    $nrec++;

                    // Generate the INSERT statement up to the current batch
                    $insertSql = $this->constructInsertQuery("$schema.$tableName", $columns, $batchValues);
                    $querySize = strlen($insertSql);

                    // If we hit either the batch size or max query size, execute and reset
                    if ($this->reachBatchLimit($nrec, $batchSize, $querySize, $maxQuerySize)) {
                        $this->outputBuffer .= $insertSql.self::NEW_LINE;
                        $batchValues = [];  // Clear the batch for the next set
                        $querySize = 0;     // Reset query size
                    }
                }

                // Insert any remaining rows after the loop
                if (!empty($batchValues)) {
                    $insertSql = $this->constructInsertQuery("$schema.$tableName", $columns, $batchValues);
                    $this->outputBuffer .= $insertSql;
                }

                $this->addNewLine($nrec);
            }
        }
    }

    /**
     * Get column types for an SQLite table.
     *
     * This method queries the SQLite `PRAGMA table_info` to retrieve column
     * names and their data types for the specified table.
     *
     * @param string $tableName The name of the SQLite table.
     * @return array An associative array where keys are column names and values are their SQLite data types.
     */
    private function getSQLiteColumnTypes($tableName)
    {
        $stmt = $this->db->query("PRAGMA table_info(" . $this->quoteIdentifier($tableName) . ")");
        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[$row['name']] = $row['type'];
        }
        return $columns;
    }

    /**
     * Get column types for a MySQL or MariaDB table.
     *
     * This method queries the `INFORMATION_SCHEMA.COLUMNS` view to retrieve
     * column names and their data types for the specified table and database.
     *
     * @param string $tableName The name of the MySQL/MariaDB table.
     * @param string $databaseName The name of the database (schema).
     * @return array An associative array where keys are column names and values are their MySQL/MariaDB data types.
     */
    private function getMySQLColumnTypes($tableName, $databaseName)
    {
        $sql = "
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :schema
            AND TABLE_NAME = :table
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':schema' => $databaseName,
            ':table' => $tableName
        ]);

        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[$row['COLUMN_NAME']] = $row['COLUMN_TYPE'];
        }
        return $columns;
    }

    /**
     * Get column types for a PostgreSQL table.
     *
     * This method queries the `information_schema.columns` view to retrieve
     * column names and their data types for the specified table and schema.
     *
     * @param string $tableName The name of the PostgreSQL table.
     * @param string $schemaName The name of the schema.
     * @return array An associative array where keys are column names and values are their PostgreSQL data types.
     */
    private function getPostgreSQLColumnTypes($tableName, $schemaName)
    {
        $sql = "
            SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_schema = :schema
            AND table_name = :table
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':schema' => $schemaName,
            ':table' => $tableName
        ]);

        $columns = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[$row['column_name']] = $row['data_type'];
        }
        return $columns;
    }

    /**
     * Quotes an identifier to prevent SQL injection.
     *
     * This method adds double quotes around the identifier and escapes any
     * existing double quotes within the identifier.
     *
     * @param string $identifier The identifier to quote (e.g., table name, column name).
     * @return string The quoted identifier.
     */
    private function quoteIdentifier($identifier)
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Converts an array of column types from one database type to another.
     *
     * This method iterates through the provided column types and uses the given
     * converter to translate each column's type to the specified target database type.
     *
     * @param array $columnTypes An associative array where keys are column names and values are their original data types.
     * @param string $targetDatabaseType The target database type to convert the column types to (e.g., 'mysql', 'pgsql').
     * @param PicoDatabaseConverter $converter An instance of the PicoDatabaseConverter responsible for type translation.
     * @return array An associative array with column names as keys and their converted data types as values.
     */
    private function convertColumnTypes($columnTypes, $targetDatabaseType, $converter)
    {
        $result = array();
        foreach($columnTypes as $columnName => $columnType)
        {
            $result[$columnName] = $converter->translateFieldType($columnType, $this->dbType, $targetDatabaseType);
        }
        return $result;
    }

    /**
     * Creates a string of comma-separated values enclosed in parentheses for an SQL INSERT statement.
     *
     * This method converts the data types in the provided row to the target database
     * types and then formats them as a string suitable for an SQL `INSERT` statement's
     * `VALUES` clause.
     *
     * @param array $row An associative array representing a table row, where keys are column names and values are raw data.
     * @param PicoDatabaseConverter $converter An instance of a database converter responsible for type conversions.
     * @param array $targetColumnTypes An associative array where keys are column names and values are their target SQL column types (e.g., `NVARCHAR`, `CHARACTER VARYING`).
     * @param string $targetDatabaseType The target database type (e.g., `PicoDatabaseType::DATABASE_TYPE_MYSQL`).
     * @return string A string formatted as "(`value1`, `value2`, ...)" suitable for an SQL `INSERT` statement.
     */
    private function createInsertValue($row, $converter, $targetColumnTypes, $targetDatabaseType)
    {
        return "(".implode(", ", $this->convertData($row, $converter, $targetColumnTypes, $targetDatabaseType)).")"; // Added closing parenthesis
    }

    /**
     * Converts data types in a table row based on target database types.
     *
     * This method iterates through a table row, converts raw values to PHP types
     * using a provided converter, and returns an array of the converted values.
     * Conversion only occurs if the current database type differs from the target.
     *
     * @param array $row An associative array representing a table row, where keys are column names and values are raw data.
     * @param PicoDatabaseConverter $converter An instance of a database converter responsible for type conversions.
     * @param array $targetColumnTypes An associative array where keys are column names and values are their target SQL column types (e.g., `NVARCHAR`, `CHARACTER VARYING`).
     * @param string $targetDatabaseType The target database type (e.g., `PicoDatabaseType::DATABASE_TYPE_MYSQL`).
     * @return array An indexed array of converted values. Returns the original row if no conversion is needed.
     */
    private function convertData($row, $converter, $targetColumnTypes, $targetDatabaseType)
    {
        $values = [];
        foreach ($row as $columnName => $rawValue) {
            $sqlType = $converter->getColumnType($targetColumnTypes, $columnName);
            $value = $converter->convertToPhpType($rawValue, $sqlType, $targetDatabaseType);
            $values[] = $value;
        }
        return $values;   
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
     * Set buffer to store the SQL export data
     */
    public function appendExportData($outputBuffer)
    {
        $this->outputBuffer .= $outputBuffer;

        return $this;
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

    
}
