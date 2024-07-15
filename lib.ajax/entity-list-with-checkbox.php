<?php

use AppBuilder\Util\EntityUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$separatorNLT = "\r\n\t";

try
{
    $inputGet = new InputGet();
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $chk = $inputGet->getAutoload() == 'true' ? ' checked' : '';


    echo "<div>\r\n";
    echo '<div style="white-space:nowrap"><input type="checkbox" id="entity-check-controll"'.$chk.'> <label for="entity-check-controll">Select all</label></div>';
    echo '<div style="white-space:nowrap"><input type="checkbox" id="entity-merge" class="entity-merge" checked> <label for="entity-merge">Merge queries per table</label></div>';
    
    echo "<h4>Data</h4>\r\n";

    $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    
    $list = glob($baseDir."/*.php");
    $li = array();

    foreach($list as $idx=>$file)
    {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        exec("php -l $file 2>&1", $output, $return_var);
        if($return_var === 0)
        {
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
            if(!isset($li[$tableName]))
            {
                $li[$tableName]  = array();
            }
            $li[$tableName][] = '<li class="entity-li"><input type="checkbox" class="entity-checkbox" name="entity['.$idx.']" value="'.$dir.'\\'.$entity.'"'.$chk.'> <a href="#" data-entity-name="'.$dir.'\\'.$entity.'">'.$entity.'</a></li>';
        }
        else
        {
            if(!isset($li[$idx]))
            {
                $li[$idx]  = array();
            }
            $li[$idx][] = '<li class="entity-li file-syntax-error"><input type="checkbox" class="entity-checkbox" name="entity['.$idx.']" value="'.$dir.'\\'.$entity.'" disabled> '.$entity.'</li>';
        }
    }
    ksort($li);

    $lim = array();
    foreach($li as $elem)
    {
        $lim = array_merge($lim, $elem);
    }

    echo '<ul class="entity-ul">'.$separatorNLT.implode($separatorNLT, $lim)."\r\n".'</ul>'."\r\n";

    echo "<h4>App</h4>\r\n";

    $baseEntity = $appConfig->getApplication()->getBaseEntityAppNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    $list = glob($baseDir."/*.php");
    $li = array();
    foreach($list as $idx=>$file)
    {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        exec("php -l $file 2>&1", $output, $return_var);
        if($return_var === 0)
        {
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
            if(!isset($li[$tableName]))
            {
                $li[$tableName]  = array();
            }
            $li[$tableName][] = '<li class="entity-li"><input type="checkbox" class="entity-checkbox" name="entity['.$idx.']" value="'.$dir.'\\'.$entity.'"'.$chk.'> <a href="#" data-entity-name="'.$dir.'\\'.$entity.'">'.$entity.'</a></li>';
        }
        else
        {
            if(!isset($li[$idx]))
            {
                $li[$idx]  = array();
            }
            $li[$idx][] = '<li class="entity-li file-syntax-error"><input type="checkbox" class="entity-checkbox" name="entity['.$idx.']" value="'.$dir.'\\'.$entity.'" disabled> '.$entity.'</li>';
        }
    }
    ksort($li);
 
    $lim = array();
    foreach($li as $elem)
    {
        $lim = array_merge($lim, $elem);
    }

    echo '<ul class="entity-ul">'.$separatorNLT.implode($separatorNLT, $lim)."\r\n".'</ul>'."\r\n";
    echo "</div>\r\n";
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
