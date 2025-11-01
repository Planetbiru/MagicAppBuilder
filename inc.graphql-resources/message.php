<?php
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
        $sql = "SELECT message.*, message_folder.name AS message_folder_name 
        FROM message LEFT JOIN message_folder ON message.message_folder_id = message_folder.message_folder_id
        WHERE (message.sender_id = :sender_id OR message.receiver_id = :receiver_id) AND message.message_id = :message_id
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(':sender_id' => $admin['admin_id'], ':receiver_id' => $admin['admin_id'], ':message_id' => $_GET['messageId']));
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        if($message !== false)
        {
            ?>
            <div class="back-controls">
                <button id="back-to-list" class="btn btn-secondary" onclick="backToList()"><?php echo $i18n->t('back_to_list'); ?></button>
            </div>
            <div class="message-container">
                <div class="message-header">
                    <?php
                    echo htmlspecialchars($message['subject']);
                    ?>
                </div>
                <div class="message-content">
                    <?php
                    echo htmlspecialchars($message['content']);
                    ?>
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
    $countSql = "SELECT COUNT(*) FROM message WHERE sender_id = :sender_id OR receiver_id = :receiver_id";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array(':sender_id' => $admin['admin_id'], ':receiver_id' => $admin['admin_id']));
    $totalMessages = $countStmt->fetchColumn();
    $totalPages = ceil($totalMessages / $dataLimit);

    $sql = "SELECT message.*, message_folder.name AS message_folder_name 
    FROM message LEFT JOIN message_folder ON message.message_folder_id = message_folder.message_folder_id
    WHERE message.sender_id = :sender_id OR message.receiver_id = :receiver_id 
    ORDER BY message.time_create DESC
    LIMIT $dataLimit OFFSET $offset
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(':sender_id' => $admin['admin_id'], ':receiver_id' => $admin['admin_id']));
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="table-container detail-view">
        <?php
        foreach ($messages as $message) {
            $content = trim($message['content']);
            ?>
            <div class="message-container">
                <div class="message-header">
                    <a href="#message?messageId=<?php echo $message['message_id']; ?>">
                        <?php
                        echo htmlspecialchars($message['subject']);
                        ?>
                    </a>
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