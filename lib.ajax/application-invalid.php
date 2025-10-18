<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputGet;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$systemModulePath = "/";

if(!isset($application) || $application instanceof EntityApplication == false || !$application->isApplicationValid())
{
    $inputGet = new InputGet();
    $applicationId = $inputGet->getApplicationId();
    $application = new EntityApplication(null, $databaseBuilder);
    $application->find($applicationId);
    
    $dir = $application->getProjectDirectory();
    $yml = FileDirUtil::normalizePath($dir."/default.yml");
    
    if(file_exists($yml))
    {
        $appConfig = new SecretObject();
        $appConfig->loadYamlFile($yml, false, true, true);
        if($appConfig->issetApplication() && $appConfig->getApplication()->issetBaseModuleDirectory())
        {
            if($appConfig->getApplication()->getBaseModuleDirectory())
            {
                $baseModuleDirs = $appConfig->getApplication()->getBaseModuleDirectory();
                foreach($baseModuleDirs as $dir)
                {
                    if($dir->isActive() && $dir->issetPath())
                    {
                        // get the first active module path
                        $systemModulePath = $dir->getPath();
                        break;
                    }
                }
            }
        }
    }
    
}

if($application->getApplicationId() != null)
{
    ?>
    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                Application Directory
            </td>
            <td>
                <?php
                echo $application->getBaseApplicationDirectory();
                ?>
            </td>
        </tr>
        <tr>
            <td>
                Directory Exists
            </td>
            <td>
                <?php
                echo $application->isDirectoryExists() ? "Yes" : "No";
                ?>
            </td>
        </tr>
        <tr>
            <td>
                Application Valid
            </td>
            <td>
                <?php
                echo $application->isApplicationValid() ? "Yes" : "No";
                ?>
            </td>
        </tr>
        <tr>
            <td>
                System Module Path
            </td>
            <td>
                <?php
                echo $systemModulePath;
                ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" class="btn btn-primary button-check-application-valid" data-application-id="<?php echo $application->getApplicationId(); ?>"><i class="fa-regular fa-circle-check"></i> Validate</button>
                <button type="button" class="btn btn-primary button-rebuild-application" data-application-id="<?php echo $application->getApplicationId(); ?>"><i class="fas fa-sync"></i> Rebuild</button>
            </td>
        </tr>
    </table>
    <div class="application-valid" data-application-valid="false" style="display: none;"></div>
    <?php
}
else
{
    ?>
    <div class="application-valid" data-application-valid="false" style="display: none;"></div>
    <div class="alert alert-warning" role="alert">
        This application is not valid.
    </div>
    <?php
    require_once __DIR__ . "/application-invalid.php";
    exit();
}