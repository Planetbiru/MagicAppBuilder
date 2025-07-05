<?php

use AppBuilder\AppDatabase;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use AppBuilder\Util\ValidatorUtil;
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


    $groupedTables = [
        'custom' => [],
        'system' => []
    ];

    foreach ($tables as $tableName => $tableInfo) {
        $group = isset($tableInfo['table_group']) ? strtolower($tableInfo['table_group']) : 'custom';
        $groupedTables[$group][$tableName] = $tableInfo;
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
                    foreach (['custom', 'system'] as $group) {
                        if (!empty($groupedTables[$group])) {
                            echo '<optgroup label="' . ucfirst($group) . '">';
                            foreach ($groupedTables[$group] as $tableName => $tableInfo) {
                                $validatorName = PicoStringUtil::upperCamelize($tableInfo['table_name']) . "Validator";
                                ?>
                                <option value="<?php echo $tableInfo['table_name']; ?>"
                                        data-validator-class-name="<?php echo $validatorName; ?>">
                                    <?php echo $tableInfo['table_name']; ?>
                                </option>
                                <?php
                            }
                            echo '</optgroup>';
                        }
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
<?php
foreach($fields as $field)
{
    $fieldName = $field['column_name'];
    $maximumLength = $field['maximum_length'];
    if(!in_array($fieldName, $tableColumnInfo['skipped_insert_edit']))
    {
?>
<hr>
<div class="mb-3 field-group validation-item" data-field-name="<?php echo $fieldName;?>" data-maximum-length="<?php echo $maximumLength;?>">
    <span class="form-label"><?php echo $fieldName;?></span>
    <div class="field-validations-list mt-2"></div>
    <button type="button" class="btn btn-primary mt-2 add-validation-merged"><i class="fa-solid fa-plus"></i> Add Validation</button>
</div>
<?php
}
}
?>
<hr>
<span class="form-label">Definition</span>
<input type="hidden" name="tableName" value="<?php echo htmlspecialchars($tableName);?>">
<input type="hidden" name="validatorName" value="<?php echo htmlspecialchars($validatorName);?>">
<textarea class="form-control validation-output" name="validatorDefinition" rows="5" readonly></textarea>
<?php
}
else if($inputPost->getUserAction() == 'update-form')
{
$path = ValidatorUtil::getPath($appConfig, $inputPost);
$data = ValidatorUtil::parseValidatorClass(file_get_contents($path));

$tableName = $data['tableName'];
$validatorName = $data['className'];

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
<?php
foreach($fields as $field)
{
    $fieldName = $field['column_name'];
    $maximumLength = $field['maximum_length'];
    if(!in_array($fieldName, $tableColumnInfo['skipped_insert_edit']))
    {
?>
<hr>
<div class="mb-3 field-group validation-item" data-field-name="<?php echo $fieldName;?>" data-maximum-length="<?php echo $maximumLength;?>">
    <span class="form-label"><?php echo $fieldName;?></span>
    <div class="field-validations-list mt-2"></div>
    <button type="button" class="btn btn-primary mt-2 add-validation-merged"><i class="fa-solid fa-plus"></i> Add Validation</button>
</div>
<?php
}
}
?>
<hr>
<span class="form-label">Definition</span>
<input type="hidden" name="tableName" value="<?php echo htmlspecialchars($tableName);?>">
<input type="hidden" name="validatorName" value="<?php echo htmlspecialchars($validatorName);?>">
<textarea class="form-control validation-output" name="validatorDefinition" rows="5" readonly></textarea>
<input name="existing" type="hidden" value="<?php echo htmlspecialchars(json_encode($data));?>">
<?php
}