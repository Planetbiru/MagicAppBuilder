<?php

use AppBuilder\EntityInstaller\EntityAdminWorkspace;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;

require_once dirname(__DIR__) . "/inc.app/auth.php";
$selected = "false";

$workspaceFinder = new EntityAdminWorkspace(null, $databaseBuilder);
try
{
    $adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
    $currentWorkspaceId = isset($entityAdmin) && $entityAdmin->issetWorkspaceId() ? $entityAdmin->getWorkspaceId() : null;


    $specs = PicoSpecification::getInstance()
        ->add(['adminId', $adminId])
        ->add(['active', true])
        ->add(['workspace.active', true])
        ;
    $sorts = new PicoSortable('workspace.sortOrder', PicoSort::ORDER_TYPE_ASC, 'workspace.timeCreate', PicoSort::ORDER_TYPE_DESC);

    $pageData = $workspaceFinder->findAll($specs, null, $sorts);
    if($pageData->getTotalResult() > 0)
    {
        foreach($pageData->getResult() as $row)
        {
            if($row->issetWorkspace())
            {
                $workspace = $row->getWorkspace();
                $selected = $currentWorkspaceId == $workspace->getWorkspaceId() ? "true" : "false";

?>
<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
    <div 
    class="card workspace-item" 
    data-selected="<?php echo $selected;?>"
    data-workspace-id="<?php echo $workspace->getWorkspaceId(); ?>" 
    data-workspace-name="<?php echo $workspace->getName(); ?>"
    data-path="<?php echo str_replace("\\", "/", $workspace->getDirectory()); ?>"
    >
        <div class="card-body">
            <h5 class="card-title"><?php echo $workspace->getName(); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $workspace->getDirectory(); ?></h6>
            <p class="card-text"><?php echo $workspace->getDescription(); ?></p>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-scan">Scan</a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-open">VS Code</a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-default">Active</a>
        </div>
    </div>
</div>
<?php
            }
        }
    }
}
catch(Exception $e)
{
    // do nothing
}
