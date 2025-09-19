<?php

use MagicAdmin\AppIncludeImpl;
use MagicApp\PicoModule;
use MagicAppTemplate\AppUserPermissionImpl;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "database-migration", $appLanguage->getDatabaseMigration());
$userPermission = new AppUserPermissionImpl($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

require_once $appInclude->mainAppHeader(__DIR__);
?>
    <link rel="stylesheet" href="css/database-migration.min.css">
    <script src="js/js-yaml.min.js"></script>
    <script src="js/database-migration.min.js"></script>
    <div class="app dbm-container container-fluid">
        <div class="row h-100">
            <!-- Sidebar Config -->
            <aside class="dbm-sidebar mt-3 col-md-4 col-lg-3">

                <div class="card mb-3 p-3" id="configForm">
                    <h6 class="h6"><?php echo $appLanguage->getDatabaseTarget();?></h6>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getDriver();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.driver" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getHost();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.host" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getPort();?></label>
                            <input type="number" class="form-control" data-path="databaseTarget.port" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getUsername();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.username" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getPassword();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.password" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getDatabaseFilePath();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.databaseFilePath" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getDatabaseName();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.databaseName" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getSchema();?></label>
                            <input type="text" class="form-control" data-path="databaseTarget.databseSchema" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo $appLanguage->getTimeZone();?></label>
                        <input type="text" class="form-control" data-path="databaseTarget.timeZone" />
                    </div>

                    <h6 class="h6"><?php echo $appLanguage->getDatabaseSource();?></h6>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getDriver();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.driver" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getHost();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.host" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getPort();?></label>
                            <input type="number" class="form-control" data-path="databaseSource.port" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getUsername();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.username" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getPassword();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.password" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getDatabaseFilePath();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.databaseFilePath" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getDatabaseName();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.databaseName" />
                        </div>
                        <div class="form-group col">
                            <label><?php echo $appLanguage->getSchema();?></label>
                            <input type="text" class="form-control" data-path="databaseSource.databseSchema" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo $appLanguage->getTimeZone();?></label>
                        <input type="text" class="form-control" data-path="databaseSource.timeZone" />
                    </div>

                    <div class="form-group">
                        <label><?php echo $appLanguage->getMaximumRecord();?></label>
                        <input type="number" class="form-control" data-path="maximumRecord" value="100" />
                    </div>

                    <div class="toolbar d-flex flex-md-column flex-wrap">
                        <button class="btn btn-primary generate mb-2" id="btnAutogenerate">
                            <?php echo $appLanguage->getAutogenerate();?>
                        </button>
                        <button class="btn btn-success ok mb-2" id="btnAddTable">
                            <?php echo $appLanguage->getAddTable();?>
                        </button>
                        <button class="btn btn-outline-danger ghost" id="btnClearAll">
                            <?php echo $appLanguage->getClear();?>
                        </button>
                    </div>

                </div>

                <div class="card mb-3 p-3">
                    <h6 class="h6"><?php echo $appLanguage->getImport();?></h6>
                    <div class="toolbar btn-group mb-2">
                        <div class="input-group input-group-file mb-2">
                            <div class="custom-file">
                                <input id="fileInput" type="file" class="custom-file-input" accept=".json,.yml,.yaml">
                                <label class="custom-file-label" id="selectedFile" for="fileInput" data-label="<?php echo $appLanguage->getSelect();?>"><?php echo $appLanguage->getChooseFile();?></label>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="btnImportJson"><?php echo $appLanguage->getImport();?></button>
                            </div>
                        </div>
                    </div>
                    <h6 class="h6"><?php echo $appLanguage->getExport();?></h6>
                    <div class="toolbar btn-group mb-2">
                        <button class="btn btn-success ok" id="btnDownloadJson"><?php echo $appLanguage->getJson();?></button>
                        <button class="btn btn-warning warn" id="btnDownloadYaml"><?php echo $appLanguage->getYaml();?></button>
                    </div>
                </div>

                <div class="footer">
                    <div class="btn-group muted d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-secondary ghost" id="btnSaveLocal"><?php echo $appLanguage->getSaveLocal();?></button>
                        <button class="btn btn-outline-secondary ghost" id="btnLoadLocal"><?php echo $appLanguage->getLoadLocal();?></button>
                    </div>
                </div>
            </aside>

            <!-- Main -->
            <main class="main mt-3 col-md-8 col-lg-9">
                <div class="card mb-3 p-3">
                    <h6 class="h6"><?php echo $appLanguage->getTableList();?></h6>
                    <div id="tableList" class="table-list"></div>
                </div>

                <div class="card p-3">
                    <h6 class="h6"><?php echo $appLanguage->getOutputPreview();?></h6>
                    <div class="preview row">
                        <div class="col-md-6 previewJsonContainer">
                            <div class="muted tiny mb-2"><?php echo $appLanguage->getJson();?></div>
                            <pre id="previewJson"></pre>
                        </div>
                        <div class="col-md-6 previewYamlContainer">
                            <div class="muted tiny mb-2"><?php echo $appLanguage->getYaml();?></div>
                            <pre id="previewYaml"></pre>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Template tetap -->
    <template id="tplTableItem">
        <div class="table-item card px-2 mb-3" draggable="true">
            <div class="table-head d-flex align-items-center py-2 px-1">
                <span class="drag mr-2" title="<?php echo $appLanguage->getDragToSort();?>">↕</span>
                <span class="pill mr-1"><?php echo $appLanguage->getTarget();?></span>
                <input class="form-control in-target mr-2" placeholder="target_table_name" />
                <span class="pill mr-1"><?php echo $appLanguage->getSource();?></span>
                <input class="form-control in-source mr-2" placeholder="source_table_name" />
                <span class="spacer flex-grow-1"></span>
                <div class="controls btn-group">
                    <button class="btn btn-sm btn-outline-secondary ghost btn-up" title="<?php echo $appLanguage->getMoveUp();?>">↑</button>
                    <button class="btn btn-sm btn-outline-secondary ghost btn-down" title="<?php echo $appLanguage->getMoveDown();?>">↓</button>
                    <button class="btn btn-sm btn-danger btn-del" title="<?php echo $appLanguage->getDelete();?>"><?php echo $appLanguage->getDelete();?></button>
                    <button class="btn btn-sm btn-outline-info ghost btn-toggle" title="<?php echo $appLanguage->getDetail();?>"><?php echo $appLanguage->getButtonDetail();?> ▸</button>
                </div>
            </div>
            <details>
                <summary style="display:none"><span class="chev">▶</span> <?php echo $appLanguage->getDetail();?></summary>
                <div class="p-3">
                    <div class="mb-3">
                        <div class="muted tiny mb-1"><?php echo $appLanguage->getColumnMapping();?> (<?php echo $appLanguage->getTarget();?> : <?php echo $appLanguage->getSource();?>)</div>
                        <div class="map-list"></div>
                        <div class="toolbar mt-2">
                            <button class="btn btn-success ok btn-add-map"><?php echo $appLanguage->getAddMapping();?></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="muted tiny mb-1"><?php echo $appLanguage->getPreImportScript();?></div>
                        <div class="pre-script-list"></div>
                        <div class="toolbar mt-2">
                            <button class="btn btn-success ok btn-add-pre-import-script"><?php echo $appLanguage->getAddPreImportScript();?></button>
                        </div>
                    </div>
                    <div>
                        <div class="muted tiny mb-1"><?php echo $appLanguage->getPostImportScript();?></div>
                        <div class="post-script-list"></div>
                        <div class="toolbar mt-2">
                            <button class="btn btn-success ok btn-add-post-import-script"><?php echo $appLanguage->getAddPostImportScript();?></button>
                        </div>
                    </div>
                </div>
            </details>
        </div>
    </template>

    <template id="tplMapRow">
        <div class="map-row form-row align-items-center py-1 px-1">
            <div class="pair form-row w-100">
                <div class="col"><input class="form-control in-target-col" placeholder="target_column" /></div>
                <div class="col"><input class="form-control in-source-col" placeholder="source_column" /></div>
                <div class="col-auto"><button class="btn btn-sm btn-danger btn-del-map" title="<?php echo $appLanguage->getDeleteRow();?>">✕</button>
                </div>
            </div>
        </div>
    </template>

    <template id="tplScriptRow">
        <div class="map-row form-row align-items-center py-1">
            <div class="col"><input class="form-control in-script" placeholder="SQL..." /></div>
            <div class="col-auto"><button class="btn btn-sm btn-danger btn-del-script" title="<?php echo $appLanguage->getDeleteRow();?>">✕</button>
            </div>
        </div>
    </template>

    <!-- Resource Modal -->
    <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="commonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="commonModalLabel"><?php echo $appLanguage->getInformation(); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $appLanguage->getClose(); ?>">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $appLanguage->getClose(); ?></button>
        </div>
        </div>
    </div>
    </div>
    <script>
        const dialogTemplate = {
            info_select_file: "<?php echo $appLanguage->getInfoSelectFile(); ?>",
            error_parse_json: "<?php echo $appLanguage->getErrorParseJson(); ?>",
            error_parse_yaml: "<?php echo $appLanguage->getErrorParseYaml(); ?>",
            error_read_file: "<?php echo $appLanguage->getErrorReadFile(); ?>",
            error_reader: "<?php echo $appLanguage->getErrorReader(); ?>",
            success_save_local: "<?php echo $appLanguage->getSuccessSaveLocal(); ?>",
            error_no_draft: "<?php echo $appLanguage->getErrorNoDraft(); ?>",
            error_message: "<?php echo $appLanguage->getErrorMessage(); ?>",
            success_message: "<?php echo $appLanguage->getSuccessMessage(); ?>",
            alert: "<?php echo $appLanguage->getAlert(); ?>",
            information: "<?php echo $appLanguage->getInformation(); ?>",
            error: "<?php echo $appLanguage->getError(); ?>"
        };
    </script>


<?php
require_once $appInclude->mainAppFooter(__DIR__);
