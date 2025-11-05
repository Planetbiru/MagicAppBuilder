<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inc/I18n.php';

// Handle POST request for updating password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $currentPassword = trim($currentPassword);
    $newPassword = trim($newPassword);
    $confirmPassword = trim($confirmPassword);

    if(empty($newPassword))
    {
        http_response_code(400);
        echo json_encode(array('success' => false, 'message' => $i18n->t('new_password_required')));
        exit;
    }
    

    // Validation
    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'message' => $i18n->t('password_mismatch')));
        exit;
    }

    try {
        // Fetch current user's hashed password
        $stmt = $db->prepare("SELECT password FROM admin WHERE username = :username");
        $stmt->execute(array(':username' => $_SESSION['username']));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify current password
        // NOTE: This assumes you are using PHP's password_hash() and password_verify() functions.
        // If you use a different hashing method (e.g., md5, sha1), you must change this logic.
        if (!$user || sha1(sha1($currentPassword)) != $user['password']) {
            http_response_code(401);
            echo json_encode(array('success' => false, 'message' => $i18n->t('incorrect_current_password')));
            exit;
        }

        // Hash the new password
        $newPasswordHash = sha1(sha1($newPassword));

        // Update the database
        $updateStmt = $db->prepare("UPDATE admin SET password = :password, last_reset_password = :now WHERE username = :username");
        $updateStmt->execute(array(
            ':password' => $newPasswordHash,
            ':now' => date('Y-m-d H:i:s'),
            ':username' => $_SESSION['username']
        ));

        echo json_encode(array('success' => true, 'message' => $i18n->t('password_updated_successfully')));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'message' => $i18n->t('failed_to_update_password') . ': ' . $e->getMessage()));
    }
    exit;
}
?>

<div class="table-container detail-view">
    <form id="password-update-form" class="form-group" onsubmit="handlePasswordUpdate(event); return false">
        <table class="table table-borderless">
            <tr>
                <td><?php echo $i18n->t('current_password'); ?></td>
                <td><input type="password" name="current_password" class="form-control" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><?php echo $i18n->t('new_password'); ?></td>
                <td><input type="password" name="new_password" class="form-control" autocomplete="off" required></td>
            </tr>
            <tr>
                <td><?php echo $i18n->t('confirm_password'); ?></td>
                <td><input type="password" name="confirm_password" class="form-control" autocomplete="off" required></td>
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
<script>
    async function handlePasswordUpdate(event) {
        event.preventDefault();
        const form = document.getElementById('password-update-form');
        const formData = new FormData(form);

        try {
            const response = await fetch('update-password.php?lang=<?php echo $languageId; ?>', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            const title = response.ok ? graphqlApp.t('success_title') : graphqlApp.t('error_title');
            await graphqlApp.customAlert({ title: title, message: result.message });
            if (response.ok) {
                window.location.hash = '#user-profile';
            }
        } catch (error) {
            console.error('Error updating password:', error);
            await graphqlApp.customAlert({ title: graphqlApp.t('error_title'), message: graphqlApp.t('unexpected_error_occurred') });
        }
    }
</script>