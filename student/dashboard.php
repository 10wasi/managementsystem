<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['student']);

$student_id = $_SESSION['user']['id'];

$student = fetchOne("SELECT s.*, u.name, t.name as teacher_name FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN users t ON s.assigned_teacher_id = t.id WHERE u.id = ?", [$student_id]);

// Attendance summary for current month
$month = date('m');
$year = date('Y');
$present = fetchOne("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = ? AND date LIKE ? AND status = 'P'", [$student_id, "$year-$month-%"])['cnt'];
$absent = fetchOne("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = ? AND date LIKE ? AND status = 'A'", [$student_id, "$year-$month-%"])['cnt'];
$leave = fetchOne("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = ? AND date LIKE ? AND status = 'L'", [$student_id, "$year-$month-%"])['cnt'];

// Exam marks
$marks = fetchAll("SELECT subject, score FROM exam_marks WHERE student_id = ?", [$student_id]);
$notificationCount = fetchOne("SELECT COUNT(*) AS cnt FROM notifications WHERE recipient_role IN ('all','student') OR recipient_id = ?", [$student_id])['cnt'] ?? 0;

$pageTitle = 'Student Dashboard';
include '../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <h4 class="mb-1">🏠 My Dashboard</h4>
        <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Student'); ?> | <?php echo date('l, d M Y'); ?></small>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size:28px;">🎓</div>
                <h4 class="mb-0"><?php echo htmlspecialchars($student['grade']); ?></h4>
                <small class="text-muted">My Grade</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size:28px;">⏰</div>
                <h5 class="mb-0"><?php echo htmlspecialchars($student['class_time']); ?></h5>
                <small class="text-muted">Class Time</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size:28px;">✅</div>
                <h4 class="mb-0"><?php echo $present; ?></h4>
                <small class="text-muted">Present (<?php echo date('M'); ?>)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size:28px;">🔔</div>
                <h4 class="mb-0"><?php echo (int) $notificationCount; ?></h4>
                <small class="text-muted">Notifications</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">🗓️ Attendance — <?php echo date('F Y'); ?></h5></div>
            <div class="card-body text-center">
                <div class="d-flex justify-content-center gap-4 mb-3">
                    <div><h3 class="text-success"><?php echo $present; ?></h3><small>Present</small></div>
                    <div><h3 class="text-danger"><?php echo $absent; ?></h3><small>Absent</small></div>
                    <div><h3 class="text-warning"><?php echo $leave; ?></h3><small>Leave</small></div>
                </div>
                <a href="attendance.php" class="btn btn-sm btn-primary">Full Record →</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">📊 My Exam Marks</h5></div>
            <div class="card-body">
                <?php if (empty($marks)): ?>
                    <p class="text-muted mb-0">Marks not entered yet.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($marks as $m): ?>
                            <li>📘 <?php echo htmlspecialchars($m['subject']); ?>: <strong><?php echo (int) $m['score']; ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">👩‍🏫 My Teacher</h5>
            </div>
            <div class="card-body">
                <?php if ($student['teacher_name']): ?>
                    <p><?php echo htmlspecialchars($student['teacher_name']); ?></p>
                <?php else: ?>
                    <p>No teacher assigned.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>