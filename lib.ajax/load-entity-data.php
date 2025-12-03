<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();

/**
 * Retrieve a list of columns that are automatically handled by the backend.
 *
 * This function checks a mapping of reserved column names and returns
 * a normalized array describing which fields should be populated by
 * backend logic (such as timestamps, admin identifiers, or IP addresses).
 *
 * Each returned item contains:
 * - `columnName`: The actual database column name from the reserved map.
 * - `type`: The expected data type used by the backend to populate the value.
 *
 * @param array $reservedColumnMap An associative array containing reserved column names
 *                                 (e.g., time_create, admin_edit, ip_create).
 *
 * @return array An associative array where keys represent logical backend fields
 *               (e.g., timeCreate, adminEdit), and values contain metadata such as
 *               the actual column name and expected data type.
 */
function getBackendHandledColumns($reservedColumnMap)
{
    $backendHandledColumns = [];
    if (isset($reservedColumnMap['timeCreate'])) {
        $backendHandledColumns['timeCreate'] = ['columnName' => $reservedColumnMap['timeCreate'], 'type' => 'timestamp'];
    }
    if (isset($reservedColumnMap['timeEdit'])) {
        $backendHandledColumns['timeEdit'] = ['columnName' => $reservedColumnMap['timeEdit'], 'type' => 'timestamp'];
    }
    if (isset($reservedColumnMap['adminCreate'])) {
        $backendHandledColumns['adminCreate'] = ['columnName' => $reservedColumnMap['adminCreate'], 'type' => 'string'];
    }
    if (isset($reservedColumnMap['adminEdit'])) {
        $backendHandledColumns['adminEdit'] = ['columnName' => $reservedColumnMap['adminEdit'], 'type' => 'string'];
    }
    if (isset($reservedColumnMap['ipCreate'])) {
        $backendHandledColumns['ipCreate'] = ['columnName' => $reservedColumnMap['ipCreate'], 'type' => 'string'];
    }
    if (isset($reservedColumnMap['ipEdit'])) {
        $backendHandledColumns['ipEdit'] = ['columnName' => $reservedColumnMap['ipEdit'], 'type' => 'string'];
    }
    return $backendHandledColumns;
}

if ($inputPost->getDatabaseName() !== null) {
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $entities = $inputPost->getEntities();
    $diagrams = $inputPost->getDiagrams();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $hash = md5("$applicationId-$databaseType-$databaseName-$databaseSchema");

    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $path = $selectedApplication->getProjectDirectory()."/__data/entity/data/$filename";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $indexPath = $selectedApplication->getProjectDirectory()."/__data/entity/index.json";
        $configPath = $selectedApplication->getProjectDirectory()."/default.yml";

        $applicationConfig = new SecretObject();
        $applicationConfig->loadYamlFile($configPath, false, true, false);
        

        $dir = dirname($indexPath);
        if(!file_exists($dir))
        {
            mkdir($dir, 0755, true);
        }

        if (file_exists($path)) {
            $raw = file_get_contents($path);
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                $data = array('entities' => array(), 'diagrams' => array());
            }
        } else {
            $data = array('entities' => array(), 'diagrams' => array());
        }

        $data['reservedColumns'] = $applicationConfig->getEntityInfo();
        $data['backendHandledColumns'] = getBackendHandledColumns($data['reservedColumns']);

        if (strlen($diagrams) > 0) {
            $data['diagrams'] = json_decode($diagrams);
            file_put_contents($path, json_encode($data));
        }
        $entityCount = 0;
        if (strlen($entities) > 0) {
            $data['entities'] = json_decode($entities, true);

            // Update entity->creator and entity->modifier
            foreach($data['entities'] as $index => $entity) {
                if (isset($entity['creator']) && $entity['creator'] == '{{userName}}') {
                    $data['entities'][$index]['creator'] = $entityAdmin->getName();
                }
                if (isset($entity['modifier']) && $entity['modifier'] == '{{userName}}') {
                    $data['entities'][$index]['modifier'] = $entityAdmin->getName();
                }
                $entityCount++;
            }
            
            file_put_contents($path, json_encode($data));
        }

        if(!file_exists($indexPath))
        {
            $indexRaw = '{}';
        }
        else
        {
            $indexRaw = file_get_contents($indexPath);
        }
        $index = json_decode($indexRaw, true);
        if(!is_array($index))
        {
            $index = [];
        }
        if(!isset($index[$hash]) || !is_array($index[$hash]))
        {
            $index[$hash] = array();
        }
        $index[$hash]['applicationId'] = $applicationId;
        $index[$hash]['databaseType'] = $databaseType;
        $index[$hash]['databaseName'] = $databaseName;
        $index[$hash]['databaseSchema'] = $databaseSchema;
        $index[$hash]['entities'] = $entityCount;
        $index[$hash]['file'] = "data/$filename";
        $index[$hash]['hash'] = $hash;
        if(empty($index[$hash]['databaseName']))
        {
            $index[$hash]['label'] = basename($databaseBuilder->getDatabaseCredentials()->getDatabaseFilePath());
        }
        else
        {
            $index[$hash]['label'] = $index[$hash]['databaseName'];
        }

        file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT));
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON([]);
    exit();
} else {
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $json = json_encode(array('entities' => array(), 'diagrams' => array()));
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $selectedApplication = new EntityApplication(null, $databaseBuilder);
    try
    {
        $selectedApplication->find($applicationId);
        $path = $selectedApplication->getProjectDirectory()."/__data/entity/data/$filename";
        if (file_exists($path)) {
            $json = file_get_contents($path);
            $data = json_decode($json, true);
            $data['__magic_signature__'] = 'MAGICAPPBUILDER-DB-DESIGN-V1';
            $json = json_encode($data);
        } else {
            $json = '{}';
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
    ResponseUtil::sendJSON($json);
    exit();
}
