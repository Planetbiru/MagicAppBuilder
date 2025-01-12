<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\InputGet;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputPost = new InputPost();
$inputGet = new InputGet();

if($inputPost->getDatabaseType() != null || $inputPost->getDatabaseName() !== null)
{
    $applicationId = $inputPost->getApplicationId();
    $databaseType = $inputPost->getDatabaseType();
    $databaseName = $inputPost->getDatabaseName();
    $databaseSchema = $inputPost->getDatabaseSchema();
    $template = $inputPost->getTemplate();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $workspaceDirectory."/entity/template/$filename";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $template);
    ResponseUtil::sendJSON([]);
}
else
{
    $applicationId = $inputGet->getApplicationId();
    $databaseType = $inputGet->getDatabaseType();
    $databaseName = $inputGet->getDatabaseName();
    $databaseSchema = $inputGet->getDatabaseSchema();
    $filename = sprintf("%s-%s-%s-%s-data.json", $applicationId, $databaseType, $databaseName, $databaseSchema);
    $path = $workspaceDirectory."/entity/template/$filename";
    error_log($path);
    if(!file_exists($path))
    {
        $columns = [];

        $curApp = $builderConfig->getCurrentApplication();
        $appBaseConfigPath = $workspaceDirectory."/applications";
        $appConfig = new SecretObject();
        $appConfig->setDatabase(new SecretObject());
        $appConfig->setSessions(new SecretObject());

        if($applicationId != null)
        {
            $appConfigPath = $workspaceDirectory."/applications/".$applicationId."/default.yml";
            if(file_exists($appConfigPath))
            {
                $appConfig->loadYamlFile($appConfigPath, false, true, true);
            }
        }

        if(!$appConfig->issetApplication() && isset($appList) && $appList instanceof SecretObject)
        {
            $arr = $appList->valueArray();
            foreach($arr as $app)
            {
                if($applicationId == $app['id'])
                {
                    $fixApp = new SecretObject([
                        'name'=>$app['name']
                    ]);
                    $appConfig->setApplication($fixApp);
                }
            }
            
            
        }
        $entityInfo = null;
        if(isset($appConfig) && $appConfig->getEntityInfo() != null)
        {
            $entityInfo = $appConfig->entityInfo;
        }
        else
        {
            $entityInfo = new SecretObject([
                'name' => 'name',
                'sortOrder' => 'sort_order',
                'adminCreate' => 'admin_create',
                'adminEdit' => 'admin_edit',
                'timeCreate' => 'time_create',
                'timeEdit' => 'time_edit',
                'ipCreate' => 'ip_create',
                'ipEdit' => 'ip_edit',
                'active' => 'active'
            ]);
            
        }
        if(isset($entityInfo))
        {
            $columns[] = [
                "name" => $entityInfo->name,
                "type" => "VARCHAR",
                "length" => "50",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->sortOrder,
                "type" => "INT",
                "length" => "11",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->adminCreate,
                "type" => "VARCHAR",
                "length" => "40",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->adminEdit,
                "type" => "VARCHAR",
                "length" => "40",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->timeCreate,
                "type" => "TIMESTAMP",
                "length" => "",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->timeEdit,
                "type" => "TIMESTAMP",
                "length" => "",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->ipCreate,
                "type" => "VARCHAR",
                "length" => "50",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->ipEdit,
                "type" => "VARCHAR",
                "length" => "50",
                "nullable" => true,
                "default" => null,
                "values" => ""
            ];

            $columns[] = [
                "name" => $entityInfo->active,
                "type" => "TINYINT",
                "length" => "1",
                "nullable" => false,
                "default" => 1,
                "values" => ""
            ];

        }

        $json = [
            "columns" => $columns
        ];
    }
    else
    {
        $json = file_get_contents($path);
    }
    ResponseUtil::sendJSON($json);
}
