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
