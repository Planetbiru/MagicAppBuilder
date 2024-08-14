let initielized = false;
let currentEntity = "";
let currentModule = "";

String.prototype.replaceAll = function (search, replacement) {
  let target = this;
  return target.replace(new RegExp(search, "g"), replacement);
};

function upperCamelize(input) {
  return input
    .replaceAll("_", " ")
    .capitalize()
    .prettify()
    .replaceAll(" ", "")
    .trim();
}

function hasKey(defdata, key) {
  let len = defdata.length;
  let i;
  for (i = 0; i < len; i++) {
    if (defdata[i].value == key) {
      return true;
    }
  }
  return false;
}

function loadState(defdata, frm1, frm2) {
  let i;
  frm2.find("tbody tr").each(function (index, e) {
    let tr = $(this);
    let field = tr.find('input[type="hidden"][name="field"]').val();
    if (hasKey(defdata, field)) {
      $(frm2)
        .find('input[name$="' + field + '"]')
        .each(function (index2, e2) {
          $(this)[0].checked = false;
        });
    }
  });

  for (i in defdata) {
    let obj = $(frm2).find(":input[name=" + defdata[i]["name"] + "]");
    if (obj.length) {
      let val = defdata[i]["value"];
      let name = defdata[i]["name"];
      let tagName = obj.prop("tagName").toString().toLowerCase();
      let type = obj.attr("type");

      if (type == "radio") {
        $(frm2).find(
          '[name="' + name + '"][value="' + val + '"]'
        )[0].checked = true;
      } else if (type == "checkbox" && val != null && val != 0 && val != "0") {
        $(frm2).find('[name="' + name + '"]')[0].checked = true;
      } else if (tagName == "select") {
        obj.val(defdata[i]["value"]);
      }
    }
  }
}

function getReferenceResource() {
  return `
	<form action="">
	<label for="reference_type_entity"><input type="radio" class="reference_type" name="reference_type" id="reference_type_entity" value="entity" checked> Entity</label>
	<label for="reference_type_map"><input type="radio" class="reference_type" name="reference_type" id="reference_type_map" value="map"> Map</label>
	<label for="reference_type_yesno"><input type="radio" class="reference_type" name="reference_type" id="reference_type_yesno" value="yesno"> Yes/No</label>
	<label for="reference_type_truefalse"><input type="radio" class="reference_type" name="reference_type" id="reference_type_truefalse" value="truefalse"> True/False</label>
	<label for="reference_type_onezero"><input type="radio" class="reference_type" name="reference_type" id="reference_type_onezero" value="onezero"> 1/0</label>
	<div class="reference-container">
	  <div class="reference-section entity-section">
		<h4>Entity</h4>
		<table data-name="entity" class="modal-table" width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tbody>
			<tr>
			  <td>Entity Name</td>
			  <td><input class="form-control rd-entity-name" type="text"></td>
			</tr>
			<tr>
			  <td>Table Name</td>
			  <td><input class="form-control rd-table-name" type="text"></td>
			</tr>
			<tr>
			  <td>Primary Key</td>
			  <td><input class="form-control rd-primary-key" type="text"></td>
			</tr>
			<tr>
			  <td>Value Column</td>
			  <td><input class="form-control rd-value-column" type="text"></td>
			</tr>
			<tr class="display-reference">
			  <td>Reference Object Name</td>
			  <td><input class="form-control rd-reference-object-name" type="text"></td>
			</tr>
			<tr class="display-reference">
			  <td>Reference Property Name</td>
			  <td><input class="form-control rd-reference-property-name" type="text"></td>
			</tr>
			<tr class="entity-generator">
			  <td></td>
			  <td><button type="button" class="btn btn-primary generate-entity">Generate Entity</button></td>
			</tr>
		  </tbody>
		</table>
		<h4>Option Node</h4>
		<table data-name="entity" class="modal-table" width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tbody>
		  <tr>
		  <td>Format and Pamareters</td>
		  <td><input class="form-control rd-option-text-node-format" type="text"></td>
		  </tr>
		  <tr>
		  <td>Indent</td>
		  <td><input class="form-control rd-option-indent" type="number" step="1" min="0"></td>
		  </tr>
		</tbody>
		  </table>
		<h4>Specfification</h4>
		<p>Just leave it blank if it doesn't exist. Click Remove button to remove value.</p>
		<table data-name="specification" class="table table-reference" data-empty-on-remove="true">
		  <thead>
			<tr>
			  <td width="45%">Column Name</td>
			  <td>Value</td>
			  <td width="70">Remove</td>
			</tr>
		  </thead>
			  <tbody>
			  <tr>
				<td><input class="form-control rd-column-name" type="text" value=""></td>
				<td><input class="form-control rd-value" type="text" value=""></td>
				<td><button type="button" class="btn btn-danger btn-remove-row">Remove</button></td>
			  </tr>
			</tbody>
			<tfoot>
			  <tr>
				<td colspan="3">
				  <button type="button" class="btn btn-primary btn-add-row">Add Row</button>
				</td>
			  </tr>
			</tfoot>
		</table>
		<h4>Sortable</h4>
		<p>Use at least one column to sort.</p>
		<table data-name="sortable" class="table table-reference">
		  <thead>
			<tr>
			  <td width="65%">Column</td>
			  <td>Value</td>
			  <td width="70">Remove</td>
			</tr>
		  </thead>
			  <tbody>
			  <tr>
				<td><input class="form-control rd-column-name" type="text" value=""></td>
				<td><select class="form-control rd-order-type">
				  <option value="PicoSort::ORDER_TYPE_ASC">ASC</option>
				  <option value="PicoSort::ORDER_TYPE_DESC">DESC</option>
				</select></td>
				<td><button type="button" class="btn btn-danger btn-remove-row">Remove</button></td>
			  </tr>
			</tbody>
			<tfoot>
			  <tr>
				<td colspan="3"><button type="button" class="btn btn-primary btn-add-row">Add Row</button></td>
			  </tr>
			</tfoot>
		</table>
		<h4>Additional Output</h4>
		<p>Just leave it blank if it doesn't exist. Click Remove button to remove value.</p>
		<table data-name="additional-output" class="table table-reference" data-empty-on-remove="true">
		  <thead>
			<tr>
			  <td>Column</td>
			  <td width="70">Remove</td>
			</tr>
		  </thead>
			  <tbody>
			  <tr>
				<td><input class="form-control rd-column-name" type="text" value=""></td>
				<td><button type="button" class="btn btn-danger btn-remove-row">Remove</button></td>
			  </tr>
			</tbody>
			<tfoot>
			  <tr>
				<td colspan="3"><button type="button" class="btn btn-primary btn-add-row">Add Row</button></td>
			  </tr>
			</tfoot>
		</table>
	  </div>
	  <div class="reference-section map-section">
		<h4>Map</h4>
		<table data-name="map" class="table table-reference" data-offset="2">
		  <thead>
			<tr>
			  <td>Value</td>
			  <td>Label</td>
			  <td><input class="form-control map-key" type="text" value="" placeholder="Additional attribute name"></td>
			  <td>Def</td>
			  <td width="70">Remove</td>
			</tr>
		  </thead>
			  <tbody>
			  <tr>
				<td><input class="form-control rd-value" type="text" value=""></td>
				<td><input class="form-control rd-label" type="text" value=""></td>
				<td><input class="form-control map-value" type="text" value="" placeholder="Additional attribute value"></td>
				<td><input type="checkbox" class="rd-selected"></td>
				<td><button type="button" class="btn btn-danger btn-remove-row">Remove</button></td>
			  </tr>
			</tbody>
			<tfoot>
			  <tr>
				<td colspan="5">
				  <button type="button" class="btn btn-primary btn-add-row">Add Row</button>
				  <button type="button" class="btn btn-primary btn-add-column">Add Column</button>
				  <button type="button" class="btn btn-primary btn-remove-last-column">Remove Last Column</button>
				</td>
			  </tr>
			</tfoot>
		</table>
	  </div>
	</div>
  </form>
  `;
}

jQuery(function(){
  $(document).on('click', '#vscode', function(){
    let dir = $('#current_application option:selected').attr('data-directory');
    let lnk = 'vscode://file/'+dir;
    window.location = lnk;
  });

  $(document).on("click", "#load_table", function (e) {
    e.preventDefault();
    loadTable();
  });
  $(document).on("click", "#load_column", function (e) {
    e.preventDefault();
    let tableName = $('[name="source_table"]').val();
    let selector = $("table.main-table tbody");
    loadColumn(tableName, selector);
  });
  $(document).on("change", 'select[name="source_table"]', function (e) {
    let masterTableName = $(this).val();
    let moduleFileName = masterTableName + ".php";
    let moduleCode = masterTableName;
    let moduleName = masterTableName;
    let masterPrimaryKeyName =
      $(this).find("option:selected").attr("data-primary-key") || "";
    updateTableName(
      moduleFileName,
      moduleCode,
      moduleName,
      masterTableName,
      masterPrimaryKeyName
    );
  });
  $(document).on("click", "#save_application_config", function (e) {
    e.preventDefault();
    let frm = $(this).closest("form");
    let inputs = $(this).closest("form").serializeArray();
    let current_application = frm.find('[name="current_application"]').val();

    let dataToPost = {
      current_application: current_application,
      database: {},
      sessions: {},
      entity_info: {},
    };

    for (let i in inputs) {
      let name = inputs[i].name;
      if (name.indexOf("database_") !== -1) {
        dataToPost.database[name.substring(9)] = inputs[i].value;
      }
      if (name.indexOf("sessions_") !== -1) {
        dataToPost.sessions[name.substring(9)] = inputs[i].value;
      }
      if (name.indexOf("entity_info_") !== -1) {
        dataToPost.entity_info[name.substring(12)] = inputs[i].value;
      }
    }
    updateCurrentApplivation(dataToPost);
  });

  $(document).on("click", "#generate-script", function (e) {
    e.preventDefault();
    generateScript($(".main-table tbody"));
  });

  $(document).on("click", "#switch-application", function (e) {
    e.preventDefault();
    switchApplication($("#current_application").val());
  });

  $(document).on("change", ".input-field-filter", function (e) {
    let checked = $(this)[0].checked;
    let value = $(this).attr("value");
    if (checked) {
      let parentObj = $(this).closest("tr");
      parentObj.find(
        '.input-field-filter[value!="' + value + '"]'
      )[0].checked = false;
      prepareReferenceFilter(value, $(this));
    } else {
      let tr = $(this).closest("tr");
      tr.find(".reference-button-filter").css("display", "none");
    }
  });

  $(document).on("change", ".input-element-type", function (e) {
    let checkedValue = $(this).attr("value");
    prepareReferenceData(checkedValue, $(this));
  });

  $(document).on("click", ".reference-button-data", function (e) {
    e.preventDefault();
    $("#modal-create-reference-data")
      .find(".modal-title")
      .text("Create Data Reference");
    $("#modal-create-reference-data").attr("data-reference-type", "data");
    let parentTd = $(this).closest("td");
    let parentTr = $(this).closest("tr");
    let fieldName = parentTr.attr("data-field-name");
    let key = $(this).siblings("input").attr("name");

    $("#modal-create-reference-data").attr("data-input-name", key);
    $("#modal-create-reference-data").attr("data-field-name", fieldName);
    $("#modal-create-reference-data").find(".modal-body").empty();
    $("#modal-create-reference-data")
      .find(".modal-body")
      .append(getReferenceResource());

    let value = $('[name="' + key + '"]').val();
    if (value.length < 60) {
      loadReference(fieldName, key, function (obj) {
        if (obj != null) {
          deserializeForm(obj);
        }
      });
    }
    if (value != "") {
      let obj = parseJsonData(value);
      if (typeof obj != 'object') {
        obj = {};
      }
      deserializeForm(obj);
    }
    $("#modal-create-reference-data").modal("show");
  });

  $(document).on("click", ".reference-button-filter", function (e) {
    e.preventDefault();
    $("#modal-create-reference-data")
      .find(".modal-title")
      .text("Create Filter Reference");
    $("#modal-create-reference-data").attr("data-reference-type", "filter");

    let parentTr = $(this).closest("tr");
    let fieldName = parentTr.attr("data-field-name");
    let key = $(this).siblings("input").attr("name");

    $("#modal-create-reference-data").attr("data-input-name", key);
    $("#modal-create-reference-data").attr("data-field-name", fieldName);
    $("#modal-create-reference-data").find(".modal-body").empty();
    $("#modal-create-reference-data").find(".modal-body").append(getReferenceResource());

    let value = $('[name="' + key + '"]')
      .val()
      .trim();
    if (value.length < 60) {
      loadReference(fieldName, key, function (obj) {
        if (obj != null) {
          deserializeForm(obj);
        }
      });
    }
    if (value != "") {
      let obj = parseJsonData(value);
      if (typeof obj != 'object') {
        obj = {};
      }
      deserializeForm(obj);
    }
    $("#modal-create-reference-data").modal("show");
  });

  $(document).on("click", "#apply-reference", function (e) {
    let key = $("#modal-create-reference-data").attr("data-input-name");
    let value = JSON.stringify(serializeForm());
    $('[name="' + key + '"]').val(value);
    $("#modal-create-reference-data").modal("hide");
  });
  $(document).on("click", "#save-to-cache", function (e) {
    let fieldName = $("#modal-create-reference-data").attr("data-field-name");
    let key = $("#modal-create-reference-data").attr("data-input-name");
    let value = JSON.stringify(serializeForm());
    saveReference(fieldName, key, value);
  });

  $(document).on("click", "#load-from-cache", function (e) {
    let fieldName = $("#modal-create-reference-data").attr("data-field-name");
    let key = $("#modal-create-reference-data").attr("data-input-name");
    loadReference(fieldName, key, function (obj) {
      if (obj != null) {
        deserializeForm(obj);
      }
    });
  });

  $(document).on("click", ".reference_type", function (e) {
    let referenceType = $(this).val();
    selectReferenceType({ type: referenceType });
  });

  $(document).on("click", ".btn-add-column", function (e) {
    let table = $(this).closest("table");
    addColumn(table);
  });

  $(document).on("click", ".btn-remove-last-column", function (e) {
    let table = $(this).closest("table");
    removeLastColumn(table);
  });

  $(document).on("click", ".btn-add-row", function (e) {
    let table = $(this).closest("table");
    addRow(table);
  });

  $(document).on("click", ".btn-remove-row", function (e) {
    let nrow = $(this).closest("tbody").find("tr").length;
    if (nrow > 1) {
      $(this).closest("tr").remove();
    } else if (
      nrow == 1 &&
      $(this).closest("table").attr("data-empty-on-remove") == "true"
    ) {
      $(this)
        .closest("tr")
        .find(":input")
        .each(function (e3) {
          $(this).val("");
        });
    }
  });

  $(document).on("change", '.map-section input[type="checkbox"]', function (e) {
    if ($(this)[0].checked) {
      $(this)
        .closest("tr")
        .siblings()
        .each(function () {
          $(this).find('input[type="checkbox"]')[0].checked = false;
        });
    }
  });

  $(document).on("change", ".entity-container-query .entity-checkbox", function (e) {
    let ents = getEntitySelection();
    let merged = $(".entity-merge")[0].checked;
    getEntityQuery(ents, merged);
  });

  $(document).on("change", ".entity-merge", function (e) {
    let ents = getEntitySelection();
    let merged = $(".entity-merge")[0].checked;
    getEntityQuery(ents, merged);
  });

  $(document).on("change", "#entity-check-controll", function (e) {
    let checked = $(this)[0].checked;
    $(".entity-checkbox").each(function () {
      $(this)[0].checked = checked;
    });
    let ents = getEntitySelection();
    let merged = $(".entity-merge")[0].checked;
    getEntityQuery(ents, merged);
  });

  $(document).on("click", "#create-new-app", function (e) {
    e.preventDefault();
    let modal = $(this).closest(".modal");
    let name = modal.find('[name="application_name"]').val().trim();
    let id = modal.find('[name="application_id"]').val().trim();
    let directory = modal.find('[name="application_directory"]').val().trim();
    let author = modal.find('[name="application_author"]').val().trim();
    if (name != "" && id != "" && directory != "" && author != "") {
      $.ajax({
        method: "POST",
        url: "lib.ajax/application-create.php",
        data: { name: name, id: id, directory: directory, author: author },
        success: function (data) {
          window.location = "./#module";
        },
      });
    }
  });

  $(document).on("click", ".entity-container-file .entity-li a", function (e) {
    e.preventDefault();
    let entity = $(this).attr("data-entity-name");
    let el = $(this);
    getEntityFile([entity], function () {
      $('.entity-li').removeClass("selected-file");
      el.parent().addClass("selected-file");
    });
  });

  $(document).on("click", ".module-list .file-li a", function (e) {
    e.preventDefault();
    let module = $(this).attr("data-file-name");
    let el = $(this);
    getModuleFile(module, function () {
      $('.file-li').removeClass("selected-file");
      el.parent().addClass("selected-file");
    });
  });

  $(document).on("click", ".entity-container-query .entity-li a",
    function (e) {
      e.preventDefault();
      let entity = $(this).attr("data-entity-name");
      getEntityQuery([entity]);
    }
  );

  $(document).on("click", "#button-save-module-file", function (e) {
    e.preventDefault();
    saveModule();
  });
  $(document).on("click", "#button-save-entity-file", function (e) {
    e.preventDefault();
    saveEntity();
  });

  $(document).on("click", "#button-save-entity-query", function (e) {
    e.preventDefault();
    saveQuery();
  });

  $("tbody.data-table-manual-sort").each(function (e) {
    let dataToSort = $(this)[0];
    Sortable.create(dataToSort, {
      animation: 150,
      scroll: true,
      handle: ".data-sort-handler",
      onEnd: function () {
        // do nothing
      },
    });
  });

  $(document).on('click', '.entity-container-translate-etity .entity-list a', function (e) {
    e.preventDefault();
    e.stopPropagation();
    let entityName = $(this).attr('data-entity-name');
    $.ajax({
      method: "POST",
      url: "lib.ajax/entity-translate.php",
      dataType: "json",
      data: { entityName: entityName },
      success: function (data) {
        let textOut1 = [];
        let textOut2 = [];
        for (let i in data) {
          textOut1.push(data[i].original);
          textOut2.push(data[i].translated);
        }
        trasEd1.getDoc().setValue(textOut1.join('\r\n'));
        trasEd2.getDoc().setValue(textOut2.join('\r\n'));
        focused = {};
        trasEd1.removeLineClass(lastLine, 'background', 'highlight-line');
        trasEd2.removeLineClass(lastLine, 'background', 'highlight-line');
        lastLine = -1;
      },
    });
  });

  $(document).on('click', '.generate-entity', function(){
    let entityName = $('.rd-entity-name').val();
    let tableName = $('.rd-table-name').val();
    if(confirm('Are you sure you want to generate entity and replace existing file?'))
    {
      $.ajax({
        method: "POST",
        url: "lib.ajax/entity-generator.php",
        data: { entityName:entityName, tableName:tableName},
        success: function (data) {
          updateEntityFile();
          updateEntityQuery(true);
        },
      });
    }
  });

  $(document).on('change', '.map-key', function(e){
    onChangeMapKey($(this));
  });
  $(document).on('keyup', '.map-key', function(e){
    onChangeMapKey($(this));
  });

  $(document).on('change', '#export_to_excel', function(e){
    let chk = $(this)[0].checked;
    if(chk)
    {
      $('#export_to_csv')[0].checked = false;
      $('#export_use_temporary')[0].disabled = true;
    }
  });
  $(document).on('change', '#export_to_csv', function(e){
    let chk = $(this)[0].checked;
    if(chk)
    {
      $('#export_to_excel')[0].checked = false;
      $('#export_use_temporary')[0].disabled = false;
    }
    else
    {
      $('#export_use_temporary')[0].disabled = true;
    }
  });
  
  $(document).on('change', '.entity-container-relationship .entity-checkbox', function(e){
    let params = [];
    $('.entity-container-relationship .entity-checkbox').each(function(){
      if($(this)[0].checked)
      {
        params.push('entity[]='+$(this).val());
      }
    });
    params.push('rnd='+(new Date()).getTime());
    let img = $('<img />');
    img.attr('src', 'lib.ajax/entity-relationship-diagram.php?'+params.join('&'));
    $('.erd-image').empty().append(img);
  });
  

  loadTable();
  updateEntityQuery(false);
  updateEntityRelationshipDiagram();
  updateEntityFile();
  updateModuleFile();
});

function onChangeMapKey(obj)
{
  let val = obj.val();
  if((val.toLowerCase() == 'label' || val.toLowerCase() == 'value' || val.toLowerCase() == 'default'))
  {
    if(!obj.hasClass('input-invalid-value'))
    {
      obj.addClass('input-invalid-value');
      setTimeout(function(){
        obj.val('data-'+val.toLowerCase());
        onChangeMapKey(obj);
      }, 500);
    }
  }
  else
  {
    if(obj.hasClass('input-invalid-value'))
    {
      obj.removeClass('input-invalid-value');
    }
  }
}

function showAlertUI(title, message) {
  $('#alert-dialog .modal-title').text(title);
  $('#alert-dialog .modal-body').html(message);
  $('#alert-dialog').modal('show');
}
function closeAlertUI() {
  $('#alert-dialog').modal('hide');
}

function saveModule() {
  if (currentModule != "") {
    $("#button-save-module-file").attr("disabled", "disabled");
    let fileContent = cmEditorModule.getDoc().getValue();
    $.ajax({
      type: "POST",
      url: "lib.ajax/module-update.php",
      data: { content: fileContent, module: currentModule },
      dataType: "html",
      success: function (data) {
        $("#button-save-module-file").removeAttr("disabled");
      },
    });
  } else {
    showAlertUI("Alert", "No file open");
  }
}

function saveEntity() {
  if (currentEntity != "") {
    $("#button-save-entity-file").attr("disabled", "disabled");
    let fileContent = cmEditorFile.getDoc().getValue();
    $.ajax({
      type: "POST",
      url: "lib.ajax/entity-update.php",
      dataType: "json",
      data: { content: fileContent, entity: currentEntity },
      dataType: "json",
      success: function (data) {
        $("#button-save-entity-file").removeAttr("disabled");
        updateEntityFile();
        updateEntityQuery(true);
        if (!data.success) {
          showAlertUI(data.error_title, data.error_message);
          setTimeout(function () { closeAlertUI() }, 2000);
        }
      },
    });
  } else {
    showAlertUI("Alert", "No file open");
  }
}

function saveQuery() {
  let blob = new Blob([cmEditorSQL.getDoc().getValue()], {
    type: "application/x-sql;charset=utf-8",
  });
  let appId = $("#current_application").val();
  saveAs(blob, appId + "-" + new Date().getTime() + ".sql");
}

function getEntitySelection() {
  let ents = [];
  $(".entity-checkbox").each(function () {
    if ($(this)[0].checked) {
      ents.push($(this).val());
    }
  });
  return ents;
}

function getEntityQuery(entity, merged) {
  $.ajax({
    type: "POST",
    url: "lib.ajax/entity-query.php",
    data: { entity: entity, merged: merged ? 1 : 0 },
    dataType: "html",
    success: function (data) {
      cmEditorSQL.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorSQL.refresh();
      }, 1);
      $("#button-save-entity-query").removeAttr("disabled");
    },
  });
}

function getEntityFile(entity, clbk) {
  $.ajax({
    type: "POST",
    url: "lib.ajax/entity-file.php",
    data: { entity: entity },
    dataType: "html",
    success: function (data) {
      cmEditorFile.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorFile.refresh();
      }, 1);
      $("#button-save-entity-file").removeAttr("disabled");
      currentEntity = entity[0];
      if (clbk) {
        clbk();
      }
    },
  });
}

function getModuleFile(module, clbk) {
  $.ajax({
    type: "POST",
    url: "lib.ajax/module-file.php",
    data: { module: module },
    dataType: "html",
    success: function (data) {
      cmEditorModule.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorModule.refresh();
      }, 1);
      $("#button-save-module-file").removeAttr("disabled");
      currentModule = module;
      if (clbk) {
        clbk();
      }
    },
  });
}

function updateEntityQuery(autoload) {
  autoload = autoload || false;
  $.ajax({
    type: "GET",
    url: "lib.ajax/entity-list-with-checkbox.php",
    data: { autoload: autoload },
    dataType: "html",
    success: function (data) {
      $(".entity-container-query .entity-list").empty().append(data);
      $('.entity-container-query .entity-list [data-toggle="tooltip"]').tooltip({
        placement: 'top'
      });
      let ents = getEntitySelection();
      let merged = $(".entity-merge")[0].checked;
      if (autoload) {
        getEntityQuery(ents, merged);
      }
    },
  });
}

function updateEntityRelationshipDiagram() {
  $.ajax({
    type: "GET",
    url: "lib.ajax/entity-list-for-diagram.php",
    dataType: "html",
    success: function (data) {
      $(".entity-container-relationship .entity-list").empty().append(data);
      $('.entity-container-relationship .entity-list [data-toggle="tooltip"]').tooltip({
        placement: 'top'
      });

    },
  });
}

function updateEntityFile() {
  $.ajax({
    type: "GET",
    url: "lib.ajax/entity-list.php",
    dataType: "html",
    success: function (data) {
      $(".entity-container-file .entity-list").empty().append(data);
      $(".entity-container-translate-etity .entity-list").empty().append(data);  
      $('.entity-container-file .entity-list [data-toggle="tooltip"], .entity-container-translate-etity .entity-list [data-toggle="tooltip"]').tooltip({
        placement: 'top'
      });
    },
  });
}
function updateModuleFile() {
  $.ajax({
    type: "GET",
    url: "lib.ajax/module-list.php",
    dataType: "html",
    success: function (data) {
      $(".module-container .module-list").empty().append(data);
      $('.module-container .module-list [data-toggle="tooltip"]').tooltip({
        placement: 'top'
      });
    },
  });
}

function saveReference(fieldName, key, value) {
  $.ajax({
    type: "POST",
    url: "lib.ajax/save-reference.php",
    data: { fieldName: fieldName, key: key, value: value },
    dataType: "json",
    success: function (data) { },
  });
}
function loadReference(fieldName, key, clbk) {
  $.ajax({
    type: "POST",
    url: "lib.ajax/load-reference.php",
    data: { fieldName: fieldName, key: key },
    dataType: "json",
    success: function (data) {
      clbk(data);
    },
  });
}

function updateTableName(
  moduleFileName,
  moduleCode,
  moduleName,
  masterTableName,
  masterPrimaryKeyName
) {
  moduleFileName = moduleFileName.replaceAll("_", "-");
  moduleCode = moduleCode.replaceAll("_", "-");
  moduleName = ucWord(moduleName.replaceAll("_", " "));

  let masterEntityName = upperCamelize(masterTableName);
  let approvalEntityName = masterEntityName + "Apv";
  let trashEntityName = masterEntityName + "Trash";
  let approvalTableName = masterTableName + "_apv";
  let approvalPrimaryKeyName = approvalTableName + "_id";
  let trashTableName = masterTableName + "_trash";
  let trashPrimaryKeyName = trashTableName + "_id";
  $(this).attr("data-value", masterTableName);

  $('[name="primary_key_master"]').val(masterPrimaryKeyName);
  $('[name="entity_master_name"]').val(masterEntityName);
  $('[name="entity_approval_name"]').val(approvalEntityName);
  $('[name="entity_trash_name"]').val(trashEntityName);
  $('[name="table_approval_name"]').val(approvalTableName);
  $('[name="primary_key_approval"]').val(approvalPrimaryKeyName);
  $('[name="table_trash_name"]').val(trashTableName);
  $('[name="primary_key_trash"]').val(trashPrimaryKeyName);
  $('[name="module_file"]').val(moduleFileName);
  $('[name="module_code"]').val(moduleCode);
  $('[name="module_name"]').val(moduleName);
}

function ucWord(str) {
  str = str.toLowerCase();
  return str.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function (s) {
    return s.toUpperCase();
  });
}

function prepareReferenceData(checkedValue, ctrl) {
  let tr = ctrl.closest("tr");
  if (checkedValue == "select") {
    tr.find(".reference-button-data").css("display", "inline");
  } else {
    tr.find(".reference-button-data").css("display", "none");
  }
}
function prepareReferenceFilter(checkedValue, ctrl) {
  let tr = ctrl.closest("tr");
  if (checkedValue == "select") {
    tr.find(".reference-button-filter").css("display", "inline");
  } else {
    tr.find(".reference-button-filter").css("display", "none");
  }
}

function switchApplication(currentApplication) {
  $.ajax({
    type: "post",
    url: "lib.ajax/application-switch.php",
    dataType: "json",
    data: { currentApplication: currentApplication },
    success: function (data) {
      window.location.reload();
    },
  });
}

function generateScript(selector) {
  let fields = [];
  $(selector)
    .find("tr")
    .each(function (e) {
      let fieldName = $(this).attr("data-field-name");
      let fieldLabel = $(this).find("input.input-field-name").val();
      let includeInsert = $(this).find("input.include_insert")[0].checked;
      let includeEdit = $(this).find("input.include_edit")[0].checked;
      let includeDetail = $(this).find("input.include_detail")[0].checked;
      let includeList = $(this).find("input.include_list")[0].checked;
      let includeExport = $(this).find("input.include_export")[0].checked;
      let isKey = $(this).find("input.include_key")[0].checked;
      let isInputRequired = $(this).find("input.include_required")[0].checked;
      let elementType = $(this).find("input.input-element-type:checked").val();
      let filterElementType =
        $(this).find("input.input-field-filter:checked").length > 0
          ? $(this).find("input.input-field-filter:checked").val()
          : null;
      let dataType = $(this).find("select.input-field-data-type").val();
      let inputFilter = $(this).find("select.input-data-filter").val();

      let referenceData = parseJsonData(
        $(this).find("input.reference-data").val()
      );
      let referenceFilter = parseJsonData(
        $(this).find("input.reference-filter").val()
      );

      let field = {
        fieldName: fieldName,
        fieldLabel: fieldLabel,
        includeInsert: includeInsert,
        includeEdit: includeEdit,
        includeDetail: includeDetail,
        includeList: includeList,
        includeExport:includeExport,
        isKey: isKey,
        isInputRequired: isInputRequired,
        elementType: elementType,
        filterElementType: filterElementType,
        dataType: dataType,
        inputFilter: inputFilter,

        referenceData: referenceData,
        referenceFilter: referenceFilter,
      };
      console.log(field)
      fields.push(field);
    });

  let subquery = $("#subquery")[0].checked && true;
  let requireApproval = $("#with_approval")[0].checked && true;
  let withTrash = $("#with_trash")[0].checked && true;
  let manualSortOrder = $("#manualsortorder")[0].checked && true;
  let exportToExcel = $("#export_to_excel")[0].checked && true;
  let exportToCsv = $("#export_to_csv")[0].checked && true;
  let activateDeactivate = $("#activate_deactivate")[0].checked && true;
  let withApprovalNote = $("#with_approval_note")[0].checked && true;
  let approvalPosition = $('[name="approval_position"]:checked').val();
  let approvalType = $('[name="approval_type"]:checked').val();
  let ajaxSupport = $("#ajax_support")[0].checked && true;
  let entity = {
    mainEntity: {
      entityName: $('[name="entity_master_name"]').val(),
      tableName: $('[name="source_table"]').val(),
      primaryKey: $('[name="primary_key_master"]').val(),
    },
    approvalRequired: requireApproval,
    trashRequired: withTrash,
  };

  if (requireApproval) {
    entity.approvalEntity = {
      entityName: $('[name="entity_approval_name"]').val(),
      tableName: $('[name="table_approval_name"]').val(),
      primaryKey: $('[name="primary_key_approval"]').val(),
    };
  }

  if (withTrash) {
    entity.trashEntity = {
      entityName: $('[name="entity_trash_name"]').val(),
      tableName: $('[name="table_trash_name"]').val(),
      primaryKey: $('[name="primary_key_trash"]').val(),
    };
  }

  let features = {
    subquery: subquery,
    activateDeactivate: activateDeactivate,
    sortOrder: manualSortOrder,
    exportToExcel: exportToExcel,
    exportToCsv: exportToCsv,
    approvalRequired: requireApproval,
    approvalNote: withApprovalNote,
    trashRequired: withTrash,
    approvalType: approvalType,
    approvalPosition: approvalPosition,
    ajaxSupport: ajaxSupport
  };

  let specification = getSpecificationModule();
  let sortable = getSortableModule();

  let dataToPost = {
    entity: entity,
    fields: fields,
    specification: specification,
    sortable: sortable,
    features: features,
    moduleCode: $('[name="module_code"]').val(),
    moduleName: $('[name="module_name"]').val(),
    moduleFile: $('[name="module_file"]').val(),
    target: $('#current_module_location').val(),
    updateEntity: $('[name="update_entity"]')[0].checked,
  };
  generateAllCode(dataToPost);
}

function getSpecificationModule() {
  let result = [];
  let selector = ".table-data-filter";
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      if (
        tr.find(".data-filter-column-name").length &&
        tr.find(".data-filter-column-value").length
      ) {
        let column = tr.find(".data-filter-column-name").val();
        let value = tr.find(".data-filter-column-value").val();
        column = column ? column.trim() : "";
        value = value ? value.trim() : "";
        if (column.length > 0) {
          result.push({
            column: column,
            value: value,
          });
        }
      }
    });
  return result;
}

function getSortableModule() {
  let result = [];
  let selector = ".table-data-order";
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      if (
        tr.find(".data-order-column-name").length &&
        tr.find(".data-order-order-type").length
      ) {
        let sortBy = tr.find(".data-order-column-name").val();
        let sortType = tr.find(".data-order-order-type").val();
        sortBy = sortBy ? sortBy.trim() : "";
        sortType = sortType ? sortType.trim() : "";
        if (sortBy.length > 0) {
          result.push({
            sortBy: sortBy,
            sortType: sortType,
          });
        }
      }
    });
  return result;
}

function parseJsonData(text) {
  if (typeof text !== "string") {
    return null;
  }
  try {
    let json = JSON.parse(text);
    if (typeof json === "object") {
      return json;
    }
  } catch (error) {
    // do nothing
  }
  return null;
}

function generateAllCode(dataToPost) {
  $.ajax({
    type: "post",
    url: "lib.ajax/script-generator.php",
    dataType: "json",
    data: dataToPost,
    success: function (data) {
      updateEntityFile();
      updateEntityQuery(true);
      if (data.success) {
        showAlertUI(data.title, data.message);
        setTimeout(function () { closeAlertUI() }, 2000);
      }
    },
  });
}

function updateCurrentApplivation(dataToPost) {
  $.ajax({
    type: "post",
    url: "lib.ajax/update-current-application.php",
    dataType: "json",
    data: dataToPost,
    success: function (data) {
      $('select[name="source_table"]').empty();
      for (let i in data) {
        $('select[name="source_table"]')[0].append(
          new Option(data[i].table_name, data[i].table_name)
        );
      }
      let val = $('select[name="source_table"]').attr("data-value");
      if (val != null && val != "") {
        $('select[name="source_table"]').val(val);
      }
    },
  });
}

function loadTable() {
  $.ajax({
    type: "post",
    url: "lib.ajax/table-list.php",
    dataType: "json",
    success: function (data) {
      $('select[name="source_table"]').empty();
      $('select[name="source_table"]')[0].append(
        new Option("- Select Table -", "")
      );
      for (let i in data) {
        if (data.hasOwnProperty(i)) {
          $('select[name="source_table"]')[0].append(
            new Option(data[i].table_name, data[i].table_name)
          );
        }
      }
      $('select[name="source_table"]')
        .find("option")
        .each(function (e3) {
          let val = $(this).attr("value") || "";
          if (
            val != "" &&
            typeof data[val] != "undefined" &&
            typeof data[val].primary_key != "undefined" &&
            typeof data[val].primary_key[0] != "undefined"
          ) {
            $(this).attr("data-primary-key", data[val].primary_key[0]);
          }
        });
      let val = $('select[name="source_table"]').attr("data-value");
      if (val != null && val != "") {
        $('select[name="source_table"]').val(val);
      }
    },
  });
}
function loadColumn(tableName, selector) {
  $.ajax({
    type: "post",
    url: "lib.ajax/column-list.php",
    data: { table_name: tableName },
    dataType: "json",
    success: function (answer) {
      $(selector).empty();
      let data = answer.fields;
      let i;
      let field, args;
      let domHtml;
      let skipedOnInsertEdit = getSkipedCol();
      for (i in data) {
        field = data[i].column_name;
        args = { type: data[i].data_type };
        domHtml = generateRow(field, args, skipedOnInsertEdit);
        $(selector).append(domHtml);
      }

      if (typeof answer.primary_keys != 'undefined' && answer.primary_keys.length) {
        for (let i in answer.primary_keys) {
          let key = answer.primary_keys[i];
          $('[data-field-name="' + key + '"]').find('[name="include_key_' + key + '"]')[0].checked = true;
        }
      }
      if ($('[name="module_load_previous"]')[0].checked) {
        loadSavedModuleData($('#module_file').val(), $('#current_module_location').val(), function () {
          $(".define-wrapper").css("display", "block");
          $("#define-column-tab").click();
        });
      }
      else {
        $(".define-wrapper").css("display", "block");
        $("#define-column-tab").click();
      }
    },
  });
}

function restoreForm(data) {
  // restore column
  if (typeof data.fields != 'undefined') {
    for (let i in data.fields) {
      if (data.fields.hasOwnProperty(i)) {
        let tr = $('.main-table tbody tr[data-field-name="' + data.fields[i].fieldName + '"]');
        if (tr.length > 0) {
          tr.appendTo(tr.parent());

          tr.find('.include_insert')[0].checked = data.fields[i].includeInsert === true || data.fields[i].includeInsert == 'true';
          tr.find('.include_edit')[0].checked = data.fields[i].includeEdit === true || data.fields[i].includeEdit == 'true';
          tr.find('.include_detail')[0].checked = data.fields[i].includeDetail === true || data.fields[i].includeDetail == 'true';
          tr.find('.include_list')[0].checked = data.fields[i].includeList === true || data.fields[i].includeList == 'true';
          tr.find('.include_export')[0].checked = data.fields[i].includeExport === true || data.fields[i].includeExport == 'true';
          tr.find('.include_key')[0].checked = data.fields[i].isKey === true || data.fields[i].isKey == 'true';
          tr.find('.include_required')[0].checked = data.fields[i].isInputRequired === true || data.fields[i].isInputRequired == 'true';
          tr.find('.input-element-type[value="' + data.fields[i].elementType + '"]')[0].checked = true;
          
          if (data.fields[i].elementType == 'select') {
            tr.find('.reference-data').val(JSON.stringify(data.fields[i].referenceData));
            tr.find('.reference-button-data').css('display', 'inline');
          }

          if (data.fields[i].filterElementType == 'select') {
            tr.find('.reference-filter').val(JSON.stringify(data.fields[i].referenceFilter));
            tr.find('.reference-button-filter').css('display', 'inline');
            tr.find('.input-field-filter[value="select"]')[0].checked = true;
          }
          if (data.fields[i].filterElementType == 'text') {
            tr.find('.input-field-filter[value="text"]')[0].checked = true;
          }

          tr.find('.input-field-data-type').val(data.fields[i].dataType)
          tr.find('.input-data-filter').val(data.fields[i].inputFilter)
        }

      }
    }
  }

  let cnt;
  let selector;


  cnt = 0;
  selector = '#modal-filter-data tbody tr:last-child';

  while ($('#modal-filter-data tbody tr').length > 1) 
  {
    $(selector).remove();
  }
  $(selector).find('.data-filter-column-name').val('');
  $(selector).find('.data-filter-column-value').val('');

  if (typeof data.specification == 'undefined' || data.specification.length == 0) {
    $(selector).find('.data-filter-column-name').val('');
    $(selector).find('.data-filter-column-value').val('');
  }
  else {
    for (let i in data.specification) {
      if (data.specification.hasOwnProperty(i)) {
        if (cnt > 0) {
          let trHtml = $(selector)[0].outerHTML;
          $(selector).parent().append(trHtml);
        }
        $(selector).find('.data-filter-column-name').val(data.specification[i].column);
        $(selector).find('.data-filter-column-value').val(data.specification[i].value);
        cnt++;
      }
    }
  }
  cnt = 0;
  selector = '#modal-order-data tbody tr:last-child';

  while ($('#modal-order-data tbody tr').length > 1) {
    $(selector).remove();
  }
  $(selector).find('.data-filter-column-name').val('');
  $(selector).find('.data-filter-column-value').val('');

  if (typeof data.sortable == 'undefined' || data.sortable.length == 0) {
    $(selector).find('.data-order-column-name').val('');
    $(selector).find('.data-order-order-type').val('PicoSort::ORDER_TYPE_ASC');
  }
  else {
    for (let i in data.sortable) {
      if (data.sortable.hasOwnProperty(i)) {
        if (cnt > 0) {
          let trHtml = $(selector)[0].outerHTML;
          $(selector).parent().append(trHtml);
        }
        $(selector).find('.data-order-column-name').val(data.sortable[i].sortBy);
        $(selector).find('.data-order-order-type').val(data.sortable[i].sortType);
        cnt++;
      }
    }
  }

  if (typeof data.features != 'undefined') {

    if ($('#modal-module-features [name="subquery"]').length) {
      $('#modal-module-features [name="subquery"]')[0].checked = data.features.subquery === true || data.features.subquery == 'true';
    }

    if ($('#modal-module-features [name="activate_deactivate"]').length) {
      $('#modal-module-features [name="activate_deactivate"]')[0].checked = data.features.activateDeactivate === true || data.features.activateDeactivate == 'true';
    }

    if ($('#modal-module-features [name="manualsortorder"]').length) {
      $('#modal-module-features [name="manualsortorder"]')[0].checked = data.features.sortOrder === true || data.features.sortOrder == 'true';
    }
    
    if ($('#modal-module-features [name="export_to_excel"]').length) {
      $('#modal-module-features [name="export_to_excel"]')[0].checked = data.features.exportToExcel === true || data.features.exportToExcel == 'true';
    }

    if ($('#modal-module-features [name="export_to_csv"]').length) {
      $('#modal-module-features [name="export_to_csv"]')[0].checked = data.features.exportToCsv === true || data.features.exportToCsv == 'true';
    }


    if ($('#modal-module-features [name="with_approval"]').length) {
      $('#modal-module-features [name="with_approval"]')[0].checked = data.features.approvalRequired === true || data.features.approvalRequired == 'true';
    }

    if ($('#modal-module-features [name="with_approval_note"]').length) {
      $('#modal-module-features [name="with_approval_note"]')[0].checked = data.features.approvalNote === true || data.features.approvalNote == 'true';
    }

    if ($('#modal-module-features [name="with_trash"]').length) {
      $('#modal-module-features [name="with_trash"]')[0].checked = data.features.trashRequired === true || data.features.trashRequired == 'true';
    }

    if ($('#modal-module-features [name="approval_type"][value="' + data.features.approvalType + '"]').length) {
      $('#modal-module-features [name="approval_type"][value="' + data.features.approvalType + '"]')[0].checked = true;
    }

    if ($('#modal-module-features [name="approval_position"][value="' + data.features.approvalPosition + '"]').length) {
      $('#modal-module-features [name="approval_position"][value="' + data.features.approvalPosition + '"]')[0].checked = true;
    }

    if ($('#modal-module-features [name="ajax_support"]').length) {
      $('#modal-module-features [name="ajax_support"]')[0].checked = data.features.ajaxSupport === true || data.features.ajaxSupport == 'true';
    }
  }
}

function loadSavedModuleData(moduleFile, target, clbk) {
  $.ajax({
    type: "GET",
    url: "lib.ajax/module-data.php",
    data: { moduleFile: moduleFile, target: target },
    dataType: "json",
    success: function (data) {
      restoreForm(data)
      clbk();
    },
    error: function (err) {
      clbk();
    }
  });
}

function getSkipedCol() {
  let skiped = [];

  skiped.push($('[name="entity_info_draft"]').val());
  skiped.push($('[name="entity_info_waiting_for"]').val());
  skiped.push($('[name="entity_info_approval_note"]').val());
  skiped.push($('[name="entity_info_approval_id"]').val());
  skiped.push($('[name="entity_info_admin_create"]').val());
  skiped.push($('[name="entity_info_admin_edit"]').val());
  skiped.push($('[name="entity_info_admin_ask_edit"]').val());
  skiped.push($('[name="entity_info_time_create"]').val());
  skiped.push($('[name="entity_info_time_edit"]').val());
  skiped.push($('[name="entity_info_time_ask_edit"]').val());
  skiped.push($('[name="entity_info_ip_create"]').val());
  skiped.push($('[name="entity_info_ip_edit"]').val());
  skiped.push($('[name="entity_info_ip_ask_edit"]').val());
  return skiped;
}

function generateSelectFilter(field, args) {
  let virtualDOM;

  args = args || {};
  args.type = args.type || "text";
  let dataType = args.type;
  let matchByType = {
    FILTER_SANITIZE_NUMBER_INT: [
      "bit",
      "varbit",
      "smallint",
      "int",
      "integer",
      "bigint",
      "smallserial",
      "serial",
      "bigserial",
      "bool",
      "boolean",
    ],
    FILTER_SANITIZE_NUMBER_FLOAT: ["numeric", "double", "real", "money"],
    FILTER_SANITIZE_SPECIAL_CHARS: [
      "char",
      "character",
      "varchar",
      "character varying",
      "text",
      "date",
      "timestamp",
      "time",
    ],
  };

  virtualDOM = $(
    '<select class="form-control input-data-filter" name="filter_type_' +
    field +
    '" id="filter_type_' +
    field +
    '">\r\n' +
    '<option value="FILTER_SANITIZE_NUMBER_INT">NUMBER_INT</option>\r\n' +
    '<option value="FILTER_SANITIZE_NUMBER_UINT">NUMBER_UINT</option>\r\n' +
    '<option value="FILTER_SANITIZE_NUMBER_OCTAL">NUMBER_OCTAL</option>\r\n' +
    '<option value="FILTER_SANITIZE_NUMBER_HEXADECIMAL">NUMBER_HEXADECIMAL</option>\r\n' +
    '<option value="FILTER_SANITIZE_NUMBER_FLOAT">NUMBER_FLOAT</option>\r\n' +
    '<option value="FILTER_SANITIZE_STRING">STRING</option>\r\n' +
    '<option value="FILTER_SANITIZE_STRING_INLINE">STRING_INLINE</option>\r\n' +
    '<option value="FILTER_SANITIZE_NO_DOUBLE_SPACE">NO_DOUBLE_SPACE</option>\r\n' +
    '<option value="FILTER_SANITIZE_STRIPPED">STRIPPED</option>\r\n' +
    '<option value="FILTER_SANITIZE_SPECIAL_CHARS">SPECIAL_CHARS</option>\r\n' +
    '<option value="FILTER_SANITIZE_ALPHA">ALPHA</option>\r\n' +
    '<option value="FILTER_SANITIZE_ALPHANUMERIC">ALPHANUMERIC</option>\r\n' +
    '<option value="FILTER_SANITIZE_ALPHANUMERICPUNC">ALPHANUMERICPUNC</option>\r\n' +
    '<option value="FILTER_SANITIZE_STRING_BASE64">STRING_BASE64</option>\r\n' +
    '<option value="FILTER_SANITIZE_EMAIL">EMAIL</option>\r\n' +
    '<option value="FILTER_SANITIZE_URL">URL</option>\r\n' +
    '<option value="FILTER_SANITIZE_IP">IP</option>\r\n' +
    '<option value="FILTER_SANITIZE_ENCODED">ENCODED</option>\r\n' +
    '<option value="FILTER_SANITIZE_COLOR">COLOR</option>\r\n' +
    '<option value="FILTER_SANITIZE_MAGIC_QUOTES">MAGIC_QUOTES</option>\r\n' +
    '<option value="FILTER_SANITIZE_PASSWORD">PASSWORD</option>\r\n' +
    "</select>\r\n"
  );

  let i, j, k, l;
  let filterType = "FILTER_SANITIZE_SPECIAL_CHARS";
  let found = false;
  for (i in matchByType) {
    j = matchByType[i];
    for (k in j) {
      if (dataType.indexOf(j[k]) != -1) {
        filterType = i;
        found = true;
        break;
      }
    }
    if (found) {
      break;
    }
  }
  virtualDOM.find("option").each(function (index, element) {
    $(this).removeAttr("selected");
  });
  virtualDOM
    .find('option[value="' + filterType + '"]')
    .attr("selected", "selected");
  return virtualDOM[0].outerHTML;
}

function generateSelectType(field, args) {
  let virtualDOM;
  args = args || {};
  args.type = args.type || "text";
  let dataType = args.type;
  let matchByType = {
    int: [
      "bit",
      "varbit",
      "smallint",
      "int",
      "integer",
      "bigint",
      "smallserial",
      "serial",
      "bigserial",
      "bool",
      "boolean",
    ],
    float: ["numeric", "double", "real", "money"],
    text: ["char", "character", "varchar", "character varying", "text"],
    "datetime-local": ["datetime", "timestamp"],
    date: ["date"],
    time: ["time"],
  };

  virtualDOM = $(
    '<select class="form-control input-field-data-type" name="data_type_' +
    field +
    '" id="data_type_' +
    field +
    '">\r\n' +
    '<option value="text" title="&lt;input type=&quot;text&quot;&gt;">text</option>\r\n' +
    '<option value="email" title="&lt;input type=&quot;email&quot;&gt;">email</option>\r\n' +
    '<option value="url" title="&lt;input type=&quot;email&quot;&gt;">url</option>\r\n' +
    '<option value="tel" title="&lt;input type=&quot;tel&quot;&gt;">tel</option>\r\n' +
    '<option value="password" title="&lt;input type=&quot;password&quot;&gt;">password</option>\r\n' +
    '<option value="int" title="&lt;input type=&quot;number&quot;&gt;">int</option>\r\n' +
    '<option value="float" title="&lt;input type=&quot;number&quot; step=&quot;any&quot;&gt;">float</option>\r\n' +
    '<option value="date" title="&lt;input type=&quot;text&date;&gt;">date</option>\r\n' +
    '<option value="time" title="&lt;input type=&quot;time&quot;&gt;">time</option>\r\n' +
    '<option value="datetime-local" title="&lt;input type=&quot;datetime-local&quot;&gt;">datetime</option>\r\n' +
    '<option value="week" title="&lt;input type=&quot;week&quot;&gt;">week</option>\r\n' +
    '<option value="color" title="&lt;input type=&quot;color&quot;&gt;">color</option>\r\n' +
    "</select>\r\n"
  );

  let i;
  let j;
  let k;
  let filterType = "FILTER_SANITIZE_SPECIAL_CHARS";
  let found = false;
  for (i in matchByType) {
    j = matchByType[i];
    for (k in j) {
      if (dataType.indexOf(j[k]) != -1) {
        filterType = i;
        found = true;
        break;
      }
    }
    if (found) {
      break;
    }
  }
  virtualDOM.find("option").each(function (index, element) {
    $(this).removeAttr("selected");
  });
  virtualDOM
    .find('option[value="' + filterType + '"]')
    .attr("selected", "selected");
  return virtualDOM[0].outerHTML;
}

function arrayUnique(arr1) {
  let i;
  let arr2 = [];
  for (i = 0; i < arr1.length; i++) {
    if (arr2.indexOf(arr1[i]) == -1) {
      arr2.push(arr1[i]);
    }
  }
  return arr2;
}

String.prototype.equalIgnoreCase = function (str) {
  let str1 = this;
  if (str1.toLowerCase() == str.toLowerCase()) return true;
  return false;
};
function isKeyWord(str) {
  str = str.toString();
  let i, j;
  let kw = keyWords.split(",");
  for (i in kw) {
    if (str.equalIgnoreCase(kw[i])) {
      return true;
    }
  }
  return false;
}
let keyWords =
  "absolute,action,add,after,aggregate,alias,all,allocate,alter,analyse,analyze,and,any,are,array,as,asc,assertion,at,authorization,avg,before,begin,between,binary,bit,bit_length,blob,boolean,both,breadth,by,call,cascade,cascaded,case,cast,catalog,char,character,character_length,char_length,check,class,clob,close,coalesce,collate,collation,column,commit,completion,connect,connection,constraint,constraints,constructor,continue,convert,corresponding,count,create,cross,cube,current,current_date,current_path,current_role,current_time,current_timestamp,current_user,cursor,cycle,data,date,day,deallocate,dec,decimal,declare,default,deferrable,deferred,delete,depth,deref,desc,describe,descriptor,destroy,destructor,deterministic,diagnostics,dictionary,disconnect,distinct,do,domain,double,drop,dynamic,each,else,end,end-exec,equals,escape,every,except,exception,exec,execute,exists,external,extract,false,fetch,first,float,for,foreign,found,free,from,full,function,general,get,global,go,goto,grant,group,grouping,having,host,hour,identity,ignore,immediate,in,indicator,initialize,initially,inner,inout,input,insensitive,insert,int,integer,intersect,interval,into,is,isolation,iterate,join,key,language,large,last,lateral,leading,left,less,level,like,limit,local,localtime,localtimestamp,locator,lower,map,match,max,min,minute,modifies,modify,month,names,national,natural,nchar,nclob,new,next,no,none,not,null,nullif,numeric,object,octet_length,of,off,offset,old,on,only,open,operation,option,or,order,ordinality,out,outer,output,overlaps,pad,parameter,parameters,partial,path,placing,position,postfix,precision,prefix,preorder,prepare,preserve,primary,prior,privileges,procedure,public,read,reads,real,recursive,ref,references,referencing,relative,restrict,result,return,returns,revoke,right,role,rollback,rollup,routine,row,rows,savepoint,schema,scope,scroll,search,second,section,select,sequence,session,session_user,set,sets,size,smallint,some,space,specific,specifictype,sql,sqlcode,sqlerror,sqlexception,sqlstate,sqlwarning,start,state,statement,static,structure,substring,sum,system_user,table,temporary,terminate,than,then,time,timestamp,timezone_hour,timezone_minute,to,trailing,transaction,translate,translation,treat,trigger,trim,true,under,union,unique,unknown,unnest,update,upper,usage,user,using,value,values,varchar,variable,varying,view,when,whenever,where,with,without,work,write,year,zone";

String.prototype.replaceAll = function (str1, str2, ignore) {
  return this.replace(
    new RegExp(
      str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"),
      ignore ? "gi" : "g"
    ),
    typeof str2 == "string" ? str2.replace(/\$/g, "$$$$") : str2
  );
};
String.prototype.capitalize = function () {
  return this.replace(/\w\S*/g, function (txt) {
    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
  });
};
String.prototype.prettify = function () {
  let i, j, k;
  let str = this;
  let arr = str.split(" ");
  for (i in arr) {
    j = arr[i];
    switch (j) {
      case "Id":
        arr[i] = "";
        break;
      case "Ip":
        arr[i] = "IP";
        break;
    }
  }
  return arr.join(" ");
};

function generateRow(field, args, skipedOnInsertEdit) {
  let isKW = isKeyWord(field);
  let classes = [];
  let cls = "";
  classes.push("row-column");
  if (isKW) {
    classes.push("reserved");
  }
  cls = ' class="' + classes.join(" ") + '"';
  let insertRow = "";
  let editRow = "";
  let listRow = "";
  let exportRow = "";
  if ($.inArray(field, skipedOnInsertEdit) != -1) {
    insertRow =
      '  <td align="center"><input type="checkbox" class="include_insert" name="include_insert_' +
      field +
      '" value="0" disabled="disabled"></td>\r\n';
    editRow =
      '  <td align="center"><input type="checkbox" class="include_edit" name="include_edit_' +
      field +
      '" value="0" disabled="disabled"></td>\r\n';
    listRow =
      '  <td align="center"><input type="checkbox" class="include_list" name="include_list_' +
      field +
      '" value="1"></td>\r\n';
  } else {
    insertRow =
      '  <td align="center"><input type="checkbox" class="include_insert" name="include_insert_' +
      field +
      '" value="1" checked="checked"></td>\r\n';
    editRow =
      '  <td align="center"><input type="checkbox" class="include_edit" name="include_edit_' +
      field +
      '" value="1" checked="checked"></td>\r\n';
    listRow =
      '  <td align="center"><input type="checkbox" class="include_list" name="include_list_' +
      field +
      '" value="1" checked="checked"></td>\r\n';
  }

  exportRow =
      '  <td align="center"><input type="checkbox" class="include_export" name="include_export_' +
      field +
      '" value="1" checked="checked"></td>\r\n';

  let rowHTML =
    '<tr data-field-name="' +
    field +
    '" ' +
    cls +
    ">\r\n" +
    '  <td class="data-sort data-sort-body data-sort-handler"></td>\r\n' +
    '  <td class="field-name">' +
    field +
    '<input type="hidden" name="field" value="' +
    field +
    '"></td>\r\n' +
    '  <td><input type="hidden" class="input-field-name" name="caption_' +
    field +
    '" value="' +field.replaceAll("_", " ").capitalize().prettify().trim() +'" autocomplete="off" spellcheck="false">' + field.replaceAll("_", " ").capitalize().prettify().trim() + '</td>\r\n' +
    insertRow + editRow +
    '  <td align="center"><input type="checkbox" class="include_detail" name="include_detail_' +field +'" value="1" checked="checked"></td>\r\n' +
    listRow +
    exportRow +
    '  <td align="center"><input type="checkbox" class="include_key" name="include_key_' +field +'" value="1"></td>\r\n' +
    '  <td align="center"><input type="checkbox" class="include_required" name="include_required_' +field +'" value="1"></td>\r\n' +
    '  <td align="center"><input type="radio" class="input-element-type" name="element_type_' +field +'" value="text" checked="checked"></td>\r\n' +
    '  <td align="center"><input type="radio" class="input-element-type" name="element_type_' +field +'" value="textarea"></td>\r\n' +
    '  <td align="center"><input type="radio" class="input-element-type" name="element_type_' +field +'" value="checkbox"></td>\r\n' +
    '  <td align="center"><input type="radio" class="input-element-type" name="element_type_' +field +'" value="select"></td>\r\n' +
    '  <td align="center"><input type="hidden" class="reference-data" name="reference_data_' +
    field +
    '" value="{}"><button type="button" class="btn btn-sm btn-primary reference-button reference-button-data">Source</button></td>\r\n' +
    '  <td align="center"><input type="checkbox" name="list_filter_' +
    field +
    '" value="text" class="input-field-filter"></td>\r\n' +
    '  <td align="center"><input type="checkbox" name="list_filter_' +
    field +
    '" value="select" class="input-field-filter"></td>\r\n' +
    '  <td align="center"><input type="hidden" class="reference-filter" name="reference_filter_' +
    field +
    '" value="{}"><button type="button" class="btn btn-sm btn-primary reference-button reference-button-filter">Source</button></td>\r\n' +
    "  <td>\r\n" +
    generateSelectType(field, args) +
    "  </td>\r\n" +
    "  <td>\r\n" +
    generateSelectFilter(field, args) +
    "  </td>\r\n" +
    "</tr>\r\n";
  return rowHTML;
}

function serializeForm() {
  let type = null;
  $(".reference_type").each(function (e) {
    if ($(this)[0].checked) {
      type = $(this).val();
    }
  });
  let entity = getEntityData();
  let map = getMapData();
  let yesno = null;
  let truefalse = null;
  let onezero = null;
  let all = {
    type: type,
    entity: entity,
    map: map,
    yesno: yesno,
    truefalse: truefalse,
    onezero: onezero,
  };
  return all;
}

function deserializeForm(data) {
  $("#modal-create-reference-data").find(".modal-body").empty();
  $("#modal-create-reference-data")
    .find(".modal-body")
    .append(getReferenceResource());
  selectReferenceType(data);
  setEntityData(data);
  setMapData(data);
}

function addRow(table) {
  let nrow = table.find("tbody").find("tr").length;
  let lastRow = table.find("tbody").find("tr:last-child").prop("outerHTML");
  table.find("tbody").append(lastRow);
}

function addColumn(table) {
  let ncol = table.find("thead").find("tr").find("td").length;
  let nrow = table.find("tbody").find("tr").length;
  let pos = ncol - parseInt(table.attr("data-offset")) - 1;
  let inputHeader =
    '<td><input class="form-control map-key" type="text" value="" placeholder="Additional attribute name"></td>';
  let inputBody =
    '<td><input class="form-control map-value" type="text" value="" placeholder="Additional attribute value"></td>';
  table
    .find("thead")
    .find("tr")
    .find("td:nth(" + pos + ")")
    .after(inputHeader);
  table
    .find("tbody")
    .find("tr")
    .each(function (e3) {
      $(this)
        .find("td:nth(" + pos + ")")
        .after(inputBody);
    });
  table
    .find("tfoot")
    .find("tr")
    .find("td")
    .attr("colspan", table.find("thead").find("tr").find("td").length);
}

function removeLastColumn(table) {
  let ncol = table.find("thead").find("tr").find("td").length;
  let nrow = table.find("tbody").find("tr").length;
  let offset = parseInt(table.attr("data-offset"));
  let pos = ncol - offset - 1;
  if (ncol > offset + 2) {
    table
      .find("thead")
      .find("tr")
      .find("td:nth(" + pos + ")")
      .remove();
    table
      .find("tbody")
      .find("tr")
      .each(function (e3) {
        $(this)
          .find("td:nth(" + pos + ")")
          .remove();
      });
    table
      .find("tfoot")
      .find("tr")
      .find("td")
      .attr("colspan", table.find("thead").find("tr").find("td").length);
  }
}

function selectReferenceType(data) {
  let referenceType = data.type ? data.type : "entity";
  let obj = $('#modal-create-reference-data .modal-dialog');
  if(referenceType == 'entity' || referenceType == 'map')
  {
    obj.addClass('modal-lg');
    if(obj.hasClass('modal-md'))
    {
      obj.removeClass('modal-md');
    }
  }
  else
  {
    obj.addClass('modal-md');
    if(obj.hasClass('modal-lg'))
    {
      obj.removeClass('modal-lg');
    }
  }
  
  if ($('.reference_type[value="' + referenceType + '"]').length > 0) {
    $('.reference_type[value="' + referenceType + '"]')[0].checked = true;
  }
  $(".reference-section").css({ display: "none" });
  if (referenceType == "entity") {
    $(".entity-section").css({ display: "block" });
  } else if (referenceType == "map") {
    $(".map-section").css({ display: "block" });
  }
}

function setEntityData(data) {
  data.entity = data && data.entity ? data.entity : {};
  let entity = data.entity;
  entity.entityName = entity.entityName ? entity.entityName : "";
  entity.tableName = entity.tableName ? entity.tableName : "";
  entity.primaryKey = entity.primaryKey ? entity.primaryKey : "";
  entity.value = entity.value ? entity.value : "";
  entity.objectName = entity.objectName ? entity.objectName : "";
  entity.propertyName = entity.propertyName ? entity.propertyName : "";
  entity.textNodeFormat = entity.textNodeFormat ? entity.textNodeFormat : "";
  entity.indent = entity.indent ? entity.indent : "";

  let selector = '[data-name="entity"]';
  $(selector).find(".rd-entity-name").val(entity.entityName);
  $(selector).find(".rd-table-name").val(entity.tableName);
  $(selector).find(".rd-primary-key").val(entity.primaryKey);
  $(selector).find(".rd-value-column").val(entity.value);
  $(selector).find(".rd-reference-object-name").val(entity.objectName);
  $(selector).find(".rd-reference-property-name").val(entity.propertyName);
  $(selector).find(".rd-option-text-node-format").val(entity.textNodeFormat);
  $(selector).find(".rd-option-indent").val(entity.indent);

  setSpecificationData(data);
  setSortableData(data);
  setAdditionalOutputData(data);
}

function getEntityData() {
  let selector = '[data-name="entity"]';
  let entity = {
    entityName: $(selector).find(".rd-entity-name").val().trim(),
    tableName: $(selector).find(".rd-table-name").val().trim(),
    primaryKey: $(selector).find(".rd-primary-key").val().trim(),
    value: $(selector).find(".rd-value-column").val().trim(),
    objectName: $(selector).find(".rd-reference-object-name").val().trim(),
    propertyName: $(selector).find(".rd-reference-property-name").val().trim(),
    textNodeFormat: $(selector).find(".rd-option-text-node-format").val(),
    indent: $(selector).find(".rd-option-indent").val(),
    specification: getSpecificationData(),
    sortable: getSortableData(),
    additionalOutput: getAdditionalOutputData(),
  };
  return entity;
}

function setSpecificationData(data) {
  let result = [];
  let selector = '[data-name="specification"]';
  let table = $(selector);
  let specification = data.entity.specification;
  if (
    typeof specification != "undefined" &&
    specification != null &&
    specification.length > 0
  ) {
    for (let i in specification) {
      if (i > 0) {
        addRow(table);
      }
      let tr = table.find("tr:last-child");
      let row = specification[i];
      tr.find(".rd-column-name").val(row.column);
      tr.find(".rd-value").val(row.value);
    }
  }
}

function getSpecificationData() {
  let result = [];
  let selector = '[data-name="specification"]';
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      let column = tr.find(".rd-column-name").val().trim();
      let value = tr.find(".rd-value").val().trim();
      if (column.length > 0) {
        result.push({
          column: column,
          value: fixValue(value),
        });
      }
    });
  return result;
}

function setSortableData(data) {
  let result = [];
  let selector = '[data-name="sortable"]';
  let table = $(selector);
  let sortable = data.entity.sortable;
  if (
    typeof sortable != "undefined" &&
    sortable != null &&
    sortable.length > 0
  ) {
    for (let i in sortable) {
      if (i > 0) {
        addRow(table);
      }
      let tr = table.find("tr:last-child");
      let row = sortable[i];
      tr.find(".rd-column-name").val(row.sortBy);
      tr.find(".rd-order-type").val(row.sortType);
    }
  }
}

function getSortableData() {
  let result = [];
  let selector = '[data-name="sortable"]';
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      let sortBy = tr.find(".rd-column-name").val().trim();
      let sortType = tr.find(".rd-order-type").val().trim();
      if (sortBy.length > 0) {
        result.push({
          sortBy: sortBy,
          sortType: sortType,
        });
      }
    });
  return result;
}

function setAdditionalOutputData(data) {
  let selector = '[data-name="additional-output"]';
  let table = $(selector);
  let additional = data.entity.additionalOutput;
  if (
    typeof additional != "undefined" &&
    additional != null &&
    additional.length > 0
  ) {
    for (let i in additional) {
      if (i > 0) {
        addRow(table);
      }
      let tr = table.find("tr:last-child");
      let row = additional[i];
      tr.find(".rd-column-name").val(row.column);
    }
  }
}

function getAdditionalOutputData() {
  let result = [];
  let selector = '[data-name="additional-output"]';
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      let column = tr.find(".rd-column-name").val().trim();
      if (column.length > 0) {
        result.push({
          column: column,
        });
      }
    });
  return result;
}

function setMapData(data) {
  let selector = '[data-name="map"]';
  let table = $(selector);
  let keys = [];
  data.map = data.map ? data.map : [];
  let map = data.map;
  let mapKey = [];
  if (map.length > 0) {
    let map0 = map[0];
    let objLength = 0;
    let j = 0;
    for (let i in map0) {
      if (map0.hasOwnProperty(i)) {
        objLength++;
        if (objLength > 4) {
          addColumn(table);
        }
        if (i != "value" && i != "label" && i != "default") {
          keys.push(i);
          mapKey[j] = i;
        }
      }
    }
    for (let i in keys) {
      let j = parseInt(i) + 1;
      table
        .find("thead")
        .find("tr")
        .find(".map-key:nth-child(" + j + ")")
        .val(keys[i]);
    }
    if ($(selector).find("thead").find("tr").find(".map-key").length > 0) {
      $(selector)
        .find("thead")
        .find("tr")
        .find(".map-key")
        .each(function (e) {
          keys.push($(this).val().trim());
        });
    }

    for (let i in map) {
      if (i > 0) {
        addRow(table);
      }
      let tr = table.find("tr:last-child");
      let row = map[i];
      tr.find(".rd-value").val(row.value);
      tr.find(".rd-label").val(row.label);
      if (map[i]["default"]) {
        tr.find(".rd-selected")[0].checked = true;
      }

      for (let k in keys) {
        let j = parseInt(k) + 1;
        let refValue = map[i][keys[k]];
        tr.find(".map-value:nth-child(" + j + ")").val(refValue);
      }
    }
  }
}

function getMapData() {
  let result = [];
  let selector = '[data-name="map"]';
  let keys = [];
  if ($(selector).find("thead").find("tr").find(".map-key").length > 0) {
    $(selector)
      .find("thead")
      .find("tr")
      .find(".map-key")
      .each(function (e) {
        keys.push($(this).val().trim());
      });
  }
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      let value = tr.find(".rd-value").val().trim();
      let label = tr.find(".rd-label").val().trim();
      let selected = tr.find(".rd-selected")[0].checked;
      let opt = {
        value: value,
        label: label,
        default: selected,
      };
      if (keys.length > 0) {
        let idx = 0;
        tr.find(".map-value").each(function (e) {
          let attrVal = $(this).val();
          if (keys[idx].length > 0) {
            opt[keys[idx]] = attrVal;
          }
          idx++;
        });
      }
      result.push(opt);
    });
  return result;
}

function fixValue(value) {
  if (value == "true") {
    return true;
  } else if (value == "false") {
    return false;
  } else if (value == "null") {
    return null;
  } else if (isNumeric(value)) {
    return parseNumber(value);
  } else {
    return value;
  }
}

function parseNumber(str) {
  if (str.indexOf(".") !== -1) {
    return parseFloat(str);
  } else {
    return parseInt(str);
  }
}

function isNumeric(str) {
  if (typeof str != "string") return false;
  return !isNaN(str) && !isNaN(parseFloat(str));
}
function setLanguage(languages)
{
  $('select.target-language').each(function(){
    let obj = $(this);
    obj.empty();
    for(let i in languages)
    {
      let language = languages[i];
      let opt = '<option value="'+language.code+'"'+(language.selected ? ' selected':'')+'>'+language.name+'</option>';
      obj.append(opt);
    }
  });
}