<?php

use MagicObject\SecretObject;

require_once __DIR__ . "/inc.app/auth.php";
require_once __DIR__ . "/inc.app/navs.php";
require_once __DIR__ . "/inc.app/sqlite-detector.php";

$constShowActive = ' show active';
$constSelected = ' selected';

?><!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <title>MagicAppBuilder</title>
  <link rel="icon" type="image/png" href="favicon.png" />
  <link rel="shortcut icon" type="image/png" href="favicon.png" />
  <link rel="stylesheet" type="text/css" href="lib.assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/cm/lib/codemirror.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/css.css">
  <link rel="stylesheet" type="text/css" href="lib.assets/css/fontawesome/css/all.min.css">
  <script type="text/javascript" src="lib.assets/jquery/js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="lib.assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="lib.assets/js/Editor.min.js"></script>
  <script type="text/javascript" src="lib.assets/cm/lib/codemirror.js"></script>
  <script type="text/javascript" src="lib.assets/cm/addon/mode/loadmode.js"></script>
  <script type="text/javascript" src="lib.assets/cm/mode/meta.js"></script>
  <script type="text/javascript" src="lib.assets/js/script.js"></script>
  <script type="text/javascript" src="lib.assets/js/Sortable.min.js"></script>
</head>

<body>
  <div class="all">
    <div class="tabs">
      <ul class="nav nav-tabs" id="maintab" role="tablist">
        <?php
        $navigators = $appNavs->getNavs();
        foreach ($navigators as $nav) {
        ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link<?php echo $nav->getActive() ? ' active' : ''; ?>" id="<?php echo $nav->getKey(); ?>-tab" data-toggle="tab" data-target="#<?php echo $nav->getKey(); ?>" type="button" role="tab" aria-controls="config" aria-selected="true"><?php echo $nav->getCaption(); ?></button>
          </li>
        <?php
        }
        ?>

      </ul>
      <div class="tab-content" id="myTabContent">

        <?php
        $nav = $appNavs->item(0);
        $cfgSession = new SecretObject($builderConfig->getSessions());
        ?>
        <div id="<?php echo $nav->getKey(); ?>" class="tab-pane fade<?php echo $nav->getActive() ? $constShowActive : ''; ?>" role="tabpanel" aria-labelledby="<?php echo $nav->getKey(); ?>-tab">
          <div class="main-menu">
            <button type="button" class="btn btn-primary change-workspace" data-toggle="modal" data-target="#modal-workspace">Workspace</button>
          </div>
        </div>

        <?php
        $nav = $appNavs->item(1);
        $cfgSession = new SecretObject($appConfig->getSessions());
        ?>
        <div id="<?php echo $nav->getKey(); ?>" class="tab-pane fade<?php echo $nav->getActive() ? $constShowActive : ''; ?>" role="tabpanel" aria-labelledby="<?php echo $nav->getKey(); ?>-tab">

          <div class="main-menu">
            <button type="button" class="btn btn-primary create-new-application" data-toggle="modal" data-target="#modal-create-application">Create New</button>
            <button type="button" class="btn btn-primary refresh-application-list">Refresh</button>
          </div>

          <div class="container-fluid application-container">
            <div class="row application-card">
            
            </div>
          </div>  
        </div>

        <?php
        $nav = $appNavs->item(2);
        ?>
        <div id="<?php echo $nav->getKey(); ?>" class="tab-pane fade<?php echo $nav->getActive() ? $constShowActive : ''; ?>" role="tabpanel" aria-labelledby="<?php echo $nav->getKey(); ?>-tab">

          <form name="formdatabase" id="formdatabase" method="post" action="" class="config-table">
            <div class="card">
              <div id="collapse4" class="collapse show" aria-labelledby="heading4" data-parent="#accordion">
                <div class="card-body">
                  <h4>Entity</h4>
                  <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                      <tr>
                        <td>Table</td>
                        <td><select class="form-control" name="source_table"></select>
                        </td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>
                          <button class="btn btn-primary" type="button" name="load_table" id="load_table">Reload Table</button>
                        </td>
                      </tr>
                      <tr>
                        <td>Master Entity Name</td>
                        <td><input class="form-control" type="text" name="entity_master_name" id="entity_master_name"></td>
                      </tr>
                      <tr>
                        <td>Master Primary Key</td>
                        <td><input class="form-control" type="text" name="primary_key_master" id="primary_key_master"></td>
                      </tr>
                      <tr>
                        <td>Approval Table Name</td>
                        <td><input class="form-control" type="text" name="table_approval_name" id="table_approval_name"></td>
                      </tr>
                      <tr>
                        <td>Approval Primary Key</td>
                        <td><input class="form-control" type="text" name="primary_key_approval" id="primary_key_approval"></td>
                      </tr>
                      <tr>
                        <td>Approval Entity Name</td>
                        <td><input class="form-control" type="text" name="entity_approval_name" id="entity_approval_name"></td>
                      </tr>
                      <tr>
                        <td>Trash Table Name</td>
                        <td><input class="form-control" type="text" name="table_trash_name" id="table_trash_name"></td>
                      </tr>
                      <tr>
                        <td>Trash Primary Key</td>
                        <td><input class="form-control" type="text" name="primary_key_trash" id="primary_key_trash"></td>
                      </tr>
                      <tr>
                        <td>Trash Entity Name</td>
                        <td><input class="form-control" type="text" name="entity_trash_name" id="entity_trash_name"></td>
                      </tr>
                    </tbody>
                  </table>

                  <h4>Module Option</h4>
                  <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                      <tr>
                        <td>Target</td>
                        <td>
                          <select class="form-control" name="current_module_location" id="current_module_location">
                          </select>  
                        </td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td>
                          <button class="btn btn-primary" type="button" name="update_current_location" id="update_current_location">Update Current Location</button>
                          <button class="btn btn-primary" type="button" name="update_module_location" id="update_module_location" data-toggle="modal" data-target="#modal-update-path">Manage</button>
                        </td>
                      </tr>
                      <tr>
                        <td>Update Entity</td>
                        <td><label for=""><input type="checkbox" name="update_entity"> Yes</label></td>
                      </tr>
                    </tbody>
                  </table>

                  <h4>Module</h4>
                  <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                      <tr>
                        <td>File Name</td>
                        <td><input class="form-control" type="text" name="module_file" id="module_file"></td>
                      </tr>
                      <tr>
                        <td>Module Code</td>
                        <td><input class="form-control" type="text" name="module_code" id="module_code"></td>
                      </tr>
                      <tr>
                        <td>Module Name</td>
                        <td><input class="form-control" type="text" name="module_name" id="module_name"></td>
                      </tr>
                      <tr>
                        <td>Load Saved Module</td>
                        <td><label for=""><input type="checkbox" name="module_load_previous" value="1" checked> Yes</label></td>
                      </tr>
                      <tr>
                        <td>Add To Module List</td>
                        <td><label for=""><input type="checkbox" name="module_add_to_list" value="1"> Yes</label></td>
                      </tr>
                      <tr>
                        <td>Special Access</td>
                        <td><label for=""><input type="checkbox" name="module_special_access" value="1"> Yes</label></td>
                      </tr>
                      <tr>
                        <td>Add Menu</td>
                        <td><label for=""><input type="checkbox" name="module_as_menu" value="1" checkdate> Yes</label></td>
                      </tr>
                      <tr>
                        <td>Append To</td>
                        <td><select class="form-control" name="module_menu"></select></td>
                      </tr>
                    </tbody>
                  </table>

                  <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                      <tr>
                        <td></td>
                        <td><input class="btn btn-success" type="button" name="load_column" id="load_column" value="Load Column"></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </form>
        </div>
        <?php
        $nav = $appNavs->item(3);
        ?>
        <div id="<?php echo $nav->getKey(); ?>" class="tab-pane fade<?php echo $nav->getActive() ? $constShowActive : ''; ?>" role="tabpanel" aria-labelledby="<?php echo $nav->getKey(); ?>-tab">
          <div class="define-wrapper">
            <form name="formgenerator" id="formgenerator" method="post" action="">
              <table width="100%" border="1" cellspacing="0" cellpadding="0" class="main-table">
                <thead>
                  <tr>
                    <td rowspan="2" class="data-sort data-sort-header"></td>
                    <td rowspan="2" align="center">Field</td>
                    <td rowspan="2" align="center">Caption</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Insert">I</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Update">U</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Detail">D</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="List">L</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Export">E</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Primary Key">K</td>
                    <td rowspan="2" align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Required">R</td>
                    <td colspan="5" align="center">Element Type</td>
                    <td colspan="3" align="center" width="60">Search</td>
                    <td rowspan="2" align="center" width="100">Data Type</td>
                    <td rowspan="2" align="center" width="180">Filter Type</td>
                  </tr>
                  <tr>
                    <td align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Text">TE</td>
                    <td align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Textarea">TA</td>
                    <td align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Checkbox">CB</td>
                    <td align="center" width="32" data-toggle="tooltip" data-placement="top" data-title="Select">SE</td>
                    <td align="center" width="74" data-toggle="tooltip" data-placement="top" data-title="Select">Source</td>
                    <td align="center" width="30" data-toggle="tooltip" data-placement="top" data-title="Text">TE</td>
                    <td align="center" width="30" data-toggle="tooltip" data-placement="top" data-title="Select">SE</td>
                    <td align="center" width="74" data-toggle="tooltip" data-placement="top" data-title="Select">Source</td>
                  </tr>
                </thead>

                <tbody class="data-table-manual-sort">
                </tbody>
              </table>
              <div class="button-area">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-filter-data">
                  Data Filter
                </button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-order-data">
                  Data Order
                </button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-module-features">
                  Module Features
                </button>
                <input class="btn btn-success" type="button" name="generate_script" id="generate_script" value="Generate Script"> &nbsp;

                <div class="modal fade" id="modal-filter-data" tabindex="-1" aria-labelledby="modal_filter_label" aria-hidden="true">
                  <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Data Filter</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="table-reference-container">
                        <table class="table table-reference table-data-filter" width="100%" border="0" cellspacing="0" cellpadding="0" data-empty-on-remove="true">
                          <thead>
                            <tr>
                              <td width="40%">Column</td>
                              <td>Value</td>
                              <td width="42">Rem</td>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><input class="form-control data-filter-column-name" type="text" value=""></td>
                              <td><input class="form-control data-filter-column-value" type="text" value=""></td>
                              <td><button type="button" class="btn btn-danger btn-remove-row"><i class="fa-regular fa-trash-can"></i></button></td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="3"><button type="button" class="btn btn-primary btn-add-row">Add Row</button></td>
                            </tr>
                          </tfoot>
                        </table>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="modal fade" id="modal-order-data" tabindex="-1" aria-labelledby="modal_order_label" aria-hidden="true">
                  <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Data Order</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                      <div class="table-reference-container">
                        <table class="table table-reference table-data-order" width="100%" border="0" cellspacing="0" cellpadding="0" data-empty-on-remove="true">
                          <thead>
                            <tr>
                              <td width="65%">Column</td>
                              <td>Value</td>
                              <td width="42">Rem</td>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><input class="form-control data-order-column-name" type="text" value=""></td>
                              <td><select class="form-control data-order-order-type">
                                  <option value="PicoSort::ORDER_TYPE_ASC">ASC</option>
                                  <option value="PicoSort::ORDER_TYPE_DESC">DESC</option>
                                </select></td>
                              <td><button type="button" class="btn btn-danger btn-remove-row"><i class="fa-regular fa-trash-can"></i></button></td>
                            </tr>
                          </tbody>
                          <tfoot>
                            <tr>
                              <td colspan="3"><button type="button" class="btn btn-primary btn-add-row">Add Row</button></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="modal fade" id="modal-module-features" tabindex="-1" aria-labelledby="modal_features_label" aria-hidden="true">
                  <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Module Features</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tbody>
                            <tr>
                              <td>Activate/Decativate</td>
                              <td><label><input type="checkbox" name="activate_deactivate" id="activate_deactivate" value="1"> Activate/Decativate</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Manual Sort Order</td>
                              <td><label><input type="checkbox" name="manualsortorder" id="manualsortorder" value="1"> Manual Sort Order</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Export to Excel</td>
                              <td><label><input type="checkbox" name="export_to_excel" id="export_to_excel" value="1"> Export to Excel</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Export to CSV</td>
                              <td><label><input type="checkbox" name="export_to_csv" id="export_to_csv" value="1"> Export to CSV</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Use Temporary File</td>
                              <td><label><input type="checkbox" name="export_use_temporary" id="export_use_temporary" value="1" disabled> Use Temporary File</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Approval</td>
                              <td><label><input type="checkbox" name="with_approval" id="with_approval" value="1"> Approval</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Approval Note</td>
                              <td><label><input type="checkbox" name="with_approval_note" id="with_approval_note" value="1"> Approval Note</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Approval Type</td>
                              <td><label><input type="radio" name="approval_type" id="approval_type" value="1"> Separated</label> &nbsp; <label><input type="radio" name="approval_type" id="approval_type" value="2" checked> Combined</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Approval Position</td>
                              <td><label><input type="radio" name="approval_position" id="approval_position" value="before-data"> Before Data</label> &nbsp; <label><input type="radio" name="approval_position" id="approval_position" value="after-data" checked> After Data</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Approval by Another User</td>
                              <td><label><input type="checkbox" name="approval_by_other_user" id="approval_by_other_user" value="1"> Approval by Another User</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Trash</td>
                              <td><label><input type="checkbox" name="with_trash" id="with_trash" value="1"> Trash</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>AJAX Support</td>
                              <td><label><input type="checkbox" name="ajax_support" id="ajax_support" value="1"> AJAX Support</label> &nbsp;</td>
                            </tr>
                            <tr>
                              <td>Subquery</td>
                              <td><label><input type="checkbox" name="subquery" id="subquery" value="1"> Subquery</label> &nbsp;</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div id="module-file" class="tab-pane fade" role="tabpanel" aria-labelledby="module-file-tab">
          <div class="module-container">
            <div class="row">
              <div class="col col-2">
                <div class="column-title">
                  <h4>Modules</h4>
                </div>
                <div class="column-body">
                  <div class="module-list module-list-file"></div>
                </div>
              </div>
              <div class="col col-10">
                <div class="column-title">
                  <h4>Content</h4>
                </div>
                <div class="column-body">
                  <textarea class="module-file app-code" spellcheck="false" contenteditable="true"></textarea>
                  <div class="button-area">
                    <button class="btn btn-success" id="button_save_module_file" type="button" disabled>Save Module</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="entity-file" class="tab-pane fade" role="tabpanel" aria-labelledby="entity-file-tab">
          <div class="entity-container-file">
            <div class="row">
              <div class="col col-2">
                <div class="column-title">
                  <h4>Entities</h4>
                </div>
                <div class="column-body">
                  <div class="entity-list"></div>
                </div>
              </div>
              <div class="col col-10">
                <div class="column-title">
                  <h4>Content</h4>
                </div>
                <div class="column-body">
                  <textarea class="entity-file app-code" spellcheck="false" contenteditable="true"></textarea>
                  <div class="button-area">
                    <button class="btn btn-success" id="button_save_entity_file" type="button" disabled>Save Entity</button>
                    <button class="btn btn-success" id="button_save_entity_file_as" type="button">Save Entity As</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="entity-relationship" class="tab-pane fade" role="tabpanel" aria-labelledby="entity-relationship-tab">
          <div class="entity-container-relationship">
            <div class="row">
              <div class="col col-2">
                <div class="column-title">
                  <h4>Entities</h4>
                </div>
                <div class="column-body">
                  <div class="entity-list"></div>
                </div>
              </div>
              <div class="col col-10">
                <div class="column-title">
                  <h4>Entity Relationship Diagram</h4>
                </div>
                <div class="column-body">
                  <div class="diagram-option">
                    Max Level <input class="form-control" type="number" step="1" name="maximum_level" value="0" min="0" max="20">
                    Max Column <input class="form-control" type="number" step="1" name="maximum_column" value="8" min="4">
                    Margin X <input class="form-control" type="number" name="margin_x" value="0" min="0">
                    Margin Y <input class="form-control" type="number" name="margin_y" value="0" min="0">
                    H Space <input class="form-control" type="number" name="entity_margin_x" value="40" min="0">
                    V Space <input class="form-control" type="number" name="entity_margin_y" value="20" min="0">
                    Zoom <input class="form-control" type="number" step="0.05" name="zoom" value="1" min="0.5">
                    <button type="button" class="btn btn-primary reload-diagram">Reload</button>
                    <button type="button" class="btn btn-primary download-svg" onclick="downloadSVG()">SVG</button>
                    <button type="button" class="btn btn-primary download-png" onclick="downloadPNG()">PNG</button>
                  </div>
                  <div class="erd-image"></div>
                  <map name="erd-map"></map>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div id="entity-query" class="tab-pane fade" role="tabpanel" aria-labelledby="entity-query-tab">
          <div class="entity-container-query">
            <div class="row">
              <div class="col col-2">
                <div class="column-title">
                  <h4>Entities</h4>
                </div>
                <div class="column-body">
                  <div class="entity-list"></div>
                </div>
              </div>
              <div class="col col-10">
                <div class="column-title">
                  <h4>Query</h4>
                </div>
                <div class="column-body">
                  <textarea class="entity-query app-code" spellcheck="false" contenteditable="true"></textarea>
                  <div class="button-area">
                    <button class="btn btn-success" id="button_save_entity_query" type="button" disabled>Save Query</button>
                    <button class="btn btn-primary" id="button_execute_entity_query" type="button">Execute Query</button>
                    <button class="btn btn-primary" id="button_explore_database" type="button">Explore Database</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="translate-entity" class="tab-pane fade" role="tabpanel" aria-labelledby="translate-entity-tab">
          <div class="container-translate-entity">
            <div class="row">
              <div class="col col-2">
                <div class="column-title">
                  <h4>Entities</h4>
                </div>
                <div class="column-body">
                  <div class="entity-list"></div>
                </div>
              </div>
              <div class="col col-10">
                <div class="column-title"> 
                  Target Language
                  <select class="form-control target-language" data-translate-for="entity">
                  </select>
                  Filter
                  <select class="form-control filter-translate" data-translate-for="entity">
                    <option value="untranslated">Untranslated</option>
                    <option value="all">All</option>
                  </select>
                  <button class="btn btn-primary default-language" type="button">Default Language</button>
                  <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-update-language">Manage</button>
                </div>
                <div class="column-body">
                  <div class="row">
                    <div class="col-6"><textarea class="entity-translate-original app-code" spellcheck="false" contenteditable="true"></textarea></div>
                    <div class="col-6"><textarea class="entity-translate-target app-code" spellcheck="false" contenteditable="true"></textarea></div>
                  </div>
                  <div class="button-area">
                    <input type="hidden" class="entity-property-name" value="">
                    <input type="hidden" class="entity-name" value="">
                    <button class="btn btn-success" id="button-save-entity-translation" type="button">Save Translation</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="translate-application" class="tab-pane fade" role="tabpanel" aria-labelledby="translate-application-tab">
        <div class="container-translate-module">
            <div class="row">
              <div class="col col-2">
                <div class="column-title">
                  <h4>Modules</h4>
                </div>
                <div class="column-body">
                  <div class="module-list module-list-translate"></div>
                </div>
              </div>
              <div class="col col-10">
                <div class="column-title"> 
                  Target Language
                  <select class="form-control target-language" data-translate-for="module">
                  </select>
                  Filter
                  <select class="form-control filter-translate" data-translate-for="module">
                    <option value="untranslated">Untranslated</option>
                    <option value="all">All</option>
                  </select>
                  <button class="btn btn-primary default-language" type="button">Default Language</button>
                  <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-update-language">Manage</button>
                </div>
                <div class="column-body">
                  <div class="row">
                    <div class="col-6"><textarea class="module-translate-original app-code" spellcheck="false" contenteditable="true"></textarea></div>
                    <div class="col-6"><textarea class="module-translate-target app-code" spellcheck="false" contenteditable="true"></textarea></div>
                  </div>
                  <div class="button-area">
                    <input type="hidden" class="module-property-name" value="">
                    <button class="btn btn-success" id="button-save-module-translation" type="button">Save Translation</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="docs" class="tab-pane fade" role="tabpanel" aria-labelledby="docs-tab">
          <div class="desc">
            <h4>Column Description</h4>
            <ol>
              <li><strong>Field</strong><br />
                Field is the column name of the table
              </li>
              <li><strong>Caption</strong><br />
                Caption is label of associated column
              </li>
              <li><strong>I</strong><br />
                If the field will included on &quot;insert&quot; section, please check this
              </li>
              <li><strong>U</strong><br />
                If the field will included on &quot;update&quot; section, please check this </li>
              <li><strong>D</strong><br />
                If the field will included on &quot;detail&quot; section, please check this </li>
              <li><strong>L</strong><br />
                If the field will included on &quot;list&quot; section, please check this </li>
              <li><strong>K</strong><br />
                Is the field is primary key of the table, please check this. Each modul must have one key for control data
              </li>
              <li><strong>R</strong><br />
                If input is mandatory to filed, please check this
              </li>
              <li><strong>TE</strong><br />
                Input type is &lt;input type=&quot;text&quot;&gt;, &lt;input type=&quot;email&quot;&gt;, &lt;input type=&quot;tel&quot;&gt;, &lt;input type=&quot;password&quot;&gt;, &lt;input type=&quot;number&quot;&gt;, or &lt;input type=&quot;number&quot; step=&quot;any&quot;&gt; according to data type</li>
              <li><strong>TA</strong><br />
                Input type is &lt;textarea&gt;&lt;/textarea&gt;
              </li>
              <li><strong>SE</strong><br />
                Input type is &lt;select&gt;&lt;option value&quot;&quot;&gt;&lt;/option&gt;&lt;/select&gt;
              </li>
              <li><strong>CB</strong><br />
                Input type is &lt;input type=&quot;checkbox&quot;&gt;
              </li>
              <li><strong>Data Type</strong><br />
                Data type for &lt;input&gt;
              </li>
              <li><strong>Filter Type</strong><br />
                Filter type for input sent to server</li>
            </ol>
            <p>&lt;input type=&quot;date&quot;&gt;, &lt;input type=&quot;datetime&quot;&gt;, &lt;input type=&quot;time&quot;&gt; and &lt;input type=&quot;color&quot;&gt; are not applied on this generator because not all browsers support these input type. You can use JavaScript library for its.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="modal fade" id="modal-create-application" tabindex="-1" aria-labelledby="application_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create New Application</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="">
            <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td>Application Name</td>
                  <td><input class="form-control" type="text" name="application_name"></td>
                </tr>
                <tr>
                  <td>Application ID</td>
                  <td><input class="form-control" type="text" name="application_id"></td>
                </tr>
                <tr>
                  <td>Architecture</td>
                  <td>
                      <select class="form-control" name="application_architecture">
                          <option value="monolith">Monolith Application</option>
                          <option value="microservices">Microservices Aplication</option>
                      </select>
                  </td>
                </tr>
                <tr>
                  <td>Description</td>
                  <td><textarea class="form-control" name="application_description" spellcheck="false"></textarea></td>
                </tr>
                <tr>
                  <td>MagicApp Version</td>
                  <td>
                      <select class="form-control" name="magic_app_version">
                          
                      </select>
                  </td>
                </tr>
                <tr>
                  <td>Application Directory</td>
                  <td><input class="form-control" type="text" name="application_directory"></td>
                </tr>
                <tr>
                  <td>Base Namespace</td>
                  <td><input class="form-control" type="text" name="application_namespace"></td>
                </tr>
                <tr>
                  <td>Author</td>
                  <td><input class="form-control" type="text" name="application_author"></td>
                </tr>
                
                <tr>
                  <td>Path</td>
                  <td class="paths">
                    <table class="path-manager">
                      <thead>
                        <tr>
                          <td>Name</td>
                          <td>Path</td>
                          <td width="20"></td>
                          <td width="35"></td>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><input class="form-control" type="text" name="name[0]" value="root"></td>
                          <td><input class="form-control" type="text" name="path[0]" value="/"></td>
                          <td><input type="checkbox" name="checked[0]" checked></td>
                          <td><button type="button" class="btn btn-danger path-remover"><i class="fa-regular fa-trash-can"></i></button></td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="4">
                          <button type="button" class="btn btn-primary add-path">Add</button>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="create_new_app">Create</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-create-reference-data" tabindex="-1" aria-labelledby="reference_data_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create Data Reference</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="load_from_cache">Load</button>
          <button type="button" class="btn btn-primary" id="save_to_cache">Save</button>
          <button type="button" class="btn btn-success" id="apply_reference">OK</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="add-language-dialog" tabindex="-1" aria-labelledby="add-language-dialog-label" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Language</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">            
             <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td>Language</td>
                  <td><input class="form-control" type="text" name="language_name"></td>
                </tr>
                <tr>
                  <td>Code</td>
                  <td><input class="form-control" type="text" name="language_code"></td>
                </tr>
              </tbody>
            </table>      
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success add-new-language">Add</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-update-path" tabindex="-1" aria-labelledby="application_path" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Module Location Manager</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="">
            <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td>Path</td>
                  <td class="paths">
                    <table class="path-manager">
                      <thead>
                        <tr>
                          <td>Name</td>
                          <td>Path</td>
                          <td width="20"></td>
                          <td width="35"></td>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><input class="form-control location-name" type="text" name="name[0]" value=""></td>
                          <td><input class="form-control location-path" type="text" name="path[0]" value=""></td>
                          <td><input type="checkbox" name="checked[0]" checked></td>
                          <td><button type="button" class="btn btn-danger path-remover"><i class="fa-regular fa-trash-can"></i></button></td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="4">
                          <button type="button" class="btn btn-primary add-path">Add</button>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="update_module_path">Update</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-update-language" tabindex="-1" aria-labelledby="application_language" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Application Language Manager</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="">
            <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td>Language</td>
                  <td class="languages">
                    <table class="language-manager">
                      <thead>
                        <tr>
                          <td>Name</td>
                          <td>Code</td>
                          <td width="20"></td>
                          <td width="35"></td>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td><input class="form-control language-name" type="text" name="language_name[0]" value=""></td>
                          <td><input class="form-control language-code" type="text" name="language_code[0]" value=""></td>
                          <td><input type="checkbox" name="checked[0]" checked></td>
                          <td><button type="button" class="btn btn-danger language-remover"><i class="fa-regular fa-trash-can"></i></button></td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="4">
                          <button type="button" class="btn btn-primary add-language">Add</button>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="update-application-language">Update</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-entity-detail" tabindex="-1" aria-labelledby="application_entity" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Entity Detail</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="entity-detail"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-application-setting" tabindex="-1" aria-labelledby="application_setting" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Application Setting</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="application-setting"></div>
        </div>
        <div class="modal-footer">
          <input class="btn btn-success button-save-application-config" type="button" name="button-save-application-config" value="Save">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-workspace" tabindex="-1" aria-labelledby="application_workspace" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Workspace</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="">
            <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td>Workspace</td>
                  <td><input class="form-control" type="text" name="workspace" value="<?php echo $builderConfig->getWorkspaceDirectory();?>" autocomplete="off"></td>
                </tr>
                <tr>
                  <td>PHP Path</td>
                  <td><input class="form-control" type="text" name="php_path" value="<?php echo $builderConfig->getPhpPath();?>" autocomplete="off"></td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
          <input class="btn btn-success button-save-workspace" type="button" name="button-save-workspace" value="Save">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  
  <div class="modal fade" id="modal-application-menu" tabindex="-1" aria-labelledby="application_menu" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Application Menu</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary button-add-menu">Add</button>
          <button type="button" class="btn btn-success button-save-menu">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-application-menu-add" tabindex="-1" aria-labelledby="application_menu_add" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Menu</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="text" name="new_menu" id="new_menu" class="form-control" autocomplete="off">       
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success button-apply-new-menu">OK</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  
  <div class="modal fade" id="modal-database-explorer" tabindex="-1" aria-labelledby="database_exsplorer" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Database Explorer</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="database-explorer">
            
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="modal fade" id="modal-query-executor" tabindex="-1" aria-labelledby="application_query_executor" aria-hidden="true" >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Query Executor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="query-container">
            <textarea name="query_to_execute" id="query_to_execute" spellcheck="false"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success button-execute-query">Execute</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="alert-dialog" tabindex="-1" aria-labelledby="alert-dialog-label" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Alert</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Overlay background (optional, will cover other elements when modal appears) -->
  <div class="alertOverlay" id="alertOverlay"></div>
  <!-- Modal Structure -->
  <div class="modal fade" id="customAlert" tabindex="-1" role="dialog" aria-labelledby="alertTitle"
      aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="alertTitle">Confirmation</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p id="alertMessage">This is the alert message.</p>
              </div>
              <div class="modal-footer" id="modalFooter">
                  <!-- Buttons will be dynamically added here -->
              </div>
          </div>
      </div>
  </div>

</body>
</html>