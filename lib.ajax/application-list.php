<?php

use AppBuilder\Entity\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$applicationFinder = new EntityApplication(null, $databaseBuilder);

try
{
    $pageData = $applicationFinder->findByWorkspaceId($activeWorkspace->getWorkspaceId());
    foreach ($pageData->getResult() as $application) {

        $currentApplicationId = isset($entityAdmin) && $entityAdmin->issetApplicationId() ? $entityAdmin->getApplicationId() : null;

        if ($application->getApplicationId() == $currentApplicationId) {
            $selected = 'true';
        } else {
            $selected = '';
        }

        $yml = FileDirUtil::normalizePath($application->getProjectDirectory()."/default.yml");
        
        if(file_exists($yml))
        {
            $config = new SecretObject(null);
            $config->loadYamlFile($yml, false, true, true);
            $app = $config->getApplication();
        }
        if(!isset($app))
        {
            $app = new SecretObject();
        }
?>
<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
    <div 
    class="card application-item" 
    data-selected="<?php echo $selected;?>"
    data-application-id="<?php echo $app->getId(); ?>" 
    data-application-name="<?php echo htmlspecialchars($app->getName()); ?>"
    data-path="<?php echo str_replace("\\", "/", $app->getDocumentRoot()); ?>"
    >
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($app->getName()); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $app->getId(); ?></h6>
            <p class="card-text"><?php echo $app->getDescription(); ?></p>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-setting">Setting</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-menu">Menu</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-database">Database</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-open">VS Code</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-default">Default</a>
        </div>
    </div>
</div>
<?php
    }
}
catch(Exception $e)
{
    // do nothing
}
