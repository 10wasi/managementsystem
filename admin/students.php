<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$pageTitle = 'Students Management';

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = trim($_POST['name']);
    $grade = $_POST['grade'];
    $class_time = $_POST['class_time'];
    $yearly_fee = $_POST['yearly_fee'];
    $phone = $_POST['phone'];
    $assigned_teacher = $_POST['assigned_teacher'] ?: null;

    try {
        $db = getDB();
        $db->beginTransaction();
        // Insert into users
        $stmt = $db->prepare("INSERT INTO users (username, password, role, name, display_name) VALUES (?, ?, 'student', ?, ?)");
        $stmt->execute([$username, $password, $name, $name]);
        $user_id = $db->lastInsertId();
        // Insert into students
        $stmt = $db->prepare("INSERT INTO students (user_id, grade, class_time, yearly_fee, phone, assigned_teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $grade, $class_time, $yearly_fee, $phone, $assigned_teacher]);
        $db->commit();
        $success = "Student added successfully.";
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) ($_GET['delete'] ?? 0);
    delete('users', 'id = :id', ['id' => $id]);
    header('Location: students.php');
    exit;
}

// Fetch all students with teacher names
$students = fetchAll("SELECT u.id, u.name, s.grade, s.class_time, s.yearly_fee, s.phone, t.name as teacher_name, s.assigned_teacher_id FROM users u JOIN students s ON u.id = s.user_id LEFT JOIN users t ON s.assigned_teacher_id = t.id WHERE u.role = 'student' ORDER BY u.name");
$teachers = fetchAll("SELECT id, name FROM users WHERE role = 'teacher'");

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>All Students</h5>
                <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add New Student</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>#</th><th>Name</th><th>Grade</th><th>Teacher</th><th>Class Time</th><th>Yearly Fee</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($s['name']); ?></td>
                                <td><?php echo $s['grade']; ?></td>
                                <td><?php echo $s['teacher_name'] ?? 'Not Assigned'; ?></td>
                                <td><?php echo $s['class_time']; ?></td>
                                <td>Rs.<?php echo number_format($s['yearly_fee']); ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Grade</label>
                        <input type="text" name="grade" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Class Time</label>
                        <input type="text" name="class_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Yearly Fee (Rs.)</label>
                        <input type="number" name="yearly_fee" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Assign Teacher</label>
                        <select name="assigned_teacher" class="form-control">
                            <option value="">None</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>