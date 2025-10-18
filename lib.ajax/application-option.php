<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$inputGet = new InputGet();
$inputPost = new InputPost();

$attributeChecked = ' checked="checked"';

if($inputPost->getUserAction() == 'save')
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
   $application->find($applicationId);
   if($inputGet->getValidate() == 'true')
   {
      // Validate application
      // Check if application is valid
      $rootDir = FileDirUtil::normalizePath($application->getBaseApplicationDirectory());
      if(file_exists($rootDir))
      {
         $appDir = FileDirUtil::normalizePath($application->getBaseApplicationDirectory()."/inc.app");
         $classesDir = FileDirUtil::normalizePath($application->getBaseApplicationDirectory()."/inc.lib/classes");
         $vendorDir = FileDirUtil::normalizePath($application->getBaseApplicationDirectory()."/inc.lib/vendor");
         $yml = FileDirUtil::normalizePath($application->getBaseApplicationDirectory()."/inc.cfg/application.yml");
         $rootDirExists = true;
         $appDirExists = file_exists($appDir);
         $classesDirExists = file_exists($classesDir);
         $vendorDirExists = file_exists($vendorDir);
         $ymlExists = file_exists($yml);
      }
      else
      {
         $rootDirExists = false;
         $appDirExists = false;
         $classesDirExists = false;
         $vendorDirExists = false;
         $ymlExists = false;
      }
      if(!$rootDirExists || !$appDirExists || !$classesDirExists || !$vendorDirExists || !$ymlExists)
      {
         $applicationValid = false;
      }
      else
      {
         $applicationValid = true;
      }

      $application
         ->setApplicationValid($applicationValid)
         ->setDirectoryExists($rootDirExists)
         ->update();
   }

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

    if($application->isApplicationValid())
    {
?>
<div class="application-valid" data-application-valid="true" style="display: none;"></div>

<form name="formdatabase" id="formdatabase" method="post" action="" class="">
   <!-- BEGIN Accordion Wrapper -->
   <div id="accordion-option" class="accordion">

      <!-- BEGIN Application Menu -->
      <div class="card">
         <div class="card-header" id="heading0">
            <h5 class="mb-0">
               <button type="button" class="btn" data-toggle="collapse" data-target="#collapse0" aria-expanded="true" aria-controls="collapse0">
                  Application Menu
               </button>
            </h5>
         </div>
         <div id="collapse0" class="collapse collapsed" aria-labelledby="heading0" data-parent="#accordion-option">
            <div class="card-body">
               <div class="menu-container">
                  <?php include_once __DIR__ . "/application-menu-import.php"; ?>
               </div>
               <button type="button" class="btn btn-primary" id="import-menu">Import Menu</button>
            </div>
         </div>
      </div>
      <!-- END Application Menu -->

      <!-- BEGIN Application User -->
      <div class="card">
         <div class="card-header" id="heading1">
            <h5 class="mb-0">
               <button type="button" class="btn" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                  Application User
               </button>
            </h5>
         </div>
         <div id="collapse1" class="collapse collapsed" aria-labelledby="heading1" data-parent="#accordion-option">
            <div class="card-body">
               <div class="user-container">
                  <?php include_once __DIR__ . "/application-user.php"; ?>
               </div>
               <button type="button" class="btn btn-primary" id="create-user">Create</button>
               <button type="button" class="btn btn-primary" id="reset-user-password">Reset Password</button>
               <button type="button" class="btn btn-primary" id="set-user-role">Set Superuser Role</button>
               <button type="button" class="btn btn-danger" id="delete-user">Delete</button>
            </div>
         </div>
      </div>
      <!-- END Application User -->

      <!-- BEGIN Application Mode -->
      <div class="card">
         <div class="card-header" id="heading2">
            <h5 class="mb-0">
               <button type="button" class="btn" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                  Application Mode
               </button>
            </h5>
         </div>
         <div id="collapse2" class="collapse collapsed" aria-labelledby="heading2" data-parent="#accordion-option">
            <div class="card-body">
               <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tbody>
                     <tr>
                        <td>Development Mode</td>
                        <td><label for="development_mode"><input type="checkbox" name="development_mode" id="development_mode" value="1" <?php echo $appConfig->getDevelopmentMode() ? $attributeChecked : '';?>> Yes</label></td>
                     </tr>
                     <tr>
                        <td>Bypass Role</td>
                        <td><label for="bypass_role"><input type="checkbox" name="bypass_role" id="bypass_role" value="1" <?php echo $appConfig->getBypassRole() ? $attributeChecked : '';?>> Yes</label></td>
                     </tr>
                     <tr>
                        <td>Access Localhost Only</td>
                        <td><label for="access_localhost_only"><input type="checkbox" name="access_localhost_only" id="access_localhost_only" value="1" <?php echo $appConfig->getAccessLocalhostOnly() ? $attributeChecked : '';?>> Yes</label></td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <!-- END Application Mode -->

      <!-- BEGIN Application Icon -->
      <div class="card">
         <div class="card-header" id="heading3">
            <h5 class="mb-0">
               <button type="button" class="btn" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
                  Application Icon
               </button>
            </h5>
         </div>
         <div id="collapse3" class="collapse collapsed" aria-labelledby="heading3" data-parent="#accordion-option">
            <div class="card-body">
               <div class="icon-container">
                  <?php include_once __DIR__ . "/application-icon-list.php"; ?>
               </div>
               <button type="button" class="btn btn-primary upload-application-icons">Upload Icon</button>
            </div>
         </div>
      </div>
      <!-- END Application Icon -->
      
      <!-- BEGIN Application Dependency Update -->
      <div class="card">
         <div class="card-header" id="heading4">
            <h5 class="mb-0">
               <button type="button" class="btn" data-toggle="collapse" data-target="#collapse4" aria-expanded="true" aria-controls="collapse4">
                  Dependency Update
               </button>
            </h5>
         </div>
         <div id="collapse4" class="collapse collapsed" aria-labelledby="heading4" data-parent="#accordion-option">
            <div class="card-body">
               <button type="button" class="btn btn-primary update-magic-object">Update MagicObject</button>
               <button type="button" class="btn btn-primary update-magic-app">Update MagicApp</button>
               <button type="button" class="btn btn-primary update-classes">Update Classes</button>
            </div>
         </div>
      </div>
      <!-- END Application Dependency -->

      <!-- BEGIN Trash -->
      <div class="card">
         <div class="card-header" id="heading5">
            <h5 class="mb-0">
               <button type="button" class="btn" data-toggle="collapse" data-target="#collapse5" aria-expanded="true" aria-controls="collapse5">
                  Data Restoration
               </button>
            </h5>
         </div>
         <div id="collapse5" class="collapse collapsed" aria-labelledby="heading5" data-parent="#accordion-option">
            <div class="card-body">
               <div class="data-container">
                  <form method="post" action="">
                     <div class="table-row-container entity-trash-list-container">
                        <?php
                        require_once __DIR__ ."/entity-trash-list.php";
                        ?>
                     </div>
                     <div class="button-area">
                        <button type="button" class="btn btn-success update-trash-entity" value="update-trash-entity">Update</button>
                        <button type="button" class="btn btn-danger delete-trash-entity" value="delete-trash-entity">Delete</button>
                     </div>
                  </form>
                  <?php
                  ?>
               </div>
            </div>
         </div>
      </div>
      <!-- END Trash -->

   </div>
   <!-- END Accordion Wrapper -->
    
   <input type="hidden" name="application_id" value="<?php echo $applicationId;?>">
</form>

<?php
    }
    else
    {
      require_once __DIR__ . "/application-invalid.php";
      exit();
    }
}
catch (Exception $e)
{
    $application = null;
}