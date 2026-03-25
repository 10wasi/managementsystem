<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$id = $_GET['id'] ?? 0;
$student = fetchOne("SELECT u.id, u.name, u.username, s.grade, s.class_time, s.yearly_fee, s.phone, s.assigned_teacher_id FROM users u JOIN students s ON u.id = s.user_id WHERE u.id = ? AND u.role = 'student'", [$id]);
if (!$student) {
    header('Location: students.php');
    exit;
}

$teachers = fetchAll("SELECT id, name FROM users WHERE role = 'teacher'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $grade = $_POST['grade'];
    $class_time = $_POST['class_time'];
    $yearly_fee = $_POST['yearly_fee'];
    $phone = $_POST['phone'];
    $assigned_teacher = $_POST['assigned_teacher'] ?: null;
    $new_password = $_POST['new_password'];

    $db = getDB();
    $db->beginTransaction();
    try {
        // Update users table
        $updates = ['name' => $name, 'username' => $username];
        if (!empty($new_password)) {
            $updates['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        update('users', $updates, 'id = :id', ['id' => (int) $id]);

        // Update students table
        update('students', [
            'grade' => $grade,
            'class_time' => $class_time,
            'yearly_fee' => $yearly_fee,
            'phone' => $phone,
            'assigned_teacher_id' => $assigned_teacher
        ], 'user_id = :id', ['id' => (int) $id]);

        $db->commit();
        $success = "Student updated successfully.";
        // Refresh student data
        $student = fetchOne("SELECT u.id, u.name, u.username, s.grade, s.class_time, s.yearly_fee, s.phone, s.assigned_teacher_id FROM users u JOIN students s ON u.id = s.user_id WHERE u.id = ?", [$id]);
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$pageTitle = 'Edit Student';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Edit Student</h5>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($student['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password (blank = keep current)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Grade</label>
                        <input type="text" name="grade" class="form-control" value="<?php echo $student['grade']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Assign Teacher</label>
                        <select name="assigned_teacher" class="form-control">
                            <option value="">None</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $student['assigned_teacher_id'] == $t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Class Time</label>
                        <input type="text" name="class_time" class="form-control" value="<?php echo $student['class_time']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Yearly Fee (Rs.)</label>
                        <input type="number" name="yearly_fee" class="form-control" value="<?php echo $student['yearly_fee']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $student['phone']; ?>">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="students.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>