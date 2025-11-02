<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

// Handle POST request for marking message status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $messageId = isset($_POST['messageId']) ? $_POST['messageId'] : null;
    $action = $_POST['action'];

    if (!$messageId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $i18n->t('message_id_required')]);
        exit;
    }

    try {
        $sql = "SELECT admin_id FROM admin WHERE username = :username";
        $stmt = $db->prepare($sql);
        $stmt->execute([':username' => $_SESSION['username']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentAdminId = $admin['admin_id'];

        $newStatus = '0';
        if(stripos($cfgDbDriver, 'posgre') !== false || stripos($cfgDbDriver, 'pgsql') !== false)
        {
            $newStatus = 'false';
        }

        if ($action === 'mark_as_unread') {
            $updateSql = "UPDATE message SET is_read = :is_read, time_read = NULL WHERE message_id = :message_id AND receiver_id = :receiver_id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([':message_id' => $messageId, ':receiver_id' => $currentAdminId, ':is_read' => $newStatus]);
            echo json_encode(['success' => true, 'message' => $i18n->t('message_marked_as_unread')]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

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

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$dataLimit = abs($pagination['pageSize']);
$offset = ($page - 1) * $dataLimit;

try
{
    $sql = "SELECT * FROM admin WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(':username' => $_SESSION['username']));
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if(isset($_GET['messageId']))
    {
        $messageId = $_GET['messageId'];
        $currentAdminId = $admin['admin_id'];

        // Mark as read if the current user is the receiver and the message is unread
        $checkReadSql = "SELECT is_read, receiver_id FROM message WHERE message_id = :message_id";
        $checkReadStmt = $db->prepare($checkReadSql);
        $checkReadStmt->execute([':message_id' => $messageId]);
        $messageStatus = $checkReadStmt->fetch(PDO::FETCH_ASSOC);

        if ($messageStatus && $messageStatus['receiver_id'] == $currentAdminId && !$messageStatus['is_read']) {
            $markReadSql = "UPDATE message SET is_read = 1, time_read = :time_read WHERE message_id = :message_id";
            $markReadStmt = $db->prepare($markReadSql);
            $markReadStmt->execute([':time_read' => date('Y-m-d H:i:s'), ':message_id' => $messageId]);
        }

        $sql = "SELECT 
            m.*, 
            mf.name AS message_folder_name,
            sender.name AS sender_name,
            receiver.name AS receiver_name
        FROM message m
        LEFT JOIN message_folder mf ON m.message_folder_id = mf.message_folder_id
        LEFT JOIN admin sender ON m.sender_id = sender.admin_id
        LEFT JOIN admin receiver ON m.receiver_id = receiver.admin_id
        WHERE (m.sender_id = :admin_id OR m.receiver_id = :admin_id) AND m.message_id = :message_id
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':admin_id' => $currentAdminId, ':message_id' => $messageId));
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if($message !== false)
        {
            ?>
            <div class="back-controls">
                <button id="back-to-list" class="btn btn-secondary" onclick="backToList('message')"><?php echo $i18n->t('back_to_list'); ?></button>
                <?php if ($message['receiver_id'] == $currentAdminId && $message['is_read']): ?>
                    <button class="btn btn-warning" onclick="markMessageAsUnread('<?php echo $message['message_id']; ?>')"><?php echo $i18n->t('mark_as_unread'); ?></button>
                <?php endif; ?>
            </div>
            <div class="message-container">
                <div class="message-header">
                    <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                    <div class="message-meta">
                        <div><strong><?php echo $i18n->t('from'); ?>:</strong> <?php echo htmlspecialchars($message['sender_name'] ?? $i18n->t('system')); ?></div>
                        <div><strong><?php echo $i18n->t('to'); ?>:</strong> <?php echo htmlspecialchars($message['receiver_name'] ?? $i18n->t('system')); ?></div>
                        <div><strong><?php echo $i18n->t('time'); ?>:</strong> <?php echo htmlspecialchars($message['time_create']); ?></div>
                        <div><strong><?php echo $i18n->t('status'); ?>:</strong> 
                            <?php if ($message['is_read']): ?>
                                <span class="status-read"><?php echo $i18n->t('read_at'); ?> <?php echo htmlspecialchars($message['time_read']); ?></span>
                            <?php else: ?>
                                <span class="status-unread"><?php echo $i18n->t('unread'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="message-body">
                    <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                </div>
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="table-container detail-view">
            <?php echo $i18n->t('no_message'); ?>
            </div>
            <?php
        }
    }
    else
    {
    // Count total messages for pagination
    $countSql = "SELECT COUNT(*) FROM message WHERE sender_id = :admin_id OR receiver_id = :admin_id";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array(':admin_id' => $admin['admin_id']));
    $totalMessages = $countStmt->fetchColumn();
    $totalPages = ceil($totalMessages / $dataLimit);

    $sql = "SELECT 
        m.message_id, m.subject, m.content, m.is_read, m.time_create,
        sender.name AS sender_name
    FROM message m
    LEFT JOIN admin sender ON m.sender_id = sender.admin_id
    WHERE m.sender_id = :admin_id OR m.receiver_id = :admin_id 
    ORDER BY m.time_create DESC
    LIMIT $dataLimit OFFSET $offset
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(':admin_id' => $admin['admin_id']));
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="message-list-container">
        <?php
        foreach ($messages as $message) {
            $content = trim($message['content']);
            $isReadClass = $message['is_read'] ? 'read' : 'unread';
            ?>
            <div class="message-item <?php echo $isReadClass; ?>">
                <span class="message-status-indicator"></span>
                <div class="message-header">
                    <div class="message-link-wrapper">
                        <a href="#message?messageId=<?php echo $message['message_id']; ?>" class="message-link">
                            <span class="message-sender"><?php echo htmlspecialchars($message['sender_name'] ?? $i18n->t('system')); ?></span>
                            <span class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></span>
                        </a>
                        <span class="message-time"><?php echo htmlspecialchars($message['time_create']); ?></span>
                    </div>
                </div>
                <div class="message-content">
                    <?php
                    echo htmlspecialchars(substr($content, 0, 150)) . (strlen($content) > 150 ? '...' : '');
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <span><?php echo $i18n->t('page_of', $page, $totalPages, $totalMessages); ?></span>
            <?php if ($page > 1): ?>
                <a href="#message?page=<?php echo $page - 1; ?>" class="btn btn-secondary"><?php echo $i18n->t('previous'); ?></a>
            <?php endif; ?>

            <?php
            $window = 1; // Number of pages to show before and after the current page
            $startPage = max(1, $page - $window);
            $endPage = min($totalPages, $page + $window);

            if ($startPage > 1) {
                echo '<a href="#message?page=1" class="btn btn-secondary">1</a>';
                if ($startPage > 2) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="#message?page=<?php echo $i; ?>" class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
            <?php endfor;

            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                echo '<a href="#message?page='.$totalPages.'" class="btn btn-secondary">'.$totalPages.'</a>';
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="#message?page=<?php echo $page + 1; ?>" class="btn btn-secondary"><?php echo $i18n->t('next'); ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
    exit;
}

?>
