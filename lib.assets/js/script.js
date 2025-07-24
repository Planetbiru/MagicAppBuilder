let initielized = false;
let currentEntity = '';
let currentValidator = '';
let currentModule = '';
let currentEntity2Translated = '';
let lastErrorLine = -1;
let ajaxPending = 0;
let referenceResource = '';
let currentEntityName = '';
let currentValidatorName = '';
let currentTableName = '';
let currentTableStructure = {};

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
 * Updates the width of the `.ajax-pending` element to visually represent the current `ajaxPending` count.
 * The width is calculated as `ajaxPending * 16` pixels.
 */
function updatePendingBar() {
  $('.ajax-pending').css('width', (ajaxPending * 16) + 'px');
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
    initFileManager();
  });
});

/**
 * Show waiting screen
 *
 * This function displays a waiting screen overlay, typically used to indicate
 * that an asynchronous operation is in progress. It hides the main content's
 * scrollbar and sets the waiting screen to be visible.
 *
 * @returns {void}
 */
function showWaitingScreen() {
    document.body.style.overflow = 'hidden';
    document.querySelector('.waiting-screen').style.display = 'block';
    document.querySelector('.all').style.height = 'calc(100vh - 32px)';
    document.querySelector('.all').style.overflow = 'hidden';
}

/**
 * Hide waiting screen
 *
 * This function hides the waiting screen overlay, restoring the main content's
 * scrollability. It should be called after the asynchronous operation has completed.
 *
 * @returns {void}
 */
function hideWaitingScreen() {
    document.body.style.overflow = 'auto';
    document.querySelector('.waiting-screen').style.display = 'none';
    document.querySelector('.all').style.height = 'auto';
    document.querySelector('.all').style.overflow = 'auto';
}

/**
 * Checks the status of an application periodically until it is marked as 'finish'.
 *
 * Makes an AJAX request to check the current application status. If the status is not 'finish',
 * it will recursively call itself every second. Once finished, it calculates the time taken and
 * displays a toast notification. It also hides the waiting screen and triggers resource loading.
 *
 * @param {string} id - The application ID to check.
 * @param {number} startTime - The timestamp when the check started, used to calculate elapsed time.
 */
function setCheckingStatus(id, startTime)
{
  $.ajax({
    type: 'GET', 
    data: {userAction:'check-status', applicationId: id},
    url: 'lib.ajax/application-status.php',
    dataType: 'json',
    success: function(data)
    {
      if(data.applicationStatus !== 'finish')
      {
        setTimeout(function(){
          setCheckingStatus(id, startTime);
        }, 1000);
      }
      else
      {
        let seconds = ((new Date()).getTime() - startTime) / 1000;
        showToast("Application Ready", `The application was successfully created in ${seconds.toFixed(2)} seconds.`);
        updateApplicationInfo(id);
        hideWaitingScreen();
        loadAllResource();
      }
    },
    error: function(err)
    {
      console.error(err);
    }
  });
}

/**
 * Sends a request to update the application's information on the server.
 *
 * This function increases the global AJAX pending counter before sending the request,
 * and decreases it upon success or error. It uses a POST request to inform the server
 * to update the specified application's information.
 *
 * @param {string} id - The unique ID of the application whose information should be updated.
 */
function updateApplicationInfo(id)
{
  increaseAjaxPending();
  $.ajax({
    type: 'POST', 
    data: {userAction:'update-info', applicationId: id},
    url: 'lib.ajax/application-status.php',
    dataType: 'json',
    success: function(data)
    {
      decreaseAjaxPending();
    },
    error: function(err)
    {
      decreaseAjaxPending();
    }
  });
}

/**
 * Populates the entity generator form with values derived from the selected table.
 *
 * This function extracts the table name, converts it to an entity name using the 
 * `upperCamelize` function, and retrieves the primary key(s) from the selected table option.
 * It then updates the form fields for the entity name and primary key.
 *
 * @param {jQuery} table - The jQuery object representing the table dropdown element.
 */
function fillEntityGeneratorForm(table)
{
  let frm = table.closest('table');
  let tableName = table.val();
  let entityName = upperCamelize(tableName);
  let primaryKeys = table.find('option:selected').attr('data-primary-keys').split(',');
  let primaryKey = primaryKeys[0];
  frm.find('[name="entity_generator_entity_name"]').val(entityName);
  frm.find('[name="entity_generator_primary_key"]').val(primaryKey);
}

/**
 * Saves the feature form data by collecting checkbox and radio button values
 */
function saveFeatureForm()
{
  let data = {};
  $('#modal-module-features').find('input[type="checkbox"]').each(function(){
    data[$(this).attr('name')] = $(this)[0].checked;
  });
  $('#modal-module-features').find('input[type="radio"]').each(function(){
    if($(this)[0].checked)
    {
      data[$(this).attr('name')] = $(this).val();
    }
  });
  increaseAjaxPending();
  $.ajax({
    type: 'POST',
    url: 'lib.ajax/module-features.php',
    data: {data: JSON.stringify(data)},
    dataType: 'json',
    success: function(data)
    {
      decreaseAjaxPending();
    },
    error: function(err)
    {
      decreaseAjaxPending();
    }
  });
}

/**
 * Restores the feature form to its default state by making an AJAX request
 */
function restoreFeatureForm() {
  increaseAjaxPending();
  $.ajax({
    type: 'GET',
    url: 'lib.ajax/module-features.php',
    dataType: 'json',
    success: function (data) {
      if(typeof data.activate_deactivate == 'undefined')
      {
        asyncAlert(
          'No cononfiguration found for the current application.',  
          'Configuration Not Found',  
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
        // Restore checkbox states
        $('#modal-module-features').find('input[type="checkbox"]').each(function () {
          const name = $(this).attr('name');
          if (data.hasOwnProperty(name)) {
            $(this).prop('checked', data[name]);
          }
        });

        // Restore radio button selections
        $('#modal-module-features').find('input[type="radio"]').each(function () {
          const name = $(this).attr('name');
          const value = $(this).val();
          if (data.hasOwnProperty(name) && data[name] === value) {
            $(this).prop('checked', true);
          }
        });
        
        if(data.with_approval)
        {
          $('#approval_type1')[0].disabled = false;
          $('#approval_type2')[0].disabled = false;  
          $('#approval_position1')[0].disabled = false;
          $('#approval_position2')[0].disabled = false;
          $('#approval_by_other_user')[0].disabled = false; 
          $('#approval_bulk')[0].disabled = false;
        }
        else
        {
          $('#approval_type1')[0].disabled = true;
          $('#approval_type2')[0].disabled = true;
          $('#approval_position1')[0].disabled = true;
          $('#approval_position2')[0].disabled = true;
          $('#approval_by_other_user')[0].disabled = true;
          $('#approval_bulk')[0].disabled = true;
        }
        
        if(data.export_to_csv)
        {
          $('#export_use_temporary')[0].disabled = false; 
        }
        else
        {
          $('#export_use_temporary')[0].disabled = true;
        }
        if(data.backend_only)
        {
          $('#ajax_support')[0].disabled = true;
        }
        else
        {
          $('#ajax_support')[0].disabled = false;
        }
      }
      decreaseAjaxPending();
    },
    error: function (err) {
      console.error(err);
      decreaseAjaxPending();
    }
  });
}

let validatorBuilder = null;
let valBuilder = null;

function createValidator(elem)
{
  increaseAjaxPending();
  currentValidator = $('#validationMasterModal [name="validatorName"]').val();
  $.ajax({
    type: 'POST',
    dataType: 'html',
    url: 'lib.ajax/validator-create.php',
    data: {
      userAction: 'create', 
      tableName: $('#validationMasterModal [name="tableName"]').val(),
      validator: $('#validationMasterModal [name="validatorName"]').val(), 
      definition: $('#validationMasterModal [name="validatorDefinition"]').val()
    },
    success: function(data)
    {
      $(".validator-container-file .validator-list").empty().append(data);
      $(".container-translate-validator .validator-list").empty().append(data);
      // Mark tab
      $('button#validator-file-tab').removeClass('text-danger');
      let errorCount = $(".validator-container-file .validator-list").find('a[data-error="true"]').length;
      if(errorCount)
      {
        $('button#validator-file-tab').addClass('text-danger');
      }
      clearValidatorFile();
      clearTtransEd5();
      clearTtransEd6();

      let lineNumber = 0;
      decreaseAjaxPending();
      $('#validationMasterModal').modal('hide');
      $('.modal-backdrop').css('display', 'none');
      $('body').removeClass('modal-open');
      getValidatorFile(currentValidator, function () {
        $('.validator-container-file .validator-li').removeClass("selected-file");
        $('.validator-container-file .validator-li [data-validator-name="'+currentValidator+'"]').closest('li').addClass("selected-file");
      }, lineNumber);
    },
    error: function(e)
    {
      decreaseAjaxPending();
    }
  });
}

function addValidatorForm()
{
  increaseAjaxPending();
  $.ajax({
    type: 'POST',
    dataType: 'html',
    url: 'lib.ajax/validator-create-form.php',
    data: {userAction: 'select-table'},
    success: function(data)
    {
      $('#validationMasterModal .modal-header .modal-title').text('Create New Validator');
      $('#validationMasterModal .modal-body').empty().append(data);
      $('#validationMasterModal .master-validation-modal-ok').text('Create Form');
      $('#validationMasterModal .master-validation-modal-ok').attr('onclick', "selectTableForNewValidator(this)");
      
      $('#validationMasterModal').data('bs.modal', null);
      $('#validationMasterModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: true
      });
      if(!valBuilder)
      {
        valBuilder = new ValidationBuilder('#validationMasterModal', '.validation-modal-merged', '#validationMasterModal .validation-output', '#validationMasterModal .field-group');
      }
      decreaseAjaxPending();
    },
    error: function()
    {
      decreaseAjaxPending();
    }
  });
}

function selectTableForNewValidator(elem)
{
  let tableName = elem.form.querySelector('[name="tableName"]').value;
  let validatorName = elem.form.querySelector('[name="validatorName"]').value;
  increaseAjaxPending();
  $.ajax({
    type: 'POST',
    dataType: 'html',
    url: 'lib.ajax/validator-create-form.php',
    data: {userAction: 'create-form', tableName: tableName, validatorName:validatorName},
    success: function(data)
    {

      valBuilder.setValidation({});
      $('#validationMasterModal .modal-header .modal-title').text('Create New Validator');
      $('#validationMasterModal .modal-body').empty().append(data);
      $('#validationMasterModal .master-validation-modal-ok').text('Create');
      $('#validationMasterModal .master-validation-modal-ok').attr('onclick', "createValidator(this)");
      $('#validationMasterModal').data('bs.modal', null);
      // Tampilkan modal dengan opsi yang benar-benar baru
      $('#validationMasterModal').modal({
        keyboard: false,
        show: true
      });
      decreaseAjaxPending();
    },
    error: function()
    {
      decreaseAjaxPending();
    }
  });
}

/**
 * Submits the form data to the validator test endpoint via AJAX.
 * Displays success or error messages in a modal.
 *
 * @param {HTMLElement} elem The element that triggered the function (e.g., the submit button).
 */
function testValidator(elem) {
  let frm = elem.form;
  increaseAjaxPending(); // Assumption: this function exists for loading indicators

  $.ajax({
    type: 'post',
    dataType: 'json',
    url: 'lib.ajax/validator-test.php?validator=' + encodeURIComponent(currentValidator),
    data: $(frm).serialize(), // Gathers all data from the form
    success: function(data) {
      
      const modalBody = $('#genericModal .modal-body');

      // Clear validation states
      modalBody.find('input').removeClass('is-invalid');

      if (data && data.success) // NOSONAR
      {
        modalBody.find('.validator-test-message').empty().append(
          '<div class="alert alert-success">' + data.message + '</div>'
        );
      } else {
        
        let errorMessage = 'An error occurred.';
        if (data && data.message) // NOSONAR
        {
          errorMessage = data.message;
        }

        modalBody.find('.validator-test-message').empty().append(
          '<div class="alert alert-danger">' + errorMessage + '</div>'
        );


        if (data.propertyName) {
          let inputElem = modalBody.find('[name="' + data.propertyName + '"]');
          inputElem.addClass('is-invalid').select();
        }
      }

      decreaseAjaxPending(); // Assumption: this function exists
    },
    error: function(jqXHR, textStatus, errorThrown) {
      $('#validationMasterModal .modal-body .validator-test-message').empty().append(
        '<div class="alert alert-danger">AJAX Error: ' + textStatus + ' - ' + errorThrown + '</div>'
      );
      decreaseAjaxPending(); // Assumption: this function exists
    }
  });
}

function updateValidatorForm()
{
  $('#validationMasterModal .modal-header .modal-title').text('Update Validator');
  $('#validationMasterModal .modal-body').empty();
  $('#validationMasterModal .master-validation-modal-ok').text('Update');
  $('#validationMasterModal .master-validation-modal-ok').attr('onclick', "createValidator(this)");
  $.ajax({
    type: 'post',
    dataType: 'html',
    url: 'lib.ajax/validator-create-form.php',
    data: {userAction: 'update-form', validator: currentValidator}, 
    success: function(data) {
      decreaseAjaxPending(); 
      $('#validationMasterModal .modal-body').append(data);
      if(!valBuilder)
      {
        valBuilder = new ValidationBuilder('#validationMasterModal', '.validation-modal-merged', '#validationMasterModal .validation-output', '#validationMasterModal .field-group');
      }
      let json = JSON.parse($('#validationMasterModal .modal-body [name="existing"]').val());
      valBuilder.setValidation(json.properties);
      valBuilder.renderValidationsMerged();
    },
    error: function(jqXHR, textStatus, errorThrown) {

      decreaseAjaxPending(); 
    }
  });
  
  $('#validationMasterModal').data('bs.modal', null);
  $('#validationMasterModal').modal({
    backdrop: 'static',
    keyboard: false,
    show: true
  });
}

function renderValidatorEditor(data) {
  const wrapper = document.createElement('div');

  // Info table
  const infoTable = document.createElement('table');
  infoTable.className = 'config-table';
  infoTable.innerHTML = `
    <tbody>
      <tr><td>Table Name</td><td>${data.tableName}</td></tr>
      <tr><td>Validator Class Name</td><td>${data.className}</td></tr>
    </tbody>
  `;
  wrapper.appendChild(infoTable);

  // Fields
  Object.entries(data.properties).forEach(([fieldName, fieldData]) => {
    const fieldGroup = document.createElement('div');
    fieldGroup.className = 'mb-3 field-group validation-item';
    fieldGroup.dataset.fieldName = fieldName;
    fieldGroup.dataset.maximumLength = fieldData?.validators?.find(v => v.validationType === 'MaxLength')?.attributes?.value || '';

    fieldGroup.innerHTML = `
      <hr>
      <span class="form-label">${fieldName}</span>
      <div class="field-validations-list mt-2"></div>
      <button type="button" class="btn btn-primary mt-2 add-validation-merged">
        <i class="fa-solid fa-plus"></i> Add Validation
      </button>
    `;
    wrapper.appendChild(fieldGroup);
  });

  // Hidden inputs + output textarea
  wrapper.innerHTML += `
    <hr>
    <span class="form-label">Definition</span>
    <input type="hidden" name="tableName" value="${htmlEncode(data.tableName)}">
    <input type="hidden" name="validatorName" value="${htmlEncode(data.className)}">
    <textarea class="form-control validation-output" name="validatorDefinition" rows="5" readonly></textarea>
  `;

  return wrapper;
}

function htmlEncode(str) {
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}


/**
 * Displays a modal containing a form generated for the current validator.
 * The form's content is fetched via an AJAX call.
 */
function showTestValidatorForm()
{
  increaseAjaxPending();
  $.ajax({
    type: 'POST',
    dataType: 'html',
    url: 'lib.ajax/validator-form.php',
    data: {validator: currentValidator},
    success: function(data)
    {
      $('#genericModal .modal-header .modal-title').text(currentValidator+' Test');
      $('#genericModal .modal-body').empty().append(data);
      $('#genericModal .generic-modal-ok').text('Test Validator');
      $('#genericModal .generic-modal-ok').attr('onclick', "testValidator(this)");
      $('#genericModal').modal('show');
      decreaseAjaxPending();
    },
    error: function()
    {
      decreaseAjaxPending();
    }
  });
}

function deleteValidatorFile()
{
  asyncAlert(
    `Do you want to delete file ${currentValidator}.php?`,  // Message to display in the modal
    'Confirmation',  
    [
      {
        'caption': 'Yes',  
        'fn': () => {
          
          increaseAjaxPending();
          $.ajax({
            type: "POST",
            url: "lib.ajax/validator-delete.php",
            dataType: "json",
            data: { validator: currentValidator},
            success: function (data) {
              decreaseAjaxPending();
              updateValidatorFile();
              resetFileManager();
              removeHilightLineError(cmEditorValidator);
              if(data.success)
              {
                setValidatorFile('');
                $('#button_save_validator_file').attr('disabled', 'disabled');
                $('#button_save_validator_file_as').attr('disabled', 'disabled');
                $('#button_test_validator').attr('disabled', 'disabled');
                $('#button_delete_validator_file').attr('disabled', 'disabled');
                $('#button_update_validator_file').attr('disabled', 'disabled');
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
};

/**
 * Uploads an application file and handles the server response.
 *
 * This function is used for both previewing and importing application files.
 * It sends the file and related metadata to the server and updates the UI based on the response.
 *
 * @param {File} file - The file selected by the user for upload.
 * @param {string} action - The action to perform: either 'preview' or 'import'.
 * @param {string} [application_id] - The ID of the application (if available).
 * @param {string} [application_name] - The name of the application (if available).
 * @param {string} [base_application_directory] - The base directory path for the application (if applicable).
 */
function handleApplicationFileUpload(file, action, application_id, application_name, base_application_directory) {
    const importInfoDiv = $('#modal-application-import .import-message');
    const updateBtn = $('#modal-application-import .button-save-application-import');

    const formData = new FormData();
    formData.append('user_action', action);
    if(application_id)
    {
      formData.append('application_id', application_id);
    }
    if(application_name)
    {
      formData.append('application_name', application_name);
    }
    if(base_application_directory)
    {
      formData.append('base_application_directory', base_application_directory);
    }
    formData.append('file[]', file);

    $('#modal-application-import [name="file_name"]').val(file.name);
    importInfoDiv.html('<div class="alert alert-info">Uploading and parsing file...</div>');

    $.ajax({
        url: 'lib.ajax/application-import.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            if (data.status === 'success') {
              $('#modal-application-import [name="application_name"]').val(data.data.application_name);
              $('#modal-application-import [name="application_id"]').val(data.data.application_id);
              $('#modal-application-import [name="base_application_directory"]').val(data.data.base_application_directory);
              updateBtn[0].disabled = false;
              if(action == 'import')
              {
                loadAllResource();
                $('#modal-application-import').modal('hide');
              }
            } else {
              updateBtn[0].disabled = true;
            }
            importInfoDiv.html(`<div class="alert alert-${data.status}">${data.message}</div>`);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            updateBtn[0].disabled = true;
            console.error('Error during file upload:', textStatus, errorThrown, jqXHR);
            let errorMessage = `Error: Failed to upload or parse file. ${errorThrown}`;
            if (jqXHR.responseText) {
                errorMessage += `<br>Server Response: ${jqXHR.responseText.substring(0, 200)}...`;
            }
            importInfoDiv.html(`<div class="alert alert-danger">${errorMessage}</div>`);
        }
    });
}

/**
 * Generates a namespaced localStorage key based on the current URL path.
 * 
 * This function ensures that localStorage keys are unique per directory path
 * within the same domain. This avoids collisions when multiple applications
 * are hosted under different subpaths of the same domain.
 * 
 * Example:
 * - Current URL: https://example.com/admin/dashboard
 *   Original key: "userSettings"
 *   Resulting key: "admin__userSettings"
 * 
 * @param {string} key - The original localStorage key to namespace.
 * @returns {string} - A namespaced key based on the current path.
 */
function getLocalStorageKey(key) {
    // Get the current URL path (e.g., /admin/dashboard)
    const path = window.location.pathname;

    // Determine the directory part of the path (e.g., /admin/)
    const dirname = path.endsWith('/')
        ? path
        : path.substring(0, path.lastIndexOf('/') + 1); // NOSONAR

    // Convert slashes to underscores and remove leading/trailing underscores
    const prefix = dirname.replace(/\/+/g, '_').replace(/^_+|_+$/g, ''); // NOSONAR

    // If a valid prefix exists, prepend it to the key
    return prefix ? `${prefix}__${key}` : key;
}

/**
 * Initialize all event handlers and elements
 */
let initAll = function () {

  $(document).on('click', '.update-trash-entity', function(e){
    let applicationId = $(this).closest('form').find('[name="application_id"]').val();
    let tables = [];
    $(this).closest('form').find('.select-trash-table').each(function(e){
      if($(this)[0].checked)
      {
        tables.push($(this).val());
      }
    });
    let data = {
      applicationId: applicationId,
      userAction: 'update',
      table: tables
    };
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/entity-trash-update.php',
      data: data,
      dataType: 'html',
      success: function(response){
        $('.entity-trash-list-container').empty().append(response);
      }
    });
  });
  $(document).on('click', '.delete-trash-entity', function(e){
    let applicationId = $(this).closest('form').find('[name="application_id"]').val();  
    let tables = [];
    $(this).closest('form').find('.select-trash-table').each(function(e){
      if($(this)[0].checked)
      {
        tables.push($(this).val());
      }
    });
    let data = {
      applicationId: applicationId,
      userAction: 'delete',
      table: tables
    };
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/entity-trash-update.php',
      data: data,
      dataType: 'html',
      success: function(response){
        $('.entity-trash-list-container').empty().append(response);
      }
    });
  });

  $(document).on('hidden.bs.modal', '.modal', function () {
    setTimeout(() => {
      const anyModalShown = document.querySelector('.modal.show');
      if (!anyModalShown) {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').removeAttr('style');
      }
    }, 40);
  });

  $(document).on('hidden.bs.modal', '.validation-modal-merged', function () {
    setTimeout(function () {
      if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
      }
    }, 40);
  });

  $(document).on('click', '#button_create_validator_file', function(e){
    addValidatorForm();
  });
  $(document).on('click', '#button_update_validator_file', function(e){
    updateValidatorForm();
  });
  $(document).on('click', '#button_test_validator', function(e){
    showTestValidatorForm();
  });

  $(document).on('click', '#button_delete_validator_file', function(e){
    deleteValidatorFile();
  });
  
  $(document).on('click', '.button-load-string-format', function(e){
    increaseAjaxPending();
    $.ajax({
      type: 'get',
      dataType: 'json',
      url: 'lib.ajax/format-string.php',
      success: function(data)
      {
        $('#input-control-string-format').val(data.stringFormat);
        decreaseAjaxPending();
      },
      error: function()
      {
        decreaseAjaxPending();
      }
    })
  });
  $(document).on('click', '.button-save-string-format', function(e){
    increaseAjaxPending();
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: 'lib.ajax/format-string.php',
      data: {stringFormat: $('#input-control-string-format').val()},
      success: function(data)
      {
        decreaseAjaxPending();
      },
      error: function()
      {
        decreaseAjaxPending();
      }
    })
  });

  $(document).on('click', '.button-load-date-format', function(e){
    increaseAjaxPending();
    $.ajax({
      type: 'get',
      dataType: 'json',
      url: 'lib.ajax/format-date.php',
      success: function(data)
      {
        $('#input-control-date-format').val(data.dateFormat);
        decreaseAjaxPending();
      },
      error: function()
      {
        decreaseAjaxPending();
      }
    })
  });
  $(document).on('click', '.button-save-date-format', function(e){
    increaseAjaxPending();
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: 'lib.ajax/format-date.php',
      data: {dateFormat: $('#input-control-date-format').val()},
      success: function(data)
      {
        decreaseAjaxPending();
      },
      error: function()
      {
        decreaseAjaxPending();
      }
    })
  });

  $(document).on('click', '.button-load-number-format', function(e){
    increaseAjaxPending();
    $.ajax({
      type: 'get',
      dataType: 'json',
      url: 'lib.ajax/format-number.php',
      success: function(data)
      {
        $('#input-control-decimal').val(data.decimal);
        $('#input-control-decimal-separator').val(data.separator);
        $('#input-control-thousands-separator').val(data.thousandsSeparator);
        decreaseAjaxPending();
      },
      error: function()
      {
        decreaseAjaxPending();
      }
    })
  });
  $(document).on('click', '.button-save-number-format', function(e){
    increaseAjaxPending();
    $.ajax({
      type: 'post',
      dataType: 'json',
      url: 'lib.ajax/format-number.php',
      data: {
        decimal: $('#input-control-decimal').val(),
        separator: $('#input-control-decimal-separator').val(),
        thousandsSeparator: $('#input-control-thousands-separator').val()
      },
      success: function(data)
      {
        decreaseAjaxPending();
      },
      error: function()
      {
        decreaseAjaxPending();
      }
    })
  });
  
  validatorBuilder = new ValidationBuilder('.field-validation-modal', '.validation-modal', '.json-output', '.main-table tbody tr');
  $(document).on('click', '#button-load-module-features', function (e) {
    e.preventDefault();
    restoreFeatureForm();
  });
  
  $(document).on('click', '#button-save-module-features', function (e) {
    e.preventDefault();
    asyncAlert(
      'Do you want to save this configuration for the current application?',
      'Confirmation',
      [
        {
          'caption': 'Yes',
          'fn': () => {
            saveFeatureForm();
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
  
  $(document).on('click', '#button-clear-module-features', function (e) {
    e.preventDefault();
    resetFeatureForm();
  });
  
  $(document).on('click', '.group-reference', function(e2){
    let value = $(this).val();
    $(this).closest('table').attr('data-group-source', value);
  });

  $(document).on('click', '#button_create_entity_file', function(e){
    e.preventDefault();
    $('#modal-entity-generator').modal('show');
    increaseAjaxPending();
    $('.modal-body .entity-generator').load('lib.ajax/entity-generator-dialog.php?entityName='+encodeURIComponent(currentEntityName)+'&tableName='+encodeURIComponent(currentTableName), function(){
      decreaseAjaxPending();
      $('select[name="entity_generator_table_name"]').on('change', function(e2){
        fillEntityGeneratorForm($(this));
      });
    });
  });

  $(document).on('click', '.button-add-entity-suffix', function (e) {
    let entityName = $('[name="entity_generator_entity_name"]').val();
    if (!entityName.endsWith('Min')) {
      entityName += 'Min';
      $('[name="entity_generator_entity_name"]').val(entityName);
    }
  });

  $(document).on('click', '.button-create-entity', function (e) {
    let entityName = $('[name="entity_generator_entity_name"]').val();
    let tableName = $('[name="entity_generator_table_name"]').val();

    asyncAlert(
      'Are you sure you want to generate the entity and replace the existing file?',  // Message to display in the modal
      'Entity Generation Confirmation',  
      [
        {
          'caption': 'Yes', 
          'fn': () => {
            $('#modal-entity-generator').modal('hide');
            increaseAjaxPending();
            $.ajax({
              method: "POST",
              url: "lib.ajax/entity-generator.php",
              data: { entityName: entityName, tableName: tableName },
              success: function (data) {
                decreaseAjaxPending();
                updateEntityFile(function()// NOSONAR
                {
                  let selector = '';
                  let fullEntityName = '';
                  if(entityName.indexOf('App\\') == 0 || entityName.indexOf('Data\\') == 0)
                  {
                    selector = '.entity-li > [data-entity-name="'+entityName+'"]';
                    selector = selector.replace(/\\/g, '\\\\');
                    fullEntityName = ''+entityName;
                  }
                  else
                  {
                    selector = '.entity-li > [data-entity-name="Data\\\\'+entityName+'"]';
                    fullEntityName = 'Data\\'+entityName;
                  }
                  let el = $(selector);
                  getEntityFile([fullEntityName], function() // NOSONAR
                  { 
                    $('.entity-container-file .entity-li').removeClass("selected-file");
                    el.closest('li').addClass("selected-file");
                  });
                });
                resetFileManager();
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
                resetFileManager();
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
                resetFileManager();
                updateEntityQuery(true);
                updateEntityRelationshipDiagram();
                removeHilightLineError(cmEditorFile);
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
    if(tableName == '')
    {
      $('[name="source_table"]').focus();
    }
    else
    {
      loadColumn(tableName, selector);
    }
    
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

  $(document).on("change", '[name="multi_level_menu"]', function(e) {
    e.preventDefault();
    
    let isChecked = $(this).is(':checked');
    let $activeThemeSelect = $('[name="active_theme"]');
    
    $activeThemeSelect.find('option').each(function() {
        const supportsMultiLevel = $(this).data('multi-level-menu');
        $(this).prop('disabled', isChecked ? !supportsMultiLevel : supportsMultiLevel);
    });

    // Find the first enabled option and set the select's value to it
    let $firstEnabledOption = $activeThemeSelect.find('option:enabled').first();
    if ($firstEnabledOption.length) { // Check if an enabled option was found
        $activeThemeSelect.val($firstEnabledOption.val());
    }
  });


  $(document).on("click", ".button-save-application-config", function (e) {
    e.preventDefault();
    let form = $(this).closest(".modal").find('form');
    let inputs = form.serializeArray();
    let dataToPost = {
      name: form.find('[name="application_name"]').val(),
      architecture: form.find('[name="application_architecture"]').val(),
      base_application_directory: form.find('[name="application_base_directory"]').val(),
      base_application_url: form.find('[name="application_url_directory"]').val(),
      description: form.find('[name="description"]').val(),
      multi_level_menu: form.find('[name="multi_level_menu"]')[0].checked,
      active_theme: form.find('[name="active_theme"]').val(),
      database: {},
      sessions: {},
      account_security: {},
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
      if (name.indexOf("account_security_") !== -1) {
        dataToPost.account_security[name.substring(17)] = inputs[i].value;
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
    let checkedValue = $(this).val();  // Get the value of the selected radio button
    let name = $(this).attr('name');  // Get the name of the radio button group
    
    // Remove 'checked' attribute from all radio buttons with the same 'name' in the same row
    $(this).closest('tr').find('input[type="radio"][name="'+name+'"]').each(function() {
        // Remove the checked status from all radio buttons
        this.checked = false;
        // Optionally, you can remove the 'checked' attribute from HTML if needed
        $(this).removeAttr('checked');
        $(this).attr('data-checked', 'false');
    });

    // Mark the selected element by directly setting `checked`
    $(this)[0].checked = true;  // Set the selected element to checked
    $(this).attr('data-checked', 'true');
    $(this).attr('checked', 'checked');
    // Call an additional function to process further data
    prepareReferenceData(checkedValue, $(this));
  });

  $(document).on("click", ".reference_button_data", function (e) {
    e.preventDefault();
    let referenceDialog = $("#modal-create-reference-data");
    referenceDialog
      .find(".modal-title")
      .text("Create Data Reference");
    referenceDialog.attr("data-reference-type", "data");
    let parentTd = $(this).closest("td");
    let parentTr = parentTd.closest("tr");
    let fieldName = parentTr.attr("data-field-name");
    let key = $(this).siblings("input").attr("name");

    referenceDialog.attr("data-input-name", key);
    referenceDialog.attr("data-field-name", fieldName);
    referenceDialog.find(".modal-body").empty();
    referenceDialog
      .find(".modal-body")
      .append(getReferenceResource());

    let value = $('[name="' + key + '"]').val();
    if (value.length < 60) {
      loadReference(fieldName, key, function (obj) {
        if (obj != null) {
          deserializeForm(obj);
          if(parentTd.attr('data-valid') != 'true')
          {
            validateEntityName();
            validateReference();
          }
          else
          {
            referenceDialog.find('.input-with-checker').attr('data-valid', 'true');
          }
        }
      });
    }
    if (value.length > 20) {
      let obj = parseJsonData(value);
      if (typeof obj != 'object') {
        obj = {};
      }
      deserializeForm(obj);
      if(parentTd.attr('data-valid') != 'true')
      {
        validateEntityName();
        validateReference();
      }
      else
      {
        referenceDialog.find('.input-with-checker').attr('data-valid', 'true');
      }
      
    }
    referenceDialog.attr('data-type', 'reference');
    referenceDialog.modal("show");
  });

  $(document).on("click", ".reference_button_filter", function (e) {
    e.preventDefault();
    let referenceDialog = $("#modal-create-reference-data");
    referenceDialog
      .find(".modal-title")
      .text("Create Filter Reference");
    referenceDialog.attr("data-reference-type", "filter");

    let parentTd = $(this).closest("td");
    let parentTr = parentTd.closest("tr");
    let fieldName = parentTr.attr("data-field-name");
    let key = $(this).siblings("input").attr("name");

    referenceDialog.attr("data-input-name", key);
    referenceDialog.attr("data-field-name", fieldName);
    referenceDialog.find(".modal-body").empty();
    referenceDialog.find(".modal-body").append(getReferenceResource());

    let value = $('[name="' + key + '"]').val().trim();
    if (value.length < 60) {
      loadReference(fieldName, key, function (obj) {
        if (obj != null) {
          deserializeForm(obj);
          if(parentTd.attr('data-valid') != 'true')
          {
            validateEntityName();
            validateReference();
          }
          else
          {
            referenceDialog.find('.input-with-checker').attr('data-valid', 'true');
          }
        }
      });
    }
    if (value.length > 20) {
      let obj = parseJsonData(value);
      if (typeof obj != 'object') {
        obj = {};
      }
      deserializeForm(obj);
      if(parentTd.attr('data-valid') != 'true')
      {
        validateEntityName();
        validateReference();
      }
      else
      {
        referenceDialog.find('.input-with-checker').attr('data-valid', 'true');
      }
    }
    referenceDialog.attr('data-type', 'filter');
    referenceDialog.modal("show");
  });

  $(document).on("click", "#apply_reference", function (e) {
    let referenceDialog = $("#modal-create-reference-data");
    let key = referenceDialog.attr("data-input-name");
    let value = JSON.stringify(serializeForm());
    $('[name="' + key + '"]').val(value);
    
    let valid = (referenceDialog.attr('data-type') == 'filter' && referenceDialog.find('.entity-section .input-with-checker[data-valid="true"]').length >= 4) || 
    (referenceDialog.attr('data-type') == 'reference' && referenceDialog.find('.entity-section .input-with-checker[data-valid="true"]').length >= 6);
    
    $('[name="' + key + '"]').closest('td').attr('data-valid', valid ? 'true' : 'false');
    
    referenceDialog.modal("hide");
    
    
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


  $(document).on("change", "#backend_only", function (e) {
    if($(this)[0].checked)
    {
      $('#ajax_support')[0].checked = false;
      $('#ajax_support')[0].disabled = true;
    }
    else
    {
      $('#ajax_support')[0].disabled = false;
    }
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
    let url = modal.find('[name="application_url"]').val().trim();
    let namespace = modal.find('[name="application_namespace"]').val().trim();
    let workspace_id = modal.find('[name="application_workspace_id"]').val().trim();
    let author = modal.find('[name="application_author"]').val().trim();
    let magic_app_version = modal.find('[name="magic_app_version"]').val();
    let composer_online = modal.find('[name="installation_method"]').val().toLowerCase().indexOf('online') === 0;
    let paths = [];

    $('#modal-create-application table.path-manager tbody tr').each(function () {
      let tr = $(this);
      let name = tr.find('td:nth-child(1) input[type="text"]').val();
      let path = tr.find('td:nth-child(2) input[type="text"]').val();
      let active = tr.find('td:nth-child(3) input[type="checkbox"]')[0].checked;
      paths.push({ name: name, path: path, active: active });
    });

    if (name != "" && id != "" && directory != "" && author != "") {
      showWaitingScreen();
      increaseAjaxPending();
      
      setCheckingStatus(id, (new Date()).getTime());
      
      $.ajax({
        method: "POST",
        url: "lib.ajax/application-create.php",
        dataType: "html",
        data: {
          id: id,
          application_name: name,
          application_architecture: architecture,
          application_description: description,
          application_directory: directory,
          application_url: url,
          application_namespace: namespace,
          workspace_id: workspace_id,
          author: author,
          paths: paths,
          magic_app_version: magic_app_version,
          composer_online: composer_online
        },
        success: function (data) {
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
      data: { userAction: 'get' },
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
        data: { userAction: 'update', paths: paths },
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
      data: { userAction: 'default', selected_path: select.val() },
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
    let entityName = $(this).attr("data-entity-name");
    let tableName = $(this).attr("data-table-name");
    let lineNumber = parseInt($(this).attr("data-error-line-number"));
    currentEntityName = entityName;
    currentTableName = tableName;
    let el = $(this);
    getEntityFile([entityName], function () {
      $('.entity-container-file .entity-li').removeClass("selected-file");
      el.closest('li').addClass("selected-file");
    }, lineNumber);
  });
  
  $(document).on("click", ".validator-container-file .validator-li a", function (e) {
    e.preventDefault();
    let validatorName = $(this).attr("data-validator-name");
    let lineNumber = parseInt($(this).attr("data-error-line-number"));
    currentValidatorName = validatorName;
    let el = $(this);
    getValidatorFile(validatorName, function () {
      $('.validator-container-file .validator-li').removeClass("selected-file");
      el.closest('li').addClass("selected-file");
    }, lineNumber);
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
  
  $(document).on("click", "#button_save_validator_file", function (e) {
    e.preventDefault();
    saveValidator();
  });

  $(document).on("click", "#button_save_validator_file_as", function (e) {
    e.preventDefault();
    saveValidatorAs();
  });

  $(document).on("click", "#button_save_entity_query", function (e) {
    e.preventDefault();
    saveQuery();
  });

  $("tbody.data-table-manual-sort").each(function () {
    let dataToSort = $(this)[0];
  
    // Initialize Sortable for the table
    Sortable.create(dataToSort, {
      animation: 150,
      scroll: true,
      handle: ".data-sort-handler",
  
      onStart: function (e1) {
        if (e1.item) {
          // Process checkboxes first
          let checkboxes = e1.item.querySelectorAll('input[type="checkbox"]');
          checkboxes.forEach(function (input) {
            const isChecked = input.checked;
            input.setAttribute('data-checked', isChecked ? 'true' : 'false');
            if (isChecked) {
              input.setAttribute('checked', 'checked');
            } else {
              input.removeAttribute('checked');
            }
          });
  
          // Process radios by group (name)
          let radioInputs = e1.item.querySelectorAll('input[type="radio"]');
          let radioNames = [...new Set([...radioInputs].map(r => r.name))]; // get unique names
  
          radioNames.forEach(function (name) {
            let group = e1.item.querySelectorAll(`input[type="radio"][name="${name}"]`);
            group.forEach(function (input) // NOSONAR
            {
              const isChecked = input.checked || input.getAttribute('checked');
              input.setAttribute('data-checked', isChecked ? 'true' : 'false');
              if (isChecked) {
                input.setAttribute('checked', 'checked');
              } else {
                input.removeAttribute('checked');
              }
            });
          });
        }
      },
  
      onEnd: function (e1) {
        let checkboxes = e1.item.querySelectorAll('input[type="checkbox"]');
        let radios = e1.item.querySelectorAll('input[type="radio"]');
  
        setTimeout(function () {
          // Restore checkbox state
          checkboxes.forEach(function (input) // NOSONAR
          {
            const isChecked = input.getAttribute('data-checked') === 'true';
            input.checked = isChecked;
            if (isChecked) {
              input.setAttribute('checked', 'checked');
            } else {
              input.removeAttribute('checked');
            }
            input.removeAttribute('data-checked');
          });
  
          // Restore radio state by group
          let radioNames = [...new Set([...radios].map(r => r.name))]; // NOSONAR
  
          radioNames.forEach(function (name) // NOSONAR
          {
            let group = e1.item.querySelectorAll(`input[type="radio"][name="${name}"]`);
            group.forEach(function (input) {
              const isChecked = input.getAttribute('data-checked') === 'true';
              input.checked = isChecked;
              if (isChecked) {
                input.setAttribute('checked', 'checked');
              } else {
                input.removeAttribute('checked');
              }
              input.removeAttribute('data-checked');
            });
          });
        }, 155);
      },
    });
  });

  

  $(document).on('click', '.add_suffix', function (e) {
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
                resetFileManager();
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
      $('#export_use_temporary')[0].checked = false;
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
      $('#export_use_temporary')[0].checked = false;
    }
  });

  $(document).on('change', '#with_approval', function (e) {
    let chk = $(this)[0].checked;
    if (chk) {
      $('#approval_by_other_user')[0].disabled = false;
      $('#approval_type1')[0].disabled = false;
      $('#approval_type2')[0].disabled = false;
      $('#approval_position1')[0].disabled = false;
      $('#approval_position2')[0].disabled = false;
      $('#approval_bulk')[0].disabled = false;
    }
    else {
      $('#approval_by_other_user')[0].checked = false;
      $('#approval_by_other_user')[0].disabled = true;
      $('#approval_type1')[0].disabled = true;
      $('#approval_type2')[0].disabled = true;
      $('#approval_position1')[0].disabled = true;
      $('#approval_position2')[0].disabled = true;
      $('#approval_bulk')[0].disabled = true;
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
    if (modal.find('.alert').length > 0) {
      modal.find('.alert').remove();
    }
    let createBtn = modal.find('#create_new_app');
    createBtn[0].disabled = true;
    modal.find('[name="application_name"]').val('');
    modal.find('[name="application_id"]').val('');
    modal.find('[name="application_directory"]').val('');
    modal.find('[name="application_workspace_id"]').val('');
    modal.find('[name="application_namespace"]').val('');
    modal.find('[name="application_author"]').val('');
    modal.find('[name="magic_app_version"]').empty();
    modal.find('[name="installation_method"]').val('');
    // Disable input
    $('#modal-create-application .modal-body :input').each(function(){
      $(this)[0].disabled = true;
    });
    increaseAjaxPending();
    resetCheckWriretableDirectory(modal.find('[name="application_directory"]'));
    $.ajax({
      type: 'GET',
      url: 'lib.ajax/application-new.php',
      success: function (data) {
        decreaseAjaxPending();       
        if (data.application_workspace.length == 0) {
          let alertDiv = $('<div />');
          alertDiv.addClass('alert alert-warning');
          alertDiv.html('Please select a workspace before creating a new application.');
          modal.find('form').prepend(alertDiv);
          createBtn[0].disabled = true;
        }
        else {
          modal.find('[name="application_name"]').val(data.application_name);
          modal.find('[name="application_id"]').val(data.application_id);
          modal.find('[name="application_architecture"]').val(data.application_architecture);
          modal.find('[name="application_directory"]').val(data.application_directory);
          modal.find('[name="application_url"]').val(data.application_url);
          modal.find('[name="composer_online"]').val(data.composer_online ? 1 : 0);
          modal.find('[name="application_namespace"]').val(data.application_namespace);
          modal.find('[name="application_workspace_id"]').empty();
          modal.find('[name="installation_method"]').empty();
          modal.find('[name="application_author"]').val(data.application_author);
          modal.find('[name="application_description"]').val(data.application_description);
          updateNewApplicationForm(data);
          checkWriretableDirectory(modal.find('[name="application_directory"]'));
          createBtn[0].disabled = false;

          // Enable input
          $('#modal-create-application .modal-body :input').each(function(){
            $(this)[0].disabled = false;
          });
          modal.find('[name="application_name"]')[0].select();
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
        resetFileManager();
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
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
        resetFileManager();
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
    });
  });

  $(document).on('click', '#button-save-app-translation', function (e) {
    let translated = transEd6.getDoc().getValue();
    let propertyNames = $('.app-property-name').val();
    let targetLanguage = $('.target-language').val();
    let translationType = $('#app_translation_type').val();

    let url = '';
    if(translationType == 'menu')
    {
      url = 'lib.ajax/menu-translate.php';
    }
    else if(translationType == 'menu-group')
    {
      url = 'lib.ajax/menu-group-translate.php';
    }
    else if(translationType == 'validation')
    {
      url = 'lib.ajax/validation-translate.php';
    }

    if(url != '')
    {
      increaseAjaxPending();
      $.ajax({
        method: "POST",
        url: url,
        dataType: "json",
        data: { userAction: 'set', translated: translated, propertyNames: propertyNames, targetLanguage: targetLanguage },
        success: function (data) {
          decreaseAjaxPending();
        },
        error: function (xhr, status, error) {
          decreaseAjaxPending();
        }
      });
    }
  });

  $(document).on('change', '.target-language-entity, .target-language-module', function (e) {
    let val = $(this).val();
    let translateFor = $(this).attr('data-translate-for');
    $('.target-language').val(val);
    reloadTranslate(translateFor);
  });
  
  $(document).on('change', '.target-language-app', function (e) {
    let val = $(this).val();
    $('.target-language').val(val);
    loadAppTranslation();
  });
  
  $(document).on('click', '.reload-app-translation', function (e) {    
    loadAppTranslation();
  });

  $(document).on('change', '.filter-translate', function (e) {
    let val = $(this).val();
    let translateFor = $(this).attr('data-translate-for');
    $('.filter-translate').val(val);
    reloadTranslate(translateFor);
  });

  $(document).on('click', '.reload-entity-translation', function(e){
    let val = $(this).parent().find('.filter-translate').val();
    let translateFor = $(this).parent().find('.filter-translate').attr('data-translate-for');
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

  $(document).on('click', '.load-menu-translation, .load-validation-translation, .load-menu-group-translation', function () {
    const isMenu = $(this).hasClass('load-menu-translation');
    const isValidation = $(this).hasClass('load-validation-translation');
    const isMenuGroup = $(this).hasClass('load-menu-group-translation');

    // Toggle active class for each button
    $('.load-menu-translation')
      .toggleClass('btn-primary', isMenu)
      .toggleClass('btn-secondary', !isMenu);

    $('.load-validation-translation')
      .toggleClass('btn-primary', isValidation)
      .toggleClass('btn-secondary', !isValidation);

    $('.load-menu-group-translation')
      .toggleClass('btn-primary', isMenuGroup)
      .toggleClass('btn-secondary', !isMenuGroup);

    // Load corresponding translation data
    if (isMenu) {
      loadMenuTranslation();
    } else if (isValidation) {
      loadValidationTranslation();
    } else if (isMenuGroup) {
      loadMenuGroupTranslation();
    }
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
      data: { userAction: 'get' },
      success: function (data) {
        decreaseAjaxPending();
        while ($('#modal-update-language table.language-manager > tbody > tr').length > 1) {
          $('#modal-update-language table.language-manager > tbody > tr:last').remove();
        }
        for (let i = 0; i < data.length; i++) {
          if (i > 0) {
            let clone = $('#modal-update-language table.language-manager > tbody > tr:first').clone();
            $('#modal-update-language table.language-manager > tbody').append(clone);
          }

          let child = i + 1;
          let clone2 = $('#modal-update-language table.language-manager > tbody > tr:nth-child(' + child + ')');

          if (clone2.length) {
            clone2.find('input[type="text"].language-name').val(data[i].name);
            clone2.find('input[type="text"].language-code').val(data[i].code);
            clone2.find('input[type="checkbox"]')[0].checked = data[i].active;
          }
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
        data: { userAction: 'update', languages: languages },
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
        error: function (xhr, status, error) {
          decreaseAjaxPending();
        }
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
              data: { userAction: 'execute', query: query },
              success: function (data) {
                decreaseAjaxPending();
                let ents = getEntitySelection();
                let merged = $(".entity-merge")[0].checked;
                let createNew = $(".entity-create-new")[0].checked;
                getEntityQuery(ents, merged, createNew);
                modal.modal('hide');
                loadTable();
              },
              error: function (xhr, status, error) {
                decreaseAjaxPending();
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

  });

  $(document).on('click', '.default-language', function (e) {
    e.preventDefault();
    let select = $('.target-language');
    increaseAjaxPending();
    $.ajax({
      method: "POST",
      url: "lib.ajax/application-language.php",
      data: { userAction: 'default', selected_language: select.val() },
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
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
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
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
    });
  });

  $(document).on('click', '.button-application-setting', function (e) {
    e.preventDefault();
    let updateBtn = $('#modal-application-setting .button-save-application-config');
    updateBtn[0].disabled = true;
    let applicationId = $(this).closest('.application-item').attr('data-application-id');
    $('#modal-application-setting .application-setting').empty();
    $('#modal-application-setting').modal('show');
    increaseAjaxPending();
    resetCheckWriretableDirectory($('#modal-application-setting [name="application_base_directory"]'));
    resetCheckWriretableDirectory($('#modal-application-setting [name="database_database_file_path"]'));
    $.ajax({
      type: 'GET',
      url: 'lib.ajax/application-setting.php',
      data: { applicationId: applicationId },
      dataType: 'html',
      success: function (data) {
        decreaseAjaxPending();
        $('#modal-application-setting .application-setting').empty().append(data);
        checkWriretableDirectory($('#modal-application-setting [name="application_base_directory"]'));
        checkWriretableDirectory($('#modal-application-setting [name="database_database_file_path"]'));
        setTimeout(function () {
          // set database_password to be empty
          // prevent autofill password
          $('#modal-application-setting .application-setting').find('[name="database_password"]').val('');
        }, 2000);
        updateBtn[0].disabled = false;
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
        updateBtn[0].disabled = false;
      }
    });
  });
  
  $(document).on('click', '.button-application-option', function (e) {
    e.preventDefault();
    let updateBtn = $('#modal-application-option .button-save-application-option');
    updateBtn[0].disabled = true;
    let applicationId = $(this).closest('.application-item').attr('data-application-id');
    $('#modal-application-option .application-option').empty();
    $('#modal-application-option').modal('show');
    increaseAjaxPending();

    $.ajax({
      type: 'GET',
      url: 'lib.ajax/application-option.php',
      data: { applicationId: applicationId },
      dataType: 'html',
      success: function (data) {
        decreaseAjaxPending();
        $('#modal-application-option').attr('data-application-id', applicationId);
        $('#modal-application-option .application-option').empty().append(data);
        updateBtn[0].disabled = false;
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
    });
  });
  
  // When the "Import Application" button is clicked
  $(document).on('click', '.button-import-application', function (e) {
      e.preventDefault();
      
      // Display initial info message in modal
      $('#modal-application-import .import-message').html('<div class="alert alert-info">Select file to import the application.</div>')
      $('#modal-application-import [name="application_id"]').val('');
      $('#modal-application-import [name="application_name"]').val('');
      $('#modal-application-import [name="base_application_directory"]').val('');
      $('#modal-application-import [name="file_name"]').val('');
      // Disable the "Import" button until a valid file is selected
      let updateBtn = $('#modal-application-import .button-save-application-import');
      updateBtn[0].disabled = true;

      // Show the modal for importing application
      $('#modal-application-import').modal('show');
  });

  // When the "Select File" button is clicked, trigger the hidden file input
  $(document).on('click', '.button-select-file-import', function (e) {
      $('#import-application-file').click(); // Simulate click on hidden file input
  });
  
  // Preview file (called first when user selects file)
  $(document).on('change', '#import-application-file', function () {
      if (this.files.length > 0) {
          handleApplicationFileUpload(this.files[0], 'preview');
      }
  });

  // Import file (re-uses selected file and sends as 'import')
  $(document).on('click', '.button-save-application-import', function () {
      const input = document.getElementById('import-application-file');
      let application_id = $('#modal-application-import [name="application_id"]').val();
      let application_name = $('#modal-application-import [name="application_name"]').val();
      let base_application_directory = $('#modal-application-import [name="base_application_directory"]').val();
      if (input.files.length > 0) {
          handleApplicationFileUpload(input.files[0], 'import', application_id, application_name, base_application_directory);
      }
  });
  
  
  $(document).on("click", ".button-save-application-option", function (e) {
    e.preventDefault();
    let form = $(this).closest(".modal").find('form');
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-option.php',
      data: { 
        userAction: 'save', 
        developmentMode: form.find('[name="development_mode"]:checked').val(), 
        bypassRole: form.find('[name="bypass_role"]:checked').val(), 
        accessLocalhostOnly: form.find('[name="access_localhost_only"]:checked').val(),
        applicationId: form.closest('.modal').attr('data-application-id') 
      },
      success: function (data) {
        let modal = form.closest('.modal');
        modal.modal('hide');
      },
    })
  });
  $(document).on('click', '#import-menu', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let applicationId = $(this).closest('.modal').attr('data-application-id');
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-menu-import.php',
      data: { applicationId: applicationId },
      dataType: 'html',
      success: function (data) {
        modal.find('.menu-container').empty().append(data);
      }
    });
  });
  
  $(document).on('click', '#create-user', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let applicationId = $(this).closest('.modal').attr('data-application-id');
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-user.php',
      data: { applicationId: applicationId },
      dataType: 'html',
      success: function (data) {
        decreaseAjaxPending();
        modal.find('.user-container').empty().append(data);
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
    });
  });
  
  $(document).on('click', '#reset-user-password', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let frm = $(this).closest('form');
    let applicationId = $(this).closest('.modal').attr('data-application-id');
    let adminIds = [];
    frm.find('.admin_id:checked').each(function (e) {
      let adminId = $(this).val();
      adminIds.push(adminId);
    });
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-user.php',
      data: { userAction: 'reset-user-password', applicationId: applicationId, adminId: adminIds },
      success: function (data) {
        decreaseAjaxPending();
        modal.find('.user-container').empty().append(data);
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
    });
  });
  
  $(document).on('click', '#set-user-role', function (e) {
    e.preventDefault();
    let modal = $(this).closest('.modal');
    let frm = $(this).closest('form');
    let applicationId = $(this).closest('.modal').attr('data-application-id');
    let adminIds = [];
    frm.find('.admin_id:checked').each(function (e) {
      let adminId = $(this).val();
      adminIds.push(adminId);
    });
    increaseAjaxPending();
    $.ajax({
      type: 'POST',
      url: 'lib.ajax/application-user.php',
      data: { userAction: 'set-user-role', applicationId: applicationId, adminId: adminIds },
      success: function (data) {
        decreaseAjaxPending();
        modal.find('.user-container').empty().append(data);
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
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

    showApplicationMenuDialog(applicationId);

  });

  $(document).on('click', '.button-manage-application-menu', function (e) {
    e.preventDefault();
    let updateBtn = $('#modal-application-menu .button-save-menu');
    updateBtn[0].disabled = true;
    let applicationId = $('meta[name="application-id"]').attr('content');
    showApplicationMenuDialog(applicationId);
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
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
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
      if (existingMenu[i].title.toLowerCase() == newMenu.toLowerCase()) {
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
        window.localStorage.setItem(getLocalStorageKey('workspace-id'), workspaceId);
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
        window.localStorage.setItem(getLocalStorageKey('application-id'), applicationId);
        resetCheckActiveApplication();
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
      }
    });
  });
  
  $(document).on('click', '.button-application-export', function (e) {
    e.preventDefault();
    
    let applicationId = $(this).closest('.application-item').attr('data-application-id') || '';

    // Create a temporary form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'lib.ajax/application-export.php';
    form.style.display = 'none';

    // Add input for applicationId
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'applicationId';
    input.value = applicationId;
    form.appendChild(input);

    // Append form to body and submit
    document.body.appendChild(form);
    form.submit();

    // Remove form after submission (optional)
    setTimeout(() => {
        document.body.removeChild(form);
    }, 1000);
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

  $(document).on("click", '.upload-application-icons', function () {
    let applicationId = $(this).closest('form').find('input[name="application_id"]').val();

    let el = document.querySelector('#iconFileInput');
    if (el) {
        el.parentNode.removeChild(el);
    }

    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = 'image/png, image/svg+xml';
    inputFile.id = 'iconFileInput';
    inputFile.style.position = 'absolute';
    inputFile.style.left = '-1000000px';
    inputFile.style.top = '-1000000px';

    inputFile.addEventListener('change', async function () {
        const selectedFile = inputFile.files[0];
        if (!selectedFile) {
            asyncAlert('Please select an image file first.', 'Notification', [
                { caption: 'Close', fn: () => {}, class: 'btn-primary' }
            ]);
            return;
        }

        const fileType = selectedFile.type;

        let image = new Image();
        let imageDataUrl = null;

        if (fileType === 'image/svg+xml') {
            // Read SVG as text
            const svgText = await selectedFile.text();
            const parser = new DOMParser();
            const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
            const svgElement = svgDoc.querySelector('svg');

            if (!svgElement) {
                asyncAlert('Invalid SVG content.', 'Notification', [
                    { caption: 'Close', fn: () => {}, class: 'btn-primary' }
                ]);
                return;
            }

            try {
                // Convert SVG to PNG via helper function
                imageDataUrl = await convertSvgToPng(svgElement);
            } catch (err) {
                console.error('SVG conversion error:', err);
                asyncAlert('Failed to convert SVG to PNG.', 'Notification', [
                    { caption: 'Close', fn: () => {}, class: 'btn-primary' }
                ]);
                return;
            }
        } else if (fileType === 'image/png') {
            imageDataUrl = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.onerror = reject;
                reader.readAsDataURL(selectedFile);
            });
        } else {
            asyncAlert('Unsupported file type. Please select PNG or SVG.', 'Notification', [
                { caption: 'Close', fn: () => {}, class: 'btn-primary' }
            ]);
            return;
        }

        // Load image for dimension validation and icon generation
        image.onload = function () {
            if (image.width < 512 || image.height < 512) {
                asyncAlert('The image must be at least 512x512 pixels.', 'Notification', [
                    { caption: 'Close', fn: () => {}, class: 'btn-primary' }
                ]);
                return;
            }

            const previewImg = document.querySelector('.application-icon-preview');
            if (previewImg) {
                previewImg.src = imageDataUrl;
                previewImg.width = 256;
                previewImg.height = 256;
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
                { size: 256, name: "favicon.png" },
                { size: 512, name: "android-icon-512x512.png" }
            ];

            iconSizes.forEach(icon => {
                canvas.width = icon.size;
                canvas.height = icon.size;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(image, 0, 0, image.width, image.height, 0, 0, icon.size, icon.size);
                const dataUrl = canvas.toDataURL('image/png');
                sendIconPngToServer(applicationId, dataUrl, icon.name);
            });

            generateFaviconICO(applicationId, image);
            resetFileManager();
        };

        image.src = imageDataUrl;
    });

    inputFile.click();
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

  $(document).on('change', '.include-list', function(e1){
    let tr = $(this).closest('tr');
    if($(this)[0].checked)
    {
      tr.attr('data-include-list', 'true');
    }
    else
    {
      tr.attr('data-include-list', 'false');
    }
  });
  $(document).on('change', '.include-detail', function(e1){
    let tr = $(this).closest('tr');
    if($(this)[0].checked)
    {
      tr.attr('data-include-detail', 'true');
    }
    else
    {
      tr.attr('data-include-detail', 'false');
    }
  });

  $(document).on('click', '.button-format-data', function(e1){
    let tr = $(this).closest('tr');
    let dataType = tr.find('.input-field-data-type').val();
    let fieldName = tr.attr('data-field-name');
    let currentFormatStr = $(this).siblings('.input-format-data').val();
    let currentFormat = parseJsonData(currentFormatStr);
    if(currentFormat === null)
    {
      currentFormat = {};
    }
    showDataFormatDialog(fieldName, dataType, currentFormat);
  });
  
  $(document).on('change', '.input-element-type', function(e1){
    let tr = $(this).closest('tr');
    if($(this)[0].checked)
    {
      if(isSupportMultiple($(this).val()))
      {
        tr.find('.input-multiple-data')[0].disabled = false;
      }
      else
      {
        tr.find('.input-multiple-data')[0].disabled = true;
        tr.find('.input-multiple-data')[0].checked = false;
      }
      tr.attr('data-element-type', 'text');
    }
    let elementType = tr.find('.input-element-type:checked').val();
    if(elementType != '')
    {
      tr.attr('data-element-type', elementType);
    }
  });
  
  $(document).on('change', '.input-field-filter', function(e1){
    let tr = $(this).closest('tr');
    if($(this)[0].checked)
    {
      if(isSupportMultiple($(this).val()))
      {
        tr.find('.input-multiple-filter')[0].disabled = false;
      }
      else
      {
        tr.find('.input-multiple-filter')[0].disabled = true;
        tr.find('.input-multiple-filter')[0].checked = false;
      }
    }
    else
    {
      tr.find('.input-multiple-filter')[0].disabled = true;
      tr.find('.input-multiple-filter')[0].checked = false;
    }
  });

  $(document).on('click', '.button-reload-application-menu', function(e1){
    e1.preventDefault();
    loadMenu();
  });
  
  $(document).on('change', '.directory-container input[type="text"]', function(e1){
    let input = $(this);
    checkWriretableDirectory(input);
  });

  $(document).on('change keyup', '#modal-create-application input[name="application_name"]', function(e1){
    changeApplicationName($(this).val());
  });

  $(document).on('change keyup', '#modal-create-application input[name="application_id"]', function(e1){
    changeApplicationId($(this).val());
  });

  $(document).on('click', '#button-save-data-format', function(e1){
    saveDataFormat();
  });
  
  $(document).on('change', '.rd-table-name, .rd-primary-key, .rd-value-column, .rd-reference-object-name, .rd-reference-property-name', function(e){
    validateReference();
  });
  
  $(document).on('change', '.rd-entity-name', function(e){
    validateEntityName();
  });
  
  $(document).on('focus', '.rd-table-name, .rd-primary-key, .rd-value-column, .rd-reference-property-name', function(e){
    let value = $(this).val();
    let td = $(this).closest('td');
    td.find('.column-container a').removeClass('column-selected');
    td.find('.column-container a[data-column="'+value+'"]').addClass('column-selected');
    td.attr('data-focus', 'true');
  });
  $(document).on('blur', '.rd-table-name, .rd-primary-key, .rd-value-column, .rd-reference-property-name', function(e){
    let td = $(this).closest('td');
    setTimeout(function(){
      td.attr('data-focus', 'false');  
    }, 240);
  });
  
  $(document).on('click', '.primary-key-list a, .column-list a', function(e){
    e.preventDefault();
    let column = $(this).attr('data-column');
    let td = $(this).closest('td');
    td.attr('data-focus', 'false');
    let input = td.find('input[type="text"]');
    input.val(column);
    input.closest('.input-with-checker').attr('data-valid', 'true');
  });
  
  $(document).on('click', '.table-list a', function(e){
    e.preventDefault();
    let table = $(this).attr('data-table');
    let td = $(this).closest('td');
    td.attr('data-focus', 'false');
    let input = td.find('input[type="text"]');
    input.closest('.input-with-checker').attr('data-valid', 'true');
    let primaryKey = $(this).attr('data-primary-key');
    input.val(table);
    input.closest('.input-with-checker').attr('data-valid', 'true');
    let inputPk = input.closest('table').find('.rd-primary-key');
    inputPk.val(primaryKey);
    inputPk.closest('.input-with-checker').attr('data-valid', 'true');
    validateReference();
  });

  $(document).on('change', 'select[name="module_menu"]', function(e1){
    e1.preventDefault();
    let moduleMenu = $(this).val();
    updateCurrentApplivationMenu(moduleMenu);
  });

  let val1 = $('meta[name="workspace-id"]').attr('content') || '';
  let val2 = $('meta[name="application-id"]').attr('content') || '';
  window.localStorage.setItem(getLocalStorageKey('workspace-id'), val1);
  window.localStorage.setItem(getLocalStorageKey('application-id'), val2);
  loadAllResource();
  resetCheckActiveWorkspace();
  resetCheckActiveApplication();
  loadReferenceResource();
};

/**
 * Updates the current application menu by sending an AJAX request to the server. 
 * @param {string} moduleMenu Current module menu.
 */
function updateCurrentApplivationMenu(moduleMenu)
{
  let applicationId = $('meta[name="application-id"]').attr('content');
  let moduleMenuId = moduleMenu;
  increaseAjaxPending();
  $.ajax({
    type: 'POST',
    url: 'lib.ajax/application-menu-default.php',
    data: { applicationId: applicationId, moduleMenuId: moduleMenuId },
    success: function (data) {
      decreaseAjaxPending();
    },
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
  });
}

/**
 * Validates the class name to ensure it starts with an uppercase letter and 
 * only contains alphanumeric characters.
 * 
 * @param {string} className - The class name to validate.
 * @returns {boolean} - Returns true if the class name is valid, false otherwise.
 */
function validateClassName(className) {
  // Regular expression to ensure class name starts with an uppercase letter 
  // and only contains alphanumeric characters (letters and digits).
  let regex = /^[A-Z][a-zA-Z0-9]*$/; 
  return regex.test(className);
}

/**
 * Validates the entity name input field by checking if the entered value is 
 * a valid class name.
 * 
 * This function updates the data-valid attribute of the entity name container 
 * based on whether the class name is valid or not.
 */
function validateEntityName() {
  // Get the value of the entity name input field
  let entityName = $('.rd-entity-name').val();

  // Validate the entity name using the validateClassName function
  let valid = validateClassName(entityName);

  // Update the data-valid attribute of the entity name container based on validity
  $('.rd-entity-name-container').attr('data-valid', valid ? 'true' : 'false');
}

/**
 * Validates the reference information by making an AJAX request to check 
 * if the reference values (e.g., table name, primary key, etc.) are valid.
 * 
 * This function updates the data-valid attribute of various containers based 
 * on the validation results from the server response.
 */
function validateReference() {
  $('.column-list').empty();
  $('.primary-key-list').empty();

  // Collect values from various input fields
  let table_name = $('select[name="source_table"]').val();
  let reference_table_name = $('.rd-table-name').val();
  let reference_primary_key = $('.rd-primary-key').val();
  let reference_value_column = $('.rd-value-column').val();
  let reference_object_name = $('.rd-reference-object-name').val();
  let reference_property_name = $('.rd-reference-property-name').val();

  // Get references to the container elements for each input field
  let tableContainer = $('.rd-table-name-container');
  let primaryKeyContainer = $('.rd-primary-key-container');
  let valueColumnContainer = $('.rd-value-column-container');
  let referenceObjectNameContainer = $('.rd-reference-object-name-container');
  let referencePropertyNameContainer = $('.rd-reference-property-name-container');
  
  // Set loading state to true for all containers
  tableContainer.attr('data-loading', 'true');
  primaryKeyContainer.attr('data-loading', 'true');
  valueColumnContainer.attr('data-loading', 'true');
  referenceObjectNameContainer.attr('data-loading', 'true');
  referencePropertyNameContainer.attr('data-loading', 'true');

  // Make an AJAX request to validate the reference information
  $.ajax({
    method: 'POST',
    url: 'lib.ajax/entity-reference-test.php',
    data: {
      table_name: table_name,
      reference_table_name: reference_table_name,
      reference_primary_key: reference_primary_key,
      reference_value_column: reference_value_column,
      reference_object_name: reference_object_name,
      reference_property_name: reference_property_name
    },
    dataType: 'json',
    success: function(data) {
      // Set loading state to false once the request is complete
      tableContainer.attr('data-loading', 'false');
      primaryKeyContainer.attr('data-loading', 'false');
      valueColumnContainer.attr('data-loading', 'false');
      referenceObjectNameContainer.attr('data-loading', 'false');
      referencePropertyNameContainer.attr('data-loading', 'false');
      
      // Update the data-valid attribute for each container based on the server response
      tableContainer.attr('data-valid', data.tableName ? 'true' : 'false');
      primaryKeyContainer.attr('data-valid', data.primaryKey ? 'true' : 'false');
      valueColumnContainer.attr('data-valid', data.valueColumn ? 'true' : 'false');
      referenceObjectNameContainer.attr('data-valid', data.referenceObjectName ? 'true' : 'false');
      referencePropertyNameContainer.attr('data-valid', data.referencePropertyName ? 'true' : 'false');
      
      let ul1 = $('<ul />');
      for(let i in data.primaryKeys)
      {
        let li1 = $(`<li><a href="javascript:;" data-column="${data.primaryKeys[i]}">${data.primaryKeys[i]}</a></li>`);
        ul1.append(li1);
      }
      $('.primary-key-list').append(ul1);
      
      let ul2 = $('<ul />');
      for(let i in data.columns)
      {
        let li2 = $(`<li><a href="javascript:;" data-column="${data.columns[i]}">${data.columns[i]}</a></li>`);
        ul2.append(li2);
      }
      $('.column-list').append(ul2);
      
      if(!data.tableName)
      {
        let ul3 = $('<ul />');
        for(let i in data.tables)
        {
          let li3 = $(`<li><a href="javascript:;" data-table="${data.tables[i].tableName}" data-primary-key="${data.tables[i].primaryKeys[0]}">${data.tables[i].tableName}</a></li>`);
          ul3.append(li3);
        }
        $('.table-list').append(ul3);
      }
      
    },
    error: function(e1, e2)
    {
      console.error(e1);
    }
  });
}

/**
 * Saves the selected data format and updates the corresponding table field.
 */
function saveDataFormat()
{
  $('#data-format').modal('hide');
  let stringFormat = $('#input-control-string-format').val() || '';
  let dateFormat = $('#input-control-date-format').val() || '';
  let decimal = $('#input-control-decimal').val() || '';
  let decimalSeparator = $('#input-control-decimal-separator').val() || '';
  let thousandsSeparator = $('#input-control-thousands-separator').val() || '';

  let fieldName = $('#data-format').attr('data-field-name');

  stringFormat = stringFormat.trim();
  dateFormat = dateFormat.trim();
  decimal = decimal.trim();
  decimalSeparator = decimalSeparator.trim();
  thousandsSeparator = thousandsSeparator.trim();

  let dataFormat = {};
  if(stringFormat != '')
  {
    dataFormat.formatType = 'stringFormat';
    dataFormat.stringFormat = stringFormat;
  }
  else if(dateFormat != '')
  {
    dataFormat.formatType = 'dateFormat';
    dataFormat.dateFormat = dateFormat;
  }
  else if(decimal != '')
  {
    dataFormat.formatType = 'numberFormat';
    dataFormat.numberFormat = {
      decimal: parseInt(decimal),
      decimalSeparator: decimalSeparator,
      thousandsSeparator: thousandsSeparator
    };
  }
  $('.main-table tbody tr[data-field-name="'+fieldName+'"]').find('.input-format-data').val(JSON.stringify(dataFormat));
}

/**
 * Displays the data format selection dialog and initializes the fields.
 *
 * @param {string} fieldName - The name of the field to update.
 * @param {string} dataType - The data type (e.g., 'string', 'date', 'number').
 * @param {object} currentFormat - The current format object.
 */
function showDataFormatDialog(fieldName, dataType, currentFormat) {
  // Show the modal
  $('#data-format').modal('show');
  $('#data-format').attr('data-field-name', fieldName);

  // Mapping format types to tab content IDs
  const formatTabs = {
    'stringFormat': '#string-format-tab',
    'dateFormat': '#date-format-tab',
    'numberFormat': '#number-format-tab'
  };
  let formatType = 'stringFormat';
  // Ensure currentFormat exists and has a formatType
  if (!currentFormat || !currentFormat.formatType) {
    // Use data type instead
    if(dataType == 'timestamp' || dataType == 'datetime' || dataType == 'datetime-local' || dataType == 'date' || dataType == 'time')
    {
      formatType = 'dateFormat';
    }
  }
  else
  {
    formatType = currentFormat.formatType;
  }
  $('#input-control-string-format').val('');
  $('#input-control-date-format').val('');
  $('#input-control-decimal').val('');
  $('#input-control-decimal-separator').val('');
  $('#input-control-thousands-separator').val('');
  
  if(formatType == 'stringFormat')
  {
    $('#input-control-string-format').val(currentFormat.stringFormat || '');
  }
  else if(formatType == 'dateFormat')
  {
    $('#input-control-date-format').val(currentFormat.dateFormat || '');
  }
  else if(formatType == 'numberFormat')
  {
    let numberFormat = currentFormat && currentFormat.numberFormat || {};
    let decimal = (typeof numberFormat.decimal !== 'undefined') ? numberFormat.decimal : '';
    let decimalSeparator = (typeof numberFormat.decimalSeparator !== 'undefined') ? numberFormat.decimalSeparator : '';
    let thousandsSeparator = (typeof numberFormat.thousandsSeparator !== 'undefined') ? numberFormat.thousandsSeparator : '';

    $('#input-control-decimal').val(decimal);
    $('#input-control-decimal-separator').val(decimalSeparator);
    $('#input-control-thousands-separator').val(thousandsSeparator);
  }

  let tabSelector = formatTabs[formatType];

  if (tabSelector) {
    // Deactivate currently active tab
    $('#data-format .nav-link.active').removeClass('active');
    $('#data-format .tab-pane.active').removeClass('active');
    $('#data-format .tab-pane.active').removeClass('show');
    // Activate the selected tab
    $('#data-format .nav-link[data-target="' + tabSelector + '"]').addClass('active');
    $(tabSelector).addClass('active show');
    setTimeout(function(){
    $(tabSelector).find('input:first').focus(); // Focus on the first input in the selected tab
    }, 380);
  }
}

/**
 * Initiates the download of a Markdown file for the entity-relationship diagram.
 * 
 * - Collects selected entities from checkboxes.
 * - Appends a random timestamp to avoid caching issues.
 * - Constructs a URL and triggers the download.
 */
function downloadMarkdown()
{
  let params = [];
  params = addDiagramOption(params);

  $('.entity-container-relationship .entity-checkbox').each(function (e) {
    if ($(this)[0].checked) {
      params.push('entity[]=' + $(this).val());
    }
  });
  params.push('rnd=' + (new Date()).getTime());
  let urlMap = 'lib.ajax/entity-relationship-diagram-markdown.php?' + params.join('&');
  window.location = urlMap;
}

let checkTimeout = setTimeout('', 10000);

/**
 * Updates the application namespace and application ID based on the provided application name.
 * 
 * @param {string} applicationName - The name of the application entered by the user.
 */
function changeApplicationName(applicationName) {
    // Remove all non-alphabetic characters to create a namespace
    let namespace = applicationName.replace(/[^a-zA-Z]/g, '');

    // Generate a valid application ID:
    // - Remove non-alphanumeric characters except '-'
    // - Replace multiple '-' with a single '-'
    // - Trim leading and trailing '-'
    // - Convert to lowercase
    let applicationId = applicationName.replace(/[^a-zA-Z0-9-]/g, '-')
                                       .replace(/-+/g, '-')
                                       .replace(/^-+|-+$/g, '') // NOSONAR
                                       .toLowerCase();

    // Set the values in the modal form
    $('#modal-create-application input[name="application_namespace"]').val(namespace);
    changeApplicationId(applicationId);
}

/**
 * Updates the application ID field and triggers an update for the application directory.
 * 
 * @param {string} applicationId - The formatted application ID.
 */
function changeApplicationId(applicationId) {
    $('#modal-create-application input[name="application_id"]').val(applicationId);
    changeApplicationDirectory(applicationId);
}

/**
 * Updates the application directory path based on the application ID.
 * 
 * @param {string} applicationId - The application ID used to rename the directory.
 */
function changeApplicationDirectory(applicationId) {
    // Get the original directory path
    let originalDirectory = $('#modal-create-application input[name="application_directory"]').val();
    let originalUrl = $('#modal-create-application input[name="application_url"]').val();

    // Replace the last part of the path (basename) with the application ID
    let newDirectory = replaceBasename(originalDirectory, applicationId);
    let newUrl = replaceBasename(originalUrl, applicationId);

    // Update the input field with the new directory path
    $('#modal-create-application input[name="application_directory"]').val(newDirectory);
    $('#modal-create-application input[name="application_url"]').val(newUrl);

    // Delay checking the directory's writability status to prevent multiple rapid checks
    clearTimeout(checkTimeout);
    checkTimeout = setTimeout(function() {
        checkWriretableDirectory($('#modal-create-application input[name="application_directory"]'));
    }, 500);
}

/**
 * Replaces the basename of a directory path with a new name, keeping the original separator format.
 * 
 * @param {string} dirPath - The full directory path.
 * @param {string} newBasename - The new basename to replace the existing one.
 * @returns {string} The updated directory path.
 */
function replaceBasename(dirPath, newBasename) {
    // Normalize the path separator to '/'
    let normalizedPath = dirPath.replace(/\\/g, '/');

    // Split the path into parts
    let pathParts = normalizedPath.split('/');

    // Replace the last part (basename) with the new name
    pathParts[pathParts.length - 1] = newBasename;

    // Reconstruct the path using the original separator
    return dirPath.includes('\\') ? pathParts.join('\\') : pathParts.join('/');
}

/**
 * Checks if the specified directory is writable and updates the UI accordingly.
 *
 * This function sends an AJAX request to the server to check if the given directory 
 * is writable. It updates the associated UI container with a loading state during 
 * the request and changes the state to indicate whether the directory is writable 
 * or not once the response is received. The result is reflected in the `data-valid` 
 * attribute of the closest `.directory-container` element. It also handles the case 
 * where the directory is being checked for being a file or directory.
 *
 * @param {HTMLElement} input - The input element that triggers the directory check.
 *                               This input element contains the directory path.
 * 
 * @returns {void}
 */
function checkWriretableDirectory(input)
{
    let container = $(input).closest('.directory-container');
    let isFile = container.attr('data-isfile') || '';
    let failedIfExists = container.attr('data-failed-if-exists') || '';
    let directory = input.val() || '';
    directory = directory.trim();
    
    if(directory != '' && directory != '/' && directory != '\\')
    {
      container.attr('data-loading', 'true');
      $.ajax({
        method: 'POST',
        url: 'lib.ajax/directory-writeable-test.php',
        data: {
          directory: directory, 
          isfile: isFile,
          failedIfExists: failedIfExists
        },
        dataType: 'json',
        success: function(data) {
          container.attr('data-loading', 'false');
          if(data.writeable)
          {
            container.attr('data-valid', 'true');
          }
          else
          {
            container.attr('data-valid', 'false');
          }
        },
        error: function(err) {
          container.attr('data-loading', 'false');
        }
      });
    }
}

/**
 * Resets the writable directory check by removing the `data-valid` and `data-loading` attributes.
 *
 * This function clears the results of the directory writable check by removing the 
 * `data-valid` and `data-loading` attributes from the closest `.directory-container` 
 * element, effectively resetting the UI state.
 *
 * @param {HTMLElement} input - The input element that triggers the reset of the directory check.
 *                               This input element is used to find the closest `.directory-container`.
 * 
 * @returns {void}
 */
function resetCheckWriretableDirectory(input)
{
  let container = $(input).closest('.directory-container');
  container.removeAttr('data-valid');
  container.removeAttr('data-loading');
}

/**
 * Updates the application form with dynamic data for workspace, installation method, and magic app versions.
 * 
 * This function populates the dropdown options in a form using the provided data object. It adds options to 
 * the "application_workspace_id" and "installation_method" fields, setting the selected and disabled attributes 
 * based on the data. It also populates the "magic_app_version" field with a list of versions and highlights 
 * the latest version.
 * 
 * @param {Object} data - The data object containing the information to update the form with.
 * @param {Array} data.application_workspace - A list of workspaces, each containing a label, value, and selected flag.
 * @param {Array} data.installation_method - A list of installation methods, each containing a label, value, 
 *                                          selected flag, and disabled flag.
 * @param {Array} data.magic_app_versions - A list of magic app versions, each containing a value, key, 
 *                                           and a flag indicating the latest version.
 */
function updateNewApplicationForm(data) {
  // Populate the application workspace dropdown
  for (let workspace of data.application_workspace) {
    let opt = $('<option />');
    opt.text(workspace.label);
    opt.attr('value', workspace.value);
    if (workspace.selected) {
      opt.attr('selected', 'selected');
    }
    $('[name="application_workspace_id"]').append(opt);
  }

  // Populate the installation method dropdown
  for (let method of data.installation_method) {
    let opt = $('<option />');
    opt.text(method.label);
    opt.attr('value', method.value);
    if (method.selected) {
      opt.attr('selected', 'selected');
    }
    if (method.disabled) {
      opt.attr('disabled', 'disabled');
    }
    $('[name="installation_method"]').append(opt);
  }

  // Populate the magic app version dropdown
  for (let i in data.magic_app_versions) {
    let latest = data.magic_app_versions[i]['latest'];
    $('[name="magic_app_version"]')[0].appendChild(
      new Option(data.magic_app_versions[i]['value'], data.magic_app_versions[i]['key'], latest, latest)
    );
  }
}

/**
 * Shows the application menu dialog for the specified application ID.
 * @param {string} applicationId - The unique identifier for the application.
 */
function showApplicationMenuDialog(applicationId) {
  let modal = $('#modal-application-menu');
  let updateBtn = $('#modal-application-menu .button-save-menu');
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
}

/**
 * Loads reference resource content via an AJAX GET request.
 * 
 * - Increases the AJAX pending counter before making the request.
 * - Fetches the reference resource from 'lib.ajax/reference.min.html'.
 * - Stores the retrieved data in `referenceResource` on success.
 * - Decreases the AJAX pending counter after completion.
 */
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

/**
 * Filters workspace cards based on the search input value.
 *
 * @param {HTMLElement} elem - The input element containing the search query.
 */
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

/**
 * Filters application cards based on the search input value.
 *
 * @param {HTMLElement} elem - The input element containing the search query.
 */
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
    let val1 = window.localStorage.getItem(getLocalStorageKey('workspace-id')) || '';
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
    let val1 = window.localStorage.getItem(getLocalStorageKey('application-id')) || '';
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
      window.localStorage.setItem(getLocalStorageKey('workspace-id'), val1);
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
      let applicationId = $('.application-item[data-selected="true"]').attr('data-application-id') || '';
      let applicationName = $('.application-item[data-selected="true"]').attr('data-application-name') || '';
      window.localStorage.setItem(getLocalStorageKey('application-id'), applicationId);
      $('meta[name="application-id"]').attr('content', applicationId);
      $('meta[name="application-name"]').attr('content', applicationName);
      let builderName = $('meta[name="builder-name"]').attr('content');
      document.title = applicationName != '' ? applicationName + " | " + builderName : builderName;
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

      let $select = $('[name="current_module_location"]');
      $select.empty();

      $.each(data, function (i, item) {
        $('<option>', {
          text: item.name + ' - ' + item.path,
          value: item.path,
          selected: item.active
        }).appendTo($select);
      });
    },
    error: function () {
      decreaseAjaxPending();
      console.error('Failed to load language list.');
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
  updateValidatorFile();
  updateModuleFile();
  resetFileManager();
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
  updateValidatorFile();
  updateModuleFile();
  resetFileManager();
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
  updateValidatorFile();
  updateModuleFile();
  resetFileManager();
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
          button.fn($('.prompt-input').val());  // Execute the callback for this button
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
function asyncAlert(message, title, buttons) {
  const result = showModal // NOSONAR
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
function getUserInput(message, title, buttons, initialValue) {
  const result = asyncPrompt // NOSONAR
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
      title: menuItem.querySelector('a.app-menu-text').textContent,
      icon: menuItem.querySelector('a.app-menu-text').dataset.icon,
      submenu: []
    };
    const submenuItems = menuItem.querySelectorAll('.sortable-submenu-item');
    submenuItems.forEach(submenuItem => {
      menuData.submenu.push({
        title: submenuItem.querySelector('a.app-menu-text').textContent,
        code: submenuItem.querySelector('a.app-menu-text').dataset.code,
        href: submenuItem.querySelector('a.app-menu-text').getAttribute('href'),
        icon: submenuItem.querySelector('a.app-menu-text').dataset.icon,
        specialAccess: submenuItem.querySelector('a.app-menu-text').dataset.specialAccess,
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

let entityTranslationData = [];
let moduleTranslationData = [];
let appTranslationData = [];

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
        entityTranslationData = data;
        $('.entity-translation-status').text('');
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
      },
      error: function (xhr, status, error) {
        decreaseAjaxPending();
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
      moduleTranslationData = data;
      $('.module-translation-status').text('');
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
    error: function (data) {
      decreaseAjaxPending();
      $('.module-translation-status').text('Error: ' + data.responseText);
    }
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
      decreaseAjaxPending();
  })
  .catch(error => {
    decreaseAjaxPending();
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
      decreaseAjaxPending();
  })
  .catch(error => {
    decreaseAjaxPending();
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
        removeHilightLineError(cmEditorFile);
        addHilightLineError(cmEditorFile, data.error_line - 1)
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
 * Saves the current validator to the server.
 *
 * This function checks if a validator is currently open. If so, it disables the
 * save button and retrieves the content from the validator editor. It then sends an AJAX
 * POST request to update the validator's content on the server. Upon successful
 * completion, it re-enables the save button, updates the validator file, highlights
 * any error lines, and updates the UI. If no validator is open, it shows an alert to the user.
 *
 * @returns {void} This function does not return a value.
 */
function saveValidator() {
  if (currentValidator != "") {
    $("#button_save_validator_file").attr("disabled", "disabled");
    let fileContent = cmEditorValidator.getDoc().getValue();
    increaseAjaxPending();
    $.ajax({
      type: "POST",
      url: "lib.ajax/validator-file.php",
      dataType: "json",
      data: { userAction: 'set', content: fileContent, validator: currentValidator },
      success: function (data) {
        decreaseAjaxPending();
        $("#button_save_validator_file").removeAttr("disabled");
        updateValidatorFile(function(){
          setValidatorFile(fileContent);
          updateSelectedValidator();
          removeHilightLineError(cmEditorValidator);
          addHilightLineError(cmEditorValidator, data.error_line - 1);
        });
        
        removeHilightLineError(cmEditorValidator);
        addHilightLineError(cmEditorValidator, data.error_line - 1);
        
        if (!data.success) {
          showAlertUI(data.title, data.message);
          setTimeout(function () { closeAlertUI() }, 5000);
        }
      },
      error: function(err)
      {
        $("#button_save_validator_file").removeAttr("disabled");
        decreaseAjaxPending();
      }
    });
  } else {
    showAlertUI("Alert", "No file open");
  }
}

/**
 * Saves the current validator as a new validator file on the server.
 *
 * This function checks if a validator is currently open. If so, it retrieves the content from the validator editor,
 * prompts the user for a new validator name, and sends an AJAX POST request to save the validator under the new name.
 * Upon successful completion, it updates the validator file list, resets the file manager, highlights any error lines,
 * and displays an alert if there are errors. If no validator is open, it shows an alert to the user.
 *
 * @returns {void} This function does not return a value.
 */
function saveValidatorAs() {
  if (currentValidator != "") {
    let fileContent = cmEditorValidator.getDoc().getValue();

    getUserInput('New Validator Name', 'Save Validator As', [
      {
        'caption': 'Yes',  // Caption for the button
        'fn': () => {
          let newValidator = $('.prompt-input').val();
          increaseAjaxPending();
          $.ajax({
            type: "POST",
            url: "lib.ajax/validator-file.php",
            dataType: "json",
            data: { userAction: 'set', content: fileContent, validator: currentValidator, newValidator: newValidator },
            success: function (data) {
              currentValidator = newValidator;
              decreaseAjaxPending();
              $("#button_save_validator_file").removeAttr("disabled");
              updateValidatorFile(function(){
                setValidatorFile(data.new_content);
                updateSelectedValidator();
                removeHilightLineError(cmEditorValidator);
                addHilightLineError(cmEditorValidator, data.error_line - 1);
              });
              resetFileManager();
              
              removeHilightLineError(cmEditorValidator);
              addHilightLineError(cmEditorValidator, data.error_line - 1)
              if (!data.success) {
                showAlertUI(data.title, data.message);
                setTimeout(function () { closeAlertUI() }, 5000);
              }
            },
            error: function(err)
            {
              decreaseAjaxPending();
            }
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
 * Updates the selected entity in the list by highlighting the relevant item.
 *
 * This function removes the 'selected-file' class from all entity list items,
 * then adds the 'selected-file' class to the entity item that matches the current entity.
 * It ensures that only the currently selected entity is visually highlighted in the UI.
 */
function updateSelectedEntity()
{
  $('.entity-list .entity-li').removeClass('selected-file');
  $('.entity-list [data-entity-name="'+currentEntity.split('\\').join('\\\\')+'"]').closest('.entity-li').addClass('selected-file');
}

/**
 * Updates the selected validator in the list by highlighting the relevant item.
 *
 * This function removes the 'selected-file' class from all validator list items,
 * then adds the 'selected-file' class to the validator item that matches the current validator.
 * It ensures that only the currently selected validator is visually highlighted in the UI.
 */
function updateSelectedValidator()
{
  $('.validator-list .validator-li').removeClass('selected-file');
  $('.validator-list [data-validator-name="'+currentValidator.split('\\').join('\\\\')+'"]').closest('.validator-li').addClass('selected-file');
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
              resetFileManager();
              updateEntityQuery(true);
              updateEntityRelationshipDiagram();
              removeHilightLineError(cmEditorFile);
              addHilightLineError(cmEditorFile, data.error_line - 1)
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
function addHilightLineError(cmEditor, lineNumber) {
  if (lineNumber != -1) {
    cmEditor.addLineClass(lineNumber, 'background', 'highlight-line');
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
function removeHilightLineError(cmEditor) {
  if (lastErrorLine != -1) {
    cmEditor.removeLineClass(lastErrorLine, 'background', 'highlight-line');
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
 * @param {number} lineNumber - Optional line number to higlight the entity code
 * @returns {void} This function does not return a value.
 */
function getEntityFile(entity, clbk, lineNumber) {
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
        if(lineNumber && lineNumber > -1)
        {
          focusOnLine(cmEditorFile, lineNumber);
        }
      }, 1);
      $("#button_save_entity_file").removeAttr("disabled");
      $("#button_delete_entity_file").removeAttr("disabled");
      currentEntity = entity[0];
      if (clbk) {
        clbk();
      }
    },
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
  });
}

/**
 * Fetches the content of a validator file and updates the validator editor.
 *
 * This function sends an AJAX POST request to retrieve the content of the specified
 * validator file and sets it in the validator editor. It also enables the save and
 * delete buttons for the validator, updates the currentValidator variable, and calls
 * an optional callback function after loading.
 *
 * @param {string} validator - The validator to load.
 * @param {function} [clbk] - Optional callback function to call after loading.
 * @param {number} lineNumber - Optional line number to higlight the entity code
 * @returns {void} This function does not return a value.
 */
function getValidatorFile(validator, clbk, lineNumber) {
  increaseAjaxPending();
  $.ajax({
    type: "POST",
    url: "lib.ajax/validator-file.php",
    data: { validator: validator },
    dataType: "text",
    success: function (data) {
      decreaseAjaxPending();
      cmEditorValidator.getDoc().setValue(data);
      setTimeout(function () {
        cmEditorValidator.refresh();
        if(lineNumber && lineNumber > -1)
        {
          focusOnLine(cmEditorValidator, lineNumber);
        }
      }, 1);
      $("#button_save_validator_file").removeAttr("disabled");
      $("#button_save_validator_file_as").removeAttr("disabled");
      $("#button_delete_validator_file").removeAttr("disabled");
      $("#button_test_validator").removeAttr("disabled");
      $('#button_update_validator_file').removeAttr('disabled');

      currentValidator = validator;
      if (clbk) {
        clbk();
      }
    },
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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

      // Mark tab
      $('button#entity-file-tab').removeClass('text-danger');
      let errorCount = $(".entity-container-file .entity-list").find('a[data-error="true"]').length;
      if(errorCount)
      {
        $('button#entity-file-tab').addClass('text-danger');
      }

      clearEntityFile();
      if(typeof clbk == 'function')
      {
        clbk();
      }
      clearTtransEd3();
      clearTtransEd4();
    },
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
  });
}

/**
 * Updates the validator file list in the UI.
 *
 * This function retrieves the list of validator files and updates the corresponding
 * sections in the UI. It also clears the entity file and translation editors.
 * If a callback function is provided, it will be called after the update.
 *
 * @param {function} [clbk] - Optional callback function to call after updating the validator file list.
 * @returns {void} This function does not return a value.
 */
function updateValidatorFile(clbk) {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/validator-list.php",
    dataType: "html",
    success: function (data) {
      decreaseAjaxPending();
      $(".validator-container-file .validator-list").empty().append(data);
      $(".container-translate-validator .validator-list").empty().append(data);

      // Mark tab
      $('button#validator-file-tab').removeClass('text-danger');
      let errorCount = $(".validator-container-file .validator-list").find('a[data-error="true"]').length;
      if(errorCount)
      {
        $('button#validator-file-tab').addClass('text-danger');
      }

      clearValidatorFile();
      if(typeof clbk == 'function')
      {
        clbk();
      }
      clearTtransEd5();
      clearTtransEd6();
    },
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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
    error: function (xhr, status, error) {
      decreaseAjaxPending();
    }
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

  let insertValidatorClass = masterEntityName + "InsertValidator";
  let updateValidatorClass = masterEntityName + "UpdateValidator";

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
  $('[name="module_icon"]').val('fa fa-file');

  $('[name="insert_validator_class"]').val(insertValidatorClass);
  $('[name="update_validator_class"]').val(updateValidatorClass);
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
function generateScript(selector) // NOSONAR
{
  let fields = [];
  $(selector)
    .find("tr")
    .each(function (e) {
      let fieldName = $(this).attr("data-field-name");
      let fieldType = $(this).attr('data-field-type');
      let fieldLabel = $(this).find("input.input-field-name").val();
      let includeInsert = $(this).find("input.include-insert")[0].checked;
      let includeEdit = $(this).find("input.include-edit")[0].checked;
      let includeDetail = $(this).find("input.include-detail")[0].checked;
      let includeList = $(this).find("input.include-list")[0].checked;
      let includeExport = $(this).find("input.include-export")[0].checked;
      let isKey = $(this).find("input.include-key")[0].checked;
      let isInputRequired = $(this).find("input.include-required")[0].checked;
      let elementType = $(this).find("input.input-element-type:checked").val();
      let filterElementType =
        $(this).find("input.input-field-filter:checked").length > 0
          ? $(this).find("input.input-field-filter:checked").val()
          : null;
      let dataType = $(this).find("select.input-field-data-type").val();
      let dataFormatStr = $(this).find("input.input-format-data").val();
      let dataFormat = {};
      if(dataFormatStr.indexOf('{') != -1 && dataFormatStr.indexOf('}') != -1)
      {
        dataFormat = JSON.parse(dataFormatStr);
      }
      let inputFilter = $(this).find("select.input-data-filter").val();

      let referenceData = parseJsonData(
        $(this).find("input.reference-data").val()
      );
      let referenceFilter = parseJsonData(
        $(this).find("input.reference-filter").val()
      );
      
      let multipleData = $(this).find("input.input-multiple-data")[0].checked;
      let multipleFilter = $(this).find("input.input-multiple-filter")[0].checked;

      let field = {
        fieldName: fieldName,
        fieldLabel: fieldLabel,
        fieldType: fieldType,
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
        dataFormat: dataFormat,
        inputFilter: inputFilter,
        referenceData: referenceData,
        referenceFilter: referenceFilter,
        multipleData: multipleData,
        multipleFilter: multipleFilter
      };
      fields.push(field);
    });

  let subquery = $('#subquery')[0].checked && true; //NOSONAR
  let requireApproval = $('#with_approval')[0].checked && true; //NOSONAR
  let withTrash = $('#with_trash')[0].checked && true; //NOSONAR
  let manualSortOrder = $('#manual_sort_order')[0].checked && true; //NOSONAR
  let exportToExcel = $('#export_to_excel')[0].checked && true; //NOSONAR
  let exportToCsv = $('#export_to_csv')[0].checked && true; //NOSONAR
  let exportUseTemporary = $('#export_use_temporary')[0].checked && true; //NOSONAR
  let activateDeactivate = $('#activate_deactivate')[0].checked && true; //NOSONAR
  let userActivityLogger = $('#user_activity_logger')[0].checked && true; //NOSONAR
  let withApprovalNote = $('#with_approval_note')[0].checked && true; //NOSONAR
  let approvalByAnotherUser = $('[name="approval_by_other_user"]')[0].checked && true; //NOSONAR
  let ajaxSupport = $('#ajax_support')[0].checked && true; //NOSONAR
  let backendOnly = $('#backend_only')[0].checked && true; //NOSONAR
  let withValidation = $('#with_validation')[0].checked && true; //NOSONAR

  let approvalPosition = $('[name="approval_position"]:checked').val(); //NOSONAR
  let approvalBulk = $('[name="approval_bulk"]:checked').val(); //NOSONAR
  let approvalType = $('[name="approval_type"]:checked').val(); //NOSONAR

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
    activateDeactivate: activateDeactivate,
    sortOrder: manualSortOrder,
    exportToExcel: exportToExcel,
    exportToCsv: exportToCsv,
    exportUseTemporary: exportUseTemporary,
    userActivityLogger: userActivityLogger,
    approvalRequired: requireApproval,
    approvalNote: withApprovalNote,
    trashRequired: withTrash,
    approvalType: approvalType,
    approvalPosition: approvalPosition,
    approvalByAnotherUser: approvalByAnotherUser,
    approvalBulk: approvalBulk,
    withValidation: withValidation,
    backendOnly: backendOnly,
    subquery: subquery,
    ajaxSupport: ajaxSupport
  };

  let specification = getSpecificationModule();
  let sortable = getSortableModule();

  let validationDefinition = buildValidationDefinition(fields);

  let validator = {
    insertValidatorClass: $('[name="insert_validator_class"]').val(),
    updateValidatorClass: $('[name="update_validator_class"]').val(),
    updateValidationDefintion: $('[name="update_validation_definition"]').prop("checked"),
    validationDefinition: validationDefinition
  };

  let dataToPost = {
    entity: entity,
    fields: fields,
    validator: validator,
    specification: specification,
    sortable: sortable,
    features: features,
    moduleCode: $('[name="module_code"]').val(),
    moduleName: $('[name="module_name"]').val(),
    moduleFile: $('[name="module_file"]').val(),
    moduleIcon: $('[name="module_icon"]').val(),
    moduleAsMenu: $('[name="module_as_menu"]').val(),
    moduleMenu: $('[name="module_menu"]').val(),
    target: $('#current_module_location').val(),
    updateEntity: $('[name="update_entity"]')[0].checked,
  };
  generateAllCode(dataToPost);
}

let validation = {};

/**
 * Builds a serial array of validation definitions based on the given field definitions.
 *
 * This function maps each field in the input array to its corresponding validation rules
 * (from a global `validation` object), and constructs a flattened array of validation
 * definitions, each with a `fieldName` property attached.
 *
 * @param {Array<Object>} fields - The array of field definition objects, each containing a `fieldName`.
 * @returns {Array<Object>} An array of validation objects with the `fieldName` included.
 */
function buildValidationDefinition(fields) {
  if (typeof validation !== 'undefined' && validation) {
    let result = [];
    let idx = 0;
    for (const index in fields) {
      if (fields.hasOwnProperty(index)) {
        const fieldName = fields[index].fieldName;
        const fieldType = fields[index].fieldType;

        if (validation.hasOwnProperty(fieldName)) {
          const validations = validation[fieldName];

          result[idx] = {};
          result[idx].fieldName = fieldName;
          result[idx].fieldType = fieldType;
          result[idx].validation = [];

          validations.forEach(validationRule => {
            result[idx].validation.push(validationRule);
          });
          idx++;
        }
      }
    }

    return result;
  }
}

/**
 * Extracts a grouped validation definition object from a serial array of validation entries.
 *
 * This function takes an array where each item contains a `fieldName` and its `validation`,
 * and transforms it into an object where each key is the `fieldName` and the value is
 * the corresponding validation definition.
 *
 * Example input:
 * [
 *   { fieldName: "Name", validation: [{ type: "Required" }] },
 *   { fieldName: "Email", validation: [{ type: "Email" }] }
 * ]
 *
 * Output:
 * {
 *   Name: [{ type: "Required" }],
 *   Email: [{ type: "Email" }]
 * }
 *
 * @param {Array<Object>} validationDefinition - Array of validation definitions with `fieldName` and `validation`.
 * @returns {Object} A plain object mapping each field name to its validation definition array.
 */
function extractValidationDefinition(validationDefinition)
{
  let extractedValidation = {};
  if (typeof validationDefinition !== 'undefined' && validationDefinition) {
    for(let i in validationDefinition)
    {
      if(validationDefinition[i] !== 'undefined' && validationDefinition[i])
      {
        let fieldName = validationDefinition[i].fieldName;
        let definition = validationDefinition[i].validation;
        extractedValidation[fieldName] = definition;
      }
    }
  }
  return extractedValidation;
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
 * Safely parses a JSON string into a JavaScript object.
 *
 * This function attempts to parse the provided JSON string. If the parsing
 * is successful, it returns the resulting object. If an error occurs during
 * parsing (e.g., invalid JSON), it catches the error and returns `null`.
 *
 * @param {string} text - The JSON string to be parsed.
 * @returns {Object|null} - The parsed JavaScript object if successful, or `null` if parsing fails.
 */
function safeJsonParse(text) {
  try 
  {
    return JSON.parse(text);
  } 
  catch (e) // NOSONAR
  {
    return null;
  }
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
  if (typeof text !== "string") 
  {
    return null;
  }
  const json = safeJsonParse(text);
  return typeof json === "object" && json !== null ? json : null;
}

/**
 * Generates code by sending data to the server and updating UI components.
 *
 * This function sends the provided data to a server endpoint for code generation.
 * Upon successful code generation, it updates the entity file, entity query,
 * and entity relationship diagram. Additionally, it displays a success message 
 * through a toast notification, triggers further actions upon module creation,
 * and provides feedback on the process. If an error occurs, it handles the 
 * failure gracefully.
 *
 * @param {Object} dataToPost - The data to send to the server for code generation.
 *                              This object contains the necessary information 
 *                              to generate the code, such as entity details.
 * 
 * @returns {void}
 */
function generateAllCode(dataToPost) {
  increaseAjaxPending();
  fetch('lib.ajax/script-generator.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(dataToPost),
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast(data.title, data.message);
      }
      decreaseAjaxPending();
      updateEntityFile();
      updateValidatorFile();
      updateEntityQuery(true);
      updateEntityRelationshipDiagram();
      if (data.success) {
        onModuleCreated();
        setTimeout(function () { closeAlertUI() }, 2000);
      }
    })
    .catch((error) => {
      decreaseAjaxPending();
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
 * on the options if available, and groups tables using <optgroup> based on their type.
 * Custom tables are shown before system tables.
 */
function loadTable() {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/database-table-list.php",
    dataType: "json",
    success: function (data) {
      decreaseAjaxPending();

      const $select = $('select[name="source_table"]');
      $select.empty();

      // Placeholder option
      $('<option>', {
        text: '- Select Table -',
        value: ''
      }).appendTo($select);

      // Prepare grouping by table_group
      const grouped = {
        custom: [],
        system: []
      };

      $.each(data, function (key, table) {
        const group = table.table_group === 'system' ? 'system' : 'custom';

        const $option = $('<option>', {
          text: table.table_name,
          value: table.table_name
        });

        if (Array.isArray(table.primary_key) && table.primary_key.length > 0) {
          $option.attr("data-primary-key", table.primary_key[0]);
        }

        grouped[group].push($option);
      });

      // Label mapping
      const groupLabels = {
        custom: 'Custom Tables',
        system: 'System Tables'
      };

      // Render <optgroup> in desired order: custom first
      ['custom', 'system'].forEach(groupKey => {
        if (grouped[groupKey].length > 0) {
          const $optgroup = $('<optgroup>', {
            label: groupLabels[groupKey]
          });

          grouped[groupKey].forEach(option => {
            $optgroup.append(option);
          });

          $select.append($optgroup);
        }
      });

      // Restore selected value if present
      const selectedVal = $select.attr("data-value");
      if (selectedVal) {
        $select.val(selectedVal);
      }
    },
    error: function () {
      decreaseAjaxPending();
    }
  });
}

/**
 * Load the application menu and populate the module menu select element.
 * This function sends an AJAX GET request to fetch the application menu data in JSON format.
 * On success, it populates the select element with the received menu items.
 * It also handles the increase and decrease of AJAX pending requests.
 */
function loadMenu() {
  increaseAjaxPending();
  $.ajax({
    type: "GET",
    url: "lib.ajax/application-menu-json.php",
    dataType: "json",
    success: function(data) {
      decreaseAjaxPending();

      let $select = $('select[name="module_menu"]');
      $select.empty();
      if(data.menu && data.menu.length > 0)
      {
        $.each(data.menu, function(index, item) {
          $('<option>', {
            text: item.title,
            value: item.title,
            selected: item.active === true
          }).appendTo($select);
        });
      }
    },
    error: function() {
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
      currentTableStructure = answer;
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
        let tr = $(domHtml);
        tr.attr('data-include-detail', 'true');
        tr.attr('data-include-list', 'true');
        tr.attr('data-element-type', 'text');
        tr.attr('data-field-type', data[i].data_type);
        tr.attr('data-maximum-length', data[i].maximum_length);
        tr.attr('data-has-validation', 'false');
        $(selector).append(tr);
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
 * Checks if a value is true (either boolean true or string 'true').
 *
 * @param {any} value - The value to check.
 * @returns {boolean} - Returns true if value is boolean true or string 'true', otherwise false.
 */
function isTrue(value)
{
  return value === true || value == 'true';
}

/**
 * Checks if a given value supports multiple selection.
 *
 * @param {string} value - The value to check.
 * @returns {boolean} - Returns true if the value is 'text' or 'select', otherwise false.
 */
function isSupportMultiple(value)
{
  return value == 'text' || value == 'select';
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
          let disabledInsert = tr.find('.include-insert')[0].disabled;
          let disabledEdit = tr.find('.include-edit')[0].disabled;
          tr.find('.include-insert')[0].checked = isTrue(data.fields[i].includeInsert) && !disabledInsert;
          tr.find('.include-edit')[0].checked = isTrue(data.fields[i].includeEdit) && !disabledEdit;
          tr.find('.include-detail')[0].checked = isTrue(data.fields[i].includeDetail);
          tr.find('.include-list')[0].checked = isTrue(data.fields[i].includeList);
          tr.find('.include-export')[0].checked = isTrue(data.fields[i].includeExport);
          tr.find('.include-key')[0].checked = isTrue(data.fields[i].isKey);
          tr.find('.include-required')[0].checked = isTrue(data.fields[i].isInputRequired);
          tr.find('.input-element-type[value="' + data.fields[i].elementType + '"]')[0].checked = true;
          tr.find('.input-element-type[value="' + data.fields[i].elementType + '"]')[0].setAttribute('checked', 'checked');

          if(isTrue(data.fields[i].includeDetail))
          {
            tr.attr('data-include-detail', 'true');
          }
          else
          {
            tr.attr('data-include-detail', 'false');
          }
          if(isTrue(data.fields[i].includeList))
          {
            tr.attr('data-include-list', 'true');
          }
          else
          {
            tr.attr('data-include-list', 'false');
          }

          if (data.fields[i].elementType == 'select') {
            tr.find('.reference-data').val(JSON.stringify(data.fields[i].referenceData));
            tr.find('.reference_button_data').css('display', 'inline');
          }
          if (data.fields[i].elementType == 'text') {
            let dataFormat = data.fields[i].dataFormat || {};
            tr.find('.input-format-data').val(JSON.stringify(dataFormat));
          }

          tr.attr('data-element-type', data.fields[i].elementType);
          tr.attr('data-type', data.fields[i].dataType);

          if (data.fields[i].filterElementType == 'select') {
            tr.find('.reference-filter').val(JSON.stringify(data.fields[i].referenceFilter));
            tr.find('.reference_button_filter').css('display', 'inline');
            tr.find('.input-field-filter[value="select"]')[0].checked = true;
          }
          if (data.fields[i].filterElementType == 'text') {
            tr.find('.input-field-filter[value="text"]')[0].checked = true;
          }
          
          if(isSupportMultiple(data.fields[i].elementType))
          {
            tr.find('.input-multiple-data')[0].disabled = false;
            if(isTrue(data.fields[i].multipleData))
            {
              tr.find('.input-multiple-data')[0].checked = 1;
            }
          }
          else
          {
            tr.find('.input-multiple-data')[0].disabled = true;
          }
          
          if(isSupportMultiple(data.fields[i].filterElementType))
          {
            tr.find('.input-multiple-filter')[0].disabled = false;
            if(isTrue(data.fields[i].multipleFilter))
            {
              tr.find('.input-multiple-filter')[0].checked = 1;
            }
          }
          else
          {
            tr.find('.input-multiple-filter')[0].disabled = true;
          }
          
          tr.find('.input-field-data-type').val(data.fields[i].dataType);
          tr.find('.input-data-filter').val(data.fields[i].inputFilter);
        }
      }
    }

    // validator
    if(data.validator)
    {
      let extractedValidation = extractValidationDefinition(data.validator.validationDefinition);
      validation = extractedValidation;
      validatorBuilder.setValidation(validation);
      validatorBuilder.markValidation();
    }

    // restore features
    if(data.features)
    {
      $('#activate_deactivate')[0].checked = isTrue(data.features.activateDeactivate);
      $('#manual_sort_order')[0].checked = isTrue(data.features.sortOrder);
      $('#export_to_excel')[0].checked = isTrue(data.features.exportToExcel);
      $('#export_to_csv')[0].checked = isTrue(data.features.exportToCsv);
      $('#export_use_temporary')[0].checked = isTrue(data.features.exportUseTemporary);
      if(isTrue(data.features.exportToCsv))
      {
        $('#export_to_excel')[0].disabled = false;
        $('#export_use_temporary')[0].disabled = false;
      }
      else
      {
        $('#export_to_excel')[0].disabled = false;
        $('#export_use_temporary')[0].disabled = true;
      }
      $('#user_activity_logger')[0].checked = isTrue(data.features.userActivityLogger);
      $('#with_approval')[0].checked = isTrue(data.features.approvalRequired);
      if(isTrue(data.features.approvalRequired))
      {
        $('#approval_by_other_user')[0].disabled = false;
        $('#approval_type1')[0].disabled = false;
        $('#approval_type2')[0].disabled = false;
        $('#approval_position1')[0].disabled = false;
        $('#approval_position2')[0].disabled = false;
        $('#approval_bulk')[0].disabled = false;
      }
      else
      {
        $('#approval_by_other_user')[0].disabled = true;
        $('#approval_type1')[0].disabled = true;
        $('#approval_type2')[0].disabled = true;
        $('#approval_position1')[0].disabled = true;
        $('#approval_position2')[0].disabled = true;
        $('#approval_bulk')[0].disabled = true;
      }
      $('#with_approval_note')[0].checked = isTrue(data.features.approvalNote);
      $('#approval_by_other_user')[0].checked = isTrue(data.features.approvalByAnotherUser);
      $('#with_trash')[0].checked = isTrue(data.features.trashRequired);
      $('#subquery')[0].checked = isTrue(data.features.subquery);
      $('#with_validation')[0].checked = isTrue(data.features.withValidation);
      $('#ajax_support')[0].checked = isTrue(data.features.ajaxSupport);
      $('#backend_only')[0].checked = isTrue(data.features.backendOnly);
      if(isTrue(data.features.backendOnly))
      {
        $('#ajax_support')[0].disabled = true;
      }
      else
      {
        $('#ajax_support')[0].disabled = false;
      }
      $('[name="approval_type"][value="' + data.features.approvalType + '"]')[0].checked = true;
      $('[name="approval_position"][value="' + data.features.approvalPosition + '"]')[0].checked = true;
    }
    else
    {
      resetFeatureForm()
    }
  }
  else
  {
    resetFeatureForm();
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

  if (data.specification == null || typeof data.specification == 'undefined' && data.specification.length == 0) {
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
        if(column == null)
        {
          column = '';
        }
        if(value == null)
        {
          value = '';
        }
        if(comparison == null)
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
  selector = '#modal-order-data tbody tr:last-child';

  while ($('#modal-order-data tbody tr').length > 1) {
    $(selector).remove();
  }
  $(selector).find('.data-filter-column-name').val('');
  $(selector).find('.data-filter-column-value').val('');

  cnt = 0;

  if (!data.sortable || data.sortable.length === 0) {
    $(selector).find('.data-order-column-name').val('');
    $(selector).find('.data-order-order-type').val('PicoSort::ORDER_TYPE_ASC');
  } else {
    for (let i in data.sortable) {
      if (data.sortable.hasOwnProperty(i)) {
        if (cnt > 0) {
          let trHtml = $(selector).clone(true); 
          $(selector).parent().append(trHtml);
        }
        $(selector).find('.data-order-column-name').val(data.sortable[i].sortBy);
        $(selector).find('.data-order-order-type').val(data.sortable[i].sortType);
        cnt++;
      }
    }
  }

}

/**
 * Resets the feature form to its default state.
 */
function resetFeatureForm()
{
  $('#activate_deactivate')[0].checked = false
  $('#manual_sort_order')[0].checked = false;
  $('#export_to_excel')[0].checked = false;
  $('#export_to_csv')[0].checked = false;
  $('#export_use_temporary')[0].checked = false;
  $('#export_to_excel')[0].disabled = false;
  $('#export_use_temporary')[0].disabled = true;
  $('#user_activity_logger')[0].checked = false;
  $('#with_approval')[0].checked = false;      
  $('#approval_by_other_user')[0].disabled = true;
  $('#approval_type1')[0].disabled = true;
  $('#approval_type2')[0].disabled = true;
  $('#approval_position1')[0].disabled = true;
  $('#approval_position2')[0].disabled = true;
  $('#approval_bulk')[0].disabled = true;
  $('[name="approval_type"][value="2"]')[0].checked = true;
  $('[name="approval_position"][value="after-data"]')[0].checked = true;
  $('#with_approval_note')[0].checked = false;
  $('#approval_by_other_user')[0].checked = false;
  $('#with_trash')[0].checked = false;
  $('#backend_only')[0].checked = false;
  $('#ajax_support')[0].checked = false;
  $('#ajax_support')[0].disabled = false;
  $('#subquery')[0].checked = false;
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
 * 
 * @param {string} field - Field name
 * @param {object} args - Additional arguments that may influence the row's configuration.
 * @returns {string} Button and hidden input containing data format
 */
function generateDataFormat(field, args)
{
  return `<button type="button" id="format_data_${field}" class="btn btn-sm btn-primary button-format-data" data-type="${args.data_type}">Format</button><input type="hidden" class="input-format-data" value="">`;
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
    "int": [
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
    "float": [
      "numeric",
      "double",
      "real",
      "money"
    ],
    "text": [
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
    "date": [
      "date"
    ],
    "time": [
      "time"
    ],
    "file": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    "image": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    "audio": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
    ],
    "video": [
      "char",
      "character",
      "varchar",
      "character varying",
      "text"
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
        <option value="file" title="&lt;input type=&#39;file&#39;&gt;">file</option>
        <option value="image" title="&lt;input type=&#39;file&#39;&gt;">image</option>
        <option value="audio" title="&lt;input type=&#39;file&#39;&gt;">audio</option>
        <option value="video" title="&lt;input type=&#39;file&#39;&gt;">video</option>
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
  classes.push("validation-item")
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
        <input type="checkbox" class="include-insert" name="include_insert_${field}" value="0" disabled="disabled">
      </td>
    `;

    editRow = `
      <td align="center">
        <input type="checkbox" class="include-edit" name="include_edit_${field}" value="0" disabled="disabled">
      </td>
    `;

    listRow = `
      <td align="center">
        <input type="checkbox" class="include-list" name="include_list_${field}" value="1">
      </td>
    `;

  } else {
    insertRow = `
      <td align="center">
        <input type="checkbox" class="include-insert" name="include_insert_${field}" value="1" checked="checked">
      </td>
    `;

    editRow = `
      <td align="center">
        <input type="checkbox" class="include-edit" name="include_edit_${field}" value="1" checked="checked">
      </td>
    `;

    listRow = `
      <td align="center">
        <input type="checkbox" class="include-list" name="include_list_${field}" value="1" checked="checked">
      </td>
    `;

  }

  exportRow = `
    <td align="center">
      <input type="checkbox" class="include-export" name="include_export_${field}" value="1" checked="checked">
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
        <input type="checkbox" class="include-detail" name="include_detail_${field}" value="1" checked="checked">
      </td>
      ${listRow}
      ${exportRow}
      <td align="center">
        <input type="checkbox" class="include-key" name="include_key_${field}" value="1">
      </td>
      <td align="center">
        <input type="checkbox" class="include-required" name="include_required_${field}" value="1">
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
        <input type="checkbox" class="input-multiple-data" name="multiple_data_${field}" value="1">
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
      <td align="center">
        <input type="checkbox" class="input-multiple-filter" name="multiple_filter_${field}" value="1" disabled="disabled">
      </td>
      <td>
        ${generateSelectType(field, args)}
      </td>
      <td>
        ${generateDataFormat(field, args)}
      </td>
      <td>
        ${generateSelectFilter(field, args)}
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-primary validation-button">Validation</button>
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
 */
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
    onezero: onezero
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
  if (data && data.entity && data.entity.group) // NOSONAR
  {
    selector.attr('data-group-source', data.entity.group.source);
    selector.find(".rd-group-value").val(data.entity.group.value);
    selector.find(".rd-group-label").val(data.entity.group.label);
    selector.find(".group-reference").filter('[value="'+data.entity.group.source+'"]')[0].checked = true;
    selector.find(".rd-group-entity").val(data.entity.group.entity);
    let table = selector.find('table.table-reference');
    let group = data.entity.group;
    if (group && Array.isArray(group.map) && group.map.length > 0) {
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
      if (isTrue(map[i]["selected"])) {
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
 *
 * @returns {string} The HTML string for the reference configuration form.
 */
function getReferenceResource() {
  return referenceResource;
}

/**
 * Loads translation data from the server based on the selected target language.
 *
 * @param {string} url - The AJAX endpoint to fetch translation data.
 * @param {string} translationType - The translation type to set in the UI (e.g., 'menu', 'validation', 'menu-group').
 * @returns {void}
 */
function loadTranslation(url, translationType)
{
  const languageId = $('.container-translate-app .target-language').val();
  increaseAjaxPending();
  $.ajax({
    method: "GET",
    url: url,
    data: { userAction: 'get', targetLanguage: languageId },
    success: function (data) {
      appTranslationData = data;
      decreaseAjaxPending();
      $('.module-translation-status').text('');
      const textOut1 = [];
      const textOut2 = [];
      const propertyNames = [];

      for (const item of data) {
        textOut1.push(item.original);
        textOut2.push(item.translated);
        propertyNames.push(item.propertyName);
      }

      transEd5.getDoc().setValue(textOut1.join('\r\n'));
      transEd6.getDoc().setValue(textOut2.join('\r\n'));
      setTimeout(() => {
        transEd5.refresh();
        transEd6.refresh();
      }, 50);

      $('.app-property-name').val(propertyNames.join('|'));
      $('#app_translation_type').val(translationType);
      focused = {};
      transEd5.removeLineClass(lastLine2, 'background', 'highlight-line');
      transEd6.removeLineClass(lastLine2, 'background', 'highlight-line');
      lastLine2 = -1;
    },
    error: function (err) {
      decreaseAjaxPending();
    },
  });
}

/**
 * Loads the appropriate application translation based on the selected type.
 * It determines the correct AJAX URL and translation type, then calls `loadTranslation`.
 */
function loadAppTranslation() {
    let translationType = $('#app_translation_type').val();
    let url = '';
    if (translationType == 'menu') {
        url = 'lib.ajax/menu-translate.php';
    } else if (translationType == 'menu-group') {
        url = 'lib.ajax/menu-group-translate.php';
    } else if (translationType == 'validation') {
        url = 'lib.ajax/validation-translate.php';
    }
    loadTranslation(url, translationType);
}

/**
 * Loads the menu translations specifically.
 * It calls `loadTranslation` with the URL for menu translations and the type 'menu'.
 */
function loadMenuTranslation() {
    loadTranslation('lib.ajax/menu-translate.php', 'menu');
}

/**
 * Loads the menu group translations specifically.
 * It calls `loadTranslation` with the URL for menu group translations and the type 'menu-group'.
 */
function loadMenuGroupTranslation() {
    loadTranslation('lib.ajax/menu-group-translate.php', 'menu-group');
}

/**
 * Loads the validation translations specifically.
 * It calls `loadTranslation` with the URL for validation translations and the type 'validation'.
 */
function loadValidationTranslation() {
    loadTranslation('lib.ajax/validation-translate.php', 'validation');
}