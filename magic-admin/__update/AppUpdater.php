<?php

/**
 * Exception class for update-related errors.
 */
class UpdateException extends Exception
{
}

/**
 * AppUpdater
 *
 * A class to safely update an application from a GitHub repository ZIP archive.
 * It downloads the release, reads the contents directly from the ZIP,
 * and writes the files into the current application directory.
 *
 * Designed for use in environments without Git installed.
 */
class AppUpdater
{
    private $repoOwner;
    private $repoName;
    private $tag;
    private $zipUrl;
    private $appDir;
    private $storageDir;
    private $zipFile;

    /**
     * Constructor
     *
     * @param string $repoOwner GitHub repository owner (username or organization)
     * @param string $repoName  Repository name
     * @param string $tag       Release tag (or 'latest' for main branch)
     */
    public function __construct($repoOwner, $repoName, $tag = 'latest')
    {
        $this->repoOwner = $repoOwner;
        $this->repoName = $repoName;
        $this->tag = $tag;

        $this->appDir = dirname(dirname(dirname(__FILE__)));
        $this->storageDir = dirname(__FILE__) . '/storage';
        $this->zipFile = $this->storageDir . '/update.zip';

        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        $this->zipUrl = ($tag === 'latest')
            ? "https://github.com/{$repoOwner}/{$repoName}/archive/refs/heads/main.zip"
            : "https://github.com/{$repoOwner}/{$repoName}/archive/refs/tags/{$tag}.zip";
    }

    /**
     * Get a list of available releases from GitHub (version >= 1.5.1 only).
     *
     * @return array An array of releases with 'tag_name', 'name', 'published_at', and 'zipball_url'
     * @throws UpdateException
     */
    public function listReleases()
    {
        $url = "https://api.github.com/repos/{$this->repoOwner}/{$this->repoName}/releases";

        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: PHP-Updater\r\n"
            )
        );

        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new UpdateException("Failed to fetch release list from GitHub.");
        }

        $releases = json_decode($response, true);
        if (!is_array($releases)) {
            throw new UpdateException("Invalid response format from GitHub.");
        }

        $result = array();
        foreach ($releases as $release) {
            $tag = isset($release['tag_name']) ? $release['tag_name'] : '';

            // Skip if version is lower than 1.5.1
            if (version_compare(ltrim($tag, 'v'), '1.6.0', '<')) {
                continue;
            }

            $result[] = array(
                'tag_name'    => $tag,
                'name'        => isset($release['name']) ? $release['name'] : '',
                'published_at'=> isset($release['published_at']) ? $release['published_at'] : '',
                'zipball_url' => isset($release['zipball_url']) ? $release['zipball_url'] : ''
            );
        }

        return $result;
    }



    /**
     * Run the update process.
     */
    public function update()
    {
        $this->downloadZip();
        $this->replaceFromZip();
        $this->cleanUp();
    }

    /**
     * Download the ZIP archive from GitHub.
     */
    public function downloadZip()
    {
        $data = file_get_contents($this->zipUrl);
        if ($data === false) {
            throw new UpdateException("Failed to download ZIP file.");
        }
        file_put_contents($this->zipFile, $data);
    }

    /**
     * Extract and replace application files by reading them directly from ZIP.
     *
     * @throws UpdateException
     */
    public function replaceFromZip()
    {
        $zip = new ZipArchive();
        if ($zip->open($this->zipFile) !== true) {
            throw new UpdateException("Unable to open ZIP archive.");
        }

        // Detect the base folder (e.g., myrepo-main/)
        $firstFile = $zip->getNameIndex(0);
        $baseFolder = '';
        $parts = explode('/', $firstFile);
        if (count($parts) > 1) {
            $baseFolder = $parts[0] . '/';
        } else {
            throw new UpdateException("Unexpected ZIP structure.");
        }

        // Loop through each file in the ZIP
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            // Skip directory entries
            if (substr($entry, -1) === '/') {
                continue;
            }

            // Calculate relative path
            if (strpos($entry, $baseFolder) !== 0) {
                continue;
            }

            $relativePath = substr($entry, strlen($baseFolder));
            $targetPath = $this->appDir . '/' . $relativePath;

            // Optionally skip certain files (e.g., updater itself)
            $basename = basename($targetPath);
            if (in_array($basename, ['update.php', '.env'])) {
                continue;
            }

            // Ensure directory exists
            $targetDir = dirname($targetPath);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Extract and write file
            $content = $zip->getFromIndex($i);
            if ($content === false) {
                throw new UpdateException("Failed to extract $entry from ZIP.");
            }

            file_put_contents($targetPath, $content);
        }

        $zip->close();
    }

    /**
     * Clean up downloaded ZIP file.
     */
    public function cleanUp()
    {
        @unlink($this->zipFile);
    }
}


