<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $send_to = $_POST['send_to'];
    $message = trim($_POST['message']);
    $recipient_id = null;
    $recipient_role = null;

    if ($send_to === 'all') {
        $recipient_role = 'all';
    } elseif ($send_to === 'teachers') {
        $recipient_role = 'teacher';
    } elseif ($send_to === 'students') {
        $recipient_role = 'student';
    } elseif (strpos($send_to, 'user_') === 0) {
        $recipient_id = (int) substr($send_to, 5);
        $recipient_role = null;
    }

    insert('notifications', [
        'sender_id' => $_SESSION['user']['id'],
        'recipient_role' => $recipient_role,
        'recipient_id' => $recipient_id,
        'message' => $message
    ]);
    $success = "Notification sent.";
}

// Fetch sent notifications
$notifications = fetchAll("SELECT n.*, u.name as sender_name FROM notifications n LEFT JOIN users u ON n.sender_id = u.id ORDER BY n.created_at DESC LIMIT 20");

$pageTitle = 'Notifications';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Send New Notification</h5>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Send To</label>
                        <select name="send_to" class="form-control" required>
                            <option value="all">All (Students + Teachers)</option>
                            <option value="teachers">Teachers only</option>
                            <option value="students">Students only</option>
                            <?php
                            $allUsers = fetchAll("SELECT id, name, role FROM users WHERE role IN ('teacher','student') ORDER BY role, name");
                            foreach ($allUsers as $u):
                            ?>
                                <option value="user_<?php echo $u['id']; ?>"><?php echo ucfirst($u['role']) . ': ' . htmlspecialchars($u['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="send" class="btn btn-primary">Send Notification</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Sent Notifications</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($notifications as $n): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>To: <?php
                                    if ($n['recipient_role'] == 'all') echo 'All';
                                    elseif ($n['recipient_role']) echo ucfirst($n['recipient_role']) . 's';
                                    else echo 'User ID ' . $n['recipient_id'];
                                ?></strong>
                                <small><?php echo $n['created_at']; ?></small>
                            </div>
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($n['message'])); ?></p>
                            <small>From: <?php echo $n['sender_name'] ?? 'System'; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>