<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

$configPath = __DIR__ . "/config/frontend-config.json";
if (file_exists($configPath)) {
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

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$dataLimit = abs($pagination['pageSize']);
$offset = ($page - 1) * $dataLimit;

try {
    $sql = "SELECT * FROM admin WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(':username' => $_SESSION['username']));
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_GET['notificationId'])) {
        $sql = "SELECT notification.* 
                FROM notification
                WHERE (notification.admin_id = :admin_id OR notification.admin_group = :admin_group)
                AND notification.notification_id = :notification_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            ':admin_id' => $admin['admin_id'],
            ':admin_group' => $admin['admin_group'] ?? null,
            ':notification_id' => $_GET['notificationId']
        ));
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($notification !== false) {
            ?>
            <div class="back-controls">
                <button id="back-to-list" class="btn btn-secondary" onclick="backToList('notification')">
                    <?php echo $i18n->t('back_to_list'); ?>
                </button>
            </div>
            <div class="notification-container">
                <div class="notification-header">
                    <?php echo htmlspecialchars($notification['subject']); ?>
                </div>
                <div class="notification-content">
                    <?php echo htmlspecialchars($notification['content']); ?>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div class="table-container detail-view">
                <?php echo $i18n->t('no_notification'); ?>
            </div>
            <?php
        }
    } else {
        // Count total notifications for pagination
        $countSql = "SELECT COUNT(*) FROM notification WHERE admin_id = :admin_id OR admin_group = :admin_group";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute(array(
            ':admin_id' => $admin['admin_id'],
            ':admin_group' => $admin['admin_group'] ?? null
        ));
        $totalNotifications = $countStmt->fetchColumn();
        $totalPages = ceil($totalNotifications / $dataLimit);

        $sql = "SELECT notification.* 
                FROM notification
                WHERE notification.admin_id = :admin_id OR notification.admin_group = :admin_group
                ORDER BY notification.time_create DESC
                LIMIT $dataLimit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            ':admin_id' => $admin['admin_id'],
            ':admin_group' => $admin['admin_group'] ?? null
        ));
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="table-container detail-view">
            <?php foreach ($notifications as $notification): 
                $content = trim($notification['content']); ?>
                <div class="notification-container">
                    <div class="notification-header">
                        <a href="#notification?notificationId=<?php echo $notification['notification_id']; ?>">
                            <?php echo htmlspecialchars($notification['subject']); ?>
                        </a>
                    </div>
                    <div class="notification-content">
                        <?php
                        echo htmlspecialchars(substr($content, 0, 150)) . (strlen($content) > 150 ? '...' : '');
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="pagination-container">
            <?php if ($totalPages > 1): ?>
                <span><?php echo $i18n->t('page_of', $page, $totalPages, $totalNotifications); ?></span>
                <?php if ($page > 1): ?>
                    <a href="#notification?page=<?php echo $page - 1; ?>" class="btn btn-secondary">
                        <?php echo $i18n->t('previous'); ?>
                    </a>
                <?php endif; ?>

                <?php
                $window = 1;
                $startPage = max(1, $page - $window);
                $endPage = min($totalPages, $page + $window);

                if ($startPage > 1) {
                    echo '<a href="#notification?page=1" class="btn btn-secondary">1</a>';
                    if ($startPage > 2) echo '<span class="pagination-ellipsis">...</span>';
                }

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="#notification?page=<?php echo $i; ?>"
                       class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                    echo '<a href="#notification?page='.$totalPages.'" class="btn btn-secondary">'.$totalPages.'</a>';
                }

                if ($page < $totalPages): ?>
                    <a href="#notification?page=<?php echo $page + 1; ?>" class="btn btn-secondary">
                        <?php echo $i18n->t('next'); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
