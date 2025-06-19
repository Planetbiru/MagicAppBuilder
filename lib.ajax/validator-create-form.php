<?php

use AppBuilder\AppDatabase;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$inputPost = new InputPost();
if($inputPost->getUserAction() == 'select-table')
{
    $tables = array(
        'table_name' => array()
    );
    try {
        $databaseType = $database->getDatabaseType();
        $schemaName = $databaseConfig->getDatabaseSchema();
        $databaseName = $databaseConfig->getDatabaseName();
        $tables = AppDatabase::getTableList($database, $databaseName, $schemaName);
    }
    catch(Exception $e)
    {
        // Do nothing
    }
    ?>
    <table class="config-table">
    <tbody>
        <tr>
            <td>Table Name</td>
            <td>
                <select name="tableName" class="form-control" onchange="this.form.querySelector('[name=validatorName]').value = this.options[this.selectedIndex].dataset.validatorClassName">
                    <option value="" data-validator-class-name="">- Select One -</option>
                    <?php
                    foreach($tables as $tableName=>$tableInfo)
                    {
                        $validatorName = PicoStringUtil::upperCamelize($tableName)."Validator";
                        ?>
                        <option value="<?php echo $tableName;?>" data-validator-class-name="<?php echo $validatorName;?>"><?php echo $tableName;?></option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Validator Class Name</td>
            <td><input name="validatorName" class="form-control"></td>
        </tr>
    </tbody>
</table>

    <?php
}
else if($inputPost->getUserAction() == 'create-form')
{
$tableName = $inputPost->getTableName();
$validatorName = $inputPost->getValidatorName();

$tableColumnInfo = AppDatabase::getColumnList($appConfig, $databaseConfig, $database, $tableName);

$fields = $tableColumnInfo['fields'];
?>

<table class="config-table">
    <tbody>
        <tr>
            <td>Table Name</td>
            <td><?php echo $tableName;?></td>
        </tr>
        <tr>
            <td>Validator Class Name</td>
            <td><?php echo $validatorName;?></td>
        </tr>
    </tbody>
</table>
<hr>
<?php
foreach($fields as $field)
{
    $fieldName = $field['column_name'];
    if(!in_array($fieldName, $tableColumnInfo['skipped_insert_edit']))
    {
?>
<div class="mb-3 field-group validation-item" data-field="<?php echo $fieldName;?>">
    <span class="form-label"><?php echo $fieldName;?></span>
    <div class="field-validations-list mt-2"></div>
    <button type="button" class="btn btn-primary mt-2 add-validation"><i class="fa-solid fa-plus"></i> Add Validation</button>
</div>
<?php
}
}
?>
<pre class="validation-output"></pre>
<?php
}