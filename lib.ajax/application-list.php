<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

$arr = $appList->valueArray();
foreach ($arr as $app) {
    if ($app['id'] != null) {
        if ($currentApplication != null && $currentApplication->getId() == $app['id']) {
            $selected = 'true';
        } else {
            $selected = '';
        }
?>
<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
    <div 
    class="card application-item" 
    data-selected="<?php echo $selected;?>"
    data-application-id="<?php echo $app['id']; ?>" 
    data-application-name="<?php echo htmlspecialchars($app['name']); ?>"
    data-path="<?php echo str_replace("\\", "/", $app['documentRoot']); ?>"
    >
        <div class="card-body">
            <h5 class="card-title"><?php echo $app['name']; ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $app['id']; ?></h6>
            <p class="card-text"><?php echo $app['description']; ?></p>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-setting">Setting</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-menu">Menu</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-database">Database</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-open">VS Code</a>
            <a href="javascript:;" class="btn btn-sm btn-primary button-application-default">Default</a>
        </div>
    </div>
</div>
<?php
    }
}
