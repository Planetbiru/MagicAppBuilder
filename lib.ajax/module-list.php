<?php

use AppBuilder\Util\EntityUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$separatorNLT = "\r\n\t";

try
{
	$baseModuleDirectory = $appConfig->getApplication()->getBaseModuleDirectory();
    echo "<div>\r\n";

    foreach($baseModuleDirectory as $elem)
    {
        echo "<h4>".$elem->getName()."</h4>\r\n";
        $baseDirectory = $elem->getPath();        

        $len = strlen($appConfig->getApplication()->getBaseApplicationDirectory());
        $dir = substr($baseDirectory, $len);

        $list = glob($baseDirectory."/*.php");
        $li = array();

        foreach($list as $idx=>$file)
        {
            $module = basename($file, '.php');
            $path = str_replace("\\", "//", trim($dir.'/'.$module, "//"));
            $li[] = '<li class="file-li"><a href="#" data-file-name="'.$path.'">'.$module.'.php</a></li>';
        }
        echo '<ul class="module-ul">'.$separatorNLT.implode($separatorNLT, $li)."\r\n".'</ul>'."\r\n";
    }
    echo "</div>\r\n";
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
