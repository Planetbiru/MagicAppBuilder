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

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="database-type" content="<?php echo $dbType;?>">
    <meta name="database-name" content="<?php echo $databaseConfig->getDatabaseName();?>">
    <meta name="database-schema" content="<?php echo $schemaName;?>">
    <meta name="application-id" content="<?php echo $applicationId;?>">
    <title>Database Explorer</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
    <link rel="stylesheet" href="../lib.assets/css/database-explorer.min.css">
    <script src="../lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
    <script src="../lib.assets/datetimepicker/jquery.datetimepicker.full.min.js"></script>
    <script src="../lib.assets/js/TableParser.min.js"></script>
    <script src="../lib.assets/js/SQLConverter.min.js"></script>
    <script src="../lib.assets/js/Column.min.js"></script>
    <script src="../lib.assets/js/Entity.min.js"></script>
    <script src="../lib.assets/js/Diagram.min.js"></script>
    <script src="../lib.assets/js/EntityEditor.min.js"></script>
    <script src="../lib.assets/js/EntityRenderer.min.js"></script>
    <script src="../lib.assets/js/ResizablePanel.min.js"></script>
    <script src="../lib.assets/js/DatabaseExplorer.min.js"></script>
    <script src="../lib.assets/js/EntityContextMenu.min.js"></script>
    <script src="../lib.assets/js/TabDragger.min.js"></script>
    <script src="../lib.assets/js/SVGtoPNG.min.js"></script>
    <script src="../lib.assets/js/GraphQLSchemaUtils.min.js"></script>
    <script src="../lib.assets/wasm/sql-wasm.min.js"></script>
    <link rel="stylesheet" href="../lib.assets/css/entity-editor.min.css">
    <link rel="stylesheet" href="../lib.assets/datetimepicker/jquery.datetimepicker.min.css">
    <script src="../lib.assets/xlsx/xlsx.full.min.js"></script>
    <script src="../lib.assets/papaparse/papaparse.min.js"></script>
    <script src="../lib.assets/dbf/DBFParser.min.js"></script>
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
                                <div class="entity-filter">
                                    <input type="text" id="tableFilter" placeholder="Type entity name">
                                </div>
                                <ul class="table-list"></ul>
                            </div>
                            <div class="entities-container tabs-container">
                                <!-- Entities will be rendered here -->
                                <div class="panel-title">
                                    <ul class="tab-mover">
                                        <li><a class="move-left" href="javascript:"><span class="icon-move-left"></span></a></li>
                                        <li><a class="move-right" href="javascript:"><span class="icon-move-right"></span></a></li>
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
                            <textarea class="query-generated" spellcheck="false" autocomplete="off" readonly></textarea>
                        </div>
                    </div>
                    <div class="editor-container">
                        <div class="button-container">
                            

                            <span class="btn-group">
                                <button class="btn" onclick="editor.showEditor(-1)">Add New Entity</button>
                            </span>

                            <!-- Import group -->
                            <span class="btn-group btn-group-with-label">
                                <span class="group-label">Import</span>
                                <button class="btn" onclick="editor.uploadEntities()">Entity</button>
                                <button class="btn" onclick="editor.importSQL()">SQL</button>
                                <button class="btn" onclick="editor.importSheet()">Spreadsheet</button>
                                <button class="btn" onclick="editor.triggerImportFromClipboard()">Clipboard</button>
                                <button class="btn" onclick="editor.importGraphQLSchema()">GraphQL</button>
                                <input class="import-file-json" type="file" accept=".json" style="display: none;" />
                                <input class="import-file-sql" type="file" accept=".sql,.sqlite,.db" style="display: none;" />
                                <input class="import-file-sheet" type="file" accept=".xlsx,.xls,.ods,.csv,.dbf" style="display: none;" />
                                <input class="import-file-graphql" type="file" accept=".graphqls" style="display: none;" />
                            </span>

                            <!-- Export group -->
                            <span class="btn-group btn-group-with-label">
                                <span class="group-label">Export</span>
                                <button class="btn" onclick="editor.downloadEntities()">Entity</button>
                                <button class="btn" onclick="downloadSVG()">SVG</button>
                                <button class="btn" onclick="downloadPNG()">PNG</button>
                                <button class="btn" onclick="downloadMD()">MD</button>
                                <button class="btn" onclick="downloadHTML()">HTML</button>
                                <button class="btn" onclick="editor.downloadSQL()">SQL</button>
                                <button class="btn" onclick="editor.showEntitySelector()">GraphQL</button>
                            </span>
                            
                            <span class="btn-group">
                                <button class="btn" onclick="editor.sortEntities()">Sort</button>    
                                <button class="btn" onclick="editor.sortAndGroupEntities()">Sort by Type</button>              
                                <label for="draw-relationship"><input type="checkbox" id="draw-relationship" class="draw-relationship" checked> Relationship</label>  
                            </span>
                        </div>

                        <!-- Entity Editor Form -->
                        <div class="editor-form" style="display:none;">
                            <div class="entity-container">
                                <input class="entity-name" type="text" id="entity-name" placeholder="Enter entity name">
                                <button class="btn" onclick="editor.addColumn(true)">Add Column</button>
                                <button class="btn" onclick="editor.addColumnFromTemplate()">Add Column from Template</button>
                                <button class="btn" onclick="editor.saveEntity()">Save Entity</button>                    
                                <button class="btn" onclick="editor.showEditorTemplate()">Edit Template</button>
                                <button class="btn" onclick="editor.preference()">Preferences</button>
                                <button class="btn" onclick="editor.cancelEdit()">Cancel</button>
                                <button class="btn btn-data" onclick="editor.showDescriptionDialog()">Description</button>
                                <button class="btn btn-data" onclick="editor.viewData()">Data</button>
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
                        <label>Programming Language</label>
                        <select class="form-control programming-language-selector">
                            <option value="php">PHP</option>
                            <option value="java">Java (Commercial Use)</option>
                            <option value="kotlin">Kotlin (Experimental)</option>
                            <option value="nodejs">Node.js (Commercial Use)</option>
                            <option value="python">Python (Commercial Use)</option>
                            <option value="go">Go (Experimental)</option>
                        </select>
                        &nbsp;
                        <label><input type="checkbox" class="entity-type-checker" data-entity-type="custom" onchange="editor.checkEntityTypes(this)" checked> Custom Entities</label>
                        &nbsp;
                        <label><input type="checkbox" class="entity-type-checker" data-entity-type="system" onchange="editor.checkEntityTypes(this)"> System Entities</label>
                        &nbsp;
                        <label><input type="checkbox" class="in-memory-cache-checker" data-entity-type="in-memory-cache" onchange="editor.inMemoryCacheChange(this)"> In-Memory Cache</label>
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
            <li id="menu-copy-structure"><a href="javascript:;" onclick="editor.copyTableStructure(event);">Copy Structure</a></li>
            <li id="menu-copy-data"><a href="javascript:;" onclick="editor.copyTableData(event);">Copy Data</a></li>
            <li id="menu-copy-both"><a href="javascript:;" onclick="editor.copyTableStructureAndData(event);">Copy All</a></li>
            <li id="menu-paste-clipboard"><a href="javascript:;" onclick="editor.triggerImportFromClipboard();">Import from Clipboard</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.editEntityContextMenu();">Edit Entity</a></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.dataEntityContextMenu();">Edit Data</a></li>
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
            <li id="menu-copy-structure"><a href="javascript:;" onclick="editor.copyTableStructure(event);">Copy Structure</a></li>
            <li id="menu-copy-data"><a href="javascript:;" onclick="editor.copyTableData(event);">Copy Data</a></li>
            <li id="menu-copy-both"><a href="javascript:;" onclick="editor.copyTableStructureAndData(event);">Copy All</a></li>
            <li id="menu-paste-clipboard"><a href="javascript:;" onclick="editor.triggerImportFromClipboard();">Import from Clipboard</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.editEntityContextMenu();">Edit Entity</a></li>
            <li id="menu-edit-entity"><a href="javascript:;" onclick="editor.dataEntityContextMenu();">Edit Data</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-duplicate-entity"><a href="javascript:;" onclick="editor.duplicateEntity();">Duplicate Entity</a></li>
            <li class="dropdown-divider"></li>
            <li id="menu-delete-diagram"><a href="javascript:;" onclick="editor.deleteAllDiagrams();">Delete All Diagrams</a></li>
            <li id="menu-delete-entity"><a href="javascript:;" onclick="editor.deleteAllEntities();">Delete All Entities</a></li>
        </ul>
    </div>
</body>
</html>