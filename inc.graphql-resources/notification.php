<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('Location: ./#'.basename(__FILE__, '.php'));
    exit();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

// Handle POST request for marking notification status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $notificationId = isset($_POST['notificationId']) ? $_POST['notificationId'] : null;
    $action = $_POST['action'];

    if (!$notificationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $i18n->t('notification_id_required')]);
        exit;
    }

    try {
        // Fetch full admin data to get admin_level_id
        $sql = "SELECT admin_id, admin_level_id FROM admin WHERE admin_id = :admin_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':admin_id' => $appAdmin['admin_id']]);
        $currentAdminData = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentAdminId = $currentAdminData['admin_id'];

        if ($action === 'mark_as_unread') {
            $updateSql = "UPDATE notification SET is_read = 0, time_read = NULL WHERE notification_id = :notification_id AND (admin_id = :admin_id OR admin_group = :admin_level_id)";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([':notification_id' => $notificationId, ':admin_id' => $currentAdminId, ':admin_level_id' => $currentAdminData['admin_level_id']]);
            echo json_encode(['success' => true, 'message' => $i18n->t('notification_marked_as_unread')]);
        } elseif ($action === 'delete') {
            $deleteSql = "DELETE FROM notification WHERE notification_id = :notification_id AND (admin_id = :admin_id OR admin_group = :admin_level_id)";
            $deleteStmt = $db->prepare($deleteSql);
            $deleteStmt->execute([':notification_id' => $notificationId, ':admin_id' => $currentAdminId, ':admin_level_id' => $currentAdminData['admin_level_id']]);
            echo json_encode(['success' => true, 'message' => $i18n->t('notification_deleted_successfully')]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

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
    $currentAdminLevelId = $appAdmin['admin_level_id'];
    $currentAdminId = $appAdmin['admin_id'];

    if (isset($_GET['notificationId'])) {
        $notificationId = $_GET['notificationId'];

        // Mark as read if the notification is unread
        $checkReadSql = "SELECT is_read FROM notification WHERE notification_id = :notification_id AND (admin_id = :admin_id OR admin_group = :admin_level_id)";
        $checkReadStmt = $db->prepare($checkReadSql);
        $checkReadStmt->execute([':notification_id' => $notificationId, ':admin_id' => $currentAdminId, ':admin_level_id' => $currentAdminLevelId]);
        $notificationStatus = $checkReadStmt->fetch(PDO::FETCH_ASSOC);

        if ($notificationStatus && !$notificationStatus['is_read']) {
            $markReadSql = "UPDATE notification SET is_read = 1, time_read = :time_read, ip_read = :ip_read WHERE notification_id = :notification_id";
            $markReadStmt = $db->prepare($markReadSql);
            $markReadStmt->execute([':time_read' => date('Y-m-d H:i:s'), ':ip_read' => $_SERVER['REMOTE_ADDR'], ':notification_id' => $notificationId]);
        }

        $sql = "SELECT * FROM notification
                WHERE (admin_id = :admin_id OR admin_group = :admin_level_id)
                AND notification_id = :notification_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':admin_id' => $currentAdminId, ':admin_level_id' => $currentAdminLevelId, ':notification_id' => $notificationId]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($notification !== false) {
            ?>
            <div class="back-controls">
                <button id="back-to-list" class="btn btn-secondary" onclick="backToList('notification')"><?php echo $i18n->t('back_to_list'); ?></button>
                <?php if ($notification['is_read']): ?>
                    <button class="btn btn-primary" onclick="markNotificationAsUnread('<?php echo $notification['notification_id']; ?>', 'detail')"><?php echo $i18n->t('mark_as_unread'); ?></button>
                    <button class="btn btn-danger" onclick="handleNotificationDelete('<?php echo $notification['notification_id']; ?>')"><?php echo $i18n->t('delete'); ?></button>
                <?php endif; ?>
            </div>
            <div class="notification-container">
                <div class="notification-header">
                    <h3><?php echo htmlspecialchars($notification['subject']); ?></h3>
                    <div class="message-meta">
                        <div><strong><?php echo $i18n->t('time'); ?>:</strong> <?php echo htmlspecialchars($notification['time_create']); ?></div>
                        <div><strong><?php echo $i18n->t('status'); ?>:</strong> 
                            <?php if ($notification['is_read']): ?>
                                <span class="status-read"><?php echo $i18n->t('read_at'); ?> <?php echo htmlspecialchars($notification['time_read']); ?></span>
                            <?php else: ?>
                                <span class="status-unread"><?php echo $i18n->t('unread'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="message-body">
                    <?php echo nl2br(htmlspecialchars($notification['content'])); ?>
                    <?php if (!empty($notification['link'])): ?>
                        <p><a href="<?php echo htmlspecialchars($notification['link']); ?>" target="_blank" class="btn btn-primary mt-3"><?php echo $i18n->t('more_info'); ?></a></p>
                    <?php endif; ?>
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
        $search = $_GET['search'] ?? '';
        $params = [':admin_id' => $currentAdminId, ':admin_level_id' => $currentAdminLevelId];
        $whereClause = "WHERE (admin_id = :admin_id OR admin_group = :admin_level_id)";

        if (!empty($search)) {
            $whereClause .= " AND (subject LIKE :search OR content LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Count total notifications for pagination
        $countSql = "SELECT COUNT(*) FROM notification " . $whereClause;
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $totalNotifications = $countStmt->fetchColumn();
        $totalPages = ceil($totalNotifications / $dataLimit);

        $sql = "SELECT notification_id, subject, content, is_read, time_create, link
                FROM notification 
                $whereClause
                ORDER BY time_create DESC
                LIMIT $dataLimit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div id="filter-container" class="filter-container" style="display: block;">
            <form id="notification-search-form" class="search-form" onsubmit="handleNotificationSearch(event)">
                <div class="filter-controls">
                    <div class="form-group">
                        <label for="search_notification"><?php echo $i18n->t('search'); ?></label>
                        <input type="text" name="search" id="search_notification" placeholder="<?php echo $i18n->t('search'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $i18n->t('search'); ?></button>
                </div>
            </form>
        </div>

        <div class="message-list-container">
            <?php if (empty($notifications)): ?>
                <p><?php echo $i18n->t('no_notification'); ?></p>
            <?php endif; ?>
            <?php foreach ($notifications as $notification): 
                $content = trim($notification['content']);
                $isReadClass = $notification['is_read'] ? 'read' : 'unread';
                ?>
                <div class="message-item <?php echo $isReadClass; ?>">
                    <span class="message-status-indicator"></span>
                    <div class="notification-header">
                        <div class="message-link-wrapper">
                            <a href="#notification?notificationId=<?php echo $notification['notification_id']; ?>" class="message-link">
                                <span class="message-subject"><?php echo htmlspecialchars($notification['subject']); ?></span>
                            </a>
                            <span class="message-time"><?php echo htmlspecialchars($notification['time_create']); ?></span>
                            <?php if ($notification['is_read']): ?>
                                <button class="btn btn-sm btn-secondary" onclick="markNotificationAsUnread('<?php echo $notification['notification_id']; ?>', 'list')"><?php echo $i18n->t('mark_as_unread'); ?></button>
                                <button class="btn btn-sm btn-danger" onclick="handleNotificationDelete('<?php echo $notification['notification_id']; ?>')"><?php echo $i18n->t('delete'); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="message-content">
                        <?php
                        echo htmlspecialchars(substr($content, 0, 150)) . (strlen($content) > 150 ? '...' : '');
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="pagination-container">
            <?php if ($totalPages > 1): ?>
                <?php $searchQuery = !empty($search) ? '&search=' . urlencode($search) : ''; ?>
                <span><?php echo $i18n->t('page_of', $page, $totalPages, $totalNotifications); ?></span>
                <?php if ($page > 1): ?>
                    <a href="#notification?page=<?php echo $page - 1; ?><?php echo $searchQuery; ?>" class="btn btn-secondary">
                        <?php echo $i18n->t('previous'); ?>
                    </a>
                <?php endif; ?>

                <?php
                $window = 1;
                $startPage = max(1, $page - $window);
                $endPage = min($totalPages, $page + $window);

                if ($startPage > 1) {
                    echo '<a href="#notification?page=1'.$searchQuery.'" class="btn btn-secondary">1</a>';
                    if ($startPage > 2) echo '<span class="pagination-ellipsis">...</span>';
                }

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="#notification?page=<?php echo $i; ?><?php echo $searchQuery; ?>"
                       class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<span class="pagination-ellipsis">...</span>';
                    echo '<a href="#notification?page='.$totalPages.$searchQuery.'" class="btn btn-secondary">'.$totalPages.'</a>';
                }

                if ($page < $totalPages): ?>
                    <a href="#notification?page=<?php echo $page + 1; ?><?php echo $searchQuery; ?>" class="btn btn-secondary">
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