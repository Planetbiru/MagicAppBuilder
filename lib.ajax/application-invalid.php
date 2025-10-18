<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

if(!isset($application) || $application instanceof EntityApplication == false || !$application->isApplicationValid())
{
    $inputGet = new InputGet();
    $applicationId = $inputGet->getApplicationId();
    $application = new EntityApplication(null, $databaseBuilder);
    $application->find($applicationId);
}


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