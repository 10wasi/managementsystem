<?php
function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

function checkRole($allowedRoles) {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
    if (!in_array($_SESSION['user']['role'], $allowedRoles)) {
        // Redirect to the appropriate dashboard
        switch ($_SESSION['user']['role']) {
            case 'admin':
                header('Location: ' . BASE_URL . 'admin/dashboard.php');
                break;
            case 'teacher':
                header('Location: ' . BASE_URL . 'teacher/dashboard.php');
                break;
            case 'student':
                header('Location: ' . BASE_URL . 'student/dashboard.php');
                break;
        }
        exit;
    }
}