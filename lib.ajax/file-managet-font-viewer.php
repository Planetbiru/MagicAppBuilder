<?php

use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Exit if the application is not set up
if ($appConfig->getApplication() == null) {
    exit();
}

$inputGet = new InputGet();

try {
    // Get the base directory of the active application
    $baseDirectory = rtrim($activeApplication->getBaseApplicationDirectory(), "/");
    $fontUrl = $inputGet->getFile();
    // Construct the full path
    $file = FileDirUtil::normalizationPath($baseDirectory . "/" . $fontUrl);
    $fontUrl = "file-manager-load-file.php?file=".urlencode($fontUrl);
    $fontName = 'PreviewFont_' . time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <link type="image/x-icon" rel="icon" href="../favicon.ico" />
  <link type="image/x-icon" rel="shortcut icon" href="../favicon.ico" />
  <style>
    body {
      font-family: sans-serif;
      padding: 2em;
      margin: 0;
      padding: 0;
      color: #333333;
    }
    .content{
      padding: 20px;
    }
    h1 {
      text-align: center;
    }
    .preview {
      margin-bottom: 1em;
      padding: 0.7em;
      background: white;
      border-radius: 5px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      font-size: 32px;
      text-align: center;
      <?php if ($fontUrl): ?>
      font-family: '<?php echo $fontName; ?>', sans-serif;
      <?php endif; ?>
    }
    .preview1{
      text-transform: capitalize;
    }
    .preview2{
      text-transform: uppercase;
    }
    .preview3{
      text-transform: lowercase;
    }
    .preview4{
      font-size: 20px;
    }
    .preview5{
      font-size: 16px;
    }
    .preview6{
      font-size: 12px;
    }
    <?php if ($fontUrl): ?>
    @font-face {
      font-family: '<?php echo $fontName; ?>';
      src: url('<?php echo htmlspecialchars($fontUrl, ENT_QUOTES); ?>');
    }
    <?php endif; ?>
  </style>
  <title>Font Viewer</title>
</head>
<body>
  <div class="content">
    <div class="preview preview0" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
    <div class="preview preview1" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
    <div class="preview preview2" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
    <div class="preview preview3" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
    <div class="preview preview4" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
    <div class="preview preview5" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
    <div class="preview preview6" contenteditable="true" spellcheck="false">The quick brown fox jumps over the lazy dog 0123456789</div>
  </div>
</body>
</html>
<?php
}
catch(Exception $e)
{
  // Do nothing
}