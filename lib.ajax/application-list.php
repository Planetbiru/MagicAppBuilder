<?php

require_once dirname(__DIR__) . "/inc.app/auth.php";

$arr = $appList->valueArray();
foreach ($arr as $app) {
    if ($app['id'] != null) {
        if ($currentApplication != null && $currentApplication->getId() == $app['id']) {
            $selected = ' application-item-selected';
        } else {
            $selected = '';
        }
?>
    <div class="card application-item<?php echo $selected;?>" data-application-id="<?php echo $app['id']; ?>" data-application-name="<?php echo htmlspecialchars($app['name']); ?>" style="width: 24rem;">
        <div class="card-body">
            <h5 class="card-title"><?php echo $app['name']; ?></h5>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo $app['id']; ?></h6>
            <p class="card-text"><?php echo $app['description']; ?></p>
            <a href="javascript:;" class="btn btn-sm btn-primary">Setting</a>
            <a href="vscode://file/<?php echo str_replace("\\", "/", $app['documentRoot']);?>" class="btn btn-sm btn-primary">VS Code</a>
            <a href="javascript:;" class="btn btn-sm btn-primary">Default</a>
        </div>
    </div>
<?php
    }
}
?>
