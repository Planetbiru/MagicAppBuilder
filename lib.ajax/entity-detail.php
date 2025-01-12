<?php

use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputGet = new InputGet();
try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));  
    $allQueries = [];
    if($inputGet->getNamespaceName() != '' && $inputGet->getEntityName() != '')
    {
        $entityName = $inputGet->getNamespaceName() . "\\" . $inputGet->getEntityName();
        $className = "\\".$baseEntity."\\".$entityName;
        $entityName = trim($entityName);
        $path = $baseDir."/".$entityName.".php";
        if(file_exists($path))
        {
            $return_var = ErrorChecker::errorCheck($cacheDir, $path);
            if($return_var == 0)
            {
                include_once $path;                  
                $entity = new $className(null, null);
                $tableInfo = $entity->tableInfo();
                $columns = $tableInfo->getColumns();
                ?>
                <h3 class="entity-table-name">Entity Name: <?php echo $entityName;?></h3>
                <h3 class="entity-table-name">Table Name: <?php echo $tableInfo->getTableName();?></h3>
                <div class="entity-table-container">
                <table width="100%" class="table entity-table">
                    <thead>
                        <tr>
                            <td width="24">No</td>
                            <td>Field</td>
                            <td width="15%">Type</td>
                            <td width="10%">Length</td>
                            <td width="10%">Nullable</td>
                            <td width="23%">Extra</td>
                        </tr>
                    </thead>
                    
                    <tbody>
                <?php
                $no = 0;
                foreach($columns as $column)
                {
                    $no++;
                    ?>
                        <tr>
                            <td align="right"><?php echo $no;?></td>
                            <td><?php echo $column['name'];?></td>
                            <td><?php echo $column['type'];?></td>
                            <td><?php echo isset($column['length']) ? $column['length'] : '';?></td>
                            <td><?php echo isset($column['nullable']) ? $column['nullable'] : '';?></td>
                            <td><?php echo isset($column['extra']) ? $column['extra'] : '';?></td>
                        </tr>
                    <?php
                }
                ?>
                    </tbody>
                </table>
                </div>
                <?php
            }
        }
    }
}
catch(Exception $e)
{
    // do nothing
}