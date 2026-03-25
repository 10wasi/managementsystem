<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$user_id = $_SESSION['user']['id'];

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_to'])) {
    $recipient_id = $_POST['recipient_id'];
    $message = trim($_POST['message']);
    if ($message) {
        insert('messages', [
            'sender_id' => $user_id,
            'recipient_id' => $recipient_id,
            'message' => $message
        ]);
        $success = "Message sent.";
    }
}

// Get conversations: unique pairs where user is either sender or recipient
$conversations = fetchAll("
    SELECT DISTINCT
        CASE WHEN sender_id = ? THEN recipient_id ELSE sender_id END AS other_user_id
    FROM messages
    WHERE sender_id = ? OR recipient_id = ?
", [$user_id, $user_id, $user_id]);

$conversation_list = [];
foreach ($conversations as $c) {
    $other = fetchOne("SELECT id, name, role FROM users WHERE id = ?", [$c['other_user_id']]);
    if ($other) {
        $last = fetchOne("SELECT message, timestamp FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY timestamp DESC LIMIT 1", [$user_id, $other['id'], $other['id'], $user_id]);
        $conversation_list[] = [
            'user' => $other,
            'last_message' => $last['message'] ?? '',
            'last_time' => $last['timestamp'] ?? ''
        ];
    }
}

$pageTitle = 'Messages';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Conversations</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($conversation_list as $conv): ?>
                    <a href="?with=<?php echo $conv['user']['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo htmlspecialchars($conv['user']['name']); ?> (<?php echo ucfirst($conv['user']['role']); ?>)</strong>
                            <small><?php echo $conv['last_time'] ? date('d M H:i', strtotime($conv['last_time'])) : ''; ?></small>
                        </div>
                        <div class="text-muted small"><?php echo substr($conv['last_message'], 0, 50); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
                <h5>Send New Message</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Send To</label>
                        <select name="recipient_id" class="form-control" required>
                            <option value="">Select User</option>
                            <?php
                            $users = fetchAll("SELECT id, name, role FROM users WHERE id != ? ORDER BY role, name", [$user_id]);
                            foreach ($users as $u):
                            ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo ucfirst($u['role']) . ': ' . htmlspecialchars($u['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="send_to" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <?php
        if (isset($_GET['with'])) {
            $with = $_GET['with'];
            $otherUser = fetchOne("SELECT id, name, role FROM users WHERE id = ?", [$with]);
            if ($otherUser) {
                $messages = fetchAll("SELECT * FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY timestamp ASC", [$user_id, $with, $with, $user_id]);
                ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Conversation with <?php echo htmlspecialchars($otherUser['name']); ?></h5>
                    </div>
                    <div class="card-body" style="height: 400px; overflow-y: auto;">
                        <?php foreach ($messages as $msg): ?>
                            <div class="mb-2 <?php echo $msg['sender_id'] == $user_id ? 'text-end' : ''; ?>">
                                <div class="d-inline-block p-2 rounded <?php echo $msg['sender_id'] == $user_id ? 'bg-primary text-white' : 'bg-light'; ?>" style="max-width: 70%;">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    <br><small class="<?php echo $msg['sender_id'] == $user_id ? 'text-white-50' : 'text-muted'; ?>"><?php echo date('d M H:i', strtotime($msg['timestamp'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <form method="post">
                            <input type="hidden" name="recipient_id" value="<?php echo $with; ?>">
                            <div class="input-group">
                                <input type="text" name="message" class="form-control" placeholder="Type your reply...">
                                <button type="submit" name="send_to" class="btn btn-primary">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="card"><div class="card-body text-center">Select a conversation from the left or send a new message.</div></div>';
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>