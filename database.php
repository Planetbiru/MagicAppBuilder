<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once (__DIR__) . "/inc.app/app.php";
require_once (__DIR__) . "/inc.app/sessions.php";

if(isset($databaseConfig))
{
    require_once (__DIR__) . "/inc.app/database.php";
}

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
 */
class DatabaseExplorer
{
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
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            } else {
                $currentStatement .= $char;
            }
        }

        // Add the last statement if exists
        if (trim($currentStatement) !== '') {
            $statements[] = trim($currentStatement);
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
        $stmt = ($dbType == 'pgsql') ? $pdo->query('SELECT datname FROM pg_database') : $pdo->query('SHOW DATABASES');

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
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
        if ($dbType == 'pgsql') {
            $selectSchema = $dom->createElement('select');
            $selectSchema->setAttribute('name', 'schema');
            $selectSchema->setAttribute('id', 'schema-select');
            $selectSchema->setAttribute('onchange', 'this.form.submit()');

            // Default option for schema
            $option = $dom->createElement('option', '-- Choose Schema --');
            $option->setAttribute('value', '');
            $selectSchema->appendChild($option);

            $scNameFromConfig = $databaseConfig ? $databaseConfig->getDatabaseSchema() : '';
            $stmt = $pdo->query("SELECT nspname FROM pg_catalog.pg_namespace WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema'; ");

            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
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
    public static function showSidebarTables($pdo, $applicationId, $databaseName, $schemaName, $table) //NOSONAR
    {
        $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');
        
        // Create heading
        $h3 = $dom->createElement('h3', 'Table');
        $dom->appendChild($h3);

        // Create unordered list
        $ul = $dom->createElement('ul');
        $ul->setAttribute('class', 'table-list');

        if ($dbType == 'mysql' || $dbType == 'mariadb' || $dbType == 'pgsql') {
            // Query for MySQL and PostgreSQL to retrieve table list
            $sql = $dbType == 'mysql' || $dbType == 'mariadb' ? "SHOW TABLES" : "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '$schemaName' ORDER BY table_name ASC";
            $stmt = $pdo->query($sql);
            
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tableName = $row[0];

                // Create list item and link
                $li = $dom->createElement('li');
                $a = $dom->createElement('a', $tableName);
                $a->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$tableName");

                // Highlight the selected table
                if ($table == $tableName) {
                    $a->setAttribute('style', 'font-weight: bold;'); //NOSONAR
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
                    $a->setAttribute('style', 'font-weight: bold;'); //NOSONAR
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
        // Menentukan tipe database (MySQL, PostgreSQL, atau SQLite) berdasarkan PDO
        $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Mendapatkan schema atau database aktif
        if ($dbType == 'mysql' || $dbType == 'mariadb') {
            // Query untuk MySQL
            $sql = "DESCRIBE `$tableName`";
        } elseif ($dbType == 'pgsql') {
            // Mengambil nama schema di PostgreSQL
            
            // Query untuk PostgreSQL
            $sql = "SELECT 
                column_name AS \"Field\", 
                data_type AS \"Type\", 
                is_nullable AS \"Null\", 
                CASE 
                    WHEN column_default IS NOT NULL THEN 'DEFAULT' 
                    ELSE '' 
                END AS \"Key\", 
                column_default AS \"Default\", 
                CASE 
                    WHEN is_identity = 'YES' THEN 'AUTO_INCREMENT' 
                    ELSE '' 
                END AS \"Extra\"
                FROM information_schema.columns
                WHERE table_name = '$tableName'
                AND table_schema = '$schemaName'";
        } elseif ($dbType == 'sqlite') {
            // SQLite tidak memiliki schema, jadi hanya menggunakan query untuk menampilkan kolom
            $sql = "PRAGMA table_info($tableName)";
        } else {
            // Jika tipe database tidak didukung
            return null;
        }

        return $sql;
    }

    /**
     * Displays the structure of a table.
     *
     * This function fetches the table structure (columns, types, keys, etc.) from the database
     * and displays it in an HTML table.
     *
     * @param PDO $pdo A PDO instance connected to the database.
     * @param string $schemaName The name of the schema (for PostgreSQL).
     * @param string $table The name of the table whose structure is to be shown.
     * @return string The generated HTML.
     */
    public static function showTableStructure($pdo, $schemaName, $table) //NOSONAR
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
        
        // Fetch the columns
        $query = self::getQueryShowColumns($pdo, $schemaName, $table);
        if (isset($query)) {
            $stmt = $pdo->query($query);
            
            if ($stmt) {
                $tbody = $dom->createElement('tbody');
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tr = $dom->createElement('tr');
                    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                        // For SQLite
                        $columns = [
                            $row['name'], 
                            $row['type'], 
                            $row['notnull'] ? 'NO' : 'YES', 
                            $row['pk'] == 1 ? 'PRI' : '', 
                            $row['dflt_value'] ? $row['dflt_value'] : 'NULL', 
                            strtoupper($row['type']) == 'INTEGER' && $row['pk'] == 1 ? 'auto_increment' : ''
                        ];
                    } else {
                        // For MySQL or PostgreSQL
                        $columns = $row;
                    }
                    foreach ($columns as $value) {
                        $td = $dom->createElement('td', $value);
                        $tr->appendChild($td);
                    }
                    $tbody->appendChild($tr);
                }
                $tableElem->appendChild($tbody);
            }
        }
        
        $divInner->appendChild($tableElem);
        $dom->appendChild($divOuter);
        
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

            // Add table rows
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tr = $dom->createElement('tr');
                foreach ($row as $value) {
                    $td = $dom->createElement('td', htmlspecialchars($value));
                    $tr->appendChild($td);
                }
                $tableElem->appendChild($tr);
            }

            $dom->appendChild($tableElem);

            // Output the HTML
            return $dom->saveHTML();
        } catch (PDOException $e) {
            // Create the DOM document for error
            $dom = new DOMDocument('1.0', 'utf-8');

            // Create error div
            $div = $dom->createElement('div', "Error: " . $e->getMessage());
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
        $offset = ($page - 1) * $limit;
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM $table");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($total / $limit);

        $cls = isset($_POST['query']) ? '' : ' open';

        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');
        
        // Create the outer div
        $divOuter = $dom->createElement('div');
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
            }

            $lastPage = $dom->createElement('a', 'Last');
            $lastPage->setAttribute('href', "?applicationId=$applicationId&database=$databaseName&schema=$schemaName&table=$table&page=$totalPages");
            $paginationDiv->appendChild($lastPage);
        }

        // Append the pagination div to the DOM
        $divOuter->appendChild($paginationDiv);
        $dom->appendChild($divOuter);

        // Output the HTML with pagination
        return $dom->saveHTML();

    }

    /**
     * Creates a query execution form.
     *
     * This function generates an HTML form for executing SQL queries. The form includes a textarea
     * pre-populated with the last executed queries, and buttons to submit or reset the form.
     *
     * @param string $lastQueries The last executed SQL queries to be pre-populated in the textarea.
     * @return string The generated HTML.
     */
    public static function createQueryExecutorForm($lastQueries)
    {
        // Create the DOM document
        $dom = new DOMDocument('1.0', 'utf-8');

        // Create heading
        $h3 = $dom->createElement('h3', 'Execute Query');
        $dom->appendChild($h3);

        // Create form
        $form = $dom->createElement('form');
        $form->setAttribute('method', 'post');

        // Create textarea
        $textarea = $dom->createElement('textarea', htmlspecialchars($lastQueries));
        $textarea->setAttribute('name', 'query');
        $textarea->setAttribute('rows', '4');
        $textarea->setAttribute('cols', '50');
        $textarea->setAttribute('spellcheck', 'false');
        $form->appendChild($textarea);

        // Create line break
        $br = $dom->createElement('br');
        $form->appendChild($br);

        // Create submit button
        $submit = $dom->createElement('input');
        $submit->setAttribute('type', 'submit');
        $submit->setAttribute('value', 'Execute');
        $form->appendChild($submit);

        // Add space between buttons
        $space = $dom->createTextNode(' ');
        $form->appendChild($space);

        // Create reset button
        $reset = $dom->createElement('input');
        $reset->setAttribute('type', 'reset');
        $reset->setAttribute('value', 'Reset');
        $form->appendChild($reset);

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

                // Create the container div for each query
                $queryDiv = $dom->createElement('div');
                $queryDiv->setAttribute('class', 'query-executed');

                // Create the query title div
                $queryTitleDiv = $dom->createElement('div', 'Query ' . $j);
                $queryTitleDiv->setAttribute('class', 'query-title');
                $queryDiv->appendChild($queryTitleDiv);

                // Create the raw query div
                $queryRawDiv = $dom->createElement('div', htmlspecialchars($q));
                $queryRawDiv->setAttribute('class', 'query-raw');
                $queryRawDiv->setAttribute('contenteditable', 'true');
                $queryRawDiv->setAttribute('spellcheck', 'false');
                $queryDiv->appendChild($queryRawDiv);

                // Create the result title div
                $resultTitleDiv = $dom->createElement('div', 'Result');
                $resultTitleDiv->setAttribute('class', 'query-title');
                $queryDiv->appendChild($resultTitleDiv);

                // Create the result div
                $resultDiv = $dom->createElement('div');
                $resultDiv->setAttribute('class', 'query-result');

                // Execute the query and append the result (HTML content)
                $resultHTML = new DOMDocument();
                $resultHTML->loadHTML(DatabaseExplorer::executeQuery($pdo, $q));
                foreach ($resultHTML->getElementsByTagName('body')->item(0)->childNodes as $child) {
                    $resultDiv->appendChild($dom->importNode($child, true));
                }
                $queryDiv->appendChild($resultDiv);

                // Append the queryDiv to the DOM
                $dom->appendChild($queryDiv);
            }

            // Output the HTML
            return $dom->saveHTML();
        }
        return "";
    }
}

// Begin database preparation
$inputGet = new InputGet();
$inputPost = new InputPost();

// Get application ID, database name, schema name, table, page, and query from input
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$databaseName = $inputGet->getDatabase(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$schemaName = $inputGet->getSchema(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$table = $inputGet->getTable(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$page = $inputGet->getPage(PicoFilterConstant::FILTER_SANITIZE_NUMBER_UINT, false, false, true);
$query = $inputPost->getQuery();

// Get the row limit per page
$limit = $builderConfig->getDataLimit(); // Rows per page

// Determine application ID and check if it is from the default application
if (empty($applicationId)) {
    $appId = $curApp->getId();
    $fromDefaultApp = true;
} else {
    $appId = $applicationId;
    $fromDefaultApp = false;
}

// Ensure the page number is at least 1
if ($page < 1) {
    $page = 1;
}

// Ensure the row limit is at least 1
if ($limit < 1) {
    $limit = 1;
}

// Load the database configuration
$databaseConfig = new SecretObject();
$appConfigPath = $workspaceDirectory . "/applications/$appId/default.yml";

if (file_exists($appConfigPath)) {
    $appConfig->loadYamlFile($appConfigPath, false, true, true);
    $databaseConfig = $appConfig->getDatabase();

    if (!isset($databaseConfig)) {
        exit();
    }
    if (!empty($databaseName)) {
        $databaseConfig->setDatabaseName($databaseName);
    }
    if (empty($schemaName)) {
        $schemaName = $databaseConfig->getDatabaseSchema();
    }

    $database = new PicoDatabase($databaseConfig);

    try {
        // Connect to the database and set schema for PostgreSQL
        $database->connect();
        $dbType = $database->getDatabaseType();
        if ($dbType == 'pgsql') {
            $database->query("SET search_path TO $schemaName;");
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
} else {
    exit();
}

// Get the PDO instance and set error mode
$pdo = $database->getDatabaseConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// End database preparation

// Initialize variables for query processing
$queries = array();
$lastQueries = "";

// Split and sanitize the query if it exists
if ($query) {
    $arr = DatabaseExplorer::splitSQL($query);
    $query = implode(";\r\n", $arr);
    $queryArray = PicoDatabaseUtil::splitSql($query);
    if (isset($queryArray) && is_array($queryArray) && !empty($queryArray)) {
        $q2 = array();
        foreach ($queryArray as $q) {
            $queries[] = $q['query'];
            $q2[] = "-- " . $q['query'];
        }
        $lastQueries = implode("\r\n", $q2);
    } else {
        $lastQueries = $query;
    }
} else {
    $queryArray = null;
}

// Execute queries and handle results
if ($query && !empty($queries)) {
    try {
        $queryResult = DatabaseExplorer::executeQueryResult($pdo, $q, $query, $queries);
    } catch (Exception $e) {
        $queryResult = "Error: " . $e->getMessage();
    }
} else {
    $queryResult = "";
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Explorer</title>
    <link rel="stylesheet" href="css/database-explorer.css">
    <script>
        window.onload = function() {
            // Select all toggle buttons within collapsible elements
            const toggles = document.querySelectorAll('.collapsible .button-toggle');

            // Attach event listeners to each toggle button
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    // Find the closest collapsible element and toggle the 'open' class
                    e.target.closest('.collapsible').classList.toggle('open');
                });
            });
        };
    </script>
</head>
<body data-from-default-app="<?php echo $fromDefaultApp ? 'true' : 'false'; ?>">
    <div class="sidebar">
        <?php
        try {
            // Show the sidebar with databases if not from default app and not using SQLite
            if (!$fromDefaultApp && $dbType != 'sqlite') {
                echo DatabaseExplorer::showSidebarDatabases($pdo, $applicationId, $databaseName, $schemaName, $databaseConfig);
            }

            // Show the sidebar with tables
            echo DatabaseExplorer::showSidebarTables($pdo, $applicationId, $databaseName, $schemaName, $table);
        } catch (PDOException $e) {
            // Handle connection errors
            if ($e->getCode() == 0x3D000 || strpos($e->getMessage(), '1046') !== false) {
                echo "Please choose one database";
            } else {
                echo "Connection failed: " . $e->getMessage();
            }
        }
        ?>
    </div>
    <div class="content">
        <?php
        // Display table structure and data if a table is selected
        if ($table) {
            echo DatabaseExplorer::showTableStructure($pdo, $schemaName, $table);
            echo DatabaseExplorer::showTableData($pdo, $applicationId, $databaseName, $schemaName, $table, $page, $limit);
        }

        // Display the query executor form and results
        echo DatabaseExplorer::createQueryExecutorForm($lastQueries);
        echo $queryResult;
        ?>
    </div>
</body>
</html>