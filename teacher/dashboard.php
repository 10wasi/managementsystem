<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['teacher']);

$teacher_id = $_SESSION['user']['id'];

// Get assigned students
$students = fetchAll("SELECT u.id, u.name, s.grade, s.class_time FROM students s JOIN users u ON s.user_id = u.id WHERE s.assigned_teacher_id = ? ORDER BY u.name", [$teacher_id]);

// Handle grade change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_grade'])) {
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $new_grade = trim((string) ($_POST['grade'] ?? ''));
    update('students', ['grade' => $new_grade], 'user_id = :id', ['id' => $student_id]);
    $success = "Grade updated.";
    // Refresh students list
    $students = fetchAll("SELECT u.id, u.name, s.grade, s.class_time FROM students s JOIN users u ON s.user_id = u.id WHERE s.assigned_teacher_id = ? ORDER BY u.name", [$teacher_id]);
}

$pageTitle = '👨‍🏫 Teacher Dashboard';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>📚 My Students & Class Schedule</h5>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>#</th><th>👤 Name</th><th>🎓 Grade</th><th>⏰ Class Time</th><th>📝 Change Grade</th></tr></thead>
                <tbody>
                    <?php $i = 1; foreach ($students as $s): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo $s['grade']; ?></td>
                        <td><?php echo $s['class_time']; ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                <select name="grade" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                    <option value="Grade 3" <?php echo $s['grade'] == 'Grade 3' ? 'selected' : ''; ?>>Grade 3</option>
                                    <option value="Grade 5" <?php echo $s['grade'] == 'Grade 5' ? 'selected' : ''; ?>>Grade 5</option>
                                    <option value="Grade 2" <?php echo $s['grade'] == 'Grade 2' ? 'selected' : ''; ?>>Grade 2</option>
                                </select>
                                <button type="submit" name="change_grade" class="btn btn-sm btn-primary">Change</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>