<?php

use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$directory = FileDirUtil::normalizePath($inputPost->getDirectory());
if($inputPost->getIsFile() == 'true')
{
    $directory = dirname($directory);
}

function hasSubdirectory($directory)
{
    $directory = str_replace("\\", "/", $directory);
    $arr = explode("/", $directory);
    return count($arr) > 2;
}
$writeable = false;
$permissions = 0;
if($directory == null || empty($directory))
{
    $writeable = false;
}
else
{
    if(file_exists($directory) && !is_dir($directory))
    {
        $writeable = false;
    }
    else
    {
        if(file_exists($directory))
        {
            $writeable = is_writable($directory);
        }
        else
        {
            $directoryToCheck = $directory;
            while(!file_exists($directoryToCheck) && hasSubdirectory($directoryToCheck))
            {
                $directoryToCheck = dirname($directoryToCheck);
            }
            $permissions = fileperms($directoryToCheck);
            $writeable = is_writable($directoryToCheck);
        }
    }
}
PicoResponse::sendJSON(['writeable'=>$writeable, 'permissions'=>$permissions]);
