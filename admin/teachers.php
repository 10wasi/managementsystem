<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$pageTitle = 'Teachers Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $username = trim($_POST['username'] ?? '');
    $passwordPlain = (string) ($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $per_student_pay = (float) ($_POST['per_student_pay'] ?? 0);
    $bonus = (float) ($_POST['bonus'] ?? 0);
    $increment = (float) ($_POST['increment'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');

    $exists = fetchOne('SELECT id FROM users WHERE username = ?', [$username]);
    if ($exists) {
        $error = 'Username already exists.';
    } elseif ($username === '' || $name === '' || $passwordPlain === '') {
        $error = 'Username, name and password are required.';
    } else {
        $password = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $db = getDB();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password, role, name, display_name) VALUES (?, ?, 'teacher', ?, ?)");
            $stmt->execute([$username, $password, $name, $name]);
            $user_id = (int) $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO teachers (user_id, per_student_pay, bonus, increment, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $per_student_pay, $bonus, $increment, $phone]);

            $db->commit();
            $success = "Teacher added successfully.";
        } catch (Throwable $e) {
            $db->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int) ($_GET['delete'] ?? 0);
    delete('users', 'id = :id', ['id' => $id]);
    header('Location: teachers.php');
    exit;
}

$teachers = fetchAll("SELECT u.id, u.name, u.username, t.per_student_pay, t.bonus, t.increment, t.phone, COUNT(s.user_id) as student_count FROM users u JOIN teachers t ON u.id = t.user_id LEFT JOIN students s ON s.assigned_teacher_id = u.id WHERE u.role = 'teacher' GROUP BY u.id ORDER BY u.name");

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>All Teachers</h5>
                <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addTeacherModal">Add New Teacher</button>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>#</th><th>Name</th><th>Username</th><th>Students</th><th>Per Student</th><th>Bonus</th><th>Increment</th><th>Total Salary</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($teachers as $t): $total = ($t['per_student_pay'] * $t['student_count']) + $t['bonus'] + $t['increment']; ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($t['name']); ?></td>
                                <td><?php echo htmlspecialchars($t['username']); ?></td>
                                <td><?php echo (int) $t['student_count']; ?></td>
                                <td>Rs.<?php echo number_format((float) $t['per_student_pay']); ?></td>
                                <td>Rs.<?php echo number_format((float) $t['bonus']); ?></td>
                                <td>Rs.<?php echo number_format((float) $t['increment']); ?></td>
                                <td>Rs.<?php echo number_format((float) $total); ?></td>
                                <td>
                                    <a href="edit_teacher.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')">Delete</a>
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

<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                    <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3"><label>Per Student Pay</label><input type="number" name="per_student_pay" class="form-control" value="0"></div>
                    <div class="mb-3"><label>Bonus</label><input type="number" name="bonus" class="form-control" value="0"></div>
                    <div class="mb-3"><label>Increment</label><input type="number" name="increment" class="form-control" value="0"></div>
                    <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
