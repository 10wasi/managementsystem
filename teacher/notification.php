<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['teacher']);

$teacher_id = $_SESSION['user']['id'];

// Fetch notifications for this teacher (role = teacher or recipient_id = teacher_id or all)
$notifications = fetchAll("SELECT * FROM notifications WHERE (recipient_role = 'teacher' OR recipient_id = ? OR recipient_role = 'all') ORDER BY created_at DESC", [$teacher_id]);

$pageTitle = 'Notifications';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Notifications</h5>
    </div>
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <p class="text-muted">No notifications.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>From: <?php echo $n['sender_id'] ? fetchOne("SELECT name FROM users WHERE id = ?", [$n['sender_id']])['name'] : 'System'; ?></strong>
                            <small><?php echo $n['created_at']; ?></small>
                        </div>
                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($n['message'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>