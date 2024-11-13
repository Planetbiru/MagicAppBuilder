
## Resumable File Download

### Namespace

`MagicObject\File`

### Description

The `PicoDownloadFile` class is designed to facilitate efficient file downloading in PHP, supporting **partial content** (range requests) for large files. It ensures that requested files exist, handles errors gracefully, and enables downloading in chunks to minimize server load and bandwidth consumption, particularly for large files.

The class supports the following:

-   Verifying the existence of the file.
-   Handling byte-range requests for resuming downloads.
-   Sending appropriate HTTP headers to manage the download.
-   Streaming the file to the client in manageable chunks (default size: 8 KB).
-   Returning relevant HTTP status codes and error messages.

This class is ideal for scenarios where large files need to be served to clients and you want to offer functionality like resuming interrupted downloads.


### Constructor

```php
__construct($filepath, $filename = null)
```

**Parameters**:

-   `$filepath` (string): The full path to the file that should be downloaded.
-   `$filename` (string|null, optional): The name of the file for download. If not provided, the filename is extracted from the `filepath` using `basename()`.

**Description**: Initializes the `PicoDownloadFile` object with the path of the file to be downloaded and an optional filename for the download response. If the filename is not specified, the base name of the file is used.

**Example**:

```php
$file = new PicoDownloadFile("/path/to/large-file.zip", "downloaded-file.zip");
```

### Method

```php
download($exit = false)
```

**Parameters**:

-   `$exit` (bool, optional): Whether to terminate the script after sending the file. Default is `false`.

**Returns**:

-   `bool`: Returns `true` if the entire file was successfully sent, `false` if only part of the file was sent (due to range requests).

**Description**: This method is responsible for initiating the file download process. It performs the following:

1.  Verifies the existence of the file.
2.  Handles byte-range requests for partial downloads (useful for resuming interrupted downloads).
3.  Sends the appropriate HTTP headers for the file download.
4.  Streams the file to the client in chunks of 8 KB (by default).

If `$exit` is set to `true`, the script will terminate after the file is sent.

**Example 1**

```php
<?php
require 'vendor/autoload.php'; // Include the PicoDownloadFile class
$path = "/path/to/large-file.zip";
$localName = "downloaded-file.zip";
$file = new PicoDownloadFile($path, $localName);
$file->download(true); // Initiate download and terminate the script after sending
```

**Example 2**

```php
<?php
require 'vendor/autoload.php'; // Include the PicoDownloadFile class
$path = "/path/to/large-file.zip";
$localName = "downloaded-file.zip";
$file = new PicoDownloadFile($path, $localName);
$finished = $file->download(false); // Initiate download without terminate the script after sending
if($finished && file_exists($path))
{
	unlink($path); // Delete file when finish
}
```

### Error Handling

-   **404 - File Not Found**: If the file does not exist at the specified path, a 404 error is returned.
-   **416 - Range Not Satisfiable**: If an invalid byte range is requested (e.g., the start byte is larger than the end byte), a 416 error is returned.
-   **500 - Internal Server Error**: If there is an issue opening the file for reading (e.g., permissions issues), a 500 error is returned.

