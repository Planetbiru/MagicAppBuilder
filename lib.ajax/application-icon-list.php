<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

try
{
    $uploadDir = $application->getBaseApplicationDirectory();
    $path = $uploadDir."/favicon.png";
    if(file_exists($path))
    {
        ?>
        <img class="application-icon-preview" src="data:image/png;base64,<?php echo base64_encode(file_get_contents($path));?>">
        <?php
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
    // Do nothing
}