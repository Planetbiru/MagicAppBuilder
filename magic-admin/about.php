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

if(!$userPermission->allowedAccess($inputGet, $inputPost)) {
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
    "list-releases.php",
    "update-database.php"
];

$sourceDir = __DIR__ . "/__update/";
$targetDir = __DIR__ . "/update/";

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

foreach ($files as $file) {
    $src = $sourceDir . $file;
    $dst = $targetDir . $file;

    if (
        !file_exists($dst) || 
        (filemtime($src) > filemtime($dst) && (time() - filemtime($src)) > 3600)
    ) {
        copy($src, $dst);
    }
}

require_once $appInclude->mainAppHeader(__DIR__);

// Localized strings for JS
$jsLang = [
    'loading' => $appLanguage->getLoadingReleases(),
    'loadSuccess' => $appLanguage->getReleaseListLoaded(),
    'loadFailed' => $appLanguage->getErrorLoadingReleases(),
    'downloading' => $appLanguage->getDownloadingRelease(),
    'extracting' => $appLanguage->getExtracting(),
    'downloadFailed' => $appLanguage->getDownloadFailed(),
    'extractionFailed' => $appLanguage->getExtractionFailed(),
    'updatingDatabase' => $appLanguage->getUpdatingDatabase(),
    'updateDatabaseFailed' => $appLanguage->getUpdateDatabaseFailed(),
    'updateDatabaseSuccessfully' => $appLanguage->getUpdateDatabaseSuccessfully()
];
?>
<div class="page page-jambi">
	<table class="responsive responsive-two-cols">
        <tbody>
            <tr>
                <td><?php echo $appLanguage->getApplicationVersion();?></td>
                <td><span id="application-version"><?php echo $ini->getApplicationVersion();?></span></td>
            </tr>
            <tr>
                <td><?php echo $appLanguage->getLastUpdate();?></td>
                <td><span id="last-update"><?php echo $ini->getLastUpdate();?></span></td>
            </tr>
            <tr>
                <td><?php echo $appLanguage->getUpdateToVersion();?></td>
                <td><select id="release-select" class="form-control" style="display: inline-block;width: auto;padding-right: 32px;max-width: 100%;" disabled>
                    <option><?php echo $appLanguage->getPleaseLoadReleasesFirst();?></option>
                </select> <button type="button" class="btn btn-primary" onclick="loadReleases()"><i class="fa fa-refresh"></i></button></td>
            </tr>
            <tr>
                <td></td>
                <td>           
                    <button type="button" class="btn btn-success" onclick="startUpdate()" id="update-btn" disabled><?php echo $appLanguage->getUpdateNow();?></button>
                    <button type="button" class="btn btn-success" onclick="updateDatabase()"><?php echo $appLanguage->getUpdateDatabase();?></button>
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
  const lang = <?php echo json_encode($jsLang, JSON_UNESCAPED_UNICODE); ?>;

  function loadReleases() {
    const statusEl = document.getElementById('status');
    const select = document.getElementById('release-select');
    const updateBtn = document.getElementById('update-btn');

    statusEl.textContent = lang.loading;
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
          opt.textContent = (release.name || release.tag_name) + ' ('+release.published_at+')';
          select.appendChild(opt);
        });
        select.disabled = false;
        updateBtn.disabled = false;
        statusEl.textContent = '✅ ' + lang.loadSuccess;
      })
      .catch(err => {
        statusEl.textContent = '❌ ' + lang.loadFailed;
        statusEl.classList.add('error');
        select.innerHTML = '<option>-- ' + lang.loadFailed + ' --</option>';
        select.disabled = true;
      });
  }

  function startUpdate() {
    const tag = document.getElementById('release-select').value;
    const statusEl = document.getElementById('status');
    statusEl.classList.remove('error', 'success');
    statusEl.textContent = '⏬ ' + lang.downloading.replace('%s', tag);
    document.querySelector('#update-btn').disabled = true;

    fetch('update/download-release.php?tag=' + encodeURIComponent(tag))
      .then(response => response.json())
      .then(json => {
        if (!json.success) throw new Error(json.message || lang.downloadFailed);
        statusEl.textContent = '⏳ ' + lang.extracting;
        return fetch('update/extract-release.php');
      })
      .then(response => response.json())
      .then(json => {
        if (!json.success) throw new Error(json.message || lang.extractionFailed);
        statusEl.textContent = '✅ ' + json.message;
        statusEl.classList.add('success');
        document.querySelector('#application-version').textContent = json.new_version;
        document.querySelector('#last-update').textContent = json.last_update;
      })
      .catch(err => {
        statusEl.textContent = '❌ ' + err.message;
        statusEl.classList.add('error');
      });
  }
  function updateDatabase() {
    const statusEl = document.getElementById('status');
    statusEl.classList.remove('error', 'success');
    statusEl.textContent = '🛠️ ' + lang.updatingDatabase;

    fetch('update/update-database.php?response=true')
      .then(response => response.json())
      .then(json => {
        if (!json.success) throw new Error(lang.updateDatabaseFailed);
        statusEl.textContent = '✅ ' + lang.updateDatabaseSuccessfully;
        statusEl.classList.add('success');
      })
      .catch(err => {
        statusEl.textContent = lang.updateDatabaseFailed;
        statusEl.classList.add('error');
      });
  }

</script>

<?php 
require_once $appInclude->mainAppFooter(__DIR__);
