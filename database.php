<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once (__DIR__) . "/inc.app/app.php";
require_once (__DIR__) . "/inc.app/sessions.php";
require_once (__DIR__) . "/inc.app/database.php";

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
function splitSQL($sqlString)
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
 * @return void
 */
function showSidebarDatabases($pdo, $applicationId, $databaseName)
{
    echo '<h3>Database</h3>';
    echo '<form method="GET" action="">';
    echo '<input type="hidden" name="applicationId" value="' . $applicationId . '">';

    echo '<select name="database" id="database-select" onchange="this.form.submit()">';
    echo '<option value="">-- Choose Database --</option>';
    $stmt = $pdo->query('SHOW DATABASES');
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $dbName = $row[0];
        $selected = $databaseName === $dbName ? 'selected' : '';
        echo '<option value="' . $dbName . '" ' . $selected . '>' . $dbName . '</option>';
    }
    echo '</select>';
    echo '</form>';
}

/**
 * Displays a sidebar with tables in the selected database.
 *
 * This function retrieves and displays the list of tables in the selected database.
 * The tables are displayed as links, and the selected table is highlighted in bold.
 *
 * @param PDO $pdo A PDO instance connected to the MySQL server.
 * @param string $applicationId The application identifier.
 * @param string $databaseName The name of the selected database.
 * @param string $table The currently selected table.
 * @return void
 */
function showSidebarTables($pdo, $applicationId, $databaseName, $table)
{
    $pdo->exec("USE `$databaseName`");
    $stmt = $pdo->query("SHOW TABLES");
    echo '<ul class="table-list">';
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tableName = $row[0];
        echo '<li><a href="?applicationId=' . $applicationId . '&database=' . $databaseName . '&table=' . $tableName . '"' .
            ($table == $tableName ? ' style="font-weight: bold;"' : '') . '>' . $tableName . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Displays the structure of a table.
 *
 * This function fetches the table structure (columns, types, keys, etc.) from the database
 * and displays it in an HTML table.
 *
 * @param PDO $pdo A PDO instance connected to the MySQL server.
 * @param string $table The name of the table whose structure is to be shown.
 * @return void
 */
function showTableStructure($pdo, $table)
{
    echo '<div class="table-structure collapsible">';
    echo '<button class="button-toggle toggle-structure" data-open="-" data-close="+"></button>';
    echo '<h3>Structure of ' . $table . '</h3>';
    echo '<div class="table-structure-inner">';
    $stmt = $pdo->query("DESCRIBE `$table`");
    echo '<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        foreach ($row as $value) {
            echo '<td>' . $value . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    echo '</div>';
}

/**
 * Executes a SQL query and displays the result in a table.
 *
 * This function executes a given SQL query and displays the result in a formatted HTML table.
 * If the query results in an error, the error message is displayed.
 *
 * @param PDO $pdo A PDO instance connected to the MySQL server.
 * @param string $query The SQL query to be executed.
 * @return void
 */
function executeQuery($pdo, $query)
{
    try {
        $stmt = $pdo->query($query);
        echo "<table><tr>";
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $col = $stmt->getColumnMeta($i);
            echo "<th>{$col['name']}</th>";
        }
        echo "</tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<div class='sql-error'><strong>Error:</strong> " . $e->getMessage() . "</div>";
    }
}

/**
 * Displays table data with pagination.
 *
 * This function retrieves and displays table data with pagination. The data is shown in a table
 * format, and pagination links are provided to navigate through pages of results.
 *
 * @param PDO $pdo A PDO instance connected to the MySQL server.
 * @param string $applicationId The application identifier.
 * @param string $databaseName The name of the selected database.
 * @param string $table The name of the selected table.
 * @param int $page The current page number.
 * @param int $limit The number of rows to display per page.
 * @return void
 */
function showTableData($pdo, $applicationId, $databaseName, $table, $page, $limit)
{
    $offset = ($page - 1) * $limit;
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `$table`");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($total / $limit);

    $cls = isset($_POST['query']) ? '' : ' open';
    echo '<div class="table-content collapsible' . htmlspecialchars($cls) . '">';
    echo '<button class="button-toggle toggle-structure" data-open="-" data-close="+"></button>';
    echo '<h3>Data in ' . htmlspecialchars($table) . ' (Page ' . htmlspecialchars($page) . ')</h3>';
    echo '<div class="table-content-inner">';
    $stmt = $pdo->query("SELECT * FROM `$table` LIMIT $limit OFFSET $offset");
    echo '<table><tr>';
    for ($i = 0; $i < $stmt->columnCount(); $i++) {
        $col = $stmt->getColumnMeta($i);
        echo '<th>' . htmlspecialchars($col['name']) . '</th>';
    }
    echo '</tr>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        foreach ($row as $value) {
            echo '<td>' . htmlspecialchars($value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';

    // Pagination navigation with a maximum of 7 pages
    echo '<div class="pagination">';
    $startPage = max(1, $page - 3);
    $endPage = min($totalPages, $page + 3);

    if ($startPage > 1) {
        echo '<a href="?applicationId=' . htmlspecialchars($applicationId) . '&database=' . htmlspecialchars($databaseName) . '&table=' . htmlspecialchars($table) . '&page=1">First</a> ';
        if ($startPage > 2) {
            echo '<span>...</span> ';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $page) {
            echo '<a href="?applicationId=' . htmlspecialchars($applicationId) . '&database=' . htmlspecialchars($databaseName) . '&table=' . htmlspecialchars($table) . '&page=' . htmlspecialchars($i) . '" style="font-weight: bold;">' . htmlspecialchars($i) . '</a> ';
        } else {
            echo '<a href="?applicationId=' . htmlspecialchars($applicationId) . '&database=' . htmlspecialchars($databaseName) . '&table=' . htmlspecialchars($table) . '&page=' . htmlspecialchars($i) . '">' . htmlspecialchars($i) . '</a> ';
        }
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            echo '<span>...</span> ';
        }
        echo '<a href="?applicationId=' . htmlspecialchars($applicationId) . '&database=' . htmlspecialchars($databaseName) . '&table=' . htmlspecialchars($table) . '&page=' . htmlspecialchars($totalPages) . '">Last</a>';
    }
    echo '</div>';
    echo '</div>';
}

$inputGet = new InputGet();
$inputPost = new InputPost();
$applicationId = $inputGet->getApplicationId();
if (empty($applicationId)) {
    $applicationId = $curApp->getId();
}
$databaseName = $inputGet->getDatabase();
$table = $inputGet->getTable();

$page = $inputGet->getPage();
if ($page < 1) {
    $page = 1;
}
$limit = $builderConfig->getDataLimit(); // Rows per page
if ($limit < 1) {
    $limit = 20;
}
$query = $inputPost->getQuery();

$appConfigPath = $workspaceDirectory . "/applications/$applicationId/default.yml";
if (file_exists($appConfigPath)) {
    $appConfig->loadYamlFile($appConfigPath, false, true, true);
    $databaseConfig = $appConfig->getDatabase();
    if (empty($databaseName)) {
        $databaseName = $databaseConfig->getDatabaseName();
    } else {
        $databaseConfig->setDatabaseName($databaseName);
    }
    $database = new PicoDatabase($databaseConfig);

    try {
        $database->connect();
    } catch (Exception $e) {
        exit();
    }
} else {
    exit();
}

$pdo = $database->getDatabaseConnection();

?>
<!DOCTYPE html>
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

<body>
    <div class="sidebar">
        <?php
        // Database connection configuration
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Variables to control navigation
            // Render sidebar
            showSidebarDatabases($pdo, $applicationId, $databaseName);
            showSidebarTables($pdo, $applicationId, $databaseName, $table);
        } catch (PDOException $e) {
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
        try {
            // Load table structure or data based on navigation
            if ($databaseName) {
                $pdo->exec("USE `$databaseName`");
                if ($table) {
                    // Show table structure
                    showTableStructure($pdo, $table);
                    showTableData($pdo, $applicationId, $databaseName, $table, $page, $limit);
                }
            }

            $queries = array();
            $lastQueries = "";
            if ($query) {
                $arr = splitSQL($query);
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

            // Query execution form
            echo '<h3>Execute Query</h3>';
            echo '<form method="post">
                    <textarea name="query" rows="4" cols="50" spellcheck="false">' . htmlspecialchars($lastQueries) . '</textarea><br>
                    <input type="submit" value="Execute">
                    <input type="reset" value="Reset">
                </form>';

            if ($query) {
                foreach ($queries as $i => $q) {
                    $j = $i + 1;
                    $query2Executed = $q;
                    echo "<div class='query-executed'>";
                    echo "<div class='query-title'>Query $j</div>";
                    echo "<div class='query-raw' contenteditable='true' spellcheck='false'>";
                    echo htmlspecialchars($q);
                    echo "</div>";
                    echo "<div class='query-title'>Result</div>";
                    echo "<div class='query-result'>";
                    executeQuery($pdo, $q);
                    echo "</div>";
                    echo "</div>";
                }
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>
</body>
</html>