let initielized = false;
let currentEntity = "";
let currentModule = "";
let currentEntity2Translated = "";
let lastErrorLine = -1;
let ajaxPending = 0;
let referenceResource = '';

/**
 * Increments the `ajaxPending` counter and updates the visual representation of the pending bar.
 */
function increaseAjaxPending() {
  ajaxPending++;
  updatePendingBar();
}

/**
 * Decrements the `ajaxPending` counter and updates the visual representation of the pending bar.
 */
function decreaseAjaxPending() {
  ajaxPending--;
  updatePendingBar();
}

/**
 * Reset the workspace search input field to an empty value and trigger the workspace filtering logic.
 *
 * This function clears the value of the workspace search input field (`#search-workspace`)
 * and calls `doFilterWorkspace` to reapply the filtering logic with an empty search value,
 * effectively resetting the workspace filter.
 */
function resetWorkspaceSearch()
{
  $('#search-workspace').val('');
  doFilterWorkspace($('#search-workspace'));
}

/**
 * Reset the application search input field to an empty value and trigger the application filtering logic.
 *
 * This function clears the value of the application search input field (`#search-application`)
 * and calls `doFilterApplication` to reapply the filtering logic with an empty search value,
 * effectively resetting the application filter.
 */
function resetApplicationSearch()
{
  $('#search-application').val('');
  doFilterApplication($('#search-application'));
}

/**
 * Updates the width of the `.ajax-pending` element to visually represent the current `ajaxPending` count.
 * The width is calculated as `ajaxPending * 16` pixels.
 */
function updatePendingBar() {
  $('.ajax-pending').css('width', (ajaxPending * 16) + 'px');
}

// A comma-separated string of SQL keywords.
let keyWords = "absolute,action,add,after,aggregate,alias,all,allocate,alter,analyse,analyze,and,any,are,"
+"array,as,asc,assertion,at,authorization,avg,before,begin,between,binary,bit,bit_length,blob,boolean,both,"
+"breadth,by,call,cascade,cascaded,case,cast,catalog,char,character,character_length,char_length,check,"
+"class,clob,close,coalesce,collate,collation,column,commit,completion,connect,connection,constraint,"
+"constraints,constructor,continue,convert,corresponding,count,create,cross,cube,current,current_date,"
+"current_path,current_role,current_time,current_timestamp,current_user,cursor,cycle,data,date,day,"
+"deallocate,dec,decimal,declare,default,deferrable,deferred,delete,depth,deref,desc,describe,descriptor,"
+"destroy,destructor,deterministic,diagnostics,dictionary,disconnect,distinct,do,domain,double,drop,dynamic,"
+"each,else,end,end-exec,equals,escape,every,except,exception,exec,execute,exists,external,extract,false,"
+"fetch,first,float,for,foreign,found,free,from,full,function,general,get,global,go,goto,grant,group,"
+"grouping,having,host,hour,identity,ignore,immediate,in,indicator,initialize,initially,inner,inout,input,"
+"insensitive,insert,int,integer,intersect,interval,into,is,isolation,iterate,join,key,language,large,last,"
+"lateral,leading,left,less,level,like,limit,local,localtime,localtimestamp,locator,lower,map,match,max,min,"
+"minute,modifies,modify,month,names,national,natural,nchar,nclob,new,next,no,none,not,null,nullif,numeric,"
+"object,octet_length,of,off,offset,old,on,only,open,operation,option,or,order,ordinality,out,outer,output,"
+"overlaps,pad,parameter,parameters,partial,path,placing,position,postfix,precision,prefix,preorder,prepare,"
+"preserve,primary,prior,privileges,procedure,public,read,reads,real,recursive,ref,references,referencing,"
+"relative,restrict,result,return,returns,revoke,right,role,rollback,rollup,routine,row,rows,savepoint,"
+"schema,scope,scroll,search,second,section,select,sequence,session,session_user,set,sets,size,smallint,"
+"some,space,specific,specifictype,sql,sqlcode,sqlerror,sqlexception,sqlstate,sqlwarning,start,state,"
+"statement,static,structure,substring,sum,system_user,table,temporary,terminate,than,then,time,timestamp,"
+"timezone_hour,timezone_minute,to,trailing,transaction,translate,translation,treat,trigger,trim,true,"
+"under,union,unique,unknown,unnest,update,upper,usage,user,using,value,values,varchar,variable,varying,"
+"view,when,whenever,where,with,without,work,write,year,zone";

/**
 * Compares two strings for equality, ignoring case differences.
 *
 * @param {string} str - The string to compare against the calling string.
 * @returns {boolean} True if the strings are equal (ignoring case), false otherwise.
 */
String.prototype.equalIgnoreCase = function (str)  //NOSONAR
{
  let str1 = this;
  return str1.toLowerCase() == str.toLowerCase();
};

/**
 * Replaces all occurrences of a specified substring (str1) with another substring (str2) in the calling string.
 *
 * This function uses a regular expression to ensure that all matches are replaced. 
 * Special characters in the search string are escaped, and the replacement string is processed 
 * to handle any dollar signs correctly.
 *
 * @param {string} str1 - The substring to be replaced.
 * @param {string} str2 - The substring to replace with.
 * @param {boolean} [ignore=false] - If true, the replacement is case insensitive.
 * @returns {string} The modified string with all occurrences replaced.
 */
String.prototype.replaceAll = function (str1, str2, ignore)  //NOSONAR
{
  return this.replace(
    new RegExp(
      str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), ignore ? "gi" : "g" //NOSONAR
    ),
    typeof str2 == "string" ? str2.replace(/\$/g, "$$$$") : str2
  );
};

/**
 * Replaces all occurrences of a specified substring (str1) with another substring (str2) in the calling string.
 *
 * This function uses a regular expression to ensure that all matches are replaced. 
 * Special characters in the search string are escaped, and the replacement string is processed 
 * to handle any dollar signs correctly.
 *
 * @param {string} str1 - The substring to be replaced.
 * @param {string} str2 - The substring to replace with.
 * @param {boolean} [ignore=false] - If true, the replacement is case insensitive.
 * @returns {string} The modified string with all occurrences replaced.
 */
String.prototype.replaceAll = function (str1, str2, ignore)  //NOSONAR
{
  return this.replace(
    new RegExp(
      str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), //NOSONAR
      ignore ? "gi" : "g"
    ),
    typeof str2 == "string" ? str2.replace(/\$/g, "$$$$") : str2
  );
};

/**
 * Capitalizes the first letter of each word in the calling string.
 *
 * This method transforms the string so that each word starts with an uppercase letter,
 * while the rest of the letters in the word are converted to lowercase.
 *
 * @returns {string} The modified string with each word capitalized.
 */
String.prototype.capitalize = function ()  //NOSONAR
{
  return this.replace(/\w\S*/g, function (txt) {
    return txt.charAt(0).toUpperCase() + txt.substring(1).toLowerCase();
  });
};

/**
 * Prettifies the calling string by performing specific transformations on certain words.
 *
 * - The word "Id" is removed from the string.
 * - The word "Ip" is transformed to "IP".
 *
 * @returns {string} The modified string after applying the transformations.
 */
String.prototype.prettify = function ()  //NOSONAR
{
  let i, j;
  let str = this;
  let arr = str.split(" ");
  for (i = 0; i < arr.length; i++)  //NOSONAR
  {
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

/**
 * Replaces all occurrences of a specified substring within a string.
 *
 * This method extends the String prototype to allow for replacing
 * multiple instances of a given substring with a specified replacement string.
 *
 * @param {string} search - The substring to search for in the string.
 * @param {string} replacement - The string to replace each occurrence of the search substring.
 * @returns {string} A new string with all occurrences of the search substring replaced by the replacement string.
 */
String.prototype.replaceAll = function (search, replacement)  //NOSONAR
{
  let target = this;
  return target.replace(new RegExp(search, "g"), replacement);
};

/**
 * Load main resource
 */
jQuery(function () {
  $('body').load('lib.ajax/body.min.html', function () {
    initAll();
    initEditor();
  });
});

// Add event listener
let initAll = function () {
  $(document).on('click', '.group-reference', function(e2){
    let value = $(this).val();
    $(this).closest('table').attr('data-group-source', value);
  });
  
  $(document).on('click', '#button_delete_module_file', function (e) {
    e.preventDefault();
    asyncAlert(
      `Do you want to delete file ${currentModule}.php?`,  // Message to display in the modal
      'Confirmation',  
      [
        {
          'caption': 'Yes',  
          'fn': () => {
            
            increaseAjaxPending();
            $.ajax({
              type: "POST",
              url: "lib.ajax/module-delete.php",
              dataType: "json",
              data: { module: currentModule},
              success: function (data) {
                decreaseAjaxPending();
                updateModuleFile();
                if(data.success)
                {
                  $('#button_save_module_file').attr('disabled', 'disabled');
                  $('#button_delete_module_file').attr('disabled', 'disabled');
                  cmEditorModule.getDoc().setValue('');
                  setTimeout(function () // NOSONAR
                  {
                    cmEditorModule.refresh();
                  }, 1);
                }
              }, 
              error: function(e){
                decreaseAjaxPending();
              },
            });
          },  
          'class': 'btn-primary'  
        },
        {
          'caption': 'No',  
          'fn': () => { },  
          'class': 'btn-secondary'  
        }
      ]
    );
  });

  $(document).on('click', '#button_delete_entity_file', function (e) {
    e.preventDefault();
    asyncAlert(
      `Do you want to delete file ${currentEntity}.php?`,  // Message to display in the modal
      'Confirmation',  
      [
        {
          'caption': 'Yes',  
          'fn': () => {
            
            increaseAjaxPending();
            $.ajax({
              type: "POST",
              url: "lib.ajax/entity-delete.php",
              dataType: "json",
              data: { entity: currentEntity},
              success: function (data) {
                decreaseAjaxPending();
                updateEntityFile();
                updateEntityQuery(true);
                updateEntityRelationshipDiagram();
                removeHilightLineError();
                if(data.success)
                {
                  setEntityFile('');
                  $('#button_save_entity_file').attr('disabled', 'disabled');
                  $('#button_save_entity_file_as').attr('disabled', 'disabled');
                  $('#button_delete_entity_file').attr('disabled', 'disabled');
                }
              }, 
              error: function(e){
                decreaseAjaxPending();
              },
            });
          },  
          'class': 'btn-primary'  
        },
        {
          'caption': 'No',  
          'fn': () => { },  
          'class': 'btn-secondary'  
        }
      ]
    );
  });

  $(document).on('change', '.multiple-selection', function (e) {
    let val = $(this).val();
    $('.multiple-selection').val(val);
  });

  $(document).on('click', '#vscode', function () {
    let dir = $('#current_application option:selected').attr('data-directory');
    let lnk = 'vscode://file/' + dir;
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
    let masterPrimaryKeyName = $(this).find("option:selected").attr("data-primary-key") || "";
    updateTableName(
      moduleFileName,
      moduleCode,
      moduleName,
      masterTableName,
      masterPrimaryKeyName
    );
  });

  $(document).on("click", ".button-save-application-config", function (e) {
    e.preventDefault();
    let form = $(this).closest(".modal").find('form');
    let inputs = form.serializeArray();
    let dataToPost = {
      name: form.find('[name="application_name"]').val(),
      architecture: form.find('[name="application_architecture"]').val(),
      base_application_directory: form.find('[name="application_base_directory"]').val(),
      description: form.find('[name="description"]').val(),
      database: {},
      sessions: {},
      entity_info: {},
      module_location: []
    };

    form.find('.path-manager tbody tr').each(function (e2) {
      let name = $(this).find('[name^="name"]');
      let path = $(this).find('[name^="path"]');
      let checked = $(this).find('[name^="checked"]');
      dataToPost.module_location.push({ name: name.val(), path: path.val(), active: checked[0].checked });
    });

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
    dataToPost.application_id = form.find('[name="application_id"]').val();
    updateCurrentApplivation(dataToPost);
    $('#modal-application-setting').modal('hide');
  });

  $(document).on("click", "#generate_script", function (e) {
    e.preventDefault();
    asyncAlert(
      'Do you want to generate the module and entities and replace the existing files?',  // Message to display in the modal
      'Confirmation',  
      [
        {
          'caption': 'Yes',  
          'fn': () => {
            generateScript($(".main-table tbody"));
          },  
          'class': 'btn-primary'  
        },
        {
          'caption': 'No',  
          'fn': () => { },  
          'class': 'btn-secondary'  
        }
      ]
    );
  });

  $(document).on("click", "#switch_application", function (e) {
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
      tr.find(".reference_button_filter").css("display", "none");
    }
  });

  $(document).on("change", ".input-element-type", function (e) {
    let checkedValue = $(this).attr("value");
    prepareReferenceData(checkedValue, $(this));
  });

  $(document).on("click", ".reference_button_data", function (e) {
    e.preventDefault();
    $("#modal-create-reference-data")
      .find(".modal-title")
      .text("Create Data Reference");
    $("#modal-create-reference-data").attr("data-reference-type", "data");
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
    if (value.length > 20) {
      let obj = parseJsonData(value);
      if (typeof obj != 'object') {
        obj = {};
      }
      deserializeForm(obj);
    }
    $("#modal-create-reference-data").modal("show");
  });

  $(document).on("click", ".reference_button_filter", function (e) {
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

    let value = $('[name="' + key + '"]').val().trim();
    if (value.length < 60) {
      loadReference(fieldName, key, function (obj) {
        if (obj != null) {
          deserializeForm(obj);
        }
      });
    }
    if (value.length > 20) {
      let obj = parseJsonData(value);
      if (typeof obj != 'object') {
        obj = {};
      }
      deserializeForm(obj);
    }
    $("#modal-create-reference-data").modal("show");
  });

  $(document).on("click", "#apply_reference", function (e) {
    let key = $("#modal-create-reference-data").attr("data-input-name");
    let value = JSON.stringify(serializeForm());
    $('[name="' + key + '"]').val(value);
    $("#modal-create-reference-data").modal("hide");
  });

  $(document).on("click", "#save_to_cache", function (e) {
    let fieldName = $("#modal-create-reference-data").attr("data-field-name");
    let key = $("#modal-create-reference-data").attr("data-input-name");
    let value = JSON.stringify(serializeForm());
    saveReference(fieldName, key, value);
  });

  $(document).on("click", "#load_from_cache", function (e) {
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
  $(document).on("click", ".btn-clear-group", function(e){
    let table = $(this).closest("table");
    clearGroup(table);
  });

  $(document).on("click", ".btn-add-row", function (e) {
    let table = $(this).closest("table");
    addRow(table);
  });

  $(document).on("click", ".btn-remove-row", function (e) {
    let nrow = $(this).closest("tbody").find("tr").length;
    if (nrow > 1) {
      let row = $(this).closest('tr');
      asyncAlert(
        'Do you want to remove this row?',
        'Deletion Confirmation',
        [
          {
            'caption': 'Yes',
            'fn': () => {
              row.remove();
            },
            'class': 'btn-primary'
          },
          {
            'caption': 'No',
            'fn': () => { },
            'class': 'btn-secondary'
          }
        ]
      );
    } else if (
      nrow == 1 &&
      $(this).closest("table").attr("data-empty-on-remove") == "true"
    ) {
      asyncAlert(
        'Do you want to clear this row?',
        'Confirmation',
        [
          {
            'caption': 'Yes',
            'fn': () => {
              $(this)
                .closest("tr")
                .find(":input")
                .each(function (e3) {
                  if ($(this).is(":checkbox, :radio")) {
                    $(this).prop("checked", false);
                  } else {
                    $(this).val("");
                  }
                });
            },
            'class': 'btn-primary'
          },
          {
            'caption': 'No',
            'fn': () => { },
            'class': 'btn-secondary'
          }
        ]
      );
      
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
    let createNew = $(".entity-create-new")[0].checked;
    getEntityQuery(ents, merged, createNew);
  });

  $(document).on("change", ".entity-merge", function (e) {
    let ents = getEntitySelection();
    let merged = $(".entity-merge")[0].checked;
    let createNew = $(".entity-create-new")[0].checked;
    getEntityQuery(ents, merged, createNew);
  });
  
  $(document).on("change", ".entity-create-new", function (e) {
    let ents = getEntitySelection();
    let merged = $(".entity-merge")[0].checked;
    let createNew = $(".entity-create-new")[0].checked;
    getEntityQuery(ents, merged, createNew);
  });

  $(document).on("change", ".entity-check-controll", function (e) {
    let checked = $(this)[0].checked;
    $(".entity-checkbox-query").each(function () {
      $(this)[0].checked = checked;
    });
    let ents = getEntitySelection();
    let merged = $(".entity-merge")[0].checked;
    let createNew = $(".entity-create-new")[0].checked;
    getEntityQuery(ents, merged, createNew);
  });

  $(document).on("click", "#create_new_app", function (e) {
    e.preventDefault();
    let modal = $(this).closest(".modal");
    let name = modal.find('[name="application_name"]').val().trim();
    let architecture = modal.find('[name="application_architecture"]').val().trim();
    let description = modal.find('[name="application_description"]').val().trim();
    let id = modal.find('[name="application_id"]').val().trim();
    let directory = modal.find('[name="application_directory"]').val().trim();
    let namespace = modal.find('[name="application_namespace"]').val().trim();
    let workspace_id = modal.find('[name="application_workspace_id"]').val().trim();
    let author = modal.find('[name="application_author"]').val().trim();
    let magic_app_version = modal.find('[name="magic_app_version"]').val().trim();
    let composer_online = modal.find('[name="composer_online"]').val().trim();
    let paths = [];
    $('#modal-create-application table.path-manager tbody tr').each(function () {
      let tr = $(this);
      let name = tr.find('td:nth-child(1) input[type="text"]').val();
      let path = tr.find('td:nth-child(2) input[type="text"]').val();
      let active = tr.find('td:nth-child(3) input[type="checkbox"]')[0].checked;
      paths.push({ name: name, path: path, active: active });
    });

    if (name != "" && id != "" && directory != "" && author != "") {
      increaseAjaxPending();
      $.ajax({
        method: "POST",
        url: "lib.ajax/application-create.php",
        dataType: "html",
        data: {
          id: id,
          name: name,
          architecture: architecture,
          description: description,
          directory: directory,
          namespace: namespace,
          workspace_id: workspace_id,
          author: author,
          paths: paths,
          magic_app_version: magic_app_version,
          composer_online: composer_online
        },
        success: function (data) {
          loadAllResource();
          decreaseAjaxPending();
        },
        error: function (e1, e2) {
          decreaseAjaxPending();
        }
      });
    }
    $(modal).modal('hide');
  });

  $('#modal-workspace').on('show.bs.modal', function (e) {
    resetWorkspaceSearch();
  })

  $('#modal-update-path').on('show.bs.modal', function (e) {
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/application-path.php",
      data: { action: 'get' },
      success: function (data) {
        decreaseAjaxPending();
        while ($('#modal-update-path table.path-manager > tbody > tr').length > 1) {
          $('#modal-update-path table.path-manager > tbody > tr:last').remove();
        }
        for (let d in data) {
          if (d > 0) {
            let clone = $('#modal-update-path table.path-manager > tbody > tr:first').clone();
            $('#modal-update-path table.path-manager > tbody').append(clone);
          }
          let clone2 = $('#modal-update-path table.path-manager > tbody > tr:nth-child(' + (parseInt(d) + 1) + ')');
          clone2.find('input[type="text"].location-path').val(data[d].path);
          clone2.find('input[type="text"].location-name').val(data[d].name);
          clone2.find('input[type="checkbox"]')[0].checked = data[d].active;
        }
        fixPathForm()
      },
    });
  });

  $(document).on("click", "#update_module_path", function (e) {
    e.preventDefault();
    let paths = [];
    $('#modal-update-path table.path-manager tbody tr').each(function () {
      let tr = $(this);
      let name = tr.find('td:nth-child(1) input[type="text"]').val();
      let path = tr.find('td:nth-child(2) input[type="text"]').val();
      let active = tr.find('td:nth-child(3) input[type="checkbox"]')[0].checked;
      paths.push({ name: name, path: path, active: active });
    });
    let select = $('#current_module_location');
    if (paths.length > 0) {
      increaseAjaxPending();
      $.ajax({
        method: "POST",
        url: "lib.ajax/application-path.php",
        data: { action: 'update', paths: paths },
        success: function (data) {
          decreaseAjaxPending();
          select.empty();
          for (let d in data) {
            select[0].options[select[0].options.length] = new Option(data[d].name + ' - ' + data[d].path, data[d].path);
            if (data[d].active) {
              select.val(data[d].path);
            }
          }
          while ($('#modal-update-path table.path-manager tbody tr').length > 1) {
            $('#modal-update-path table.path-manager tbody tr:last-child').remove();
          }
          $('#modal-update-path table.path-manager tbody tr input[type="text"]').val('');
        },
      });
    }
    $('#modal-update-path').modal('hide');
  });

  $(document).on('click', '#update_current_location', function (e) {
    e.preventDefault();
    let select = $('#current_module_location');
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/application-path.php",
      data: { action: 'default', selected_path: select.val() },
      success: function (data) {
        decreaseAjaxPending();
        select.empty();
        for (let d in data) {
          select[0].options[select[0].options.length] = new Option(data[d].name + ' - ' + data[d].path, data[d].path);
          if (data[d].active) {
            select.val(data[d].path);
          }
        }
      },
    });
  });

  $(document).on("click", ".entity-container-file .entity-li a", function (e) {
    e.preventDefault();
    let entity = $(this).attr("data-entity-name");
    let el = $(this);
    getEntityFile([entity], function () {
      $('.entity-container-file .entity-li').removeClass("selected-file");
      el.closest('li').addClass("selected-file");
    });
  });

  $(document).on("click", ".module-list-file .file-li a", function (e) {
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
      let createNew = $(".entity-create-new")[0].checked;
      getEntityQuery([entity], false, createNew);
    }
  );

  $(document).on("click", "#button_save_module_file", function (e) {
    e.preventDefault();
    saveModule();
  });

  $(document).on("click", "#button_save_entity_file", function (e) {
    e.preventDefault();
    saveEntity();
  });

  $(document).on("click", "#button_save_entity_file_as", function (e) {
    e.preventDefault();
    saveEntityAs();
  });

  $(document).on("click", "#button_save_entity_query", function (e) {
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

  $(document).on('click', '.add_subfix', function (e) {
    let entityName = $('.rd-entity-name').val();
    if (!entityName.endsWith('Min')) {
      entityName += 'Min';
      $('.rd-entity-name').val(entityName);
    }
  });

  $(document).on('click', '.generate_entity', function (e) {
    let entityName = $('.rd-entity-name').val();
    let tableName = $('.rd-table-name').val();

    asyncAlert(
      'Are you sure you want to generate the entity and replace the existing file?',  // Message to display in the modal
      'Entity Generation Confirmation',  
      [
        {
          'caption': 'Yes', 
          'fn': () => {
            increaseAjaxPending();
            $.ajax({
              method: "POST",
              url: "lib.ajax/entity-generator.php",
              data: { entityName: entityName, tableName: tableName },
              success: function (data) {
                decreaseAjaxPending();
                updateEntityFile();
                updateEntityQuery(true);
                updateEntityRelationshipDiagram();
              },
            });
          },  
          'class': 'btn-primary'  
        },
        {
          'caption': 'No',  
          'fn': () => { },  
          'class': 'btn-secondary'  
        }
      ]
    );
  });

  $(document).on('change', '.rd-map-key', function (e) {
    onChangeMapKey($(this));
  });

  $(document).on('keyup', '.rd-map-key', function (e) {
    onChangeMapKey($(this));
  });

  $(document).on('change', '#export_to_excel', function (e) {
    let chk = $(this)[0].checked;
    if (chk) {
      $('#export_to_csv')[0].checked = false;
      $('#export_use_temporary')[0].disabled = true;
    }
  });

  $(document).on('change', '#export_to_csv', function (e) {
    let chk = $(this)[0].checked;
    if (chk) {
      $('#export_to_excel')[0].checked = false;
      $('#export_use_temporary')[0].disabled = false;
    }
    else {
      $('#export_use_temporary')[0].disabled = true;
    }
  });

  $(document).on('change', '.entity-container-relationship .entity-checkbox', function (e) {
    loadDiagramMultiple();
  });

  $(document).on('click', '.reload-diagram', function (e) {
    loadDiagramMultiple();
  });

  $(document).on('click', '.entity-container-relationship .entity-li a', function (e) {
    e.preventDefault();
    let params = [];
    params = addDiagramOption(params);
    params.push('entity[]=' + $(this).attr('data-entity-name'));
    params.push('rnd=' + (new Date()).getTime());
    let img = $('<img />');
    img.attr('src', 'lib.ajax/entity-relationship-diagram.php?' + params.join('&'));
    $('.erd-image').empty().append(img);
    let urlMap = 'lib.ajax/entity-relationship-diagram-map.php?' + params.join('&');
    $('[name="erd-map"]').load(urlMap, function (e) {
      img.attr('usemap', '#erd-map');
    });
  });

  $(document).on('click', '.btn-move-up', function (e) {
    let row = $(this).closest('tr');
    if (row.prev().length) {
      row.insertBefore(row.prev());
    }
  });

  $(document).on('click', '.btn-move-down', function (e) {
    let row = $(this).closest('tr');
    if (row.next().length) {
      row.insertAfter(row.next());
    }
  });

  $(document).on('click', 'table.path-manager .path-remover', function (e) {
    let count = $(this).closest('tbody').find('tr').length;
    if (count > 1) {
      let row = $(this).closest('tr');
      asyncAlert(
        'Deleting this path will not remove the directory or its files. <br>Do you want to delete this path?',  // Message to display in the modal
        'Deletion Confirmation',  
        [
          {
            'caption': 'Yes',  
            'fn': () => {
              row.remove();
            },  
            'class': 'btn-primary'  
          },
          {
            'caption': 'No',  
            'fn': () => { },  
            'class': 'btn-secondary'  
          }
        ]
      );
    }
    fixPathForm();
  });

  $(document).on('click', 'table.path-manager .add-path', function (e) {
    let clone = $(this).closest('table').find('tbody tr:first').clone();
    clone.find('input[type="text"]').val('');
    clone.find('input[type="checkbox"]').removeAttr('checked');
    $(this).closest('table').find('tbody').append(clone);
    fixPathForm();
  });

  $(document).on('change', '[name="application_namespace"]', function (e) {
    let ctrl = $(this);
    let val = ctrl.val();
    if (val == 'MagicObject' || val == 'MagicApp') {
      ctrl.addClass('invalid-input');
    }
    else {
      ctrl.removeClass('invalid-input');
    }
  });

  $(document).on('click', '.create-new-application', function (e) {
    resetApplicationSearch();
    let modal = $('#modal-create-application');
    let createBtn = modal.find('#create_new_app');
    createBtn[0].disabled = true;
    $('[name="application_name"]').val('');
    $('[name="application_id"]').val('');
    $('[name="application_directory"]').val('');
    $('[name="application_workspace_id"]').val('');
    $('[name="application_namespace"]').val('');
    $('[name="application_author"]').val('');
    $('[name="magic_app_version"]').empty();
    $('[name="dependencies-installation"]').val('');  
    increaseAjaxPending();
    $.ajax({
      type: 'GET',
      url: 'lib.ajax/application-new.php',
      success: function (data) {
        decreaseAjaxPending();
        if (data.application_workspace.length == 0) {
          if (modal.find('.alert').length > 0) {
            modal.find('.alert').remove();
          }
          let alertDiv = $('<div />');
          alertDiv.addClass('alert alert-warning');
          alertDiv.html('Please select a workspace before creating a new application.');
          modal.find('form').prepend(alertDiv);
          createBtn[0].disabled = true;
        }
        else {
          $('[name="application_name"]').val(data.application_name);
          $('[name="application_id"]').val(data.application_id);
          $('[name="application_architecture"]').val(data.application_architecture);
          $('[name="application_directory"]').val(data.application_directory);
          $('[name="dependencies-installation"]').val(data.composer_online ? 'Online' : 'Offline');         
          $('[name="composer_online"]').val(data.composer_online ? 1 : 0);
          $('[name="application_namespace"]').val(data.application_namespace);
          $('[name="application_workspace_id"]').empty();
          for(let workspace of data.application_workspace)
          {
            let opt = $('<option />');
            opt.text(workspace.label);
            opt.attr('value', workspace.value);
            if(workspace.selected)
            {
              opt.attr('selected', 'selected');
            }
            $('[name="application_workspace_id"]').append(opt);
          }
          $('[name="application_author"]').val(data.application_author);
          $('[name="application_description"]').val(data.application_description);
          for (let i in data.magic_app_versions) {
            let latest = data.magic_app_versions[i]['latest'];
            $('[name="magic_app_version"]')[0].appendChild(
              new Option(data.magic_app_versions[i]['value'], data.magic_app_versions[i]['key'], latest, latest)
            );
          }
          createBtn[0].disabled = false;
        }
      }
    });
  });

  $(document).on('click', '.container-translate-entity .entity-list .entity-li a', function (e) {
    e.preventDefault();
    e.stopPropagation();
    let el = $(this);
    let entityName = $(this).attr('data-entity-name');
    currentEntity2Translated = entityName;
    translateEntity(function () {
      $('.container-translate-entity .entity-list .entity-li').removeClass("selected-file");
      el.closest('li').addClass("selected-file");
    });
  });

  $(document).on('click', '#button-save-entity-translation', function (e) {
    let translated = transEd2.getDoc().getValue();
    let entityName = $('.entity-name').val();
    let propertyNames = $('.entity-property-name').val();
    let targetLanguage = $('.target-language').val();
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/entity-translate.php",
      dataType: "json",
      data: { userAction: 'set', entityName: entityName, translated: translated, propertyNames: propertyNames, targetLanguage: targetLanguage },
      success: function (data) {
        decreaseAjaxPending();
      },
    });
  });

  $(document).on('click', '#button-save-module-translation', function (e) {
    let translated = transEd4.getDoc().getValue();
    let propertyNames = $('.module-property-name').val();
    let targetLanguage = $('.target-language').val();
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/module-translate.php",
      dataType: "json",
      data: { userAction: 'set', translated: translated, propertyNames: propertyNames, targetLanguage: targetLanguage },
      success: function (data) {
        decreaseAjaxPending();
      },
    });
  });

  $(document).on('change', '.target-language', function (e) {
    let val = $(this).val();
    let translateFor = $(this).attr('data-translate-for');
    $('.target-language').val(val);
    reloadTranslate(translateFor);
  });

  $(document).on('change', '.filter-translate', function (e) {
    let val = $(this).val();
    let translateFor = $(this).attr('data-translate-for');
    $('.filter-translate').val(val);
    reloadTranslate(translateFor);
  });

  $(document).on('change', '.select-module', function (e) {
    let checked = $(this)[0].checked;
    $(this).closest('.module-group').find('ul li').each(function (e) {
      $(this).find('input[type="checkbox"]')[0].checked = checked;
    });
    translateModule();
  });

  $(document).on('change', '.module-for-translate', function (e) {
    translateModule();
  });

  $(document).on('click', 'table.language-manager .add-language', function (e) {
    let clone = $(this).closest('table').find('tbody tr:first').clone();
    clone.find('input[type="text"]').val('');
    clone.find('input[type="checkbox"]').removeAttr('checked');
    $(this).closest('table').find('tbody').append(clone);
    fixLanguageForm();
  });

  $(document).on('click', 'table.language-manager .language-remover', function (e) {
    let count = $(this).closest('tbody').find('tr').length;
    if (count > 1) {
      let row = $(this).closest('tr');
      asyncAlert(
        'Deleting this language will not remove the directory or its files. <br>Do you want to delete this language?',  // Message to display in the modal
        'Deletion Confirmation',  // Modal title
        [
          {
            'caption': 'Yes',  
            'fn': () => {
              row.remove();
            },  
            'class': 'btn-primary'  
          },
          {
            'caption': 'No',  
            'fn': () => { },  
            'class': 'btn-secondary'  
          }
        ]
      );
    }
    fixLanguageForm();
  });

  $('#modal-update-language').on('show.bs.modal', function (e) {
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/application-language.php",
      data: { action: 'get' },
      success: function (data) {
        while ($('#modal-update-language table.language-manager > tbody > tr').length > 1) {
          $('#modal-update-language table.language-manager > tbody > tr:last').remove();
        }
        for (let d in data) {
          decreaseAjaxPending();

          if (d > 0) {
            let clone = $('#modal-update-language table.language-manager > tbody > tr:first').clone();
            $('#modal-update-language table.language-manager > tbody').append(clone);
          }
          let clone2 = $('#modal-update-language table.language-manager > tbody > tr:nth-child(' + (parseInt(d) + 1) + ')');
          clone2.find('input[type="text"].language-name').val(data[d].name);
          clone2.find('input[type="text"].language-code').val(data[d].code);
          clone2.find('input[type="checkbox"]')[0].checked = data[d].active;
        }
        fixLanguageForm();
      },
    });
  });

  $(document).on("click", "#update-application-language", function (e) {
    e.preventDefault();
    let languages = [];
    $('#modal-update-language table.language-manager tbody tr').each(function () {
      let tr = $(this);
      let name = tr.find('td:nth-child(1) input[type="text"]').val();
      let code = tr.find('td:nth-child(2) input[type="text"]').val();
      let active = tr.find('td:nth-child(3) input[type="checkbox"]')[0].checked;
      languages.push({ name: name, code: code, active: active });
    });
    let select = $('.target-language');
    if (languages.length > 0) {
      increaseAjaxPending();
      $.ajax({
        method: "POST",
        url: "lib.ajax/application-language.php",
        data: { action: 'update', languages: languages },
        success: function (data) {
          decreaseAjaxPending();
          select.empty();
          for (let d in data) {
            for (let i = 0; i < select.length; i++) //NOSONAR
            {
              select[i].options[select[i].options.length] = new Option(data[d].name + ' - ' + data[d].code, data[d].code);
              if (data[d].active) {
                select.val(data[d].code);
              }
            }
          }
          while ($('#modal-update-language table.language-manager tbody tr').length > 1) {
            $('#modal-update-language table.language-manager tbody tr:last-child').remove();
          }
          $('#modal-update-language table.language-manager tbody tr input[type="text"]').val('');
        },
      });
    }
    $('#modal-update-language').modal('hide');
  });

  $(document).on('click', '.button-execute-query', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    asyncAlert(
      'Do you want to execute the queries on the current database?',  // Message to display in the modal
      'Query Execution Confirmation',  
      [
        {
          'caption': 'Yes', 
          'fn': () => {
            let query = cmEditorSQLExecute.getDoc().getValue();
            $('.button-execute-query')[0].disabled = true;
            increaseAjaxPending();
            $.ajax({
              method: "POST",
              url: "lib.ajax/query-execute.php",
              data: { action: 'execute', query: query },
              success: function (data) {
                decreaseAjaxPending();
                let ents = getEntitySelection();
                let merged = $(".entity-merge")[0].checked;
                let createNew = $(".entity-create-new")[0].checked;
                getEntityQuery(ents, merged, createNew);
                modal.modal('hide');
              },
            });
          },  
          'class': 'btn-primary'  
        },
        {
          'caption': 'No',  
          'fn': () => { },  
          'class': 'btn-secondary'  
        }
      ]
    );

  });

  $(document).on('click', '.default-language', function (e) {
    e.preventDefault();
    let select = $('.target-language');
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/application-language.php",
      data: { action: 'default', selected_language: select.val() },
      success: function (data) {
        decreaseAjaxPending();
        select.empty();
        for (let d in data) {
          for (let i = 0; i < select.length; i++) //NOSONAR
          {
            select[i].options[select[i].options.length] = new Option(data[d].name + ' - ' + data[d].code, data[d].code);
            if (data[d].active) {
              select.val(data[d].code);
            }
          }
        }
      },
    });
  });

  $(document).on('click', 'area', function (e) {
    e.preventDefault();
    let dataType = $(this).attr('data-type');
    let request = {};
    let modalTitle = '';
    let url = '';
    if (dataType == 'area-relation') {
      url = 'lib.ajax/entity-relationship.php';
      modalTitle = 'Entity Relationship';
      let namespaceName = $(this).attr('data-namespace');
      let entityName = $(this).attr('data-entity');
      let tableName = $(this).attr('data-table-name');
      let columnName = $(this).attr('data-column-name');
      let referenceNamespaceName = $(this).attr('data-reference-namespace');
      let referenceEntityName = $(this).attr('data-reference-entity');
      let referenceTableName = $(this).attr('data-reference-table-name');
      let referenceColumnName = $(this).attr('data-reference-column-name');
      request = { dataType: dataType, namespaceName: namespaceName, entityName: entityName, tableName: tableName, columnName: columnName, referenceNamespaceName: referenceNamespaceName, referenceEntityName: referenceEntityName, referenceTableName: referenceTableName, referenceColumnName: referenceColumnName };
    }
    else {
      url = 'lib.ajax/entity-detail.php';
      modalTitle = 'Entity Detail';
      let namespaceName = $(this).attr('data-namespace');
      let entityName = $(this).attr('data-entity');
      let tableName = $(this).attr('data-table-name');
      request = { dataType: dataType, namespaceName: namespaceName, entityName: entityName, tableName: tableName };
    }

    $('.entity-detail').empty();
    $('.entity-detail').append('<div style="text-align: center;"><span class="animation-wave"><span></span></span></div>');
    $('#modal-entity-detail .modal-title').html(modalTitle);
    $('#modal-entity-detail').modal('show');
    increaseAjaxPending();
    $.ajax({
      type: 'GET',
      dataType: 'html',
      url: url,
      data: request,
      success: function (data) {
        decreaseAjaxPending();
        $('.entity-detail').empty();
        $('.entity-detail').append(data);
      }
    });
  });

  $(document).on('click', '.button-application-setting', function (e) {
    e.preventDefault();
    let updateBtn = $('#modal-application-setting .button-save-application-config');
    updateBtn[0].disabled = true;
    let applicationId = $(this).closest('.application-item').attr('data-application-id');
    $('#modal-application-setting').modal('show');
    $('#modal-application-setting .application-setting').empty();
    increaseAjaxPending();
    $.ajax({
      type: 'GET',
      url: 'lib.ajax/application-setting.php',
      data: { applicationId: applicationId },
      dataType: 'html',
      success: function (data) {
        decreaseAjaxPending();
        $('#modal-application-setting .application-setting').empty().append(data);
        setTimeout(function () {
          // set database_password to be empty
          // prevent autofill password
          $('#modal-application-setting .application-setting').find('[name="database_password"]').val('');
        }, 2000);
        updateBtn[0].disabled = false;
      }
    });
  });

  $(document).on('click', '.button-application-database', function (e) {
    e.preventDefault();
    let applicationId = $(this).closest('.application-item').attr('data-application-id');
    $('#modal-database-explorer .database-explorer').html('<iframe src="database-explorer/?applicationId=' + applicationId + '"></iframe>');
    $('#modal-database-explorer').modal('show');
  });

  $(document).on('click', '#button_explore_database', function (e) {
    e.preventDefault();
    $('#modal-database-explorer .database-explorer').html('<iframe src="database-explorer/"></iframe>');
    $('#modal-database-explorer').modal('show');
  });

  $(document).on('click', '.button-application-menu', function (e) {
    e.preventDefault();
    let updateBtn = $('#modal-application-menu .button-save-menu');
    updateBtn[0].disabled = true;
    let applicationId = $(this).closest('.application-item').attr('data-application-id');
    let modal = $('#modal-application-menu');
    modal.find('.modal-body').empty();
    modal.find('.modal-body').append('<div style="text-align: center;"><span class="animation-wave"><span></span></span></div>');
    modal.attr('data-application-id', applicationId);
    modal.modal('show');
    increaseAjaxPending();
    $.ajax({
      type: 'GET',
      url: 'lib.ajax/application-menu.php',
      data: { applicationId: applicationId },
      dataType: 'html',
      success: function (data) {
        decreaseAjaxPending();
        $('#modal-application-menu .modal-body').empty().append(data);
        updateBtn[0].disabled = false;
        initMenu();
      }
    });
  });

  $(document).on('click', '#modal-application-menu .button-save-menu', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let applicationId = modal.attr('data-application-id');
    const jsonOutput = JSON.stringify(serializeMenu());
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-menu-update.php',
      data: { applicationId: applicationId, data: jsonOutput },
      success: function (data) {
        decreaseAjaxPending();
        modal.modal('hide');
        loadMenu();
      }
    });
  });

  $(document).on('click', '#modal-application-menu .button-add-menu', function (e) {
    e.preventDefault();
    $('#new_menu').val('');
    let modal = $('#modal-application-menu-add');
    modal.modal('show');
    setTimeout(function () {
      $('#new_menu').val('');
      $('#new_menu').focus();
    }, 600);
  });

  $(document).on('click', '#modal-application-menu-add .button-apply-new-menu', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let invalidName = false;
    let newMenu = $('#new_menu').val();
    let existingMenu = serializeMenu();
    for (let i in existingMenu) {
      if (existingMenu[i].label.toLowerCase() == newMenu.toLowerCase()) {
        invalidName = true;
      }
    }

    if (!invalidName) {
      let newMenuStr = '';
      newMenuStr += '<li class="sortable-menu-item">' + "\r\n";
      newMenuStr += '<span class="sortable-icon icon-move-up" onclick="moveUp(this)"></span>' + "\r\n";
      newMenuStr += '<span class="sortable-icon icon-move-down" onclick="moveDown(this)"></span>' + "\r\n";
      newMenuStr += '<span class="sortable-icon icon-edit" onclick="editMenu(this)"></span>' + "\r\n";
      newMenuStr += '<a class="app-menu app-menu-text" href="#">' + newMenu + '</a>' + "\r\n";
      newMenuStr += '<span class="sortable-toggle-icon"></span>' + "\r\n";
      newMenuStr += "<ul class=\"sortable-submenu\">\r\n";
      newMenuStr += "</ul>\r\n";
      newMenuStr += "</li>\r\n";

      let lastMenu = $(newMenuStr);
      $('.sortable-menu').append(lastMenu);
      let icon = lastMenu.find('.sortable-toggle-icon')[0];

      icon.addEventListener('click', function () {
        this.parentNode.classList.toggle('expanded')
      });

      // Set up drag and drop for submenu items
      document.querySelectorAll('.sortable-submenu-item').forEach(item => {
        item.setAttribute('draggable', true);
        item.addEventListener('dragstart', dragStart);
        item.addEventListener('dragover', dragOver);
        item.addEventListener('drop', dropToSubmenu);
      });

      // Set up drag and drop for menu items
      lastMenu[0].addEventListener('dragover', dragOver); // Added dragover event
      lastMenu[0].addEventListener('drop', dropToMenu); // Adjusted to call dropToMenu

      modal.modal('hide');
    }
    else {
      $('#new_menu').select();
    }
  });

  $(document).on('click', '.button-workspace-scan', function (e) {
    e.preventDefault();
    let workspaceId = $(this).closest('.workspace-item').attr('data-workspace-id');
    resetWorkspaceSearch();
    increaseAjaxPending();
    $.ajax({
      type: 'GET',
      url: 'lib.ajax/workspace-scan.php',
      data: { workspaceId: workspaceId },
      success: function (data) {
        decreaseAjaxPending();
        loadAllResource();
      }
    });
  });

  $(document).on('click', '.button-workspace-default', function (e) {
    e.preventDefault();
    let workspaceId = $(this).closest('.workspace-item').attr('data-workspace-id') || '';
    resetWorkspaceSearch();
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/workspace-default.php',
      data: { workspaceId: workspaceId },
      success: function (data) {
        decreaseAjaxPending();
        onSetDefaultWorkspace();
        $('meta[name="workspace-id"]').attr('content', workspaceId);
        window.localStorage.setItem('workspace-id', workspaceId);
        resetCheckActiveWorkspace();
      }
    });
  });

  $(document).on('click', '.button-application-default', function (e) {
    e.preventDefault();
    let applicationId = $(this).closest('.application-item').attr('data-application-id') || '';
    resetApplicationSearch();
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-default.php',
      data: { applicationId: applicationId },
      success: function (data) {
        decreaseAjaxPending();
        onSetDefaultApplication();
        $('meta[name="application-id"]').attr('content', applicationId);
        window.localStorage.setItem('application-id', applicationId);
        resetCheckActiveApplication();
      }
    });
  });

  $(document).on('click', '.button-application-open', function (e) {
    e.preventDefault();
    let path = $(this).closest('.application-item').attr('data-path');
    window.location = 'vscode://file/' + path;
  });
  $(document).on('click', '.button-workspace-open', function (e) {
    e.preventDefault();
    let path = $(this).closest('.workspace-item').attr('data-path');
    window.location = 'vscode://file/' + path;
  });

  $(document).on('click', '.refresh-application-list, .refresh-workspace-list', function (e) {
    e.preventDefault();
    resetApplicationSearch();
    loadAllResource();
  });

  $(document).on('click', '.button-save-workspace', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let workspaceName = modal.find('[name="workspace_name"]').val();
    let workspaceDescription = modal.find('[name="workspace_description"]').val();
    let workspaceDirectory = modal.find('[name="workspace_directory"]').val();
    let phpPath = modal.find('[name="php_path"]').val();
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/workspace-create.php',
      data: { workspaceName: workspaceName, workspaceDirectory: workspaceDirectory, workspaceDescription: workspaceDescription, phpPath: phpPath },
      success: function (data) {
        decreaseAjaxPending();
        modal.modal('hide');
        loadAllResource();
      },
      error(e)
      {
        decreaseAjaxPending();
        console.log(e)
      }
    });
  });

  $(document).on('click', '.sortable-menu-item > a, .sortable-submenu-item > a', function (e) {
    e.preventDefault();
  });
  $(document).on('dblclick', '.sortable-menu-item > a, .sortable-submenu-item > a', function (e) {
    e.preventDefault();
    let el = $(this).siblings('.icon-edit')[0];
    editMenu(el);
  });

  $(document).on('blur', '.sortable-menu-item > input[type="text"], .sortable-submenu-item > input[type="text"]', function (e) {
    e.preventDefault();
    let input = $(this);
    let menu = $(this).siblings('a.app-menu-text');
    input.css({ display: 'none' });
    menu.text(input.val())
    menu.css({ display: '' });
  });

  $(document).on('click', '#button_execute_entity_query', function (e) {
    e.preventDefault();
    $('#modal-query-executor').find('textarea').val(cmEditorSQL.getSelection());
    cmEditorSQLExecute.setValue(cmEditorSQL.getSelection());
    cmEditorSQLExecute.refresh();
    $('.button-execute-query')[0].disabled = true;
    $('#modal-query-executor').modal('show');
  });

  $(document).on('change', 'table select[name=database_driver]', function (e) {
    let base = $(this).find('option:selected').attr('data-base');
    $(this).closest('table').find('tr.database-credential').attr('data-current-database-type', base)
  });

  $(document).on('blur keyup', 'input[type="number"]', function (e) {
    if (isNaN($(this).val()) || $(this).val().trim() === '') {
      $(this).addClass('input-invalid-value');
    } else {
      $(this).removeClass('input-invalid-value');
    }
  });

  $(document).on('click', '#test-database-connection', function (e1) {
    let table = $(this).closest('table');
    let input = { 'testConnection': 'test' };
    table.find(':input').each(function (e2) {
      if ($(this).attr('name') != undefined && $(this).attr('name') != '') {
        input[$(this).attr('name')] = $(this).val();
      }
    });
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/database-test.php',
      data: input,
      dataType: 'json',
      success: function (data) {
        decreaseAjaxPending();
        if (data.conneted1) {
          if (!data.conneted2) {
            $('#create-database').css({ 'display': 'inline' });
            showAlertUI('Database Connection Test', 'Successfully connected to the server, but database not found.');
          }
          else {
            $('#create-database').css({ 'display': 'none' });
            showAlertUI('Database Connection Test', 'Successfully connected to the database.');
          }
        }
        else {
          $('#create-database').css({ 'display': 'none' });
          showAlertUI('Database Connection Test', 'Invalid database credentials.');
        }
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
        $('#create-database').css('display', 'none');
        showAlertUI('Database Connection Test', 'There was an error connecting to the server: ' + error);
      }
    })
  });

  $(document).on('click', '#create-database', function (e1) {
    let table = $(this).closest('table');
    let input = { 'createDatabase': 'create' };
    table.find(':input').each(function (e2) {
      if ($(this).attr('name') != undefined && $(this).attr('name') != '') {
        input[$(this).attr('name')] = $(this).val();
      }
    });
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/database-create.php',
      data: input,
      dataType: 'json',
      success: function (data) {
        decreaseAjaxPending();
        if (data.conneted1) {
          if (!data.conneted2) {
            $('#create-database').css({ 'display': 'inline' });
            showAlertUI('Database Connection Test', 'Successfully connected to the server, but database creation failed.');
          }
          else {
            $('#create-database').css({ 'display': 'none' });
            showAlertUI('Database Connection Test', 'Successfully created and connected to the database.');
          }
        }
        else {
          $('#create-database').css({ 'display': 'none' });
          showAlertUI('Database Connection Test', 'Invalid database credentials.');
        }
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
        $('#create-database').css('display', 'none');
        showAlertUI('Database Connection Test', 'There was an error connecting to the server: ' + error);
      }
    })
  });

  $(document).on('click', '.use-original', function (e) {
    $('.entity-info-key').each(function (e2) {
      $(this).val($(this).attr('data-original'));
    });
  });
  $(document).on('click', '.use-indonesian', function (e) {
    $('.entity-info-key').each(function (e2) {
      $(this).val($(this).attr('data-indonesian'));
    });
  });

  $(document).on("keyup", "#search-workspace", function (e) {
    doFilterWorkspace($(this));
  });

  $(document).on("keyup", "#search-application", function (e) {
    doFilterApplication($(this));
  });

  $(document).on("click", '.button-application-icons', function () {
    let applicationId = $(this).closest('.application-item').attr('data-application-id');
    
    let el = document.querySelector('#iconFileInput');
    if(el)
    {
      el.parentNode.removeChild(el);
    }
    
    // Create dynamic input file element
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = 'image/png';  // Only accept PNG files
    inputFile.id = 'iconFileInput';
    inputFile.style.position = 'absolute';
    inputFile.style.left = '-1000000px';
    inputFile.style.top = '-1000000px';

    // Handle file selection change
    inputFile.addEventListener('change', function () {
        const selectedFile = inputFile.files[0]; // Get the selected file
        if (!selectedFile) {
            asyncAlert(
              'Please select a PNG file first.',  // Message to display in the modal
              'Notification',  
              [
                {
                  'caption': 'Close',  
                  'fn': () => {
                  },  
                  'class': 'btn-primary'  
                }
              ]
            );
        }
        else
        {
        
          // Read the file using FileReader
          const reader = new FileReader();
          reader.onload = function(event) {
              const image = new Image();
              
              image.onload = function() // NOSONAR
              {
                  // Validate image dimensions (minimum 512x512)
                  if (image.width < 512 || image.height < 512) {
                      asyncAlert(
                        'The image must be at least 512x512 pixels.',  // Message to display in the modal
                        'Notification',  
                        [
                          {
                            'caption': 'Close',  
                            'fn': () => {
                            },  
                            'class': 'btn-primary'  
                          }
                        ]
                      );
                      return;
                  }

                  const canvas = document.createElement('canvas');
                  const ctx = canvas.getContext('2d');
                  const iconSizes = [
                      { size: 16, name: "favicon-16x16.png" },
                      { size: 32, name: "favicon-32x32.png" },
                      { size: 48, name: "favicon-48x48.png" },
                      { size: 57, name: "apple-icon-57x57.png" },
                      { size: 60, name: "apple-icon-60x60.png" },
                      { size: 72, name: "apple-icon-72x72.png" },
                      { size: 76, name: "apple-icon-76x76.png" },
                      { size: 114, name: "apple-icon-114x114.png" },
                      { size: 120, name: "apple-icon-120x120.png" },
                      { size: 144, name: "apple-icon-144x144.png" },
                      { size: 152, name: "apple-icon-152x152.png" },
                      { size: 180, name: "apple-icon-180x180.png" },
                      { size: 192, name: "android-icon-192x192.png" },
                      { size: 512, name: "android-icon-512x512.png" }
                  ];

                  // Generate icons for each size
                  iconSizes.forEach(icon => {
                      canvas.width = icon.size;
                      canvas.height = icon.size;
                      ctx.clearRect(0, 0, canvas.width, canvas.height);
                      ctx.drawImage(image, 0, 0, image.width, image.height, 0, 0, icon.size, icon.size);
                      const dataUrl = canvas.toDataURL('image/png');

                      // Send each PNG icon to the server
                      sendIconPngToServer(applicationId, dataUrl, icon.name);
                  });

                  // Additional step: Generate favicon.ico
                  generateFaviconICO(applicationId, image);
              };
            
              // Load the image data
              image.src = event.target.result;
          };

          // Read the image as a data URL
          reader.readAsDataURL(selectedFile);
        }
    });
    

    // Trigger input file dialog
    inputFile.click();  // Open file selection dialog
  });
  
  $(document).on('click', '.button-open-file', function(e1){
    let el = document.querySelector('#sqlFileInput');
    if(el)
    {
      el.parentNode.removeChild(el);
    }
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = '.sql';  
    inputFile.id = 'sqlFileInput';
    inputFile.style.position = 'absolute';
    inputFile.style.left = '-1000000px';
    inputFile.style.top = '-1000000px';
    document.querySelector('body').appendChild(inputFile);
    inputFile.addEventListener('change', function handleFileSelect(event) {
      const file = event.target.files[0]; 
      if (file) 
      {
        const reader = new FileReader();
        reader.onload = function(e2) {
          const content = e2.target.result; 
          inputFile.parentNode.removeChild(inputFile);
          cmEditorSQLExecute.getDoc().setValue(content);
          cmEditorSQLExecute.refresh();
        };
        reader.readAsText(file);
      }
      else
      {
        inputFile.parentNode.removeChild(inputFile);
      }
    });
    inputFile.click();
  });

  let val1 = $('meta[name="workspace-id"]').attr('content') || '';
  let val2 = $('meta[name="application-id"]').attr('content') || '';
  window.localStorage.setItem('workspace-id', val1);
  window.localStorage.setItem('application-id', val2);
  loadAllResource();
  resetCheckActiveWorkspace();
  resetCheckActiveApplication();
  loadReferenceResource();
};

function loadReferenceResource()
{
  increaseAjaxPending();
  $.ajax({
    type: 'GET',
    url: 'lib.ajax/reference.min.html',
    success: function(data){
      referenceResource = data;
      decreaseAjaxPending();
    },
    error: function(e)
    {
      decreaseAjaxPending();
    }
  });
}



/**
 * Generates a favicon.ico by creating multiple icon sizes (16x16, 32x32, 48x48) 
 * and sending them to the server to create a single ICO file.
 * 
 * @param {string} applicationId - The unique identifier for the application. 
 *                                  This ID will be associated with the uploaded favicon.
 * @param {HTMLImageElement} image - The image to generate the favicon from. 
 *                                    This image is used to create the different icon sizes.
 */
function generateFaviconICO(applicationId, image) {
  const sizes = [
    16, 
    32, 
    48
  ];  // Favicon sizes for ICO (can include more if necessary)
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  const iconImages = [];

  sizes.forEach(size => {
      canvas.width = size;
      canvas.height = size;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(image, 0, 0, image.width, image.height, 0, 0, size, size);
      
      // Push the canvas data for each size
      const dataUrl = canvas.toDataURL('image/png');
      iconImages.push(dataUrl);
  });

  // After generating the individual icon sizes, send them to the server to create the .ico file
  sendIconToServer(applicationId, iconImages, 'favicon.ico');
}


function doFilterWorkspace(elem)
{
  let searchValue = $(elem).val().toLowerCase().trim();
  $(".workspace-card > div").filter(function () {
    $(this).toggle(
      $(this)
        .find(".card-title")
        .text()
        .toLowerCase()
        .includes(searchValue)
    );
  });
}
function doFilterApplication(elem)
{
  let searchValue = $(elem).val().toLowerCase().trim();
    $(".application-card > div").filter(function () {
      $(this).toggle(
        $(this)
          .find(".card-title")
          .text()
          .toLowerCase()
          .includes(searchValue)
      );
    });
}

let toCheckActiveWorkspace = setInterval('', 10000000);
let toCheckActiveApplication = setInterval('', 10000000);
let checkIntervalWorkspace = 10000;
let checkIntervalApplication = 12000;

/**
 * Resets and initializes a periodic check for active workspace changes.
 * Compares the current workspace ID in localStorage with the meta tag value,
 * and reloads resources if they differ.
 */
function resetCheckActiveWorkspace() {
  clearInterval(toCheckActiveWorkspace);
  toCheckActiveWorkspace = setInterval(function () {
    let val1 = window.localStorage.getItem('workspace-id') || '';
    let val2 = $('meta[name="workspace-id"]').attr('content');
    if (val1 != '' && val2 != '' && val2 != val1) {
      loadAllResource();
    }
  }, checkIntervalWorkspace);
}

/**
 * Resets and initializes a periodic check for active application changes.
 * This function compares the current application ID stored in `localStorage` 
 * with the value in the `application-id` meta tag. If the IDs differ, it triggers 
 * the `loadAllResource` function to reload all resources.
 * 
 * The check is performed every 22 seconds.
 */
function resetCheckActiveApplication() {
  clearInterval(toCheckActiveApplication);
  toCheckActiveApplication = setInterval(function () {
    let val1 = window.localStorage.getItem('application-id') || '';
    let val2 = $('meta[name="application-id"]').attr('content');
    if (val1 != '' && val2 != '' && val2 != val1) {
      loadAllResource();
    }
  }, checkIntervalApplication);
}

/**
 * Loads the list of workspaces via an AJAX request and updates the UI.
 *
 * This function makes a GET request to fetch the list of workspaces from 
 * the server. The returned data is used to update the `.workspace-card` element.
 * It also identifies the currently selected workspace, updates the `workspace-id`
 * in `localStorage`, and sets the `workspace-id` meta tag with the selected value.
 */
function loadWorkspaceList() {
  increaseAjaxPending();
  $.ajax({
    type: 'GET',
    url: 'lib.ajax/workspace-list.php',
    success: function (data) {
      decreaseAjaxPending();
      $('.workspace-card').empty().append(data);
      let val1 = $('.workspace-item[data-selected="true"]').attr('data-workspace-id') || '';
      window.localStorage.setItem('workspace-id', val1);
      $('meta[name="workspace-id"]').attr('content', val1);
    }
  });
}

/**
 * Loads the list of applications via an AJAX request and updates the UI.
 *
 * This function makes a GET request to fetch the list of applications from 
 * the server. The returned data is used to update the `.application-card` element.
 * It also identifies the currently selected application, updates the `application-id`
 * in `localStorage`, and sets the `application-id` meta tag with the selected value.
 */
function loadApplicationList() {
  increaseAjaxPending();
  $.ajax({
    type: 'GET',
    url: 'lib.ajax/application-list.php',
    success: function (data) {
      decreaseAjaxPending();
      $('.application-card').empty().append(data);
      let val1 = $('.application-item[data-selected="true"]').attr('data-application-id') || '';
      window.localStorage.setItem('application-id', val1);
      $('meta[name="application-id"]').attr('content', val1);
    }
  });
}

/**
 * Loads the list of applications via an AJAX request and updates the UI.
 *
 * This function makes a GET request to fetch the list of applications from 
 * the server. The returned data is used to update the `.application-card` element.
 * It identifies the currently selected application, updates the `application-id`
 * in `localStorage`, and sets the `application-id` meta tag with the selected value.
 */
function loadLanguageList() {
  increaseAjaxPending();
  $.ajax({
    type: 'GET',
    url: 'lib.ajax/application-path-list.php',
    dataType: 'json',
    success: function (data) {
      decreaseAjaxPending();
      $('[name="current_module_location"]').empty();
      for (let i in data) {
        $('[name="current_module_location"]')[0].append(new Option(data[i].name + ' - ' + data[i].path, data[i].path, data[i].active, data[i].active))
      }
    }
  });
}

/**
 * Loads the list of application languages or paths via an AJAX request.
 *
 * This function makes a GET request to fetch data from 
 * `lib.ajax/application-language-list.php`. The response is expected to be in JSON format. 
 * The retrieved data is then passed to the `setLanguage` function for further processing.
 */
function loadPathList() {
  increaseAjaxPending();
  $.ajax({
    type: 'GET',
    url: 'lib.ajax/application-language-list.php',
    dataType: 'json',
    success: function (data) {
      decreaseAjaxPending();
      setLanguage(data);
    }
  });
}

/**
 * Updates the SQL query editor by clearing its content and execution results.
 *
 * This function calls `clearEditorSQL` to clear the SQL editor's current content 
 * and `clearEditorSQLExecute` to clear the execution results of the SQL query. 
 * It ensures that the editor is reset to a clean state.
 */
function updateQuery()
{
  clearEditorSQL();
  clearEditorSQLExecute();
}

/**
 * Loads all necessary resources for the application.
 *
 * This function sequentially calls other functions to load:
 * - Workspace list
 * - Application list
 * - Tables
 * - Paths
 * - Languages
 * - Menus
 * It also updates queries, entity diagrams, and files, and initializes tooltips.
 */
function loadAllResource() {
  loadWorkspaceList();
  loadApplicationList();
  loadPathList();
  loadLanguageList();
  
  loadMenu();
  loadTable();
  updateEntityQuery(false);
  
  updateEntityRelationshipDiagram();
  updateEntityFile();
  updateModuleFile();
  initTooltip();
  
}

/**
 * Sets the default workspace and loads the necessary resources.
 *
 * This function reloads the workspace list, application list, tables, and menus.
 * It also updates entity queries, diagrams, files, and initializes tooltips for the default workspace.
 */
function onSetDefaultWorkspace() {
  loadAllResource();
}

/**
 * Sets the default application and loads the necessary resources.
 *
 * This function reloads the application list, tables, and menus. It also updates 
 * entity queries, diagrams, files, and initializes tooltips for the default application.
 */
function onSetDefaultApplication() {
  loadApplicationList();
  loadPathList();
  loadLanguageList();
  loadMenu();
  loadTable();
  updateEntityQuery(false);
  updateEntityRelationshipDiagram();
  updateEntityFile();
  updateModuleFile();
  initTooltip();
}

/**
 * Updates resources when a new module is created.
 *
 * This function updates entity queries, diagrams, and files, and reinitializes tooltips 
 * to reflect the changes caused by the creation of a new module.
 */
function onModuleCreated() {
  updateEntityQuery(false);
  updateEntityRelationshipDiagram();
  updateEntityFile();
  updateModuleFile();
  initTooltip();
}

/**
 * Clears the content of the entity relationship diagram (ERD).
 *
 * This function empties the `.erd-image` element and the `[name="erd-map"]` element 
 * to reset the ERD display.
 */
function updateErd()
{
  $('.erd-image').empty();
  $('[name="erd-map"]').empty();
}

/**
 * Initializes tooltips for elements with the `data-toggle="tooltip"` attribute 
 * or areas inside an element with `name="erd-map"`. 
 *
 * This function listens for `mouseenter` and `mouseleave` events to show and hide 
 * tooltips. It dynamically positions the tooltip based on mouse movement to ensure 
 * that the tooltip does not go off-screen. The tooltip's content is fetched from 
 * the `data-title` or `title` attribute of the element.
 */
function initTooltip() {
  $(document).on('mouseenter', '[name="erd-map"] area, [data-toggle="tooltip"]', function (e) {
    let tooltipText = $(this).attr('data-title') || $(this).attr('title');  // Get the tooltip text
    let tooltip = $('<div class="tooltip"></div>').html(tooltipText); // Create the tooltip
    let isHtml = $(this).attr('data-html') == 'true';

    // Append the tooltip to the body and make it visible
    $('body').append(tooltip);

    // Show the tooltip when the area element is hovered
    tooltip.addClass('visible');
    if (isHtml) {
      tooltip.addClass('multiline');
    }

    // Calculate tooltip position based on the cursor coordinates
    $(document).on('mousemove', function (e) {
      let tooltipWidth = tooltip.outerWidth();
      let tooltipHeight = tooltip.outerHeight();

      // Determine the position of the tooltip to avoid it going off-screen
      let mouseX = e.pageX + 15; // Right offset
      let mouseY = e.pageY + 15; // Bottom offset

      // Check if the tooltip exceeds the window width and adjust if necessary
      if (mouseX + tooltipWidth > $(window).width()) {
        mouseX = e.pageX - tooltipWidth - 15; // Position it to the left if it goes off the right
      }

      // Check if the tooltip exceeds the window height and adjust if necessary
      if (mouseY + tooltipHeight > $(window).height()) {
        mouseY = e.pageY - tooltipHeight - 15; // Position it to the top if it goes off the bottom
        if(mouseY < 16)
        {
          mouseY = 16;
        }
      }
      
      // Update the position of the tooltip based on the cursor position
      tooltip.css({
        left: mouseX,
        top: mouseY
      });
    });
  });

  $(document).on('mouseleave', '[name="erd-map"] area, [data-toggle="tooltip"]', function (e) {
    // Remove the tooltip when the mouse leaves the area
    $('.tooltip').remove();

    // Remove the mousemove event from the document to avoid unnecessary event listeners
    $(document).off('mousemove');
  });
}

/**
 * Displays a custom modal with dynamic buttons and message.
 *
 * This function creates and displays a modal with the specified message, title, 
 * and a set of dynamic buttons. Each button is associated with a caption, a class 
 * (optional), and a callback function that is executed when the button is clicked.
 * The modal also listens for the `hidden.bs.modal` event to execute a provided callback 
 * when the modal is closed.
 *
 * @param {string} message The content/message to be displayed inside the modal.
 * @param {string} title The title to be displayed at the top of the modal.
 * @param {Array} buttons An array of button objects, each containing:
 * - caption: The text to display on the button.
 * - class: (Optional) The CSS class to apply to the button (defaults to 'btn-secondary').
 * - fn: The callback function to execute when the button is clicked.
 * @param {Function} [onHideCallback] (Optional) A callback function to be executed when the modal is hidden.
 *
 * @returns {Promise} A promise that resolves when a button is clicked, with the button's caption as the resolved value.
 */
function showModal(message, title, buttons, onHideCallback) {
  return new Promise((resolve, reject) => {
    const modal = $('#customAlert');
    const alertOverlay = $('#alertOverlay');
    const alertMessage = $('#alertMessage');
    const alertTitle = $('#alertTitle');
    const modalFooter = $('#modalFooter');

    // Clear previous buttons in the modal footer
    modalFooter.empty();

    // Display modal and alertOverlay
    alertOverlay.show();
    modal.modal('show');

    // Set the modal message and title
    alertMessage.html(message);
    alertTitle.html(title);

    // Dynamically create buttons
    buttons.forEach(button => {
      const buttonElement = $('<button>')
        .addClass(`btn ${button.class || 'btn-secondary'}`)  // Default to 'btn-secondary' if no class is provided
        .text(button.caption)
        .on('click', () => {
          modal.modal('hide');
          alertOverlay.hide();
          button.fn();  // Execute the callback for this button
          resolve(button.caption);  // Resolve promise with the caption of the clicked button
        });
      modalFooter.append(buttonElement);
    });

    // Add a listener for when the modal is hidden (after it is closed)
    modal.on('hidden.bs.modal', () => {
      if (onHideCallback) {
        onHideCallback(); // Execute the callback when modal is closed
      }
    });
  });
}

/**
 * Displays a prompt modal with a text input field and dynamic buttons.
 *
 * This function creates and displays a modal with a message, a title, a text input field, 
 * and a set of dynamic buttons. The user can enter a value into the input field and click one 
 * of the buttons to submit the value or cancel the action. The modal also listens for the 
 * `hidden.bs.modal` event to execute a provided callback when the modal is closed.
 *
 * @param {string} message The content/message to be displayed inside the modal.
 * @param {string} title The title to be displayed at the top of the modal.
 * @param {Array} buttons An array of button objects, each containing:
 * - caption: The text to display on the button.
 * - class: (Optional) The CSS class to apply to the button (defaults to 'btn-secondary').
 * - fn: The callback function to execute when the button is clicked.
 * @param {string} initialValue The initial value to be displayed in the input field (optional).
 * @param {Function} [onHideCallback] (Optional) A callback function to be executed when the modal is hidden.
 *
 * @returns {Promise} A promise that resolves with the value entered in the input field when the 'OK' button is clicked, 
 * or the caption of any other clicked button.
 */
function asyncPrompt(message, title, buttons, initialValue, onHideCallback) {
  return new Promise((resolve, reject) => {
    const modal = $('#customAlert');
    const alertOverlay = $('#alertOverlay');
    const alertMessage = $('#alertMessage');
    const alertTitle = $('#alertTitle');
    const modalFooter = $('#modalFooter');

    // Clear previous buttons in the modal footer
    modalFooter.empty();

    // Create an input element for the user to enter text
    const inputElement = $('<input>')
      .addClass('form-control')  // Bootstrap class for styling input
      .addClass('prompt-input')
      .attr('type', 'text')
      .attr('placeholder', 'Enter your text here')
      .val(initialValue);  // Optional: Set a default value if needed

    // Display modal and alertOverlay
    alertOverlay.show();
    modal.modal('show');

    // Set the modal message and title
    let messageDom = $('<div />').addClass('input-label').text(message);
    alertMessage.empty().append(messageDom);
    alertMessage.append(inputElement);
    alertTitle.html(title);

    // Dynamically create buttons
    buttons.forEach(button => {
      const buttonElement = $('<button>')
        .addClass(`btn ${button.class || 'btn-secondary'}`)  // Default to 'btn-secondary' if no class is provided
        .text(button.caption)
        .on('click', () => {
          modal.modal('hide');
          alertOverlay.hide();
          if (button.caption === 'OK') {
            resolve(inputElement.val());  // Resolve promise with the value of the input field
          } else {
            resolve(button.caption);  // In case of other buttons, resolve with their caption
          }
          button.fn();  // Execute the callback for this button
        });
      modalFooter.append(buttonElement);
    });

    // Add a listener for when the modal is hidden (after it is closed)
    modal.on('hidden.bs.modal', () => {
      if (onHideCallback) {
        onHideCallback(); // Execute the callback when modal is closed
      }
    });
  });
}

/**
 * Displays an alert modal with dynamic buttons and waits for the user's interaction.
 * 
 * This function shows a modal with a message, title, and dynamically created buttons. It waits for the user to press one of the buttons and resolves the action. 
 * Once the modal is closed, it hides the overlay and restores the overflow styles of the modal.
 *
 * @param {string} message The content/message to display in the alert.
 * @param {string} title The title to display at the top of the alert modal.
 * @param {Array} buttons An array of button objects, each containing:
 * - caption: The text to display on the button.
 * - class: (Optional) The CSS class to apply to the button (defaults to 'btn-secondary').
 * - fn: The callback function to execute when the button is clicked.
 *
 * @returns {Promise} A promise that resolves with the caption of the button the user clicks.
 */
async function asyncAlert(message, title, buttons) {
  const result = await showModal // NOSONAR
  (
    message,
    title,
    buttons,
    function () {
      $('#alertOverlay').css({ 'display': 'none' });
      $('.modal').css({ 'overflow': '', 'overflow-y': 'auto' })
    }
  );
}

/**
 * Prompts the user for input using the `asyncPrompt` function and returns the user's input.
 *
 * This function displays a prompt modal with a message, title, and initial input value. It also 
 * takes dynamic buttons and returns the value entered by the user when they click the 'OK' button, 
 * or the caption of the button clicked if other than 'OK'. Once the modal is closed, it hides the 
 * overlay and restores the overflow styles of the modal.
 *
 * @param {string} message The content/message to display in the prompt.
 * @param {string} title The title to display at the top of the prompt modal.
 * @param {Array} buttons An array of button objects, each containing:
 * - caption: The text to display on the button.
 * - class: (Optional) The CSS class to apply to the button (defaults to 'btn-secondary').
 * - fn: The callback function to execute when the button is clicked.
 * @param {string} initialValue The initial value to be displayed in the input field (optional).
 *
 * @returns {Promise} A promise that resolves with the value entered in the input field when the 'OK' button is clicked, 
 * or the caption of any other clicked button.
 */
async function getUserInput(message, title, buttons, initialValue) {
  const result = await asyncPrompt // NOSONAR
  (
    message,
    title,
    buttons,
    initialValue,
    function () {
      $('#alertOverlay').css({ 'display': 'none' });
      $('.modal').css({ 'overflow': '', 'overflow-y': 'auto' })
    }
  );
}

let timeoutEditMenu = setTimeout('', 100);

/**
* Initializes the sortable menu by setting up event listeners
* for toggle icons, drag-and-drop functionality for both
* submenu items and menu items, and a button for serialization.
*/
function initMenu() {
  // Add click event to toggle the display of submenus
  document.querySelectorAll('.sortable-toggle-icon').forEach(icon => {
    icon.addEventListener('click', function () {
      this.parentNode.classList.toggle('expanded')

    });
  });

  // Set up drag and drop for submenu items
  document.querySelectorAll('.sortable-submenu-item').forEach(item => {
    item.setAttribute('draggable', true);
    item.addEventListener('dragstart', dragStart);
    item.addEventListener('dragover', dragOver);
    item.addEventListener('drop', dropToSubmenu);
  });

  document.querySelectorAll('.icon-move-down').forEach(item => {
    item.setAttribute('draggable', true);
    item.addEventListener('dragstart', dragStart);
  });

  // Set up drag and drop for menu items
  document.querySelectorAll('.sortable-menu-item').forEach(item => {
    item.addEventListener('dragover', dragOver); // Added dragover event
    item.addEventListener('drop', dropToMenu); // Adjusted to call dropToMenu
  });
}

/**
 * Enables inline editing of a menu item by replacing its text with an input field.
 *
 * This function transforms the menu item's text into an editable input field. 
 * When invoked, it finds the text associated with the menu item, hides it, and replaces it with a text input field 
 * that contains the current text. The input field is automatically focused and selected for easy editing.
 * After the input field is populated with the menu's current text, the user can modify it.
 *
 * @param {HTMLElement} el The menu item element (typically a button or link) that triggered the edit action.
 *
 * @returns {void} This function does not return any value. It modifies the DOM by replacing the text with an input field.
 */
function editMenu(el) {
  let elem = $(el);
  let parent = elem.closest('li');
  let menu = elem.siblings('.app-menu-text');
  if (parent.find('input')) {
    parent.find('input').remove();
  }
  let input = $('<input />');
  input.attr({ type: 'text', class: 'form-control' });
  menu.css('display', 'none');
  menu.before(input)
  input.val(menu.text());
  input.focus();
  input.select();
  input.focus();
}

let draggedItem = null;

/**
 * Handles the start of a drag event for sortable submenu items.
 *
 * This function is triggered when a drag operation begins on an element. It checks if the dragged element 
 * is a valid submenu item and sets up the `draggedItem` for the drag operation. It also ensures that the 
 * drag operation is allowed and sets the `effectAllowed` to 'move', indicating that the item will be moved during the drag.
 *
 * The function also supports cases where the target element is a child of a valid draggable element, 
 * by traversing the DOM to find the closest parent that is a valid sortable item.
 *
 * @param {DragEvent} e The drag event triggered by the user. It contains information about the element being dragged.
 * 
 * @returns {void} This function does not return any value. It sets up the dragged element and the drag effect.
 */
function dragStart(e) {
  if (e.target.classList.contains('sortable-submenu-item')) {
    draggedItem = e.target;
    e.dataTransfer.effectAllowed = 'move';
  }
  else {
    let target = e.target.closest('.sortable-submenu-item');
    if (target != null) {
      draggedItem = target;
      if (target.dataTransfer) {
        target.dataTransfer.effectAllowed = 'move';
      }
    }
  }
}

/**
 * Handles the dragover event to allow an element to be a valid drop target.
 *
 * This function is triggered during the drag operation when an element is being dragged over a potential drop target. 
 * The `e.preventDefault()` method is called to prevent the default behavior, allowing the drop event to be triggered 
 * later. Without this, the drop action will not be allowed.
 *
 * @param {DragEvent} e The drag event triggered when the dragged item is over a potential drop target.
 * 
 * @returns {void} This function does not return any value. It prevents the default action to allow the drop event.
 */
function dragOver(e) {
  e.preventDefault();
}

/**
 * Handles the drop event to place a dragged item into a menu or submenu.
 * 
 * This function is triggered when an item is dropped into a valid drop target. 
 * It first checks if there is an item being dragged, then determines the 
 * appropriate drop target based on its class and structure. It supports adding 
 * the dragged item to either a top-level menu item or a submenu, and handles 
 * the expansion of the target menu items.
 * 
 * @param {DragEvent} e The drop event triggered when an item is dropped onto a target.
 * 
 * @returns {void} This function does not return any value. It modifies the DOM 
 * by adding the dragged item to the target menu or submenu.
 */
function dropToMenu(e) {
  e.preventDefault(); // Prevent default behavior to allow the drop

  if (draggedItem) { // Check if there is an item being dragged

    // If the target has no classes
    if (e.target.classList.length === 0) {
      let target = e.target.closest('.sortable-menu-item'); // Find the closest element that is a menu item
      if (target) {
        target.querySelector('.sortable-submenu').appendChild(draggedItem); // Add the dragged item to the submenu
        target.classList.remove('expanded'); // Remove the expanded class
        target.classList.add('expanded'); // Add the expanded class
      }
    }

    // If the target is a menu item
    if (e.target.classList.contains('sortable-menu-item')) {
      // Check if a submenu already exists
      if (!e.target.querySelector('.sortable-submenu')) {
        // If not, create a new submenu
        let sm = document.createElement('ul'); // Create a new ul element
        sm.classList.add('sortable-submenu'); // Add the sortable-submenu class
        e.target.appendChild(sm); // Add the submenu to the menu item
      }
      // Add the dragged item to the submenu
      e.target.querySelector('.sortable-submenu').appendChild(draggedItem);
      e.target.classList.remove('expanded'); // Remove the expanded class
      e.target.classList.add('expanded'); // Add the expanded class
    }

    // If the target is a submenu or move icon
    if (e.target.classList.contains('sortable-submenu') || e.target.classList.contains('icon-move')) {
      // Add the dragged item to the existing submenu
      e.target.appendChild(draggedItem);
      e.target.parentNode.classList.remove('expanded'); // Remove the expanded class from the parent
      e.target.parentNode.classList.add('expanded'); // Add the expanded class to the parent
    }

    draggedItem = null; // Clear the dragged item after the drop
  }
}

/**
* Handles dropping items into submenu items.
* @param {Event} e - The drop event.
*/
function dropToSubmenu(e) {
  e.preventDefault();
  if (e.target.classList.contains('sortable-submenu-item')) {
    const isAfter = e.offsetY > (e.target.offsetHeight / 2);
    if (isAfter) {
      e.target.parentNode.insertBefore(draggedItem, e.target.nextSibling);
    } else {
      e.target.parentNode.insertBefore(draggedItem, e.target);
    }
  } else if (e.target.classList.contains('sortable-submenu')) {
    e.target.appendChild(draggedItem);
  }
}

/**
* Serializes the current menu structure into a JSON format.
* @returns {Array} - An array representing the menu structure.
*/
function serializeMenu() {
  const menu = [];
  const menuItems = document.querySelectorAll('.sortable-menu-item');
  menuItems.forEach(menuItem => {
    const menuData = {
      label: menuItem.querySelector('a.app-menu-text').textContent,
      submenus: []
    };
    const submenuItems = menuItem.querySelectorAll('.sortable-submenu-item');
    submenuItems.forEach(submenuItem => {
      menuData.submenus.push({
        label: submenuItem.querySelector('a.app-menu-text').textContent,
        link: submenuItem.querySelector('a.app-menu-text').getAttribute('href')
      });
    });
    menu.push(menuData);
  });
  return menu; // Return the constructed menu array
}

/**
 * Moves the specified item up in the list (either a menu item or a submenu item).
 * 
 * This function moves the target item (either a menu or submenu item) up by one 
 * position in the DOM relative to its previous sibling. If the item is already at the 
 * top, it remains in place.
 *
 * @param {HTMLElement} element The DOM element that is clicked to trigger the move up.
 * 
 * @returns {void} This function modifies the DOM by moving the item up, it does not return any value.
 */
function moveUp(element) {
  const item = element.closest('.sortable-submenu-item') || element.closest('.sortable-menu-item');
  const prevItem = item.previousElementSibling;
  if (prevItem) {
    item.parentNode.insertBefore(item, prevItem);
  }
}

/**
 * Moves the specified item down in the list (either a menu item or a submenu item).
 * 
 * This function moves the target item (either a menu or submenu item) down by one 
 * position in the DOM relative to its next sibling. If the item is already at the 
 * bottom, it remains in place.
 *
 * @param {HTMLElement} element The DOM element that is clicked to trigger the move down.
 * 
 * @returns {void} This function modifies the DOM by moving the item down, it does not return any value.
 */
function moveDown(element) {
  const item = element.closest('.sortable-submenu-item') || element.closest('.sortable-menu-item');
  const nextItem = item.nextElementSibling;
  if (nextItem) {
    item.parentNode.insertBefore(nextItem, item);
  }
}

/**
 * Reloads translations based on the specified context.
 *
 * This function determines the appropriate translation function to call
 * based on the provided `translateFor` argument. It supports two contexts:
 * 
 * - "module": Calls the `translateModule` function to handle module translations.
 * - "entity": Calls the `translateEntity` function to handle entity translations.
 *
 * @param {string} translateFor - The context for translation, which can be
 *                                either "module" or "entity".
 * @returns {void} This function does not return a value.
 */
function reloadTranslate(translateFor) {
  if (translateFor == "module") {
    translateModule();
  }
  else if (translateFor == "entity") {
    translateEntity();
  }
}

/**
 * Translates the specified entity and updates the UI with the results.
 *
 * This function retrieves translations for the current entity using an
 * AJAX POST request to `lib.ajax/entity-translate.php`. It collects the
 * original text, translated text, and property names from the response
 * and updates the relevant UI elements accordingly.
 *
 * If a callback function is provided, it is invoked after the translation
 * process is completed.
 *
 * @param {function} [clbk] - An optional callback function to be called
 *                            after the translation process is complete.
 * @returns {void} This function does not return a value.
 */
function translateEntity(clbk) {
  entityName = currentEntity2Translated; //NOSONAR
  if (entityName != '') {
    let targetLanguage = $('.target-language').val();
    let filter = $('.filter-translate').val();
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/entity-translate.php",
      dataType: "json",
      data: { userAction: 'get', entityName: entityName, targetLanguage: targetLanguage, filter: filter },
      success: function (data) {
        decreaseAjaxPending();
        let textOut1 = [];
        let textOut2 = [];
        let propertyNames = [];
        for (let i in data) {
          textOut1.push(data[i].original);
          textOut2.push(data[i].translated);
          propertyNames.push(data[i].propertyName);
        }
        transEd1.getDoc().setValue(textOut1.join('\r\n'));
        transEd2.getDoc().setValue(textOut2.join('\r\n'));
        $('.entity-property-name').val(propertyNames.join('|'));
        $('.entity-name').val(entityName);
        focused = {}; //NOSONAR
        transEd1.removeLineClass(lastLine1, 'background', 'highlight-line');
        transEd2.removeLineClass(lastLine1, 'background', 'highlight-line');
        lastLine1 = -1; //NOSONAR
      }
    });
  }
  if (typeof clbk != 'undefined') {
    clbk();
  }
}

/**
 * Translates selected modules and updates the UI with the results.
 *
 * This function collects checked modules from the UI and sends them
 * to the server via an AJAX POST request to `lib.ajax/module-translate.php`.
 * It retrieves the original and translated texts and updates the relevant
 * UI elements accordingly.
 *
 * The translations are displayed in designated text editors, and the
 * corresponding property names are also set in the input fields.
 *
 * @returns {void} This function does not return a value.
 */
function translateModule() {
  let translated = null;
  let propertyNames = null;
  let targetLanguage = $('.target-language').val();
  let filter = $('.filter-translate').val();
  let modules = [];

  $('.module-for-translate').each(function (e) {
    let checked = $(this)[0].checked;
    if (checked) {
      modules.push($(this).val());
    }
  });
  increaseAjaxPending();
  $.ajax({
    method: "POST",
    url: "lib.ajax/module-translate.php",
    dataType: "json",
    data: { userAction: 'get', modules: modules, translated: translated, propertyNames: propertyNames, targetLanguage: targetLanguage, filter: filter },
    success: function (data) {
      decreaseAjaxPending();
      let textOut1 = [];
      let textOut2 = [];
      let propertyNames = [];
      for (let i in data) {
        textOut1.push(data[i].original);
        textOut2.push(data[i].translated);
        propertyNames.push(data[i].propertyName);
      }
      transEd3.getDoc().setValue(textOut1.join('\r\n'));
      transEd4.getDoc().setValue(textOut2.join('\r\n'));
      $('.module-property-name').val(propertyNames.join('|'));
      focused = {};
      transEd3.removeLineClass(lastLine2, 'background', 'highlight-line');
      transEd4.removeLineClass(lastLine2, 'background', 'highlight-line');
      lastLine2 = -1; //NOSONAR
    },
  });
}


/**
 * Sends the generated icon to the server for storage.
 * 
 * @param {string} applicationId - The ID of the application associated with the icon.
 * @param {string} dataUrl - The base64 encoded image data of the icon.
 * @param {string} iconName - The name of the icon file (e.g., "favicon-16x16.png").
 * 
 * This function creates a FormData object, appends the application ID, base64 image data, and icon name to it, 
 * and sends it to the server using a POST request. The server endpoint is expected to handle the image upload 
 * and return a success or error response.
 */
function sendIconPngToServer(applicationId, dataUrl, iconName) {
  // Create a new FormData object to send to the server
  const formData = new FormData();
  
  // Append the application ID to the FormData object
  formData.append('application_id', applicationId);
  
  // Append the base64 encoded image data (icon) to the FormData object
  formData.append('image', dataUrl);  // base64 image data
  
  // Append the icon name (e.g., "favicon-16x16.png") to the FormData object
  formData.append('icon_name', iconName);

  increaseAjaxPending();

  // Send the FormData to the server using the Fetch API
  fetch('lib.ajax/application-icons.php', {
      method: 'POST',  // Specify that this is a POST request
      body: formData    // Attach the FormData object to the body of the request
  })
  .then(response => response.json())  // Parse the server's response as JSON
  .then(data => {
      // Check if the response indicates success
      if (data.success) {
          decreaseAjaxPending();
          console.log('Icon uploaded successfully:', data.filePath);  // Log success
      } else {
          decreaseAjaxPending();
          console.error('Error uploading icon:', data.error);  // Log error
      }
  })
  .catch(error => {
    decreaseAjaxPending();
    console.error('Error:', error);
  });  // Log any errors during the fetch request
}

/**
 * Sends the generated icon data to the server.
 * 
 * @param {string} applicationId - The unique identifier for the application.
 * @param {Array} iconImages - An array of base64 PNG images for different icon sizes.
 * @param {string} iconName - The name of the icon file (e.g., 'favicon.ico').
 */
function sendIconToServer(applicationId, iconImages, iconName) {
  const formData = new FormData();
  formData.append('application_id', applicationId);  // Add the application ID
  formData.append('icon_name', iconName);  // Add the icon file name
  
  // Loop through each icon image and append it to the form data
  iconImages.forEach((imageData, index) => {
      formData.append('images[' + index + ']', imageData);  // Add each PNG image as a separate form data entry
  });

  increaseAjaxPending();

  // Send the data to the server using a POST request
  fetch('lib.ajax/application-icon.php', {
      method: 'POST',
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          decreaseAjaxPending();
          console.log('Icon uploaded successfully:', data.filePath);
      } else {
          decreaseAjaxPending();
          console.error('Error uploading icon:', data.error);
      }
  })
  .catch(error => {
    decreaseAjaxPending();
    console.error('Error:', error);
  });
}

/**
 * Converts a string from snake_case to UpperCamelCase.
 *
 * This function takes an input string, replaces underscores with spaces,
 * capitalizes the first letter, formats it for prettiness, removes
 * any spaces, and trims any leading or trailing whitespace.
 *
 * @param {string} input - The input string in snake_case format.
 * @returns {string} The transformed string in UpperCamelCase format.
 */
function upperCamelize(input) {
  return input
    .replaceAll("_", " ")
    .capitalize()
    .prettify()
    .replaceAll(" ", "")
    .trim();
}

/**
 * Checks if a given key exists in an array of objects.
 *
 * This function iterates through an array of objects, each expected to
 * have a `value` property. It returns true if any object's `value`
 * matches the specified key; otherwise, it returns false.
 *
 * @param {Array<Object>} defdata - The array of objects to be searched.
 * @param {string} key - The key to search for in the array.
 * @returns {boolean} True if the key exists in the array, false otherwise.
 */
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

/**
 * Loads the state of a form based on provided data.
 *
 * This function populates the inputs of a form (frm2) based on the
 * definitions provided in the defdata array. It checks for each field
 * and updates the corresponding input elements (radio buttons, checkboxes,
 * and select elements) based on their names and values.
 *
 * Additionally, it resets any checkbox inputs in frm2 that correspond
 * to fields present in defdata but are not checked.
 *
 * @param {Array<Object>} defdata - An array of objects containing field
 *                                   names and values to load into the form.
 * @param {jQuery} frm1 - The first form (not used in this implementation).
 * @param {jQuery} frm2 - The second form that will be populated with data.
 * @returns {void} This function does not return a value.
 */
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

/**
 * Fixes the names of input fields in a path management table.
 *
 * This function iterates over each row of a table with the class
 * `path-manager` and updates the names of the input fields within
 * each row. The names are set to reflect their index in the format
 * `name[index]`, `path[index]`, and `checked[index]`, where `index`
 * is the row number.
 *
 * This ensures that when the form is submitted, the input values
 * are grouped correctly in an array format based on their respective
 * indices.
 *
 * @returns {void} This function does not return a value.
 */
function fixPathForm() {
  let index = 0;
  $('table.path-manager tbody tr').each(function () {
    let tr = $(this);
    tr.find('td:nth-child(1) input[type="text"]').attr('name', 'name[' + index + ']');
    tr.find('td:nth-child(2) input[type="text"]').attr('name', 'path[' + index + ']');
    tr.find('td:nth-child(3) input[type="checkbox"]').attr('name', 'checked[' + index + ']');
    index++;
  });
}

/**
 * Fixes the names of input fields in a language management table.
 *
 * This function iterates over each row of a table with the class
 * `language-manager` and updates the names of the input fields within
 * each row. The names are set to reflect their index in the format
 * `language_name[index]`, `language_code[index]`, and `checked[index]`,
 * where `index` is the row number.
 *
 * This ensures that when the form is submitted, the input values
 * are grouped correctly in an array format based on their respective
 * indices.
 *
 * @returns {void} This function does not return a value.
 */
function fixLanguageForm() {
  let index = 0;
  $('table.language-manager tbody tr').each(function () {
    let tr = $(this);
    tr.find('td:nth-child(1) input[type="text"]').attr('name', 'language_name[' + index + ']');
    tr.find('td:nth-child(2) input[type="text"]').attr('name', 'language_code[' + index + ']');
    tr.find('td:nth-child(3) input[type="checkbox"]').attr('name', 'checked[' + index + ']');
    index++;
  });
}

/**
 * Adds diagram options to the provided parameters array.
 *
 * This function collects values from various input fields related to diagram
 * settings and appends them as key-value pairs to the given parameters array.
 * The parameters include settings for maximum levels, maximum columns, margins,
 * and zoom level.
 *
 * @param {Array<string>} params - An array to which the diagram options will be added.
 * @returns {Array<string>} The updated parameters array containing the added options.
 */
function addDiagramOption(params) {
  params.push('maximum_level=' + $('[name="maximum_level"]').val());
  params.push('maximum_column=' + $('[name="maximum_column"]').val());
  params.push('margin_x=' + $('[name="margin_x"]').val());
  params.push('margin_y=' + $('[name="margin_y"]').val());
  params.push('entity_margin_x=' + $('[name="entity_margin_x"]').val());
  params.push('entity_margin_y=' + $('[name="entity_margin_y"]').val());
  params.push('zoom=' + $('[name="zoom"]').val());
  return params;
}

/**
 * Loads and displays a relationship diagram based on selected entities.
 *
 * This function gathers parameters for the diagram configuration, including
 * selected entities, and sends requests to generate the diagram and its map.
 * It then appends the generated diagram image to the UI and loads the corresponding
 * map for interactivity.
 *
 * The following parameters are collected:
 * - Maximum levels
 * - Maximum columns
 * - Margins
 * - Zoom level
 * - Selected entities
 * - A random timestamp to prevent caching
 *
 * @returns {void} This function does not return a value.
 */
function loadDiagramMultiple() {
  let params = [];
  params = addDiagramOption(params);

  $('.entity-container-relationship .entity-checkbox').each(function (e) {
    if ($(this)[0].checked) {
      params.push('entity[]=' + $(this).val());
    }
  });
  params.push('rnd=' + (new Date()).getTime());
  let img = $('<img />');
  let urlImage = 'lib.ajax/entity-relationship-diagram.php?' + params.join('&');
  let urlMap = 'lib.ajax/entity-relationship-diagram-map.php?' + params.join('&');
  img.attr('src', urlImage);
  $('.erd-image').empty().append(img);

  $('[name="erd-map"]').load(urlMap, function (e) {
    img.attr('usemap', '#erd-map');
  });
}

/**
 * Downloads the SVG image directly as a file.
 * 
 * This function retrieves the URL of the SVG image from the `src` attribute of the
 * `<img>` tag within the `.erd-image` container. It creates a temporary `<a>` element 
 * with the `download` attribute, which triggers the download of the SVG file.
 * 
 * @returns {void} This function does not return any value.
 */
function downloadSVG() {
  const imageSVG = document.querySelector('.erd-image img');
  const url = imageSVG.getAttribute('src');

  // Create a temporary <a> element
  const link = document.createElement('a');
  link.href = url;
  link.download = 'downloaded-image.svg'; // Default file name
  document.body.appendChild(link); // Append the link to the document
  link.click(); // Trigger the download
  document.body.removeChild(link); // Clean up by removing the link
}
/**
 * Downloads the SVG image as a PNG file by rendering it to a canvas and converting it to a PNG data URL.
 * 
 * This function retrieves the URL of the SVG image from the `src` attribute of the 
 * `<img>` tag within the `.erd-image` container, draws the image onto a canvas, 
 * and then converts the canvas to a PNG data URL. It triggers the download process 
 * so the user can save the image as a PNG file.
 * 
 * @returns {void} This function does not return any value.
 */
function downloadPNG() {
  const imageSVG = document.querySelector('.erd-image img');
  let url = imageSVG.getAttribute('src');
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  canvas.width = imageSVG.width;
  canvas.height = imageSVG.height;
  const img = new Image();

  img.onload = function () {
    ctx.drawImage(img, 0, 0);
    URL.revokeObjectURL(url);
    const pngData = canvas.toDataURL('image/png');

    // Create a temporary <a> element to trigger the download
    const link = document.createElement('a');
    link.href = pngData;
    link.download = 'downloaded-image.png'; // Default file name
    document.body.appendChild(link); // Append the link to the document
    link.click(); // Trigger the download
    document.body.removeChild(link); // Clean up by removing the link
  };

  img.src = url;
}

/**
 * Handles changes to a map key input and validates its value.
 *
 * This function checks the value of the provided input element (obj).
 * If the value is 'label', 'value', or 'default' (case-insensitive),
 * it marks the input as invalid and updates its value to 'data-{value}' 
 * after a short delay. This process will repeat if the new value 
 * is still invalid. If the input value is valid, any invalid class
 * is removed.
 *
 * @param {jQuery} obj - The jQuery object representing the input element
 *                       whose value is being changed.
 * @returns {void} This function does not return a value.
 */
function onChangeMapKey(obj) {
  let val = obj.val();
  if ((val.toLowerCase() == 'label' || val.toLowerCase() == 'value' || val.toLowerCase() == 'goup' || val.toLowerCase() == 'selected')) {
    if (!obj.hasClass('input-invalid-value')) {
      obj.addClass('input-invalid-value');
      setTimeout(function () {
        obj.val('data-' + val.toLowerCase());
        onChangeMapKey(obj);
      }, 500);
    }
  }
  else if (obj.hasClass('input-invalid-value')) {
    obj.removeClass('input-invalid-value');
  }
}

/**
 * Displays an alert dialog with a specified title and message.
 *
 * This function sets the title and message of a Bootstrap modal dialog
 * with the ID `alert-dialog`, and then shows the modal to the user.
 *
 * @param {string} title - The title to display in the modal.
 * @param {string} message - The message to display in the modal body.
 * @returns {void} This function does not return a value.
 */
function showAlertUI(title, message) {
  $('#alert-dialog .modal-title').text(title);
  $('#alert-dialog .modal-body').html(message);
  $('#alert-dialog').modal('show');
}

/**
 * Closes the alert dialog.
 *
 * This function hides the Bootstrap modal dialog with the ID `alert-dialog`.
 * It can be used to dismiss the alert displayed to the user.
 *
 * @returns {void} This function does not return a value.
 */
function closeAlertUI() {
  $('#alert-dialog').modal('hide');
}

/**
 * Saves the current module to the server.
 *
 * This function checks if a module is currently open. If so, it disables the
 * save button and retrieves the content from the editor. It then sends an AJAX
 * POST request to update the module's content on the server. Once the request
 * is complete, it re-enables the save button. If no module is open, it shows
 * an alert to the user.
 *
 * @returns {void} This function does not return a value.
 */
function saveModule() {
  if (currentModule != "") {
    $("#button_save_module_file").attr("disabled", "disabled");
    let fileContent = cmEditorModule.getDoc().getValue();
    increaseAjaxPending();
    $.ajax({
      type: "POST",
      url: "lib.ajax/module-update.php",
      data: { content: fileContent, module: currentModule },
      dataType: "html",
      success: function (data) {
        decreaseAjaxPending();
        $("#button_save_module_file").removeAttr("disabled");
      },
    });
  } else {
    showAlertUI("Alert", "No file open");
  }
}

/**
 * Saves the current entity to the server.
 *
 * This function checks if an entity is currently open. If so, it disables the
 * save button and retrieves the content from the editor. It then sends an AJAX
 * POST request to update the entity's content on the server. Upon successful 
 * completion, it re-enables the save button, updates various UI components,
 * and highlights any error lines. If no entity is open, it shows an alert to the user.
 *
 * @returns {void} This function does not return a value.
 */
function saveEntity() {
  if (currentEntity != "") {
    $("#button_save_entity_file").attr("disabled", "disabled");
    let fileContent = cmEditorFile.getDoc().getValue();
    increaseAjaxPending();
    $.ajax({
      type: "POST",
      url: "lib.ajax/entity-update.php",
      dataType: "json",
      data: { content: fileContent, entity: currentEntity },
      success: function (data) {
        decreaseAjaxPending();
        $("#button_save_entity_file").removeAttr("disabled");
        updateEntityFile(function(){
          setEntityFile(fileContent);
          updateSelectedEntity();
        });
        updateEntityQuery(true);
        updateEntityRelationshipDiagram();
        removeHilightLineError();
        addHilightLineError(data.error_line - 1)
        if (!data.success) {
          showAlertUI(data.title, data.message);
          setTimeout(function () { closeAlertUI() }, 5000);
        }
      },
    });
  } else {
    showAlertUI("Alert", "No file open");
  }
}

/**
 * Updates the selected entity in the list by highlighting the relevant item.
 */
function updateSelectedEntity()
{
  $('.entity-list .entity-li').removeClass('selected-file');
  $('.entity-list [data-entity-name="'+currentEntity.split('\\').join('\\\\')+'"]').closest('.entity-li').addClass('selected-file');
}

/**
 * Saves the current entity to the server.
 *
 * This function checks if an entity is currently open. If so, it disables the
 * save button and retrieves the content from the editor. It then sends an AJAX
 * POST request to update the entity's content on the server. Upon successful 
 * completion, it re-enables the save button, updates various UI components,
 * and highlights any error lines. If no entity is open, it shows an alert to the user.
 *
 * @returns {void} This function does not return a value.
 */
function saveEntityAs() {
  if (currentEntity != "") {
    let fileContent = cmEditorFile.getDoc().getValue();

    getUserInput('New Etity Name', 'Save Entity As', [
      {
        'caption': 'Yes',  // Caption for the button
        'fn': () => {
          let newEntity = $('.prompt-input').val();
          increaseAjaxPending();
          $.ajax({
            type: "POST",
            url: "lib.ajax/entity-save-as.php",
            dataType: "json",
            data: { content: fileContent, entity: currentEntity, newEntity: newEntity },
            success: function (data) {
              decreaseAjaxPending();
              updateEntityFile();
              updateEntityQuery(true);
              updateEntityRelationshipDiagram();
              removeHilightLineError();
              addHilightLineError(data.error_line - 1)
              if (!data.success) {
                showAlertUI(data.title, data.message);
                setTimeout(function () { closeAlertUI() }, 5000);
              }
            },
          });
        },  // Callback for OK button
        'class': 'btn-primary'  // Bootstrap class for styling
      },
      {
        'caption': 'No',  // Caption for the button
        'fn': () => { },  // Callback for Cancel button
        'class': 'btn-secondary'  // Bootstrap class for styling
      }
    ],
      currentEntity);
  } else {
    showAlertUI("Alert", "No file open");
  }
}

/**
 * Highlights a specific line in the editor to indicate an error.
 *
 * This function adds a highlight class to the specified line number
 * if it is not -1. It also updates the lastErrorLine variable.
 *
 * @param {number} lineNumber - The line number to highlight.
 * @returns {void} This function does not return a value.
 */
function addHilightLineError(lineNumber) {
  if (lineNumber != -1) {
    cmEditorFile.addLineClass(lineNumber, 'background', 'highlight-line');
  }
  lastErrorLine = lineNumber;
}

/**
 * Removes the highlight from the last error line in the editor.
 *
 * This function checks if there is a lastErrorLine set and removes
 * the highlight class from that line if it is not -1.
 *
 * @returns {void} This function does not return a value.
 */
function removeHilightLineError() {
  if (lastErrorLine != -1) {
    cmEditorFile.removeLineClass(lastErrorLine, 'background', 'highlight-line');
  }
}

/**
 * Saves the current SQL query as a .sql file.
 *
 * This function retrieves the content from the SQL editor, creates a
 * Blob object, and prompts the user to download the file with a
 * filename based on the current application and the current timestamp.
 *
 * @returns {void} This function does not return a value.
 */
function saveQuery() {
  let blob = new Blob([cmEditorSQL.getDoc().getValue()], {
    type: "application/x-sql;charset=utf-8",
  });
  let finalFileName = (new Date()).getTime() + ".sql"
  // Create a URL for the Blob
  const url = URL.createObjectURL(blob);
  // Create a temporary anchor element
  const a = document.createElement("a");
  a.href = url;
  a.download = finalFileName; // Set the filename to include the datetime suffix
  document.body.appendChild(a);
  a.click(); // Trigger the download by clicking the anchor
  document.body.removeChild(a); // Clean up by removing the anchor
  URL.revokeObjectURL(url); // Release the object URL
}

/**
 * Retrieves selected entity checkboxes.
 *
 * This function collects and returns the values of all checked
 * checkboxes with the class 'entity-checkbox'.
 *
 * @returns {Array<string>} An array of selected entity values.
 */
function getEntitySelection() {
  let ents = [];
  $(".entity-checkbox").each(function () {
    if ($(this)[0].checked) {
      ents.push($(this).val());
    }
  });
  return ents;
}

/**
 * Fetches the query for a specific entity and updates the SQL editor.
 *
 * This function sends an AJAX request to retrieve the query for the specified
 * entity, and then updates the SQL editor with the received data.
 * It handles both merging of entities and the option to create a new table
 * instead of updating an existing structure.
 *
 * @param {string} entity The entity for which to retrieve the query.
 * @param {boolean} merged Indicates whether to merge the entities.
 * @param {boolean} createNew Indicates whether to create a new table instead of updating the structure.
 * @returns {void} This function does not return a value.
 */
function getEntityQuery(entity, merged, createNew) {
  increaseAjaxPending();
  $.ajax({
    type: "POST",
    url: "lib.ajax/entity-query.php",
    data: { entity: entity, merged: merged ? 1 : 0, createNew: createNew ? 1 : 0},
    dataType: "text",
    success: function (data) {
      decreaseAjaxPending();
      cmEditorSQL.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorSQL.refresh();
      }, 1);
      $("#button_save_entity_query").removeAttr("disabled");
    },
  });
}

/**
 * Fetches the content of an entity file and updates the editor.
 *
 * This function sends an AJAX request to retrieve the content of the
 * specified entity file and sets it in the editor. It also calls a
 * callback function if provided.
 *
 * @param {string} entity - The entity to load.
 * @param {function} [clbk] - Optional callback function to call after loading.
 * @returns {void} This function does not return a value.
 */
function getEntityFile(entity, clbk) {
  increaseAjaxPending();
  $.ajax({
    type: "POST",
    url: "lib.ajax/entity-file.php",
    data: { entity: entity },
    dataType: "text",
    success: function (data) {
      decreaseAjaxPending();
      cmEditorFile.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorFile.refresh();
      }, 1);
      $("#button_save_entity_file").removeAttr("disabled");
      $("#button_delete_entity_file").removeAttr("disabled");
      currentEntity = entity[0];
      if (clbk) {
        clbk();
      }
    },
  });
}


/**
 * Fetches the content of a module file and updates the editor.
 *
 * This function sends a GET request to retrieve the content of the
 * specified module file and updates the editor with the response.
 * It also calls a callback function if provided.
 *
 * @param {string} module - The module to load.
 * @param {function} [clbk] - Optional callback function to call after loading.
 * @returns {void} This function does not return a value.
 */
function getModuleFile(module, clbk) {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/module-file.php",
    data: { module: module },
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      cmEditorModule.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorModule.refresh();
      }, 1);
      $("#button_save_module_file").removeAttr("disabled");
      $("#button_delete_module_file").removeAttr("disabled");
      currentModule = module;
      if (clbk) {
        clbk();
      }
    },
  });
}

/**
 * Updates the entity query based on the current selection.
 *
 * This function retrieves the entity list with checkboxes and updates
 * the query in the SQL editor if autoload is true.
 *
 * @param {boolean} [autoload=false] - Whether to auto-load the query.
 * @returns {void} This function does not return a value.
 */
function updateEntityQuery(autoload) {
  autoload = autoload || false;
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/entity-list-with-checkbox.php",
    data: { autoload: autoload },
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      $(".entity-container-query .entity-list").empty().append(data);
      let ents = getEntitySelection();
      let merged = $(".entity-merge");
      let createNew = $(".entity-create-new");
      if (merged.length > 0 && createNew.length > 0 && autoload) {
        getEntityQuery(ents, merged[0].checked, createNew[0].checked);
      }
    },
  });
}

/**
 * Updates the entity relationship diagram based on the current entities.
 *
 * This function retrieves the list of entities for the diagram and updates
 * the UI accordingly.
 *
 * @returns {void} This function does not return a value.
 */
function updateEntityRelationshipDiagram() {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/entity-list-for-diagram.php",
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      $(".entity-container-relationship .entity-list").empty().append(data);
      updateErd();
    },
  });
}

/**
 * Updates the entity file list in the UI.
 *
 * This function retrieves the list of entity files and updates the corresponding
 * sections in the UI.
 *
 * @returns {void} This function does not return a value.
 */
function updateEntityFile(clbk) {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/entity-list.php",
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      $(".entity-container-file .entity-list").empty().append(data);
      $(".container-translate-entity .entity-list").empty().append(data);
      clearEntityFile();
      if(typeof clbk == 'function')
      {
        clbk();
      }
      clearTtransEd3();
      clearTtransEd4();
    },
  });
}

/**
 * Updates the module file list in the UI.
 *
 * This function retrieves the list of module files and updates the UI
 * accordingly, including the translation list.
 *
 * @returns {void} This function does not return a value.
 */
function updateModuleFile() {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/module-list-file.php",
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      $(".module-container .module-list-file").empty().append(data);
      clearModuleFile();
    },
  });
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/module-list-translate.php",
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      $(".container-translate-module .module-list-translate").empty().append(data);
      clearTtransEd1();
      clearTtransEd2();
    },
  });
}

/**
 * Saves a reference value to the server.
 *
 * This function sends a POST request to save a reference value associated
 * with a specific field name and key.
 *
 * @param {string} fieldName - The name of the field for the reference.
 * @param {string} key - The key of the reference to save.
 * @param {string} value - The value to save for the reference.
 * @returns {void} This function does not return a value.
 */
function saveReference(fieldName, key, value) {
  increaseAjaxPending();
  $.ajax({
    type: "POST",
    url: "lib.ajax/reference-save.php",
    data: { fieldName: fieldName, key: key, value: value },
    dataType: "json",
    success: function (data) {
      decreaseAjaxPending();
    },
  });
}

/**
 * Loads a reference value from the server.
 *
 * This function sends a GET request to load a reference value associated
 * with a specific field name and key, and then calls a callback function
 * with the retrieved data.
 *
 * @param {string} fieldName - The name of the field for the reference.
 * @param {string} key - The key of the reference to load.
 * @param {function} clbk - The callback function to call with the loaded data.
 * @returns {void} This function does not return a value.
 */
function loadReference(fieldName, key, clbk) {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/reference-load.php",
    data: { fieldName: fieldName, key: key },
    dataType: "json",
    success: function (data) {
      decreaseAjaxPending();
      clbk(data);
    },
  });
}

/**
 * Updates various module-related attributes and values in the UI.
 *
 * This function formats the module file name, code, and name,
 * and updates the associated input fields with the given parameters.
 *
 * @param {string} moduleFileName - The name of the module file.
 * @param {string} moduleCode - The code for the module.
 * @param {string} moduleName - The display name of the module.
 * @param {string} masterTableName - The name of the master table.
 * @param {string} masterPrimaryKeyName - The primary key name for the master table.
 * @returns {void} This function does not return a value.
 */
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

/**
 * Capitalizes the first letter of each word in a string.
 *
 * This function converts the input string to lowercase and then capitalizes
 * the first letter of each word, returning the formatted string.
 *
 * @param {string} str - The input string to format.
 * @returns {string} The formatted string with capitalized words.
 */
function ucWord(str) {
  str = str.toLowerCase();
  return str.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function (s) //NOSONAR
  {
    return s.toUpperCase();
  });
}

/**
 * Prepares the UI for reference data based on the selected value.
 *
 * This function shows or hides the reference button in the UI based
 * on whether the checked value is "select".
 *
 * @param {string} checkedValue - The value of the checkbox.
 * @param {jQuery} ctrl - The jQuery object of the checkbox control.
 * @returns {void} This function does not return a value.
 */
function prepareReferenceData(checkedValue, ctrl) {
  let tr = ctrl.closest("tr");
  if (checkedValue == "select") {
    tr.find(".reference_button_data").css("display", "inline");
  } else {
    tr.find(".reference_button_data").css("display", "none");
  }
}

/**
 * Prepares the UI for reference filter options based on the selected value.
 *
 * This function shows or hides the reference filter button in the UI
 * based on whether the checked value is "select".
 *
 * @param {string} checkedValue - The value of the checkbox.
 * @param {jQuery} ctrl - The jQuery object of the checkbox control.
 * @returns {void} This function does not return a value.
 */
function prepareReferenceFilter(checkedValue, ctrl) {
  let tr = ctrl.closest("tr");
  if (checkedValue == "select") {
    tr.find(".reference_button_filter").css("display", "inline");
  } else {
    tr.find(".reference_button_filter").css("display", "none");
  }
}

/**
 * Switches the current application and reloads the page.
 *
 * This function sends a POST request to change the current application
 * and then reloads the page upon success.
 *
 * @param {string} currentApplication - The name of the application to switch to.
 * @returns {void} This function does not return a value.
 */
function switchApplication(currentApplication) {
  increaseAjaxPending();
  $.ajax({
    type: "post",
    url: "lib.ajax/application-switch.php",
    dataType: "json",
    data: { currentApplication: currentApplication },
    success: function (data) {
      decreaseAjaxPending();
      window.location.reload();
    },
  });
}

/**
 * Generates a script based on the selected fields in the table.
 *
 * This function collects field details from the specified selector,
 * constructs a data structure for the entity, and calls a function
 * to generate code based on that data.
 *
 * @param {string} selector - The jQuery selector for the table containing fields.
 * @returns {void} This function does not return a value.
 */
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
        includeExport: includeExport,
        isKey: isKey,
        isInputRequired: isInputRequired,
        elementType: elementType,
        filterElementType: filterElementType,
        dataType: dataType,
        inputFilter: inputFilter,
        referenceData: referenceData,
        referenceFilter: referenceFilter,
      };
      fields.push(field);
    });

  let subquery = $("#subquery")[0].checked && true; //NOSONAR
  let requireApproval = $("#with_approval")[0].checked && true; //NOSONAR
  let withTrash = $("#with_trash")[0].checked && true; //NOSONAR
  let manualSortOrder = $("#manualsortorder")[0].checked && true; //NOSONAR
  let exportToExcel = $("#export_to_excel")[0].checked && true; //NOSONAR
  let exportToCsv = $("#export_to_csv")[0].checked && true; //NOSONAR
  let activateDeactivate = $("#activate_deactivate")[0].checked && true; //NOSONAR
  let withApprovalNote = $("#with_approval_note")[0].checked && true; //NOSONAR
  let approvalPosition = $('[name="approval_position"]:checked').val(); //NOSONAR
  let approvalByAnotherUser = $('[name="approval_by_other_user"]:checked').val(); //NOSONAR
  let approvalType = $('[name="approval_type"]:checked').val(); //NOSONAR
  let ajaxSupport = $("#ajax_support")[0].checked && true; //NOSONAR
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
    approvalByAnotherUser: approvalByAnotherUser,
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
    moduleAsMenu: $('[name="module_as_menu"]').val(),
    moduleMenu: $('[name="module_menu"]').val(),
    target: $('#current_module_location').val(),
    updateEntity: $('[name="update_entity"]')[0].checked,
  };
  generateAllCode(dataToPost);
}

/**
 * Retrieves specification data from the filter table.
 *
 * This function collects column names and their corresponding values
 * from the specified filter table, returning an array of objects
 * containing the column-value pairs.
 *
 * @returns {Array<{column: string, value: string}>} An array of objects
 *          representing the column names and their values.
 */
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
        let comparison = tr.find(".data-filter-column-comparison").val();

        if(typeof column == 'undefined')
        {
          column = '';
        }
        if(typeof value == 'undefined')
        {
          value = '';
        }
        if(typeof comparison == 'undefined')
        {
          comparison = '';
        }
        if(comparison == '')
        {
          comparison = 'equals';
        }
        if (column.length > 0) {
          result.push({
            column: column.trim(),
            value: value.trim(),
            comparison: comparison,
          });
        }
      }
    });
  return result;
}

/**
 * Retrieves sorting specifications from the order table.
 *
 * This function collects the column names and their sorting types
 * from the specified order table, returning an array of objects
 * that represent the sorting configurations.
 *
 * @returns {Array<{sortBy: string, sortType: string}>} An array of objects
 *          representing the column names and their sorting types.
 */
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

/**
 * Parses a JSON string and returns the corresponding JavaScript object.
 *
 * This function takes a string input, attempts to parse it as JSON,
 * and returns the resulting object. If the input is not a string,
 * or if parsing fails, it returns null.
 *
 * @param {string} text - The JSON string to be parsed.
 * @returns {object|null} The parsed JavaScript object, or null if
 *                        the input is not a valid JSON string or 
 *                        if parsing fails.
 */
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

/**
 * Parses a JSON string into an object.
 *
 * This function attempts to parse the provided text as JSON.
 * If the parsing is successful and the result is an object,
 * it returns the object. If the input is not a string or
 * parsing fails, it returns null.
 *
 * @param {string} text - The JSON string to be parsed.
 * @returns {Object|null} The parsed JSON object, or null if parsing fails.
 */
function parseJsonData(text)  //NOSONAR
{
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

/**
 * Generates code by sending data to the server and updating UI components.
 *
 * This function sends the provided data to a server endpoint
 * for code generation. Upon success, it updates the entity file,
 * entity query, and entity relationship diagram.
 *
 * @param {Object} dataToPost - The data to send to the server for code generation.
 */
function generateAllCode(dataToPost) {
  increaseAjaxPending();
  $.ajax({
    type: "post",
    url: "lib.ajax/script-generator.php",
    dataType: "json",
    data: dataToPost,
    success: function (data) {
      decreaseAjaxPending();
      updateEntityFile();
      updateEntityQuery(true);
      updateEntityRelationshipDiagram();
      if (data.success) {
        onModuleCreated();
        showToast(data.title, data.message);
        setTimeout(function () { closeAlertUI() }, 2000);
      }
    },
  });
}

/**
 * Creates and shows a dynamic Bootstrap toast notification.
 *
 * This function generates a toast element with a custom header and body, then appends it
 * to the toast container. The toast is displayed using Bootstrap's Toast API and is
 * automatically removed from the DOM after it disappears.
 *
 * @param {string} header The header text of the toast.
 * @param {string} body The body content of the toast.
 */
function showToast(header, body) {
  // Generate a unique ID for the toast element using a timestamp
  let toastId = 'toast-' + new Date().getTime(); // Use timestamp as a unique ID

  // Construct the HTML structure for the toast dynamically
  let toastHTML = `
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="true" data-delay="3000" id="${toastId}">
      <div class="toast-header">
        <strong class="mr-auto">${header}&nbsp;&nbsp;</strong>
        <small>just now</small>
        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="toast-body">
        ${body}
      </div>
    </div>
  `;

  // Append the toast to the beginning of the toast container
  $('.toast-container').prepend(toastHTML);

  // Get the toast element by its unique ID and initialize Bootstrap Toast
  let toastEl = $('#' + toastId)[0];  // Get the toast element using the unique ID
  let toast = new bootstrap.Toast(toastEl);
  toast.show(); // Show the toast notification

  // Remove the toast element from the DOM once it has fully disappeared
  $(toastEl).on('hidden.bs.toast', function () {
    $(toastEl).remove(); // Remove the toast element after it disappears
  });
}

/**
 * Updates the current application by fetching and populating source tables.
 *
 * This function sends the provided data to the server to update
 * the application context. It then clears and repopulates the source
 * table selection based on the response.
 *
 * @param {Object} dataToPost - The data used to update the application.
 */
function updateCurrentApplivation(dataToPost) {
  increaseAjaxPending();
  $.ajax({
    type: "POST",
    url: "lib.ajax/application-update.php",
    data: dataToPost,
    success: function (data) {
      decreaseAjaxPending();
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
      loadAllResource();
    }
  });
}

/**
 * Loads the list of tables from the database into a select element.
 *
 * This function requests the list of database tables and populates
 * the source table dropdown. It also sets data attributes for primary keys
 * on the options if available.
 */
function loadTable() {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/database-table-list.php",
    dataType: "json",
    success: function (data) {
      decreaseAjaxPending();
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

function loadMenu() {
  increaseAjaxPending();
  $.ajax({
    type: "get",
    url: "lib.ajax/application-menu-json.php",
    dataType: "json",
    success: function (data) {
      decreaseAjaxPending();
      $('select[name="module_menu"]').empty();
      for (let i in data) {
        if (data.hasOwnProperty(i)) {
          $('select[name="module_menu"]')[0].append(
            new Option(data[i].label, data[i].label)
          );
        }
      }
    },
    error: function(er)
    {
      decreaseAjaxPending();
    }
  });
}

/**
 * Loads the columns of a specified table into a selector.
 *
 * This function fetches the column information for the given table name
 * and populates the specified selector with rows representing each column.
 *
 * @param {string} tableName - The name of the table whose columns are to be loaded.
 * @param {string} selector - The jQuery selector where the column rows will be appended.
 */
function loadColumn(tableName, selector) {
  increaseAjaxPending();
  $.ajax({
    type: "post",
    url: "lib.ajax/database-column-list.php",
    data: { table_name: tableName },
    dataType: "json",
    success: function (answer) {
      decreaseAjaxPending();
      $(selector).empty();
      let data = answer.fields;
      let i;
      let field, args;
      let domHtml;
      let skippedOnInsertEdit = answer.skipped_insert_edit;
      for (i in data) {
        field = data[i].column_name;
        args = { data_type: data[i].data_type, column_type: data[i].column_type };
        domHtml = generateRow(field, args, skippedOnInsertEdit);
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

/**
 * Restores the form data from a given object.
 *
 * This function takes a data object containing configuration settings
 * for a form and restores the values in the form fields accordingly.
 * It handles fields related to columns, filters, sorting, and features.
 *
 * @param {Object} data - The data object containing the form values to restore.
 * @param {Array} data.fields - An array of field configurations, where each 
 *        configuration includes properties like fieldName, includeInsert, 
 *        includeEdit, and more.
 * @param {Array} data.specification - An array of filter specifications 
 *        for the modal filter data, containing column names and values.
 * @param {Array} data.sortable - An array of sorting specifications for the 
 *        modal order data, containing sort columns and their order types.
 * @param {Object} data.features - An object containing feature flags for the 
 *        module, such as subquery, export options, and approval settings.
 *
 * This function:
 * - Iterates through the fields and updates corresponding form elements
 * - Clears existing rows in filter and order modals before restoring new values
 * - Sets the state of feature toggles based on the provided data
 */
function restoreForm(data)  //NOSONAR
{
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
            tr.find('.reference_button_data').css('display', 'inline');
          }

          if (data.fields[i].filterElementType == 'select') {
            tr.find('.reference-filter').val(JSON.stringify(data.fields[i].referenceFilter));
            tr.find('.reference_button_filter').css('display', 'inline');
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

  while ($('#modal-filter-data tbody tr').length > 1) {
    $(selector).remove();
  }
  $(selector).find('.data-filter-column-name').val('');
  $(selector).find('.data-filter-column-value').val('');

  if (typeof data.specification == 'undefined' || data.specification.length == 0) {
    $(selector).find('.data-filter-column-name').val('');
    $(selector).find('.data-filter-column-comparison').val('');
    $(selector).find('.data-filter-column-value').val('');
  }
  else {
    for (let i in data.specification) {
      if (data.specification.hasOwnProperty(i)) {
        if (cnt > 0) {
          let trHtml = $(selector)[0].outerHTML;
          $(selector).parent().append(trHtml);
        }
        let column = data.specification[i].column || '';
        let comparison = data.specification[i].comparison || '';
        let value = data.specification[i].value || '';
        if(typeof column == 'undefined')
        {
          column = '';
        }
        if(typeof value == 'undefined')
        {
          value = '';
        }
        if(typeof comparison == 'undefined')
        {
          comparison = '';
        }
        if(comparison == '')
        {
          comparison = 'equals';
        }
        $(selector).find('.data-filter-column-name').val(column);
        $(selector).find('.data-filter-column-comparison').val(comparison);
        $(selector).find('.data-filter-column-value').val(value);
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

/**
 * Loads saved module data from a specified module file and restores it into the form.
 *
 * This function performs an AJAX GET request to retrieve the module data 
 * based on the provided module file and target. Upon successful retrieval, 
 * it calls the `restoreForm` function to populate the form with the data 
 * and then executes a callback function.
 *
 * @param {string} moduleFile - The name of the module file from which to load data.
 * @param {string} target - The target location for which to load the module data.
 * @param {Function} clbk - A callback function to be executed after the data is loaded.
 *        This function is called whether the request is successful or fails.
 */
function loadSavedModuleData(moduleFile, target, clbk) {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/module-data.php",
    data: { moduleFile: moduleFile, target: target },
    dataType: "json",
    success: function (data) {
      decreaseAjaxPending();
      restoreForm(data)
      clbk();
    },
    error: function (err) {
      clbk();
    }
  });
}

/**
 * Generates a select dropdown for filter types based on the provided field and column type.
 *
 * This function creates a `<select>` element populated with various filter options. 
 * It determines the default filter type based on the provided column type and sanitization 
 * rules. The generated select element allows users to choose the appropriate filter type 
 * for the specified field.
 *
 * @param {string} field - The name of the field for which the filter is being generated.
 * @param {Object} [args] - An optional object containing additional arguments.
 *        - {string} [args.column_type] - The type of the column, which helps determine the default filter.
 *
 * @returns {string} The outer HTML of the generated select element, including options.
 */
function generateSelectFilter(field, args)  //NOSONAR
{
  let virtualDOM;

  args = args || {};
  args.column_type = args.column_type || "text";
  let columnType = args.column_type;
  let matchByType = {
    FILTER_SANITIZE_BOOL: [
      "tinyint(1)",
      "bool",
      "boolean",
    ],
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
      "tinyint",
    ],
    FILTER_SANITIZE_NUMBER_FLOAT: [
      "numeric",
      "double",
      "real",
      "money"
    ],
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

  virtualDOM = $(`
    <select class="form-control input-data-filter" name="filter_type_${field}" id="filter_type_${field}">
        <option value="FILTER_DEFAULT">DEFAULT</option>
        <option value="FILTER_SANITIZE_BOOL">BOOL</option>
        <option value="FILTER_SANITIZE_NUMBER_INT">NUMBER_INT</option>
        <option value="FILTER_SANITIZE_NUMBER_UINT">NUMBER_UINT</option>
        <option value="FILTER_SANITIZE_NUMBER_OCTAL">NUMBER_OCTAL</option>
        <option value="FILTER_SANITIZE_NUMBER_HEXADECIMAL">NUMBER_HEXADECIMAL</option>
        <option value="FILTER_SANITIZE_NUMBER_FLOAT">NUMBER_FLOAT</option>
        <option value="FILTER_SANITIZE_STRING">STRING</option>
        <option value="FILTER_SANITIZE_STRING_INLINE">STRING_INLINE</option>
        <option value="FILTER_SANITIZE_NO_DOUBLE_SPACE">NO_DOUBLE_SPACE</option>
        <option value="FILTER_SANITIZE_STRIPPED">STRIPPED</option>
        <option value="FILTER_SANITIZE_SPECIAL_CHARS">SPECIAL_CHARS</option>
        <option value="FILTER_SANITIZE_ALPHA">ALPHA</option>
        <option value="FILTER_SANITIZE_ALPHANUMERIC">ALPHANUMERIC</option>
        <option value="FILTER_SANITIZE_ALPHANUMERICPUNC">ALPHANUMERICPUNC</option>
        <option value="FILTER_SANITIZE_STRING_BASE64">STRING_BASE64</option>
        <option value="FILTER_SANITIZE_EMAIL">EMAIL</option>
        <option value="FILTER_SANITIZE_URL">URL</option>
        <option value="FILTER_SANITIZE_IP">IP</option>
        <option value="FILTER_SANITIZE_ENCODED">ENCODED</option>
        <option value="FILTER_SANITIZE_COLOR">COLOR</option>
        <option value="FILTER_SANITIZE_MAGIC_QUOTES">MAGIC_QUOTES</option>
        <option value="FILTER_SANITIZE_PASSWORD">PASSWORD</option>
    </select>
  `);


  let i, j, k;
  let filterType = "FILTER_SANITIZE_SPECIAL_CHARS";
  let found = false;
  for (i in matchByType) {
    j = matchByType[i];
    for (k in j) {
      if (columnType.toLowerCase().indexOf(j[k].toLowerCase()) != -1) {
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

/**
 * Generates a select dropdown for input types based on the provided field and data type.
 *
 * This function creates a `<select>` element populated with various input type options. 
 * It determines the default input type based on the provided data type and predefined mappings. 
 * The generated select element allows users to choose the appropriate input type for the specified field.
 *
 * @param {string} field - The name of the field for which the input type is being generated.
 * @param {Object} [args] - An optional object containing additional arguments.
 *        - {string} [args.data_type] - The data type of the field, which helps determine the default input type.
 *
 * @returns {string} The outer HTML of the generated select element, including options.
 */
function generateSelectType(field, args) {
  let virtualDOM;
  args = args || {};
  args.data_type = args.data_type || "text";
  let dataType = args.data_type;
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
    float: [
      "numeric",
      "double",
      "real",
      "money"
    ],
    text: [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    "datetime-local": [
      "datetime",
      "timestamp"
    ],
    "color": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    "month": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    "week": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    date: [
      "date"
    ],
    time: [
      "time"
    ],
  };

  virtualDOM = $(`
    <select class="form-control input-field-data-type" name="data_type_${field}" id="data_type_${field}">
        <option value="text" title="&lt;input type=&#39;text&#39;&gt;">text</option>
        <option value="email" title="&lt;input type=&#39;email&#39;&gt;">email</option>
        <option value="url" title="&lt;input type=&#39;url&#39;&gt;">url</option>
        <option value="tel" title="&lt;input type=&#39;tel&#39;&gt;">tel</option>
        <option value="password" title="&lt;input type=&#39;password&#39;&gt;">password</option>
        <option value="int" title="&lt;input type=&#39;number&#39;&gt;">int</option>
        <option value="float" title="&lt;input type=&#39;number&#39; step=&#39;any&#39;&gt;">float</option>
        <option value="date" title="&lt;input type=&#39;date&#39;&gt;">date</option>
        <option value="time" title="&lt;input type=&#39;time&#39;&gt;">time</option>
        <option value="datetime-local" title="&lt;input type=&#39;datetime-local&#39;&gt;">datetime</option>
        <option value="month" title="&lt;input type=&#39;month&#39;&gt;">month</option>
        <option value="week" title="&lt;input type=&#39;week&#39;&gt;">week</option>
        <option value="color" title="&lt;input type=&#39;color&#39;&gt;">color</option>
    </select>
  `);



  let i;
  let j;
  let k;
  let filterType = "text";
  let found = false;
  for (i in matchByType) {
    j = matchByType[i];
    for (k in j) {
      if (dataType.toLowerCase().indexOf(j[k].toLowerCase()) != -1) {
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

/**
 * Returns a new array containing only unique elements from the provided array.
 *
 * @param {Array} arr1 - The input array from which to filter out duplicates.
 * @returns {Array} A new array containing only unique elements.
 */
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

/**
 * Checks if the provided string is a SQL keyword.
 *
 * @param {string} str - The string to check against the list of SQL keywords.
 * @returns {boolean} True if the string is a keyword, false otherwise.
 */
function isKeyWord(str) {
  str = str.toString();
  let i;
  let kw = keyWords.split(",");
  for (i in kw) {
    if (str.equalIgnoreCase(kw[i])) {
      return true;
    }
  }
  return false;
}

/**
 * Generates an HTML table row for a specified field, including various input elements 
 * for configuration such as inclusion in inserts, edits, and lists, as well as type selection.
 *
 * The function takes into account whether the field is a reserved keyword and skips 
 * certain checkboxes if specified. It constructs the row with appropriate classes, 
 * input fields, and other HTML elements necessary for the configuration interface.
 *
 * @param {string} field - The name of the field for which the row is generated.
 * @param {object} args - Additional arguments that may influence the row's configuration.
 * @param {Array} skippedOnInsertEdit - An array of field names to be skipped for insert/edit checkboxes.
 * @returns {string} The HTML string representing a table row with input elements.
 */
function generateRow(field, args, skippedOnInsertEdit)  //NOSONAR
{
  // Check if the field is a reserved keyword
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
  if ($.inArray(field, skippedOnInsertEdit) != -1) {
    insertRow = `
      <td align="center">
        <input type="checkbox" class="include_insert" name="include_insert_${field}" value="0" disabled="disabled">
      </td>
    `;

    editRow = `
      <td align="center">
        <input type="checkbox" class="include_edit" name="include_edit_${field}" value="0" disabled="disabled">
      </td>
    `;

    listRow = `
      <td align="center">
        <input type="checkbox" class="include_list" name="include_list_${field}" value="1">
      </td>
    `;

  } else {
    insertRow = `
      <td align="center">
        <input type="checkbox" class="include_insert" name="include_insert_${field}" value="1" checked="checked">
      </td>
    `;

    editRow = `
      <td align="center">
        <input type="checkbox" class="include_edit" name="include_edit_${field}" value="1" checked="checked">
      </td>
    `;

    listRow = `
      <td align="center">
        <input type="checkbox" class="include_list" name="include_list_${field}" value="1" checked="checked">
      </td>
    `;

  }

  exportRow = `
    <td align="center">
      <input type="checkbox" class="include_export" name="include_export_${field}" value="1" checked="checked">
    </td>
  `;

  let rowHTML = `
    <tr data-field-name="${field}" ${cls}>
      <td class="data-sort data-sort-body data-sort-handler"></td>
      <td class="field-name">
        ${field}
        <input type="hidden" name="field" value="${field}">
      </td>
      <td>
        <input type="hidden" class="input-field-name" name="caption_${field}" 
          value="${field.replaceAll("_", " ").capitalize().prettify().trim()}" 
          autocomplete="off" spellcheck="false">
        ${field.replaceAll("_", " ").capitalize().prettify().trim()}
      </td>
      ${insertRow} ${editRow}
      <td align="center">
        <input type="checkbox" class="include_detail" name="include_detail_${field}" value="1" checked="checked">
      </td>
      ${listRow}
      ${exportRow}
      <td align="center">
        <input type="checkbox" class="include_key" name="include_key_${field}" value="1">
      </td>
      <td align="center">
        <input type="checkbox" class="include_required" name="include_required_${field}" value="1">
      </td>
      <td align="center">
        <input type="radio" class="input-element-type" name="element_type_${field}" value="text" checked="checked">
      </td>
      <td align="center">
        <input type="radio" class="input-element-type" name="element_type_${field}" value="textarea">
      </td>
      <td align="center">
        <input type="radio" class="input-element-type" name="element_type_${field}" value="checkbox">
      </td>
      <td align="center">
        <input type="radio" class="input-element-type" name="element_type_${field}" value="select">
      </td>
      <td align="center">
        <input type="hidden" class="reference-data" name="reference_data_${field}" value="{}">
        <button type="button" class="btn btn-sm btn-primary reference-button reference_button_data">Source</button>
      </td>
      <td align="center">
        <input type="checkbox" name="list_filter_${field}" value="text" class="input-field-filter">
      </td>
      <td align="center">
        <input type="checkbox" name="list_filter_${field}" value="select" class="input-field-filter">
      </td>
      <td align="center">
        <input type="hidden" class="reference-filter" name="reference_filter_${field}" value="{}">
        <button type="button" class="btn btn-sm btn-primary reference-button reference_button_filter">Source</button>
      </td>
      <td>
        ${generateSelectType(field, args)}
      </td>
      <td>
        ${generateSelectFilter(field, args)}
      </td>
    </tr>
  `;


  return rowHTML;
}

/**
 * Serializes form data into an object structure for processing or submission.
 *
 * The function retrieves the selected reference type, multiple selection values, 
 * and additional entity and mapping data. It then compiles this information into 
 * an object which is returned for further use.
 *
 * @returns {Object} An object containing serialized form data including:
 *   - type: The selected reference type.
 *   - entity: The entity data retrieved from the form.
 *   - map: The mapping data retrieved from the form.
 *   - yesno: Placeholder for yes/no data (currently null).
 *   - truefalse: Placeholder for true/false data (currently null).
 *   - onezero: Placeholder for one/zero data (currently null).
 *   - multipleSelection: The value from the multiple selection dropdown.
 */
function serializeForm() {
  let type = null;
  $(".reference_type").each(function (e) {
    if ($(this)[0].checked) {
      type = $(this).val();
    }
  });
  let multipleSelection = $(".multiple-selection").val();
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
    multipleSelection: multipleSelection
  };
  return all;
}

/**
 * Deserializes an object of data into a modal form for editing or viewing.
 *
 * The function empties the current modal body and populates it with the reference 
 * resource layout. It then selects the reference type and sets entity and mapping 
 * data based on the provided data object.
 *
 * @param {Object} data - The object containing data to populate the modal form.
 */
function deserializeForm(data) {
  $("#modal-create-reference-data").find(".modal-body").empty();
  $("#modal-create-reference-data")
    .find(".modal-body")
    .append(getReferenceResource());
  selectReferenceType(data);
  setEntityData(data);
  setMapData(data);
}

/**
 * Adds a new row to the end of the specified table by duplicating the last row in the tbody.
 *
 * @param {jQuery} table - The jQuery object representing the table to which a row should be added.
 */
function addRow(table) {
  let lastRow = table.find("tbody").find("tr:last-child").prop("outerHTML");
  table.find("tbody").append(lastRow);
}

/**
 * Adds a new column to the specified table, including input fields in the header and body.
 *
 * @param {jQuery} table - The jQuery object representing the table to which a column should be added.
 */
function addColumn(table) {
  let ncol = table.find("thead").find("tr").find("td").length;
  let pos = ncol - parseInt(table.attr("data-offset")) - 2;
  let inputHeader = '<td><input class="form-control rd-map-key" type="text" value="" placeholder="Additional attribute name"></td>';
  let inputBody = '<td><input class="form-control rd-map-value" type="text" value="" placeholder="Additional attribute value"></td>';
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
    .attr("colspan", table.find("thead").find("tr").find("td").length + 1);
}

/**
 * Removes the last column from the specified table, ensuring that a minimum number of columns is maintained.
 *
 * @param {jQuery} table - The jQuery object representing the table from which a column should be removed.
 */
function removeLastColumn(table) {
  let ncol = table.find("thead").find("tr").find("td").length;
  let offset = parseInt(table.attr("data-offset"));
  let mincol = parseInt(table.attr("data-number-of-column"));
  let pos = ncol - offset - 2;
  if (pos > mincol) {
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
      .attr("colspan", table.find("thead").find("tr").find("td").length + 1);
  }
  else
  {
    table
      .find("thead")
      .find("tr")
      .find("td:nth(" + pos + ")")
      .find(':input').val('');
    table
      .find("tbody")
      .find("tr")
      .each(function (e3) {
        $(this)
          .find("td:nth(" + pos + ")")
          .find(':input').val('');
      });
  }
}

/**
 * Clears the input values of all elements with the class "rd-group" in the given table.
 * A confirmation prompt is shown to the user before clearing the values.
 * 
 * @param {jQuery} table - The jQuery object representing the table containing the groups.
 * @returns {void}
 */
function clearGroup(table)
{
  asyncAlert(
    'Do you want to clear the groups?',
    'Confirmation',
    [
      {
        'caption': 'Yes',
        'fn': () => {
          table.find(".rd-group").val("");
        },
        'class': 'btn-primary'
      },
      {
        'caption': 'No',
        'fn': () => { },
        'class': 'btn-secondary'
      }
    ]
  );
}

/**
 * Selects the appropriate reference type based on the provided data and adjusts modal layout accordingly.
 *
 * @param {Object} data - The object containing reference type data.
 */
function selectReferenceType(data) {
  let referenceType = data.type ? data.type : "entity";
  let obj = $('#modal-create-reference-data .modal-dialog');
  if (referenceType == 'entity' || referenceType == 'map') {
    obj.addClass('modal-lg');
    if (obj.hasClass('modal-md')) {
      obj.removeClass('modal-md');
    }
  }
  else {
    obj.addClass('modal-md');
    if (obj.hasClass('modal-lg')) {
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

/**
 * Sets entity data into the form based on the provided data object.
 *
 * @param {Object} data - The object containing entity data to populate the form.
 */
function setEntityData(data) {
  data.entity = data && data.entity ? data.entity : {}; //NOSONAR
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
  let multiple = data.multipleSelection || '0';
  if (multiple != '1') {
    multiple = '0';
  }
  $('.multiple-selection').val(multiple);

  setSpecificationData(data);
  setSortableData(data);
  setGroupData(data);
  setAdditionalOutputData(data);
}

/**
 * Retrieves entity data from the form and compiles it into an object.
 *
 * @returns {Object} An object containing the entity data retrieved from the form.
 */
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
    group: getGroupData(),
    additionalOutput: getAdditionalOutputData(),
  };
  return entity;
}

/**
 * Sets specification data into the form based on the provided data object.
 *
 * @param {Object} data - The object containing specification data to populate the form.
 */
function setSpecificationData(data) // NOSONAR
{
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
      let comparison = row.comparison;
      let column = row.column;
      let value = row.value;
      if(typeof column == 'undefined')
      {
        column = '';
      }
      if(typeof value == 'undefined')
      {
        value = '';
      }
      if(typeof comparison == 'undefined')
      {
        comparison = '';
      }
      if(comparison == '')
      {
        comparison = 'equals';
      }
      tr.find(".rd-column-name").val(column);
      tr.find(".rd-comparison").val(comparison);
      tr.find(".rd-value").val(value);
    }
  }
}

/**
 * Retrieves specification data from the form and compiles it into an array of objects.
 *
 * @returns {Array} An array of objects containing the specification data retrieved from the form.
 */
function getSpecificationData() {
  let result = [];
  let selector = '[data-name="specification"]';
  $(selector)
    .find("tbody")
    .find("tr")
    .each(function (e) {
      let tr = $(this);
      let column = tr.find(".rd-column-name").val();
      let value = tr.find(".rd-value").val();
      let comparison = tr.find(".rd-comparison").val();
      if(typeof column == 'undefined')
      {
        column = '';
      }
      if(typeof value == 'undefined')
      {
        value = '';
      }
      if(typeof comparison == 'undefined')
      {
        comparison = '';
      }
      if(comparison == '')
      {
        comparison = 'equals';
      }
      if(comparison == '')
      {
        comparison = 'equals';
      }
      if (column.length > 0) {
        result.push({
          column: column.trim(),
          value: fixValue(value.trim()),
          comparison: comparison.trim(),
        });
      }
    });
  return result;
}

/**
 * Sets sortable data into the form based on the provided data object.
 *
 * @param {Object} data - The object containing sortable data to populate the form.
 */
function setSortableData(data) {
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

/**
 * Retrieves sortable data from the form and compiles it into an array of objects.
 *
 * @returns {Array} An array of objects containing the sortable data retrieved from the form.
 */
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

/**
 * Sets group data into the element with the attribute `data-name="grouping"`.
 *
 * @param {Object} data - The data object containing group information.
 * @param {Object} data.entity - The entity object that includes group details.
 * @param {Object} data.entity.group - The group object containing values, labels, source, and entity.
 * @param {string} data.entity.group.value - The main value of the group.
 * @param {string} data.entity.group.label - The label of the group.
 * @param {string} data.entity.group.source - The source of the group.
 * @param {string} data.entity.group.entity - The entity associated with the group.
 * @param {Array} [data.entity.group.map] - An optional array of mapping objects in the reference table.
 * @param {string} data.entity.group.map[].value - The value of each mapping.
 * @param {string} data.entity.group.map[].label - The label of each mapping.
 */
function setGroupData(data) {
  let selector = $('[data-name="grouping"]');
  if (data?.entity?.group) 
  {
    selector.attr('data-group-source', data.entity.group.source);
    selector.find(".rd-group-value").val(data.entity.group.value);
    selector.find(".rd-group-label").val(data.entity.group.label);
    selector.find(".group-reference").filter('[value="'+data.entity.group.source+'"]')[0].checked = true;
    selector.find(".rd-group-entity").val(data.entity.group.entity);
    let table = selector.find('table.table-reference');
    let group = data.entity.group;
    if (group?.map?.length > 0) {
      for (let i in group.map) {
        if (i > 0) {
          addRow(table);
        }
        let tr = table.find("tr:last-child");
        let row = group.map[i];
        tr.find(".rd-map-value").val(row.value);
        tr.find(".rd-map-label").val(row.label);
      }
    }
  }
}

/**
 * Retrieves group data from the element with the attribute `data-name="grouping"`.
 *
 * @returns {Object} result - The object containing group data.
 * @returns {string} result.value - The main value of the group.
 * @returns {string} result.label - The label of the group.
 * @returns {string} result.source - The source of the group.
 * @returns {string} result.entity - The entity associated with the group.
 * @returns {Array} result.map - An array of mapping objects in the reference table.
 * @returns {string} result.map[].value - The value of each mapping.
 * @returns {string} result.map[].label - The label of each mapping.
 */
function getGroupData() {
  let result = {};
  let map = [];

  let selector = $('[data-name="grouping"]');

  let value = selector.find(".rd-group-value").val();
  let label = selector.find(".rd-group-label").val();
  let source = selector.find(".group-reference:checked").val();
  let entity = selector.find(".rd-group-entity").val();

  result.value = value;
  result.label = label;
  result.source = source;
  result.entity = entity;

  $(selector)
    .find(".table-reference tbody tr")
    .each(function () {
      let tr = $(this);
      let value = tr.find(".rd-map-value").val().trim();
      let label = tr.find(".rd-map-label").val().trim();
      if (value.length > 0) { 
        map.push({
          value: value,
          label: label,
        });
      }
    });

  result.map = map;
  return result;
}


/**
 * Sets additional output data into the form based on the provided data object.
 *
 * @param {Object} data - The object containing additional output data to populate the form.
 */
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

/**
 * Retrieves additional output data from the form and compiles it into an array of objects.
 *
 * @returns {Array} An array of objects containing the additional output data retrieved from the form.
 */
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

/**
 * Sets map data into the form based on the provided data object.
 *
 * @param {Object} data - The object containing map data to populate the form.
 */
function setMapData(data) // NOSONAR
{
  let selector = '[data-name="map"]';
  let table = $(selector);
  let keys = [];
  data.map = data.map ? data.map : [];
  let map = data.map;
  if (map.length > 0) {
    let map0 = map[0];
    let objLength = 0;
    for (let i in map0) {
      if (map0.hasOwnProperty(i)) {
        objLength++;
        if (objLength > 5) {
          addColumn(table);
        }
        if (i != "value" && i != "label" && i != "group" && i != "selected") {
          keys.push(i);
        }
      }
    }
    for (let i in keys) {
      table.find("thead tr .rd-map-key")[i].value = keys[i];
    }
    for (let i in map) {
      if (i > 0) {
        addRow(table);
      }  
      let tr = table.find("tr:last-child");
      let row = map[i];
      tr.find(".rd-value").val(row.value);
      tr.find(".rd-label").val(row.label);
      tr.find(".rd-group").val(row.group);
      if (map[i]["selected"] == 'true' || map[i]["selected"] === true) {
        tr.find(".rd-selected")[0].checked = true;
      }
      for (let k in keys) {
        tr.find(".rd-map-value")[k].value = map[i][keys[k]];
      }
    }
  }
}

/**
 * Retrieves map data from the form and compiles it into an array of objects.
 *
 * @returns {Array} An array of objects containing the map data retrieved from the form.
 */
function getMapData() {
  let result = [];
  let selector = '[data-name="map"]';
  let keys = [];
  if ($(selector).find("thead").find("tr").find(".rd-map-key").length > 0) {
    $(selector)
      .find("thead")
      .find("tr")
      .find(".rd-map-key")
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
      let group = tr.find(".rd-group").val().trim();
      let selected = tr.find(".rd-selected")[0].checked ? 'true':'false';
      let opt = {
        value: value,
        label: label,
        group: group,
        selected: selected,
      };
      if (keys.length > 0) {
        let idx = 0;
        tr.find(".rd-map-value").each(function (e) {
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

/**
 * Fixes the value by converting it to appropriate types (boolean, null, number, or string).
 *
 * @param {string} value - The value to be fixed.
 * @returns {boolean|null|number|string} The fixed value.
 */
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

/**
 * Parses a string into a number, returning either an integer or a float.
 *
 * @param {string} str - The string to be parsed.
 * @returns {number} The parsed number.
 */
function parseNumber(str) {
  if (str.indexOf(".") !== -1) {
    return parseFloat(str);
  } else {
    return parseInt(str);
  }
}

/**
 * Checks if a string is numeric.
 *
 * @param {string} str - The string to be checked.
 * @returns {boolean} True if the string is numeric, false otherwise.
 */
function isNumeric(str) {
  if (typeof str != "string") return false;
  return !isNaN(str) && !isNaN(parseFloat(str));
}

/**
 * Sets the language options in the target language selects based on the provided languages array.
 *
 * @param {Array} languages - An array of language objects containing name and code.
 */
function setLanguage(languages) {
  $('select.target-language').each(function () {
    let select = $(this);
    select.empty();
    for (let d in languages) {
      select[0].options[select[0].options.length] = new Option(languages[d].name + ' - ' + languages[d].code, languages[d].code);
    }
  });
}

/**
 * Generates the HTML structure for a reference configuration form.
 *
 * The form includes options for selecting different reference types such as:
 * - Entity
 * - Map
 * - Yes/No
 * - True/False
 * - 1/0
 *
 * Each reference type section is designed with input fields and tables for:
 * - Entity details (name, table name, primary key, etc.)
 * - Map details (value, label, additional attributes)
 * - Specification, sortable, and additional output configurations.
 * - Selection method (single or multiple) for both entity and map sections.
 *
 * @returns {string} The HTML string for the reference configuration form.
 */
function getReferenceResource() {
  return referenceResource;
}