<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

header('Content-type: application/json; charset=utf-8');
try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    $option = $inputPost->getOption();

    $applicationToUpdate = new EntityApplication(null, $databaseBuilder);

    if(isset($option) && !empty($option) && isset($appId) && !empty($appId))
    {
        $applicationToUpdate->find($appId);
        $appConfig = new SecretObject(null);
        $projectDirectory = $applicationToUpdate->getProjectDirectory();
        $yml = $projectDirectory . "/default.yml";
        if(file_exists($yml))
        {
            $appConfig->loadYamlFile($yml, false, true, true);
        }
        else
        {
            // Fallback to template if config not found
            $appConfig->loadYamlFile($configTemplatePath, false, true, true);
        }
        
        if($appConfig->getApplication() != null)
        {
            $appConf = $appConfig->getApplication();
            $baseApplicationDirectory = $appConf->getBaseApplicationDirectory();
            $composerBaseDirectory = $baseApplicationDirectory . '/inc.lib';

            $phpPath = trim($builderConfig->getPhpPath());
            if(empty($phpPath))
            {
                $phpPath = "php";
            }
    
            if($option == 'update-magic-object')
            {
                $cmd = sprintf('cd %s && %s composer.phar update planetbiru/magic-object --ignore-platform-reqs', escapeshellarg($composerBaseDirectory), escapeshellarg($phpPath));
                exec($cmd);
                echo json_encode(array('success' => true));
                exit();
            }
            else if($option == 'update-magic-app')
            {
                $cmd = sprintf('cd %s && %s composer.phar update planetbiru/magic-app --ignore-platform-reqs', escapeshellarg($composerBaseDirectory), escapeshellarg($phpPath));
                exec($cmd);
                echo json_encode(array('success' => true));
                exit();
            }
            else if($option == 'update-composer')
            {
                $cmd = sprintf('cd %s && %s composer.phar update --ignore-platform-reqs', escapeshellarg($composerBaseDirectory), escapeshellarg($phpPath));
                exec($cmd);
                echo json_encode(array('success' => true));
                exit();
            }
            else if($option == 'update-classes')
            {
                $scriptGenerator = new ScriptGenerator();
    
                $sourceDir = dirname(__DIR__)."/inc.lib/classes/MagicAppTemplate";
                $destinationDir = $appConf->getBaseApplicationDirectory()."/inc.lib/classes/".$appConf->getBaseApplicationNamespace();
                $scriptGenerator->copyDirectory($sourceDir, $destinationDir, false, array('php'), function($source, $destination) use ($appConf) {
                    $content = file_get_contents($source);
                    $baseApplicationNamespace = $appConf->baseApplicationNamespace;
                    $content = str_replace('MagicAppTemplate', $baseApplicationNamespace, $content);
                    file_put_contents($destination, $content);
                });
                echo json_encode(array('success' => true));
                exit();
            }
            else
            {
                echo json_encode(array('success' => false));
                exit(); 
            }
        }
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
echo json_encode(array('success' => false));
exit(); 