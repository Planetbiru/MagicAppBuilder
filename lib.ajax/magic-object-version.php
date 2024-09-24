<?php
function isValidDomPath($path)
{
    return $path != null && $path->item(0) != null;
}
$html = file_get_contents("https://packagist.org/packages/planetbiru/magic-app");
$doc = new DOMDocument();
@$doc->loadHTML($html);
$versionPath = new DOMXPath($doc);
$versionList = $versionPath->query("//*[contains(@class, 'versions')]/ul");
$versions = array();
if(isValidDomPath($versionList))
{
    foreach($versionList->item(0)->childNodes as $child)
    {
        if(isset($child))
        {
            $version = trim($child->textContent);
            if(!empty($version) && stripos($version, 'dev-') === false)
            {
                $versions[] = $version;
            }
        }
    }
}
header('Content-type: application/json');
echo json_encode($versions);