let modified = true;
let cmEditorModule = null;
let cmEditorFile = null;
let cmEditorSQL = null;
let trasEd1 = null;
let trasEd2 = null;
let currentTab = "";
function format() {
  let totalLinesModule = cmEditorModule.lineCount();
  cmEditorModule.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesModule });

  let totalLinesFile = cmEditorFile.lineCount();
  cmEditorFile.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesFile });

  let totalLinesSql = cmEditorFile.lineCount();
  cmEditorSQL.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesSql });


  let totalLinesEntityTran1 = trasEd1.lineCount();
  let totalLinesEntityTran2 = trasEd2.lineCount();
  
  trasEd1.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran1 });
  trasEd2.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran2 });
}

function isHidden(el) {
  return el.display == 'none' ||
    el.visibility == 'hidden';
}

$(document).ready(function () {

  $('#maintab').on("shown.bs.tab", function (e) {
    var currId = $(e.target).attr("id");
    currentTab = currId;
    if (currId == 'entity-file-tab') {
      cmEditorFile.focus();
      cmEditorFile.refresh();
    }
    if (currId == 'entity-query-tab') {
      cmEditorSQL.focus();
      cmEditorSQL.refresh();
    }

    if (currId == 'translate-entity-tab') {
      trasEd1.focus();
      trasEd1.refresh();
      trasEd2.focus();
      trasEd2.refresh();
    }
  });

  $(document).on('keydown', function (e) {
    if ((e.which == '115' || e.which == '83') && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      if (currentTab == 'module-file-tab') {
        saveModule();
      }
      if (currentTab == 'entity-file-tab') {
        saveEntity();
      }
      if (currentTab == 'entity-query-tab') {
        saveQuery();
      } return false;
    } else {
      return true;
    }
  });

  CodeMirror.modeURL = "lib.assets/cm/mode/%N/%N.js";

  cmEditorModule = CodeMirror.fromTextArea(
    document.querySelector(".module-file"),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
      extraKeys: {
        "Ctrl-S": function (instance) {
          saveModule();
        }
      }
    }
  );
  cmEditorFile = CodeMirror.fromTextArea(
    document.querySelector(".entity-file"),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
    }
  );

  cmEditorSQL = CodeMirror.fromTextArea(
    document.querySelector(".entity-query"),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
    }
  );

  trasEd1 = CodeMirror.fromTextArea(
    document.querySelector('.entity-translate-original'),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
      readOnly: true
    }
  );

  trasEd2 = CodeMirror.fromTextArea(
    document.querySelector('.entity-translate-target'),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
    }
  );

  let modeModule;
  let specModule;

  let infoModule = CodeMirror.findModeByExtension("php");
  modeModule = infoModule.mode;
  specModule = infoModule.mime;

  if (modeModule) {
    cmEditorModule.setOption("mode", specModule);
    CodeMirror.autoLoadMode(cmEditorModule, modeModule);
    setTimeout(function () {
      cmEditorModule.refresh();
    }, 1);
  }

  let modeFile;
  let specFile;

  let infoFile = CodeMirror.findModeByExtension("php");
  modeFile = infoFile.mode;
  specFile = infoFile.mime;

  if (modeFile) {
    cmEditorFile.setOption("mode", specFile);
    CodeMirror.autoLoadMode(cmEditorFile, modeFile);
    setTimeout(function () {
      cmEditorFile.refresh();
    }, 1);
  }

  let modeSQL;
  let specSQL;

  let infoSQL = CodeMirror.findModeByExtension("sql");
  modeSQL = infoSQL.mode;
  specSQL = infoSQL.mime;

  if (modeSQL) {
    cmEditorSQL.setOption("mode", specSQL);
    CodeMirror.autoLoadMode(cmEditorSQL, modeSQL);
    setTimeout(function () {
      cmEditorSQL.refresh();
    }, 1);
  }

});
