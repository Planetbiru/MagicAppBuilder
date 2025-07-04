<?php

namespace DatabaseExplorer;

use AppBuilder\Util\DatabaseUtil;
use DataException;
use DOMDocument;
use Exception;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Util\Database\PicoDatabaseUtil;
use PDO;
use PDOException;

/**
 * Class DatabaseExplorer
 * 
 * This class provides various methods to interact with a database and render the data in HTML format.
 * It supports different database operations such as displaying table structures, data with pagination,
 * executing SQL queries, and creating forms for query execution.
 * 
 * Key functionalities include:
 * - Rendering a sidebar with available databases, schemas, and tables.
 * - Displaying the structure of tables and their data.
 * - Displaying table data with pagination.
 * - Executing a series of SQL queries and displaying their results.
 * - Creating a query execution form with pre-populated SQL queries.
 * 
 * @package DatabaseExplorer
 */
class DatabaseExplorer // NOSONAR
{
    const ERROR = "Error: ";
    
    /**
     * Splits a SQL string into individual SQL statements.
     *
     * This function takes a raw SQL string and splits it into an array of SQL statements.
     * It handles single and double quotes properly, ensuring that semicolons inside
     * quotes do not cause premature statement splits.
     *
     * @param string $sqlString The raw SQL string to be split.
     * @return array An array of individual SQL statements.
     */
    public static function splitSQL($sqlString)
    {
        $statements = [];
        $currentStatement = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;

        for ($i = 0; $i < strlen($sqlString); $i++) {
            $char = $sqlString[$i];

            // Toggle quote context
            if ($char === "'" && !$inDoubleQuote) {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote) {
                $inDoubleQuote = !$inDoubleQuote;
            }

            // Check for semicolon outside of quotes
            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
                $statements[] = $currentStatement;
                $currentStatement = '';
            } else {
                $currentStatement .= $char;
            }
        }

        // Add the last statement if exists
        if (trim($currentStatement) !== '') {
            $statements[] = $currentStatement;
        }

        return $statements;
    }

    /**
     * Displays a sidebar form to select a database.
     *
     * This function generates an HTML form for selecting a database. The form is populated
     * with available databases from the MySQL server and will submit the selected database.
     *
     * @param PDO $pdo A PDO instance connected to the MySQL server.
     * @param string $applicationId The application identifier.
     * @param string $databaseName The currently selected database name.
     * @param string $schemaname The currently selected schema name (for PostgreSQL).
     * @param object $databaseConfig The database configuration object.
     * @return string The generated HTML.
     */
    public static function showSidebarDatabases($pdo, $applicationId, $databaseName, $schemaname, $databaseConfig)
    {
        // Create the HTML elements using DOM
        $dom = new DOMDocument('1.0', 'utf-8');
        
        // Create heading
        $h3 = $dom->createElement('h3', 'Database');
        $dom->appendChild($h3);

        // Create form
        $form = $dom->createElement('form');
        $form->setAttribute('method', 'GET');
        $form->setAttribute('action', '');

        // Add hidden input for applicationId
        $input = $dom->createElement('input');
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', 'applicationId');
        $input->setAttribute('value', $applicationId);
        $form->appendChild($input);

        // Create select for databases
        $selectDb = $dom->createElement('select');
        $selectDb->setAttribute('name', 'database');
        $selectDb->setAttribute('id', 'database-select');
        $selectDb->setAttribute('onchange', 'this.form.submit()');

        // Default option
        $option = $dom->createElement('option', '-- Choose Database --');
        $option->setAttribute('value', '');
        $selectDb->appendChild($option);

        // Fetch databases
        $dbNameFromConfig = $databaseConfig ? $databaseConfig->getDatabaseName() : '';
        $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) ? 'SELECT datname FROM pg_database' : 'SHOW DATABASES';
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            $dbName = $row[0];
            $option = $dom->createElement('option', $dbName);
            $option->setAttribute('value', $dbName);
            if ($dbName === $databaseName || $dbName === $dbNameFromConfig) {
                $option->setAttribute('selected', 'selected');
            }
            $selectDb->appendChild($option);
        }
        $form->appendChild($selectDb);

        // If PostgreSQL, add schema selection
        if ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            $selectSchema = $dom->createElement('select');
            $selectSchema->setAttribute('name', 'schema');
            $selectSchema->setAttribute('id', 'schema-select');
            $selectSchema->setAttribute('onchange', 'this.form.submit()');

            // Default option for schema
            $option = $dom->createElement('option', '-- Choose Schema --');
            $option->setAttribute('value', '');
            $selectSchema->appendChild($option);

            $scNameFromConfig = $databaseConfig ? $databaseConfig->getDatabaseSchema() : '';
            $sql = "SELECT nspname FROM pg_catalog.pg_namespace WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema'; ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_NUM);
        
            foreach ($rows as $row) {
                $scName = $row[0];
                $option = $dom->createElement('option', $scName);
                $option->setAttribute('value', $scName);
                if ($scName === $schemaname || $scName === $scNameFromConfig) {
                    $option->setAttribute('selected', 'selected');
                }
                $selectSchema->appendChild($option);
            }
            $form->appendChild($selectSchema);
        }

        // Append form to DOM
        $dom->appendChild($form);

        // Output the HTML
        return $dom->saveHTML();
    }
    
    /**
     * Displays a sidebar with tables in the selected database.
     *
     * This function retrieves and displays the list of tables in the selected database.
     * The tables are displayed as links, and the selected table is highlighted in bold.
     * 
     * Supports MySQL, PostgreSQL, and SQLite databases.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $applicationId The application identifier.
     * @param string $databaseName The name of the selected database.
     * @param string $schemaName The name of the selected schema.
     * @param string $table The currently selected table.
     * @return string The generated HTML.
     */
    public static function showSidebarTablesSithGroup($pdo, $applicationId, $databaseName, $schemaName, $table)
    {
        $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $dom = new DOMDocument('1.0', 'utf-8');

        $h3 = $dom->createElement('h3', 'Table List');
        $a = $dom->createElement('a');
        $a->appendChild($h3);
        $a->setAttribute('class', 'all-table');
        $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=");
        $dom->appendChild($a);

        $ul = $dom->createElement('ul');
        $ul->setAttribute('class', 'table-list');

        $tables = [];

        if (in_array($dbType, [PicoDatabaseType::DATABASE_TYPE_MYSQL, PicoDatabaseType::DATABASE_TYPE_MARIADB, PicoDatabaseType::DATABASE_TYPE_PGSQL])) {
            $sql = $dbType === PicoDatabaseType::DATABASE_TYPE_PGSQL
                ? "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '$schemaName' ORDER BY table_name ASC"
                : ConstantText::SHOW_TABLES;

            $stmt = $pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        } elseif ($dbType === 'sqlite') {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tables[] = $row['name'];
            }
        }

        // Grupkan tabel menjadi system dan custom
        $grouped = ['custom' => [], 'system' => []];
        foreach ($tables as $tbl) {
            $group = in_array($tbl, DatabaseUtil::SYSTEM_TABLES) ? 'system' : 'custom';
            $grouped[$group][] = $tbl;
        }

        foreach (['custom', 'system'] as $group) {
            if (count($grouped[$group]) > 0) {
                $groupLi = $dom->createElement('li', ucfirst($group) . ' Tables');
                $groupLi->setAttribute('class', 'table-group');
                $ul->appendChild($groupLi);

                foreach ($grouped[$group] as $tblName) {
                    $li = $dom->createElement('li');
                    $a = $dom->createElement('a', $tblName);
                    $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$tblName");

                    if ($table === $tblName) {
                        $a->setAttribute('class', 'active');
                    }

                    $li->appendChild($a);
                    $ul->appendChild($li);
                }
            }
        }

        $dom->appendChild($ul);
        return $dom->saveHTML();
    }

    /**
     * Displays a sidebar with tables in the selected database.
     *
     * This function retrieves and displays the list of tables in the selected database.
     * The tables are displayed as links, and the selected table is highlighted in bold.
     * 
     * Supports MySQL, PostgreSQL, and SQLite databases.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $applicationId The application identifier.
     * @param string $databaseName The name of the selected database.
     * @param string $schemaName The name of the selected schema.
     * @param string $table The currently selected table.
     * @return string The generated HTML.
     */
    public static function showSidebarTables($pdo, $applicationId, $databaseName, $schemaName, $table) //NOSONAR
    {
        $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');
        
        // Create heading
        $h3 = $dom->createElement('h3', 'Table List');

        $a = $dom->createElement('a');
        $a->appendChild($h3);
        $a->setAttribute('class', 'all-table');
        $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=");

        $dom->appendChild($a);

        // Create unordered list
        $ul = $dom->createElement('ul');
        $ul->setAttribute('class', 'table-list');

        if ($dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            // Query for MySQL and PostgreSQL to retrieve table list
            $sql = $dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB ? ConstantText::SHOW_TABLES : "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '$schemaName' ORDER BY table_name ASC";
            $stmt = $pdo->query($sql);
            
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tableName = $row[0];

                // Create list item and link
                $li = $dom->createElement('li');
                $a = $dom->createElement('a', $tableName);
                $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$tableName");

                // Highlight the selected table
                if ($table == $tableName) {
                    $a->setAttribute('class', 'active'); //NOSONAR
                }

                $li->appendChild($a);
                $ul->appendChild($li);
            }
        } elseif ($dbType == 'sqlite') {
            // Query for SQLite to retrieve table list
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tableName = $row['name'];

                // Create list item and link
                $li = $dom->createElement('li');
                $a = $dom->createElement('a', $tableName);
                $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$tableName");

                // Highlight the selected table
                if ($table == $tableName) {
                    $a->setAttribute('class', 'active'); //NOSONAR
                }

                $li->appendChild($a);
                $ul->appendChild($li);
            }
        } else {
            // Unsupported database type
            $li = $dom->createElement('li', 'No tables found for this database.');
            $ul->appendChild($li);
        }

        // Append unordered list to DOM
        $dom->appendChild($ul);

        // Output the HTML
        return $dom->saveHTML();
    }

    /**
     * Generates a SQL query to retrieve the columns and structure of a table based on the database type (MySQL, PostgreSQL, or SQLite).
     *
     * This function automatically detects the type of the database (MySQL, PostgreSQL, or SQLite) 
     * and constructs the appropriate SQL query to retrieve the columns and metadata of a table.
     * The query syntax differs depending on the database type.
     *
     * @param PDO $pdo The PDO instance connected to the database.
     * @param string $tableName The name of the table whose column information is to be retrieved.
     *
     * @return string|null The SQL query to retrieve the table columns:
     *                     - For MySQL, it returns "SHOW COLUMNS FROM `$tableName`".
     *                     - For PostgreSQL, it returns a query using "information_schema.columns".
     *                     - For SQLite, it returns a "PRAGMA table_info($tableName)" query.
     *                     - Returns `null` if the database type is unsupported.
     */
    public static function getQueryShowColumns($pdo, $schemaName, $tableName)
    {
        // Determine the database type (MySQL, PostgreSQL, or SQLite) based on PDO
        $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Get the active schema or database
        if ($dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB) {
            // Query for MySQL
            $sql = "DESCRIBE `$tableName`";
        } elseif ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            // Query for PostgreSQL
            $sql = "SELECT 
                    c.column_name AS \"Field\", 
                    CASE 
                        WHEN c.data_type IN ('character varying', 'character', 'char') 
                            THEN CONCAT(c.data_type, '(', c.character_maximum_length, ')') 
                        ELSE c.data_type
                    END AS \"Type\", 
                    c.is_nullable AS \"Null\", 
                    CASE 
                        WHEN c.column_default IS NOT NULL THEN 'DEFAULT' 
                        ELSE '' 
                    END || 
                    CASE 
                        WHEN kcu.constraint_name IS NOT NULL THEN 'PRI' 
                        ELSE '' 
                    END AS \"Key\", 
                    c.column_default AS \"Default\", 
                    CASE 
                        WHEN c.is_identity = 'YES' THEN 'AUTO_INCREMENT' 
                        ELSE '' 
                    END AS \"Extra\"
                    
                FROM information_schema.columns c
                LEFT JOIN information_schema.key_column_usage kcu
                    ON c.column_name = kcu.column_name 
                    AND c.table_name = kcu.table_name
                    AND c.table_schema = kcu.table_schema
                LEFT JOIN information_schema.table_constraints tc
                    ON kcu.constraint_name = tc.constraint_name
                    AND tc.constraint_type = 'PRIMARY KEY' 
                WHERE c.table_name = '$tableName'
                AND c.table_schema = '$schemaName'";
        } elseif ($dbType == 'sqlite') {
            // SQLite does not have schemas, so use a simple query to show columns
            $sql = "PRAGMA table_info($tableName)";
        } else {
            // If the database type is not supported
            return null;
        }

        return $sql;
    }

    /**
     * Fetches the column information for a specified table.
     * 
     * This function retrieves details about the columns in a specified table, including their data type, nullability, keys, default values, and other related information. It adapts to different database drivers (such as SQLite, MySQL, or PostgreSQL) and returns the column information in an associative array.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $schemaName The name of the schema (for PostgreSQL). For MySQL or SQLite, this can be an empty string.
     * @param string $table The name of the table whose column structure is to be fetched.
     * 
     * @return array An array containing the column details for the specified table. Each entry in the array represents a column and its associated details (e.g., name, type, nullability, etc.).
     */
    public static function getColumnInfo($pdo, $schemaName, $table)
    {
        $columnCount = 0;
        $columnRows = []; // List to store column data
        
        // Fetch the columns
        $query = self::getQueryShowColumns($pdo, $schemaName, $table);
        try
        {
            if (isset($query)) {
                $stmt = $pdo->query($query);
                
                if ($stmt) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $columnCount++;
                        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                            // For SQLite
                            $columns = self::createSqliteColumn($row);
                        } else {
                            // For MySQL or PostgreSQL
                            $columns = $row;
                        }
                        $columnRows[] = $columns; // Store row data in the list
                    }
                }
            }
        }
        catch(Exception $e)
        {
            // Do nothing
        }
        return $columnRows;
    }

    /**
     * Displays the structure of a table, including column details like name, type, nullability, key, default value, and extra information.
     *
     * This function fetches the structure of a table (such as columns, types, nullability, primary keys, default values, etc.)
     * from the database and displays it in an HTML table format. If the table is not found, it will show an error message with
     * a link to navigate back to the previous page.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $applicationId The application identifier.
     * @param string $databaseName The name of the database.
     * @param string $schemaName The name of the schema (for PostgreSQL).
     * @param string $table The name of the table whose structure is to be shown.
     * 
     * @return string The generated HTML displaying the table structure or an error message if the table is not found.
     */
    public static function showTableStructure($pdo, $applicationId, $databaseName, $schemaName, $table)
    {
        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');
        
        // Create the outer div
        $divOuter = $dom->createElement('div');
        $divOuter->setAttribute('class', 'table-structure collapsible');
        
        // Create the toggle button
        $button = $dom->createElement('button');
        $button->setAttribute('class', 'button-toggle toggle-structure');
        $button->setAttribute('data-open', '-');
        $button->setAttribute('data-close', '+');
        $divOuter->appendChild($button);
        
        // Create the heading
        $h3 = $dom->createElement('h3', 'Structure of ' . $table);
        $divOuter->appendChild($h3);
        
        // Create the inner div
        $divInner = $dom->createElement('div');
        $divInner->setAttribute('class', 'table-structure-inner');
        $divOuter->appendChild($divInner);
        
        // Create the table
        $tableElem = $dom->createElement('table');
        $thead = $dom->createElement('thead');
        $tr = $dom->createElement('tr');
        $headers = ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'];
        foreach ($headers as $header) {
            $th = $dom->createElement('th', $header);
            $tr->appendChild($th);
        }
        $thead->appendChild($tr);
        $tableElem->appendChild($thead);

        $columnRows = self::getColumnInfo($pdo, $schemaName, $table);

        $columnCount = count($columnRows);
        
        if($columnCount > 0)
        {
            // Now render the rows from the list into the table
            $tbody = $dom->createElement('tbody');
            foreach ($columnRows as $columns) {
                $tr = $dom->createElement('tr');
                foreach ($columns as $value) {
                    $td = $dom->createElement('td', $value);
                    $tr->appendChild($td);
                }
                $tbody->appendChild($tr);
            }
            $tableElem->appendChild($tbody);
            
            // Append the table to the div
            $divInner->appendChild($tableElem);
            $dom->appendChild($divOuter);
        }
        else
        {
            // Handle the case when the table is not found
            $divInner = $dom->createElement('div');
            $divInner->setAttribute('class', 'sql-error');

            // Prepare the URL for the back link
            $url = "?applicationId=$applicationId&database=$databaseName&schema=$schemaName";

            // Create the message text (without brackets) as a span
            $message = $dom->createElement('span', "Table \"{$table}\" is not found.");

            // Create the <a> element for the back link
            $backLink = $dom->createElement('a', 'Back');
            $backLink->setAttribute('href', $url);

            // Create a text node for the brackets
            $leftBracket = $dom->createTextNode(' [');
            $rightBracket = $dom->createTextNode(']');

            // Append the text nodes (left bracket and right bracket) around the link
            $message->appendChild($leftBracket);
            $message->appendChild($backLink);
            $message->appendChild($rightBracket);

            // Append the message to the div
            $divInner->appendChild($message);

            // Append the div to the document
            $dom->appendChild($divInner);
        }
        
        // Output the HTML
        return $dom->saveHTML();
    }


    /**
     * Executes a SQL query and displays the result in a table.
     *
     * This function executes a given SQL query and displays the result in a formatted HTML table.
     * If the query results in an error, the error message is displayed.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $query The SQL query to be executed.
     * @return string The generated HTML.
     */
    public static function executeQuery($pdo, $query)
    {
        try {
            $stmt = $pdo->query($query);

            // Create the DOM document
            $dom = new DOMDocument('1.0', 'utf-8');
            try
            {
                // Create table element
                $tableElem = $dom->createElement('table');
                $tr = $dom->createElement('tr');

                // Add table headers
                for ($i = 0; $i < $stmt->columnCount(); $i++) {
                    $col = $stmt->getColumnMeta($i);
                    $th = $dom->createElement('th', htmlspecialchars($col['name']));
                    $tr->appendChild($th);
                }
                $tableElem->appendChild($tr);
                $countResult = 0;

                // Add table rows
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tr = $dom->createElement('tr');
                    foreach ($row as $value) {
                        $td = $dom->createElement('td', htmlspecialchars($value));
                        $tr->appendChild($td);
                    }
                    $tableElem->appendChild($tr);
                    $countResult++;
                }

                if($countResult > 0)
                {
                    // Create the result title div
                    $resultTitleDiv = $dom->createElement('div', 'Result');
                    $resultTitleDiv->setAttribute('class', 'query-title');
                    $dom->appendChild($resultTitleDiv);

                    $queryResultDiv = $dom->createElement('div');
                    $queryResultDiv->setAttribute('class', 'query-result');
                    $queryResultDiv->appendChild($tableElem);
                    $dom->appendChild($queryResultDiv);
                }
                else
                {
                    $resultTitleDiv = $dom->createElement('div', 'No results to display');
                    $resultTitleDiv->setAttribute('class', 'query-title');
                    $dom->appendChild($resultTitleDiv);
                }
            }
            catch(Exception $e)
            {
                $resultTitleDiv = $dom->createElement('div', 'No results to display');
                $resultTitleDiv->setAttribute('class', 'query-title');
                $dom->appendChild($resultTitleDiv);
            }
            // Output the HTML
            return $dom->saveHTML();
        } catch (PDOException $e) {
            // Create the DOM document for error
            $dom = new DOMDocument('1.0', 'utf-8');

            // Create error div
            $div = $dom->createElement('div', self::ERROR . $e->getMessage());
            $div->setAttribute('class', 'sql-error');

            // Append error div to DOM
            $dom->appendChild($div);

            // Output the HTML
            return $dom->saveHTML();
        }
    }

    /**
     * Displays table data with pagination.
     *
     * This function retrieves and displays table data with pagination. The data is shown in a table
     * format, and pagination links are provided to navigate through pages of results.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $applicationId The application identifier.
     * @param string $databaseName The name of the selected database.
     * @param string $table The name of the selected table.
     * @param int $page The current page number.
     * @param int $limit The number of rows to display per page.
     * @return string The generated HTML.
     */
    public static function showTableData($pdo, $applicationId, $databaseName, $schemaName, $table, $page, $limit)
    {
        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');

        $cls = isset($_POST['query']) ? '' : ' open';
        
        // Create the outer div
        $divOuter = $dom->createElement('div');
        
        try
        {
            $primaryKeyName = self::getPrimaryKeyName($pdo, $schemaName, $table);

            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $isPgSql = stripos($driver, 'pg') !== false || stripos($driver, 'postgre') !== false;
            
            $offset = ($page - 1) * $limit;
            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM $table");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($total / $limit);

            $divOuter->setAttribute('class', 'table-content collapsible' . $cls);
            
            // Create the toggle button
            $button = $dom->createElement('button');
            $button->setAttribute('class', 'button-toggle toggle-structure');
            $button->setAttribute('data-open', '-');
            $button->setAttribute('data-close', '+');
            $divOuter->appendChild($button);

            // Create the heading
            $h3 = $dom->createElement('h3', "Data in $table (Page $page)");
            $divOuter->appendChild($h3);

            // Create the inner div
            $divInner = $dom->createElement('div');
            $divInner->setAttribute('class', 'table-content-inner');
            $divOuter->appendChild($divInner);

            // Create the table
            $tableElem = $dom->createElement('table');
            $thead = $dom->createElement('thead');
            $tr = $dom->createElement('tr');

            // Add table headers
            $stmt = $pdo->query("SELECT * FROM $table LIMIT $limit OFFSET $offset");
            $th = $dom->createElement('th', '');
            $th->setAttribute('width', '28');
            $th->setAttribute('class', 'cell-edit');
            $tr->appendChild($th);
            $th2 = $dom->createElement('th', '');
            $th2->setAttribute('width', '28');
            $th2->setAttribute('class', 'cell-edit');
            $tr->appendChild($th2);
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $col = $stmt->getColumnMeta($i);
                $th = $dom->createElement('th', htmlspecialchars($col['name']));
                $tr->appendChild($th);
            }
            $thead->appendChild($tr);
            $tableElem->appendChild($thead);

            // Add table rows
            $tbody = $dom->createElement('tbody');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tr = $dom->createElement('tr');
                $id = isset($primaryKeyName) && isset($row[$primaryKeyName]) ? $row[$primaryKeyName] : '';
                $a = $dom->createElement('a');
                $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&action=update-form&id=$id");
                $a->appendChild($dom->createTextNode("✏️"));
                $td = $dom->createElement('td');
                $td->setAttribute('class', 'cell-edit');
                $td->appendChild($a);
                $tr->appendChild($td);

                $deleteLink = $dom->createElement('a');
                $deleteLink->setAttribute('href', "javascript:;");
                $deleteLink->setAttribute('data-database', $databaseName);
                
                if($isPgSql)
                {
                    $deleteLink->setAttribute('data-schema', $schemaName);
                }
                else
                {
                    $deleteLink->setAttribute('data-schema', '');
                }
                
                $deleteLink->setAttribute('data-table', $table);
                $deleteLink->setAttribute('data-primary-key', $primaryKeyName);
                $deleteLink->setAttribute('data-value', $id);
                $deleteLink->appendChild($dom->createTextNode("❌"));
                $deleteTd = $dom->createElement('td');
                $deleteTd->setAttribute('class', 'cell-delete');
                $deleteTd->appendChild($deleteLink);
                $tr->appendChild($deleteTd);

                foreach ($row as $value) {
                    $td = $dom->createElement('td', htmlspecialchars($value));
                    $tr->appendChild($td);
                }
                $tbody->appendChild($tr);
            }
            $tableElem->appendChild($tbody);
            $divInner->appendChild($tableElem);

            // Append the outer div to the DOM
            $dom->appendChild($divOuter);

            $paginationDiv = self::createPagination($dom, $applicationId, $databaseName, $schemaName, $table, $page, $totalPages);

            // Append the pagination div to the DOM
            $divOuter->appendChild($paginationDiv);
            $dom->appendChild($divOuter);
        }
        catch(Exception $e)
        {
            $divInner = $dom->createElement('div');
            $divInner->setAttribute('class', 'sql-error');

            // Add the exception message to the div
            $message = $dom->createTextNode(self::ERROR . $e->getMessage());
            $divInner->appendChild($message);
            
            $divOuter->appendChild($divInner);
            $dom->appendChild($divOuter);
        }

        // Output the HTML with pagination
        return $dom->saveHTML();
    }
    
    /**
     * Generates pagination controls for navigating between pages.
     * 
     * This function creates a pagination navigation bar with links to specific pages. It ensures that the displayed page range is centered around the current page, with a maximum of 7 visible page numbers. It also includes links for "First" and "Last" pages with ellipses for skipped pages when necessary.
     * 
     * @param DOMDocument $dom The DOMDocument object used to create HTML elements.
     * @param string $applicationId The application identifier.
     * @param string $databaseName The name of the database.
     * @param string $schemaName The name of the schema (for PostgreSQL).
     * @param string $table The name of the table being paginated.
     * @param int $page The current page number.
     * @param int $totalPages The total number of pages available.
     * 
     * @return DOMDocument The DOM element representing the pagination controls.
     */
    public static function createPagination($dom, $applicationId, $databaseName, $schemaName, $table, $page, $totalPages)
    {
        // Pagination navigation with a maximum of 7 pages
        $paginationDiv = $dom->createElement('div');
        $paginationDiv->setAttribute('class', 'pagination');
        $startPage = max(1, $page - 3);
        $endPage = min($totalPages, $page + 3);

        if ($startPage > 1) {
            $firstPage = $dom->createElement('a', 'First');
            $firstPage->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&page=1");
            $paginationDiv->appendChild($firstPage);

            if ($startPage > 2) {
                $ellipsis = $dom->createElement('span', '...');
                $paginationDiv->appendChild($ellipsis);
                $space = $dom->createTextNode(" ");
                $paginationDiv->appendChild($space);
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $pageLink = $dom->createElement('a', htmlspecialchars($i));
            $pageLink->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&page=$i");
            if ($i == $page) {
                $pageLink->setAttribute('style', 'font-weight: bold;'); //NOSONAR
            }
            $paginationDiv->appendChild($pageLink);
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                $ellipsis = $dom->createElement('span', '...');
                $paginationDiv->appendChild($ellipsis);
                $space = $dom->createTextNode(" ");
                $paginationDiv->appendChild($space);
            }

            $lastPage = $dom->createElement('a', 'Last');
            $lastPage->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&page=$totalPages");
            $paginationDiv->appendChild($lastPage);
        }

        return $paginationDiv;
    }

    /**
     * Generates an HTML form to edit data for a specific row in the table.
     *
     * This function retrieves the data for a given record (identified by the primary key) from the
     * specified table and generates an HTML form for editing the data. The form is displayed using a
     * two-column table layout, where the first column contains labels and the second column contains
     * input fields. The form is built dynamically using DOMDocument.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $applicationId The identifier of the application requesting the data.
     * @param string $databaseName The name of the database being queried.
     * @param string $schemaName The name of the schema (if applicable) in the database.
     * @param string $table The name of the table containing the data.
     * @param string $primaryKeyValue The value of the primary key for the row being edited.
     *
     * @return string The HTML form as a string, or an error message if something goes wrong.
     *
     * @throws DataException If there is an error retrieving the data or if no data is found for the given ID.
     */
    public static function updateData($pdo, $applicationId, $databaseName, $schemaName, $table, $primaryKeyValue)
    {
        $primaryKeyName = self::getPrimaryKeyName($pdo, $schemaName, $table);
        $dom = new DOMDocument('1.0', 'utf-8');
        
        try {

            // Fetch the data for the given ID
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKeyName = :id");
            $stmt->bindParam(':id', $primaryKeyValue, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new DataException("No data found for ID: $primaryKeyValue");
            }
            
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            if(!isset($referer) || empty($referer))
            {
                $referer = '';
                $backLink = "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&page=1";
            }
            else
            {
                $backLink = $referer;
            }

            $columnRows = self::getColumnInfo($pdo, $schemaName, $table);
            $columnTypes = self::getColumnType($columnRows);

            // Create form container
            $form = $dom->createElement('form');
            $form->setAttribute('method', 'POST');
            $form->setAttribute('action', $referer);
            $form->setAttribute('class', 'update-form');

            // Add hidden input for table name
            $inputId1 = $dom->createElement('input');
            $inputId1->setAttribute('type', 'hidden');
            $inputId1->setAttribute('name', '___table_name___');
            $inputId1->setAttribute('value', htmlspecialchars($table));

            // Add hidden input for primary key name
            $inputId2 = $dom->createElement('input');
            $inputId2->setAttribute('type', 'hidden');
            $inputId2->setAttribute('name', '___primary_key_name___');
            $inputId2->setAttribute('value', htmlspecialchars($primaryKeyName));
            
            // Add hidden input for primary key value
            $inputId3 = $dom->createElement('input');
            $inputId3->setAttribute('type', 'hidden');
            $inputId3->setAttribute('name', '___primary_key_value___');
            $inputId3->setAttribute('value', htmlspecialchars($primaryKeyValue));
            
            $form->appendChild($inputId1);
            $form->appendChild($inputId2);
            $form->appendChild($inputId3);

            // Add title
            $h3 = $dom->createElement('h3', "Edit Data for $table");
            $form->appendChild($h3);

            // Start the table
            $tableElem = $dom->createElement('table');
            $tableElem->setAttribute('class', 'two-side-table');
            $tableElem->setAttribute('border', '1');
            
            $columnNames = [];

            $tr = $dom->createElement('tr');

            // First column - Label
            $tdLabel = $dom->createElement('td');
            $label = $dom->createElement('label', htmlspecialchars($primaryKeyName));
            $label->setAttribute('for', htmlspecialchars($primaryKeyName));
            $tdLabel->appendChild($label);
            $tr->appendChild($tdLabel);

            // Second column - Textarea field
            $tdInput = $dom->createElement('td');
            $textarea = $dom->createElement('div');
            $textarea->setAttribute('class', 'data-editor');
            $textarea->nodeValue = htmlspecialchars($primaryKeyValue);  
            $tdInput->appendChild($textarea);
            $tr->appendChild($tdInput);

            // Append row to the table
            $tableElem->appendChild($tr);

            // Loop through the row data to create form fields
            foreach ($row as $key => $value) {
                if ($key != $primaryKeyName) {  // Skip the primary key field in the form
                    $columnNames[] = $key;
                    $tr = $dom->createElement('tr');

                    // First column - Label
                    $tdLabel = $dom->createElement('td');
                    $label = $dom->createElement('label', htmlspecialchars($key));
                    $label->setAttribute('for', htmlspecialchars($key));
                    $tdLabel->appendChild($label);
                    $tr->appendChild($tdLabel);

                    // Second column - Textarea field
                    $tdInput = $dom->createElement('td');

                    $type = isset($columnTypes[$key]) ? $columnTypes[$key] : 'text';
                    $input = self::getEditor($dom, $key, $value, $type);
                    
                    $tdInput->appendChild($input);
                    $tr->appendChild($tdInput);

                    // Append row to the table
                    $tableElem->appendChild($tr);
                }
            }

            // Append the table to the form
            $form->appendChild($tableElem);

            
            // Add hidden input for primary key value
            $inputId4 = $dom->createElement('input');
            $inputId4->setAttribute('type', 'hidden');
            $inputId4->setAttribute('name', '___column_list___');
            $inputId4->setAttribute('value', htmlspecialchars(implode(",", $columnNames)));
            $form->appendChild($inputId4);
            
            // Add the submit button
            $inputSubmit = $dom->createElement('input');
            $inputSubmit->setAttribute('type', 'submit');
            $inputSubmit->setAttribute('name', 'update');
            $inputSubmit->setAttribute('value', 'Update');
            $inputSubmit->setAttribute('class', ConstantText::BTN_SUCCESS);
            $form->appendChild($inputSubmit);
            
            // Add space between buttons
            $space = $dom->createTextNode(' ');
            $form->appendChild($space);
            
            // Add the back button
            $inputBack = $dom->createElement('input');
            $inputBack->setAttribute('type', 'button');
            $inputBack->setAttribute('name', 'back');
            $inputBack->setAttribute('value', 'Back');
            $inputBack->setAttribute('class', 'btn btn-secondary');
            $inputBack->setAttribute('onclick', "window.location='$backLink'");
            $form->appendChild($inputBack);

            // Return the form's HTML
            $dom->appendChild($form);
            return $dom->saveHTML();
        } catch (Exception $e) {
            return self::ERROR . $e->getMessage();
        }
    }

    /**
     * Generates an HTML form to create new data for a specific row in the table.
     *
     * This function generates an HTML form for inserting new data into a database table. The form is dynamically created
     * using a two-column layout, where the first column contains labels for each field and the second column contains 
     * input fields (typically text inputs or textareas). The form is built using the DOMDocument class.
     * 
     * The form includes hidden inputs for the table name, primary key name, and a list of columns. It also provides 
     * buttons to submit the form or go back to the previous page.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $applicationId The identifier of the application requesting the data.
     * @param string $databaseName The name of the database being queried.
     * @param string $schemaName The name of the schema (if applicable) in the database.
     * @param string $table The name of the table for which the form is being created.
     *
     * @return string The HTML form as a string, or an error message if something goes wrong.
     *
     * @throws DataException If there is an error retrieving the data or if no data is found for the given table.
     */
    public static function insertData($pdo, $applicationId, $databaseName, $schemaName, $table)
    {
        $primaryKeyName = self::getPrimaryKeyName($pdo, $schemaName, $table);
        $dom = new DOMDocument('1.0', 'utf-8');
        
        try {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            if(!isset($referer) || empty($referer))
            {
                $referer = '';
                $backLink = "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&page=1";
            }
            else
            {
                $backLink = $referer;
            }

            $columnRows = self::getColumnInfo($pdo, $schemaName, $table);

            // Create form container
            $form = $dom->createElement('form');
            $form->setAttribute('method', 'POST');
            $form->setAttribute('action', $referer);
            $form->setAttribute('class', 'update-form');

            // Add hidden input for table name
            $inputId1 = $dom->createElement('input');
            $inputId1->setAttribute('type', 'hidden');
            $inputId1->setAttribute('name', '___table_name___');
            $inputId1->setAttribute('value', htmlspecialchars($table));

            // Add hidden input for primary key name
            $inputId2 = $dom->createElement('input');
            $inputId2->setAttribute('type', 'hidden');
            $inputId2->setAttribute('name', '___primary_key_name___');
            $inputId2->setAttribute('value', htmlspecialchars($primaryKeyName));
            
            $form->appendChild($inputId1);
            $form->appendChild($inputId2);

            // Add title
            $h3 = $dom->createElement('h3', "New Data for $table");
            $form->appendChild($h3);

            // Start the table
            $tableElem = $dom->createElement('table');
            $tableElem->setAttribute('class', 'two-side-table');
            $tableElem->setAttribute('border', '1');
            
            $columnNames = [];

            // Loop through the row data to create form fields
            foreach ($columnRows as $value) {
                $key = $value['Field'];
                $columnNames[] = $key;
                $tr = $dom->createElement('tr');

                // First column - Label
                $tdLabel = $dom->createElement('td');
                $label = $dom->createElement('label', htmlspecialchars($key));
                $label->setAttribute('for', htmlspecialchars($key));
                $tdLabel->appendChild($label);
                $tr->appendChild($tdLabel);

                // Second column - Textarea field
                $tdInput = $dom->createElement('td');

                $type = isset($value['Type']) ? $value['Type'] : 'text';
                $input = self::getEditor($dom, $key, '', $type);
                if($primaryKeyName == $key)
                {
                    $input->setAttribute('required', 'required');
                }
                
                $tdInput->appendChild($input);
                $tr->appendChild($tdInput);

                // Append row to the table
                $tableElem->appendChild($tr);
                
            }

            // Append the table to the form
            $form->appendChild($tableElem);

            // Add hidden input for primary key value
            $inputId4 = $dom->createElement('input');
            $inputId4->setAttribute('type', 'hidden');
            $inputId4->setAttribute('name', '___column_list___');
            $inputId4->setAttribute('value', htmlspecialchars(implode(",", $columnNames)));
            $form->appendChild($inputId4);
            
            // Add the submit button
            $inputSubmit = $dom->createElement('input');
            $inputSubmit->setAttribute('type', 'submit');
            $inputSubmit->setAttribute('name', 'insert');
            $inputSubmit->setAttribute('value', 'Insert');
            $inputSubmit->setAttribute('class', ConstantText::BTN_SUCCESS);
            $form->appendChild($inputSubmit);
            
            // Add space between buttons
            $space = $dom->createTextNode(' ');
            $form->appendChild($space);
            
            // Add the back button
            $inputBack = $dom->createElement('input');
            $inputBack->setAttribute('type', 'button');
            $inputBack->setAttribute('name', 'back');
            $inputBack->setAttribute('value', 'Back');
            $inputBack->setAttribute('class', 'btn btn-secondary');
            $inputBack->setAttribute('onclick', "window.location='$backLink'");
            $form->appendChild($inputBack);

            // Return the form's HTML
            $dom->appendChild($form);
            return $dom->saveHTML();
        } catch (Exception $e) {
            return self::ERROR . $e->getMessage();
        }
    }

    /**
     * Generates an HTML editor element (input or textarea) based on the provided type.
     * 
     * This function creates an appropriate form element for editing data based on the column's data type. It returns an `<input>` element for numeric types (`int`, `float`, `double`, etc.) and a `<textarea>` element for other types such as strings. The function adjusts the input's attributes according to the data type, such as `step` for numeric inputs and `spellcheck` for text areas.
     * 
     * @param DOMDocument $dom The DOMDocument object used to create HTML elements.
     * @param string $key The name or identifier for the input field (usually the column name).
     * @param mixed $value The current value of the field to be edited.
     * @param string $type The data type of the field (e.g., "int", "float", "string", etc.).
     * 
     * @return mixed A DOM element representing the input or textarea, or `false` if no valid element can be created.
     */
    public static function getEditor($dom, $key, $value, $type)
    {
        if(self::isInteger($type))
        {
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'number');
            $input->setAttribute('step', '1');
            $input->setAttribute('name', htmlspecialchars($key));
            $input->setAttribute('class', 'data-editor');
            $input->setAttribute('value', $value);
            $input->setAttribute('data-type', 'integer');    
        }
        else if(self::isFloat($type))
        {
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'number');
            $input->setAttribute('step', 'any');
            $input->setAttribute('name', htmlspecialchars($key));
            $input->setAttribute('class', 'data-editor');
            $input->setAttribute('value', $value);  
            $input->setAttribute('data-type', 'float');  
        }
        else if(self::isDateTime($type))
        {
            $input = $dom->createElement('input');
            $input->setAttribute('type', 'text');
            $input->setAttribute('name', htmlspecialchars($key));
            $input->setAttribute('class', 'data-editor');
            $input->setAttribute('value', $value);
            if(stripos($type, 'datetime') === 0 || stripos($type, 'timestamp') === 0)
            {
                $input->setAttribute('data-type', 'datetime'); 
            }
            else if(stripos($type, 'time') === 0)
            {
                $input->setAttribute('data-type', 'time'); 
            } 
            else if(stripos($type, 'date') === 0)
            {
                $input->setAttribute('data-type', 'date'); 
            } 
        }
        else
        {
            $input = $dom->createElement('textarea');
            $input->setAttribute('name', htmlspecialchars($key));
            $input->setAttribute('class', 'data-editor');
            $input->setAttribute('spellcheck', 'false');
            $input->nodeValue = htmlspecialchars($value);  // Set the value inside the textarea
        }
        return $input;
    }
    
    /**
     * Determines if the given type represents an integer.
     *
     * This method checks if the provided string matches any of the common integer
     * types used in databases, such as tinyint, smallint, mediumint, bigint, or int.
     *
     * @param string $type The type to check.
     * @return bool True if the type represents an integer, otherwise false.
     */
    private static function isInteger($type)
    {
        return stripos($type, 'bool') === 0 
        || stripos($type, 'tinyint') === 0 
        || stripos($type, 'smallint') === 0 
        || stripos($type, 'mediumint') === 0 
        || stripos($type, 'bigint') === 0 
        || stripos($type, 'int') === 0;
    }

    /**
     * Determines if the given type represents a floating-point number.
     *
     * This method checks if the provided string matches any of the common floating-point
     * types used in databases, such as float, double, real, or decimal.
     *
     * @param string $type The type to check.
     * @return bool True if the type represents a floating-point number, otherwise false.
     */
    private static function isFloat($type)
    {
        return stripos($type, 'float') === 0 
        || stripos($type, 'double') === 0 
        || stripos($type, 'real') === 0 
        || stripos($type, 'decimal') === 0;
    }

    /**
     * Determines if the given type represents a date or time.
     *
     * This method checks if the provided string matches any of the common date and time
     * types used in databases, such as datetime, date, or time.
     *
     * @param string $type The type to check.
     * @return bool True if the type represents a date or time, otherwise false.
     */
    private static function isDateTime($type)
    {
        return stripos($type, 'datetime') === 0 || stripos($type, 'timestamp') === 0 || stripos($type, 'time') === 0 || stripos($type, 'date') === 0;
    }


    /**
     * Extracts the column types from an array of column details.
     * 
     * This function processes an array of column data, which includes details about each column in a table. It returns an associative array where the keys are the column names, and the values are their corresponding data types.
     *
     * @param array $columnRows An array of column details, where each entry contains information about a specific column, including the 'Field' (column name) and 'Type' (column data type).
     * 
     * @return array An associative array where the keys are column names, and the values are the corresponding column types.
     */
    public static function getColumnType($columnRows)
    {
        $map = array();
        if(isset($columnRows) && is_array($columnRows) && !empty($columnRows))
        {
            foreach($columnRows as $col)
            {
                $map[$col['Field']] = $col['Type'];
            }
        }
        return $map;
    }

    /**
     * Retrieves the name of the primary key column for a given table.
     *
     * This function retrieves the name of the primary key column for a specified table. It queries
     * the database's schema information to find the column marked as the primary key. The function
     * supports SQLite, MySQL, and PostgreSQL databases, and will return the name of the primary key
     * field for the given table.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $schemaName The name of the schema (for MySQL/PostgreSQL). SQLite does not require this.
     * @param string $table The name of the table for which the primary key is being retrieved.
     *
     * @return string|null The name of the primary key column if found, or null if not found or an error occurs.
     *
     * @throws Exception If there is an error while querying the database or processing the result.
     */
    public static function getPrimaryKeyName($pdo, $schemaName, $table)
    {
        $query = self::getQueryShowColumns($pdo, $schemaName, $table);
        try
        {
            $stmt = $pdo->query($query);
            
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                        // For SQLite
                        $columns = self::createSqliteColumn($row);
                    } else {
                        // For MySQL or PostgreSQL
                        $columns = $row;
                    }
                    if($columns['Key'] == 'PRI' || $columns['Key'] == 'DEFAULTPRI')
                    {
                        return $columns['Field'];
                    }
                }
            }
        }
        catch(Exception $e)
        {
            // Do nothing
        }
        return null;
    }
    
    /**
     * Creates a structured column array for an SQLite database.
     *
     * This function generates an associative array that represents a database column's properties
     * for SQLite. The array contains details such as the column name, type, nullability, key type,
     * default value, and extra properties (e.g., auto_increment for INTEGER columns).
     *
     * @param array $row An associative array representing the column details fetched from the database.
     *                   The array should contain keys such as 'name', 'type', 'notnull', 'pk', and 'dflt_value'.
     *
     * @return array An associative array with the following keys:
     *               - 'Field'   => Column name
     *               - 'Type'    => Column data type
     *               - 'Null'    => 'YES' or 'NO' indicating whether the column allows NULL values
     *               - 'Key'     => 'PRI' if the column is the primary key, otherwise an empty string
     *               - 'Default' => The default value of the column, or 'NULL' if no default is specified
     *               - 'Extra'   => 'auto_increment' if the column is an INTEGER primary key, otherwise an empty string
     */
    public static function createSqliteColumn($row)
    {
        return [
            'Field'   => $row['name'], 
            'Type'    => $row['type'], 
            'Null'    => $row['notnull'] ? 'NO' : 'YES', 
            'Key'     => $row['pk'] == 1 ? 'PRI' : '', 
            'Default' => $row['dflt_value'] ? $row['dflt_value'] : 'NULL', 
            'Extra'   => strtoupper($row['type']) == 'INTEGER' && $row['pk'] == 1 ? 'auto_increment' : ''
        ];
    }

    /**
     * Creates a query execution form.
     *
     * This function generates an HTML form that allows users to execute SQL queries. The form includes a textarea
     * pre-populated with the last executed queries, and buttons to submit or reset the form. The form is tailored 
     * to the type of database selected (PostgreSQL, SQLite, or MySQL).
     *
     * @param string $lastQueries The last executed SQL queries to be pre-populated in the textarea.
     * @param string $dbType The type of the database (e.g., 'pgsql', 'sqlite', 'mysql').
     * @param string $applicationId The application ID
     * 
     * @return string The generated HTML of the form as a string.
     */
    public static function createQueryExecutorForm($lastQueries, $dbType, $applicationId)
    {
        if($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL)
        {
            $label = 'Execute Query (PostgreSQL)';
        }
        else if($dbType == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            $label = 'Execute Query (SQLite)';
        }
        else if($dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB)
        {
            $label = 'Execute Query (MariaDB)';
        }
        else
        {
            $label = 'Execute Query (MySQL)';
        }
        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');

        // Create heading
        $h3 = $dom->createElement('h3', $label);
        $dom->appendChild($h3);

        // Create form
        $form = $dom->createElement('form');
        $form->setAttribute('method', 'post');
        $form->setAttribute('action', '');

        $form1 = $dom->createElement('div');
        $form1->setAttribute('class', 'query-area');
        $form2 = $dom->createElement('div');
        $form2->setAttribute('class', 'button-area');

        $form->appendChild($form1);
        $form->appendChild($form2);

        // Create textarea
        $textarea = $dom->createElement('textarea', htmlspecialchars($lastQueries));
        $textarea->setAttribute('name', 'query');
        $textarea->setAttribute('spellcheck', 'false');
        $textarea->setAttribute('autocomplete', 'off');
        $form1->appendChild($textarea);

        // Create submit button
        $submit = $dom->createElement('button');
        $submit->setAttribute('type', 'submit');
        $submit->setAttribute('value', 'Execute');
        $submit->setAttribute('class', 'btn btn-success execute');
        $buttonText = $dom->createTextNode('Execute');
        $submit->appendChild($buttonText);
        $form2->appendChild($submit);

        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form2->appendChild($space);

        // Create save button
        $save = $dom->createElement('button');
        $save->setAttribute('type', 'button');
        $save->setAttribute('class', 'btn btn-success save');
        $buttonText = $dom->createTextNode('Save');
        $save->appendChild($buttonText);
        $form2->appendChild($save);

        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form2->appendChild($space);

        // Create reset button
        $reset = $dom->createElement('button');
        $reset->setAttribute('type', 'reset');
        $reset->setAttribute('value', 'Reset');
        $reset->setAttribute('class', 'btn btn-warning reset');
        $reset->appendChild($dom->createTextNode('Reset'));
        $form2->appendChild($reset);
        
        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form2->appendChild($space);

        if(!empty($applicationId))
        {
        
            // Create importStructure button
            $importStructure = $dom->createElement('button');
            $importStructure->setAttribute('type', 'button');
            $importStructure->setAttribute('value', 'Import Structure');
            $importStructure->setAttribute('class', 'btn btn-primary import-structure');
            $importStructure->appendChild($dom->createTextNode('Import Structure'));
            $form2->appendChild($importStructure);
            
            // Add space between buttons
            $space = $dom->createTextNode(' ');
            $form2->appendChild($space);
        
            // Create entityEditor button
            $entityEditor = $dom->createElement('button');
            $entityEditor->setAttribute('type', 'button');
            $entityEditor->setAttribute('value', 'Entity Editor');
            $entityEditor->setAttribute('class', 'btn btn-primary open-entity-editor');
            $entityEditor->appendChild($dom->createTextNode('Entity Editor'));
            $form2->appendChild($entityEditor);
        }

        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form2->appendChild($space);

        // Create exportTable button
        $inputGet = new InputGet();

        $tableName = $inputGet->getTable(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
        $databaseName = $inputGet->getDatabase(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
        $applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
        $schemaName = $inputGet->getSchemaName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
        $table = $inputGet->getTable(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form2->appendChild($space);

        // Create exportDatabaseStructure button (Export Struktur Database)
        $exportDatabaseStructure = $dom->createElement('button');
        $exportDatabaseStructure->setAttribute('type', 'submit');
        $exportDatabaseStructure->setAttribute('name', '___export_database_structure___');
        $exportDatabaseStructure->setAttribute('value', $databaseName);
        $exportDatabaseStructure->setAttribute('class', ConstantText::BTN_SUCCESS);
        $exportDatabaseStructure->appendChild($dom->createTextNode('Export Database Structure'));
        $form2->appendChild($exportDatabaseStructure);

        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form2->appendChild($space);

        // Create exportDatabase button
        $exportDatabase = $dom->createElement('button');
        $exportDatabase->setAttribute('type', 'submit');
        $exportDatabase->setAttribute('name', '___export_database___');
        $exportDatabase->setAttribute('value', $databaseName);
        $exportDatabase->setAttribute('class', ConstantText::BTN_SUCCESS);
        $exportDatabase->appendChild($dom->createTextNode('Export Database'));
        $form2->appendChild($exportDatabase);

       

        if(!empty($table))
        {
            // Add space between buttons
            $space = $dom->createTextNode(' ');
            $form2->appendChild($space);

            // Create exportTableStructure button (Export Structure Tabel)
            $exportTableStructure = $dom->createElement('button');
            $exportTableStructure->setAttribute('type', 'submit');
            $exportTableStructure->setAttribute('name', '___export_table_structure___');
            $exportTableStructure->setAttribute('value', $tableName);
            $exportTableStructure->setAttribute('class', ConstantText::BTN_SUCCESS);
            $exportTableStructure->appendChild($dom->createTextNode('Export Table Structure'));
            $form2->appendChild($exportTableStructure);

            // Add space between buttons
            $space = $dom->createTextNode(' ');
            $form2->appendChild($space);

            $exportTable = $dom->createElement('button');
            $exportTable->setAttribute('type', 'submit');
            $exportTable->setAttribute('name', '___export_table___');
            $exportTable->setAttribute('value', $tableName);
            $exportTable->setAttribute('class', ConstantText::BTN_SUCCESS);
            $exportTable->appendChild($dom->createTextNode('Export Table'));
            $form2->appendChild($exportTable);

            // Add space between buttons
            $space = $dom->createTextNode(' ');
            $form2->appendChild($space);

            if($inputGet->getUserAction() == 'insert-form')
            {
                // Create viewData button
                $insertData = $dom->createElement('button');
                $insertData->setAttribute('type', 'button');
                $insertData->setAttribute('class', ConstantText::BTN_PRIMARY);
                $insertData->setAttribute('onclick', "window.location='?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table'");

                $insertData->appendChild($dom->createTextNode('View Data'));
                $form2->appendChild($insertData);
            }
            else
            {
                // Create insertData button
                $insertData = $dom->createElement('button');
                $insertData->setAttribute('type', 'button');
                $insertData->setAttribute('class', ConstantText::BTN_PRIMARY);
                $insertData->setAttribute('onclick', "window.location='?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&action=insert-form'");

                $insertData->appendChild($dom->createTextNode('Insert Data'));
                $form2->appendChild($insertData);
            }
        }

        // Append form to DOM
        $dom->appendChild($form);

        // Output the HTML
        return $dom->saveHTML();
    }

    /**
     * Executes a series of SQL queries and displays the results.
     *
     * This function executes a given SQL query and displays the result in a formatted HTML.
     * If the query results in an error, the error message is displayed.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $q The current SQL query being executed.
     * @param string $query The SQL query to be executed.
     * @param string[] $queries An array of SQL queries to be executed.
     * @return string The generated HTML.
     */
    public static function executeQueryResult($pdo, $q, $query, $queries)
    {
        if ($query) {
            // Create the DOM document
            $dom = new DOMDocument('1.0', 'utf-8');

            foreach ($queries as $i => $q) {
                $j = $i + 1;

                $queryDivContainer = $dom->createElement('div');
                $queryDivContainer->setAttribute('class', 'last-query');

                // Create the container div for each query
                $queryDiv = $dom->createElement('div');
                $queryDiv->setAttribute('class', 'query-executed');

                // Create the query title div
                $queryTitleDiv = $dom->createElement('div', 'Query ' . $j);
                $queryTitleDiv->setAttribute('class', 'query-title');
                $queryDiv->appendChild($queryTitleDiv);

                $queryDivContainer->appendChild($queryDiv);

                // Create the raw query div

                $queryRawDiv = $dom->createElement('div');

                $queryRawPre = $dom->createElement('pre', htmlspecialchars($q));
                $queryRawPre->setAttribute('class', 'query-raw');
                $queryRawPre->setAttribute('contenteditable', 'true');
                $queryRawPre->setAttribute('spellcheck', 'false');

                $queryRawDiv->appendChild($queryRawPre);
                $queryDiv->appendChild($queryRawDiv);

                // Create the result div
                $resultDiv = $dom->createElement('div');
                $resultDiv->setAttribute('class', 'query-result-container');

                // Execute the query and append the result (HTML content)
                $resultHTML = new DOMDocument();
                $resultHTML->loadHTML(DatabaseExplorer::executeQuery($pdo, $q));
                foreach ($resultHTML->getElementsByTagName('body')->item(0)->childNodes as $child) {
                    $resultDiv->appendChild($dom->importNode($child, true));
                }
                $queryDivContainer->appendChild($resultDiv);

                // Append the queryDiv to the DOM
                $dom->appendChild($queryDivContainer);
            }

            // Output the HTML
            return $dom->saveHTML();
        }
        return "";
    }

    /**
     * Split a SQL string into separate queries.
     *
     * This method takes a raw SQL string and splits it into individual
     * queries based on delimiters, handling comments and whitespace.
     *
     * @param string $sqlText The raw SQL string containing one or more queries.
     * @return array An array of queries with their respective delimiters.
     */
    public static function splitSqlNoLeftTrim($sqlText)
    {
        // Normalize newlines and clean up any redundant line breaks
        $sqlText = str_replace("\r\r\n", "\r\n", str_replace("\n", "\r\n", $sqlText));

        // Split the SQL text by newlines
        $lines = explode("\r\n", $sqlText);

        // Clean up lines, remove comments, and empty lines, without trimming leading whitespaces
        $cleanedLines = array_filter($lines, function ($line) {
            return self::arrayFilterFunction($line); // Custom filter function to check for valid lines
        });

        // Initialize state variables
        $queries = array();
        $currentQuery = '';
        $isAppending = false;
        $delimiter = ';';
        $skip = false;

        foreach ($cleanedLines as $line) {
            // Skip lines if needed
            if ($skip) {
                $skip = false;
                continue;
            }

            // Handle "delimiter" statements
            if (stripos(trim($line), 'delimiter ') === 0) {
                $delimiter = self::getDelimiter($line);
                continue;
            }

            // Start a new query if necessary
            if (!$isAppending) {
                if (!empty($currentQuery)) {
                    // Store the previous query and reset for the next one
                    $queries[] = array('query' => rtrim($currentQuery, PicoDatabaseUtil::INLINE_TRIM), 'delimiter' => $delimiter);
                }
                $currentQuery = '';
                $isAppending = true;
            }

            // Append current line to the current query
            $currentQuery .= $line . "\r\n";

            // Check if the query ends with the delimiter
            if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
                $isAppending = false; // End of query, so we stop appending
            }
        }

        // Add the last query if any
        if (!empty($currentQuery)) {
            $queries[] = array(
                'query' => rtrim($currentQuery, PicoDatabaseUtil::INLINE_TRIM), 
                'delimiter' => $delimiter
            );
        }

        return $queries;
    }

    /**
     * Filters out empty lines, comments (lines starting with "-- "), and lines that are exactly "--".
     *
     * This function is commonly used in contexts where a list of lines or strings needs to be processed
     * and filtered by excluding empty lines, comment lines (starting with "-- "), and lines with only
     * the string "--".
     *
     * @param string $line The line to be checked.
     * @return bool Returns `true` if the line should be included, `false` otherwise.
     */
    private static function arrayFilterFunction($line)
    {
        return !(empty($line) || stripos($line, "-- ") === 0 || $line == "--");
    }
    
    /**
     * Determines the delimiter based on the second part of a trimmed line.
     *
     * This function splits the given line by spaces and checks the second part. If the second part exists,
     * it returns a semicolon (`;`) as the delimiter. Otherwise, it returns `null`.
     *
     * @param string $line The line to be analyzed, which is trimmed and split by spaces.
     * @return string|null Returns `';'` if there is a second part after splitting the line, otherwise `null`.
     */
    private static function getDelimiter($line)
    {
        $parts = explode(' ', trim($line));
        return $parts[1] != null ? ';' : null;
    }

}
