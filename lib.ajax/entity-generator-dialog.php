<?php

use AppBuilder\AppDatabase;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$inputGet = new InputGet();
$currentTableName = $inputGet->getTableName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$currentEntityName = $inputGet->getEntityName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$primaryKey = "";
try {
    $databaseType = $database->getDatabaseType();

    $tables = AppDatabase::getTableList($database, $databaseName, $schemaName, true, true);

    ?>
        <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td>Table Name</td>
                    <td>
                        <select class="form-control" name="entity_generator_table_name">
                            <option value="">Select One</option>
                            <?php
                            // Kelompokkan tabel berdasarkan table_group
                            $groupedTables = [
                                'custom' => [],
                                'system' => []
                            ];

                            foreach ($tables as $table) {
                                $group = isset($table['table_group']) ? strtolower($table['table_group']) : 'custom';
                                $groupedTables[$group][] = $table;
                            }

                            // Urutkan: custom dulu, baru system
                            foreach (['custom', 'system'] as $group) {
                                if (!empty($groupedTables[$group])) {
                                    ?>
                                    <optgroup label="<?php echo ucfirst($group); ?>">
                                        <?php
                                        foreach ($groupedTables[$group] as $table) {
                                            $selected = "";
                                            if ($table['table_name'] == $currentTableName) {
                                                $selected = " selected";
                                                $primaryKey = isset($table['primary_key'][0]) ? $table['primary_key'][0] : '';
                                            }
                                            
                                            
                                            ?>
                                            <option value="<?php echo $table['table_name']; ?>"
                                                    data-primary-keys="<?php echo implode(",", $table['primary_key']); ?>"<?php echo $selected; ?>>
                                                <?php echo $table['table_name']; ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </optgroup>
                                    <?php
                                }
                            }
                            ?>
                        </select>

                    </td>
                </tr>
                <tr>
                    <td>Entity Name</td>
                    <td><input type="text" class="form-control" name="entity_generator_entity_name" value="<?php echo htmlspecialchars($currentEntityName);?>"></td>
                </tr>
                <tr>
                    <td>Primary Key</td>
                    <td><input type="text" class="form-control" name="entity_generator_primary_key" value="<?php echo htmlspecialchars($primaryKey);?>"></td>
                </tr>
            </tbody>
        </table>
    <?php

} catch (Exception $e) {
    // Log the error for debugging purposes
    error_log("Error: " . $e->getMessage());
}