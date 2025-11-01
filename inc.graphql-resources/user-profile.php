<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

// --- end i18n ---

// Handle POST request for updating user profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/database.php';

    try {
        $sql = "UPDATE admin SET name = :name, email = :email, gender = :gender, birth_day = :birth_day, phone = :phone WHERE username = :username";
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            ':name' => htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : ''),
            ':email' => htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''),
            ':gender' => htmlspecialchars(isset($_POST['gender']) ? $_POST['gender'] : ''),
            ':birth_day' => htmlspecialchars(isset($_POST['birth_day']) ? $_POST['birth_day'] : ''),
            ':phone' => htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : ''),
            ':username' => $_SESSION['username'] // Use session username for security
        ));

        echo json_encode(array('success' => true, 'message' => $i18n->t('profile_updated_successfully')));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'message' => $i18n->t('failed_to_update_profile', $e->getMessage())));
    }
    exit;
}



/*
CREATE TABLE IF NOT EXISTS admin (
	 admin_id NVARCHAR(40) PRIMARY KEY NOT NULL,
	 name NVARCHAR(100) NULL,
	 username NVARCHAR(100) NULL,
	 password NVARCHAR(512) NULL,
	 password_version NVARCHAR(512) NULL,
	 admin_level_id NVARCHAR(40) NULL,
	 gender NVARCHAR(2) NULL,
	 birth_day DATE NULL,
	 email NVARCHAR(100) NULL,
	 phone NVARCHAR(100) NULL,
	 language_id NVARCHAR(40) NULL,
	 validation_code TEXT NULL,
	 last_reset_password TIMESTAMP NULL,
	 blocked BOOLEAN NULL DEFAULT 0,
	 time_create TIMESTAMP NULL,
	 time_edit TIMESTAMP NULL,
	 admin_create NVARCHAR(40) NULL,
	 admin_edit NVARCHAR(40) NULL,
	 ip_create NVARCHAR(50) NULL,
	 ip_edit NVARCHAR(50) NULL,
	 active BOOLEAN NULL DEFAULT 1
);
*/
try
{
$sql = "SELECT admin.*, (SELECT admin_level.name FROM admin_level WHERE admin_level.admin_level_id = admin.admin_level_id) AS admin_level_name FROM admin WHERE admin.username = :username";
$stmt = $db->prepare($sql);
$stmt->execute(array(':username' => $_SESSION['username']));
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_GET) && isset($_GET['action']) && $_GET['action'] == 'update') {
?>
    <div class="table-container detail-view">
        <form id="profile-update-form" class="form-group" onsubmit="handleProfileUpdate(event); return false;">
            <table class="table table-borderless">
                <tr>
                    <td><?php echo $i18n->t('admin_id'); ?></td>
                    <td><input type="text" name="admin_id" class="form-control" value="<?php echo htmlspecialchars($admin['admin_id']); ?>" readonly></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('name'); ?></td>
                    <td><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('username'); ?></td>
                    <td><input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('gender'); ?></td>
                    <td>
                        <select name="gender" class="form-control">
                            <option value="M" <?php echo ($admin['gender'] == 'M') ? 'selected' : ''; ?>><?php echo $i18n->t('male'); ?></option>
                            <option value="F" <?php echo ($admin['gender'] == 'F') ? 'selected' : ''; ?>><?php echo $i18n->t('female'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('birthday'); ?></td>
                    <td><input type="date" name="birth_day" class="form-control" value="<?php echo htmlspecialchars($admin['birth_day']); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('phone'); ?></td>
                    <td><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>"></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('email'); ?></td>
                    <td><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" class="btn btn-success"><?php echo $i18n->t('update'); ?></button>
                        <button type="button" class="btn btn-secondary" onclick="window.location='#user-profile'"><?php echo $i18n->t('cancel'); ?></button>
                    </td>
                </tr>
            </table>
        </form>
    </div>

<?php
} else {
?>
    <div class="table-container detail-view">
        <form action="" class="form-group">
            <table class="table table-borderless">
                <tr>
                    <td><?php echo $i18n->t('admin_id'); ?></td>
                    <td><?php echo htmlspecialchars($admin['admin_id']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('name'); ?></td>
                    <td><?php echo htmlspecialchars($admin['name']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('username'); ?></td>
                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('gender'); ?></td>
                    <td><?php echo ($admin['gender'] == 'M') ? $i18n->t('male') : $i18n->t('female'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('birthday'); ?></td>
                    <td><?php echo htmlspecialchars($admin['birth_day']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('phone'); ?></td>
                    <td><?php echo htmlspecialchars($admin['phone']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('email'); ?></td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('admin_level_id'); ?></td>
                    <td><?php echo htmlspecialchars($admin['admin_level_name']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('language_id'); ?></td>
                    <td><?php echo htmlspecialchars($admin['language_id']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('last_reset_password'); ?></td>
                    <td><?php echo htmlspecialchars($admin['last_reset_password']); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('blocked'); ?></td>
                    <td><?php echo $admin['blocked'] ? $i18n->t('yes') : $i18n->t('no'); ?></td>
                </tr>
                <tr>
                    <td><?php echo $i18n->t('active'); ?></td>
                    <td><?php echo $admin['active'] ? $i18n->t('yes') : $i18n->t('no'); ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="window.location='#user-profile-update'"><?php echo $i18n->t('edit'); ?></button>
                        <button type="button" class="btn btn-warning" onclick="window.location='#update-password'"><?php echo $i18n->t('update_password'); ?></button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php
}
}
catch(Exception $e)
{
    echo $e->getMessage();
    exit;
}
