<?php

namespace AppBuilder\Util\Entity;

use Exception;
use MagicAdmin\Entity\Data\TooltipCache;
use MagicApp\Field;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoEmptyParameter;

class EntityUtil
{
    /**
     * Extracts table name from an entity file.
     *
     * @param string $entityFile The path to the entity file.
     * @return array The parsed key-value pairs from the @Table annotation.
     */
    public static function getTableName($entityFile)
    {
        if(file_exists($entityFile))
        {
            $content = file_get_contents($entityFile);
            // @Table(
            $tableOffset = stripos($content, '@Table');
            if($tableOffset !== false)
            {
                // find (
                $p1 = strpos($content, "(", $tableOffset + 1);
                if($p1 !== false)
                {
                    $p2 = strpos($content, ")", $p1 + 1);
                    if($p2 !== false)
                    {
                        $len = $p2 - $p1 - 1;
                        $str = substr($content, $p1+1, $len);
                        return self::parseKeyValue($str);
                    }
                }
            }
        }
        return array();
    }

    /**
     * Parses a query string into key-value pairs.
     *
     * @param string $queryString The query string to parse.
     * @return string[] Parsed key-value pairs.
     * @throws InvalidQueryInputException
     */
    public static function parseKeyValue($queryString)
    {
        if(!isset($queryString) || empty($queryString) || $queryString instanceof PicoEmptyParameter)
        {
            return array();
        }
        if(!is_string($queryString))
        {
            throw new InvalidAnnotationException("Invalid query string");
        }

        // For every modification, please test regular expression with https://regex101.com/
    
        // parse attributes with quotes
        $pattern1 = '/([_\-\w+]+)\=\"([a-zA-Z0-9\-\+ _,.\(\)\{\}\`\~\!\@\#\$\%\^\*\\\|\<\>\[\]\/&%?=:;\'\t\r\n|\r|\n]+)\"/m'; // NOSONAR
        preg_match_all($pattern1, $queryString, $matches1);
        $pair1 = array_combine($matches1[1], $matches1[2]);
        
        // parse attributes without quotes
        $pattern2 = '/([_\-\w+]+)\=([a-zA-Z0-9._]+)/m'; // NOSONAR
        preg_match_all($pattern2, $queryString, $matches2);

        $pair3 = self::combineAndMerge($matches2, $pair1);
        
        // parse attributes without any value
        $pattern3 = '/([\w\=\-\_"]+)/m'; // NOSONAR
        preg_match_all($pattern3, $queryString, $matches3);
        
        $pair4 = array();
        if(isset($matches3) && isset($matches3[0]) && is_array($matches3[0]))
        {
            $keys = array_keys($pair3);
            foreach($matches3[0] as $val)
            {
                if(self::matchArgs($keys, $val))
                {
                    if(is_numeric($val))
                    {
                        // prepend attribute with underscore due unexpected array key
                        $pair4["_".$val] = true;
                    }
                    else
                    {
                        $pair4[$val] = true;
                    }
                }
            }
        }
        
        // merge $pair3 and $pair4 into result
        return array_merge($pair3, $pair4);
    }

    /**
     * Matches arguments ensuring they are valid.
     *
     * @param array $keys The list of existing keys.
     * @param string $val The value to check.
     * @return bool True if it is a valid argument, false otherwise.
     */
    public static function matchArgs($keys, $val)
    {
        return stripos($val, '=') === false && stripos($val, '"') === false && stripos($val, "'") === false && !in_array($val, $keys);
    }

    /**
     * Combines and merges arrays of matches.
     *
     * @param array $matches2 The matched attributes.
     * @param array $pair The existing array.
     * @return array The merged result.
     */
    public static function combineAndMerge($matches2, $pair)
    {
        if(isset($matches2[1]) && isset($matches2[2]) && is_array($matches2[1]) && is_array($matches2[2]))
        {
            $pair2 = array_combine($matches2[1], $matches2[2]);
            // merge $pair and $pair2 into $pair3
            return array_merge($pair, $pair2);
        }
        else
        {
            return $pair;
        }
    }

    /**
     * Formats the title and table information for an entity.
     *
     * This method generates an HTML string that represents the entity's name,
     * table name, last update time, and details about its columns, primary keys,
     * and join columns.
     *
     * @param string $entityName The name of the entity.
     * @param string $filetime The timestamp of the last update.
     * @param PicoTableInfo $entityInfo The entity information, which includes table name,
     *        columns, primary keys, and join columns.
     * 
     * @return string The formatted HTML content that includes entity details and table structure.
     */
    public static function formatTitle($entityName, $filetime, $entityInfo)
    {
        // Table for basic entity info
        $table1 = '<table><tbody>
        <tr><td>Entity Name</td><td>'.$entityName.'</td></tr>
        <tr><td>Table Name</td><td>'.$entityInfo->getTableName().'</td></tr>
        <tr><td>Last Update</td><td>'.$filetime.'</td></tr>
        </tbody></table>';

        // Initialize the table to hold column details
        $table = '';
        $columns = $entityInfo->getColumns();
        $primaryKeys = $entityInfo->getPrimaryKeys();
        $joinColumns = $entityInfo->getJoinColumns();

        // Start table for columns, primary keys, and join columns
        $table .= '<table><tbody>';

        $table .= '<tr><td colspan="2">Columns</td></tr>';
        if (isset($columns) && is_array($columns) && !empty($columns)) {
            foreach ($columns as $column) {
                $table .= '<tr><td>'.$column['name'].'</td><td>'.$column['type'].'</td></tr>'; // NOSONAR
            }
        }

        // Add primary keys to the table
        if (isset($primaryKeys) && is_array($primaryKeys) && !empty($primaryKeys)) {
            $table .= '<tr><td colspan="2">Primary Keys</td></tr>';
            foreach ($primaryKeys as $column) {
                $table .= '<tr><td>'.$column['name'].'</td><td></td></tr>'; // NOSONAR
            }
        }

        // Add join columns to the table
        if (isset($joinColumns) && is_array($joinColumns) && !empty($joinColumns)) {
            $table .= '<tr><td colspan="2">Join Columns</td></tr>';
            foreach ($joinColumns as $column) {
                $ref = isset($column['referenceColumnName']) ? $column['referenceColumnName'] : $column['name'];
                $table .= '<tr><td>'.$column['name'].'</td><td>'.$ref.'</td></tr>'; // NOSONAR
            }
        }

        $table .= '</tbody></table>';

        // Return the final formatted HTML structure
        return '<div>'
            .$table1
            .'<div class="horizontal-line"></div>'
            .$table
            .'</div>';
    }

    /**
     * Retrieves or generates the tooltip for a given entity based on its file path, class name, and last modified time.
     *
     * This method checks if a cached tooltip is available for the entity. If a valid cached tooltip is found 
     * and it has not expired, it returns the cached content. If no valid cached tooltip is found, it generates 
     * a new tooltip, caches it, and returns it. The method can bypass caching if requested.
     *
     * @param PicoDatabase $databaseBuilder The database instance used for querying and saving tooltip cache data.
     * @param string $path The path to the entity file, used to identify the entity and generate a hash for caching.
     * @param string $className The class name of the entity, used for generating the entity name.
     * @param string $filetime The last modified timestamp of the entity file, used for determining cache expiration time.
     * @param bool $cache A flag indicating whether to use the cached tooltip or always generate a new one. Default is `false`.
     * 
     * @return string The generated or retrieved tooltip content for the entity.
     * 
     * @throws Exception If an error occurs during fetching or caching the tooltip. This could occur when accessing 
     *                   the database or inserting/updating the tooltip cache.
     */
    public static function getEntityTooltip($databaseBuilder, $path, $className, $filetime, $cache = false)
    {
        $entityName = basename($className);
        
        if ($cache) {
            $hash = md5($path);

            $tooltipCache = new TooltipCache(null, $databaseBuilder);
            $specs = PicoSpecification::getInstance()
                ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->tooltipCacheId, $hash));

            try {
                $tooltipCache->findOne($specs);
                if ($tooltipCache->getExpire() > $filetime) {
                    $tooltip = self::getEntityTooltipFromFile($path, $className, $entityName, $filetime);
                    $tooltipCache->setTooltipCacheId($hash)
                        ->setExpire($filetime)
                        ->setContent($tooltip)
                        ->update();
                }
                else {
                    $tooltip = $tooltipCache->getContent();
                }
            } catch (Exception $e) {
                $tooltip = self::getEntityTooltipFromFile($path, $className, $entityName, $filetime);
                try {
                    $tooltipCache->setTooltipCacheId($hash)
                        ->setExpire($filetime)
                        ->setContent($tooltip)
                        ->insert();
                } catch (Exception $e2) {
                    // Do nothing
                }
            }
            return $tooltip;
        } else {
            return self::getEntityTooltipFromFile($path, $className, $entityName, $filetime);
        }
    }

    /**
     * Generates the tooltip for a given entity based on its file path, class name, and last modified time.
     *
     * This method includes the PHP file located at the specified `$path`, instantiates the entity class 
     * based on the `$className`, retrieves its table information, and generates the tooltip by formatting 
     * the entity's details. The tooltip is then returned as a string.
     *
     * @param string $path The path to the entity file to include.
     * @param string $className The class name of the entity, used to instantiate the entity object.
     * @param string $entityName The name of the entity, which is used to provide a label in the tooltip.
     * @param string $filetime The last modified timestamp of the file, which is used to show the last update time in the tooltip.
     *
     * @return string The formatted tooltip content for the entity, containing table information and other entity details.
     * 
     * @throws Exception If an error occurs while including the file or creating the entity object.
     */
    public static function getEntityTooltipFromFile($path, $className, $entityName, $filetime)
    {
        include_once $path;
        $entity = new $className();
        $tableInfo = $entity->tableInfo();
        return EntityUtil::formatTitle($entityName, $filetime, $tableInfo);
    }

    /**
     * Generates the tooltip for a given entity in JSON format based on its file path, class name, and last modified time.
     *
     * This method includes the PHP file located at the specified `$path`, instantiates the entity class 
     * based on the `$className`, retrieves its table information, and generates the tooltip by formatting 
     * the entity's details into a JSON structure. The JSON structure is then returned as an array.
     *
     * @param string $path The path to the entity file to include.
     * @param string $className The class name of the entity, used to instantiate the entity object.
     * @param string $filetime The last modified timestamp of the file, which is used to show the last update time in the tooltip.
     *
     * @return array The formatted tooltip content for the entity in JSON format, containing table information and other entity details.
     * 
     * @throws Exception If an error occurs while including the file or creating the entity object.
     */
    public static function getEntityTooltipFromFileAsJson($path, $className, $filetime)
    {
        include_once $path;
        $entity = new $className();
        $entityName = basename($className);
        $tableInfo = $entity->tableInfo();
        return [
            'entityName'=>$entityName, 
            'filetime'=>$filetime, 
            'columns'=>$tableInfo->getColumns(),
            'primaryKeys'=>$tableInfo->getPrimaryKeys(),
            'joinColumns'=>$tableInfo->getJoinColumns()
        ];
    }

}