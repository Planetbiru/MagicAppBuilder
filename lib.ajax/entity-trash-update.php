<?php

use AppBuilder\AppDatabase;
use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Database\PicoDatabase;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$application = new EntityApplication(null, $databaseBuilder);
try
{
    $application->findOneByApplicationId($applicationId);
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    $menuAppConfig = new SecretObject();
    if(file_exists($appConfigPath))
    {
        $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
    }
    
    // Database connection for the application
    $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
    try
    {
        $database->connect();
        $databaseType = $database->getDatabaseType();
        $schemaName = $databaseConfig->getDatabaseSchema();
        $databaseName = $databaseConfig->getDatabaseName();

        $validTrashTables = AppDatabase::getValidTashTable($appConfig, $databaseConfig, $database, $databaseName, $schemaName);    
        
        if($inputPost->getUserAction() == 'delete' && $inputPost->countableTable())
        {
            $path = $application->getBaseApplicationDirectory()."/inc.cfg/trash.yml";
            $trash = new SecretObject();
            if(file_exists($path))
            {
                $trash->loadYamlFile($path, false, true, true);
            }
            $tables = $inputPost->getTable();
            $trashEntity = array();
            $deletedTrashEntities = array();
            foreach($tables as $table)
            {
                list($primaryTable, $trashTable) = explode("|", $table);
                if(isset($validTrashTables[$primaryTable]) && $validTrashTables[$primaryTable] == $trashTable)
                {
                    // Logic to create entity from trash table
                    // This is where you would implement the restoration logic
                    // For example, you might call a method to restore the entity
                    // from the trash table to the primary table.

                    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
                    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
                    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
                    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));

                    $primaryEntityName = PicoStringUtil::upperCamelize($primaryTable);
                    $trashEntityName = PicoStringUtil::upperCamelize($trashTable);
                    $path1 = $baseDir.DIRECTORY_SEPARATOR."Trash".DIRECTORY_SEPARATOR.$primaryEntityName.".php";
                    $path2 = $baseDir.DIRECTORY_SEPARATOR."Trash".DIRECTORY_SEPARATOR.$trashEntityName.".php";

                    if(file_exists($path1))
                    {
                        unlink($path1);
                    }
                    if(file_exists($path2))
                    {
                        unlink($path2);
                    }
                    $deletedTrashEntities[] = $trashEntityName;
                }
            }

            if($trash->issetTrashEntity() && is_array($trash->getTrashEntity()))
            {
                $newTrashEntities = array();
                foreach($trash->getTrashEntity() as $item)
                {
                    if(!in_array($item->getName(), $deletedTrashEntities))
                    {
                        $newTrashEntities[] = $item;
                    }
                }
                $trash->setTrashEntity($newTrashEntities);
            }
            
            file_put_contents($path, $trash->dumpYaml());
        }    
        else if($inputPost->getUserAction() == 'update' && $inputPost->countableTable())
        {
            $tables = $inputPost->getTable();

            $trashEntity = array();
            foreach($tables as $table)
            {
                list($primaryTable, $trashTable) = explode("|", $table);
                if(isset($validTrashTables[$primaryTable]) && $validTrashTables[$primaryTable] == $trashTable)
                {
                    // Logic to create entity from trash table
                    // This is where you would implement the restoration logic
                    // For example, you might call a method to restore the entity
                    // from the trash table to the primary table.

                    $baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
                    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
                    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
                    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));

                    $primaryEntityName = PicoStringUtil::upperCamelize($primaryTable);
                    $trashEntityName = PicoStringUtil::upperCamelize($trashTable);

                    

                    $baseDir = $baseDirectory;
                    $baseEntity = $baseEntity."\\Trash";



                    // Generate the entity files for the primary table
                    $generator = new PicoEntityGenerator($database, $baseDir, $primaryTable, $baseEntity, $primaryEntityName, true);
                    $generator->generate();

                    // Generate the entity files for the trash table
                    $generator = new PicoEntityGenerator($database, $baseDir, $trashTable, $baseEntity, $trashEntityName, true);
                    $generator->generate();
                    
                    $trashEntity[] = new SecretObject([
                        'name' => $trashEntityName,
                        'label' => $trashEntityName,
                        'description' => $trashEntityName,
                        'namespace' => $baseEntity
                    ]);
                }
            }
            $trash = new SecretObject();
            $trash->setTrashEntity($trashEntity);

            $path = $application->getBaseApplicationDirectory()."/inc.cfg/trash.yml";
            file_put_contents($path, $trash->dumpYaml());
        }
    }
    catch(Exception $e) {
        // Handle database connection error
    }
}
catch(Exception $e)
{
    // Log the error for debugging purposes
}  

require_once __DIR__ . "/entity-trash-list.php";