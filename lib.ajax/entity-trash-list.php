<?php

use AppBuilder\AppDatabase;
use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if(empty($applicationId))
{
    $applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
}

$application = new EntityApplication(null, $databaseBuilder);
try
{
    $application->findOneByApplicationId($applicationId);
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    $menuAppConfig = new SecretObject();
    if(file_exists($appConfigPath))
    {
        $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
    }
    
    // Database connection for the application
    $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
    try
    {
                        $database->connect();
                        $databaseType = $database->getDatabaseType();
                        $schemaName = $databaseConfig->getDatabaseSchema();
                        $databaseName = $databaseConfig->getDatabaseName();
                        $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
                        $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
                        $baseEntity = str_replace("\\\\", "\\", $baseEntity);
                        $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/")).DIRECTORY_SEPARATOR."Trash";
                        $validTrashTables = AppDatabase::getValidTashTable($appConfig, $databaseConfig, $database, $databaseName, $schemaName);
                        ?>
                        <table class="table table-striped table-bordered">
                           <thead>
                              <tr>
                                 <th><input type="checkbox" id="select-all-trash-tables" onchange="this.closest('table').querySelectorAll('.select-trash-table').forEach(cb => cb.checked = this.checked)" style="margin: 0;" /></th>
                                 <th>No</th>
                                 <th>Primary Table</th>
                                 <th>Trash Table</th>
                                 <th>Primary Entity</th>
                                 <th>Trash Entity</th>
                              </tr>
                           </thead>
                           <tbody>
                           <?php
                           $i = 0;
                           foreach($validTrashTables as $primaryTableName=>$trashTableName)
                           {
                              $i++;
                              $primaryEntity = PicoStringUtil::upperCamelize($primaryTableName);
                              $trashEntity = PicoStringUtil::upperCamelize($trashTableName);
                              if(!file_exists($baseDir.DIRECTORY_SEPARATOR.$primaryEntity.".php"))
                              {
                                 $primaryEntity = "";
                              }
                              if(!file_exists($baseDir.DIRECTORY_SEPARATOR.$trashEntity.".php"))
                              {
                                 $trashEntity = "";
                              }

                              ?>
                              <tr>
                                 <td><input type="checkbox" name="table[]" class="select-trash-table" value="<?php echo "$primaryTableName|$trashTableName";?>" data-primary-table="<?php echo $primaryTableName;?>" data-trash-table="<?php echo $trashTableName;?>" style="margin: 0;" /></td>
                                 <td align="right"><?php echo $i;?></td>
                                 <td><?php echo $primaryTableName;?></td>
                                 <td><?php echo $trashTableName;?></td>
                                 <td><?php echo $primaryEntity;?></td>
                                 <td><?php echo $trashEntity;?></td>
                              </tr>
                              <?php
                           }
                           ?>
                           </tbody>
                        </table>
                        <input type="hidden" name="application_id" value="<?php echo $applicationId;?>">
                        <?php
    }
    catch(Exception $e)
    {
        // Do nothing
    }
}
catch(Exception $e)
{
    // Do nothing
}