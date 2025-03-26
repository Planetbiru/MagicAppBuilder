<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\PicoModule;
use MagicApp\AppUserPermission;
use MagicAdmin\AppIncludeImpl;
use MagicObject\MagicObject;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "tools", $appLanguage->getTools());
$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

$composerJson = dirname(__DIR__) . "/inc.lib/composer.json";
$composerLock = dirname(__DIR__) . "/inc.lib/composer.lock";
$lastUpdate = date("Y-m-d H:i:s", filemtime($composerLock));
$composerObject = (new MagicObject())->loadJsonFile($composerJson, false, true, true);
$bundeledMagicAppVersion = $composerObject->getRequire()->get("planetbiru/magic-app");
$bundeledMagicAppVersion = str_replace("^", "", $bundeledMagicAppVersion);

require_once $appInclude->mainAppHeader(__DIR__);
?>
<?php echo $appLanguage->getUpdateMagicAppVersion(); ?>
<div class="page page-jambi">
	<table class="responsive responsive-two-cols">
        <tbody>
            <tr>
                <td><?php echo $appLanguage->getCurrentMagicAppVersion();?></td>
                <td><?php echo $bundeledMagicAppVersion;?></td>
            </tr>
            <tr>
                <td><?php echo $appLanguage->getLastUpdate();?></td>
                <td><?php echo $lastUpdate;?></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="button" class="btn btn-success update-composer"><?php echo $appLanguage->getUpdateNow();?></button></td>
            </tr>
        </tbody>
    </table>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const updateButton = document.querySelector(".update-composer");

    if (updateButton) {
        updateButton.addEventListener("click", function () {
            if (!confirm("Are you sure you want to update Composer?")) {
                return;
            }

            updateButton.disabled = true;
            updateButton.textContent = "Updating...";

            fetch("update.php", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(() => {
                alert("An error occurred while updating.");
            })
            .finally(() => {
                updateButton.disabled = false;
                updateButton.textContent = "Update Now";
            });
        });
    }
});

</script>
<?php 
require_once $appInclude->mainAppFooter(__DIR__);
