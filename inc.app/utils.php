<?php

/**
 * Gets the base directory of a URI, handling trailing slashes correctly.
 *
 * This function ensures that if the URI ends with a slash, the slash is preserved.
 * Otherwise, it returns the directory name of the URI, normalizing backslashes to forward slashes.
 * This is useful for constructing base URLs for assets.
 *
 * @param string $uri The URI string to process (e.g., "/my/app/", "/my/app/page.php").
 * @return string The base directory of the URI.
 */
function basenameRequestUri($uri)
{
    if(substr($uri, strlen($uri) - 1, 1) == "/")
    {
        return $uri;
    }
    else
    {
        return str_replace("\\", "/", dirname($uri));
    }
}