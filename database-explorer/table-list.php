<?php

use AppBuilder\AppDatabase;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once __DIR__ . "/inc.db/config.php";

$inputGet = new InputGet();
$pdo = $database->getDatabaseConnection();
$dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

$schemaName = $databaseConfig->getDatabaseSchema();
$databaseName = $databaseConfig->getDatabaseName();

$tables = AppDatabase::getTableList($database, $databaseName, $schemaName);

$customTables = [];
$systemTables = [];
foreach ($tables as $tableName => $tableData) {
    if ($tableData['table_group'] === 'system') {
        $systemTables[] = $tableName;
    } else {
        $customTables[] = $tableName;
    }
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
        foreach ([['label' => 'Custom Tables', 'tables' => $customTables, 'group' => 'custom'],
                  ['label' => 'System Tables', 'tables' => $systemTables, 'group' => 'system']] as $group) {
            if (!empty($group['tables'])) {
                $groupId = htmlspecialchars($group['group']);
                echo '<tr class="group-header">
                        <td>
                        </td>
                        <td>
                            <input type="checkbox" id="check-group-structure-' . $groupId . '" class="check-group-structure" data-group="' . $groupId . '">
                            <label for="check-group-structure-' . $groupId . '">Structure</label>
                        </td>
                        <td>
                            <input type="checkbox" id="check-group-data-' . $groupId . '" class="check-group-data" data-group="' . $groupId . '">
                            <label for="check-group-data-' . $groupId . '">Data</label>
                        </td>
                        <td>
                            ' . htmlspecialchars($group['label']) . '
                        </td>
                      </tr>';

                foreach ($group['tables'] as $table) {
                    $ecodedTableName = htmlspecialchars($table);
                    $no++;
                    ?>
                    <tr data-table-name="<?php echo $ecodedTableName; ?>" data-group="<?php echo $groupId; ?>">
                        <td align="right"><?php echo $no;?></td>
                        <td>
                            <input type="checkbox" name="structure_export[]" value="<?php echo $ecodedTableName; ?>" id="structure_<?php echo $ecodedTableName; ?>" class="check-for-structure check-structure-<?php echo $groupId; ?>">
                            <label for="structure_<?php echo $ecodedTableName; ?>">Structure</label>
                        </td>
                        <td>
                            <input type="checkbox" name="data_export[]" value="<?php echo $ecodedTableName; ?>" id="data_<?php echo $ecodedTableName; ?>" class="check-for-data check-data-<?php echo $groupId; ?>">
                            <label for="data_<?php echo $ecodedTableName; ?>">Data</label>
                        </td>
                        <td class="table-name"><?php echo $ecodedTableName; ?></td>
                    </tr>
                    <?php
                }
            }
        }
        ?>
    </tbody>
</table>