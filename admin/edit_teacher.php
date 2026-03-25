<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$teacher = fetchOne("SELECT u.id, u.name, u.username, t.per_student_pay, t.bonus, t.increment, t.phone FROM users u JOIN teachers t ON u.id = t.user_id WHERE u.id = ? AND u.role = 'teacher'", [$id]);
if (!$teacher) {
    header('Location: teachers.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $perStudentPay = (float) ($_POST['per_student_pay'] ?? 0);
    $bonus = (float) ($_POST['bonus'] ?? 0);
    $increment = (float) ($_POST['increment'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';

    $db = getDB();
    $db->beginTransaction();
    try {
        $updates = ['name' => $name, 'username' => $username];
        if (!empty($new_password)) {
            $updates['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        update('users', $updates, 'id = :id', ['id' => $id]);
        update('teachers', [
            'phone' => $phone,
            'per_student_pay' => $perStudentPay,
            'bonus' => $bonus,
            'increment' => $increment,
        ], 'user_id = :id', ['id' => $id]);

        $db->commit();
        $success = "Teacher updated successfully.";
        $teacher = fetchOne("SELECT u.id, u.name, u.username, t.per_student_pay, t.bonus, t.increment, t.phone FROM users u JOIN teachers t ON u.id = t.user_id WHERE u.id = ? AND u.role = 'teacher'", [$id]);
    } catch (Throwable $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$pageTitle = 'Edit Teacher';
include '../includes/header.php';
?>
<div class="card">
    <div class="card-header"><h5>Edit Teacher</h5></div>
    <div class="card-body">
        <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($teacher['name']); ?>" required></div>
                    <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($teacher['username']); ?>" required></div>
                    <div class="mb-3"><label>New Password (blank = keep current)</label><input type="password" name="new_password" class="form-control"></div>
                    <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($teacher['phone']); ?>"></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3"><label>Per Student Pay</label><input type="number" name="per_student_pay" class="form-control" value="<?php echo htmlspecialchars((string) $teacher['per_student_pay']); ?>"></div>
                    <div class="mb-3"><label>Bonus</label><input type="number" name="bonus" class="form-control" value="<?php echo htmlspecialchars((string) $teacher['bonus']); ?>"></div>
                    <div class="mb-3"><label>Increment</label><input type="number" name="increment" class="form-control" value="<?php echo htmlspecialchars((string) $teacher['increment']); ?>"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Teacher</button>
            <a href="teachers.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>