<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['student']);

$student_id = $_SESSION['user']['id'];
$marks = fetchAll("SELECT subject, score, exam_date FROM exam_marks WHERE student_id = ? ORDER BY exam_date DESC", [$student_id]);

$pageTitle = 'My Marks';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Exam Marks</h5>
    </div>
    <div class="card-body">
        <?php if (empty($marks)): ?>
            <p class="text-muted">No marks entered yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Subject</th><th>Score</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($marks as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['subject']); ?></td>
                                <td><?php echo $m['score']; ?></td>
                                <td><?php echo $m['exam_date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>