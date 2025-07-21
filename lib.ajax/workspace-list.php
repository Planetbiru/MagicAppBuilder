<?php

use AppBuilder\EntityInstaller\EntityAdminWorkspace;
use MagicAdmin\Entity\Data\StarWorkspace;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";
$selected = "false";

$workspaceFinder = new EntityAdminWorkspace(null, $databaseBuilder);

$adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
$stars = array();
try
{
    $starAplication = new StarWorkspace(null, $databaseBuilder);
    $starsData = $starAplication->findByAdminId($adminId);
    foreach($starsData->getResult() as $star)
    {
        $stars[$star->getWorkspaceId()] = $star->getStar();
    }
}
catch(Exception $e)
{
    // Do nothing
}

try
{
    $currentWorkspaceId = isset($entityAdmin) && $entityAdmin->issetWorkspaceId() ? $entityAdmin->getWorkspaceId() : null;


    $specs = PicoSpecification::getInstance()
        ->add(['adminId', $adminId])
        ->add(['active', true])
        ->add(['workspace.active', true])
        ;
    $sorts = new PicoSortable('workspace.sortOrder', PicoSort::ORDER_TYPE_ASC, 'workspace.timeCreate', PicoSort::ORDER_TYPE_DESC);

    $pageData = $workspaceFinder->findAll($specs, null, $sorts);
    $myWorkspace = [];
    if($pageData->getTotalResult() > 0)
    {
        foreach($pageData->getResult() as $row)
        {
            $row->setStared(isset($stars[$row->getWorkspaceId()]) && $stars[$row->getWorkspaceId()]);
            $myWorkspace[] = $row;
        }
        
        // 
        foreach($myWorkspace as $row)
        {
            if($row->isStared() && $row->issetWorkspace())
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
            <h5 class="card-title">
                <span><?php echo htmlspecialchars($workspace->getName()); ?></span>
                <span>
                <?php if ($stared){ ?>
                    <a class="mark-unstared" href="#"><i class="fa-solid fa-star"></i></a>
                <?php } else { ?>
                    <a class="mark-stared" href="#"><i class="fa-regular fa-star"></i></a>
                <?php } ?>
                </span>
            </h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $workspace->getDirectory(); ?></h6>
            <p class="card-text"><?php echo $workspace->getDescription(); ?></p>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-scan"><i class="fa-solid fa-folder-tree"></i> Scan</a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-open"><i class="fas fa-code"></i> VS Code</a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-default"><i class="fas fa-check"></i>  Active</a>
        </div>
    </div>
</div>
<?php
            }
        }
        foreach($myWorkspace as $row)
        {
            if(!$row->isStared() && $row->issetWorkspace())
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
            <h5 class="card-title">
                <span><?php echo htmlspecialchars($workspace->getName()); ?></span>
                <span>
                <?php if ($stared){ ?>
                    <a class="mark-unstared" href="#"><i class="fa-solid fa-star"></i></a>
                <?php } else { ?>
                    <a class="mark-stared" href="#"><i class="fa-regular fa-star"></i></a>
                <?php } ?>
                </span>
            </h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $workspace->getDirectory(); ?></h6>
            <p class="card-text"><?php echo $workspace->getDescription(); ?></p>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-scan"><i class="fa-solid fa-folder-tree"></i> Scan</a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-open"><i class="fas fa-code"></i> VS Code</a>
            <a href="javascript:;" class="btn btn-tn btn-primary button-workspace-default"><i class="fas fa-check"></i>  Active</a>
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
