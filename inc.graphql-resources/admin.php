<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header('Localtion: ./'.basename(__FILE__, '.php'));
    exit();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

// Handle POST requests for all admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $adminId = isset($_POST['adminId']) ? $_POST['adminId'] : null;

    try {
        if ($action === 'create' || $action === 'update') {
            $name = $_POST['name'];
            $username = $_POST['username'];
            $email = $_POST['email'];
            $admin_level_id = $_POST['admin_level_id'];
            $active = isset($_POST['active']) ? 1 : 0;

            if ($action === 'create') {
                $password = $_POST['password'];
                if (empty($password)) {
                    throw new Exception($i18n->t('password_is_required'));
                }
                $hashedPassword = sha1(sha1($password));
                $newId = uniqid();

                $sql = "INSERT INTO admin (admin_id, name, username, email, password, admin_level_id, active, time_create, admin_create, ip_create) 
                        VALUES (:admin_id, :name, :username, :email, :password, :admin_level_id, :active, :time_create, :admin_create, :ip_create)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':admin_id' => $newId,
                    ':name' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':admin_level_id' => $admin_level_id,
                    ':active' => $active,
                    ':time_create' => date('Y-m-d H:i:s'),
                    ':admin_create' => $appAdmin['admin_id'],
                    ':ip_create' => $_SERVER['REMOTE_ADDR']
                ]);
                echo json_encode(['success' => true, 'message' => $i18n->t('admin_created_successfully')]);
            } else { // Update
                if (!$adminId) throw new Exception($i18n->t('admin_id_required'));

                if($adminId == $appAdmin['admin_id']) {
                    $active = 1; // Must be active
                    $admin_level_id = $appAdmin['admin_level_id']; // Can not be changed
                }

                $sql = "UPDATE admin SET name = :name, username = :username, email = :email, admin_level_id = :admin_level_id, active = :active, 
                        time_edit = :time_edit, admin_edit = :admin_edit, ip_edit = :ip_edit WHERE admin_id = :admin_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':admin_level_id' => $admin_level_id,
                    ':active' => $active,
                    ':time_edit' => date('Y-m-d H:i:s'),
                    ':admin_edit' => $appAdmin['admin_id'],
                    ':ip_edit' => $_SERVER['REMOTE_ADDR'],
                    ':admin_id' => $adminId
                ]);
                echo json_encode(['success' => true, 'message' => $i18n->t('admin_updated_successfully')]);
            }
        } elseif ($action === 'toggle_active') {
            if (!$adminId) throw new Exception($i18n->t('admin_id_required'));
            if ($adminId === $appAdmin['admin_id']) {
                throw new Exception($i18n->t('cannot_deactivate_self'));
            }
            $stmt = $db->prepare("SELECT active FROM admin WHERE admin_id = :admin_id");
            $stmt->execute([':admin_id' => $adminId]);
            $currentStatus = $stmt->fetchColumn();
            
            $newStatus = $currentStatus ? 0 : 1;

            if($adminId == $appAdmin['admin_id']) {
                $newStatus = 1; // Must be active
            }

            $updateStmt = $db->prepare("UPDATE admin SET active = :active WHERE admin_id = :admin_id");
            $updateStmt->execute([':active' => $newStatus, ':admin_id' => $adminId]);
            echo json_encode(['success' => true, 'message' => $i18n->t('admin_status_updated')]);

        } elseif ($action === 'change_password') {
            if (!$adminId) throw new Exception($i18n->t('admin_id_required'));
            $password = $_POST['password'];
            if (empty($password)) {
                throw new Exception($i18n->t('password_is_required'));
            }
            $hashedPassword = sha1(sha1($password));
            $stmt = $db->prepare("UPDATE admin SET password = :password WHERE admin_id = :admin_id");
            $stmt->execute([':password' => $hashedPassword, ':admin_id' => $adminId]);
            echo json_encode(['success' => true, 'message' => $i18n->t('password_updated_successfully')]);

        } elseif ($action === 'delete') {
            if (!$adminId) throw new Exception($i18n->t('admin_id_required'));
            if ($adminId === $appAdmin['admin_id']) {
                throw new Exception($i18n->t('cannot_delete_self'));
            }
            $stmt = $db->prepare("DELETE FROM admin WHERE admin_id = :admin_id");
            $stmt->execute([':admin_id' => $adminId]);
            echo json_encode(['success' => true, 'message' => $i18n->t('admin_deleted_successfully')]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$configPath = __DIR__ . "/config/frontend-config.json";
$config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
$pagination = $config['pagination'] ?? ['pageSize' => 20];


$view = $_GET['view'] ?? 'list';
$adminId = $_GET['adminId'] ?? null;

function getAdminLevels($db) {
    return $db->query("SELECT admin_level_id, name FROM admin_level WHERE active = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
}

function getLanguages() {
    $langData = json_decode(file_get_contents(__DIR__ . '/langs/available-language.json'), true);
    $languages = [];
    foreach($langData['supported'] as $code => $name) {
        $languages[] = ['code' => $code, 'name' => $name];
    }
    return $languages;
}

if ($view === 'create' || ($view === 'edit' && $adminId)) {
    $admin = null;
    if ($view === 'edit') {
        $stmt = $db->prepare("SELECT * FROM admin WHERE admin_id = :admin_id");
        $stmt->execute([':admin_id' => $adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    $adminLevels = getAdminLevels($db);
    $languages = getLanguages();
    ?>
    <div class="back-controls">
        <a href="#admin" class="btn btn-secondary"><?php echo $i18n->t('back_to_list'); ?></a>
    </div>
    <div class="table-container detail-view">
        <h3><?php echo $i18n->t($view === 'create' ? 'add_new_admin' : 'edit_admin'); ?></h3>
        <form id="admin-form" class="form-group" onsubmit="handleAdminSave(event, '<?php echo $adminId; ?>'); return false;">
            <table class="table table-borderless">
                <tr>
                    <td><?php echo $i18n->t('name'); ?></td>
                    <td><input type="text" name="name" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required autocomplete="off"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('username'); ?></td>
                    <td><input type="text" name="username" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" required autocomplete="off"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('email'); ?></td>
                    <td><input type="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required autocomplete="off"></td>
                </tr>
                <?php if ($view === 'create'): ?>
                <tr>
                    <td><?php echo $i18n->t('password'); ?></td>
                    <td><input type="password" name="password" required autocomplete="new-password"></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><?php echo $i18n->t('admin_level'); ?></td>
                    <td>
                        <select name="admin_level_id" required>
                            <option value=""><?php echo $i18n->t('select_option'); ?></option>
                            <?php foreach ($adminLevels as $level): ?>
                                <option value="<?php echo $level['admin_level_id']; ?>" <?php echo (isset($admin['admin_level_id']) && $admin['admin_level_id'] == $level['admin_level_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($level['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('active'); ?></td>
                    <td><input type="checkbox" name="active" <?php echo (isset($admin['active']) && $admin['active']) || $view === 'create' ? 'checked' : ''; ?>></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success"><?php echo $i18n->t('save'); ?></button>
                        <a href="#admin" class="btn btn-secondary"><?php echo $i18n->t('cancel'); ?></a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
} else if ($view === 'change-password' && $adminId) {
    ?>
    <div class="back-controls">
        <a href="#admin/detail/<?php echo $adminId; ?>" class="btn btn-secondary"><?php echo $i18n->t('back_to_detail'); ?></a>
    </div>
    <div class="table-container detail-view">
        <h3><?php echo $i18n->t('change_password'); ?></h3>
        <form id="change-password-form" class="form-group" onsubmit="handleAdminChangePassword(event, '<?php echo $adminId; ?>'); return false;">
            <table class="table table-borderless">
                <tr>
                    <td><?php echo $i18n->t('new_password'); ?></td>
                    <td><input type="password" name="password" required autocomplete="new-password"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success"><?php echo $i18n->t('update'); ?></button>
                        <a href="#admin/detail/<?php echo $adminId; ?>" class="btn btn-secondary"><?php echo $i18n->t('cancel'); ?></a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
} else if ($adminId) { // Detail view
    $stmt = $db->prepare("
        SELECT a.*, al.name as admin_level_name 
        FROM admin a 
        LEFT JOIN admin_level al ON a.admin_level_id = al.admin_level_id
        WHERE a.admin_id = :admin_id
    ");
    $stmt->execute([':admin_id' => $adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        ?>
        <div class="back-controls">
            <a href="#admin" class="btn btn-secondary"><?php echo $i18n->t('back_to_list'); ?></a>
            <a href="#admin?view=edit&adminId=<?php echo $adminId; ?>" class="btn btn-primary"><?php echo $i18n->t('edit'); ?></a>
            <?php if ($admin['admin_id'] != $appAdmin['admin_id']): ?>
                <a href="#admin?view=change-password&adminId=<?php echo $adminId; ?>" class="btn btn-warning"><?php echo $i18n->t('change_password'); ?></a>
                <button class="btn <?php echo $admin['active'] ? 'btn-warning' : 'btn-success'; ?>" onclick="handleAdminToggleActive('<?php echo $adminId; ?>', <?php echo $admin['active'] ? 'true' : 'false'; ?>)">
                    <?php echo $admin['active'] ? $i18n->t('deactivate') : $i18n->t('activate'); ?>
                </button>
                <button class="btn btn-danger" onclick="handleAdminDelete('<?php echo $adminId; ?>')"><?php echo $i18n->t('delete'); ?></button>
            <?php endif; ?>
        </div>
        <div class="table-container detail-view">
            <table class="table">
                <tbody>
                    <tr><td><strong><?php echo $i18n->t('admin_id'); ?></strong></td><td><?php echo htmlspecialchars($admin['admin_id']); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('name'); ?></strong></td><td><?php echo htmlspecialchars($admin['name']); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('username'); ?></strong></td><td><?php echo htmlspecialchars($admin['username']); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('email'); ?></strong></td><td><?php echo htmlspecialchars($admin['email']); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('admin_level'); ?></strong></td><td><?php echo htmlspecialchars($admin['admin_level_name']); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('status'); ?></strong></td><td><?php echo $admin['active'] ? $i18n->t('active') : $i18n->t('inactive'); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('time_create'); ?></strong></td><td><?php echo htmlspecialchars($admin['time_create']); ?></td></tr>
                    <tr><td><strong><?php echo $i18n->t('time_edit'); ?></strong></td><td><?php echo htmlspecialchars($admin['time_edit']); ?></td></tr>
                </tbody>
            </table>
        </div>
        <?php
    } else {
        echo "<p>" . $i18n->t('admin_not_found') . "</p>";
    }
} else { // List view
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $dataLimit = abs($pagination['pageSize']);
    $offset = ($page - 1) * $dataLimit;

    $params = [];
    $whereClause = '';
    if (!empty($search)) {
        $whereClause = " WHERE (a.name LIKE :search OR a.username LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $countSql = "SELECT COUNT(*) FROM admin a" . $whereClause;
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalAdmins = $countStmt->fetchColumn();
    $totalPages = ceil($totalAdmins / $dataLimit);

    $sql = "SELECT a.admin_id, a.name, a.username, a.email, a.active, al.name as admin_level_name
            FROM admin a
            LEFT JOIN admin_level al ON a.admin_level_id = al.admin_level_id
            $whereClause
            ORDER BY a.name
            LIMIT $dataLimit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    
    <div id="filter-container" class="filter-container" style="display: block;">
        <form id="admin-search-form" class="search-form" onsubmit="handleAdminSearch(event)"> 
            <div class="filter-controls">
                
                <div class="form-group">
                    <label for="username"><?php echo $i18n->t('name'); ?></label>
                    <input type="text" name="search" id="username" placeholder="<?php echo $i18n->t('name'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $i18n->t('search'); ?></button>
                <a href="#admin?view=create" class="btn btn-primary"><?php echo $i18n->t('add_new_admin'); ?></a>
                
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo $i18n->t('name'); ?></th>
                    <th><?php echo $i18n->t('username'); ?></th>
                    <th><?php echo $i18n->t('email'); ?></th>
                    <th><?php echo $i18n->t('admin_level'); ?></th>
                    <th><?php echo $i18n->t('status'); ?></th>
                    <th><?php echo $i18n->t('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($admins) > 0): ?>
                    <?php foreach ($admins as $admin): ?>
                        <tr class="<?php echo $admin['active'] ? '' : 'inactive'; ?>">
                            <td><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['admin_level_name']); ?></td>
                            <td><?php echo $admin['active'] ? $i18n->t('active') : $i18n->t('inactive'); ?></td>
                            <td class="actions">
                                <a href="#admin?view=detail&adminId=<?php echo $admin['admin_id']; ?>" class="btn btn-sm btn-info"><?php echo $i18n->t('view'); ?></a>
                                <a href="#admin?view=edit&adminId=<?php echo $admin['admin_id']; ?>" class="btn btn-sm btn-primary"><?php echo $i18n->t('edit'); ?></a>
                                <?php if ($admin['admin_id'] != $appAdmin['admin_id']): ?>
                                    <a href="#admin?view=change-password&adminId=<?php echo $admin['admin_id']; ?>" class="btn btn-sm btn-warning"><?php echo $i18n->t('change_password'); ?></a>
                                    <button class="btn btn-sm <?php echo $admin['active'] ? 'btn-warning' : 'btn-success'; ?>" onclick="handleAdminToggleActive('<?php echo $admin['admin_id']; ?>', <?php echo $admin['active'] ? 'true' : 'false'; ?>)">
                                        <?php echo $admin['active'] ? $i18n->t('deactivate') : $i18n->t('activate'); ?>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="handleAdminDelete('<?php echo $admin['admin_id']; ?>')"><?php echo $i18n->t('delete'); ?></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><?php echo $i18n->t('no_admins_found'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <?php $searchQuery = !empty($search) ? '&search=' . urlencode($search) : ''; ?>
            <span><?php echo $i18n->t('page_of', $page, $totalPages, $totalAdmins); ?></span>
            <?php if ($page > 1): ?>
                <a href="#admin?page=<?php echo $page - 1; ?><?php echo $searchQuery; ?>" class="btn btn-secondary"><?php echo $i18n->t('previous'); ?></a>
            <?php endif; ?>

            <?php
            $window = 1;
            $startPage = max(1, $page - $window);
            $endPage = min($totalPages, $page + $window);

            if ($startPage > 1) {
                echo '<a href="#admin?page=1'.$searchQuery.'" class="btn btn-secondary">1</a>';
                if ($startPage > 2) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="#admin?page=<?php echo $i; ?><?php echo $searchQuery; ?>" class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
            <?php endfor;

            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                echo '<a href="#admin?page='.$totalPages.$searchQuery.'" class="btn btn-secondary">'.$totalPages.'</a>';
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="#admin?page=<?php echo $page + 1; ?><?php echo $searchQuery; ?>" class="btn btn-secondary"><?php echo $i18n->t('next'); ?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}
?>