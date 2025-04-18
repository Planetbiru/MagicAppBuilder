<?php

namespace MagicObject\File;

/**
 * Class PicoDownloadFile
 *
 * Facilitates downloading a file, with support for partial content (range requests). 
 * This class ensures that requested files exist, handles errors, and supports downloading large files
 * efficiently by sending them in chunks. 
 *
 * @author Kamshory
 * @package MagicObject\File
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDownloadFile
{
    /**
     * @var string The path to the file being downloaded.
     */
    private $filepath;

    /**
     * @var string The filename to be used in the download response.
     */
    private $filename;

    /**
     * PicoDownloadFile constructor.
     *
     * @param string $filepath The full path to the file.
     * @param string|null $filename The name of the file for download (optional).
     */
    public function __construct($filepath, $filename = null)
    {
        $this->filepath = $filepath;
        $this->filename = $filename ?: basename($filepath); // Use basename if no filename provided
    }

    /**
     * Initiates the download of the file with support for partial content (range requests).
     *
     * Handles the following:
     * - Verifies the file exists at the specified path.
     * - Supports byte range requests for resuming downloads.
     * - Sends appropriate HTTP headers for file transfer.
     * - Streams the file to the client in chunks (8 KB by default).
     *
     * @param bool $exit Whether to terminate the script after sending the file. Default is `false`.
     * 
     * @return bool Returns `true` if the entire file was successfully sent, `false` if only part of the file was sent.
     */
    public function download($exit = false) // NOSONAR
    {
        if (!$this->fileExists()) {
            $this->sendError(404, "File not found.");
            return false;
        }

        $fileSize = filesize($this->filepath);
        list($start, $end) = $this->getRange($fileSize);

        if ($this->isInvalidRange($start, $end, $fileSize)) {
            $this->sendError(416, "Range Not Satisfiable", $fileSize);
            return false;
        }

        $this->sendHeaders($start, $end, $fileSize);

        $fp = fopen($this->filepath, 'rb');
        if ($fp === false) {
            $this->sendError(500, "Failed to open file.");
            return false;
        }
        
        $this->streamFile($fp, $start, $end);

        fclose($fp);

        if ($exit) {
            exit;
        }

        return $end === ($fileSize - 1); // Return true if the whole file was sent
    }

    /**
     * Checks if the file exists.
     *
     * @return bool true if the file exists, false otherwise.
     */
    private function fileExists()
    {
        return file_exists($this->filepath);
    }

    /**
     * Sends an error response with the given status code and message.
     *
     * @param int $statusCode The HTTP status code.
     * @param string $message The error message.
     * @param int|null $fileSize The file size to include in the Content-Range header (optional).
     */
    private function sendError($statusCode, $message, $fileSize = null)
    {
        header("HTTP/1.1 $statusCode $message");
        if ($fileSize !== null) {
            header("Content-Range: bytes 0-0/$fileSize");
        }
        echo $message;
    }

    /**
     * Determines the byte range from the HTTP_RANGE header.
     *
     * @param int $fileSize The size of the file.
     * @return array The start and end byte positions for the range.
     */
    private function getRange($fileSize)
    {
        if (isset($_SERVER['HTTP_RANGE'])) {
            list($range, $extra) = explode(',', $_SERVER['HTTP_RANGE'], 2); // NOSONAR
            list($start, $end) = explode('-', $range);
            $start = max(0, (int)$start);
            $end = $end ? (int)$end : $fileSize - 1;
        } else {
            $start = 0;
            $end = $fileSize - 1;
        }

        return array($start, $end);
    }

    /**
     * Checks if the byte range is valid.
     *
     * @param int $start The start byte.
     * @param int $end The end byte.
     * @param int $fileSize The total size of the file.
     * @return bool true if the range is invalid.
     */
    private function isInvalidRange($start, $end, $fileSize)
    {
        return $start > $end || $start >= $fileSize || $end >= $fileSize;
    }

    /**
     * Sends the appropriate HTTP headers for the download.
     *
     * @param int $start The start byte.
     * @param int $end The end byte.
     * @param int $fileSize The total size of the file.
     */
    private function sendHeaders($start, $end, $fileSize)
    {
        header('HTTP/1.1 206 Partial Content');
        header("Content-Type: application/octet-stream");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . $this->filename . "\"");
        header("Content-Range: bytes $start-$end/$fileSize");
        header("Content-Length: " . ($end - $start + 1));
        header("Accept-Ranges: bytes");
    }

    /**
     * Streams the file to the client in chunks.
     *
     * @param resource $fp The file pointer.
     * @param int $start The start byte.
     * @param int $end The end byte.
     */
    private function streamFile($fp, $start, $end)
    {
        $bufferSize = 1024 * 8; // 8 KB buffer size
        fseek($fp, $start);
        while (!feof($fp) && ftell($fp) <= $end) {
            echo fread($fp, $bufferSize);
            flush();
        }
    }
}
