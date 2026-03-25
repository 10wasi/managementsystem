<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = getDB()->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        switch ($user['role']) {
            case 'admin': header('Location: admin/dashboard.php'); break;
            case 'teacher': header('Location: teacher/dashboard.php'); break;
            case 'student': header('Location: student/dashboard.php'); break;
        }
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

$pageTitle = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LI Management System - Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08), transparent 35%),
                linear-gradient(135deg, #16135a 0%, #241b87 55%, #2f1b8f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .auth-card {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
            overflow: hidden;
        }
        .card-header {
            background: white;
            border-bottom: none;
            text-align: center;
            padding-top: 28px;
        }
        .card-header img {
            max-height: 74px;
        }
        .brand-title {
            color: #171a56;
            font-weight: 700;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        .brand-subtitle {
            color: #8c8f9a;
            font-size: 0.86rem;
        }
        .card-body {
            padding: 1.5rem 1.6rem 1.25rem;
        }
        .btn-primary {
            background: #2a2c86;
            border: none;
            border-radius: 10px;
            padding: 10px 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #1f2172;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #dde1ea;
        }
        .form-label {
            font-size: 0.72rem;
            color: #6d7383;
            font-weight: 700;
            letter-spacing: .4px;
            margin-bottom: 6px;
        }
        .contact-box {
            margin-top: 16px;
            border: 1px solid #e6e8ef;
            border-radius: 10px;
            background: #f8f9fd;
            color: #6b7280;
            padding: 10px 12px;
            font-size: .84rem;
        }
        .contact-box a {
            color: #2a2c86;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card auth-card">
                    <div class="card-header">
                        <?php
                        $logo = getSetting('login_logo');
                        $defaultLogo = 'learning-impact-logo.png';
                        $logoPath = '';
                        if (!empty($logo) && file_exists(ROOT_PATH . '/assets/uploads/' . $logo)) {
                            $logoPath = 'assets/uploads/' . $logo;
                        } elseif (file_exists(ROOT_PATH . '/assets/uploads/' . $defaultLogo)) {
                            $logoPath = 'assets/uploads/' . $defaultLogo;
                        }
                        if ($logoPath !== '') {
                            echo '<img src="' . htmlspecialchars($logoPath) . '" alt="Logo">';
                        }
                        ?>
                        <h5 class="brand-title">Learning Impact</h5>
                        <div class="brand-subtitle">Management System — Please sign in</div>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">USERNAME</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">PASSWORD</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Sign In</button>
                        </form>
                        <div class="contact-box">
                            Forgot your username or password?<br>Please contact your administrator:<br>
                            <a href="mailto:<?php echo getSetting('contact_email') ?: 'admin@example.com'; ?>">
                                <?php echo getSetting('contact_email') ?: 'admin@example.com'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>