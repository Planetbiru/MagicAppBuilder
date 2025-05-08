<?php

use AppBuilder\AppDatabase;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

try {
    $databaseType = $database->getDatabaseType();

    $tables = AppDatabase::getTableList($database, $databaseName, $schemaName);

    ?>
        <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td>Table Name</td>
                    <td>
                        <select class="form-control" name="entity_generator_table_name">
                            <option value="">Select One</option>
                            <?php
                            foreach($tables as $table)
                            {
                                ?>
                                <option value="<?php echo $table['table_name'];?>" data-primary-keys="<?php echo implode(",", $table['primary_key']);?>"><?php echo $table['table_name'];?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Entity Name</td>
                    <td><input type="text" class="form-control" name="entity_generator_entity_name"></td>
                </tr>
                <tr>
                    <td>Primary Key</td>
                    <td><input type="text" class="form-control" name="entity_generator_primary_key"></td>
                </tr>
            </tbody>
        </table>
    <?php

} catch (Exception $e) {
    // Log the error for debugging purposes
    error_log("Error: " . $e->getMessage());}