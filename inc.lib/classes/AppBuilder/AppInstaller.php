<?php

namespace AppBuilder;

use AppBuilder\Util\Entity\EntityUtil;
use AppBuilder\Util\Error\ErrorChecker;
use MagicObject\Generator\PicoDatabaseDump;

/**
 * Class AppInstaller
 * 
 * This class is responsible for generating SQL queries for database installation or alteration.
 * It processes entity classes located in the `Entity` folder, checks them for errors, and then generates
 * SQL queries based on the entity definitions. The generated SQL queries can be used for creating
 * or altering tables in the database.
 *
 * @package AppBuilder
 */
class AppInstaller {

    /**
     * Generates the installer SQL queries for the database schema.
     *
     * This method processes the entities in the Entity folder, checks them for errors, 
     * and generates SQL queries for creating or altering tables based on the entity definitions.
     *
     * @param string $database The database connection or database name to be used for generating queries.
     * @param bool $createIfNotExists Flag to indicate whether to create the table if it doesn't exist.
     * @param bool $dropIfExists Flag to indicate whether to drop existing columns before adding new ones.
     * 
     * @return string The generated SQL queries for installing or altering the database schema.
     */
    public function generateInstallerQuery($database, $createIfNotExists = false, $dropIfExists = false)
    {
        $entities = [];
        $entityNames = [];

        $files = glob(__DIR__."/EntityInstaller/*.php");
        
        // Process each entity file
        foreach($files as $idx=>$fileName)
        {
            $entityName = basename($fileName, ".php");
            $className = "\\AppBuilder\EntityInstaller\\".$entityName;
            $entityName = trim($entityName);
            
            // Check if file exists
            if(file_exists($fileName))
            {
                // Check for errors in the entity file
                $phpError = ErrorChecker::phpTest($fileName);

                $returnVar = $phpError->errorCode;
                
                // If no errors, include the entity class and process it
                if($returnVar == 0)
                {
                    include_once $fileName;                  
                    $entity = new $className(null, $database);
                    $entityInfo = EntityUtil::getTableName($fileName);
                    $tableName = isset($entityInfo['name']) ? $entityInfo['name'] : $idx;
                    
                    // Initialize the entities and entityNames arrays for the table if not set
                    if(!isset($entities[$tableName]))
                    {
                        $entities[$tableName] = [];
                        $entityNames[$tableName] = [];
                    }
                    
                    // Add the entity and its name to the respective arrays
                    $entities[$tableName][] = $entity;
                    $entityNames[$tableName][] = $entityName;
                }
            }
        }
        
        // Generate and return the SQL queries
        return $this->generateQuery($database, $entities, $entityNames, $createIfNotExists, $dropIfExists);
    }
    
    /**
     * Generates the SQL queries for each table based on the provided entities.
     *
     * This method processes the provided entities to generate SQL queries for creating or altering tables. 
     * It uses a `PicoDatabaseDump` object to create the queries and groups them by table names. 
     * The method handles multiple tables, generating the SQL for each and formatting the output with comments indicating the beginning and end of each table's SQL.
     *
     * @param string $database The database connection or database name to be used for generating queries.
     * @param array $entities An associative array of entities grouped by their table names. The key is the table name, and the value is an array of entities for that table.
     * @param array $entityNames An associative array of entity names grouped by their table names. The key is the table name, and the value is an array of entity names for that table.
     * @param bool $createIfNotExists Flag to indicate whether to create the table if it doesn't exist.
     * @param bool $dropIfExists Flag to indicate whether to drop existing columns before adding new ones.
     * 
     * @return string The generated SQL queries for each table, formatted with comments and grouped by table name.
     */
    private function generateQuery($database, $entities, $entityNames, $createIfNotExists = false, $dropIfExists = false)
    {
       
        $allQueries = [];

        // Iterate over each table and generate the queries for its entities
        foreach($entities as $tableName=>$entity)
        {
            
            $entityQueries = [];
            $dumper = new PicoDatabaseDump();   
            
            // Generate the SQL queries for the entity
            $queryArr = $dumper->createAlterTableAddFromEntities($entity, $tableName, $database, $createIfNotExists, $dropIfExists);
            // Add non-empty queries to the list
            foreach($queryArr as $sql)
            {
                if(!empty($sql))
                {
                    $entityQueries[] = $sql;
                }
            }
            
            // If there are any queries for this table, format and append them
            if(!empty($entityQueries))
            {
                $entityName = implode(", ", $entityNames[$tableName]);
                $entityName = str_replace("\\", ".", $entityName);
                $allQueries[] = "-- SQL for $entityName begin";
                $allQueries[] = implode("\r\n", $entityQueries);
                $allQueries[] = "-- SQL for $entityName end\r\n";
            }           
        }
    
        // Return the generated queries as a string
        return implode("\r\n\r\n", $allQueries);
    }
}
