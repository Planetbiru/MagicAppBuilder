<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$inputGet = new InputGet();
$inputPost = new InputPost();

if($inputPost->getAction() == 'save')
{
    $applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    try
    {
        $application = new EntityApplication(null, $databaseBuilder);
        $application->findOneWithPrimaryKeyValue($applicationId);
        
        // Save the application configuration on workspace
        $appConfig->setDevelopmentMode($inputPost->getDevelopmentMode() == 1);
        $appConfig->setBypassRole($inputPost->getBypassRole() == 1);
        $appConfig->setAccessLocalhostOnly($inputPost->getAccessLocalhostOnly() == 1);
        
        $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
        $yaml = $appConfig->dumpYaml(true, true, true);
        file_put_contents($appConfigPath, $yaml);
        
        // Save the application configuration on project
        $appConfigPath = $application->getBaseApplicationDirectory()."/inc.cfg/application.yml";
        $appConf = new SecretObject();
        $appConf->loadYamlFile($appConfigPath, false, true, true);
        $appConf->setDevelopmentMode($inputPost->getDevelopmentMode() == 1);
        $appConf->setBypassRole($inputPost->getBypassRole() == 1);
        $appConf->setAccessLocalhostOnly($inputPost->getAccessLocalhostOnly() == 1);
        $yaml = $appConf->dumpYaml(true, true, true);
        file_put_contents($appConfigPath, $yaml);
 
        
    }
    catch (Exception $e)
    {
        $application = null;
    }

    
    exit();
}

$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
try
{
    $application = new EntityApplication(null, $databaseBuilder);
    $application->findOneWithPrimaryKeyValue($applicationId);

    $appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";
    $appConfig = new SecretObject();

    if($applicationId != null)
    {
        $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
        if(file_exists($appConfigPath))
        {
            $appConfig->loadYamlFile($appConfigPath, false, true, true);
        }
    }

?><form name="formdatabase" id="formdatabase" method="post" action="" class="config-table">
    <div class="collapsible-card">
        <div id="accordion" class="accordion">
        <div class="card">
            <div class="card-header" id="heading0">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse0" aria-expanded="true" aria-controls="collapse0">
                    Application Menu
                    </button>
                </h5>
            </div>

            <div id="collapse0" class="collapse collapsed" aria-labelledby="heading0" data-parent="#accordion">
                <div class="card-body">
                    <?php
                        
                        $menuPath = $application->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
                        $path = basename($application->getBaseApplicationDirectory());
                        if(file_exists($menuPath))
                        {
                            $menu = new SecretObject();
                            $menu->loadYamlFile($menuPath, false, true, true);
                            
                            // Render the menu
                            $ul = '<ul class="nav flex-column">';
                            foreach ($menu->getMenu() as $item) {
                                $li = '<li class="nav-item">';
                                $li .= '<a class="nav-link" href="#"><i class="'.$item->getIcon().'"></i> ' . htmlspecialchars($item->getTitle()) . '</a>';
                                if ($item->issetSubmenu() && is_array($item->getSubmenu())) {
                                    $li .= '<ul class="nav flex-column">';
                                    foreach ($item->getSubmenu() as $subItem) {
                                        $li .= '<li class="nav-item">';
                                        $li .= '<a class="nav-link" href="../'. $path . '/' . htmlspecialchars($subItem->getHref()) . '" target="_blank"><i class="' . htmlspecialchars($subItem->getIcon()) . '"></i> ' . htmlspecialchars($subItem->getTitle()) . '</a>';
                                        $li .= '</li>';
                                    }
                                    $li .= '</ul>';
                                }
                                $li .= '</li>';
                                $ul .= $li;
                            }
                            $ul .= '</ul>';
                            echo $ul;
                        }
                        else
                        {
                            echo "<div class='alert alert-danger'>Menu file not found</div>";
                        }
                    ?>
                </div>
            </div>
        </div>

        <div id="accordion" class="accordion">
        <div class="card">
            <div class="card-header" id="heading1">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    Application User
                    </button>
                </h5>
            </div>

            <div id="collapse1" class="collapse collapsed" aria-labelledby="heading1" data-parent="#accordion">
                <div class="card-body">

                    
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading2">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                    Application Mode
                    </button>
                </h5>
            </div>

            <div id="collapse2" class="collapse collapsed" aria-labelledby="heading2" data-parent="#accordion">
                <div class="card-body">

                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td>Development Mode</td>
                                <td><label for="development_mode"><input type="checkbox" name="development_mode" id="development_mode" value="1" <?php echo $appConfig->getDevelopmentMode() ? ' checked' : '';?>> Yes</label></td>
                            </tr>
                            <tr>
                                <td>Bypass Role</td>
                                <td><label for="bypass_role"><input type="checkbox" name="bypass_role" id="bypass_role" value="1" <?php echo $appConfig->getBypassRole() ? ' checked' : '';?>> Yes</label></td>
                            </tr>
                            <tr>
                                <td>Access Localhost Only</td>
                                <td><label for="access_localhost_only"><input type="checkbox" name="access_localhost_only" id="access_localhost_only" value="1" <?php echo $appConfig->getAccessLocalhostOnly() ? ' checked' : '';?>> Yes</label></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
}
catch (Exception $e)
{
    $application = null;
}