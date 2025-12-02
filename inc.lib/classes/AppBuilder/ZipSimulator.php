<?php

namespace AppBuilder;

/**
 * Class ZipSimulator
 *
 * A lightweight utility class that simulates basic ZIP archive operations
 * by writing files and creating directories directly on the filesystem.
 * This class is intended to mimic the behavior of ZipArchive for environments
 * where ZIP extensions are not available or for simplified generation of file structures.
 */
class ZipSimulator
{
    /**
     * @var string The base directory where all files and folders will be generated.
     */
    private $baseDirectory = '';

    /**
     * Constructor.
     *
     * Initializes the simulator with a base directory. All added files will
     * be placed inside this directory.
     *
     * @param string $baseDirectory The root directory for simulated ZIP content.
     */
    public function __construct($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Add a file to the simulated ZIP archive using a string as the content.
     *
     * Creates necessary directories if they do not exist and writes the
     * provided string into the generated file path.
     *
     * @param string $fileName The target path inside the simulated ZIP.
     * @param string $content  The file content as a string.
     * @return void
     */
    public function addFromString($fileName, $content)
    {
        $path = $this->constructPath($fileName);
        $this->prepareDirectory($path);
        file_put_contents($path, $content);
    }

    /**
     * Add a file to the simulated ZIP archive from an existing source file.
     *
     * Reads the content of the source file and writes it to the corresponding
     * location inside the simulated ZIP structure.
     *
     * @param string $source   The full path of the existing file to copy.
     * @param string $fileName The destination path inside the simulated ZIP.
     * @return void
     */
    public function addFile($source, $fileName)
    {
        $path = $this->constructPath($fileName);
        $this->prepareDirectory($path);
        file_put_contents($path, file_get_contents($source));
    }

    /**
     * Construct the full filesystem path based on the base directory
     * and the file name provided.
     *
     * Ensures consistent directory separators and trims leading slashes.
     *
     * @param string $fileName The file path inside the simulated ZIP.
     * @return string The normalized absolute filesystem path.
     */
    private function constructPath($fileName)
    {
        $fileName = ltrim($fileName, "\\/");
        if (!empty($this->baseDirectory)) {
            $path = rtrim($this->baseDirectory, "\\/") . "/" . ltrim($fileName);
        } else {
            $path = $fileName;
        }
        $path = str_replace(array("//", "\\\\"), "/", $path);
        return $path;
    }

    /**
     * Ensure the directory for a file path exists.
     *
     * Creates directories recursively if needed.
     *
     * @param string $path The path of the file to be created.
     * @return void
     */
    private function prepareDirectory($path)
    {
        $dirname = dirname($path);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
    }

    /**
     * Close the simulated ZIP archive.
     *
     * This is a placeholder method to match ZipArchive's API.
     * No actual closing operations are required.
     *
     * @return void
     */
    public function close()
    {
        // Do nothing
    }

    /**
     * Create an empty directory inside the simulated ZIP structure.
     *
     * @param string $dirname The directory path to create.
     * @return void
     */
    public function addEmptyDir($dirname)
    {
        if (!file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
    }
}
