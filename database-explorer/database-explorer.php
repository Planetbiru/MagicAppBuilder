<?php

if(basename($_SERVER['PHP_SELF']) == basename(__FILE__))
{
    // Prevent user to access this path
    exit();
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
    <link rel="stylesheet" href="../lib.assets/css/entity-editor.css">
    <link rel="stylesheet" href="../lib.assets/css/database-explorer.min.css">
    <script src="../lib.assets/js/TableParser.min.js"></script>
    <script src="../lib.assets/js/SQLConverter.min.js"></script>
    <script src="../lib.assets/js/EntityEditor.min.js"></script>
    <script src="../lib.assets/js/EntityRenderer.min.js"></script>
    <script src="../lib.assets/js/ResizablePanel.min.js"></script>
    <script src="../lib.assets/js/DatabaseExplorer.min.js"></script>
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
            echo DatabaseExplorer::showSidebarTables($pdo, $applicationId, $databaseName, $schemaName, $table);
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
        echo DatabaseExplorer::createQueryExecutorForm($lastQueries, $dbType);
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
                <textarea name="original" id="original" class="original" spellcheck="false"></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary open-structure">Open File</button>
                &nbsp;            
                <button class="btn btn-success translate-structure">Import</button>
                &nbsp;
                <button class="btn btn-warning clear">Clear</button>
                &nbsp;
                <button class="btn btn-secondary cancel-button">Cancel</button>
                <input class="structure-sql" type="file" accept=".sql" style="display: none;" />
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
                                <div class="panel-title">
                                    <h3>Table List</h3>
                                </div>
                                <ul class="table-list"></ul>
                            </div>
                            <div class="entities-container tabs-container">
                                <!-- Entities will be rendered here -->
                                <div class="panel-title">
                                    <ul class="tab-mover">
                                        <li><a href="javascript:"><span class="icon-move-left"></span></a></li>
                                        <li><a href="javascript:"><span class="icon-move-right"></span></a></li>
                                    </ul>
                                    <div class="tabs-link-container">
                                        <ul class="diagram-list tabs">
                                            <li class="all-entities active"><a href="javascript:" class="tab-link elected-entity" data-id="all-entities" data-name="">All Entities</a></li>
                                            <li><a href="javascript:" class="tab-link add-diagram">+</a></li>
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
                            <div class="entity-selector"><label><input type="checkbox" class="check-all-entity">Check all <span class="entity-count"></span></label></div>
                            <ul class="table-list-for-export"></ul>
                            <textarea class="query-generated" spellcheck="false"></textarea>
                        </div>
                    </div>
                    
                    <div class="editor-container">
                        <div class="button-container">
                            <button class="btn" onclick="editor.showEditor(-1)">Add New Entity</button>
                            <button class="btn" onclick="editor.uploadEntities()">Upload Entity</button>
                            <button class="btn" onclick="editor.downloadEntities()">Download Entity</button>
                            <button class="btn" onclick="editor.importSQL()">Upload SQL</button>
                            <button class="btn" onclick="editor.downloadSQL()">Download SQL</button>
                            <button class="btn" onclick="downloadSVG()">Download SVG</button>
                            <button class="btn" onclick="downloadPNG()">Download PNG</button>
                            <button class="btn" onclick="editor.sortEntities()">Sort Entity</button>              
                            <label for="draw-relationship"><input type="checkbox" id="draw-relationship" class="draw-relationship" checked> Draw Relationship</label>
                            <input class="import-file-json" type="file" accept=".json" style="display: none;" />
                            <input class="import-file-sql" type="file" accept=".sql" style="display: none;" />
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
                                <div class="table-container">
                                    <table id="table-entity-editor">
                                        <thead>
                                            <tr>
                                                <th class="column-action"></th>
                                                <th>Column Name</th>
                                                <th>Type</th>
                                                <th>Length</th>
                                                <th>Value</th>
                                                <th>Default</th>
                                                <th class="column-nl">NL</th>
                                                <th class="column-pk">PK</th>
                                                <th class="column-ai">AI</th>
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
                                                <th class="column-action"></th>
                                                <th>Column Name</th>
                                                <th>Type</th>
                                                <th>Length</th>
                                                <th>Value</th>
                                                <th>Default</th>
                                                <th class="column-nl">NL</th>
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
</body>
</html>