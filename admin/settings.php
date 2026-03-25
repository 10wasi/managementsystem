<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

function handleLogoUpload(string $inputName, string $settingKey): array
{
    $targetDir = ROOT_PATH . '/assets/uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $file = $_FILES[$inputName] ?? null;
    if (!$file || !isset($file['tmp_name'])) {
        return ['error' => 'No file selected.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (!in_array($ext, $allowed, true) || (int) $file['size'] > 2 * 1024 * 1024) {
        return ['error' => 'Invalid file type or file size > 2MB.'];
    }

    $newName = $settingKey . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $targetDir . $newName)) {
        return ['error' => 'Failed to upload logo.'];
    }

    $old = getSetting($settingKey);
    if ($old && file_exists($targetDir . $old)) {
        @unlink($targetDir . $old);
    }
    setSetting($settingKey, $newName);
    return ['success' => 'Logo uploaded successfully.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_login_logo'])) {
    $result = handleLogoUpload('login_logo', 'login_logo');
    $login_logo_success = $result['success'] ?? null;
    $login_logo_error = $result['error'] ?? null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_sidebar_logo'])) {
    $result = handleLogoUpload('sidebar_logo', 'sidebar_logo');
    $sidebar_logo_success = $result['success'] ?? null;
    $sidebar_logo_error = $result['error'] ?? null;
}
if (isset($_POST['remove_login_logo'])) {
    $old = getSetting('login_logo');
    if ($old && file_exists(ROOT_PATH . '/assets/uploads/' . $old)) {
        @unlink(ROOT_PATH . '/assets/uploads/' . $old);
    }
    setSetting('login_logo', '');
    $login_logo_success = "Login logo removed.";
}
if (isset($_POST['remove_sidebar_logo'])) {
    $old = getSetting('sidebar_logo');
    if ($old && file_exists(ROOT_PATH . '/assets/uploads/' . $old)) {
        @unlink(ROOT_PATH . '/assets/uploads/' . $old);
    }
    setSetting('sidebar_logo', '');
    $sidebar_logo_success = "Sidebar logo removed.";
}

// Handle email update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_email'])) {
    $email = trim($_POST['contact_email']);
    setSetting('contact_email', $email);
    $email_success = "Email saved.";
}

// Handle credential change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_credentials'])) {
    $current = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $display_name = $_POST['display_name'];
    $username = $_POST['username'];

    $user = fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user']['id']]);
    if ($user && password_verify($current, $user['password'])) {
        $data = ['display_name' => $display_name, 'username' => $username];
        if (!empty($new_password)) {
            $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        update('users', $data, 'id = :id', ['id' => (int) $user['id']]);
        // Update session
        $_SESSION['user'] = fetchOne("SELECT * FROM users WHERE id = ?", [$user['id']]);
        $cred_success = "Credentials updated. You may need to log in again.";
    } else {
        $cred_error = "Current password is incorrect.";
    }
}

$login_logo = getSetting('login_logo');
$sidebar_logo = getSetting('sidebar_logo');
$contact_email = getSetting('contact_email');
$current_user = $_SESSION['user'];

$pageTitle = 'Settings';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Login Screen Logo</h5>
            </div>
            <div class="card-body">
                <p>This logo appears on the main login page.</p>
                <?php if ($login_logo): ?>
                    <div class="mb-3">
                        <img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $login_logo; ?>" style="max-height: 100px;" alt="Login logo">
                    </div>
                <?php endif; ?>
                <?php if (isset($login_logo_success)): ?><div class="alert alert-success"><?php echo $login_logo_success; ?></div><?php endif; ?>
                <?php if (isset($login_logo_error)): ?><div class="alert alert-danger"><?php echo $login_logo_error; ?></div><?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Select Image File</label>
                        <input type="file" name="login_logo" class="form-control" accept="image/*">
                        <small class="text-muted">JPG, PNG, GIF, WebP, SVG • Max 2MB</small>
                    </div>
                    <button type="submit" name="upload_login_logo" class="btn btn-primary">Upload Login Logo</button>
                    <button type="submit" name="remove_login_logo" class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <h5>Sidebar Logo</h5>
            </div>
            <div class="card-body">
                <p>This logo appears in the left sidebar.</p>
                <?php if ($sidebar_logo): ?>
                    <div class="mb-3">
                        <img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo $sidebar_logo; ?>" style="max-height: 100px;" alt="Sidebar logo">
                    </div>
                <?php endif; ?>
                <?php if (isset($sidebar_logo_success)): ?><div class="alert alert-success"><?php echo $sidebar_logo_success; ?></div><?php endif; ?>
                <?php if (isset($sidebar_logo_error)): ?><div class="alert alert-danger"><?php echo $sidebar_logo_error; ?></div><?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Select Image File</label>
                        <input type="file" name="sidebar_logo" class="form-control" accept="image/*">
                        <small class="text-muted">JPG, PNG, GIF, WebP, SVG • Max 2MB</small>
                    </div>
                    <button type="submit" name="upload_sidebar_logo" class="btn btn-primary">Upload Sidebar Logo</button>
                    <button type="submit" name="remove_sidebar_logo" class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Contact Email</h5>
            </div>
            <div class="card-body">
                <p>This email is shown on the login page when users forget their credentials.</p>
                <form method="post">
                    <div class="mb-3">
                        <label>Admin Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($contact_email); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Email</button>
                </form>
                <hr>
                <h6>Login Page Preview</h6>
                <div class="alert alert-info">
                    Forgot your username or password?<br>
                    Please contact your administrator:<br>
                    <strong><?php echo htmlspecialchars($contact_email ?: 'admin@example.com'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Change My Credentials</h5>
            </div>
            <div class="card-body">
                <?php if (isset($cred_success)): ?>
                    <div class="alert alert-success"><?php echo $cred_success; ?></div>
                <?php elseif (isset($cred_error)): ?>
                    <div class="alert alert-danger"><?php echo $cred_error; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Display Name</label>
                        <input type="text" name="display_name" class="form-control" value="<?php echo htmlspecialchars($current_user['display_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Current Password *required to save</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password (blank = keep current)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <button type="submit" name="change_credentials" class="btn btn-primary">Save Credentials</button>
                </form>
                <div class="alert alert-warning mt-3">
                    <strong>Important:</strong> After changing your username or password you will need to log in again with the new credentials.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>