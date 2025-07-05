<?php

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Generator\PicoDatabaseDump;

// Require essential application and database builder files.
require_once dirname(dirname(__DIR__)) . "/inc.app/app.php";
require_once dirname(dirname(__DIR__)) . "/inc.app/database-builder.php";

// Initialize an empty array to store entity objects, indexed by table name.
$entities = [];

// Get all PHP files within the AppBuilder/EntityInstaller directory.
// These files are expected to define database entities.
$paths = glob(dirname(dirname(__DIR__))."/inc.lib/classes/AppBuilder/EntityInstaller/*.php");
// Define the base namespace for the entity classes.
$baseEntity = "AppBuilder\\EntityInstaller";

// Loop through each found entity file.
foreach($paths as $idx=>$path)
{
    // Extract the entity name (class name) from the file path.
    $entityName = basename($path, ".php");
    
    // Construct the full class name with its namespace.
    $className = "\\".$baseEntity."\\".$entityName;
    // Trim any whitespace from the entity name.
    $entityName = trim($entityName);
    
    // Check if the entity file actually exists.
    if(file_exists($path))
    {
        // Perform an error check on the PHP file to ensure it's valid.
        $phpError = ErrorChecker::errorCheck($databaseBuilder, $path, "-");
        $returnVar = intval($phpError->errorCode);

        // If no PHP errors are found (error code is 0).
        if($returnVar == 0)
        {
            // Include the entity file to make its class available.
            include_once $path; 
                                
            // Create a new instance of the entity class.
            $entity = new $className(null, $databaseBuilder);
            // Get table information, including the table name, from the entity definition.
            $entityInfo = EntityUtil::getTableName($path);
            // Determine the table name; use a fallback index if not explicitly defined.
            $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;

            // If this table name hasn't been encountered yet, initialize its array.
            if(!isset($entities[$tableName]))
            {
                $entities[$tableName] = [];
            }
            // Add the current entity object to the list for its corresponding table.
            $entities[$tableName][] = $entity;
        }
    }
}

$errors = 0;
// Loop through the collected entities, grouped by table name.
foreach($entities as $tableName=>$entityObjects) // Renamed $entity to $entityObjects for clarity as it's an array of entities for the same table
{
    // Create a new instance of PicoDatabaseDump to generate SQL queries.
    $dumper = new PicoDatabaseDump();   
    // Generate ALTER TABLE ADD queries based on the entities for the current table.
    // The parameters (true, false, false) control various aspects of the query generation.
    $queryArr = $dumper->createAlterTableAddFromEntities($entityObjects, $tableName, $databaseBuilder, true, false, false);
    
    // Loop through each generated SQL query.
    foreach($queryArr as $sql)
    {
        // If the SQL query is not empty.
        if(!empty($sql))
        {
            try
            {
                // Attempt to execute the SQL query using the database builder.
                $databaseBuilder->execute($sql);
            }
            catch(Exception $e)
            {
                // If an error occurs during execution, log the error message.
                error_log($e->getMessage());
                $errors++;
            }
        }
    }           
}
if($errors == 0)
{
    header('Content-Type: application/json');
    echo json_encode(['success'=>true]);
}
else
{
    header('Content-Type: application/json');
    echo json_encode(['success'=>false]);
}
