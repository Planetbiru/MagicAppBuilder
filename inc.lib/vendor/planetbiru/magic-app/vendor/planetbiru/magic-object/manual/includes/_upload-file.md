## Upload File

Uploading lots of files with arrays is difficult for some developers, especially novice developers. There is a significant difference between uploading a single file and multiple files.

When the developer decides to change the form from single file to multiple files or vice versa, the backend developer must change the code to handle the uploaded files.

```html
<!-- single file -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload" type="file" />
     <input type="submit" />
</form>
```

```html
<!-- multiple files -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload[]" type="file" webkitdirectory multiple />
     <input type="submit" />
</form>
```

- For single uploads, the input field is named `myupload`.
- For multiple uploads, the input field name is `myupload[]`, which allows multiple files to be uploaded at once.

```php
<?php

use MagicObject\File\PicoUplodFile;

require_once "vendor/autoload.php";

$files = new PicoUplodFile();

$file1 = $files->get('myupload');
// or 
// $file1 = $files->myupload;

$targetDir = __DIR__;

foreach($file1->getAll() as $fileItem)
{
	$temporaryName = $fileItem->getTmpName();
	$name = $fileItem->getName();
	$size = $fileItem->getSize();
	echo "$name | $temporaryName | $size\r\n";
	move_uploaded_file($temporaryName, $targetDir."/".$name);
}

```

- The `PicoUplodFile` class simplifies file handling. The developer can retrieve the uploaded files easily using the get method.
- The `getAll` method retrieves all files, regardless of whether they were uploaded via single or multiple file forms.

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

This implementation offers a straightforward way to manage file uploads in PHP, abstracting complexities for developers. By using methods like getAll() and isMultiple(), developers can seamlessly handle both types of uploads without needing to write separate logic for each scenario. This approach not only improves code maintainability but also enhances the developer experience.