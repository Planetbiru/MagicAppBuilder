<?php

namespace AppBuilder\Util;

use AppBuilder\Exception\PlatformException;
use Exception;

/**
 * GitAPI class allows interaction with GitHub and GitLab API for repository management.
 * It supports creating repositories, pulling, pushing, and switching branches.
 */
class GitAPI
{
    private $platform; // 'github' or 'gitlab'
    private $token;    // Personal access token for authentication
    private $username; // GitHub username for identifying repositories
    private $apiUrl;  // GitHub or GitLab API base URL

    /**
     * Constructor to initialize the API with platform, token, and optional username (for GitHub).
     *
     * @param string $platform The platform ('github' or 'gitlab')
     * @param string $token The personal access token
     * @param string|null $username The GitHub username (required for GitHub)
     */
    public function __construct($platform, $token, $username = null)
    {
        // Set platform and token
        $this->platform = strtolower($platform);
        $this->token = $token;
        $this->username = $username;

        // Set API base URL depending on the platform
        if ($this->platform == 'github') {
            $this->apiUrl = 'https://api.github.com/';
        } elseif ($this->platform == 'gitlab') {
            $this->apiUrl = 'https://gitlab.com/api/v4/';
        } else {
            throw new PlatformException("Unsupported platform: {$platform}. Supported platforms are 'github' and 'gitlab'.");
        }
    }

    /**
     * Create a new repository on the selected platform (GitHub or GitLab).
     *
     * @param string $repoName The name of the new repository
     * @return array The response from the API in array format
     */
    public function createRepository($repoName)
    {
        $url = $this->apiUrl . ($this->platform == 'github' ? 'user/repos' : 'projects');
        $data = json_encode([
            'name' => $repoName,
            'visibility' => 'public', // Change to 'private' for private repo
        ]);
        
        // Set headers for authentication
        if ($this->platform == 'github') {
            $headers = [
                "Authorization: token " . $this->token,
                "Content-Type: application/json",
                "User-Agent: MagicAppBuilder"
            ];
        } else {
            $headers = [
                "PRIVATE-TOKEN: " . $this->token,
                "Content-Type: application/json"
            ];
        }

        // Send POST request to the API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);

        // Return the response as an array
        return json_decode($response, true);
    }

    /**
     * Pull changes from the specified repository.
     *
     * @param string $repoPath The local path of the repository
     * @return array The output of the 'git pull' command
     */
    public function pull($repoPath)
    {
        exec("cd {$repoPath} && git pull", $output, $resultCode);
        return $output; // Return the output of the 'git pull' command
    }

    /**
     * Push changes to the specified repository.
     *
     * @param string $repoPath The local path of the repository
     * @return array The output of the 'git push' command
     */
    public function push($repoPath)
    {
        exec("cd {$repoPath} && git push", $output, $resultCode);
        return $output; // Return the output of the 'git push' command
    }

    /**
     * Switch to a different branch in the repository.
     *
     * @param string $repoPath The local path of the repository
     * @param string $branchName The name of the branch to switch to
     * @return array The output of the 'git checkout' command
     */
    public function switchBranch($repoPath, $branchName)
    {
        exec("cd {$repoPath} && git checkout {$branchName}", $output, $resultCode);
        return $output; // Return the output of the 'git checkout' command
    }

    /**
     * Get repository information from either GitHub or GitLab.
     *
     * @param string $repoName The name of the repository
     * @return array The repository information as an array
     */
    public function getRepositoryInfo($repoName)
    {
        // Construct the URL for the repository information API endpoint
        $url = $this->apiUrl . ($this->platform == 'github' ? "repos/{$this->username}/{$repoName}" : "projects/{$repoName}");
        
        // Set headers for authentication
        if ($this->platform == 'github') {
            $headers = [
                "Authorization: token " . $this->token,
                "Content-Type: application/json",
                "User-Agent: PHP"
            ];
        } else {
            $headers = [
                "PRIVATE-TOKEN: " . $this->token,
                "Content-Type: application/json"
            ];
        }

        // Send GET request to fetch repository info
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        // Return the response as an array
        return json_decode($response, true);
    }
}

// Example Usage:

try {
    // Initialize GitHub API
    $gitHub = new GitAPI('github', 'your-github-access-token', 'your-github-username');
    $repoResponse = $gitHub->createRepository('new-github-repository');
    echo json_encode($repoResponse);

    // Initialize GitLab API
    $gitLab = new GitAPI('gitlab', 'your-gitlab-access-token');
    $repoResponse = $gitLab->createRepository('new-gitlab-repository');
    echo json_encode($repoResponse);
    
    // Pull changes from a GitHub repository
    $output = $gitHub->pull('/path/to/your/github/repo');
    echo implode("\n", $output);
    
    // Push changes to a GitLab repository
    $output = $gitLab->push('/path/to/your/gitlab/repo');
    echo implode("\n", $output);
    
    // Switch branch on GitHub repository
    $output = $gitHub->switchBranch('/path/to/your/github/repo', 'main');
    echo implode("\n", $output);
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

