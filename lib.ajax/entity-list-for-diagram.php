<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
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

    echo "<h4>Data</h4>\r\n";

    $baseEntity = $appConfig->getApplication()->getBaseEntityDataNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    
    $list = glob($baseDir."/*.php");
    $li = [];
    $format1 = '<li class="entity-li"><input type="checkbox" class="entity-checkbox" name="entity[%d]" value="%s\\%s"%s> <a href="#" data-entity-name="%s\\%s" data-toggle="tooltip" data-placement="top" title="%s">%s</a></li>';
    $format2 = '<li class="entity-li file-syntax-error"><input type="checkbox" class="entity-checkbox" name="entity[%d]" value="%s\\%s" disabled data-toggle="tooltip" data-placement="top" title="%s"> %s</li>';
    
    foreach($list as $idx=>$file)
    {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        $return_var = ErrorChecker::errorCheck($cacheDir, $file);       
        if($return_var === 0)
        {
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
            if(!isset($li[$tableName]))
            {
                $li[$tableName]  = [];
            }
            $li[$tableName][] = sprintf($format1, $idx, $dir, $entity, $chk, $dir, $entity, $filetime, $entity);
        }
        else
        {
            if(!isset($li[$idx]))
            {
                $li[$idx]  = [];
            }
            $li[$idx][] = sprintf($format2, $idx, $dir, $entity, $filetime, $entity);
        }
    }
    ksort($li);

    $lim = [];
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
    $li = [];
    foreach($list as $idx=>$file)
    {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        $return_var = ErrorChecker::errorCheck($cacheDir, $file); 
        if($return_var === 0)
        {
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
            if(!isset($li[$tableName]))
            {
                $li[$tableName]  = [];
            }
            $li[$tableName][] = sprintf($format1, $idx, $dir, $entity, $chk, $dir, $entity, $filetime, $entity);
        }
        else
        {
            if(!isset($li[$idx]))
            {
                $li[$idx]  = [];
            }
            $li[$idx][] = sprintf($format2, $idx, $dir, $entity, $filetime, $entity);
        }
    }
    ksort($li);
 
    $lim = [];
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