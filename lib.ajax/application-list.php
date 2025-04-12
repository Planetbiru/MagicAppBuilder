<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicApp\Field;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$applicationFinder = new EntityApplication(null, $databaseBuilder);

try
{
    $specs = PicoSpecification::getInstance()
        ->addAnd([Field::of()->workspaceId, $activeWorkspace->getWorkspaceId()])
        ->addAnd([Field::of()->active, true])
        ;
    $sorts = new PicoSortable(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC, Field::of()->timeCreate, PicoSort::ORDER_TYPE_DESC);
    $pageData = $applicationFinder->findAll($specs, null, $sorts);
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
        data-application-id="<?php echo $app->getId();?>" 
        data-application-name="<?php echo htmlspecialchars($app->getName());?>"
        data-path="<?php echo str_replace("\\", "/", $application->getBaseApplicationDirectory());?>"
    >
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($app->getName()); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $app->getId(); ?></h6>
            <p class="card-text"><?php echo $app->getDescription(); ?></p>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-setting">
                <i class="fas fa-cog"></i> Setting
            </a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-menu">
                <i class="fas fa-bars"></i> Menu
            </a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-database">
                <i class="fas fa-database"></i> Database
            </a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-option">
                <i class="fas fa-ellipsis-v"></i> Option
            </a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-open">
                <i class="fas fa-code"></i> Code
            </a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-icons">
                <i class="fas fa-icons"></i> Icon
            </a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-application-default">
                <i class="fas fa-check"></i> Active
            </a>
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
