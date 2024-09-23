<?php

use MagicObject\Request\InputGet;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId();

$curApp = $builderConfig->getCurrentApplication();
$appBaseConfigPath = $workspaceDirectory."/applications";
$appConfig = new SecretObject();
$appConfig->setDatabase(new SecretObject());
$appConfig->setSessions(new SecretObject());

if($applicationId != null)
{
    $appConfigPath = $workspaceDirectory."/applications/".$applicationId."/default.yml";
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}

$cfgDatabase = new SecretObject($appConfig->getDatabase());
$cfgSession = new SecretObject($appConfig->getSessions());
$app = new SecretObject($appConfig->getApplication());
?>
<form name="formdatabase" id="formdatabase" method="post" action="" class="config-table">
    <div class="collapsible-card">
        <div id="accordion" class="accordion">
        <div class="card">
            <div class="card-header" id="heading0">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse0" aria-expanded="true" aria-controls="collapse0">
                    Application
                    </button>
                </h5>
            </div>

            <div id="collapse0" class="collapse collapsed" aria-labelledby="heading0" data-parent="#accordion">
                <div class="card-body">
                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td>Application Name</td>
                                <td><input class="form-control" type="text" name="application_name" id="application_name" value="<?php echo $app->getName(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Application Type</td>
                                <td>
                                    <select class="form-control" name="application_type" id="application_type">
                                        <option value="fullstack"<?php echo $app->getType() == 'fullstack' ? ' selected' : ''; ?>>Fullstack Application</option>
                                        <option value="api"<?php echo $app->getType() == 'api' ? ' selected' : ''; ?>>Application Programming Interface (API)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td><input class="form-control" type="text" name="description" id="description" value="<?php echo $app->getDescription(); ?>"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="accordion" class="accordion">
        <div class="card">
            <div class="card-header" id="heading1">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    Database
                    </button>
                </h5>
            </div>

            <div id="collapse1" class="collapse collapsed" aria-labelledby="heading1" data-parent="#accordion">
                <div class="card-body">

                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                            <td>Driver</td>
                            <td>
                                <select class="form-control" name="database_driver" id="database_driver">
                                <option value="mysql" <?php echo $cfgDatabase->getDriver() == 'mysql' ? $constSelected : ''; ?>>MySQL</option>
                                <option value="mariadb" <?php echo $cfgDatabase->getDriver() == 'mariadb' ? $constSelected : ''; ?>>MariaDB</option>
                                <option value="postgresql" <?php echo $cfgDatabase->getDriver() == 'postgresql' ? $constSelected : ''; ?>>PostgreSQL</option>
                                </select>
                            </td>
                            </tr>
                            <tr>
                                <td>Host</td>
                                <td><input class="form-control" type="text" name="database_host" id="database_host" value="<?php echo $cfgDatabase->getHost(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Port</td>
                                <td><input class="form-control" type="text" name="database_port" id="database_port" value="<?php echo $cfgDatabase->getPort(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Username</td>
                                <td><input class="form-control" type="text" name="database_username" id="database_username" value="<?php echo $cfgDatabase->getUsername(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Password</td>
                                <td><input class="form-control" name="database_password" type="password" id="database_password" value=""></td>
                            </tr>
                            <tr>
                                <td>Name</td>
                                <td><input class="form-control" type="text" name="database_database_name" id="database_database_name" value="<?php echo $cfgDatabase->getDatabaseName(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Schema</td>
                                <td><input class="form-control" type="text" name="database_database_schema" id="database_database_schema" value="<?php echo $cfgDatabase->getDatabaseSchema(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Time Zone</td>
                                <td><input class="form-control" type="text" name="database_time_zone" id="database_time_zone" value="<?php echo $cfgDatabase->getTimeZone(); ?>"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading2">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                    Session
                    </button>
                </h5>
            </div>

            <div id="collapse2" class="collapse collapsed" aria-labelledby="heading2" data-parent="#accordion">
                <div class="card-body">

                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td>Session Name</td>
                                <td><input class="form-control" type="text" name="sessions_name" id="sessions_name" value="<?php echo $cfgSession->getName(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Session Life Time</td>
                                <td><input class="form-control" type="text" name="sessions_max_life_time" id="sessions_max_life_time" value="<?php echo $cfgSession->getMaxLifeTime(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Session Save Handler</td>
                                <td>
                                    <select class="form-control" name="sessions_save_handler" id="sessions_save_handler">
                                    <option value="files" <?php echo $cfgSession->getSaveHandler() == 'files' ? $constSelected : ''; ?>>files</option>
                                    <option value="redis" <?php echo $cfgSession->getSaveHandler() == 'redis' ? $constSelected : ''; ?>>redis</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Session Save Path</td>
                                <td><input class="form-control" type="text" name="sessions_save_path" id="sessions_save_path" value=""></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading3">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
                    Reserved Columns
                    </button>
                </h5>
            </div>

            <div id="collapse3" class="collapse collapsed" aria-labelledby="heading3" data-parent="#accordion">
                <div class="card-body">
                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <?php
                            $entityConstant = new SecretObject($appConfig->getEntityInfo());
                            if (empty($entityConstant->valueArray())) {
                            $entityConstant = new SecretObject($builderConfig->getEntityInfo());
                            }
                            $arr = $entityConstant->valueArray(true);

                            if (!empty($arr)) {
                            foreach ($arr as $key => $value) {
                            ?>
                                <tr>
                                <td><?php echo $key ?></td>
                                <td><input class="form-control" type="text" name="entity_info_<?php echo $key ?>" value="<?php echo $value; ?>"></td>
                                </tr>
                            <?php
                            }
                            }
                            ?>
                        </tbody>
                    </table>
                    <input type="hidden" name="application_id" value="<?php echo $applicationId;?>">
                </div>
            </div>
        </div>
    </div>
</form>