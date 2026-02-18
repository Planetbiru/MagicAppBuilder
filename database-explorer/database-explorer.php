<?php

use DatabaseExplorer\DatabaseExplorer;

require_once dirname(__DIR__) . "/inc.app/auth-core.php";
if(!$userLoggedIn)
{
    exit();
}
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__))
{
    // Prevent user to access this path
    exit();
}

if(!isset($databaseName))
{
    $databaseName = "";
}
$hash = md5("$applicationId-$dbType-{$databaseConfig->getDatabaseName()}-$schemaName");
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="database-type" content="<?php echo $dbType;?>">
    <meta name="database-name" content="<?php echo $databaseConfig->getDatabaseName();?>">
    <meta name="database-schema" content="<?php echo $schemaName;?>">
    <meta name="application-id" content="<?php echo $applicationId;?>">
    <meta name="hash" content="<?php echo $hash;?>">
    <title>Database Explorer</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
    <link rel="stylesheet" href="../lib.assets/css/database-explorer.min.css">
    <script src="../lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
    <script src="../lib.assets/datetimepicker/jquery.datetimepicker.full.min.js"></script>
    <script src="../lib.assets/js/TableParser.js"></script>
    <script src="../lib.assets/js/SQLConverter.js"></script>
    <script src="../lib.assets/js/MWBConverter.js"></script>
    <script src="../lib.assets/js/Column.js"></script>
    <script src="../lib.assets/js/Entity.js"></script>
    <script src="../lib.assets/js/Diagram.js"></script>
    <script src="../lib.assets/js/DatabaseExplorer.js"></script>
    <script src="../lib.assets/js/EntityEditor.js"></script>
    <script src="../lib.assets/js/EntityRenderer.js"></script>
    <script src="../lib.assets/js/ResizablePanel.js"></script>
    <script src="../lib.assets/js/EntityContextMenu.js"></script>
    <script src="../lib.assets/js/TabDragger.min.js"></script>
    <script src="../lib.assets/js/SVGtoPNG.min.js"></script>
    <script src="../lib.assets/js/GraphQLSchemaUtils.min.js"></script>
    <script src="../lib.assets/wasm/sql-wasm.min.js"></script>
    <script src="../lib.assets/jszip/jszip.min.js"></script>
    <link rel="stylesheet" href="../lib.assets/css/entity-editor.css">
    <link rel="stylesheet" href="../lib.assets/datetimepicker/jquery.datetimepicker.min.css">
    <script src="../lib.assets/xlsx/xlsx.full.min.js"></script>
    <script src="../lib.assets/papaparse/papaparse.min.js"></script>
    <script src="../lib.assets/dbf/DBFParser.min.js"></script>
    <style>
        
    </style>
</head>

<body data-from-default-app="<?php echo $fromDefaultApp ? 'true' : 'false'; ?>" database-type="<?php echo $dbType;?>" data-no-table="<?php echo empty($table) ? "true" : "false";?>">
    <div class="sidebar">
        <?php
        try {
            // Show the sidebar with databases if not from default app and not using SQLite
            if (!$fromDefaultApp && $dbType != 'sqlite' && $accessedFrom == 'database-explorer') {
                echo DatabaseExplorer::showSidebarDatabases($pdo, $applicationId, $databaseName, $schemaName, $databaseConfig);
            }

            // Show the sidebar with tables
            if($applicationId == '')
            {
                echo DatabaseExplorer::showSidebarTables($pdo, $applicationId, $databaseName, $schemaName, $table);
            }
            else
            {
                echo DatabaseExplorer::showSidebarTablesSithGroup($pdo, $applicationId, $databaseName, $schemaName, $table);
            }
        } catch (PDOException $e) {
            // Handle connection errors
            if ($e->getCode() == 0x3D000 || strpos($e->getMessage(), '1046') !== false) {
                echo "Please choose one database";
            } else {
                echo "Connection failed: " . $e->getMessage();
            }
        }
        ?>
    </div>
    <div class="content">
        <?php
        // Display table structure and data if a table is selected
        if ($table) {
            echo DatabaseExplorer::showTableStructure($pdo, $applicationId, $databaseName, $schemaName, $table);
            if(isset($_GET['action']) && $_GET['action'] == 'update-form' && isset($_GET['id']))
            {
                $primaryKeyValue = addslashes($_GET['id']);
                echo DatabaseExplorer::updateData($pdo, $applicationId, $databaseName, $schemaName, $table, $primaryKeyValue);
            }
            else if(isset($_GET['action']) && $_GET['action'] == 'insert-form')
            {
                echo DatabaseExplorer::insertData($pdo, $applicationId, $databaseName, $schemaName, $table);
            }
            else
            {
                echo DatabaseExplorer::showTableData($pdo, $applicationId, $databaseName, $schemaName, $table, $page, $limit);
            }
        }

        // Display the query executor form and results
        echo DatabaseExplorer::createQueryExecutorForm($lastQueries, $dbType, $applicationId);
        echo $queryResult;
        ?>
    </div>

    <div class="modal" id="queryTranslatorModal">
        <div class="modal-backdrop"></div>

        <div class="modal-content">
            <div class="modal-header">
                <h3>Import Database Structure</h3>
                <span class="close-btn cancel-button">&times;</span>
            </div>

            <div class="modal-body">
                <textarea name="original" id="original" class="original" spellcheck="false" autocomplete="off"></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary open-structure">Open File</button>
                &nbsp;
                <button class="btn btn-success translate-structure">Import</button>
                &nbsp;
                <button class="btn btn-warning clear">Clear</button>
                &nbsp;
                <button class="btn btn-secondary cancel-button">Cancel</button>
                <input class="structure-sql" type="file" accept=".sql,.sqlite,.db" style="display: none;" />
            </div>
        </div>
    </div>

    <div class="modal" id="entityEditorModal">
        <div class="modal-backdrop"></div>

        <div class="modal-content">
            <div class="modal-header">
                <h3>Entity Editor</h3>
                <span class="close-btn cancel-button">&times;</span>
            </div>

            <div class="modal-body">
                <div class="entity-editor">
                    <div class="container">
                        <div class="left-panel">
                            <div class="object-container">
                                <div class="schema-container">
                                    <select class="schema-selector" onchange="onChangeDatabase(this)">
                                    </select>
                                </div>
                                <div class="entity-filter">
                                    <input type="text" id="tableFilter" placeholder="Type entity name">
                                </div>
                                <ul class="table-list"></ul>
                            </div>
                            <div class="entities-container tabs-container">
                                <!-- Entities will be rendered here -->
                                <div class="panel-title">
                                    <ul class="tab-mover">
                                        <li><a class="move-first" href="javascript:"><span class="icon-move-first"></span></a></li>
                                        <li><a class="move-left" href="javascript:"><span class="icon-move-left"></span></a></li>
                                        <li><a class="move-right" href="javascript:"><span class="icon-move-right"></span></a></li>
                                        <li><a class="move-last" href="javascript:"><span class="icon-move-last"></span></a></li>
                                    </ul>
                                    <div class="tabs-link-container">
                                        <ul class="diagram-list tabs">
                                            <li class="all-entities active"><a href="javascript:" class="tab-link elected-entity" data-id="all-entities" data-name="">All Entities</a></li>
                                            <li class="add-tab"><a href="javascript:" class="tab-link add-diagram">+</a></li>
                                        </ul>
                                    </div>

                                </div>
                                <div class="diagram-container">
                                    <div id="all-entities" class="diagram tab-content active">
                                        <svg class="erd-svg" width="600" height="800"></svg>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="resize-bar"></div>
                        <div class="right-panel">
                            <div class="table-export-sql-container">
                            <table class="sql-table-export">
                                <thead>
                                    <tr>
                                        <td>
                                            <label><input type="checkbox" class="check-all-entity-structure"> S</label>
                                        </td>
                                        <td>
                                            <label><input type="checkbox" class="check-all-entity-data"> D</label>
                                        </td>
                                        <td>
                                            Entity Name <span class="entity-count"></span>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody class="table-list-for-export">
                                    <!-- data -->
                                </tbody>
                            </table>
                            </div>
                            <div class="foreign-key-container">
                                <label><input type="checkbox" class="with-foreign-key"> With Foreign Key</label>
                            </div>
                            <div class="index-container">
                                <label><input type="checkbox" class="with-index"> With Index</label>
                            </div>
                            <div>
                                <textarea class="query-generated" spellcheck="false" autocomplete="off" readonly></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="editor-container">
                        <div class="button-container">


                            <span class="btn-group">
                                <button class="btn" onclick="editor.showEditor(-1)">Add New Entity</button>
                            </span>

                            <div class="btn-group dropdown">
                                <button class="btn dropdown-toggle" type="button">Import</button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" onclick="editor.uploadEntities()">MagicAppBuilder Model</a>
                                    <a class="dropdown-item" onclick="editor.importSQL()">Database File</a>
                                    <a class="dropdown-item" onclick="editor.importSheet()">Spreadsheet File</a>
                                    <a class="dropdown-item" onclick="editor.triggerImportFromClipboard()">Clipboard</a>
                                    <a class="dropdown-item" onclick="editor.importGraphQLSchema()">GraphQL Schema</a>
                                </div>
                                <input class="import-file-json" type="file" accept=".json" style="display: none;" />
                                <input class="import-file-sql" type="file" accept=".sql,.sqlite,.db,.mwb" style="display: none;" />
                                <input class="import-file-sheet" type="file" accept=".xlsx,.xls,.ods,.csv,.dbf" style="display: none;" />
                                <input class="import-file-graphql" type="file" accept=".graphqls" style="display: none;" />
                            </div>

                            <div class="btn-group dropdown">
                                <button class="btn dropdown-toggle" type="button">Export</button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" onclick="editor.downloadEntities()">MagicAppBuilder Model</a>
                                    <a class="dropdown-item" onclick="editor.downloadMWB()">MySQL Workbench Model</a>
                                    <a class="dropdown-item" onclick="editor.downloadSVG()">SVG Image</a>
                                    <a class="dropdown-item" onclick="editor.downloadPNG()">PNG Image</a>
                                    <a class="dropdown-item" onclick="editor.downloadMD()">Markdown</a>
                                    <a class="dropdown-item" onclick="editor.downloadHTML()">HTML</a>
                                    <a class="dropdown-item" onclick="editor.downloadSQL()">SQL File</a>
                                    <a class="dropdown-item" onclick="editor.showEntitySelector()">GraphQL Application</a>
                                </div>
                            </div>

                            <span class="btn-group">
                                <button class="btn" onclick="editor.sortEntities()">Sort</button>
                                <button class="btn" onclick="editor.sortAndGroupEntities()">Sort by Type</button>
                                <label for="draw-fk-relationship"><input type="checkbox" id="draw-fk-relationship" class="draw-fk-relationship" checked> Foreign Key Relationship</label>
                                <label for="draw-auto-relationship"><input type="checkbox" id="draw-auto-relationship" class="draw-auto-relationship"> Auto Relationship</label>
                            </span>
                        </div>

                        <!-- Entity Editor Form -->
                        <div class="editor-form" style="display:none;">
                            <div class="entity-container">
                                <input class="entity-name" type="text" id="entity-name" placeholder="Enter entity name">
                                <button class="btn" onclick="editor.addColumn(true)">Add Column</button>
                                <button class="btn" onclick="editor.addColumnFromTemplate()">Add Column from Template</button>
                                <button class="btn" onclick="editor.editForeignKeys()">Foreign Key</button>
                                <button class="btn" onclick="editor.manageIndexes()">Index</button>
                                <button class="btn" onclick="editor.showDescriptionDialog()">Description</button>
                                <button class="btn btn-data" onclick="editor.viewData()">Data</button>
                                <button class="btn" onclick="editor.showEditorTemplate()">Edit Template</button>
                                <button class="btn" onclick="editor.preference()">Preferences</button>
                                <button class="btn" onclick="editor.saveEntity()">Save Entity</button>
                                <button class="btn" onclick="editor.cancelEdit()">Cancel</button>
                                <div class="table-container">
                                    <table id="table-entity-editor">
                                        <thead>
                                            <tr>
                                                <th class="header-drag-handle"></th>
                                                <th class="column-action"></th>
                                                <th>Column Name</th>
                                                <th>Type</th>
                                                <th>Length</th>
                                                <th>Value</th>
                                                <th>Default</th>
                                                <th class="column-nl">NL</th>
                                                <th class="column-pk">PK</th>
                                                <th class="column-ai">AI</th>
                                                <th class="column-ds">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="entity-columns-table-body">
                                            <!-- Columns will be dynamically inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="template-container">
                                <button class="btn" onclick="editor.addColumnTemplate(true)">Add Column</button>
                                <button class="btn" onclick="editor.saveTemplate()">Save Template</button>
                                <button class="btn" onclick="editor.cancelEditTemplate()">Cancel</button>
                                <div class="table-container">
                                    <table id="table-template-editor" class="table-template-editor">
                                        <thead>
                                            <tr>
                                                <th class="header-drag-handle"></th>
                                                <th class="column-action"></th>
                                                <th>Column Name</th>
                                                <th>Type</th>
                                                <th>Length</th>
                                                <th>Value</th>
                                                <th>Default</th>
                                                <th class="column-nl">NL</th>
                                                <th class="column-ds">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="template-columns-table-body">
                                            <!-- Columns will be dynamically inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary import-from-entity">Import</button>
                &nbsp;
                <button class="btn btn-secondary cancel-button">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-lg modal-top-80" id="exportModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Export Database</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary button-ok">OK</button>
                &nbsp;
                <button class="btn btn-secondary button-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-xxl" id="entityDataEditorModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Entity Data Editor</h3>
                <span class="close-btn cancel-button">&times;</span>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button class="btn btn-danger clear-data-entity">Clear</button>
                &nbsp;
                <button class="btn btn-success export-data-entity">Export</button>
                &nbsp;
                <button class="btn btn-success import-data-entity">Import</button>
                &nbsp;
                <button class="btn btn-success add-data-entity">Add</button>
                &nbsp;
                <button class="btn btn-primary save-data-entity">Save</button>
                &nbsp;
                <button class="btn btn-secondary cancel-button">Cancel</button>
                <input type="file" id="importDataFileInput" accept=".xlsx,.xls,.ods,.csv,.dbf" style="display: none;">
            </div>
        </div>
    </div>

    <div class="modal modal-xl modal-top-40" id="graphqlGeneratorModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>GraphQL Generator</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
                <form>
                    <div class="entity-type-selector">
                        <div>
                            <label>Profile</label>
                            <select class="form-control graphql-app-profile" onchange="editor.gqlChangeProfile()">
                            </select>
                            <button type="button" class="btn btn-primary" onclick="editor.manageGraphQlAppProfile()">Manage</button>
                        </div>
                        <div>
                            <label>Programming Language</label>
                            <select class="form-control programming-language-selector">
                                <option value="php">PHP</option>
                                <option value="java">Java (Commercial Use)</option>
                                <option value="kotlin">Kotlin (Commercial Use)</option>
                                <option value="nodejs">Node.js (Commercial Use)</option>
                                <option value="python">Python (Commercial Use)</option>
                                <option value="go">Go (Commercial Use)</option>
                            </select>
                            &nbsp;
                            <label><input type="checkbox" class="entity-type-checker" data-entity-type="custom" onchange="editor.checkEntityTypes(this)" checked> Custom Entities</label>
                            &nbsp;
                            <label><input type="checkbox" class="entity-type-checker" data-entity-type="system" onchange="editor.checkEntityTypes(this)"> System Entities</label>
                            &nbsp;
                            <label><input type="checkbox" class="in-memory-cache-checker" data-entity-type="in-memory-cache" onchange="editor.inMemoryCacheChange(this)"> In-Memory Cache</label>
                        </div>
                    </div>
                    <div class="entity-selector-container"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary generate-graphql-ok" onclick="editor.handleOkGenerate()">Generate GraphQL</button>
                &nbsp;
                <button class="btn btn-primary generate-graphql-ok" onclick="editor.handleOkGenerateWithFrontend()">Generate GraphQL with Frontend</button>
                &nbsp;
                <button class="btn btn-secondary generate-graphql-cancel" onclick="editor.handleCancelGenerate()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-md" id="foreignKeyModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Foreign Key Settings</h3>
                <span class="close-btn cancel-button">&times;</span>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Entity Name</label>
                        <input type="text" class="form-control entity_name_selector" readonly>
                    </div>
                    <div class="form-group">
                        <label>Column Name</label>
                        <input type="text" class="form-control column_name_selector" readonly>
                    </div>
                    <div class="form-group">
                        <label>Key Name</label>
                        <input type="text" class="form-control foreign_key_name_selector">
                    </div>
                    <div class="form-group">
                        <label>Reference Table</label>
                        <select class="form-control reference_table_selector"></select>
                    </div>
                    <div class="form-group">
                        <label>Reference Column</label>
                        <select class="form-control reference_column_selector"></select>
                    </div>
                    <div class="form-group">
                        <label>On Update</label>
                        <select class="form-control on_update_action_selector">
                            <option value="NO ACTION">NO ACTION</option>
                            <option value="CASCADE">CASCADE</option>
                            <option value="SET NULL">SET NULL</option>
                            <option value="RESTRICT">RESTRICT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>On Delete</label>
                        <select class="form-control on_delete_action_selector">
                            <option value="NO ACTION">NO ACTION</option>
                            <option value="CASCADE">CASCADE</option>
                            <option value="SET NULL">SET NULL</option>
                            <option value="RESTRICT">RESTRICT</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger delete-foreign-key">Delete</button>
                &nbsp;
                <button class="btn btn-success save-foreign-key">Save</button>
                &nbsp;
                <button class="btn btn-secondary cancel-foreign-key">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-xl" id="foreignKeyBulkModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Foreign Key Settings</h3>
                <span class="close-btn cancel-button">&times;</span>
            </div>
            <div class="modal-body">
                <p class="entity-name"></p>
                <form>
                    <table class="foreign-key-editor-table">
                        <thead>
                            <tr>
                                <th align="center" class="remover">❌</th>
                                <th>Column Name</th>
                                <th>Key Name</th>
                                <th>Reference Table</th>
                                <th>Reference Column</th>
                                <th>On Update</th>
                                <th>On Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary add-foreign-key" onclick="editor.addForeignKey(this);">Add</button>
                &nbsp;
                <button class="btn btn-success save-foreign-key-bulk">Save</button>
                &nbsp;
                <button class="btn btn-secondary cancel-foreign-key-bulk">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Index Editor Modal -->
    <div id="indexEditorModal" class="modal modal-lg" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Manage Indexes</h3>
                <span class="close-btn cancel-button" onclick="this.closest('.modal').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <p class="entity-name"></p>
                <table id="index-editor-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th align="center" class="remover">❌</th>
                            <th>Index Name</th>
                            <th>Columns</th>
                            <th width="80">Unique</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary add-index-row">Add Index</button>
                &nbsp;
                <button type="button" class="btn btn-primary add-index-from-fk">From Foreign Key</button>
                &nbsp;
                <button type="button" class="btn btn-success save-indexes">Save Indexes</button>
                &nbsp;
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').style.display='none'">Cancel</button>
            </div>
        </div>
    </div>


    <div class="modal modal-sm" id="descriptionModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Title</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
                <textarea class="description-textarea" placeholder="Enter description here..." spellcheck="false" autocomplete="off"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary description-ok">OK</button>
                &nbsp;
                <button class="btn btn-secondary description-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-sm" id="settingModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Title</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
                Confirmation
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary confirm-ok">OK</button>
                &nbsp;
                <button class="btn btn-secondary confirm-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-sm" id="profileModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Title</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
                Profile
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary confirm-ok">OK</button>
                &nbsp;
                <button class="btn btn-secondary confirm-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-sm" id="asyncConfirm">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Title</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
                Message
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary confirm-ok">OK</button>
                &nbsp;
                <button class="btn btn-secondary confirm-cancel">Cancel</button>
            </div>
        </div>
    </div>

    <div class="modal modal-sm" id="asyncAlert">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Title</h3>
                <span class="close-btn cancel-button">×</span>
            </div>
            <div class="modal-body">
                Message
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary alert-ok">OK</button>
            </div>
        </div>
    </div>

    <div id="context-menu" class="context-menu context-menu-relation" style="display: none; position: absolute; z-index: 1000;">
        <ul>
            <li class="has-submenu" id="menu-make-reference">
            <a href="javascript:;">Add Reference</a>
            <ul class="submenu" id="reference-submenu">
                <li data-type="relation"><label for="id1"><input id="id1" type="checkbox"> Check all</label></li>
            </ul>
            <li id="menu-export-svg"><a href="javascript:;" onclick="editor.downloadEntitySVG(event);">Export SVG</a></li>
            <li id="menu-export-png"><a href="javascript:;" onclick="editor.downloadEntityPNG(event);">Export PNG</a></li>
            <li id="menu-export-md"><a href="javascript:;" onclick="editor.downloadDiagramMD(event);">Export MD</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-copy-structure"><a href="javascript:;" onclick="editor.copyTableStructure(event);">Copy DDL</a></li>
            <li id="menu-copy-structure"><a href="javascript:;" onclick="editor.copyTableIndexes(event);">Copy Indexes</a></li>
            <li id="menu-copy-data"><a href="javascript:;" onclick="editor.copyTableData(event);">Copy Data</a></li>
            <li id="menu-copy-both"><a href="javascript:;" onclick="editor.copyTableStructureAndData(event);">Copy DDL + Data</a></li>
            <li id="menu-paste-clipboard"><a href="javascript:;" onclick="editor.triggerImportFromClipboard();">Import from Clipboard</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.editEntityContextMenu();">Edit Entity</a></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.dataEntityContextMenu();">Edit Data</a></li>
            <li id="menu-edit-foreign-key"><a href="javascript:;" onclick="editor.editForeignKeyContextMenu(event);">Edit Foreign Key</a></li>
            <li id="menu-edit-index"><a href="javascript:;" onclick="editor.editIndexContextMenu();">Manage Indexes</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-duplicate-entity"><a href="javascript:;" onclick="editor.duplicateEntity();">Duplicate Entity</a></li>
        </ul>
    </div>

    <div id="context-menu-all-entities" class="context-menu context-menu-relation" style="display: none; position: absolute; z-index: 1000;">
        <ul>
            <li id="menu-export-svg"><a href="javascript:;" onclick="editor.downloadEntitySVG(event);">Export SVG</a></li>
            <li id="menu-export-png"><a href="javascript:;" onclick="editor.downloadEntityPNG(event);">Export PNG</a></li>
            <li id="menu-export-md"><a href="javascript:;" onclick="editor.downloadDiagramMD(event);">Export MD</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-copy-structure"><a href="javascript:;" onclick="editor.copyTableStructure(event);">Copy DDL</a></li>
            <li id="menu-copy-structure"><a href="javascript:;" onclick="editor.copyTableIndexes(event);">Copy Indexes</a></li>
            <li id="menu-copy-data"><a href="javascript:;" onclick="editor.copyTableData(event);">Copy DDL + Data</a></li>
            <li id="menu-copy-both"><a href="javascript:;" onclick="editor.copyTableStructureAndData(event);">Copy All</a></li>
            <li id="menu-paste-clipboard"><a href="javascript:;" onclick="editor.triggerImportFromClipboard();">Import from Clipboard</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.editEntityContextMenu();">Edit Entity</a></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.dataEntityContextMenu();">Edit Data</a></li>
            <li id="menu-edit-foreign-key"><a href="javascript:;" onclick="editor.editForeignKeyContextMenu(event);">Edit Foreign Key</a></li>
            <li id="menu-edit-index"><a href="javascript:;" onclick="editor.editIndexContextMenu();">Manage Indexes</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-duplicate-entity"><a href="javascript:;" onclick="editor.duplicateEntity();">Duplicate Entity</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-delete-diagram"><a href="javascript:;" onclick="editor.deleteAllDiagrams();">Delete All Diagrams</a></li>
            <li id="menu-delete-entity"><a href="javascript:;" onclick="editor.deleteAllEntities();">Delete All Entities</a></li>
        </ul>
    </div>
</body>
</html>