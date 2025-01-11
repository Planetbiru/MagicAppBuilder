<?php

use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputGet = new InputGet();
try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));  
    $allQueries = [];
    if(
        $inputGet->getNamespaceName() != '' 
        && $inputGet->getEntityName() != ''
        && $inputGet->getTableName() != ''
        && $inputGet->getColumnName() != ''

        && $inputGet->getReferenceNamespaceName() != '' 
        && $inputGet->getReferenceEntityName() != ''
        && $inputGet->getReferenceTableName() != ''
        && $inputGet->getReferenceColumnName() != ''
    )
    {
        $entityName = $inputGet->getNamespaceName() . "\\" . $inputGet->getEntityName();
        $className = "\\".$baseEntity."\\".$entityName;
        $entityName = trim($entityName);
        $columnName = $inputGet->getColumnName();
        $path1 = $baseDir."/".$entityName.".php";
        if(file_exists($path1))
        {
            $return_var = ErrorChecker::errorCheck($cacheDir, $path1);
            if($return_var == 0)
            {
                include_once $path1;                  
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
                    if($columnName == $column['name'])
                    {
                        $colPos1 = $no;
                    }
                    $no++;
                    ?>
                        <tr class="entity-column<?php echo $columnName == $column['name'] ? ' entity-column-selected' : '';?>">
                            <td align="right"><?php echo $no;?></td>
                            <td><?php echo $column['name'];?></td>
                            <td><?php echo $column['type'];?></td>
                            <td><?php echo isset($column['length']) ? $column['length'] : '';?></td>
                            <td><?php echo isset($column['nullable']) ? $column['nullable'] : '';?></td>
                            <td><?php echo isset($column['extra']) ? $column['extra'] : '';?></td>
                        </tr>
                    <?php
                }
                $ncol1 = $no;
                ?>
                    </tbody>
                </table>
                </div>
                <?php
            }
        }

        $entityName = $inputGet->getReferenceNamespaceName() . "\\" . $inputGet->getReferenceEntityName();
        $className = "\\".$baseEntity."\\".$entityName;
        $entityName = trim($entityName);
        $columnName = $inputGet->getReferenceColumnName();
        $path2 = $baseDir."/".$entityName.".php";
        if(file_exists($path2))
        {
            $return_var = ErrorChecker::errorCheck($cacheDir, $path2);
            if($return_var == 0)
            {
                include_once $path2;                  
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
                            <td>Column</td>
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
                    if($columnName == $column['name'])
                    {
                        $colPos2 = $no;
                    }
                    $no++;
                    ?>
                        <tr class="entity-column<?php echo $columnName == $column['name'] ? ' entity-column-selected' : '';?>">
                            <td align="right"><?php echo $no;?></td>
                            <td><?php echo $column['name'];?></td>
                            <td><?php echo $column['type'];?></td>
                            <td><?php echo isset($column['length']) ? $column['length'] : '';?></td>
                            <td><?php echo isset($column['nullable']) ? $column['nullable'] : '';?></td>
                            <td><?php echo isset($column['extra']) ? $column['extra'] : '';?></td>
                        </tr>
                    <?php
                }
                $ncol2 = $no;
                ?>
                    </tbody>
                </table>
                </div>
                <?php
            }
        }
        $rowHeight = 30.9;

        $corection = 90;
        $offset = 4;

        $height = $corection + ($rowHeight * (($ncol1 - $colPos1) + $colPos2 ));
        $marginTop = $offset - $corection + ($rowHeight * ($colPos1 - ($ncol1 + $ncol2)));

        ?>
        <div class="line-relation" style="
            position: absolute;
            width: 5px;
            margin-left: -5px;
            border: solid #666666;
            border-width: 1px 0 1px 1px;
            height: <?php echo $height;?>px;
            margin-top: <?php echo $marginTop;?>px;
            "></div>
        <?php

    }
}
catch(Exception $e)
{
    // do nothing
}