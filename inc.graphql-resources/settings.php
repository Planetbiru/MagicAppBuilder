<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('Localtion: ./'.basename(__FILE__, '.php'));
    exit();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

$configPath = __DIR__ . "/config/frontend-config.json";
if(file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
    $pagination = $config['pagination'];

} else {
    $config = [];
    $pagination = array(
        'pageSize' => 20,
        'maxPageSize' => 100,
        'minPageSize' => 1
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $pageSize = isset($_POST['pageSize']) ? $_POST['pageSize'] : $pagination['pageSize'];
        $maxPageSize = isset($_POST['maxPageSize']) ? $_POST['maxPageSize'] : $pagination['maxPageSize'];
        $minPageSize = isset($_POST['minPageSize']) ? $_POST['minPageSize'] : $pagination['minPageSize'];

        $config['pagination'] = array(
            'pageSize' => (int) $pageSize,
            'maxPageSize' => (int) $maxPageSize,
            'minPageSize' => (int) $minPageSize
        );

        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));

        http_response_code(200);
        echo json_encode(array('success' => true, 'message' => $i18n->t('settings_updated_successfully')));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'message' => $i18n->t('failed_to_update_settings', $e->getMessage())));
    }
    exit;
}


if (isset($_GET) && isset($_GET['action']) && $_GET['action'] == 'update') {
?>
    <div class="table-container detail-view">
        <form id="settings-update-form" class="form-group" onsubmit="handleSettingsUpdate(event); return false;">
            <table class="table table-borderless">
                <tr>
                    <td><?php echo $i18n->t('page_size');?></td>
                    <td><input type="number" min=="1" name="pageSize" value="<?php echo $pagination['pageSize']; ?>" required autocomplete="off"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('max_page_size');?></td>
                    <td><input type="number" min=="1" name="maxPageSize" value="<?php echo $pagination['maxPageSize']; ?>" required autocomplete="off"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('min_page_size');?></td>
                    <td><input type="number" min=="1" name="minPageSize" value="<?php echo $pagination['minPageSize']; ?>" required autocomplete="off"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success"><?php echo $i18n->t('update'); ?></button>
                        <button type="button" class="btn btn-secondary" onclick="window.location='#settings'"><?php echo $i18n->t('cancel');?></button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php
} else {
?>
    <div class="table-container detail-view">
        <form id="settings-update-form" action="" class="form-group">
            <table class="table table-borderless">
                <tr>
                    <td><?php echo $i18n->t('page_size');?></td>
                    <td><?php echo $pagination['pageSize']; ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('max_page_size');?></td>
                    <td><?php echo $pagination['maxPageSize']; ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('min_page_size');?></td>
                    <td><?php echo $pagination['minPageSize']; ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td><button type="button" class="btn btn-primary" onclick="window.location='#settings-update'"><?php echo $i18n->t('update');?></button></td>
                </tr>
            </table>
        </form>
    </div>
<?php
}
