<?php

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$separatorNLT = "\r\n\t";

try
{
	$baseModuleDirectory = $appConfig->getApplication()->getBaseModuleDirectory();
    echo "<div>\r\n";
    foreach($baseModuleDirectory as $elem)
    {
        echo "<div class=\"module-group\">\r\n";
        echo "<h4><label><input type=\"checkbox\" class=\"select-module\"> ".$elem->getName()."</label></h4>\r\n";
        $target = trim($elem->getPath(), "/\\");
        if(!empty($target))
        {
            $target = "/".$target;
        }
        $baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
        $dir =  $baseDirectory."$target";
        $pattern = $baseDirectory."$target/*.php";
        $list = glob($pattern);
        $li = [];
        foreach($list as $idx=>$file)
        {
            $module = basename($file, '.php');
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            $path = str_replace("\\", "//", trim($target.'/'.$module, "//")).".php";
            $li[] = '<li class="file-li"><label><input class="module-for-translate" type="checkbox" value="'.$path.'"> '.$module.'.php</label></li>';
        }
        echo '<ul class="module-ul">'.$separatorNLT.implode($separatorNLT, $li)."\r\n".'</ul>'."\r\n";
        echo "</div>\r\n";
    }
    echo "</div>\r\n";
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}