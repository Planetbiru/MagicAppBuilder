<?php

use MagicAdmin\Entity\Data\ActiveApplicationHistory;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Record the start time
$startTime = microtime(true);

try
{
    $inputPost = new InputPost();
    if(isset($entityAdmin) && $entityAdmin->issetAdminId())
    {
        $appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
        $entityAdmin->setApplicationId($appId)->update();
        $activeApplicationHistory = new ActiveApplicationHistory(null, $databaseBuilder);
        try
        {
            $activeApplicationHistory->findOneByAdminIdAndWorkspaceId($entityAdmin->getAdminId(), $entityAdmin->getWorkspaceId());
            // Update
            $activeApplicationHistory
                ->setApplicationId($entityAdmin->getApplicationId())
                ->update()
                ;
        }
        catch(Exception $e)
        {
            // Insert
            $activeApplicationHistory
                ->setAdminId($entityAdmin->getAdminId())
                ->setWorkspaceId($entityAdmin->getWorkspaceId())
                ->setApplicationId($entityAdmin->getApplicationId())
                ->insert()
                ;
        }
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}

// Record the end time
$endTime = microtime(true);

// Calculate the duration in seconds
$executionTime = $endTime - $startTime;

// Log the execution time
error_log("Execution Time: " . number_format($executionTime, 4) . " seconds");
