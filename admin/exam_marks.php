<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $score = (int) ($_POST['score'] ?? 0);
    $exam_date = $_POST['exam_date'] ?? date('Y-m-d');
    if ($student_id > 0 && $subject !== '') {
        insert('exam_marks', [
            'student_id' => $student_id,
            'subject' => $subject,
            'score' => $score,
            'exam_date' => $exam_date
        ]);
        $success = "Exam mark saved.";
    } else {
        $error = "Student and subject are required.";
    }
}

$students = fetchAll("SELECT u.id, u.name, s.grade FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student' ORDER BY u.name");
$marks = fetchAll("SELECT em.*, u.name FROM exam_marks em JOIN users u ON em.student_id = u.id ORDER BY em.exam_date DESC, em.id DESC LIMIT 100");
$pageTitle = 'Exam Marks';
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>Add Exam Mark</h5></div>
            <div class="card-body">
                <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Student</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">Select student</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']) . ' (' . htmlspecialchars($s['grade']) . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Subject</label><input type="text" name="subject" class="form-control" required></div>
                    <div class="mb-3"><label>Score (0-100)</label><input type="number" min="0" max="100" name="score" class="form-control" required></div>
                    <div class="mb-3"><label>Exam Date</label><input type="date" name="exam_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>
                    <button type="submit" class="btn btn-primary">Save Mark</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5>Recent Marks</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Date</th><th>Student</th><th>Subject</th><th>Score</th></tr></thead>
                        <tbody>
                            <?php if (empty($marks)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No marks found.</td></tr>
                            <?php else: foreach ($marks as $m): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m['exam_date']); ?></td>
                                    <td><?php echo htmlspecialchars($m['name']); ?></td>
                                    <td><?php echo htmlspecialchars($m['subject']); ?></td>
                                    <td><?php echo (int) $m['score']; ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>