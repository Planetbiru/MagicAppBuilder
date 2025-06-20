<?php

use DatabaseExplorer\ConstantText;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once __DIR__ . "/inc.db/config.php";

$inputGet = new InputGet();
$pdo = $database->getDatabaseConnection();

$dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
$tableList = array();

if ($dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
    // Query for MySQL and PostgreSQL to retrieve table list
    $sql = $dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB ? ConstantText::SHOW_TABLES : "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '$schemaName' ORDER BY table_name ASC";
    $stmt = $pdo->query($sql);
    
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tableName = $row[0];
        $tableList[] = $tableName;
    }
} elseif ($dbType == 'sqlite') {
    // Query for SQLite to retrieve table list
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tableName = $row['name'];
        $tableList[] = $tableName;
    }
} else {
    // Unsupported database type
}

$selected =' selected';

?>

<div class="input-label">
    Target Database Type 
    <select class="form-control" name="target_database_type">
        <option value="mysql"<?php echo $dbType == 'mysql' ? $selected : '';?>>MySQL</option>
        <option value="mariadb"<?php echo $dbType == 'mariadb' ? $selected : '';?>>MariaDB</option>
        <option value="postgresql"<?php echo $dbType == 'pgsql' || $dbType == 'postgresql' ? $selected : '';?>>PostgreSQL</option>
        <option value="sqlite"<?php echo $dbType == 'sqlite' ? $selected : '';?>>SQLite</option>
        <option value="sqlserver"<?php echo $dbType == 'sqlserver' ? $selected : '';?>>SQL Server</option>
    </select>
</div>

<table width="100%" border="1" class="table-export-database">
    <thead>
        <tr>
            <td width="20">No</td>
            <td width="98">
                <input type="checkbox" id="cstructure">
                <label for="cstructure">Structure</label>
            </td>
            <td width="70">
                <input type="checkbox" id="cdata">
                <label for="cdata">Data</label>
            </td>
            <td>Table Name</td>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 0;
        foreach($tableList as $table)
        {
            $ecodedTableName = htmlspecialchars($table);
            $no++;
        ?>
        <tr data-table-name="<?php echo $ecodedTableName; ?>">
            <td align="right"><?php echo $no;?></td>
            <td>
                <input type="checkbox" name="structure_export[]" value="<?php echo $ecodedTableName; ?>" id="structure_<?php echo $ecodedTableName; ?>" class="check-for-structure">
                <label for="structure_<?php echo $ecodedTableName; ?>">Structure</label>
            </td>
            <td>
                <input type="checkbox" name="data_export[]" value="<?php echo $ecodedTableName; ?>" id="data_<?php echo $ecodedTableName; ?>" class="check-for-data">
                <label for="data_<?php echo $ecodedTableName; ?>">Data</label>
            </td>
            <td class="table-name"><?php echo $ecodedTableName; ?></td>
        </tr>
        <?php 
        }
        ?>
    </tbody>
</table>
