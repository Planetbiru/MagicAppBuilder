<?php

namespace AppBuilder\Util;

use AppBuilder\Exception\PlatformException;
use Exception;

/**
 * GitAPI class allows interaction with GitHub, GitLab, and Bitbucket APIs for repository management.
 * It supports creating repositories, pulling, pushing, switching branches, and checking if a repository exists.
 */
class GitAPI
{
    private $platform; // 'github', 'gitlab', or 'bitbucket'
    private $token;    // Personal access token for authentication
    private $username; // GitHub/GitLab username or Bitbucket team for identifying repositories
    private $apiUrl;   // GitHub, GitLab, or Bitbucket API base URL

    /**
     * Constructor to initialize the API with platform, token, and optional username (for GitHub and Bitbucket).
     *
     * @param string $platform The platform ('github', 'gitlab', or 'bitbucket')
     * @param string $token The personal access token
     * @param string|null $username The GitHub/GitLab username or Bitbucket team (required for GitHub and Bitbucket)
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
        } elseif ($this->platform == 'bitbucket') {
            $this->apiUrl = 'https://api.bitbucket.org/2.0/';
        } else {
            throw new PlatformException("Unsupported platform: {$platform}. Supported platforms are 'github', 'gitlab', and 'bitbucket'.");
        }
    }

    /**
     * Get the headers required for making API requests to GitHub, GitLab, or Bitbucket.
     * These headers include authentication tokens and content type specifications.
     *
     * @return array An associative array of request headers.
     *              For GitHub: Authorization token, Content-Type, and User-Agent.
     *              For GitLab: PRIVATE-TOKEN, Content-Type, and User-Agent.
     *              For Bitbucket: Authorization header, Content-Type, and User-Agent.
     */
    public function getRequestHeaders()
    {
        // Set headers for authentication based on platform
        $headers = [];
        if ($this->platform == 'github') {
            $headers = [
                "Authorization: token " . $this->token,   // GitHub authentication token
                
            ];
        } elseif ($this->platform == 'gitlab') {
            $headers = [
                "PRIVATE-TOKEN: " . $this->token,         // GitLab private token
            ];
        } else {
            $headers = [
                "Authorization: Bearer " . $this->token,  // Bitbucket Bearer token
            ];
        }
        
        $headers[] = "Content-Type: application/json";    // Specify that the content is in JSON format
        $headers[] = "User-Agent: MagicAppBuilder";       // Custom User-Agent header for identifying the application
        
        return $headers;
    }

    /**
     * Create a new repository on the selected platform (GitHub, GitLab, or Bitbucket).
     *
     * @param string $repoName The name of the new repository
     * @param string $visibility Visibility ('public' or 'private')
     * @return array The response from the API in array format
     */
    public function createRepository($repoName, $visibility = 'public')
    {
        $url = $this->apiUrl;

        // Set platform-specific repository creation endpoint and data
        if ($this->platform == 'github') {
            $url .= 'user/repos';
            $data = json_encode([
                'name' => $repoName,
                'private' => $visibility == 'private',
            ]);
        } elseif ($this->platform == 'gitlab') {
            $url .= 'projects';
            $data = json_encode([
                'name' => $repoName,
                'visibility' => $visibility,
            ]);
        } elseif ($this->platform == 'bitbucket') {
            $url .= 'repositories/' . $this->username . '/' . $repoName;
            $data = json_encode([
                'scm' => 'git',
                'is_private' => $visibility == 'private',
            ]);
        }

        // Set headers for authentication
        $headers = $this->getRequestHeaders();

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
     * Get repository information from either GitHub, GitLab, or Bitbucket.
     *
     * @param string $repoName The name of the repository
     * @return array The repository information as an array
     */
    public function getRepositoryInfo($repoName)
    {
        // Construct the URL for the repository information API endpoint
        $url = $this->apiUrl;

        if ($this->platform == 'github') {
            $url .= "repos/{$this->username}/{$repoName}";
        } elseif ($this->platform == 'gitlab') {
            $url .= "projects/{$repoName}";
        } elseif ($this->platform == 'bitbucket') {
            $url .= "repositories/{$this->username}/{$repoName}";
        }

        // Set headers for authentication
        $headers = $this->getRequestHeaders();

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

    /**
     * Check if the repository exists on GitHub, GitLab, or Bitbucket.
     *
     * @param string $repoName The name of the repository
     * @return bool True if the repository exists, false otherwise
     */
    public function repositoryExists($repoName)
    {
        // Construct the URL for checking repository existence
        $url = $this->apiUrl;

        if ($this->platform == 'github') {
            $url .= "repos/{$this->username}/{$repoName}";
        } elseif ($this->platform == 'gitlab') {
            $url .= "projects/{$repoName}";
        } elseif ($this->platform == 'bitbucket') {
            $url .= "repositories/{$this->username}/{$repoName}";
        }

        // Set headers for authentication
        $headers = $this->getRequestHeaders();

        // Send GET request to check repository existence
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        // If response is empty or error, the repository doesn't exist
        $response = json_decode($response, true);
        if ($response && isset($response['message']) && $response['message'] == 'Not Found') {
            return false;
        }

        return true; // Repository exists
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

