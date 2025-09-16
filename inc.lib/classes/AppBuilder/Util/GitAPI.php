<?php

namespace AppBuilder\Util;

use AppBuilder\Exception\PlatformException;
use MagicObject\Util\PicoCurlUtil;

/**
 * GitAPI class allows interaction with GitHub, GitLab, and Bitbucket APIs for repository management.
 * It supports creating repositories, pulling, pushing, switching branches, and checking if a repository exists.
 */
class GitAPI
{
    /**
     * The platform for the API ('github', 'gitlab', or 'bitbucket').
     *
     * @var string
     */
    private $platform; 
    
    /**
     * The personal access token for authentication.
     *
     * @var string
     */
    private $token;   
    
    /**
     * The username for GitHub/GitLab or team name for Bitbucket.
     *
     * @var string
     */
    private $username; 
    
    /**
     * The API base URL for the selected platform.
     *
     * @var string
     */
    private $apiUrl;   

    /**
     * @var PicoCurlUtil
     */
    private $picoCurl;

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
        $this->picoCurl = new PicoCurlUtil();

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
     */
    public function getRequestHeaders()
    {
        // Set headers for authentication based on platform
        $headers = [];
        if ($this->platform == 'github') {
            $headers = [
                "Authorization: token " . $this->token,  // GitHub authentication token
                
            ];
        } elseif ($this->platform == 'gitlab') {
            $headers = [
                "PRIVATE-TOKEN: " . $this->token,        // GitLab private token
            ];
        } else {
            $headers = [
                "Authorization: Bearer " . $this->token, // Bitbucket Bearer token
            ];
        }
        
        $headers[] = "Content-Type: application/json";     // Specify that the content is in JSON format
        $headers[] = "User-Agent: MagicAppBuilder";      // Custom User-Agent header for identifying the application
        
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

        // Use PicoCurlUtil to send the POST request
        $response = $this->picoCurl->post($url, $data, $headers);

        // Return the response as an array
        return json_decode($response, true);
    }
    
    /**
     * Commit changes in the repository.
     *
     * @param string $repoPath The local path of the repository
     * @param string $commitMessage The commit message
     * @param bool $addAll Whether to add all changes (including new, modified, and deleted files) before committing
     * @return array The output of the 'git commit' command
     */
    public function commit($repoPath, $commitMessage, $addAll = false)
    {
        // If $addAll is true, we stage all changes first using 'git add .'
        if ($addAll) {
            exec("cd {$repoPath} && git add .", $output, $resultCode);
            if ($resultCode !== 0) {
                return ['success' => false, 'message' => 'Failed to stage files.', 'output' => $output];
            }
        }

        // Execute the 'git commit' command with the provided commit message
        exec("cd {$repoPath} && git commit -m \"{$commitMessage}\"", $output, $resultCode);
        
        // If the result code is 0, the commit was successful
        if ($resultCode === 0) {
            return ['success' => true, 'message' => "Successfully committed changes with message: {$commitMessage}"];
        } else {
            return ['success' => false, 'message' => 'Failed to commit changes.', 'output' => $output];
        }
    }
    
    /**
     * Discard changes in the repository.
     *
     * @param string $repoPath The local path of the repository
     * @param string|null $filePath The specific file to discard changes (optional). If null, discard all changes.
     * @return array The output of the 'git checkout' or 'git reset' command
     */
    public function discardChanges($repoPath, $filePath = null)
    {
        // If no file path is provided, discard all changes (hard reset)
        if ($filePath === null) {
            exec("cd {$repoPath} && git reset --hard", $output, $resultCode);
        } else {
            // Discard changes for the specific file (checkout)
            exec("cd {$repoPath} && git checkout -- {$filePath}", $output, $resultCode);
        }

        // Check if the operation was successful
        if ($resultCode === 0) {
            return ['success' => true, 'message' => 'Changes discarded successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to discard changes.', 'output' => $output];
        }
    }
    
    /**
     * Stash changes in the repository.
     *
     * @param string $repoPath The local path of the repository
     * @param string $stashMessage The optional message to describe the stash
     * @return array The output of the 'git stash' command
     */
    public function stashChanges($repoPath, $stashMessage = '')
    {
        // Prepare the command to stash changes
        $command = "cd {$repoPath} && git stash";
        
        // If a stash message is provided, include it in the stash command
        if (!empty($stashMessage)) {
            $command .= " save \"{$stashMessage}\"";
        }

        // Execute the stash command
        exec($command, $output, $resultCode);

        // Check if the operation was successful
        if ($resultCode === 0) {
            return ['success' => true, 'message' => 'Changes have been stashed successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to stash changes.', 'output' => $output];
        }
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
     * List branches of a repository from GitHub, GitLab, or Bitbucket.
     *
     * @param string $repoName The name of the repository
     * @return array The list of branches as an array
     */
    public function listBranches($repoName)
    {
        // Construct the URL for listing branches
        $url = $this->apiUrl;

        if ($this->platform == 'github') {
            $url .= "repos/{$this->username}/{$repoName}/branches";
        } elseif ($this->platform == 'gitlab') {
            $url .= "projects/{$repoName}/repository/branches";
        } elseif ($this->platform == 'bitbucket') {
            $url .= "repositories/{$this->username}/{$repoName}/refs/branches";
        }

        // Set headers for authentication
        $headers = $this->getRequestHeaders();

        // Use PicoCurlUtil to send the GET request
        $response = $this->picoCurl->get($url, $headers);

        // Return the response as an array of branches
        $branches = json_decode($response, true);

        // If the response is empty or there is an error, return an empty array
        if (!$branches) {
            return [];
        }

        // Different platforms return different formats, so we process them accordingly
        if ($this->platform == 'github' || $this->platform == 'gitlab') {
            // GitHub and GitLab return an array of branch objects, so we extract the branch names
            return array_map(function ($branch) {
                return $branch['name'];
            }, $branches);
        } elseif ($this->platform == 'bitbucket') {
            // Bitbucket returns a paginated result, we need to handle pagination if necessary
            $branchNames = [];
            do {
                foreach ($branches['values'] as $branch) {
                    $branchNames[] = $branch['name'];
                }
                // If there's a next page, make another request to get more branches
                if (isset($branches['next'])) {
                    $url = $branches['next'];
                    $response = $this->picoCurl->get($url, $headers);
                    $branches = json_decode($response, true);
                } else {
                    break;
                }
            } while (isset($branches['next']));
            
            return $branchNames;
        }

        return [];
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
     * Get the current branch of the repository.
     *
     * @param string $repoPath The local path of the repository
     * @return string The name of the current branch
     */
    public function getCurrentBranch($repoPath)
    {
        exec("cd {$repoPath} && git rev-parse --abbrev-ref HEAD", $output, $resultCode);
        
        if ($resultCode === 0) {
            return trim($output[0]); // Return the current branch name
        } else {
            throw new PlatformException("Failed to get current branch.");
        }
    }
    
    /**
     * Merge a branch into the current branch in the repository.
     *
     * @param string $repoPath The local path of the repository
     * @param string $sourceBranch The name of the branch to merge (source)
     * @return array The output of the 'git merge' command
     */
    public function mergeBranch($repoPath, $sourceBranch)
    {
        // Execute the 'git merge' command to merge the source branch into the current branch
        exec("cd {$repoPath} && git merge {$sourceBranch}", $output, $resultCode);
        
        // If the result code is 0, the merge was successful
        if ($resultCode === 0) {
            return ['success' => true, 'message' => "Successfully merged {$sourceBranch} into the current branch."];
        } else {
            return ['success' => false, 'message' => 'Failed to merge branch.', 'output' => $output];
        }
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

        // Use PicoCurlUtil to send the GET request
        $response = $this->picoCurl->get($url, $headers);

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
        
        // Use PicoCurlUtil to send the GET request
        $this->picoCurl->get($url, $headers);
        $httpCode = $this->picoCurl->getHttpCode();

        // A 200 HTTP code indicates the repository exists
        return $httpCode === 200;
    }
    
    /**
     * Change the origin URL of a repository.
     *
     * @param string $repoPath The local path of the repository
     * @param string $newUrl The new origin URL to set
     * @return array The output of the 'git remote set-url' command
     */
    public function changeOriginUrl($repoPath, $newUrl)
    {
        // Execute the 'git remote set-url' command to change the origin URL
        exec("cd {$repoPath} && git remote set-url origin {$newUrl}", $output, $resultCode);
        
        // If the result code is 0, the operation was successful
        if ($resultCode === 0) {
            return ['success' => true, 'message' => 'Origin URL changed successfully.'];
        } else {
            return ['success' => false, 'message' => 'Failed to change origin URL.', 'output' => $output];
        }
    }

}