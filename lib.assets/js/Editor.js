let modified = true;
let cmEditorModule = null;
let cmEditorFile = null;
let cmEditorSQL = null;
let cmEditorSQLExecute = null;
let transEd1 = null;
let transEd2 = null;
let transEd3 = null;
let transEd4 = null;
let currentTab = "";
let lastLine1 = -1;
let lastLine2 = -1;
let focused = {};

function format() {
  let totalLinesModule = cmEditorModule.lineCount();
  cmEditorModule.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesModule });

  let totalLinesFile = cmEditorFile.lineCount();
  cmEditorFile.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesFile });

  let totalLinesSql = cmEditorFile.lineCount();
  cmEditorSQL.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesSql });

  let totalLinesSqlExecute = cmEditorSQLExecute.lineCount();
  cmEditorSQLExecute.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesSqlExecute });

  let totalLinesEntityTran1 = transEd1.lineCount();
  let totalLinesEntityTran2 = transEd2.lineCount();

  transEd1.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran1 });
  transEd2.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran2 });

  let totalLinesEntityTran3 = transEd3.lineCount();
  let totalLinesEntityTran4 = transEd4.lineCount();

  transEd3.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran3 });
  transEd4.autoFormatRange({ line: 0, ch: 0 }, { line: totalLinesEntityTran4 });
}

function isHidden(el) {
  return el.display == 'none' ||
    el.visibility == 'hidden';
}

jQuery(function () {

  $('#modal-query-executor').on('shown.bs.modal', function () {
    cmEditorSQLExecute.refresh();
    cmEditorSQLExecute.focus();
    $('.button-execute-query')[0].disabled = false;
  });
  $('#maintab').on("shown.bs.tab", function (e) {
    let currId = $(e.target).attr("id");
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
      transEd1.refresh();
      transEd2.refresh();
    }

    if (currId == 'translate-application-tab') {
      transEd3.refresh();
      transEd4.refresh();
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

  cmEditorSQLExecute = CodeMirror.fromTextArea(
    document.querySelector("#query_to_execute"),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
    }
  );

  transEd1 = CodeMirror.fromTextArea(
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

  transEd2 = CodeMirror.fromTextArea(
    document.querySelector('.entity-translate-target'),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
    }
  );

  transEd1.on('focus', function () {
    focused['transEd1'] = true;
    hilightLine1();
  });
  transEd2.on('focus', function () {
    focused['transEd2'] = true;
    hilightLine2();
  });
  transEd1.on('cursorActivity', function () {
    if (typeof focused['transEd1'] != 'undefined') {
      hilightLine1();
    }
  });

  transEd2.on('cursorActivity', function () {
    if (typeof focused['transEd2'] != 'undefined') {
      hilightLine2();
    }
  });

  transEd3 = CodeMirror.fromTextArea(
    document.querySelector('.module-translate-original'),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
      readOnly: true
    }
  );

  transEd4 = CodeMirror.fromTextArea(
    document.querySelector('.module-translate-target'),
    {
      lineNumbers: true,
      lineWrapping: true,
      matchBrackets: true,
      indentUnit: 4,
      indentWithTabs: true,
    }
  );

  transEd3.on('focus', function () {
    focused['transEd3'] = true;
    hilightLine3();
  });
  transEd4.on('focus', function () {
    focused['transEd4'] = true;
    hilightLine4();
  });
  transEd3.on('cursorActivity', function () {
    if (typeof focused['transEd3'] != 'undefined') {
      hilightLine3();
    }
  });

  transEd4.on('cursorActivity', function () {
    if (typeof focused['transEd4'] != 'undefined') {
      hilightLine4();
    }
  });

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

    cmEditorSQLExecute.setOption("mode", specSQL);
    CodeMirror.autoLoadMode(cmEditorSQLExecute, modeSQL);
    setTimeout(function () {
      cmEditorSQLExecute.refresh();
    }, 1);
  }

});

function hilightLine1() {
  let cursor = transEd1.getCursor();
  let lineNumber = cursor.line;

  transEd1.removeLineClass(lastLine1, 'background', 'highlight-line');
  transEd2.removeLineClass(lastLine1, 'background', 'highlight-line');

  transEd2.addLineClass(lineNumber, 'background', 'highlight-line');
  transEd1.addLineClass(lineNumber, 'background', 'highlight-line');

  lastLine1 = lineNumber;
}

function hilightLine2() {
  let cursor = transEd2.getCursor();
  let lineNumber = cursor.line;

  transEd1.removeLineClass(lastLine1, 'background', 'highlight-line');
  transEd2.removeLineClass(lastLine1, 'background', 'highlight-line');

  transEd2.addLineClass(lineNumber, 'background', 'highlight-line');
  transEd1.addLineClass(lineNumber, 'background', 'highlight-line');

  lastLine1 = lineNumber;
}

function hilightLine3() {
  let cursor = transEd3.getCursor();
  let lineNumber = cursor.line;

  transEd3.removeLineClass(lastLine1, 'background', 'highlight-line');
  transEd4.removeLineClass(lastLine1, 'background', 'highlight-line');

  transEd4.addLineClass(lineNumber, 'background', 'highlight-line');
  transEd3.addLineClass(lineNumber, 'background', 'highlight-line');

  lastLine1 = lineNumber;
}

function hilightLine4() {
  let cursor = transEd4.getCursor();
  let lineNumber = cursor.line;

  transEd3.removeLineClass(lastLine1, 'background', 'highlight-line');
  transEd4.removeLineClass(lastLine1, 'background', 'highlight-line');

  transEd4.addLineClass(lineNumber, 'background', 'highlight-line');
  transEd3.addLineClass(lineNumber, 'background', 'highlight-line');

  lastLine1 = lineNumber;
}