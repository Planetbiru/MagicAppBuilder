var initielized = false;
var editorPHP = null;
var editorJSP = null;
var editorSQL = null;
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};
function initCodeMirror()
{
	if(!initielized)
	{
		$('.code-area').css({'display':'block'});

		editorPHP = CodeMirror.fromTextArea(document.getElementById("text-code-php"), {
			lineNumbers: true,
			matchBrackets: true,
			mode: "application/x-httpd-php"
		});
		editorJSP = CodeMirror.fromTextArea(document.getElementById("text-code-jsp"), {
			lineNumbers: true,
			matchBrackets: true,
			mode: "text/x-csrc"
		});
		editorSQL = CodeMirror.fromTextArea(document.getElementById("sql-from-server"), {
			mode: "text/x-pgsql",
			indentWithTabs: true,
			smartIndent: true,
			lineNumbers: true,
			matchBrackets : true
		});
		initielized = true;
	}
}
function saveState(frm1, frm2)
{
	var arr = frm1.serializeArray();
	var value = JSON.stringify(arr);
	var dbInfo = databaseInfo(frm2);
	var host = dbInfo.host.val();
	var port = dbInfo.port.val();
	var database = dbInfo.database.val();
	var table = dbInfo.table.val();

	var key = host+"_"+port+"_"+database+"_"+table;
	window.localStorage.setItem(key, value);
}

function databaseInfo(frm2)
{
	var host = $(frm2).find('#host');
	var port = $(frm2).find('#port');
	var database = $(frm2).find('#database');
	var table = $(frm2).find('#table');
	return {host:host, port:port, database:database, table:table};
}
function getSavedData(frm2)
{
	var dbInfo = databaseInfo(frm2);
	var host = dbInfo.host.val();
	var port = dbInfo.port.val();
	var database = dbInfo.database.val();
	var table = dbInfo.table.val();
	var key = host+"_"+port+"_"+database+"_"+table;

	var str = window.localStorage.getItem(key);

	var defdata = JSON.parse(str);
	return defdata;
}
function hasKey(defdata, key)
{
	var len = defdata.length;
	var i;
	for(i = 0; i<len; i++)
	{
		if(defdata[i].value == key)
		{
			return true;
		}
	}
	return false;
}
function loadState(defdata, frm1, frm2)
{

	var i;
	frm2.find("tbody tr").each(function(index, e){
		var tr = $(this);
		var field = tr.find('input[type="hidden"][name="field"]').val();
		if(hasKey(defdata, field))
		{
			$(frm2).find('input[name$="'+field+'"]').each(function(index2, e2){
				$(this)[0].checked = false;
			});
		}
	});
	
	
	for(i in defdata)
	{
		var obj = $(frm2).find(':input[name='+defdata[i]['name']+']');
		if(obj.length)
		{
			var val = defdata[i]['value'];
			var name = defdata[i]['name'];
			var tagName = obj.prop("tagName").toString().toLowerCase();
			var type = obj.attr('type');
			
			if(type == 'radio')
			{
				$(frm2).find('[name="'+name+'"][value="'+val+'"]')[0].checked = true;
			}
			else if(type == 'checkbox' && val != null && val != 0 && val != "0")
			{
				$(frm2).find('[name="'+name+'"]')[0].checked = true;
			}
			else if(tagName == 'select')
			{
				obj.val(defdata[i]['value']);
			}
		}
	}
}

// sql-from-server
$(document).ready(function(e) {
    //$('.tabs').tabs();
	
	$(document).on('change click', '#formgenerator input, #formgenerator select', function(e){
		var frm1 = $(this).closest('form');
		var frm2 = $('#formdatabase');
		saveState(frm1, frm2);
	})
	
	$(document).on('submit', '#formgenerator', function(e)
	{
		var formData = $(this).serializeArray(); 
		
		var args = {withApproval:$('#with_approval')[0].checked, withTrash:$('#with_trash')[0].checked, withNote:$('#with_approval_note')[0].checked, defaultOrderDesc:$('#pkeydesc')[0].checked, sortOrder:$('#manualsortorder')[0].checked};
		
		
		var scriptPHP = "";
		var scriptJSP = "";
		
		var language = "";
		
		
		language = "php";
		scriptPHP = "";
		scriptPHP += generateAuthentification(formData, language);
		scriptPHP += generateController(formData, args, language);
		scriptPHP += generateInsertUI(formData, args, language);
		scriptPHP += generateUpdateUI(formData, args, language);
		scriptPHP += generateDetailUI(formData, args, language);
		scriptPHP += generateListUI(formData, args, language);
		scriptPHP = scriptPHP.replaceAll('Default Data', 'Default');
		scriptPHP = scriptPHP.replaceAll('Sort Order', 'Order');

		
		if($('#quote')[0].checked)
		{
		}
		else
		{		
			scriptPHP = finalizeString(scriptPHP);
		}
		
		language = "jsp";
		scriptJSP = "";
		scriptJSP += generateAuthentification(formData, language);
		scriptJSP += generateController(formData, args, language);
		scriptJSP += generateInsertUI(formData, args, language);
		scriptJSP += generateUpdateUI(formData, args, language);
		scriptJSP += generateDetailUI(formData, args, language);
		scriptJSP += generateListUI(formData, args, language);
		scriptJSP = scriptJSP.replaceAll('Default Data', 'Default');
		scriptJSP = scriptJSP.replaceAll('Sort Order', 'Order');
		
		if($('#quote')[0].checked)
		{
		}
		else
		{		
			scriptJSP = finalizeString(scriptJSP);
		}
		
		initCodeMirror();
		$('.tabs').find(' > ul > li > a[href="#sql-query"]').click();
		editorSQL.getDoc().setValue(SQLFromServer);
		$('.tabs').find(' > ul > li > a[href="#result-jsp"]').click();
		editorJSP.getDoc().setValue(scriptJSP);
		$('.tabs').find(' > ul > li > a[href="#result-php"]').click();
		editorPHP.getDoc().setValue(scriptPHP);
		e.preventDefault();
	});
	$(document).on('click', '#load_table', function(e){
		var url = "database-table-list.php";
		var table = $('.config-table [name="table"]');
		$.ajax({
			url:url,
			type:"GET",
			dataType:"JSON",
			success: function(data)
			{
				var i;
				var tablename;
				table.replaceWith($('<select name="table" id="table"></select>'));
				for(i in data)
				{
					tablename = data[i].table_name;
					$('.config-table [name="table"]').append('<option value="'+tablename+'">'+tablename+'</option>');
				}
			},
			error: function(err, err2){
			}
		});
	});
	$(document).on('click', '#load_structure', function(e){
		var selector = ".main-table tbody";
		var url = "structure.php";
		var host = $('.config-table [name="host"]').val();
		var port = $('.config-table [name="port"]').val();
		var username = $('.config-table [name="username"]').val();
		var password = $('.config-table [name="password"]').val();
		var database = $('.config-table [name="database"]').val();
		var table = $('.config-table [name="table"]').val();

		if(database != '' && table != '')
		{
			loadJSONFromServer(selector, url, host, port, username, password, database, table);
			$('.label-for-host').text(host);
			$('.label-for-database').text(database);
			$('.label-for-table').text(table);
			$('.define-wrapper').css({display:"block"});
			$('.tabs').find(' > ul > li > a[href="#define"]').click(); 
			$('#formgenerator input[name="table"]').val(table);
			
			setTimeout(function(){
				var isKW1 = isKeyWord(table);
				var isKW2 = ($(selector).find('.reserved').length > 0);
				if(isKW1 || isKW2)
				{
					$('#quote').attr('data-reserved', 'true');
					$('#quote')[0].checked = true;
				}
				else
				{
					$('#quote').attr('data-reserved', 'false');
				}
			}, 1000);
		}
		else
		{
			alert('Please complete form first.');
		}
	});
	$(document).on('change', '#quote', function(e){
		var chk = $('#quote')[0].checked;
		if(!chk && $(this).attr('data-reserved') == 'true')
		{
			alert('This table contain PostgreSQL reserved word. You must quote table and field name.');
		}
	});
	$(document).on('change keyup blur', '#formdatabase input[type="text"], #formdatabase input[type="password"]', function(e){
		var name = $(this).attr('name');
		var value = $(this).val();
		window.localStorage.setItem('sg_'+name, value);
	});
	
	$(document).on('change', '.list_filter', function(e){
		var obj = $(this);
		var value = obj.attr('value');
		var name = obj.attr('name');
		var chk = obj[0].checked;
		if(chk)
		{
			var contra = getContraVal(value);
			$('[name="'+name+'"][value="'+contra+'"]')[0].checked = false;
			console.log(contra);
		}
	});
	loadConfig();
});
function getContraVal(val)
{
	if(val == 'text') return 'select';
	if(val == 'select') return 'text';	
}
function loadConfig()
{
	var host = window.localStorage.getItem('sg_host') || '';
	var port = window.localStorage.getItem('sg_port') || '';
	var username = window.localStorage.getItem('sg_username') || '';
	var password = window.localStorage.getItem('sg_password') || '';
	var database = window.localStorage.getItem('sg_database') || '';
	var table = window.localStorage.getItem('sg_table') || '';
	$('.config-table [name="host"]').val(host);
	$('.config-table [name="port"]').val(port);
	$('.config-table [name="username"]').val(username);
	$('.config-table [name="password"]').val(password);
	$('.config-table [name="database"]').val(database);
	$('.config-table [name="table"]').val(table);
}

function finalizeString(script)
{
	script = script.replaceAll('`', '');
	return script;
}

function generateAuthentification(formData, language)
{
	language = language || 'php';
	
	var script = '';
	if(language == 'jsp')
	{
		script = '<%@ include file="lib.inc/auth-with-login-form.jsp" %>';
	}
	else if(language == 'php')
	{
		script = '<?php include_once dirname(__FILE__)."/lib.inc/auth-with-login-form.php"; ?>';
	}
	return script;
}

function generateController(formData, args, language)
{
	language = language || 'php';
	
	args = args || {};
	args.withApproval = args.withApproval || false;
	args.withTrash = args.withTrash || false;
	args.withNote = args.withNote || false;
	
	var i, j, k;
	var fields = getFieldValues(formData, 'field');
	var currentTable = getFieldValues(formData, 'table')[0];
	var values = [];
	
	var pk = getPrimaryKey(formData);
	var pkey = (pk.length)?pk[0]:fields[0];
	var pkeyFilter = getSelectedFilterType(formData, pkey);
	
	var filter = '';
	var script = '';
	
	if(language == 'jsp')
	{
	
		script += '<%\r\n';
	
		script += '// Script Generator\r\n';
		script += '// Witten by Kamshory\r\n';
		script += '// All rights reserved\r\n';
		script += '// http://planetbiru.com/kamshory\r\n\r\n';
		
		script += '// declare all variables\r\n\r\n';
		script += '_self_name = "'+currentTable.replaceAll('_', '-')+'.jsp";\r\n';
		script += '_table_name = "'+currentTable+'";\r\n';
		script += '_module_name = "'+currentTable.replaceAll('_', '-')+'";\r\n';
		script += '_module_title = "'+currentTable.replaceAll('_', ' ').capitalize().prettify().trim()+'";\r\n\r\n';
		
		script += 'String _pkey_name = "'+pkey+'";\r\n';
		script += 'String _pkey_value = "";\r\n';
		script += 'String _na_name = "aktif";\r\n';
		script += 'String _now = query1.now();\r\n';
		
		script += 'String _query;\r\n';
		script += 'String _action = filterInput(request, "user_action", "FILTER_SANITIZE_STRING", true);\r\n';
		script += 'String _activate = filterInput(request, "data_activate", "FILTER_SANITIZE_STRING", true);\r\n';
		script += 'String _deactivate = filterInput(request, "data_deactivate", "FILTER_SANITIZE_STRING", true);\r\n';
		script += 'String _delete = filterInput(request, "data_delete", "FILTER_SANITIZE_STRING", true);\r\n';
		script += 'String _save = filterInput(request, "button_save", "FILTER_SANITIZE_STRING", true);\r\n';
		script += 'String _show_only = filterInput(request, "show-only", "FILTER_SANITIZE_SPECIAL_CHARS", true);\r\n\r\n';
		
		script += '// You can comment two lines below in case you don\'t want to use it.\r\n';
		script += '_permission = getPermission(_userLevel, _module_name);\r\n';
		script += 'createUserLog(request, _userID, _module_name, _action);\r\n\r\n';
		
		script += 'int int_i = 0, int_j = 0, int_k = 0;\r\n';
		
		var insertFields = getInsertFields(formData);
		var updateFields = getUpdateFields(formData);
		var insertUpdateFields = arrayUnique(insertFields.concat(updateFields));
	
		// fields di sini adalah field gabungan antara insert dan update
	
		for(i in insertUpdateFields)
		{
			script += 'String r_'+insertUpdateFields[i]+' = "";\r\n';
		}
		if(args.withNote)
		{
			script += 'String r_apv_note = "";\r\n';
		}
		script += '\r\n';
		script += 'if(_action.equals("insert") || _action.equals("update")) \r\n{\r\n';
	
	
		
		for(i in insertUpdateFields)
		{
			filter = getSelectedFilterType(formData, insertUpdateFields[i]);
			script += '\tr_'+insertUpdateFields[i]+' = filterInput(request, "'+insertUpdateFields[i]+'", "'+filter+'", true);\r\n';
		}
		
		if(args.withNote)
		{
			script += '\tr_apv_note = filterInput(request, "apv_note", "FILTER_SANITIZE_SPECIAL_CHARS", true);\r\n';
		}
		
		
		script += '}\r\n\r\n';
	
		
		
		// insert
		values = [];
		for(i in insertFields)
		{
			values[i] = 'r_'+insertFields[i];
		}
		script += '// [INSERT BEGIN]\r\n';
		script += '// TODO: Here are your code to insert data to database\r\n';
		script += 'if(_action.equals("insert") && !_save.equals("")) \r\n{\r\n';
		script += '\tif(hasPermission(_userLevel, _module_name, "insert"))\r\n\t{\r\n';
	
		script += '\t\t_query = query1.newQuery()\r\n'+
		'\t\t\t.insert()\r\n'+
		'\t\t\t.into("`'+currentTable+'`")\r\n'+
		'\t\t\t.fields("(`' + insertFields.join('`, `')+'`)")\r\n'+
		'\t\t\t.values("(\'\"+' + values.join('+"\', \'\"+')+'+"\')")\r\n'+
		'\t\t\t.toString();\r\n';
		script += '\t\tdatabase1.execute(_query);\r\n\r\n';
	
		script += '\t\tlong newID = database1.getLastID();\r\n';
		
		////////////////////////////////////////////////
		/////////////// APPROVAL BEGIN /////////////////
		////////////////////////////////////////////////
		
		if(args.withApproval)
		{
			var insertFieldsApv = insertFields;
			var insertValuesApv = values;
			/*
			if(insertFieldsApv.indexOf(pkey) == -1)
			{
				insertFieldsApv.push(pkey);
				insertValuesApv.push('_pkey_value');
			}
			*/
			if(args.withNote)
			{
				insertFieldsApv.push('note_create');
				insertValuesApv.push('r_apv_note');
			}
	
			script += '\r\n';
			script += '\t\t// [NEED APPROVAL BEGIN]';
			script += '\r\n';
	
	
	
			if($.inArray(currentTable+'_id', insertFieldsApv) == -1)
			{
				insertFieldsApv.push(currentTable+'_id');
				insertValuesApv.push('newID');
			}
	
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.insert()\r\n'+
			'\t\t\t.into("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t.fields("(`' + insertFieldsApv.join('`, `')+'`)")\r\n'+
			'\t\t\t.values("(\'\"+' + insertValuesApv.join('+"\', \'\"+')+'+"\')")\r\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n\r\n';
	
			
			script += '\t\tlong newApvID = database1.getLastID();\r\n';
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t.set("`admin_buat` = \'"+_userID+"\', `waktu_buat` = "+_now+", `ip_buat` = \'"+_remoteAddress+"\', `status_data` = \'1\', `status_approve` = \'0\', `'+pkey+'` = \'"+newID+"\'")\r\n'+
			'\t\t\t.where("`'+currentTable+'_apv_id` = \'"+newApvID+"\'")\r\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n\r\n';
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.update("`'+currentTable+'`")\r\n'+
			'\t\t\t.set("`draft` = 1, `waiting_for` = 1, `'+currentTable+'_apv_id` = \'"+newApvID+"\'")\r\n'+
			'\t\t\t.where("`'+pkey+'` = \'"+newID+"\'")\t\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n\r\n';
	
			if(args.withNote)
			{
				script += '\t\t_query = query1.newQuery()\r\n'+
				'\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t.set("`note_create` = \'"+r_apv_note+"\', ")\r\n'+
				'\t\t\t.where("`'+currentTable+'_apv_id` = \'"+newApvID+"\'")\r\n'+
				'\t\t\t.toString();\r\n';
				script += '\t\tdatabase1.execute(_query);\r\n\r\n';
			}
	
			script += '\t\t// [NEED APPROVAL END]';
			script += '\r\n';
			script += '\r\n';
		}
	
		////////////////////////////////////////////////
		///////////////  APPROVAL END  /////////////////
		////////////////////////////////////////////////
		
		script += '\t\tresponse.sendRedirect(_self_name+"?user_action=detail&'+pkey+'="+newID);\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to insert new data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [INSERT END]\r\n';
	
		script += '\r\n\r\n';
	
		// update
		values = [];
		for(i in updateFields)
		{
			values[i] = 'r_'+updateFields[i];
		}
		script += '// [UPDATE BEGIN]\r\n';
		script += '// TODO: Here are your code to update data to database\r\n';
		script += 'if(_action.equals("update") && !_save.equals("")) \r\n{\r\n';
		script += '\tif(hasPermission(_userLevel, _module_name, "update"))\r\n\t{\r\n';
		script += '\t\t_pkey_value = filterInput(request, "'+pkey+'", "'+pkeyFilter+'", true);\r\n';
	
		////////////////////////////////////////////////
		/////////////// APPROVAL BEGIN /////////////////
		////////////////////////////////////////////////
	
		if(args.withApproval)
		{
			var updateFieldsApv = updateFields;
			var updateValuesApv = values;
			if(updateFieldsApv.indexOf(pkey) == -1)
			{
				updateFieldsApv.push(pkey);
				updateValuesApv.push('_pkey_value');
			}
			
			script += '\r\n';
			script += '\t\t// [NEED APPROVAL BEGIN]';
			script += '\r\n';
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.insert()\r\n'+
			'\t\t\t.into("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t.fields("(`' + updateFieldsApv.join('`, `')+'`)")\r\n'+
			'\t\t\t.values("(\'"+' + updateValuesApv.join('+"\', \'\"+')+'+"\')")\r\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n\r\n';
					
			script += '\t\tlong newApvIDForEdit = database1.getLastID();\r\n';
			
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.update("`'+currentTable+'`")\r\n'+
			'\t\t\t.set("`waiting_for` = 2, `'+currentTable+'_apv_id` = \'"+newApvIDForEdit+"\'")\r\n'+
			'\t\t\t.where("`'+pkey+'` = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n\r\n';
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t.set("`admin_buat_id` = \'"+_userID+"\', `waktu_buat` = "+_now+", `ip_buat` = \'"+_remoteAddress+"\', `status_data` = \'0\', `status_approve` = \'0\', `'+pkey+'` = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t.where("`'+currentTable+'_apv_id` = \'"+newApvIDForEdit+"\'")\r\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n';
			if(args.withNote)
			{
				script += '\t\t_query = query1.newQuery()\r\n'+
				'\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t.set("`note_create` = \'"+r_apv_note+"\'")\r\n'+
				'\t\t\t.where("`'+currentTable+'_apv_id` = \'"+newApvIDForEdit+"\'")\r\n'+
				'\t\t\t.toString();\r\n';
				script += '\t\tdatabase1.execute(_query);\r\n\r\n';
			}
			script += '\t\t// [NEED APPROVAL END]';
			script += '\r\n';
			script += '\r\n';
		}
		else
		{
			var ss = [];
			for(i in updateFields)
			{
				ss.push('`'+updateFields[i]+'` = \'"+r_'+updateFields[i]+'+"\'');
			}
			script += '\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t.update("`'+currentTable+'`")\r\n'+
			'\t\t\t.set("'+ss.join(', ')+'")\r\n'+
			'\t\t\t.where("`'+pkey+'` = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t.toString();\r\n';
			script += '\t\tdatabase1.execute(_query);\r\n\r\n';
		}
		
		////////////////////////////////////////////////
		///////////////  APPROVAL END  /////////////////
		////////////////////////////////////////////////
	
		script += '\t\tresponse.sendRedirect(_self_name+"?user_action=detail&'+pkey+'="+_pkey_value);\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to update data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [UPDATE END]\r\n';
	
		script += '\r\n\r\n';
	
		// activate
		script += '// [ACTIVATE BEGIN]\r\n';
		script += '// TODO: Here are your code to activate selected data\r\n';
		script += '//       You can add filter here or modify the default code\r\n';
		script += 'if(!_activate.equals("")) \r\n{\r\n';
		script += '\tif(hasPermission(_userLevel, _module_name, "update"))\r\n\t{\r\n';
		script += '\t\ttry\r\n\t\t{\r\n';
		script += '\t\t\tString[] activate_key_array = getRequestArray(request, _pkey_name);\r\n';
		script += '\t\t\tfor(int_i = 0; int_i < activate_key_array.length; int_i++)\r\n'
		script += '\t\t\t{\r\n';
		if(args.withApproval)
		{
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("'+currentTable+'_apv_id = -1, waiting_for = 3")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+cms.escapeSQL(activate_key_array[int_i])+"\' and '+currentTable+'_apv_id = 0")\r\n'+
			'\t\t\t\t\t.toString();\r\n';	
			script += '\t\t\t\tdatabase1.execute(_query);\r\n';
		}
		else
		{		
			script += '\t\t\t\tcms.setRecordValue("'+currentTable+'", "'+pkey+'", activate_key_array[int_i], _na_name, "1");\r\n';
		}
		script += '\t\t\t}\r\n';
		script += '\t\t\tresponse.sendRedirect(_self_name);\r\n';
		script += '\t\t}\r\n';
		script += '\t\tcatch(Exception e)\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t\tfinally\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to update data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [ACTIVATE END]\r\n';
	
		script += '\r\n\r\n';
	
		// deactivate
		script += '// [DEACTIVATE BEGIN]\r\n';
		script += '// TODO: Here are your code to deactivate selected data\r\n';
		script += '//       You can add filter here or modify the default code\r\n';
		script += 'if(!_deactivate.equals("")) \r\n{\r\n';
		script += '\tif(hasPermission(_userLevel, _module_name, "update"))\r\n\t{\r\n';
		script += '\t\ttry\r\n\t\t{\r\n';
		script += '\t\t\tString[] deactivate_key_array = getRequestArray(request, _pkey_name);\r\n';
		script += '\t\t\tfor(int_i = 0; int_i < deactivate_key_array.length; int_i++)\r\n'
		script += '\t\t\t{\r\n';
		if(args.withApproval)
		{
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("'+currentTable+'_apv_id = -1, waiting_for = 4")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+cms.escapeSQL(deactivate_key_array[int_i])+"\' and '+currentTable+'_apv_id = 0")\r\n'+
			'\t\t\t\t\t.toString();\r\n';	
			script += '\t\t\t\tdatabase1.execute(_query);\r\n';
		}
		else
		{		
			script += '\t\t\t\tcms.setRecordValue("'+currentTable+'", "'+pkey+'", deactivate_key_array[int_i], _na_name, "0");\r\n';
		}
		script += '\t\t\t}\r\n';
		script += '\t\t\tresponse.sendRedirect(_self_name);\r\n';
		script += '\t\t}\r\n';
		script += '\t\tcatch(Exception e)\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t\tfinally\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to update data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [DEACTIVATE END]\r\n';
	
		script += '\r\n\r\n';
	
		// delete
		script += '// [DELETE BEGIN]\r\n';
		script += '// TODO: Here are your code to deactivate selected data\r\n';
		script += '//       You can add filter here or modify the default code\r\n';
		script += 'if(!_delete.equals("")) \r\n{\r\n';
		script += '\tif(hasPermission(_userLevel, _module_name, "delete"))\r\n\t{\r\n';
		script += '\t\ttry\r\n\t\t{\r\n';
		script += '\t\t\tString[] delete_key_array = getRequestArray(request, _pkey_name);\r\n';
		script += '\t\t\tfor(int_i = 0; int_i < delete_key_array.length; int_i++)\r\n'
		script += '\t\t\t{\r\n';
		if(args.withApproval)
		{
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("'+currentTable+'_apv_id = -1, waiting_for = 5")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+cms.escapeSQL(delete_key_array[int_i])+"\' and '+currentTable+'_apv_id = 0")\r\n'+
			'\t\t\t\t\t.toString();\r\n';	
			script += '\t\t\t\tdatabase1.execute(_query);\r\n';
		}
		else
		{		
			script += '\t\t\t\tcms.deleteRecord("'+currentTable+'", "'+pkey+'", delete_key_array[int_i]);\r\n';
			
			if(args.withTrash)
			{
				script += '\t\t\t\tcms.updateTrashInfo("'+currentTable+'_trash", "'+pkey+'", delete_key_array[int_i], "'+currentTable+'_trash_id", _userID, _remoteAddress);\r\n';
			}
		}
		script += '\t\t\t}\r\n';
		script += '\t\t\tresponse.sendRedirect(_self_name);\r\n';
		script += '\t\t}\r\n';
		script += '\t\tcatch(Exception e)\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t\tfinally\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to delete data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [DELETE END]\r\n';
		
		
		if(args.withApproval)
		{
			var columList = updateFields.join(",");	
			script += '\r\n';
			script += '\r\n';
			script += '// [APPROVE BEGIN]\r\n'+
			'if(!filterInput(request, "data_approve", "FILTER_SANITIZE_STRING", true).equals(""))\r\n'+
			'{\r\n'+
			'\t// TODO: Here are your code for approve\r\n'+
			'\tif(hasPermission(_userLevel, _module_name, "approve"))\r\n'+
			'\t{\r\n'+
			'\t\t_pkey_value = filterInput(request, "'+pkey+'", "'+pkeyFilter+'", true);\r\n';
			if(args.withNote)
			{
				script += '\t\tr_apv_note = filterInput(request, "apv_note", "FILTER_SANITIZE_SPECIAL_CHARS", true);\r\n';
			}
			script += '\t\tResultSet _apv_values = cms.getValues("'+currentTable+'", "'+currentTable+'_id", _pkey_value, "'+currentTable+'_apv_id as apv_id, waiting_for");\r\n';
			
			script += '\t\tif(!_apv_values.getString("apv_id").equals("0"))\r\n'+
			'\t\t{\r\n';
			script += '\t\t\tString _apv_id = _apv_values.getString("apv_id");\r\n';
			script += '\t\t\tif(_apv_values.getString("waiting_for").equals("1"))\r\n'+
			'\t\t\t{\r\n';

			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t.set("`'+currentTable+'_apv_id` = 0, `draft` = 0, `waiting_for` = 0")\r\n'+
			'\t\t\t\t\t.where("`'+pkey+'` = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';
			script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';

			
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t.set("`status_approve` = \'1\', `admin_approve_id` = \'"+_userID+"\', `ip_address_approve` = \'"+_remoteAddress+"\', `time_approve` = "+_now)\r\n'+
			'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';
			script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			
			if(args.withNote)
			{
				script += '\t\t\t\t_query = query1.newQuery()\r\n'+
				'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t.set("`note_approve` = \'"+r_apv_note+"\'")\r\n'+
				'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
				'\t\t\t\t\t.toString();\r\n';
				script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			}
			
			script += '\t\t\t\t// cms.setRecordValue("'+currentTable+'_apv", "'+currentTable+'_apv_id", _apv_id, "status_approve", "1");\r\n';				
			script += '\t\t\t\t// database1.executeQuery("update `'+currentTable+'` set `'+currentTable+'_apv_id` = \'0\' where `'+pkey+'` = \'"+_pkey_value+"\'");\r\n';
			script += '\t\t\t}\r\n';
			script += '\t\t\telse if(_apv_values.getString("waiting_for").equals("2"))\r\n';
			script += '\t\t\t{\r\n';
			script += '\t\t\t\tcms.copyData("'+currentTable+'_apv", "'+currentTable+'", "'+columList+'", "'+currentTable+'_apv_id", _apv_id, "'+pkey+'", _pkey_value);\r\n';
	
			script += '\t\t\t\tcms.setRecordValue("'+currentTable+'", "'+pkey+'", _pkey_value, "'+currentTable+'_apv_id", "0");\r\n';
			script += '\t\t\t\tcms.setRecordValue("'+currentTable+'", "'+pkey+'", _pkey_value, "waiting_for", "0");\r\n';
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t.set("`status_approve` = \'1\', `admin_approve_id` = \'"+_userID+"\', `ip_address_approve` = \'"+_remoteAddress+"\', `time_approve` = "+_now)\r\n'+
			'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';
			script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			if(args.withNote)
			{
				script += '\t\t\t\t_query = query1.newQuery()\r\n'+
				'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t.set("`note_approve` = \'"+r_apv_note+"\'")\r\n'+
				'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
				'\t\t\t\t\t.toString();\r\n';
				script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			}
			script += '\t\t\t\t// cms.setRecordValue("'+currentTable+'_apv", "'+currentTable+'_apv_id", _apv_id, "status_approve", "1");\r\n'+
	
			'\t\t\t\t// database1.executeQuery("update `'+currentTable+'` set `'+currentTable+'_apv_id` = \'0\' where `'+pkey+'` = \'"+_pkey_value+"\'");\r\n'+
			'\t\t\t\t// database1.executeQuery("update `'+currentTable+'_apv` set `status_approve` = \'1\' where `'+currentTable+'_apv_id` = \'"+_apv_id+"\'");\r\n';
			script += '\t\t\t}\r\n';
			
			script += '\t\t\telse if(_apv_values.getString("waiting_for").equals("3"))\r\n';
			script += '\t\t\t{\r\n';
			// activate
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("active = 1, '+currentTable+'_apv_id = 0, waiting_for = 0 ")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n'+
			'\t\t\t\tdatabase1.execute(_query);\r\n';
			script += '\t\t\t}\r\n';
			
			script += '\t\t\telse if(_apv_values.getString("waiting_for").equals("4"))\r\n';
			script += '\t\t\t{\r\n';
			// deactivate
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("active = 0, '+currentTable+'_apv_id = 0, waiting_for = 0 ")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n'+
			'\t\t\t\tdatabase1.execute(_query);\r\n';
			script += '\t\t\t}\r\n';
			
			script += '\t\t\telse if(_apv_values.getString("waiting_for").equals("5"))\r\n';
			script += '\t\t\t{\r\n';
			// delete
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("'+currentTable+'_apv_id = 0, waiting_for = 0")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';	
			script += '\t\t\t\tdatabase1.execute(_query);\r\n';
			script += '\t\t\t\tcms.deleteRecord("'+currentTable+'", "'+pkey+'", _pkey_value);\r\n';
			if(args.withTrash)
			{
				script += '\t\t\t\tcms.updateTrashInfo("'+currentTable+'_trash", "'+pkey+'", _pkey_value, "'+currentTable+'_trash_id", _userID, _remoteAddress);\r\n';
			}
	
			script += '\t\t\t}\r\n';
			
			script += '\t\t}\r\n'+
			'\t\tresponse.sendRedirect(_self_name+"?show-only=need-approval");\r\n'+
			'\t}\r\n'+
			'}\r\n'+
			'// [APPROVE END]\r\n'+
			'\r\n'+
			'\r\n'+
			'// [REJECT BEGIN]\r\n'+
			'if(!filterInput(request, "data_reject", "FILTER_SANITIZE_STRING", true).equals(""))\r\n'+
			'{\r\n'+
			'\t// TODO: Here are your code for approve\r\n'+
			'\tif(hasPermission(_userLevel, _module_name, "approve"))\r\n'+
			'\t{\r\n'+
			'\t\t_pkey_value = filterInput(request, "'+pkey+'", "'+pkeyFilter+'", true);\r\n';
			if(args.withNote)
			{
				script += '\t\tr_apv_note = filterInput(request, "apv_note", "FILTER_SANITIZE_SPECIAL_CHARS", true);\r\n';
			}
			script += '\t\tResultSet _apv_values = cms.getValues("'+currentTable+'", "'+currentTable+'_id", _pkey_value, "'+currentTable+'_apv_id as apv_id, waiting_for");\r\n';
			script += '\t\tif(!_apv_values.getString("apv_id").equals("0"))\r\n';
			script += '\t\t{\r\n';
			script += '\t\t\tString _apv_id = _apv_values.getString("apv_id");\r\n';
			

			script += '\t\t\tif(_apv_values.getString("waiting_for").equals("1"))\r\n'+
			'\t\t\t{\r\n';

			script += '\t\t\t\t// delete source data\r\n';
	
			script += '\t\t\t\tcms.deleteRecord("'+currentTable+'", "'+pkey+'", _pkey_value);\r\n';
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t.set("`status_approve` = \'-1\', `admin_approve_id` = \'"+_userID+"\', `ip_address_approve` = \'"+_remoteAddress+"\', `time_approve` = "+_now)\r\n'+
			'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';
			script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			
			if(args.withNote)
			{
				script += '\t\t\t\t_query = query1.newQuery()\r\n'+
				'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t.set("`note_approve` = \'"+r_apv_note+"\'")\r\n'+
				'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
				'\t\t\t\t\t.toString();\r\n';
				script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			}
			script += '\t\t\t\t// cms.setRecordValue("'+currentTable+'_apv", "'+currentTable+'_apv_id", _apv_id, "status_approve", "-1");\r\n';
	
			script += '\t\t\t\t// database1.executeQuery("delete from `'+currentTable+'` where `'+pkey+'` = \'"+_pkey_value+"\'");\r\n';
			script += '\t\t\t\t// database1.executeQuery("update `'+currentTable+'_apv` set `status_approve` = \'-1\' where `'+currentTable+'_apv_id` = \'"+_apv_id+"\'");\r\n';
			script += '\t\t\t}\r\n';
			script += '\t\t\telse\r\n';
			script += '\t\t\t{\r\n';
			script += '\t\t\t\tcms.setRecordValue("'+currentTable+'_apv", "'+currentTable+'_apv_id", _apv_id, "status_approve", "-1");\r\n';
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t.set("`status_approve` = \'-1\', `admin_approve_id` = \'"+_userID+"\', `ip_address_approve` = \'"+_remoteAddress+"\', `time_approve` = "+_now)\r\n'+
			'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';
			script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			if(args.withNote)
			{
				script += '\t\t\t\t_query = query1.newQuery()\r\n'+
				'\t\t\t\t\t.update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t.set("`note_approve` = \'"+r_apv_note+"\'")\r\n'+
				'\t\t\t\t\t.where("`'+currentTable+'_apv_id` = \'"+_apv_id+"\'")\r\n'+
				'\t\t\t\t\t.toString();\r\n';
				script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			}
			script += '\t\t\t\t_query = query1.newQuery()\r\n'+
			'\t\t\t\t\t.update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t.set("'+currentTable+'_apv_id = 0, waiting_for = 0")\r\n'+
			'\t\t\t\t\t.where("'+pkey+' = \'"+_pkey_value+"\'")\r\n'+
			'\t\t\t\t\t.toString();\r\n';
			script += '\t\t\t\tdatabase1.execute(_query);\r\n\r\n';
			script += '\t\t\t\t// database1.executeQuery(_query);\r\n';
			
			script += '\t\t\t\t// database1.executeQuery("update `'+currentTable+'_apv` set `status_approve` = \'-1\' where `'+currentTable+'_apv_id` = \'"+_apv_id+"\'");\r\n';
			script += '\t\t\t}\r\n';
			script += '\t\t}\r\n';
			script += '\t\tresponse.sendRedirect(_self_name+"?show-only=need-approval");\r\n';
			script += '\t}\r\n';
			script += '}\r\n';
			script += '// [REJECT END]\r\n';
			script += '\r\n';	
		}
		
		script += '%>';
		
		script = script.replaceAll('\'"+r_waktu_buat+"\'', '"+r_waktu_buat+"');
		script = script.replaceAll('\'"+r_waktu_ubah+"\'', '"+r_waktu_ubah+"');
		
		
		script = script.replaceAll('r_waktu_buat = ', 'r_waktu_buat = "now()"; //');
		script = script.replaceAll('r_waktu_ubah = ', 'r_waktu_ubah = "now()"; //');
		script = script.replaceAll('r_admin_buat = ', 'r_admin_buat = _userID+""; //');
		script = script.replaceAll('r_admin_ubah = ', 'r_admin_ubah = _userID+""; //');
		script = script.replaceAll('r_ip_buat = ', 'r_ip_buat = _remoteAddress; //');
		script = script.replaceAll('r_ip_ubah = ', 'r_ip_ubah = _remoteAddress; //');
	}
	else if(language == 'php')
	{
				script += '<?php\r\n';
	
		script += '// Script Generator\r\n';
		script += '// Witten by Kamshory\r\n';
		script += '// All rights reserved\r\n';
		script += '// http://planetbiru.com/kamshory\r\n\r\n';
		
		script += '// declare all variables\r\n\r\n';
		script += '$_self_name = "'+currentTable.replaceAll('_', '-')+'.php";\r\n';
		script += '$_table_name = "'+currentTable+'";\r\n';
		script += '$_module_name = "'+currentTable.replaceAll('_', '-')+'";\r\n';
		script += '$_module_title = "'+currentTable.replaceAll('_', ' ').capitalize().prettify().trim()+'";\r\n\r\n';
		
		script += '$_pkey_name = "'+pkey+'";\r\n';
		script += '$_pkey_value = "";\r\n';
		script += '$_na_name = "aktif";\r\n';
		script += '$_now = $query1->now();\r\n';
		
		script += '$_query = "";\r\n';
		script += '$_action = filterInput(INPUT_GET, "user_action", FILTER_SANITIZE_STRING, true);\r\n';
		script += '$_activate = filterInput(INPUT_POST, "data_activate", FILTER_SANITIZE_STRING, true);\r\n';
		script += '$_deactivate = filterInput(INPUT_POST, "data_deactivate", FILTER_SANITIZE_STRING, true);\r\n';
		script += '$_delete = filterInput(INPUT_POST, "data_delete", FILTER_SANITIZE_STRING, true);\r\n';
		script += '$_save = filterInput(INPUT_POST, "button_save", FILTER_SANITIZE_STRING, true);\r\n';
		script += '$_show_only = filterInput(INPUT_GET, "show-only", FILTER_SANITIZE_SPECIAL_CHARS, true);\r\n\r\n';
		
		script += '// You can comment two lines below in case you don\'t want to use it.\r\n';
		script += '$_permission = $cms->getPermission($_userLevel, $_module_name);\r\n';
		
		script += 'if($_permission == "")\r\n';
		script += '{\r\n';
		script += '\tinclude_once dirname(__FILE__)."/lib.inc/forbidden.php";\r\n';
		script += '\texit();\r\n';
		script += '}\r\n';
		
		script += '$cms->createUserLog($_POST, $_userID, $_module_name, $_action);\r\n\r\n';
		
		script += '$int_i = 0; $int_j = 0; $int_k = 0;\r\n';
		
		var insertFields = getInsertFields(formData);
		var updateFields = getUpdateFields(formData);
		var insertUpdateFields = arrayUnique(insertFields.concat(updateFields));
	
		// fields di sini adalah field gabungan antara insert dan update
	
		for(i in insertUpdateFields)
		{
			script += '$r_'+insertUpdateFields[i]+' = "";\r\n';
		}
		if(args.withNote)
		{
			script += '$r_apv_note = "";\r\n';
		}
		script += '\r\n';
		
		script += 'if(isset($_POST[\'special_action\']))\r\n'+
			'{\r\n'+
			'	if(@$_POST[\'special_action\'] == \'save-order\')\r\n'+
			'	{\r\n'+
			'		$sort_data = trim(@$_POST[\'sort_data\']);\r\n'+
			'		$sort_offset = 1*trim(@$_POST[\'offset\']);\r\n'+
			'		$arr = explode(",", $sort_data);\r\n'+
			'		if(count($arr))\r\n'+
			'		{\r\n'+
			'			foreach($arr as $key=>$val)\r\n'+
			'			{\r\n'+
			'				$sort_order = $key + $sort_offset + 1;\r\n'+
			'				$_query = $query1->newQuery()\r\n'+
			'						->update("`$_table_name`")\r\n'+
			'						->set("`sort_order` = $sort_order")\r\n'+
			'						->where("`$_pkey_name` = \'$val\'")\r\n'+
			'						->toString();\r\n'+
			'				$db_rs = $database1->prepare($_query);\r\n'+
			'				$db_rs->execute();\r\n'+
			'			}\r\n'+
			'		}\r\n'+
			'		exit();\r\n'+
			'	}\r\n'+
			'}\r\n';
		
		script += 'if($_action == ("insert") || $_action == ("update")) \r\n{\r\n';
	
	
		
		for(i in insertUpdateFields)
		{
			filter = getSelectedFilterType(formData, insertUpdateFields[i]);
			script += '\t$r_'+insertUpdateFields[i]+' = filterInput(INPUT_POST, "'+insertUpdateFields[i]+'", '+filter+', true);\r\n';
		}
		
		if(args.withNote)
		{
			script += '\t$r_apv_note = filterInput(INPUT_POST, "apv_note", FILTER_SANITIZE_SPECIAL_CHARS, true);\r\n';
		}
		
		
		script += '}\r\n\r\n';
	
		
		
		// insert
		values = [];
		for(i in insertFields)
		{
			values[i] = '$r_'+insertFields[i];
		}
		script += '// [INSERT BEGIN]\r\n';
		script += '// TODO: Here are your code to insert data to database\r\n';
		script += 'if($_action == ("insert") && $_save != "") \r\n{\r\n';
		script += '\tif(false !== stripos($_permission, "insert"))\r\n\t{\r\n';
	
		script += '\t\t$_query = $query1->newQuery()\r\n'+
		'\t\t\t->insert()\r\n'+
		'\t\t\t->into("`'+currentTable+'`")\r\n'+
		'\t\t\t->fields("(`' + insertFields.join('`, `')+'`)")\r\n'+
		'\t\t\t->values("(\'\".' + values.join('."\', \'\".')+'."\')")\r\n'+
		'\t\t\t->toString();\r\n';
		script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
		script += '\t\t$db_rs->execute();\r\n\r\n';
	
		script += '\t\t$newID = $cms->getLastID();\r\n';
		
		////////////////////////////////////////////////
		/////////////// APPROVAL BEGIN /////////////////
		////////////////////////////////////////////////
		
		if(args.withApproval)
		{
			var insertFieldsApv = insertFields;
			var insertValuesApv = values;
			/*
			if(insertFieldsApv.indexOf(pkey) == -1)
			{
				insertFieldsApv.push(pkey);
				insertValuesApv.push('$_pkey_value');
			}
			*/
			if(args.withNote)
			{
				insertFieldsApv.push('note_create');
				insertValuesApv.push('$r_apv_note');
			}
	
			script += '\r\n';
			script += '\t\t// [NEED APPROVAL BEGIN]';
			script += '\r\n';
	
	
	
			if($.inArray(currentTable+'_id', insertFieldsApv) == -1)
			{
				insertFieldsApv.push(currentTable+'_id');
				insertValuesApv.push('$newID');
			}
	
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->insert()\r\n'+
			'\t\t\t->into("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t->fields("(`' + insertFieldsApv.join('`, `')+'`)")\r\n'+
			'\t\t\t->values("(\'\".' + insertValuesApv.join('."\', \'\".')+'."\')")\r\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n\r\n';
	
			
			script += '\t\t$newApvID = $cms->getLastID();\r\n';
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t->set("`admin_buat` = \'".$_userID."\', `waktu_buat` = ".$_now.", `ip_buat` = \'".$_remoteAddress."\', `status_data` = \'1\', `status_approve` = \'0\', `'+pkey+'` = \'".$newID."\'")\r\n'+
			'\t\t\t->where("`'+currentTable+'_apv_id` = \'".$newApvID."\'")\r\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n\r\n';
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t->set("`draft` = 1, `waiting_for` = 1, `'+currentTable+'_apv_id` = \'".$newApvID."\'")\r\n'+
			'\t\t\t->where("`'+pkey+'` = \'".$newID."\'")\t\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n\r\n';
	
			if(args.withNote)
			{
				script += '\t\t$_query = $query1->newQuery()\r\n'+
				'\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t->set("`note_create` = \'".$r_apv_note."\', ")\r\n'+
				'\t\t\t->where("`'+currentTable+'_apv_id` = \'".$newApvID."\'")\r\n'+
				'\t\t\t->toString();\r\n';
				script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
				script += '\t\t$db_rs->execute();\r\n\r\n';
			}
	
			script += '\t\t// [NEED APPROVAL END]';
			script += '\r\n';
			script += '\r\n';
		}
	
		////////////////////////////////////////////////
		///////////////  APPROVAL END  /////////////////
		////////////////////////////////////////////////
		
		script += '\t\theader("Location: ".$_self_name."?user_action=detail&'+pkey+'=".$newID);\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to insert new data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [INSERT END]\r\n';
	
		script += '\r\n\r\n';
	
		// update
		values = [];
		for(i in updateFields)
		{
			values[i] = 'r_'+updateFields[i];
		}
		script += '// [UPDATE BEGIN]\r\n';
		script += '// TODO: Here are your code to update data to database\r\n';
		script += 'if($_action == ("update") && $_save != "") \r\n{\r\n';
		script += '\tif(false !== stripos($_permission, "update"))\r\n\t{\r\n';
		script += '\t\t$_pkey_value = filterInput(INPUT_GET, "'+pkey+'", '+pkeyFilter+', true);\r\n';
	
		////////////////////////////////////////////////
		/////////////// APPROVAL BEGIN /////////////////
		////////////////////////////////////////////////
	
		if(args.withApproval)
		{
			var updateFieldsApv = updateFields;
			var updateValuesApv = values;
			if(updateFieldsApv.indexOf(pkey) == -1)
			{
				updateFieldsApv.push(pkey);
				updateValuesApv.push('_pkey_value');
			}
			
			script += '\r\n';
			script += '\t\t// [NEED APPROVAL BEGIN]';
			script += '\r\n';
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->insert()\r\n'+
			'\t\t\t->into("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t->fields("(`' + updateFieldsApv.join('`, `')+'`)")\r\n'+
			'\t\t\t->values("(\'".$' + updateValuesApv.join('."\', \'\".$')+'."\')")\r\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n\r\n';
					
			script += '\t\t$newApvIDForEdit = $cms->getLastID();\r\n';
			
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t->set("`waiting_for` = 2, `'+currentTable+'_apv_id` = \'".$newApvIDForEdit."\'")\r\n'+
			'\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n\r\n';
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t->set("`admin_buat_id` = \'".$_userID."\', `waktu_buat` = ".$_now.", `ip_buat` = \'".$_remoteAddress."\', `status_data` = \'0\', `status_approve` = \'0\', `'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t->where("`'+currentTable+'_apv_id` = \'".$newApvIDForEdit."\'")\r\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n';
			if(args.withNote)
			{
				script += '\t\t$_query = $query1->newQuery()\r\n'+
				'\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t->set("`note_create` = \'".$r_apv_note."\'")\r\n'+
				'\t\t\t->where("`'+currentTable+'_apv_id` = \'".$newApvIDForEdit."\'")\r\n'+
				'\t\t\t->toString();\r\n';
				script += '\t\t$database1->prepare($_query);\r\n';
				script += '\t\t$db_rs->execute();\r\n\r\n';
			}
			script += '\t\t// [NEED APPROVAL END]';
			script += '\r\n';
			script += '\r\n';
		}
		else
		{
			var ss = [];
			for(i in updateFields)
			{
				ss.push('`'+updateFields[i]+'` = \'".$r_'+updateFields[i]+'."\'');
			}
			script += '\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t->set("'+ss.join(', ')+'")\r\n'+
			'\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t->toString();\r\n';
			script += '\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t$db_rs->execute();\r\n\r\n';
		}
		
		////////////////////////////////////////////////
		///////////////  APPROVAL END  /////////////////
		////////////////////////////////////////////////
	
		script += '\t\theader("Location: ".$_self_name."?user_action=detail&'+pkey+'=".$_pkey_value);\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to update data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [UPDATE END]\r\n';
	
		script += '\r\n\r\n';
	
		// activate
		script += '// [ACTIVATE BEGIN]\r\n';
		script += '// TODO: Here are your code to activate selected data\r\n';
		script += '//       You can add filter here or modify the default code\r\n';
		script += 'if($_activate != "")  \r\n{\r\n';
		script += '\tif(false !== stripos($_permission, "update"))\r\n\t{\r\n';
		script += '\t\ttry\r\n\t\t{\r\n';
		script += '\t\t\t$activate_key_array = $cms->getRequestArray($_POST, $_pkey_name);\r\n';
		script += '\t\t\tfor($int_i = 0; $int_i < count($activate_key_array); $int_i++)\r\n'
		script += '\t\t\t{\r\n';
		if(args.withApproval)
		{
			script += '\t\t\t\t$sqlCommand = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t->set("'+currentTable+'_apv_id = -1, waiting_for = 3")\r\n'+
			'\t\t\t\t\t->where("'+pkey+' = \'".$cms->escapeSQL($activate_key_array[$int_i])."\' and '+currentTable+'_apv_id = 0")\r\n'+
			'\t\t\t\t\t->toString();\r\n';	
			script += '\t\t\t\t$db_rs = $database1->prepare($sqlCommand);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n';
		}
		else
		{		
			script += '\t\t\t\t$cms->setRecordValue("'+currentTable+'", "'+pkey+'", $activate_key_array[$int_i], $_na_name, "1");\r\n';
		}
		
		
		script += '\t\t\t}\r\n';
		script += '\t\t\theader("Location: ".$_self_name);\r\n';
		script += '\t\t}\r\n';
		script += '\t\tcatch(Exception $e)\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to update data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [ACTIVATE END]\r\n';
	
		script += '\r\n\r\n';
	
		// deactivate
		script += '// [DEACTIVATE BEGIN]\r\n';
		script += '// TODO: Here are your code to deactivate selected data\r\n';
		script += '//       You can add filter here or modify the default code\r\n';
		script += 'if($_deactivate != "") \r\n{\r\n';
		script += '\tif(false !== stripos($_permission, "update"))\r\n\t{\r\n';
		script += '\t\ttry\r\n\t\t{\r\n';
		script += '\t\t\t$deactivate_key_array = $cms->getRequestArray($_POST, $_pkey_name);\r\n';
		script += '\t\t\tfor($int_i = 0; $int_i < count($deactivate_key_array); $int_i++)\r\n'
		script += '\t\t\t{\r\n';
		if(args.withApproval)
		{
			script += '\t\t\t\t$sqlCommand = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t->set("'+currentTable+'_apv_id = -1, waiting_for = 4")\r\n'+
			'\t\t\t\t\t->where("'+pkey+' = \'".$cms->escapeSQL($deactivate_key_array[$int_i])."\' and '+currentTable+'_apv_id = 0")\r\n'+
			'\t\t\t\t\t->toString();\r\n';	
			script += '\t\t\t\t$db_rs = $database1->prepare($sqlCommand);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n';
		}
		else
		{		
			script += '\t\t\t\t$cms->setRecordValue("'+currentTable+'", "'+pkey+'", $deactivate_key_array[$int_i], $_na_name, "0");\r\n';
		}
		script += '\t\t\t}\r\n';
		script += '\t\t\theader("Location: ".$_self_name);\r\n';
		script += '\t\t}\r\n';
		script += '\t\tcatch(Exception $e)\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to update data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [DEACTIVATE END]\r\n';
	
		script += '\r\n\r\n';
	
		// delete
		script += '// [DELETE BEGIN]\r\n';
		script += '// TODO: Here are your code to deactivate selected data\r\n';
		script += '//       You can add filter here or modify the default code\r\n';
		script += 'if($_delete != "") \r\n{\r\n';
		script += '\tif(false !== stripos($_permission, "delete"))\r\n\t{\r\n';
		script += '\t\ttry\r\n\t\t{\r\n';
		script += '\t\t\t$delete_key_array = $cms->getRequestArray($_POST, $_pkey_name);\r\n';
		script += '\t\t\tfor($int_i = 0; $int_i < count($delete_key_array); $int_i++)\r\n'
		script += '\t\t\t{\r\n';
		
		if(args.withApproval)
		{
			script += '\t\t\t\t$sqlCommand = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("'+currentTable+'")\r\n'+
			'\t\t\t\t\t->set("'+currentTable+'_apv_id = -1, waiting_for = 5")\r\n'+
			'\t\t\t\t\t->where("'+pkey+' = \'".$cms->escapeSQL($delete_key_array[$int_i])."\' and '+currentTable+'_apv_id = 0")\r\n'+
			'\t\t\t\t\t->toString();\r\n';	
			script += '\t\t\t\t$db_rs = $database1->prepare($sqlCommand);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n';
		}
		else
		{
			script += '\t\t\t\t$cms->deleteRecord("'+currentTable+'", "'+pkey+'", $delete_key_array[$int_i]);\r\n';
			if(args.withTrash)
			{
				script += '\t\t\t\t$cms->updateTrashInfo("'+currentTable+'_trash", "'+pkey+'", $delete_key_array[$int_i], "'+currentTable+'_trash_id", $_userID, $_remoteAddress);\r\n';
			}
		}
		
		script += '\t\t\t}\r\n';
		script += '\t\t\theader("Location: ".$_self_name);\r\n';
		script += '\t\t}\r\n';
		script += '\t\tcatch(Exception $e)\r\n';
		script += '\t\t{\r\n';
		script += '\t\t}\r\n';
		script += '\t}\r\n';
		
		script += '\telse\r\n\t{\r\n\t\t// TODO: add your code here when user does not have permission to delete data\r\n\r\n\t}\r\n';
		
		script += '}\r\n';
		script += '// [DELETE END]\r\n';
		
		
		if(args.withApproval)
		{
			var columList = updateFields.join(",");	
			script += '\r\n';
			script += '\r\n';
			script += '// [APPROVE BEGIN]\r\n'+
			'if(filterInput(INPUT_POST, "data_approve", FILTER_SANITIZE_STRING, true) != "")\r\n'+
			'{\r\n'+
			'\t// TODO: Here are your code for approve\r\n'+
			'\tif(false !== stripos($_permission, "approve"))\r\n'+
			'\t{\r\n'+
			'\t\t$_pkey_value = filterInput(INPUT_POST, "'+pkey+'", '+pkeyFilter+', true);\r\n';
			if(args.withNote)
			{
				script += '\t\t$r_apv_note = filterInput(INPUT_POST, "apv_note", FILTER_SANITIZE_SPECIAL_CHARS, true);\r\n';
			}
			script += '\t\t$_apv_values = $cms->getValues("'+currentTable+'", "'+pkey+'", $_pkey_value, "'+currentTable+'_apv_id as apv_id, waiting_for");\r\n'+
			'\t\tif(@$_apv_values["apv_id"] != "0")\r\n'+
			'\t\t{\r\n';
			script += '\t\t\t$_apv_id = $_apv_values["apv_id"];\r\n';
			script += '\t\t\tif(@$_apv_values["waiting_for"] == "1")\r\n'+
			'\t\t\t{\r\n';

			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t->set("`'+currentTable+'_apv_id` = 0, `draft` = 0, `waiting_for` = 0")\r\n'+
			'\t\t\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			
			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t->set("`status_approve` = \'1\', `admin_approve_id` = \'".$_userID."\', `ip_address_approve` = \'".$_remoteAddress."\', `time_approve` = ".$_now)\r\n'+
			'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			
			if(args.withNote)
			{
				script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
				'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t->set("`note_approve` = \'".$r_apv_note."\'")\r\n'+
				'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
				'\t\t\t\t\t->toString();\r\n';
				script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
				script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			}
			
			script += '\t\t\t}\r\n';
			script += '\t\t\telse if(@$_apv_values["waiting_for"] == "2")\r\n'+
			'\t\t\t{\r\n';
			script += '\t\t\t\t$cms->copyData("'+currentTable+'_apv", "'+currentTable+'", "'+columList+'", "'+currentTable+'_apv_id", $_apv_id, "'+pkey+'", $_pkey_value);\r\n';
	
			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t->set("`status_approve` = \'1\', `admin_approve_id` = \'".$_userID."\', `ip_address_approve` = \'".$_remoteAddress."\', `time_approve` = ".$_now)\r\n'+
			'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			if(args.withNote)
			{
				script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
				'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t->set("`note_approve` = \'".$r_apv_note."\'")\r\n'+
				'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
				'\t\t\t\t\t->toString();\r\n';
				script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
				script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			}
			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t->set("`'+currentTable+'_apv_id` = 0, `draft` = 0, `waiting_for` = 0")\r\n'+
			'\t\t\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
	
			script += '\t\t\t}\r\n';
			
			script += '\t\t\telse if(@$_apv_values["waiting_for"] == "3")\r\n'+
			'\t\t\t{\r\n';
			
			// activate
			script += '\t\t\t\t$_sqlCommand = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t->set("`aktif` = 1, `'+currentTable+'_apv_id` = 0, `waiting_for` = 0 ")\r\n'+
			'\t\t\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_sqlCommand);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n';
			
			script += '\t\t\t}\r\n';
			script += '\t\t\telse if(@$_apv_values["waiting_for"] == "4")\r\n'+
			'\t\t\t{\r\n';
			// deactivate
			script += '\t\t\t\t$_sqlCommand = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t->set("`aktif` = 0, `'+currentTable+'_apv_id` = 0, `waiting_for` = 0 ")\r\n'+
			'\t\t\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_sqlCommand);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n';
			script += '\t\t\t}\r\n';
			script += '\t\t\telse if(@$_apv_values["waiting_for"] == "5")\r\n'+
			'\t\t\t{\r\n';
			// delete
			script += '\t\t\t\t$_sqlCommand = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t->set("`'+currentTable+'_apv_id` = 0, `waiting_for` = 0 ")\r\n'+
			'\t\t\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_sqlCommand);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n';
			script += '\t\t\t\t$cms->deleteRecord("'+currentTable+'", "'+pkey+'", $_pkey_value);\r\n';
			
			if(args.withTrash)
			{
				script += '\t\t\t\t$cms->updateTrashInfo("'+currentTable+'_trash", "'+pkey+'", $_pkey_value, "'+currentTable+'_trash_id", $_userID, $_remoteAddress);\r\n';
			}
			script += '\t\t\t}\r\n';
			
			script += '\t\t}\r\n'+
			'\t\theader("Location: ".$_self_name."?show-only=need-approval");\r\n'+
			'\t}\r\n'+
			'}\r\n'+
			'// [APPROVE END]\r\n'+
			'\r\n'+
			'\r\n'+
			'// [REJECT BEGIN]\r\n'+
			'if(filterInput(INPUT_POST, "data_reject", FILTER_SANITIZE_STRING, true) != "")\r\n'+
			'{\r\n'+
			'\t// TODO: Here are your code for approve\r\n'+
			'\tif(false !== stripos($_permission, "approve"))\r\n'+
			'\t{\r\n'+
			'\t\t$_pkey_value = filterInput(INPUT_POST, "'+pkey+'", "'+pkeyFilter+'", true);\r\n';
			if(args.withNote)
			{
				script += '\t\t$r_apv_note = filterInput(INPUT_POST, "apv_note", FILTER_SANITIZE_SPECIAL_CHARS, true);\r\n';
			}
			script += '\t\t$_apv_id = $cms->getValue("'+currentTable+'", "'+pkey+'", $_pkey_value, "'+currentTable+'_apv_id");\r\n';
			script += '\t\tif($_apv_id != "0")\r\n';
			script += '\t\t{\r\n';
			script += '\t\t\t$_status_data = $cms->getValue("'+currentTable+'_apv", "'+currentTable+'_apv_id", $_apv_id, "status_data");\r\n';
			script += '\t\t\tif($_status_data == "1")\r\n';
			script += '\t\t\t{\r\n';
			script += '\t\t\t\t// delete source data\r\n';
	
			script += '\t\t\t\t$cms->deleteRecord("'+currentTable+'", "'+pkey+'", $_pkey_value);\r\n';
			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t->set("`status_approve` = \'-1\', `admin_approve_id` = \'".$_userID."\', `ip_address_approve` = \'".$_remoteAddress."\', `time_approve` = ".$_now)\r\n'+
			'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			
			if(args.withNote)
			{
				script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
				'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t->set("`note_approve` = \'".$r_apv_note."\'")\r\n'+
				'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
				'\t\t\t\t\t->toString();\r\n';
				script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
				script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			}
			script += '\t\t\t}\r\n';
			script += '\t\t\telse\r\n';
			script += '\t\t\t{\r\n';
			script += '\t\t\t\t$cms->setRecordValue("'+currentTable+'_apv", "'+currentTable+'_apv_id", $_apv_id, "status_approve", "-1");\r\n';
			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
			'\t\t\t\t\t->set("`status_approve` = \'-1\', `admin_approve_id` = \'".$_userID."\', `ip_address_approve` = \'".$_remoteAddress."\', `time_approve` = ".$_now)\r\n'+
			'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			if(args.withNote)
			{
				script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
				'\t\t\t\t\t->update("`'+currentTable+'_apv`")\r\n'+
				'\t\t\t\t\t->set("`note_approve` = \'".$r_apv_note."\'")\r\n'+
				'\t\t\t\t\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
				'\t\t\t\t\t->toString();\r\n';
				script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
				script += '\t\t\t\t$db_rs->execute();\r\n\r\n';
			}
			script += '\t\t\t\t$_query = $query1->newQuery()\r\n'+
			'\t\t\t\t\t->update("`'+currentTable+'`")\r\n'+
			'\t\t\t\t\t->set("`'+currentTable+'_apv_id` = 0, `draft` = 0, `waiting_for` = 0")\r\n'+
			'\t\t\t\t\t->where("`'+pkey+'` = \'".$_pkey_value."\'")\r\n'+
			'\t\t\t\t\t->toString();\r\n';
			script += '\t\t\t\t$db_rs = $database1->prepare($_query);\r\n';
			script += '\t\t\t\t$db_rs->execute();\r\n\r\n';

			script += '\t\t\t}\r\n';
			script += '\t\t}\r\n';
			script += '\t\theader("Location: ".$_self_name."?show-only=need-approval");\r\n';
			script += '\t}\r\n';
			script += '}\r\n';
			script += '// [REJECT END]\r\n';
			script += '\r\n';	
		}
		
		script += '?>';
		
		script = script.replaceAll('\'"+r_waktu_buat+"\'', '"+r_waktu_buat+"');
		script = script.replaceAll('\'"+r_waktu_ubah+"\'', '"+r_waktu_ubah+"');
		
		script = script.replaceAll('\'".$r_waktu_buat."\'', '".$r_waktu_buat."');
		script = script.replaceAll('\'".$r_waktu_ubah."\'', '".$r_waktu_ubah."');
		
		
		script = script.replaceAll('r_waktu_buat = ', 'r_waktu_buat = "now()"; //');
		script = script.replaceAll('r_waktu_ubah = ', 'r_waktu_ubah = "now()"; //');
		script = script.replaceAll('r_admin_buat = ', 'r_admin_buat = $_userID; //');
		script = script.replaceAll('r_admin_ubah = ', 'r_admin_ubah = $_userID; //');
		script = script.replaceAll('r_ip_buat = ', 'r_ip_buat = $_remoteAddress; //');
		script = script.replaceAll('r_ip_ubah = ', 'r_ip_ubah = $_remoteAddress; //');
	}
	return script;
}

function generateInsertUI(formData, args, language)
{
	language = language || 'php';
	args = args || {};
	args.withApproval = args.withApproval || false;
	args.withTrash = args.withTrash || false;
	args.withNote = args.withNote || false;

	var insertFields = getInsertFields(formData);
	var i, j, k, inputEl, caption;
	var script = '';
	var field = '';
	var currentTable = getFieldValues(formData, 'table')[0];
	var pk = getPrimaryKey(formData);
	var pkey = (pk.length)?pk[0]:insertFields[0];
	
	if(language == 'jsp')
	{
		script += '<%\r\n';
		script += 'if(_action.equals("insert"))\r\n{\r\n';
		script += '// TODO: Here are your code for insert UI\r\n';
		script += 'if(hasPermission(_userLevel, _module_name, "insert"))\r\n{\r\n';	
		script += '\r\n%><%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		script += '<form name="insertform" id="insertform" action="" method="post">\r\n';
		script += '<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		
		for(i in insertFields)
		{
			caption = getFieldValues(formData, 'caption_'+insertFields[i]);
			inputEl = generateInputForInsert(formData, insertFields[i], language);
			script += '\t<tr>\r\n\t\t<td>'+caption+'</td>\r\n\t\t<td>'+inputEl+'</td>\r\n\t</tr>\r\n';
		}
		
		if(args.withNote)
		{
			script += '\t<tr>\r\n\t\t<td>Catatan</td>\r\n\t\t<td><textarea id="apv_note" name="apv_note" class="form-control"></textarea></td>\r\n\t</tr>\r\n';
		}
		
		script += '</table>\r\n';
		
		script += '<table class="responsive responsive-two-cols responsive-button-area" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		script += '\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td>\r\n';
		script += '\t\t\t<input type="submit" class="btn btn-success" id="save" name="button_save" value="Simpan">\r\n';
		script += '\t\t\t<input type="button" class="btn btn-primary" id="showall" value="Tampilkan Semua" onclick="window.location=\'<%= _self_name %>\'">\r\n';
		script += '\t\t</td>\r\n\t</tr>\r\n';
		script += '</table>\r\n';
	
		script += '</form>\r\n';
		
		
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		// end if has permission
		script += '<%\r\n}\r\n';
		
		script += 'else\r\n{\r\n%>';
		script += '<%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><%= error_message_permission %></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		// end else has permission
		script += '<%\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '%>';
	}
	else if(language == 'php')
	{
		script += '<?php\r\n';
		script += 'if($_action == ("insert"))\r\n{\r\n';
		script += '// TODO: Here are your code for insert UI\r\n';
		script += 'if(false !== stripos($_permission, "insert"))\r\n{\r\n';	
		script += 'include_once dirname(__FILE__)."/lib.inc/header.php";\r\n';
		script += 'include_once dirname(__FILE__)."/lib.inc/form-default-data.php";\r\n'; 
		script += 'getDefaultData($database1, $_table_name, array("aktif"));\r\n';
		script += '?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		script += '<form name="insertform" id="insertform" action="" method="post">\r\n';
		script += '<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		
		for(i in insertFields)
		{
			caption = getFieldValues(formData, 'caption_'+insertFields[i]);
			inputEl = generateInputForInsert(formData, insertFields[i], language);
			script += '\t<tr>\r\n\t\t<td>'+caption+'</td>\r\n\t\t<td>'+inputEl+'</td>\r\n\t</tr>\r\n';
		}
		
		if(args.withNote)
		{
			script += '\t<tr>\r\n\t\t<td>Catatan</td>\r\n\t\t<td><textarea id="apv_note" name="apv_note" class="form-control"></textarea></td>\r\n\t</tr>\r\n';
		}
		
		script += '</table>\r\n';
		
		script += '<table class="responsive responsive-two-cols responsive-button-area" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		script += '\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td>\r\n';
		script += '\t\t\t<input type="submit" class="btn btn-success" id="save" name="button_save" value="Simpan">\r\n';
		script += '\t\t\t<input type="button" class="btn btn-primary" id="showall" value="Tampilkan Semua" onclick="window.location=\'<?php echo $_self_name; ?>\'">\r\n';
		script += '\t\t</td>\r\n\t</tr>\r\n';
		script += '</table>\r\n';
	
		script += '</form>\r\n';
		
		
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		// end if has permission
		script += '<?php\r\n}\r\n';
		
		script += 'else\r\n{\r\n?>';
		script += '<?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><?php echo $error_message_permission ?></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		// end else has permission
		script += '<?php\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '';
	}
	return script;
}
function generateUpdateUI(formData, args, language)
{
	language = language || 'php';
	args = args || {};
	args.withApproval = args.withApproval || false;
	args.withTrash = args.withTrash || false;
	args.withNote = args.withNote || false;

	var updateFields = getUpdateFields(formData);
	var i, j, k, inputEl, caption;
	var script = '';
	var field = '';
	var currentTable = getFieldValues(formData, 'table')[0];
	var pk = getPrimaryKey(formData);
	var pkey = (pk.length)?pk[0]:updateFields[0];
	var pkeyFilter = getSelectedFilterType(formData, pkey);
	
	if(language == 'jsp')
	{
		script += '<%\r\nelse if(_action.equals("update"))\r\n{\r\n';
		script += '// TODO: Here are your code for update UI\r\n';
		script += 'if(hasPermission(_userLevel, _module_name, "update"))\r\n{\r\n';	
		script += 'String _id = filterInput(request, "'+pkey+'", "'+pkeyFilter+'", true);\r\n';
		script += 'JSONObject _result = null;\r\n\r\n';
		
		for(i in updateFields)
		{
			field = updateFields[i];
			script += 'String d_'+field+' = "";\r\n';
		}
		script += 'int _apv_id = 0;\r\n';
		script += 'int _waiting_for = 0;\r\n';
	
		script += '\r\n_query = "select `'+currentTable+'`.* from `'+currentTable+'` where `'+pkey+'` = \'"+_id+"\'";\r\n';
		script += '\r\n_query = query1.newQuery()\r\n'+
		'\t.select("`'+currentTable+'`.*")\r\n'+
		'\t.from("`'+currentTable+'`")\r\n'+
		'\t.where("`'+pkey+'` = \'"+_id+"\'")\r\n'+
		'\t.toString();\r\n';
		script += '\r\ntry\r\n{\r\n';
		script += 'db_rs = database1.executeQuery(_query);\r\n\r\n';
		script += 'if(db_rs.isBeforeFirst())\r\n{\r\n';
		script += 'db_rs.next();\r\n';
		for(i in updateFields)
		{
			field = updateFields[i];
			script += 'd_'+field+' = db_rs.getString("'+field+'");\r\n';
		}
		script += '_apv_id = db_rs.getInt("'+currentTable+'_apv_id");\r\n';
		script += '_waiting_for = db_rs.getInt("waiting_for");\r\n';
		
		script += '\r\n%><%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		
		if(args.withApproval)
		{
			script += '<%\r\n';
			script += 'if(_waiting_for != 0)\r\n';
			script += '{\r\n';
			script += '%>\r\n';
			script += '\t<div class="message-warning-require-approval"><%= approval_waiting_message.get("message"+_waiting_for).toString() %></div>\r\n';
			script += '<%\r\n';
			script += '}\r\n';
			script += 'else\r\n';
			script += '{\r\n';
			script += '%>\r\n';
		}
		
		script += '<form name="editform" id="editform" action="" method="post">\r\n';
		script += '<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		
		for(i in updateFields)
		{
			caption = getFieldValues(formData, 'caption_'+updateFields[i]);
			inputEl = generateInputForUpdate(formData, updateFields[i], language);
			script += '\t<tr>\r\n\t\t<td>'+caption+'</td>\r\n\t\t<td>'+inputEl+'</td>\r\n\t</tr>\r\n';
		}
		if(args.withNote)
		{
			script += '\t<tr>\r\n\t\t<td>Catatan</td>\r\n\t\t<td><textarea id="apv_note" name="apv_note" class="form-control"></textarea></td>\r\n\t</tr>\r\n';
		}
		script += '</table>\r\n';
		
		script += '<table class="responsive responsive-two-cols responsive-button-area" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		script += '\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td>\r\n';
		script += '\t\t\t<input type="submit" class="btn btn-success" id="save" name="button_save" value="Simpan">\r\n';
		script += '\t\t\t<input type="button" class="btn btn-primary" id="showall" value="Tampilkan Semua" onclick="window.location=\'<%= _self_name %>\'">\r\n';
		script += '\t\t</td>\r\n\t</tr>\r\n';
		script += '</table>\r\n';
	
		script += '</form>\r\n';
		if(args.withApproval)
		{
			script += '<%\r\n';
			script += '}\r\n';
			script += '%>\r\n';
		}
	
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		script += '<%\r\n}\r\nelse\r\n{\r\n';
		
		script += '%>\r\n<%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\t<div class="alert alert-not-found"><%= pagination_data_not_found %></div>\r\n';
		script += templateAfterContent(formData);
		script += '<%@ include file="lib.inc/footer.jsp" %>';
		script += '\r\n<%';
	
		script += '\r\n}\r\n%>';
		// end if has permission
		script += '<%\r\n}\r\ncatch(Exception e)\r\n{\r\n';
		script += '// Catch\r\n';
		script += '}\r\nfinally\r\n{\r\n';
		script += '// Finnaly\r\n';
		script += '}\r\n%>\r\n';
		script += '<%\r\n}\r\n';
		
		script += 'else\r\n{\r\n%>';
		script += '<%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><%= error_message_permission %></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		// end else has permission
		script += '<%\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '%>';
	}
	else if(language == 'php')
	{
		script += '\r\nelse if($_action == ("update"))\r\n{\r\n';
		script += '// TODO: Here are your code for update UI\r\n';
		script += 'if(false !== stripos($_permission, "update"))\r\n{\r\n';	
		script += '$_id = filterInput(INPUT_GET, "'+pkey+'", '+pkeyFilter+', true);\r\n';
		script += '$_result = null;\r\n\r\n';
		
		for(i in updateFields)
		{
			field = updateFields[i];
			script += '$d_'+field+' = "";\r\n';
		}
	
		script += '\r\n$_query = "select `'+currentTable+'`.* from `'+currentTable+'` where `'+pkey+'` = \'".$_id."\'";\r\n';
		script += '\r\n$_query = $query1->newQuery()\r\n'+
		'\t->select("`'+currentTable+'`.*")\r\n'+
		'\t->from("`'+currentTable+'`")\r\n'+
		'\t->where("`'+pkey+'` = \'".$_id."\'")\r\n'+
		'\t->toString();\r\n';
		script += '\r\ntry\r\n{\r\n';
		script += '$db_rs = $database1->prepare($_query);\r\n';
		script += '$db_rs->execute();\r\n\r\n';
		script += 'if($db_rs->rowCount())\r\n{\r\n';
		script += '$db_data = $db_rs->fetch(PDO::FETCH_ASSOC);\r\n';
		for(i in updateFields)
		{
			field = updateFields[i];
			script += '$d_'+field+' = $db_data["'+field+'"];\r\n';
		}
		script += '$d_apv_id = $db_data["'+currentTable+'_apv_id"];\r\n';
		script += '$d_waiting_for = $db_data["waiting_for"];\r\n';
		
		script += '\r\n?><?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		
		if(args.withApproval)
		{
			script += '<?php\r\n';
			script += 'if($d_apv_id != 0)\r\n';
			script += '{\r\n';
			script += '?>\r\n';
			script += '\t<div class="message-warning-require-approval"><?php echo $approval_waiting_message[$d_waiting_for];?></div>\r\n';
			script += '<?php\r\n';
			script += '}\r\n';
			script += 'else\r\n';
			script += '{\r\n';
			script += '?>\r\n';
		}
		
		script += '<form name="editform" id="editform" action="" method="post">\r\n';
		script += '<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		
		for(i in updateFields)
		{
			caption = getFieldValues(formData, 'caption_'+updateFields[i]);
			inputEl = generateInputForUpdate(formData, updateFields[i], language);
			script += '\t<tr>\r\n\t\t<td>'+caption+'</td>\r\n\t\t<td>'+inputEl+'</td>\r\n\t</tr>\r\n';
		}
		if(args.withNote)
		{
			script += '\t<tr>\r\n\t\t<td>Catatan</td>\r\n\t\t<td><textarea id="apv_note" name="apv_note" class="form-control"></textarea></td>\r\n\t</tr>\r\n';
		}
		script += '</table>\r\n';
		
		script += '<table class="responsive responsive-two-cols responsive-button-area" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		script += '\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td>\r\n';
		script += '\t\t\t<input type="submit" class="btn btn-success" id="save" name="button_save" value="Simpan">\r\n';
		script += '\t\t\t<input type="button" class="btn btn-primary" id="showall" value="Tampilkan Semua" onclick="window.location=\'<?php echo $_self_name; ?>\'">\r\n';
		script += '\t\t</td>\r\n\t</tr>\r\n';
		script += '</table>\r\n';
	
		script += '</form>\r\n';
		if(args.withApproval)
		{
			script += '<?php\r\n';
			script += '}\r\n';
			script += '?>\r\n';
		}
	
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		script += '<?php\r\n}\r\nelse\r\n{\r\n';
		
		script += '?>\r\n<?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\t<div class="alert alert-not-found"><?php echo $pagination_data_not_found; ?></div>\r\n';
		script += templateAfterContent(formData);
		script += '<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		script += '\r\n<?php';
	
		script += '\r\n}\r\n?>';
		// end if has permission
		script += '<?php\r\n}\r\ncatch(Exception $e)\r\n{\r\n';
		script += '// Catch\r\n';
		script += '}\r\n?>\r\n';
		script += '<?php\r\n}\r\n';
		
		script += 'else\r\n{\r\n?>';
		script += '<?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><?php echo $error_message_permission; ?></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		// end else has permission
		script += '<?php\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '';
	}
	return script;
}
function generateDetailUI(formData, args, language)
{
	language = language || 'php';
	args = args || {};
	args.withApproval = args.withApproval || false;
	args.withTrash = args.withTrash || false;
	args.withNote = args.withNote || false;

	var detailFields = getDetailFields(formData);
	var i, j, k, inputEl, caption, subselectArray = [];
	var elementType = '', subselectStr = '', table = '';
	var script = '';
	var field = '';
	var currentTable = getFieldValues(formData, 'table')[0];
	var pk = getPrimaryKey(formData);
	var pkey = (pk.length)?pk[0]:detailFields[0];
	var pkeyFilter = getSelectedFilterType(formData, pkey);
	var cellVal = "";
	var cellVal2 = "";
	var cellType = "";

	var subselectArrayApv = [];
	var tableFieldsArrayApv = [];
	var tableFieldsApv = "",
	subselectStrApv = "",
	currentTableApv = "",
	pkeyApv = "";

	if(language == 'jsp')
	{

		script += '<%\r\nelse if(_action.equals("detail"))\r\n{\r\n';
		script += '// TODO: Here are your code for detail UI\r\n';
		script += 'String _queryApv = "";\r\n';
		script += 'String _next = filterInput(request, "next", "FILTER_SANITIZE_STRING", true);\r\n';
		script += 'if(hasPermission(_userLevel, _module_name, "detail"))\r\n{\r\n';	
		script += 'String _id = filterInput(request, "'+pkey+'", "'+pkeyFilter+'", true);\r\n';
		script += 'JSONObject _result = null;\r\n\r\n';
		
		var tableFieldsArray = [];
		var tableFields = '';
		
		if(args.withNote)
		{
			script += 'String d_note_create = "";\r\n';
			script += 'String d_note_approve = "";\r\n';
		}
		if(args.withApproval)
		{
			script += 'int d_apv_id = 0;\r\n';
			script += 'int d_waiting_for = 0;\r\n';
			tableFieldsArray.push('`'+currentTable+'`.`'+currentTable+'_apv_id`');
			tableFieldsArray.push('`'+currentTable+'`.`waiting_for`');
		}
		for(i in detailFields)
		{
			field = detailFields[i];
			script += 'String d_'+field+' = "";\r\n';
			
			elementType = getSelectedElementType(formData, field);
			if(elementType == 'select')
			{
				table = field;
				if(table.length > 3)
				{
					table = table.substr(0, table.length-3);
				}
				subselectStr = '(select `'+table+'`.`nama` from `'+table+'` where `'+table+'`.`'+field+'` = `'+currentTable+'`.`'+field+'`) as `'+field+'`';
				subselectArray.push(subselectStr);
				subselectStr = '(select `'+table+'`.`nama` from `'+table+'` where `'+table+'`.`'+field+'` = `'+currentTable+'_apv`.`'+field+'`) as `'+field+'`';
				subselectArrayApv.push(subselectStr);
			}
			else
			{
				tableFieldsArray.push('`'+currentTable+'`.`'+field+'`');
				tableFieldsArrayApv.push('`'+currentTable+'_apv`.`'+field+'`');
			}
		}
		if(args.withNote)
		{
			tableFieldsArrayApv.push('coalesce(`'+currentTable+'_apv`.`note_create`, \'\') as `note_create`');
		}
		if(subselectArray.length)
		{
			subselectStr = ', "+\r\n"'+subselectArray.join(', "+\r\n"')+' "+\r\n"'
		}
		else
		{
			subselectStr = ' '; 
		}
		if(subselectArrayApv.length)
		{
			subselectStrApv = ', "+\r\n"'+subselectArrayApv.join(', "+\r\n"')+' "+\r\n"'
		}
		else
		{
			subselectStrApv = ' '; 
		}
		tableFields = tableFieldsArray.join(', ');
		tableFieldsApv = tableFieldsArrayApv.join(', ');
		
		script += '\r\n';
		script += '_query = query1.newQuery()\r\n'+
		'\t.select("'+tableFields+''+subselectStr+'")\r\n'+
		'\t.from("`'+currentTable+'`")\r\n'+
		'\t.where("`'+pkey+'` = \'"+_id+"\'")\r\n'+
		'\t.toString();\r\n';
		
		script += '\r\ntry\r\n{\r\n';
		script += '\r\ndb_rs = database1.executeQuery(_query);\r\n\r\n';
		script += 'if(db_rs.isBeforeFirst())\r\n{\r\n';
		script += 'db_rs.next();\r\n';
		
		if(args.withApproval)
		{
			script += 'd_apv_id = db_rs.getInt("'+currentTable+'_apv_id");\r\n';
			script += 'd_waiting_for = db_rs.getInt("waiting_for");\r\n';
		}
		for(i in detailFields)
		{
			field = detailFields[i];
			script += 'd_'+field+' = db_rs.getString("'+field+'");\r\n';
		}
		
		script += '\r\n%><%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		script += '<form name="detailform" id="detailform" action="" method="post">\r\n';
		
		
		if(args.withApproval)
		{
			script += '<%\r\n';
			script += 'ResultSet _apvData = null;\r\n';
			script += 'boolean _needApproval = false;\r\n';
			script += 'if(d_apv_id > 0)\r\n';
			script += '{\r\n';
			script += '_queryApv = query1.newQuery()\r\n'+
			'\t.select("'+tableFieldsApv+''+subselectStrApv+'")\r\n'+
			'\t.from("`'+currentTable+'_apv`")\r\n'+
			'\t.where("`'+currentTable+'_apv_id` = \'"+d_apv_id+"\'")\r\n'+
			'\t.toString();\r\n';
			script += '_apvData = cms.getRecord(_queryApv);\r\n';
			script += 'if(_apvData != null)\r\n';
			script += '{\r\n';
			script += '\t_needApproval = true;\r\n';
			script += '}\r\n';
			script += '}\r\n';
		
			script += '\r\n';
		
			script += 'if(d_apv_id > 0 && !_next.equals(""))\r\n';
			script += '{\r\n';
			script += '%>\r\n';
		
			script += '<script type="text/javascript">\r\n';
			script += '\t$(document).ready(function(){\r\n';
			script += '\tcompareData(\'.responsive-three-cols\');\r\n';
			script += '});\r\n';
			script += '<'+'/script'+'>\r\n';
		
			script += '<table class="responsive responsive-three-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		
			script += '\t<tr class="row-label-three-cols">\r\n';
			script += '\t\t<td><span>Label</span></td>\r\n';
			script += '\t\t<td><span>Current Data</span></td>\r\n';
			script += '\t\t<td><span>Draft</span></td>\r\n';
			script += '\t</tr>\r\n';
			
			for(i in detailFields)
			{
				caption = getFieldValues(formData, 'caption_'+detailFields[i]);
				cellType = getSelectedElementType(formData, detailFields[i]);
				if(cellType == 'checkbox')
				{
					cellVal = '(d_'+detailFields[i]+' == null)?"Tidak":((d_'+detailFields[i]+'.equals("1"))?"Ya":"Tidak")';
					cellVal2 = '(_apvData.getString("'+detailFields[i]+'") == null)?"Tidak":((_apvData.getString("'+detailFields[i]+'").equals("1"))?"Ya":"Tidak")';
				}
				else
				{
					cellVal = 'd_'+detailFields[i];
					cellVal2 = '_apvData.getString("'+detailFields[i]+'")';
				}
				script += '\t<tr>\r\n';
				script += '\t\t<td><span>'+caption+'</span></td>\r\n';
				script += '\t\t<td><span class="compare"><%= '+cellVal+' %></span></td>\r\n';
				script += '\t\t<td><span class="compare"><%= '+cellVal2+' %></span></td>\r\n';
				script += '\t</tr>\r\n';
			}
			if(args.withNote)
			{
				script += '\t<%\r\n'+
				'\td_note_create = _apvData.getString("note_create");\r\n'+
				'\t%>\r\n'+
				'\t<tr>\r\n\t\t<td>Catatan</td>\r\n\t\t<td><textarea id="apv_note" name="apv_note" class="form-control ta-min-10" placeholder="Approval note here..."></textarea></td>\r\n\t\t<td><textarea class="form-control ta-min-10" readonly="readonly"><%= escapeHTML(d_note_create) %></textarea></td>\r\n\t</tr>\r\n';
			}
			script += '</table>\r\n';
		
			script += '<%\r\n';
			script += '}\r\n';
			script += 'else\r\n';
			script += '{\r\n';
			script += '%>\r\n';
		}
		script += '<%\r\n';
		script += 'if(d_waiting_for > 0 && !_next.equals(""))\r\n';
		script += '{\r\n';
		script += '%>\r\n';
		script += '\t<div class="message-warning-require-approval"><%= approval_waiting_message.get("message"+d_waiting_for).toString() %></div>\r\n';		
		script += '<%\r\n';
		script += '}\r\n';
		script += '%>\r\n';
		script += '<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		for(i in detailFields)
		{
			caption = getFieldValues(formData, 'caption_'+detailFields[i]);
			cellType = getSelectedElementType(formData, detailFields[i]);
			if(cellType == 'checkbox')
			{
				cellVal = '(d_'+detailFields[i]+' == null)?"Tidak":((d_'+detailFields[i]+'.equals("1"))?"Ya":"Tidak")';
			}
			else
			{
				cellVal = 'd_'+detailFields[i];
			}
			script += '\t<tr>\r\n';
			script += '\t\t<td>'+caption+'</td>\r\n';
			script += '\t\t<td><%= '+cellVal+' %></td>\r\n';
			script += '\t</tr>\r\n';
		}
		script += '</table>\r\n';
	
		if(args.withApproval)
		{
			script += '<%\r\n';
			script += '}\r\n';
			script += '%>\r\n';
		}
		
		script += '<table class="responsive responsive-two-cols responsive-button-area" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		script += '\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td>\r\n';
		
		if(args.withApproval)
		{
			script += '<%\r\n';
			script += 'if(d_waiting_for != 0 && !_next.equals("") && hasPermission(_userLevel, _module_name, "approve"))\r\n';
			script += '{\r\n';
			
			script += '\tif(!_next.equals(""))\r\n'+
			'\t{\r\n'+
			'\t\tif(!_next.equals("reject"))\r\n'+
			'\t\t{\r\n'+
			'%>\r\n';
	
			script += '\t\t\t<input type="button" class="btn btn-success" id="approve" name="button_approve" value="Approve" onclick="approveData(\'<%= _self_name %>\', \''+pkey+'\', \'<%= _id %>\', $(\'#apv_note\').val())">\r\n';
			
			script += '<%\r\n'+
			'\t\t}\r\n';
			script += '\t\tif(!_next.equals("approve"))\r\n'+
			'\t\t{\r\n'+
			'%>\r\n';
			
			script += '\t\t\t<input type="button" class="btn btn-danger" id="reject" name="button_reject" value="Reject" onclick="rejectData(\'<%= _self_name %>\', \''+pkey+'\', \'<%= _id %>\', $(\'#apv_note\').val())">\r\n';
			script += '<%\r\n'+
			'\t\t}\r\n'+
			'\t}\r\n';
			
			script += '}\r\n';
			script += 'else if(hasPermission(_userLevel, _module_name, "update"))\r\n';
			script += '{\r\n';
			script += '%>\r\n';
			script += '\t\t\t<input type="button" class="btn btn-success" id="edit" name="button_edit" value="Ubah" onclick="window.location=\'<%= _self_name %>?user_action=update&'+pkey+'=<%= _id %>\'">\r\n';
			script += '<%\r\n';
			script += '}\r\n';
			script += '%>\r\n';
		}
		
		script += '\t\t\t<input type="button" class="btn btn-primary" id="showall" value="Tampilkan Semua" onclick="window.location=\'<%= _self_name %>\'">\r\n';
		script += '\t\t</td>\r\n\t</tr>\r\n';
		script += '</table>\r\n';
	
		script += '</form>\r\n';
	
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		script += '<%\r\n}\r\nelse\r\n{\r\n';
		
		script += '%>\r\n<%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\t<div class="alert alert-not-found"><%= pagination_data_not_found %></div>\r\n';
		script += templateAfterContent(formData);
		script += '<%@ include file="lib.inc/footer.jsp" %>';
		script += '\r\n<%';
	
		script += '\r\n}\r\n%>';
		// end if has permission
		script += '<%\r\n}\r\ncatch(Exception e)\r\n{\r\n';
		script += '// Catch\r\n';
		script += '}\r\nfinally\r\n{\r\n';
		script += '// Finnaly\r\n';
		script += '}\r\n%>\r\n';
		script += '<%\r\n}\r\n';
		
		script += 'else\r\n{\r\n%>';
		script += '<%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><%= error_message_permission %></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		// end else has permission
		script += '<%\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '%>';
	}
	else if(language == 'php')
	{
				script += '\r\nelse if($_action == ("detail"))\r\n{\r\n';
		script += '// TODO: Here are your code for detail UI\r\n';
		script += '$_queryApv = "";\r\n';
		script += '$_next = filterInput(INPUT_GET, "next", FILTER_SANITIZE_STRING, true);\r\n';
		script += 'if(false !== stripos($_permission, "detail"))\r\n{\r\n';	
		script += '$_id = filterInput(INPUT_GET, "'+pkey+'", '+pkeyFilter+', true);\r\n';
		script += '$_result = null;\r\n\r\n';
		
		var tableFieldsArray = [];
		var tableFields = '';
		
		if(args.withNote)
		{
			script += '$d_note_create = "";\r\n';
			script += '$d_note_approve = "";\r\n';
		}
		if(args.withApproval)
		{
			script += '$d_'+currentTable+'_apv_id = "";\r\n';
			script += '$d_waiting_for = "";\r\n';
			tableFieldsArray.push('coalesce(`'+currentTable+'`.`'+currentTable+'_apv_id`, 0) as `'+currentTable+'_apv_id`');
			tableFieldsArray.push('coalesce(`'+currentTable+'`.`waiting_for`, 0) as `waiting_for`');
		}
		for(i in detailFields)
		{
			field = detailFields[i];
			script += '$d_'+field+' = "";\r\n';
			
			elementType = getSelectedElementType(formData, field);
			if(elementType == 'select')
			{
				table = field;
				if(table.length > 3)
				{
					table = table.substr(0, table.length-3);
				}
				subselectStr = '(select `'+table+'`.`nama` from `'+table+'` where `'+table+'`.`'+field+'` = `'+currentTable+'`.`'+field+'`) as `'+field+'`';
				subselectArray.push(subselectStr);
				subselectStr = '(select `'+table+'`.`nama` from `'+table+'` where `'+table+'`.`'+field+'` = `'+currentTable+'_apv`.`'+field+'`) as `'+field+'`';
				subselectArrayApv.push(subselectStr);
			}
			else
			{
				tableFieldsArray.push('`'+currentTable+'`.`'+field+'`');
				tableFieldsArrayApv.push('`'+currentTable+'_apv`.`'+field+'`');
			}
		}
		if(args.withApproval)
		{
			tableFieldsArray.push('`'+currentTable+'`.`waiting_for`');
			tableFieldsArray.push('`'+currentTable+'`.`'+currentTable+'_apv_id`');
		}
		if(args.withNote)
		{
			tableFieldsArrayApv.push('coalesce(`'+currentTable+'_apv`.`note_create`, \'\') as `note_create`');
		}
		if(subselectArray.length)
		{
			subselectStr = ', ".\r\n"'+subselectArray.join(', ".\r\n"')+' ".\r\n"'
		}
		else
		{
			subselectStr = ' '; 
		}
		if(subselectArrayApv.length)
		{
			subselectStrApv = ', ".\r\n"'+subselectArrayApv.join(', ".\r\n"')+' ".\r\n"'
		}
		else
		{
			subselectStrApv = ' '; 
		}
		tableFields = tableFieldsArray.join(', ');
		tableFieldsApv = tableFieldsArrayApv.join(', ');
		
		script += '\r\n';
		script += '$_query = $query1->newQuery()\r\n'+
		'\t->select("'+tableFields+''+subselectStr+'")\r\n'+
		'\t->from("`'+currentTable+'`")\r\n'+
		'\t->where("`'+pkey+'` = \'".$_id."\'")\r\n'+
		'\t->toString();\r\n';
		
		script += '\r\ntry\r\n{\r\n';
		script += '\r\n$db_rs = $database1->prepare($_query);\r\n';
		script += '\r\n$db_rs->execute();\r\n\r\n';
		script += 'if($db_rs->rowCount())\r\n{\r\n';
		script += '$db_data = $db_rs->fetch(PDO::FETCH_ASSOC);\r\n';
		
		if(args.withApproval)
		{
			script += '$d_'+currentTable+'_apv_id = $db_data["'+currentTable+'_apv_id"];\r\n';
			script += '$d_waiting_for = $db_data["waiting_for"];\r\n';
		}
		for(i in detailFields)
		{
			field = detailFields[i];
			script += '$d_'+field+' = $db_data["'+field+'"];\r\n';
		}
		
		script += '\r\n?><?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		script += '<form name="detailform" id="detailform" action="" method="post">\r\n';
		
		
		if(args.withApproval)
		{
			script += '<?php\r\n';
			script += '$_apvData = null;\r\n';
			script += '$_needApproval = false;\r\n';
			script += '$_apv_id = $cms->getValue("'+currentTable+'", "'+pkey+'", $_id, "'+currentTable+'_apv_id");\r\n';
			script += "if($_apv_id == null) $_apv_id = 0;\r\n";
			script += 'if($_apv_id > 0)\r\n';
			script += '{\r\n';
			script += '$_queryApv = $query1->newQuery()\r\n'+
			'\t->select("'+tableFieldsApv+''+subselectStrApv+'")\r\n'+
			'\t->from("`'+currentTable+'_apv`")\r\n'+
			'\t->where("`'+currentTable+'_apv_id` = \'".$_apv_id."\'")\r\n'+
			'\t->toString();\r\n';
			script += '$_apvData = $cms->getRecord($_queryApv);\r\n';
			script += 'if($_apvData != null)\r\n';
			script += '{\r\n';
			script += '\t$_needApproval = true;\r\n';
			script += '}\r\n';
			script += '}\r\n';
		
			script += '\r\n';
		
			script += 'if($_needApproval && $_next != "")\r\n';
			script += '{\r\n';
			script += '?>\r\n';
		
			script += '<script type="text/javascript">\r\n';
			script += '\t$(document).ready(function(){\r\n';
			script += '\tcompareData(\'.responsive-three-cols\');\r\n';
			script += '});\r\n';
			script += '<'+'/script'+'>\r\n';
		
			script += '<table class="responsive responsive-three-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		
			script += '\t<tr class="row-label-three-cols">\r\n';
			script += '\t\t<td><span>Label</span></td>\r\n';
			script += '\t\t<td><span>Current Data</span></td>\r\n';
			script += '\t\t<td><span>Draft</span></td>\r\n';
			script += '\t</tr>\r\n';
			
			for(i in detailFields)
			{
				caption = getFieldValues(formData, 'caption_'+detailFields[i]);
				cellType = getSelectedElementType(formData, detailFields[i]);
				if(cellType == 'checkbox')
				{
					cellVal = '($d_'+detailFields[i]+' == "1")?"Ya":"Tidak"';
					cellVal2 = '($_apvData["'+detailFields[i]+'"] == "1")?"Ya":"Tidak"';
				}
				else
				{
					cellVal = '$d_'+detailFields[i];
					cellVal2 = '$_apvData["'+detailFields[i]+'"]';
				}
				script += '\t<tr>\r\n';
				script += '\t\t<td><span>'+caption+'</span></td>\r\n';
				script += '\t\t<td><span class="compare"><?php echo '+cellVal+'; ?></span></td>\r\n';
				script += '\t\t<td><span class="compare"><?php echo '+cellVal2+'; ?></span></td>\r\n';
				script += '\t</tr>\r\n';
			}
			if(args.withNote)
			{
				script += '\t<?php\r\n'+
				'\t$d_note_create = $_apvData["note_create"];\r\n'+
				'\t?>\r\n'+
				'\t<tr>\r\n\t\t<td>Catatan</td>\r\n\t\t<td><textarea id="apv_note" name="apv_note" class="form-control ta-min-10" placeholder="Approval note here..."></textarea></td>\r\n\t\t<td><textarea class="form-control ta-min-10" readonly="readonly"><?php echo $cms->escapeHTML($d_note_create); ?></textarea></td>\r\n\t</tr>\r\n';
			}
			script += '</table>\r\n';
		
			script += '<?php\r\n';
			script += '}\r\n';
			script += 'else\r\n';
			script += '{\r\n';
			
			script += 'if($d_waiting_for > 0 && $_next != "")\r\n';			
			script += '{\r\n';
			script += '?>\r\n';
			script += '\t<div class="message-warning-require-approval"><?php echo $approval_waiting_message[$d_waiting_for];?></div>\r\n';
			script += '<?php\r\n';
			script += '}\r\n';
			
			script += '?>\r\n';
		}
	
		script += '<table class="responsive responsive-two-cols" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		for(i in detailFields)
		{
			caption = getFieldValues(formData, 'caption_'+detailFields[i]);
			cellType = getSelectedElementType(formData, detailFields[i]);
			if(cellType == 'checkbox')
			{
				cellVal = '($d_'+detailFields[i]+' == "1")?"Ya":"Tidak"';
			}
			else
			{
				cellVal = '$d_'+detailFields[i];
			}
			script += '\t<tr>\r\n';
			script += '\t\t<td>'+caption+'</td>\r\n';
			script += '\t\t<td><?php echo '+cellVal+'; ?></td>\r\n';
			script += '\t</tr>\r\n';
		}
		script += '</table>\r\n';
	
		if(args.withApproval)
		{
			script += '<?php\r\n';
			script += '}\r\n';
			script += '?>\r\n';
		}
		
		script += '<table class="responsive responsive-two-cols responsive-button-area" border="0" cellpadding="0" cellspacing="0" width="100%">\r\n';
		script += '\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td>\r\n';
		
		if(args.withApproval)
		{
			script += '<?php\r\n';
			script += 'if($d_'+currentTable+'_apv_id != 0 && $_next != "" && $cms->hasPermission($_userLevel, $_module_name, "approve"))\r\n';
			script += '{\r\n';
			
			script += '\tif($_next != "")\r\n'+
			'\t{\r\n'+
			'\t\tif($_next != ("reject"))\r\n'+
			'\t\t{\r\n'+
			'?>\r\n';
	
			script += '\t\t\t<input type="button" class="btn btn-success" id="approve" name="button_approve" value="Approve" onclick="approveData(\'<?php echo $_self_name; ?>\', \''+pkey+'\', \'<?php echo $_id; ?>\', $(\'#apv_note\').val())">\r\n';
			
			script += '<?php\r\n'+
			'\t\t}\r\n';
			script += '\t\tif($_next != ("approve"))\r\n'+
			'\t\t{\r\n'+
			'?>\r\n';
			
			script += '\t\t\t<input type="button" class="btn btn-danger" id="reject" name="button_reject" value="Reject" onclick="rejectData(\'<?php echo $_self_name; ?>\', \''+pkey+'\', \'<?php echo $_id; ?>\', $(\'#apv_note\').val())">\r\n';
			script += '<?php\r\n'+
			'\t\t}\r\n'+
			'\t}\r\n';
			
			script += '}\r\n';
			script += 'else if(false !== stripos($_permission, "update"))\r\n';
			script += '{\r\n';
			script += '?>\r\n';
			script += '\t\t\t<input type="button" class="btn btn-success" id="edit" name="button_edit" value="Ubah" onclick="window.location=\'<?php echo $_self_name; ?>?user_action=update&'+pkey+'=<?php echo $_id; ?>\'">\r\n';
			script += '<?php\r\n';
			script += '}\r\n';
			script += '?>\r\n';
		}
		
		script += '\t\t\t<input type="button" class="btn btn-primary" id="showall" value="Tampilkan Semua" onclick="window.location=\'<?php echo $_self_name; ?>\'">\r\n';
		script += '\t\t</td>\r\n\t</tr>\r\n';
		script += '</table>\r\n';
	
		script += '</form>\r\n';
	
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		script += '<?php\r\n}\r\nelse\r\n{\r\n';
		
		script += '?>\r\n<?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\t<div class="alert alert-not-found"><?php echo $pagination_data_not_found; ?></div>\r\n';
		script += templateAfterContent(formData);
		script += '<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		script += '\r\n<?php';
	
		script += '\r\n}\r\n?>';
		// end if has permission
		script += '<?php\r\n}\r\ncatch(Exception $e)\r\n{\r\n';
		script += '// Catch\r\n';
		script += '}\r\n';
		script += '}\r\n';
		
		script += 'else\r\n{\r\n?>';
		script += '<?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><?php echo $error_message_permission; ?></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		// end else has permission
		script += '<?php\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '';
	}
	return script;
}
function generateListUI(formData, args, language)
{
	language = language || 'php';
	args = args || {};
	args.withApproval = args.withApproval || false;
	args.withTrash = args.withTrash || false;
	args.withNote = args.withNote || false;
	args.defaultOrderDesc = args.defaultOrderDesc || false;
	args.sortOrder = args.sortOrder || false;
	var sort_order = args.sortOrder;
	var needApv = '';
	var listFields = getListFields(formData);
	var i, j, k, inputEl, caption, subselectArray = [];
	var elementType = '', subselectStr = '', table = '';
	var script = '';
	var field = '';
	var currentTable = getFieldValues(formData, 'table')[0];
	var pk = getPrimaryKey(formData);
	var pkey = (pk.length)?pk[0]:listFields[0];
	var cellVal = "";
	var cellType = "";

	var filters = generateListFilter(formData, currentTable, language);		
	
	if(language == 'jsp')
	{
		script += '<%\r\nelse\r\n{\r\n';
		script += '// TODO: Here are your code for list UI\r\n';
	
		script += 'boolean _hasPermissionInsert = hasPermission(_userLevel, _module_name, "insert");\r\n';
		script += 'boolean _hasPermissionUpdate = hasPermission(_userLevel, _module_name, "update");\r\n';
		script += 'boolean _hasPermissionDelete = hasPermission(_userLevel, _module_name, "delete");\r\n';
		
		script += 'if(hasPermission(_userLevel, _module_name, "list"))\r\n{\r\n';	
		script += 'String _id = "";\r\n';
		script += 'int int_n = 0;\r\n';
		script += 'int int_no = 0;\r\n';
		script += 'int _offset = Integer.parseInt(filterInput(request, "offset", "FILTER_SANITIZE_NUMBER_UINT", true));\r\n';
		script += 'JSONObject _result = null;\r\n';
		
		script += 'String _q = filterInput(request, "q", "FILTER_SANITIZE_SPECIAL_CHARS", true);\r\n';
		script += 'String _qNoEscape = filterInput(request, "q", "FILTER_SANITIZE_SPECIAL_CHARS", false);\r\n';
		script += 'String _filterSort = "'+listFields.join('|')+'";\r\n';
		script += 'String _orderByColumn = chooseOneFrom(_filterSort, filterInput(request, "orderby", "FILTER_SANITIZE_STRING", true), false);\r\n';  
		script += 'String _orderMethod = chooseOneFrom("asc|desc",  filterInput(request, "ordermethod", "FILTER_SANITIZE_STRING", true), true);\r\n';  
	
		script += '\r\n';
	
		script += filters.variables;
		script += filters.queries;
		
		var tableFieldsArray = [];
		var tableFields = '';
	
		
		script += '\r\n';
		if(args.withApproval)
		{
			script += 'String d_'+currentTable+'_apv_id = "";\r\n';
		}
		for(i in listFields)
		{
			field = listFields[i];
			script += 'String d_'+field+' = "";\r\n';
			elementType = getSelectedElementType(formData, field);
			if(elementType == 'select')
			{
				table = field;
				if(table.length > 3)
				{
					table = table.substr(0, table.length-3);
				}
				subselectStr = '(select `'+table+'`.`nama` from `'+table+'` where `'+table+'`.`'+field+'` = `'+currentTable+'`.`'+field+'`) as `'+field+'`';
				subselectArray.push(subselectStr);
			}
			else
			{
				tableFieldsArray.push('`'+currentTable+'`.`'+field+'`');
			}
		}
		if(subselectArray.length)
		{
			subselectStr = ', "+\r\n"'+subselectArray.join(', "+\r\n"')+' "+\r\n"'
		}
		else
		{
			subselectStr = ' '; 
		}
		
		tableFields = tableFieldsArray.join(', ');
	
		if(listFields.indexOf(pkey) == -1)
		{
			tableFields = '`'+currentTable+'`.`'+pkey+'`, '+tableFields;
		}
		
		if(args.withApproval)
		{
			tableFields += ', coalesce(`'+currentTable+'`.`'+currentTable+'_apv_id`, \'0\') as `'+currentTable+'_apv_id` ';
		}
		
		script += '\r\n'; 
		script += 'String sql_query = "";\r\n';
		script += 'String sql_field = "'+tableFields+subselectStr+'";\r\n';
		script += 'String sql_from = "`'+currentTable+'`";\r\n';  
		script += 'String sql_where = "(1=1)";\r\n';
		script += 'String sql_orderBy = "`"+_orderByColumn+"` "+_orderMethod;\r\n'; 
		script += 'String sql_limit = "limit "+pagination_record_per_page+" offset "+_offset;\r\n';
		script += '\r\n';
	
		if(args.withApproval)
		{
			script += 'if(_show_only.equals("need-approval"))\r\n';
			script += '{\r\n';
			script += '\tsql_where += " and `'+currentTable+'`.`'+currentTable+'_apv_id` != \'0\' ";\r\n';
			script += '}\r\n\r\n';
		}
		
		
		script += '// filter from search\r\n';
		script += 'if(!sql_additional_filter.equals(""))\r\n';
		script += '{\r\n';
		script += '\tsql_where += sql_additional_filter;\r\n';
		script += '}\r\n';
		script += '\r\n';
		script += '// add you additional filter here\r\n';  
		script += '\r\n'; 
					
		
		script += '\r\n'; 
		script += 'String sql_count = query1.newQuery()\r\n'+
		'\t.select("count(*)").as("`num`")\r\n'+
		'\t.from(sql_from)\r\n'+
		'\t.where(sql_where)\r\n'+
		'\t.toString();\r\n';
		script += '\r\npagination_max_record = cms.getRowCount(sql_count);\r\n';
		
		script += '// build final query here\r\n';   
		script += 'query1.newQuery()\r\n'+
		'\t.select(sql_field)\r\n'+
		'\t.from(sql_from)\r\n'+
		'\t.where(sql_where);\r\n';
		script += 'if(!_orderByColumn.equals(""))\r\n'; 
		script += '{\r\n'; 
		script += '\tquery1.orderBy(sql_orderBy);\r\n'; 
		script += '}\r\n'; 
		if(sort_order)
		{
			script += 'else\r\n'; 
			script += '{\r\n'; 
			script += '\tquery1.orderBy("`sort_order` asc");\r\n'; 
			script += '}\r\n'; 
		}
		else if(args.defaultOrderDesc)
		{
			script += 'else\r\n'; 
			script += '{\r\n'; 
			script += '\tquery1.orderBy("`"+_pkey_name+"` desc");\r\n'; 
			script += '}\r\n'; 
		}
		script += 'query1.limit(pagination_record_per_page);\r\n';
		script += 'query1.offset(_offset);\r\n';
		script += 'sql_query = query1.toString();\r\n';   
	
	
		script += '\r\n%><%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		
		
		if(sort_order)
		{
			script += '<link rel="stylesheet" type="text/css" href="vendors/sort-table/css.css" />\r\n'+
				'<'+'script type="text/javascript" src="vendors/sort-table/Sortable.js"></s'+'cript>\r\n'+
				'<'+'script type="text/javascript">\r\n'+
				'$(document).ready(function(e){\r\n'+
				'    createSortTable();\r\n'+
				'    createPagination();\r\n'+
				'    $(document).on(\'change\', \'#searchform select\', function(e){\r\n'+
				'		$(this).closest(\'form\').submit();\r\n'+
				'	});\r\n'+
				'    $(document).on(\'click\', \'.recipe-table__add-row-btn\', function (e) {\r\n'+
				'        var $el = $(e.currentTarget);\r\n'+
				'        var $tableBody = $(\'#recipeTableBody\');\r\n'+
				'        var htmlString = $(\'#rowTemplate\').html();\r\n'+
				'        $tableBody.append(htmlString);\r\n'+
				'        return false;\r\n'+
				'    });\r\n'+
				'\r\n'+
				'    $(document).on(\'click\', \'.recipe-table__del-row-btn\', function (e) {\r\n'+
				'        var $el = $(e.currentTarget);\r\n'+
				'        var $row = $el.closest(\'tr\');\r\n'+
				'        $row.remove();\r\n'+
				'        return false;\r\n'+
				'    });\r\n'+
				' 	Sortable.create(\r\n'+
				'        $(\'#recipeTableBody\')[0],\r\n'+
				'        {\r\n'+
				'            animation: 150,\r\n'+
				'            scroll: true,\r\n'+
				'            handle: \'.drag-handler\',\r\n'+
				'            onEnd: function () {\r\n'+
				'            	saveOrder();\r\n'+
				'            }\r\n'+
				'        }\r\n'+
				'    );\r\n'+
				'});\r\n'+
				'function saveOrder()\r\n'+
				'{\r\n'+
				'	var sort_data = [];\r\n'+
				'	var offset = parseInt($(\'#recipeTable tbody\').attr(\'data-offset\') || \'0\');\r\n'+
				'	var no = offset;\r\n'+
				'	$(\'#recipeTable tbody tr\').each(function(e){\r\n'+
				'		no++;\r\n'+
				'		$(this).find(\'.cell-no\').text(no);\r\n'+
				'		$(this).find(\'.cell-order\').text(no);\r\n'+
				'		sort_data.push($(this).attr(\'data-id\'));\r\n'+
				'	});\r\n'+
				'	$.post(\'<%= _self_name %>\', {special_action:\'save-order\', offset:offset, sort_data:sort_data.join(",")}, function(answer){\r\n'+
				'	});\r\n'+
				'}\r\n'+
				'</s'+'cript>\r\n';
		}
		else
		{
			script += '<'+'script type="text/javascript">\r\n'+
				'$(document).ready(function(e){\r\n'+
				'    createSortTable();\r\n'+
				'    createPagination();\r\n'+
				'    $(document).on(\'change\', \'#searchform select\', function(e){\r\n'+
				'    $(this).closest(\'form\').submit();\r\n'+
				'    });\r\n'+
				'});\r\n'+
				'</'+'script'+'>\r\n';
		}
		script += '\r\n<div class="search-bar">\r\n'+
					'	<form name="searchform" id="searchform" method="get" action="">\r\n'+
					'    	<!-- add some filters here -->\r\n'+
					'        <span class="searchform-control">\r\n'+
								filters.elements+				
					((filters.elements.length > 10)?'        	<input type="submit" class="btn btn-success" value="Cari">\r\n':'');
					
		script += '\t\t\t<%\r\n';
		script += '\t\t\tif(_hasPermissionInsert)\r\n';
		script += '\t\t\t{\r\n';
		script += '\t\t\t%>\r\n';			
					
		script += '\t\t\t\t<input type="button" class="btn btn-primary" value="Tambah" onclick="window.location=\'<%= _self_name %>?user_action=insert\'">\r\n';
		script += '\t\t\t<%\r\n';
		script += '\t\t\t}\r\n';
		script += '\t\t\t%>\r\n';
		script += '\t\t</span>\r\n'+
					'\t</form>\r\n'+
					'</div>\r\n';
	
		script += '<%\r\n'+
					'try\r\n'+
					'{\r\n'+
					'db_rs = database1.executeQuery(sql_query);\r\n'+
					'if(db_rs.isBeforeFirst())\r\n'+
					'{\r\n'+
					'%>\r\n';
		
		script += '<form name="mainformdata" id="mainformdata" method="post" action="">\r\n';
		script += '\r\n<div data-pagination="true" data-self-name="<%= _self_name %>" data-max-record="<%= pagination_max_record %>" data-record-per-page="<%= pagination_record_per_page %>" data-hide="<%= (pagination_max_record <= pagination_record_per_page)?"true":"false" %>"></div>\r\n\r\n';
		script += '<div class="row-table-container">\r\n';
		
		if(sort_order)
		{
			script += '<table class="table table-striped jambo_table recipe-table" id="recipeTable" border="0" cellpadding="0" cellspacing="0" width="100%" data-table-sort="true" data-self-name="<%= _self_name %>">\r\n';
		}
		else
		{
			script += '<table class="table table-striped jambo_table" border="0" cellpadding="0" cellspacing="0" width="100%" data-table-sort="true" data-self-name="<%= _self_name %>">\r\n';
		}
		
		script += '\t<thead>\r\n';
		script += '\t\t<tr>\r\n';
	
		script += '\t\t\t<td width="16" align="center"><input type="checkbox" class="checkbox-select-all" data-selector=".checkbox-'+pkey+'" value="1"></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><span class="fa fa-edit"></span></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><span class="fa fa-folder"></span></td>\r\n';
		script += '\t\t\t<td width="25">No</td>\r\n';
	
		
		for(i in listFields)
		{
			caption = getFieldValues(formData, 'caption_'+listFields[i]);
			script += '\t\t\t<td class="col-sort" data-col-name="'+listFields[i]+'"><a href="#">'+caption+'</a></td>\r\n';
		}
		
		if(args.withApproval)
		{
			script += '\t\t<%\r\n'+
			'\t\tif(_permission.contains("approve"))\r\n'+
			'\t\t{\r\n'+
			'\t\t%>\r\n'+
			'\t\t\t<td>Approval</td>\r\n'+
			'\t\t<%\r\n'+
			'\t\t}\r\n'+
			'\t\t%>\r\n';
		}
		
		script += '\t\t</tr>\r\n';
		script += '\t</thead>\r\n';
		
		script += '\t<tbody>\r\n';
		script += '\t\t<%\r\n';
		
		script += '\t\twhile(db_rs.next())\r\n\t\t{\r\n';
		script += '\t\t\tint_n++;\r\n';
		script += '\t\t\tint_no = _offset + int_n;\r\n';
		script += '\t\t\t_id = db_rs.getString("'+pkey+'");\r\n\r\n';
		for(i in listFields)
		{
			field = listFields[i];
			script += '\t\t\td_'+field+' = db_rs.getString("'+field+'");\r\n';
		}
		if(args.withApproval)
		{
			script += '\t\t\td_'+currentTable+'_apv_id = db_rs.getString("'+currentTable+'_apv_id");\r\n';
			needApv = ' data-need-approval="<%= (d_'+currentTable+'_apv_id.equals("0"))?"false":"true" %>"';
		}
		script += '\t\t%>\r\n';
		
		script += '\t\t<tr'+needApv+' data-id="<%= _id %>">\r\n';
		script += '\t\t\t<td width="16" align="center"><input type="checkbox" class="checkbox-'+pkey+'" name="'+pkey+'" value="<%= _id %>"></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><a href="<%= _self_name %>?user_action=update&'+pkey+'=<%= _id %>" class="edit-control"><span class="fa fa-edit"></span></a></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><a href="<%= _self_name %>?user_action=detail&'+pkey+'=<%= _id %>" class="detail-control field-master"><span class="fa fa-folder"></span></a></td>\r\n';
		script += '\t\t\t<td align="right" class="cell-no"><%= int_no %></td>\r\n';
		
		for(i in listFields)
		{
			cellType = getSelectedElementType(formData, listFields[i]);
			if(cellType == 'checkbox')
			{
				cellVal = '(d_'+listFields[i]+' == null)?"Tidak":((d_'+listFields[i]+'.equals("1"))?"Ya":"Tidak")';
			}
			else
			{
				cellVal = 'd_'+listFields[i];
			}
			if(sort_order && listFields[i] == 'sort_order')
			{			
				script += '\t\t\t<td><a class="field-slave cell-order"><%= '+cellVal+' %></a></td>\r\n';
			}
			else
			{
				script += '\t\t\t<td><a class="field-slave"><%= '+cellVal+' %></a></td>\r\n';
			}
		}
		
		if(args.withApproval)
		{
			var apv_id_str = 'd_'+currentTable+'_apv_id';
			script += '\t\t<%\r\n'+
			'\t\tif(_permission.contains("approve"))\r\n'+
			'\t\t{\r\n'+
			'\t\t%>\r\n';
			
			script += '\t\t\t<td nowrap="nowrap"><%\r\n'+
			'\t\t\tif(!'+apv_id_str+'.equals("0"))\r\n'+
			'\t\t\t{\r\n'+
			'\t\t\t%>\r\n'+
			'\t\t\t\t<a href="<%= _self_name %>?user_action=detail&'+pkey+'=<%= _id %>&next=approve" class="btn btn-on-tbl btn-success" data-id="<%= _id %>" data-action="approve">Approve</a>\r\n'+
			'\t\t\t\t<a href="<%= _self_name %>?user_action=detail&'+pkey+'=<%= _id %>&next=reject" class="btn btn-on-tbl btn-warning" data-id="<%= _id %>" data-action="reject">Reject</a>\r\n'+
			'\t\t\t<%\r\n'+
			'\t\t\t}\r\n'+
			'\t\t\t%></td>\r\n';
			
			script += '\t\t<%\r\n'+
			'\t\t}\r\n'+
			'\t\t%>\r\n';
			
		}
		
		script += '\t\t</tr>\r\n';
		script += '\t\t<%\r\n';
		script += '\t\t}\r\n';
		script += '\t\t%>\r\n';
		script += '\t</tbody>\r\n';
		script += '</table>\r\n';
		script += '</div>\r\n';
		script += '\r\n<div data-pagination="true" data-self-name="<%= _self_name %>" data-max-record="<%= pagination_max_record %>" data-record-per-page="<%= pagination_record_per_page %>" data-hide="<%= (pagination_max_record <= pagination_record_per_page)?"true":"false" %>"></div>\r\n';
		
		script += '<div class="button-area">\r\n';
		
		script += '<%\r\n';
		script += 'if(_hasPermissionUpdate)\r\n';
		script += '{\r\n';
		script += '%>\r\n';
		
		script += '\t<input type="submit" class="btn btn-success" name="data_activate" value="Aktifkan">\r\n';
		script += '\t<input type="submit" class="btn btn-warning" name="data_deactivate" value="Nonaktifkan">\r\n';
	
		script += '<%\r\n';
		script += '}\r\n';
		script += 'if(_hasPermissionDelete)\r\n';
		script += '{\r\n';
		script += '%>\r\n';
	
		script += '\t<input type="submit" class="btn btn-danger" name="data_delete" value="Hapus" onclick="return confirm(\'<%= warning_message_delete %>\')">\r\n';
	
		script += '<%\r\n';
		script += '}\r\n';
		script += '%>\r\n';
		script += '</div>\r\n';
		script += '</form>\r\n';
		
		script += '<%\r\n'+
					'}\r\n'+
					'else\r\n'+
					'{\r\n'+
					'%>\r\n'+
					'\t<div class="alert alert-not-found"><%= pagination_data_not_found %></div>\r\n'+
					'<%\r\n'+
					'}\r\n'+
					'}\r\n'+
					'catch(Exception e)\r\n'+
					'{\r\n'+
					'\te.printStackTrace();\r\n'+
					'\t%>\r\n'+
					'\t<div class="message-error-database"><%= error_message_fail_access_db %></div>\r\n'+
					'\t<%\r\n'+
					'}\r\n'+
					'finally\r\n'+
					'{\r\n'+
					'}\r\n'+
					'%>\r\n';
					
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		// end if has permission
		script += '<%\r\n}\r\n';
		
		script += 'else\r\n{\r\n%>';
		script += '<%@ include file="lib.inc/header.jsp" %>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><%= error_message_permission %></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<%@ include file="lib.inc/footer.jsp" %>';
		// end else has permission
		script += '<%\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '%>';
	}
	else if(language == 'php')
	{
		script += '\r\nelse\r\n{\r\n';
		script += '// TODO: Here are your code for list UI\r\n';
	
		script += '$_hasPermissionInsert = (stripos($_permission, "insert") !== false);\r\n';
		script += '$_hasPermissionUpdate = (stripos($_permission, "update") !== false);\r\n';
		script += '$_hasPermissionDelete = (stripos($_permission, "delete") !== false);\r\n';
		
		script += 'if(false !== stripos($_permission, "list"))\r\n{\r\n';	
		script += '$_id = "";\r\n';
		script += '$int_n = 0;\r\n';
		script += '$int_no = 0;\r\n';
		script += '$_offset = 1 * (filterInput(INPUT_GET, "offset", FILTER_SANITIZE_NUMBER_UINT, true));\r\n';
		script += '$_result = null;\r\n';
		
		script += '$_q = filterInput(INPUT_GET, "q", FILTER_SANITIZE_SPECIAL_CHARS, true);\r\n';
		script += '$_qNoEscape = filterInput(INPUT_GET, "q", FILTER_SANITIZE_SPECIAL_CHARS, false);\r\n';
		script += '$_filterSort = "'+listFields.join('|')+'";\r\n';
		script += '$_orderByColumn = $cms->chooseOneFrom($_filterSort, filterInput(INPUT_GET, "orderby", FILTER_SANITIZE_STRING, true), false);\r\n';  
		script += '$_orderMethod = $cms->chooseOneFrom("asc|desc",  filterInput(INPUT_GET, "ordermethod", FILTER_SANITIZE_STRING, true), true);\r\n';  
	
		script += '\r\n';
	
		script += filters.variables;
		script += filters.queries;
		
		var tableFieldsArray = [];
		var tableFields = '';
	
		
		script += '\r\n';
		if(args.withApproval)
		{
			script += '$d_'+currentTable+'_apv_id = "";\r\n';
			script += '$d_waiting_for = "";\r\n';
			tableFieldsArray.push('coalesce(`'+currentTable+'`.`'+currentTable+'_apv_id`, 0) as `'+currentTable+'_apv_id`');
			tableFieldsArray.push('coalesce(`'+currentTable+'`.`waiting_for`, 0) as `waiting_for`');
		}
		for(i in listFields)
		{
			field = listFields[i];
			script += '$d_'+field+' = "";\r\n';
			elementType = getSelectedElementType(formData, field);
			if(elementType == 'select')
			{
				table = field;
				if(table.length > 3)
				{
					table = table.substr(0, table.length-3);
				}
				subselectStr = '(select `'+table+'`.`nama` from `'+table+'` where `'+table+'`.`'+field+'` = `'+currentTable+'`.`'+field+'`) as `'+field+'`';
				subselectArray.push(subselectStr);
			}
			else
			{
				tableFieldsArray.push('`'+currentTable+'`.`'+field+'`');
			}
		}
		if(subselectArray.length)
		{
			subselectStr = ', ".\r\n"'+subselectArray.join(', ".\r\n"')+' ".\r\n"'
		}
		else
		{
			subselectStr = ' '; 
		}
		
		tableFields = tableFieldsArray.join(', ');
	
		if(listFields.indexOf(pkey) == -1)
		{
			tableFields = '`'+currentTable+'`.`'+pkey+'`, '+tableFields;
		}
		
		script += '\r\n'; 
		script += '$sql_query = "";\r\n';
		script += '$sql_field = "'+tableFields+subselectStr+'";\r\n';
		script += '$sql_from = "`'+currentTable+'`";\r\n';  
		script += '$sql_where = "(1=1)";\r\n';
		script += '$sql_orderBy = "`".$_orderByColumn."` ".$_orderMethod;\r\n'; 
		script += '$sql_limit = "limit ".$pagination_record_per_page." offset ".$_offset;\r\n';
		script += '\r\n';
	
		if(args.withApproval)
		{
			script += 'if($_show_only == ("need-approval"))\r\n';
			script += '{\r\n';
			script += '\t$sql_where .= " and `'+currentTable+'`.`'+currentTable+'_apv_id` != \'0\' ";\r\n';
			script += '}\r\n\r\n';
		}
		
		
		script += '// filter from search\r\n';
		script += 'if($sql_additional_filter != "")\r\n';
		script += '{\r\n';
		script += '\t$sql_where .= $sql_additional_filter;\r\n';
		script += '}\r\n';
		script += '\r\n';
		script += '// add you additional filter here\r\n';  
		script += '\r\n'; 
					
		
		script += '\r\n'; 
		script += '$sql_count = $query1->newQuery()\r\n'+
		'\t->select("count(*)")->alias("`num`")\r\n'+
		'\t->from($sql_from)\r\n'+
		'\t->where($sql_where)\r\n'+
		'\t->toString();\r\n';
		script += '\r\n$pagination_max_record = $cms->getRowCount($sql_count);\r\n';
		
		script += '// build final query here\r\n';   
		script += '$query1->newQuery()\r\n'+
		'\t->select($sql_field)\r\n'+
		'\t->from($sql_from)\r\n'+
		'\t->where($sql_where);\r\n';
		script += 'if($_orderByColumn != "")\r\n'; 
		script += '{\r\n'; 
		script += '\t$query1->orderBy($sql_orderBy);\r\n'; 
		script += '}\r\n'; 
		if(sort_order)
		{
			script += 'else\r\n'; 
			script += '{\r\n'; 
			script += '\t$query1->orderBy("`sort_order` asc");\r\n'; 
			script += '}\r\n'; 
		}
		else if(args.defaultOrderDesc)
		{
			script += 'else\r\n'; 
			script += '{\r\n'; 
			script += '\t$query1->orderBy("`".$_pkey_name."` desc");\r\n'; 
			script += '}\r\n'; 
		}
		script += '$query1->limit($pagination_record_per_page);\r\n';
		script += '$query1->offset($_offset);\r\n';
		script += '$sql_query = $query1->toString();\r\n';   
	
	
		script += '\r\n?><?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n';
		if(sort_order)
		{
			script += '<link rel="stylesheet" type="text/css" href="vendors/sort-table/css.css" />\r\n'+
				'<'+'script type="text/javascript" src="vendors/sort-table/Sortable.js"></s'+'cript>\r\n'+
				'<'+'script type="text/javascript">\r\n'+
				'$(document).ready(function(e){\r\n'+
				'    createSortTable();\r\n'+
				'    createPagination();\r\n'+
				'    $(document).on(\'change\', \'#searchform select\', function(e){\r\n'+
				'		$(this).closest(\'form\').submit();\r\n'+
				'	});\r\n'+
				'    $(document).on(\'click\', \'.recipe-table__add-row-btn\', function (e) {\r\n'+
				'        var $el = $(e.currentTarget);\r\n'+
				'        var $tableBody = $(\'#recipeTableBody\');\r\n'+
				'        var htmlString = $(\'#rowTemplate\').html();\r\n'+
				'        $tableBody.append(htmlString);\r\n'+
				'        return false;\r\n'+
				'    });\r\n'+
				'\r\n'+
				'    $(document).on(\'click\', \'.recipe-table__del-row-btn\', function (e) {\r\n'+
				'        var $el = $(e.currentTarget);\r\n'+
				'        var $row = $el.closest(\'tr\');\r\n'+
				'        $row.remove();\r\n'+
				'        return false;\r\n'+
				'    });\r\n'+
				' 	Sortable.create(\r\n'+
				'        $(\'#recipeTableBody\')[0],\r\n'+
				'        {\r\n'+
				'            animation: 150,\r\n'+
				'            scroll: true,\r\n'+
				'            handle: \'.drag-handler\',\r\n'+
				'            onEnd: function () {\r\n'+
				'            	saveOrder();\r\n'+
				'            }\r\n'+
				'        }\r\n'+
				'    );\r\n'+
				'});\r\n'+
				'function saveOrder()\r\n'+
				'{\r\n'+
				'	var sort_data = [];\r\n'+
				'	var offset = parseInt($(\'#recipeTable tbody\').attr(\'data-offset\') || \'0\');\r\n'+
				'	var no = offset;\r\n'+
				'	$(\'#recipeTable tbody tr\').each(function(e){\r\n'+
				'		no++;\r\n'+
				'		$(this).find(\'.cell-no\').text(no);\r\n'+
				'		$(this).find(\'.cell-order\').text(no);\r\n'+
				'		sort_data.push($(this).attr(\'data-id\'));\r\n'+
				'	});\r\n'+
				'	$.post(\'<?php echo $_self_name;?>\', {special_action:\'save-order\', offset:offset, sort_data:sort_data.join(",")}, function(answer){\r\n'+
				'	});\r\n'+
				'}\r\n'+
				'</s'+'cript>\r\n';
		}
		else
		{
			script += '<'+'script type="text/javascript">\r\n'+
				'$(document).ready(function(e){\r\n'+
				'    createSortTable();\r\n'+
				'    createPagination();\r\n'+
				'    $(document).on(\'change\', \'#searchform select\', function(e){\r\n'+
				'    $(this).closest(\'form\').submit();\r\n'+
				'    });\r\n'+
				'});\r\n'+
				'</'+'script'+'>\r\n';
		}
					
		script += '\r\n<div class="search-bar">\r\n'+
					'	<form name="searchform" id="searchform" method="get" action="">\r\n'+
					'    	<!-- add some filters here -->\r\n'+
					'        <span class="searchform-control">\r\n'+
								filters.elements+				
					((filters.elements.length > 10)?'        	<input type="submit" class="btn btn-success" value="Cari">\r\n':'');
					
		script += '\t\t\t<?php\r\n';
		script += '\t\t\tif($_hasPermissionInsert)\r\n';
		script += '\t\t\t{\r\n';
		script += '\t\t\t?>\r\n';			
					
		script += '\t\t\t\t<input type="button" class="btn btn-primary" value="Tambah" onclick="window.location=\'<?php echo $_self_name; ?>?user_action=insert\'">\r\n';
		script += '\t\t\t<?php\r\n';
		script += '\t\t\t}\r\n';
		script += '\t\t\t?>\r\n';
		script += '\t\t</span>\r\n'+
					'\t</form>\r\n'+
					'</div>\r\n';
	
		script += '<?php\r\n'+
					'try\r\n'+
					'{\r\n'+
					'$db_rs = $database1->prepare($sql_query);\r\n'+
					'$db_rs->execute();\r\n'+
					'$db_max_row = $db_rs->rowCount();\r\n'+
					'if($db_max_row)\r\n'+
					'{\r\n'+
					'?>\r\n';
		
		script += '<form name="mainformdata" id="mainformdata" method="post" action="">\r\n';
		script += '\r\n<div data-pagination="true" data-self-name="<?php echo $_self_name; ?>" data-max-record="<?php echo $pagination_max_record; ?>" data-record-per-page="<?php echo $pagination_record_per_page; ?>" data-hide="<?php echo ($pagination_max_record <= $pagination_record_per_page)?"true":"false"; ?>"></div>\r\n\r\n';
		script += '<div class="row-table-container">\r\n';
		
		if(sort_order)
		{
			script += '<table class="table table-striped jambo_table recipe-table" id="recipeTable" border="0" cellpadding="0" cellspacing="0" width="100%" data-table-sort="true" data-self-name="<?php echo $_self_name; ?>">\r\n';
		}
		else
		{
			script += '<table class="table table-striped jambo_table" border="0" cellpadding="0" cellspacing="0" width="100%" data-table-sort="true" data-self-name="<?php echo $_self_name; ?>">\r\n';
		}

		
		script += '\t<thead>\r\n';
		script += '\t\t<tr>\r\n';

		if(sort_order)
		{
			script += '\t\t\t<td class="drag-handler"></td>\r\n';
		}
	
		script += '\t\t\t<td width="16" align="center"><input type="checkbox" class="checkbox-select-all" data-selector=".checkbox-'+pkey+'" value="1"></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><span class="fa fa-edit"></span></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><span class="fa fa-folder"></span></td>\r\n';
		script += '\t\t\t<td width="25">No</td>\r\n';
	
		
		for(i in listFields)
		{
			caption = getFieldValues(formData, 'caption_'+listFields[i]);
			script += '\t\t\t<td class="col-sort" data-col-name="'+listFields[i]+'"><a href="#">'+caption+'</a></td>\r\n';
		}
		
		if(args.withApproval)
		{
			script += '\t\t<?php\r\n'+
			'\t\tif(stripos($_permission, "approve") !== false)\r\n'+
			'\t\t{\r\n'+
			'\t\t?>\r\n'+
			'\t\t\t<td>Approval</td>\r\n'+
			'\t\t<?php\r\n'+
			'\t\t}\r\n'+
			'\t\t?>\r\n';
		}
		
		script += '\t\t</tr>\r\n';
		script += '\t</thead>\r\n';
		
		if(sort_order)
		{
			script += '\t<tbody id="recipeTableBody" data-offset="<?php echo $_offset;?>">\r\n';
		}
		else
		{
			script += '\t<tbody>\r\n';
		}
		script += '\t\t<?php\r\n';
		script += '\t\t$db_data = $db_rs->fetchAll(PDO::FETCH_ASSOC);\r\n';
		
		script += '\t\tfor($db_i = 0; $db_i < $db_max_row; $db_i++)\r\n\t\t{\r\n';
		script += '\t\t\t$int_n++;\r\n';
		script += '\t\t\t$int_no = $_offset + $int_n;\r\n';
		script += '\t\t\t$_id = $db_data[$db_i]["'+pkey+'"];\r\n\r\n';
		for(i in listFields)
		{
			field = listFields[i];
			script += '\t\t\t$d_'+field+' = $db_data[$db_i]["'+field+'"];\r\n';
		}

		if(args.withApproval)
		{
			script += '\t\t\t$d_'+currentTable+'_apv_id = $db_data[$db_i]["'+currentTable+'_apv_id"];\r\n';
			script += '\t\t\t$d_waiting_for = $db_data[$db_i]["waiting_for"];\r\n';
			needApv = ' data-need-approval="<?php echo ($d_'+currentTable+'_apv_id == "0")?"false":"true" ?>"';
		}
		script += '\t\t?>\r\n';
		
		script += '\t\t<tr'+needApv+' data-id="<?php echo $_id ?>">\r\n';
		
		if(sort_order)
		{
			script += '\t\t\t<td class="drag-handler"></td>\r\n';
		}
		
		script += '\t\t\t<td width="16" align="center"><input type="checkbox" class="checkbox-'+pkey+'" name="'+pkey+'[]" value="<?php echo $_id ?>"></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><a href="<?php echo $_self_name; ?>?user_action=update&'+pkey+'=<?php echo $_id; ?>" class="edit-control"><span class="fa fa-edit"></span></a></td>\r\n';
		script += '\t\t\t<td width="16" align="center"><a href="<?php echo $_self_name; ?>?user_action=detail&'+pkey+'=<?php echo $_id; ?>" class="detail-control field-master"><span class="fa fa-folder"></span></a></td>\r\n';
		script += '\t\t\t<td align="right" class="cell-no"><?php echo $int_no ?></td>\r\n';
		
		for(i in listFields)
		{
			cellType = getSelectedElementType(formData, listFields[i]);
			if(cellType == 'checkbox')
			{
				cellVal = '($d_'+listFields[i]+' == "1")?"Ya":"Tidak"';
			}
			else
			{
				cellVal = '$d_'+listFields[i];
			}
			
			if(sort_order && listFields[i] == 'sort_order')
			{			
				script += '\t\t\t<td><a class="field-slave cell-order"><?php echo '+cellVal+' ?></a></td>\r\n';
			}
			else
			{
				script += '\t\t\t<td><a class="field-slave"><?php echo '+cellVal+' ?></a></td>\r\n';
			}
		}
		
		if(args.withApproval)
		{
			var apv_id_str = 'd_'+currentTable+'_apv_id';
			script += '\t\t<?php\r\n'+
			'\t\tif(stripos($_permission, "approve") !== false)\r\n'+
			'\t\t{\r\n'+
			'\t\t?>\r\n';
			
			script += '\t\t\t<td nowrap="nowrap"><?php\r\n'+
			'\t\t\tif($'+apv_id_str+' != ("0"))\r\n'+
			'\t\t\t{\r\n'+
			'\t\t\t?>\r\n'+
			'\t\t\t\t<a href="<?php echo $_self_name; ?>?user_action=detail&'+pkey+'=<?php echo $_id ?>&next=approve" class="btn btn-on-tbl btn-success" data-id="<?php echo $_id ?>" data-action="approve">Approve</a>\r\n'+
			'\t\t\t\t<a href="<?php echo $_self_name; ?>?user_action=detail&'+pkey+'=<?php echo $_id ?>&next=reject" class="btn btn-on-tbl btn-warning" data-id="<?php echo $_id ?>" data-action="reject">Reject</a>\r\n'+
			'\t\t\t<?php\r\n'+
			'\t\t\t}\r\n'+
			'\t\t\t?></td>\r\n';
			
			script += '\t\t<?php\r\n'+
			'\t\t}\r\n'+
			'\t\t?>\r\n';
			
		}
		
		script += '\t\t</tr>\r\n';
		script += '\t\t<?php\r\n';
		script += '\t\t}\r\n';
		script += '\t\t?>\r\n';
		script += '\t</tbody>\r\n';
		script += '</table>\r\n';
		script += '</div>\r\n';
		script += '\r\n<div data-pagination="true" data-self-name="<?php echo $_self_name; ?>" data-max-record="<?php echo $pagination_max_record ?>" data-record-per-page="<?php echo $pagination_record_per_page; ?>" data-hide="<?php echo ($pagination_max_record <= $pagination_record_per_page)?"true":"false" ?>"></div>\r\n';
		
		script += '<div class="button-area">\r\n';
		
		script += '<?php\r\n';
		script += 'if($_hasPermissionUpdate)\r\n';
		script += '{\r\n';
		script += '?>\r\n';
		
		script += '\t<input type="submit" class="btn btn-success" name="data_activate" value="Aktifkan">\r\n';
		script += '\t<input type="submit" class="btn btn-warning" name="data_deactivate" value="Nonaktifkan">\r\n';
	
		script += '<?php\r\n';
		script += '}\r\n';
		script += 'if($_hasPermissionDelete)\r\n';
		script += '{\r\n';
		script += '?>\r\n';
	
		script += '\t<input type="submit" class="btn btn-danger" name="data_delete" value="Hapus" onclick="return confirm(\'<?php echo $warning_message_delete ?>\')">\r\n';
	
		script += '<?php\r\n';
		script += '}\r\n';
		script += '?>\r\n';
		script += '</div>\r\n';
		script += '</form>\r\n';
		
		script += '<?php\r\n'+
					'}\r\n'+
					'else\r\n'+
					'{\r\n'+
					'?>\r\n'+
					'\t<div class="alert alert-not-found"><?php echo $pagination_data_not_found ?></div>\r\n'+
					'<?php\r\n'+
					'}\r\n'+
					'}\r\n'+
					'catch(Exception $e)\r\n'+
					'{\r\n'+
					'\t?>\r\n'+
					'\t<div class="message-error-database"><?php echo $error_message_fail_access_db ?></div>\r\n'+
					'\t<?php\r\n'+
					'}\r\n'+
					'?>\r\n';
					
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		// end if has permission
		script += '<?php\r\n}\r\n';
		
		script += 'else\r\n{\r\n?>';
		script += '<?php include_once dirname(__FILE__)."/lib.inc/header.php"; ?>\r\n';
		script += templateBeforeContent(formData);
		script += '\r\n<!-- content started here -->\r\n\r\n\t<div class="message-warning-permission"><?php echo $error_message_permission ?></div>\r\n';
		script += '\r\n<!-- content ended here -->\r\n';
		script += templateAfterContent(formData);
		script += '\r\n<?php include_once dirname(__FILE__)."/lib.inc/footer.php"; ?>';
		// end else has permission
		script += '<?php\r\n}\r\n';
	
		// end if insert
		script += '}\r\n';
		script += '?>';
	}
	return script;
}
function generateListFilter(formData, currentTable, language)
{
	language = language || 'php';
	var filters = getListFilter(formData);
	var i, j, k, l, field, type, table;
	var variables = '';
	var elements = '';
	var queries = '';
	var filterQuery = '';
	if(language == 'jsp')
	{
		queries += 'String sql_additional_filter = "";\r\n\r\n';
		for(i in filters)
		{
			field = filters[i].name;
			table = field;
			caption = getFieldValues(formData, 'caption_'+field);
			if(table.length > 3)
			{
				table = table.substr(0, table.length-3);
			}
			type = filters[i].type;
			variables += 'String s_'+field+' = filterInput(request, "'+field+'", "FILTER_SANITIZE_STRING", true);\r\n';
			
			elements += '\t\t\t<span class="group-together">\r\n\t\t\t<span class="searchform-label">'+caption+'</span>\r\n';
			if(type == 'text')
			{
				elements += '\t\t\t<input type="text" class="input-text-search" name="'+field+'" id="'+field+'" value="<%= filterInput(request, "'+field+'", "FILTER_SANITIZE_STRING", false) %>">\r\n';
				queries += 'if(!s_'+field+'.equals(""))\r\n{\r\n\tsql_additional_filter += " and LOWER(`'+currentTable+'`.`'+field+'`) like LOWER(\'%"+s_'+field+'+"%\') ";\r\n}\r\n';
			}
			else
			{
				elements += '\t\t\t<select class="select-search" name="'+field+'" id="'+field+'">\r\n\t\t\t\t<option value="">- Pilih -</option>\r\n\t\t\t\t<%= cms.createDropDownMenu("'+table+'", "'+field+'", "nama", filterInput(request, "'+field+'", "FILTER_SANITIZE_STRING", true)) %>\r\n\t\t\t</select>\r\n';
				queries += 'if(!s_'+field+'.equals(""))\r\n{\r\n\tsql_additional_filter += " and `'+currentTable+'`.`'+field+'` = \'"+s_'+field+'+"\' ";\r\n}\r\n';
			}
			elements += '\t\t\t</span>\r\n';
		}
	}
	else if(language == 'php')
	{
		queries += '$sql_additional_filter = "";\r\n\r\n';
		for(i in filters)
		{
			field = filters[i].name;
			table = field;
			caption = getFieldValues(formData, 'caption_'+field);
			if(table.length > 3)
			{
				table = table.substr(0, table.length-3);
			}
			type = filters[i].type;
			variables += '$s_'+field+' = filterInput(INPUT_GET, "'+field+'", FILTER_SANITIZE_STRING, true);\r\n';
			
			elements += '\t\t\t<span class="group-together">\r\n\t\t\t<span class="searchform-label">'+caption+'</span>\r\n';
			if(type == 'text')
			{
				elements += '\t\t\t<input type="text" class="input-text-search" name="'+field+'" id="'+field+'" value="<?php echo filterInput(INPUT_GET, "'+field+'", FILTER_SANITIZE_STRING, false) ?>">\r\n';
				queries += 'if($s_'+field+' != "")\r\n{\r\n\t$sql_additional_filter .= " and LOWER(`'+currentTable+'`.`'+field+'`) like LOWER(\'%".$s_'+field+'."%\') ";\r\n}\r\n';
			}
			else
			{
				elements += '\t\t\t<select class="select-search" name="'+field+'" id="'+field+'">\r\n\t\t\t\t<option value="">- Pilih -</option>\r\n\t\t\t\t<?php echo $cms->createDropDownMenu("'+table+'", "'+field+'", "nama", filterInput(INPUT_GET, "'+field+'", FILTER_SANITIZE_STRING, true)) ?>\r\n\t\t\t</select>\r\n';
				queries += 'if($s_'+field+' != "")\r\n{\r\n\t$sql_additional_filter .= " and `'+currentTable+'`.`'+field+'` = \'".$s_'+field+'."\' ";\r\n}\r\n';
			}
			elements += '\t\t\t</span>\r\n';
		}
	}
	return {elements:elements, variables:variables, queries:queries};
}
function getListFilter(formData)
{
	var allFields = getFieldValues(formData, 'field');
	var i, j, k, l = [], m;
	for(i in allFields)
	{
		j = allFields[i];
		k = getFieldValues(formData, 'list_filter_'+j);
		if(typeof k[0] != 'undefined')
		{
			l.push({name:j, type:k});
		}
	}
	return l;
}
function templateBeforeContent(formData)
{
	var template = '<div class="content-wrapper">\r\n';
	return template;
}
function templateAfterContent(formData)
{
	var template = '</div>\r\n';
	return template;
}
function generateInputForInsert(formData, field, language)
{
	language = language || 'php';
	var table = field;
	if(table.length > 3)
	{
		table = table.substr(0, table.length-3);
	}
	var dataType = getSelectedDataType(formData, field);
	var elementType = getSelectedElementType(formData, field);
	var required = isInputRequired(formData, field);
	var el = convertDataTypeToElement(elementType, dataType, required);
	el.attr({'name':field, 'id':field});
	if(elementType == 'select')
	{
		var sel = el[0].outerHTML;
		if(language == 'jsp')
		{
			sel = sel.replace('</select>', '\r\n\t\t\t\t<option value="">- Pilih -</option>\r\n\t\t\t\t<%= cms.createDropDownMenu("'+table+'", "'+field+'", "nama") %>\r\n\t\t\t</select>');
		}
		else
		{
			sel = sel.replace('</select>', '\r\n\t\t\t\t<option value="">- Pilih -</option>\r\n\t\t\t\t<?php echo $cms->createDropDownMenu("'+table+'", "'+field+'", "nama") ?>\r\n\t\t\t</select>');
		}
		return sel;
	}
	if(elementType == 'checkbox')
	{
		el.attr({'value':'1'});
		var cb = el[0].outerHTML;
		var label = '<label>'+cb+' '+field.replaceAll('_', ' ').capitalize().prettify().trim()+'</label>';
		return label;
	}
	
	return el[0].outerHTML;
}
function generateInputForUpdate(formData, field, language)
{
	language = language || 'php';
	var table = field;
	if(table.length > 3)
	{
		table = table.substr(0, table.length-3);
	}
	var dataType = getSelectedDataType(formData, field);
	var elementType = getSelectedElementType(formData, field);
	var required = isInputRequired(formData, field);
	var el = convertDataTypeToElement(elementType, dataType, required);
	el.attr({'name':field, 'id':field});
	
	if(language == 'jsp')
	{
		if(elementType == 'select')
		{
			var sel = el[0].outerHTML;
			sel = sel.replace('</select>', '\r\n\t\t\t\t<option value="">- Pilih -</option>\r\n\t\t\t\t<%= cms.createDropDownMenu("'+table+'", "'+field+'", "nama", d_'+field+') %>\r\n\t\t\t</select>');
			return sel;
		}
		if(elementType == 'text')
		{
			var te = el[0].outerHTML;
			te = te.replace('>', ' value="<%= d_'+field+' %>">');
			return te;
		}
		if(elementType == 'textarea')
		{
			var ta = el[0].outerHTML;
			ta = ta.replace('</textarea>', '<%= d_'+field+' %></textarea>');
			return ta;
		}
		if(elementType == 'checkbox')
		{
			el.attr({'value':'1'});
			var cb = el[0].outerHTML;
			cb = cb.replace('>', '<%= (d_'+field+' == null)?"":((d_'+field+'.equals("1"))?" checked=\\\"checked\\\"":"") %>>');
			var label = '<label>'+cb+' '+field.replaceAll('_', ' ').capitalize().prettify().trim()+'</label>';
			return label;
		}
	}
	else if(language == 'php')
	{
		if(elementType == 'select')
		{
			var sel = el[0].outerHTML;
			sel = sel.replace('</select>', '\r\n\t\t\t\t<option value="">- Pilih -</option>\r\n\t\t\t\t<?php echo $cms->createDropDownMenu("'+table+'", "'+field+'", "nama", $d_'+field+'); ?>\r\n\t\t\t</select>');
			return sel;
		}
		if(elementType == 'text')
		{
			var te = el[0].outerHTML;
			te = te.replace('>', ' value="<?php echo $d_'+field+'; ?>">');
			return te;
		}
		if(elementType == 'textarea')
		{
			var ta = el[0].outerHTML;
			ta = ta.replace('</textarea>', '<?php echo $d_'+field+'; ?></textarea>');
			return ta;
		}
		if(elementType == 'checkbox')
		{
			el.attr({'value':'1'});
			var cb = el[0].outerHTML;
			cb = cb.replace('>', '<?php echo ($d_'+field+' == "1")?" checked=\\\"checked\\\"":""; ?>>');
			var label = '<label>'+cb+' '+field.replaceAll('_', ' ').capitalize().prettify().trim()+'</label>';
			return label;
		}
	}
	return el[0].outerHTML;
}
function getPrimaryKey(formData)
{
	var allFields = getFieldValues(formData, 'field');
	var i, j, k, l = [], m;
	for(i in allFields)
	{
		j = allFields[i];
		k = getFieldValues(formData, 'include_key_'+j);
		if(typeof k[0] != 'undefined')
		{
			l.push(j);
		}
	}
	return l;
}
function getSelectedDataType(formData, field)
{
	var inputType = getFieldValues(formData, 'data_type_'+field);
	return inputType[0] || 'text';
}
function getSelectedElementType(formData, field)
{
	var elementType = getFieldValues(formData, 'element_type_'+field);
	return elementType[0] || 'text';
}
function isInputRequired(formData, field)
{
	var required = getFieldValues(formData, 'include_required_'+field) || [];
	return (required.length)?true:false;
}
function convertDataTypeToElement(elementType, dataType, required)
{
	var el;
	
	switch(elementType)
	{
		case 'text':
		el = $('<input />');
		el.attr({'type':'text'});
		break;
		case 'textarea':
		el = $('<textarea />');
		el.addClass('form-control');
		break;
		case 'select':
		el = $('<select />');
		el.addClass('form-control');
		break;
		case 'checkbox':
		el = $('<input />');
		el.attr({'type':'checkbox'});
		break;
		case 'radio':
		el = $('<input />');
		el.attr({'type':'radio'});
		break;
		case 'enum':
		el = $('<select />');
		el.addClass('form-control');
		break;
		default:
		el = $('<input />');
		el.attr({'type':'text'});
	}
	
	switch(dataType)
	{
		case 'text':
		if(elementType == 'text')
		el.attr({'type':'text', 'class':'form-control input-text input-text-plain'});
		break;
		
		case 'email':
		if(elementType == 'text')
		el.attr({'type':'email', 'class':'form-control input-text input-text-email'});
		break;
		
		case 'password':
		if(elementType == 'text')
		el.attr({'type':'password', 'class':'form-control input-text input-text-password'});
		break;
		
		case 'int':
		if(elementType == 'text')
		el.attr({'type':'number', 'class':'form-control input-text input-text-plain'});
		break;
		
		case 'float':
		if(elementType == 'text')
		el.attr({'type':'number', 'step':'any', 'class':'form-control input-text input-text-plain'});
		break;
		
		case 'time':
		if(elementType == 'text')
		el.attr({'type':'text', 'class':'form-control timepicker input-text input-text-time'});
		break;
		
		case 'datetime':
		if(elementType == 'text')
		el.attr({'type':'text', 'class':'form-control datetimepicker input-text input-text-datetime'});
		break;
		
		case 'date':
		if(elementType == 'text')
		el.attr({'type':'text', 'class':'form-control datepicker input-text input-text-date'});
		break;
		
		case 'color':
		if(elementType == 'text')
		el.attr({'type':'text', 'class':'form-control colorpicker-element input-text input-text-color'});
		break;
		
		case 'tel':
		if(elementType == 'text')
		el.attr({'type':'tel', 'class':'form-control input-text input-text-tel'});
		break;
		
		default:
		if(elementType == 'text')
		el.attr({'type':'text', 'class':'form-control input-text input-text-plain'});
		break;
	}
	if(required)
	{
		el.attr({'required':'required'});
	}
	return el;
}
function getFieldValues(formData, field)
{
	var i, j, k = [];
	for(i in formData)
	{
		j = formData[i];
		if(j.name == field)
		{
			k.push(j.value);
		}
	}
	return k;
}
function getInsertFields(formData)
{
	var allFields = getFieldValues(formData, 'field');
	var i, j, k, l = [], m;
	for(i in allFields)
	{
		j = allFields[i];
		k = getFieldValues(formData, 'include_insert_'+j);
		if(typeof k[0] != 'undefined')
		{
			l.push(j);
		}
	}
	return l;
}
function getUpdateFields(formData)
{
	var allFields = getFieldValues(formData, 'field');
	var i, j, k, l = [], m;
	for(i in allFields)
	{
		j = allFields[i];
		k = getFieldValues(formData, 'include_edit_'+j);
		if(typeof k[0] != 'undefined')
		{
			l.push(j);
		}
	}
	return l;
}
function getDetailFields(formData)
{
	var allFields = getFieldValues(formData, 'field');
	var i, j, k, l = [], m;
	for(i in allFields)
	{
		j = allFields[i];
		k = getFieldValues(formData, 'include_detail_'+j);
		if(typeof k[0] != 'undefined')
		{
			l.push(j);
		}
	}
	return l;
}
function getListFields(formData)
{
	var allFields = getFieldValues(formData, 'field');
	var i, j, k, l = [], m;
	for(i in allFields)
	{
		j = allFields[i];
		k = getFieldValues(formData, 'include_list_'+j);
		if(typeof k[0] != 'undefined')
		{
			l.push(j);
		}
	}
	return l;
}

function getSelectedFilterType(formData, field)
{
	var filterType = $('select[name="filter_type_'+field+'"]').val();
	return filterType;
}
function getSelectedDataTypeForServer(formData, field)
{
	var dataType = "String";
	var fieldType = $('select[name="data_type_'+field+'"]').val();
	switch(fieldType)
	{
		case "text": 
		dataType = "String"
		break;
		case "email": 
		dataType = "String"
		break;
		case "password": 
		dataType = "String"
		break;
		case "int":
		dataType = "int"
		break;
		case "float":
		dataType = "double"
		break;
		case "date":
		dataType = "String"
		break;
		case "time":
		dataType = "String"
		break;
		case "datetime":
		dataType = "String"
		break;
		case "color":
		dataType = "String"
		break;
		case "tel":
		dataType = "String"
		break;
		default:
		dataType = "String"
		break;
	}
	return dataType;
}
var SQLFromServer = "";
function loadJSONFromServer(selector, url, host, port, username, password, database, table)
{
	$(selector).empty();
	$.ajax({
		url:url,
		data:{host:host, port:port, username:username, password:password, database:database, table:table},
		type:"POST",
		dataType:"JSON",
		success: function(answer)
		{
			var data = answer.fields;
			SQLFromServer = Base64.decode(answer.query);
			var i;
			var field, args;
			var DOMHTML;
			var so = false;
			for(i in data)
			{
				field = data[i].column_name;
				if(field == 'sort_order')
				{
					so = true;
				}
				args = {type:data[i].data_type};
				DOMHTML = generateRow(field, args);
				$(selector).append(DOMHTML);
			}
			if(so)
			{
				$('#manualsortorder').parent().css({'display':'inline'});
			}
			else
			{
				$('#manualsortorder').parent().css({'display':'none'});
				$('#manualsortorder')[0].checked = false;
			}
			var defaultData = getSavedData($('#formdatabase'));
			loadState(defaultData, $('#formdatabase'), $('#formgenerator'));
		}
	});
}
function generateSelectFilter(field, args)
{
	var virtualDOM;
	
	args = args || {};
	args.type = args.type || 'text';
	var dataType = args.type;
	var matchByType = {
		'FILTER_SANITIZE_NUMBER_INT':['bit', 'varbit', 'smallint', 'int', 'integer', 'bigint', 'smallserial', 'serial', 'bigserial', 'bool', 'boolean'],
		'FILTER_SANITIZE_NUMBER_FLOAT':['numeric', 'double', 'real', 'money'],
		'FILTER_SANITIZE_SPECIAL_CHARS':['char', 'character', 'varchar', 'character varying', 'text', 'date', 'timestamp', 'time']
	}
	
	virtualDOM = $(
	'<select name="filter_type_'+field+'" id="filter_type_'+field+'">\r\n'+
		'<option value="FILTER_SANITIZE_NUMBER_INT">NUMBER_INT</option>\r\n'+
		'<option value="FILTER_SANITIZE_NUMBER_UINT">NUMBER_UINT</option>\r\n'+
		'<option value="FILTER_SANITIZE_NUMBER_OCTAL">NUMBER_OCTAL</option>\r\n'+
		'<option value="FILTER_SANITIZE_NUMBER_HEXADECIMAL">NUMBER_HEXADECIMAL</option>\r\n'+
		'<option value="FILTER_SANITIZE_NUMBER_FLOAT">NUMBER_FLOAT</option>\r\n'+
		'<option value="FILTER_SANITIZE_STRING">STRING</option>\r\n'+
		'<option value="FILTER_SANITIZE_STRING_INLINE">STRING_INLINE</option>\r\n'+
		'<option value="FILTER_SANITIZE_NO_DOUBLE_SPACE">NO_DOUBLE_SPACE</option>\r\n'+
		'<option value="FILTER_SANITIZE_STRIPPED">STRIPPED</option>\r\n'+
		'<option value="FILTER_SANITIZE_SPECIAL_CHARS">SPECIAL_CHARS</option>\r\n'+
		'<option value="FILTER_SANITIZE_ALPHA">ALPHA</option>\r\n'+
		'<option value="FILTER_SANITIZE_ALPHANUMERIC">ALPHANUMERIC</option>\r\n'+
		'<option value="FILTER_SANITIZE_ALPHANUMERICPUNC">ALPHANUMERICPUNC</option>\r\n'+
		'<option value="FILTER_SANITIZE_STRING_BASE64">STRING_BASE64</option>\r\n'+
		'<option value="FILTER_SANITIZE_EMAIL">EMAIL</option>\r\n'+
		'<option value="FILTER_SANITIZE_URL">URL</option>\r\n'+
		'<option value="FILTER_SANITIZE_IP">IP</option>\r\n'+
		'<option value="FILTER_SANITIZE_ENCODED">ENCODED</option>\r\n'+
		'<option value="FILTER_SANITIZE_COLOR">COLOR</option>\r\n'+
		'<option value="FILTER_SANITIZE_MAGIC_QUOTES">MAGIC_QUOTES</option>\r\n'+
		'<option value="FILTER_SANITIZE_PASSWORD">PASSWORD</option>\r\n'+
	'</select>\r\n'
	);
	
	var i, j, k, l;
	var filterType = 'FILTER_SANITIZE_SPECIAL_CHARS';
	var found = false;
	for(i in matchByType)
	{
		j = matchByType[i];
		for(k in j)
		{
			if(dataType.indexOf(j[k]) != -1)
			{
				filterType = i;
				found = true;
				break;
			}
		}
		if(found)
		{
			break;
		}
	}
	virtualDOM.find('option').each(function(index, element) {
        $(this).removeAttr('selected');
    });
	virtualDOM.find('option[value="'+filterType+'"]').attr('selected', 'selected');
	return virtualDOM[0].outerHTML;
}


function generateSelectType(field, args)
{
	var virtualDOM;
	args = args || {};
	args.type = args.type || 'text';
	var dataType = args.type;
	var matchByType = {
		'int':['bit', 'varbit', 'smallint', 'int', 'integer', 'bigint', 'smallserial', 'serial', 'bigserial', 'bool', 'boolean'],
		'float':['numeric', 'double', 'real', 'money'],
		'text':['char', 'character', 'varchar', 'character varying', 'text', 'enum'],
		'date':['date'],
		'datetime':['datetime', 'timestamp'],
		'time':['time']
	}

	virtualDOM = $(
	'<select name="data_type_'+field+'" id="data_type_'+field+'">\r\n'+
		'<option value="text" title="&lt;input type=&quot;text&quot;&gt;">text</option>\r\n'+
		'<option value="email" title="&lt;input type=&quot;email&quot;&gt;">email</option>\r\n'+
		'<option value="tel" title="&lt;input type=&quot;tel&quot;&gt;">tel</option>\r\n'+
		'<option value="password" title="&lt;input type=&quot;password&quot;&gt;">password</option>\r\n'+
		'<option value="int" title="&lt;input type=&quot;number&quot;&gt;">int</option>\r\n'+
		'<option value="float" title="&lt;input type=&quot;number&quot; step=&quot;any&quot;&gt;">float</option>\r\n'+
		'<option value="date" title="&lt;input type=&quot;text&quot;&gt;">date</option>\r\n'+
		'<option value="time" title="&lt;input type=&quot;text&quot;&gt;">time</option>\r\n'+
		'<option value="datetime" title="&lt;input type=&quot;text&quot;&gt;">datetime</option>\r\n'+
		'<option value="color" title="&lt;input type=&quot;text&quot;&gt;">color</option>\r\n'+
	'</select>\r\n'
	);
	
	var i, j, k, l;
	var filterType = 'FILTER_SANITIZE_SPECIAL_CHARS';
	var found = false;
	for(i in matchByType)
	{
		j = matchByType[i];
		for(k in j)
		{
			if(dataType.indexOf(j[k]) != -1)
			{
				filterType = i;
				found = true;
				break;
			}
		}
		if(found)
		{
			break;
		}
	}
	virtualDOM.find('option').each(function(index, element) {
        $(this).removeAttr('selected');
    });
	virtualDOM.find('option[value="'+filterType+'"]').attr('selected', 'selected');
	return virtualDOM[0].outerHTML;
}

function arrayUnique(arr1)
{
	var i;
	var arr2 = [];
	for(i = 0; i < arr1.length; i++)
	{
		if(arr2.indexOf(arr1[i]) == -1)
		{
			arr2.push(arr1[i]);
		}
	}
	return arr2;
}

String.prototype.equalIgnoreCase = function(str)
{
	var str1 = this;
	if(str1.toLowerCase() == str.toLowerCase())
	return true;
	return false;
}
function isKeyWord(str)
{
	str = str.toString();
	var i, j;
	var kw = keyWords.split(",");
	for(i in kw)
	{
		if(str.equalIgnoreCase(kw[i]))
		{
			return true;
		}
	}
	return false;
}
var keyWords = "absolute,action,add,after,aggregate,alias,all,allocate,alter,analyse,analyze,and,any,are,array,as,asc,assertion,at,authorization,avg,before,begin,between,binary,bit,bit_length,blob,boolean,both,breadth,by,call,cascade,cascaded,case,cast,catalog,char,character,character_length,char_length,check,class,clob,close,coalesce,collate,collation,column,commit,completion,connect,connection,constraint,constraints,constructor,continue,convert,corresponding,count,create,cross,cube,current,current_date,current_path,current_role,current_time,current_timestamp,current_user,cursor,cycle,data,date,day,deallocate,dec,decimal,declare,default,deferrable,deferred,delete,depth,deref,desc,describe,descriptor,destroy,destructor,deterministic,diagnostics,dictionary,disconnect,distinct,do,domain,double,drop,dynamic,each,else,end,end-exec,equals,escape,every,except,exception,exec,execute,exists,external,extract,false,fetch,first,float,for,foreign,found,free,from,full,function,general,get,global,go,goto,grant,group,grouping,having,host,hour,identity,ignore,immediate,in,indicator,initialize,initially,inner,inout,input,insensitive,insert,int,integer,intersect,interval,into,is,isolation,iterate,join,key,language,large,last,lateral,leading,left,less,level,like,limit,local,localtime,localtimestamp,locator,lower,map,match,max,min,minute,modifies,modify,month,names,national,natural,nchar,nclob,new,next,no,none,not,null,nullif,numeric,object,octet_length,of,off,offset,old,on,only,open,operation,option,or,order,ordinality,out,outer,output,overlaps,pad,parameter,parameters,partial,path,placing,position,postfix,precision,prefix,preorder,prepare,preserve,primary,prior,privileges,procedure,public,read,reads,real,recursive,ref,references,referencing,relative,restrict,result,return,returns,revoke,right,role,rollback,rollup,routine,row,rows,savepoint,schema,scope,scroll,search,second,section,select,sequence,session,session_user,set,sets,size,smallint,some,space,specific,specifictype,sql,sqlcode,sqlerror,sqlexception,sqlstate,sqlwarning,start,state,statement,static,structure,substring,sum,system_user,table,temporary,terminate,than,then,time,timestamp,timezone_hour,timezone_minute,to,trailing,transaction,translate,translation,treat,trigger,trim,true,under,union,unique,unknown,unnest,update,upper,usage,user,using,value,values,varchar,variable,varying,view,when,whenever,where,with,without,work,write,year,zone";

String.prototype.replaceAll = function(str1, str2, ignore) 
{
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
};
String.prototype.capitalize = function()
{
    return this.replace(/\w\S*/g, function(txt){
		return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
	});
}
String.prototype.prettify = function()
{
	var i, j, k;
	var str = this;
	var arr = str.split(" ");
	for(i in arr)
	{
		j = arr[i];
		switch(j)
		{
			case "Id": arr[i] = "";
			break;
			case "Ip": arr[i] = "IP";
			break;
		}
	}
	return arr.join(" ");
}

function generateRow(field, args)
{
	var isKW = isKeyWord(field);
	var cls = (isKW)?' class="reserved"':'';
	var rowHTML =
	'<tr'+cls+'>\r\n'+
	'  <td class="field-name">'+field+'<input type="hidden" name="field" value="'+field+'"></td>\r\n'+
	'  <td><input type="text" name="caption_'+field+'" value="'+field.replaceAll("_", " ").capitalize().prettify().trim()+'" autocomplete="off" spellcheck="false"></td>\r\n'+
	'  <td align="center"><input type="checkbox" class="include_insert" name="include_insert_'+field+'" value="1" checked="checked"></td>\r\n'+
	'  <td align="center"><input type="checkbox" class="include_edit" name="include_edit_'+field+'" value="1" checked="checked"></td>\r\n'+
	'  <td align="center"><input type="checkbox" class="include_detail" name="include_detail_'+field+'" value="1" checked="checked"></td>\r\n'+
	'  <td align="center"><input type="checkbox" class="include_list" name="include_list_'+field+'" value="1" checked="checked"></td>\r\n'+
	'  <td align="center"><input type="checkbox" class="include_key" name="include_key_'+field+'" value="1"></td>\r\n'+
	'  <td align="center"><input type="checkbox" class="include_required" name="include_required_'+field+'" value="1"></td>\r\n'+
	'  <td align="center"><input type="radio" name="element_type_'+field+'" value="text" checked="checked"></td>\r\n'+
	'  <td align="center"><input type="radio" name="element_type_'+field+'" value="textarea"></td>\r\n'+
	'  <td align="center"><input type="radio" name="element_type_'+field+'" value="select"></td>\r\n'+
	'  <td align="center"><input type="radio" name="element_type_'+field+'" value="checkbox"></td>\r\n'+
	'  <td align="center"><input type="checkbox" name="list_filter_'+field+'" value="text" class="list_filter"></td>\r\n'+
	'  <td align="center"><input type="checkbox" name="list_filter_'+field+'" value="select" class="list_filter"></td>\r\n'+
	'  <td>\r\n'+
	generateSelectType(field, args)+
	'  </td>\r\n'+
	'  <td>\r\n'+
	generateSelectFilter(field, args)+
	'  </td>\r\n'+
	'</tr>\r\n';
	return rowHTML;
}
