## File Upload

### Overview

Uploading files can be challenging, especially for novice developers. This guide explains how to manage single and multiple file uploads in PHP, highlighting the differences and providing straightforward examples.

### Key Features

-    **Easy File Handling:** The `PicoUploadFile` class simplifies the process of retrieving uploaded files.
-    **Unified Retrieval:** The `getAll` method allows you to handle files uploaded through both single and multiple forms without needing separate logic.

### Uploading Files

#### Single File Upload

To upload a single file, use the following HTML form:

```html
<!-- single file -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload" type="file" />
     <input type="submit" />
</form>
```

#### Multiple File Upload

To enable multiple file uploads, modify the input name to include brackets (`[]`) and use the `multiple` attribute:

```html
<!-- multiple files -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload[]" type="file" webkitdirectory multiple />
     <input type="submit" />
</form>
```

- For single uploads, the input field is named `myupload`.
- For multiple uploads, the input field name is `myupload[]`, which allows multiple files to be uploaded at once.

### PHP Backend Handling

To handle the uploaded files on the server side, you can use the PicoUploadFile class. Here’s how to do it:

```php
<?php

use MagicObject\File\PicoUploadFile;

require_once "vendor/autoload.php";

$files = new PicoUploadFile();

$file1 = $files->get('myupload');
// or alternatively
// $file1 = $files->myupload;
// or
// $file1 = $files->getMyupload();

$targetDir = __DIR__;

foreach ($file1->getAll() as $fileItem) {
    $temporaryName = $fileItem->getTmpName();
    $name = $fileItem->getName();
    $size = $fileItem->getSize();
    
    echo "$name | $temporaryName | $size\r\n";
    move_uploaded_file($temporaryName, $targetDir . "/" . $name);
}
```

### Checking Upload Type

Developers simply retrieve data using the `getAll` method and developers will get all files uploaded by users either via single file or multiple file forms. If necessary, the developer can check whether the file was uploaded using a single file or multiple file form with the `isMultiple()` method

```php

if($file1->isMultiple())
{
	// do something here
}
else
{
	// do something here
}
```

### Summary

This implementation offers a straightforward way to manage file uploads in PHP, abstracting complexities for developers. By using methods like `getAll()` and `isMultiple()`, developers can seamlessly handle both types of uploads without needing to write separate logic for each scenario. This approach not only improves code maintainability but also enhances the developer experience.