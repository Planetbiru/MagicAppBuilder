<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\PicoModule;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicObject\MagicObject;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "about", $appLanguage->getAboutMagicAppBuilder());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

$ini = new MagicObject(null, $databaseBuilder);
$ini->loadIniFile(dirname(__DIR__)."/app.ini");
$files = [
    "AppUpdater.php",
    "extract-release.php",
    "download-release.php",
    "list-releases.php"
];

$sourceDir = __DIR__ . "/__update/";
$targetDir = __DIR__ . "/update/";

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$oneDayInSeconds = 86400;

foreach ($files as $file) {
    $src = $sourceDir . $file;
    $dst = $targetDir . $file;

    if (
        !file_exists($dst) || 
        (filemtime($src) > filemtime($dst) && (time() - filemtime($src)) > $oneDayInSeconds)
    ) {
        copy($src, $dst);
    }
}

require_once $appInclude->mainAppHeader(__DIR__);
?>
<div class="page page-jambi">
	<table class="responsive responsive-two-cols">
        <tbody>
            <tr>
                <td><?php echo $appLanguage->getApplicationVersion();?></td>
                <td><?php echo $ini->getApplicationVersion();?></td>
            </tr>
            <tr>
                <td><?php echo $appLanguage->getLastUpdate();?></td>
                <td><?php echo $ini->getLastUpdate();?></td>
            </tr>
            <tr>
                <td><?php echo $appLanguage->getUpdateToVersion();?></td>
                <td><select id="release-select" class="form-control" disabled>
                    <option><?php echo $appLanguage->getPleaseLoadReleasesFirst();?></option>
                </select></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="button" class="btn btn-primary" onclick="loadReleases()"><?php echo $appLanguage->getLoadReleases();?></button>
                    <button type="button" class="btn btn-success" onclick="startUpdate()" id="update-btn" disabled><?php echo $appLanguage->getUpdateNow();?></button>
                </td>
            </tr>
            <tr>
                <td></td>
                <td><div class="status" id="status"></div></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function loadReleases() {
      const statusEl = document.getElementById('status');
      const select = document.getElementById('release-select');
      const updateBtn = document.getElementById('update-btn');

      statusEl.textContent = 'Loading releases...';
      select.disabled = true;
      updateBtn.disabled = true;

      fetch('update/list-releases.php')
        .then(response => response.json())
        .then(data => {
          if (!Array.isArray(data)) {
            throw new Error('Invalid response');
          }
          select.innerHTML = '';
          data.forEach(release => {
            const opt = document.createElement('option');
            opt.value = release.tag_name;
            opt.textContent = release.name || release.tag_name;
            select.appendChild(opt);
          });
          select.disabled = false;
          updateBtn.disabled = false;
          statusEl.textContent = '✅ Release list loaded.';
        })
        .catch(err => {
          statusEl.textContent = '❌ Error loading releases.';
          statusEl.classList.add('error');
          select.innerHTML = '<option>-- Failed to load --</option>';
          select.disabled = true;
        });
    }

    function startUpdate() {
      const tag = document.getElementById('release-select').value;
      const statusEl = document.getElementById('status');
      statusEl.classList.remove('error', 'success');
      statusEl.textContent = `⏬ Downloading release "${tag}"...`;
      document.querySelector('#update-btn').disabled = true;

      fetch('update/download-release.php?tag=' + encodeURIComponent(tag))
        .then(response => response.json())
        .then(json => {
          if (!json.success) throw new Error(json.message || 'Download failed');
          statusEl.textContent = '⏳ Extracting...';
          return fetch('update/extract-release.php');
        })
        .then(response => response.json())
        .then(json => {
          if (!json.success) throw new Error(json.message || 'Extraction failed');
          statusEl.textContent = '✅ ' + json.message;
          statusEl.classList.add('success');
        })
        .catch(err => {
          statusEl.textContent = '❌ ' + err.message;
          statusEl.classList.add('error');
        });
    }
  </script>

<?php 
require_once $appInclude->mainAppFooter(__DIR__);
