<?php

use MagicObject\Request\InputFiles;
use MagicObject\Request\InputPost;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Initialize POST and FILE input handlers
$inputPost = new InputPost();
$inputFile = new InputFiles();

// Check if the user action is 'preview' and a file has been uploaded
if ($inputPost->getUserAction() == 'preview' && $inputFile->file) {
    $file1 = $inputFile->file;

    // Loop through all uploaded files
    foreach ($file1->getAll() as $fileItem) {
        $temporaryName = $fileItem->getTmpName(); // Temporary path of uploaded file
        $name = $fileItem->getName();             // Original file name
        $size = $fileItem->getSize();             // File size

        $zip = new ZipArchive();

        // Try to open the uploaded ZIP file
        if ($zip->open($temporaryName) === true) {
            // Attempt to read the contents of 'default.yml' inside the ZIP
            $yamlContent = $zip->getFromName('default.yml');

            if ($yamlContent !== false) {
                // Load YAML content into a MagicObject for parsing
                $applicationConfig = new MagicObject();
                $applicationConfig->loadYamlString($yamlContent, false, true, true);
                
                // Check if the YAML contains application information
                if ($applicationConfig->issetApplication()) {
                    ?>
                    <div class="mb-2">Application ID</div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="application_id" value="<?php echo $applicationConfig->getApplication()->getId();?>">
                    </div>
                    <div class="mb-2">Base Application Directory</div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="base_application_directory" value="<?php echo $applicationConfig->getApplication()->getBaseApplicationDirectory();?>">
                    </div>
                    <?php
                } else {
                    // The YAML file does not contain valid application data
                    echo '<div class="alert alert-warning">The file <code>default.yml</code> does not contain application information.</div>';
                }
            } else {
                // The required YAML file was not found in the archive
                echo '<div class="alert alert-warning">The file <code>default.yml</code> was not found in the ZIP archive.</div>';
            }

            $zip->close();
        } else {
            // Failed to open the ZIP file
            echo '<div class="alert alert-danger">Failed to open the ZIP file.</div>';
        }
    }
}
else
{
?>

<!-- Placeholder for showing additional import info or messages -->
<div class="application-import-info"></div>

<!-- File selector button for uploading ZIP project file -->
<div class="application-import-file-selector">
    <button class="btn btn-primary button-select-file-import">
        <i class="fa fa-folder-open"></i> Select File
    </button>
</div>
<?php
}

