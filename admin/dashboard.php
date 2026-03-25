<?php
require_once '../config.php';
require_once '../includes/auth.php';

checkLogin();
checkRole(['admin']);

$pageTitle = '🛡️ Dashboard Overview';

// Get stats
$totalStudents = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'student'")['count'];
$totalTeachers = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")['count'];

// Monthly salary payout
$teachers = fetchAll("SELECT u.id, t.per_student_pay, t.bonus, t.increment FROM users u JOIN teachers t ON u.id = t.user_id");
$totalSalary = 0;
foreach ($teachers as $t) {
    $studentCount = fetchOne("SELECT COUNT(*) as cnt FROM students WHERE assigned_teacher_id = ?", [$t['id']])['cnt'];
    $base = $t['per_student_pay'] * $studentCount;
    $totalSalary += $base + $t['bonus'] + $t['increment'];
}

// Fee collected for current year
$year = date('Y');
$feeCollected = fetchOne(
    "SELECT COALESCE(SUM(COALESCE(s.yearly_fee, 0) / 12.0), 0) AS total
     FROM students s
     JOIN fee_payments fp ON s.user_id = fp.student_id
     WHERE fp.year = ? AND fp.status = 'paid'",
    [$year]
)['total'] ?? 0;

// Enrolled students (last 5)
$students = fetchAll("SELECT s.*, u.name, t.name as teacher_name FROM students s JOIN users u ON s.user_id = u.id LEFT JOIN users t ON s.assigned_teacher_id = t.id ORDER BY u.created_at DESC LIMIT 5");

// Teachers overview
$teachersOverview = fetchAll("SELECT u.name, COUNT(s.user_id) as student_count, (t.per_student_pay * COUNT(s.user_id) + t.bonus + t.increment) as monthly_salary FROM users u JOIN teachers t ON u.id = t.user_id LEFT JOIN students s ON s.assigned_teacher_id = u.id GROUP BY u.id");

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">🎓 Total Students</h5>
                <h2 class="card-text"><?php echo $totalStudents; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">👨‍🏫 Total Teachers</h5>
                <h2 class="card-text"><?php echo $totalTeachers; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">💰 Monthly Salary Payout</h5>
                <h2 class="card-text">Rs.<?php echo number_format($totalSalary); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">🏦 Fee Collected (<?php echo $year; ?>)</h5>
                <h2 class="card-text">Rs.<?php echo number_format($feeCollected); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>🧑‍🎓 Enrolled Students</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Name</th><th>Grade</th><th>Teacher</th><th>Class Time</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo $student['grade']; ?></td>
                                <td><?php echo $student['teacher_name'] ?? 'Not Assigned'; ?></td>
                                <td><?php echo $student['class_time']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>👩‍🏫 Teachers Overview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Teacher</th><th>Students</th><th>Monthly Salary</th></tr></thead>
                        <tbody>
                            <?php foreach ($teachersOverview as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['name']); ?></td>
                                <td><?php echo $t['student_count']; ?> students</td>
                                <td>Rs.<?php echo number_format($t['monthly_salary']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>