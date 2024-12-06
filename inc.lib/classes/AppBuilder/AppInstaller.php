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
     * @param string $cacheDir The directory path used for error checking and validation.
     * 
     * @return string The generated SQL queries for installing or altering the database schema.
     */
    public function generateInstallerQuery($database, $cacheDir)
    {
        $entities = [];
        $entityNames = [];

        $files = glob(__DIR__."/Entity/*.php");
        
        // Process each entity file
        foreach($files as $idx=>$fileName)
        {
            $entityName = basename($fileName, ".php");
            $className = "\\AppBuilder\Entity\\".$entityName;
            $entityName = trim($entityName);
            
            if(file_exists($fileName))
            {
                // Check for errors in the entity file
                $return_var = ErrorChecker::errorCheck($cacheDir, $fileName);
                
                // If no errors, include the entity class and process it
                if($return_var == 0)
                {
                    include_once $fileName;                  
                    $entity = new $className(null, $database);
                    $tableInfo = EntityUtil::getTableName($fileName);
                    $tableName = isset($tableInfo['name']) ? $tableInfo['name'] : $idx;
                    
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
        return $this->generateQuery($database, $entities, $entityNames);
    }
    
    /**
     * Generates the SQL queries for each table based on the provided entities.
     *
     * This method generates the SQL queries for creating or altering tables by processing
     * the provided entities. It uses a `PicoDatabaseDump` object to create the queries 
     * and groups them by table names.
     *
     * @param string $database The database connection or database name to be used for generating queries.
     * @param array $entities An associative array of entities grouped by their table names.
     * @param array $entityNames An associative array of entity names grouped by their table names.
     * 
     * @return string The generated SQL queries for each table.
     */
    private function generateQuery($database, $entities, $entityNames)
    {
        $allQueries = [];

        // Iterate over each table and generate the queries for its entities
        foreach($entities as $tableName=>$entity)
        {
            $entityQueries = [];
            $dumper = new PicoDatabaseDump();   
            
            // Generate the SQL queries for the entity
            $queryArr = $dumper->createAlterTableAddFromEntities($entity, $tableName, $database);
            
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
                $allQueries[] = "-- SQL for $entityName begin";
                $allQueries[] = implode("\r\n", $entityQueries);
                $allQueries[] = "-- SQL for $entityName end\r\n";
            }           
        }
    
        // Return the generated queries as a string
        return implode("\r\n\r\n", $allQueries);
    }
}
