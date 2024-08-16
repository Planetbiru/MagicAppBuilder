<?php

use AppBuilder\Util\EntityUtil;
use AppBuilder\Util\ErrorCacheUtil;
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
    $format1 = '<li class="entity-li"><input type="checkbox" class="entity-checkbox" name="entity[%d]" value="%s\\%s"%s> <a href="#" data-entity-name="%s\\%s" data-toggle="tooltip" data-placement="top" title="%s">%s</a></li>';
    $format2 = '<li class="entity-li file-syntax-error"><input type="checkbox" class="entity-checkbox" name="entity[%d]" value="%s\\%s" disabled data-toggle="tooltip" data-placement="top" title="%s"> %s</li>';
    
    $cacheDir = dirname(__DIR__)."/tmp/";
    
    foreach($list as $idx=>$file)
    {
        $entity = basename($file, '.php');
        $dir = basename(dirname($file));
        
        
        // begin check
        $ft = filemtime($file);
        $filetime = date('Y-m-d H:i:s', $ft);
        $cachePath = $cacheDir.preg_replace("/[^a-zA-Z0-9]/", "", $file);
        $return_var = 1;
        if(file_exists($cachePath."-".$ft))
        {
            $err = ErrorCacheUtil::getCacheError($cachePath, $ft);
            if($err != 'true')
            {
                $return_var = 0;
            }
        }
        else
        {
            exec("php -l $file 2>&1", $output, $return_var);
            ErrorCacheUtil::saveCacheError($cachePath, $ft, $return_var === 0 ? 'false': 'true');
        }
        // end check
        
        if($return_var === 0)
        {
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
            if(!isset($li[$tableName]))
            {
                $li[$tableName]  = array();
            }
            $li[$tableName][] = sprintf($format1, $idx, $dir, $entity, $chk, $dir, $entity, $filetime, $entity);
        }
        else
        {
            if(!isset($li[$idx]))
            {
                $li[$idx]  = array();
            }
            $li[$idx][] = sprintf($format2, $idx, $dir, $entity, $filetime, $entity);
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
        
        // begin check
        $ft = filemtime($file);
        $filetime = date('Y-m-d H:i:s', $ft);
        $cachePath = $cacheDir.preg_replace("/[^a-zA-Z0-9]/", "", $file);
        $return_var = 1;
        if(file_exists($cachePath."-".$ft))
        {
            $err = ErrorCacheUtil::getCacheError($cachePath, $ft);
            if($err != 'true')
            {
                $return_var = 0;
            }
        }
        else
        {
            exec("php -l $file 2>&1", $output, $return_var);
            ErrorCacheUtil::saveCacheError($cachePath, $ft, $return_var === 0 ? 'false': 'true');
        }
        // end check
        
        if($return_var === 0)
        {
            $tableInfo = EntityUtil::getTableName($file);
            $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
            if(!isset($li[$tableName]))
            {
                $li[$tableName]  = array();
            }
            $li[$tableName][] = sprintf($format1, $idx, $dir, $entity, $chk, $dir, $entity, $filetime, $entity);
        }
        else
        {
            if(!isset($li[$idx]))
            {
                $li[$idx]  = array();
            }
            $li[$idx][] = sprintf($format2, $idx, $dir, $entity, $filetime, $entity);
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
