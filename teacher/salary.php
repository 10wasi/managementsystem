<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['teacher']);

$teacher_id = $_SESSION['user']['id'];
$teacher = fetchOne("SELECT * FROM teachers WHERE user_id = ?", [$teacher_id]);
$studentCount = fetchOne("SELECT COUNT(*) as cnt FROM students WHERE assigned_teacher_id = ?", [$teacher_id])['cnt'];
$base = $teacher['per_student_pay'] * $studentCount;
$total = $base + $teacher['bonus'] + $teacher['increment'];

$pageTitle = 'My Salary';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Salary Breakdown</h5>
    </div>
    <div class="card-body">
        <ul class="list-group">
            <li class="list-group-item">Students Assigned: <?php echo $studentCount; ?> students</li>
            <li class="list-group-item">Per Student Pay: Rs.<?php echo number_format($teacher['per_student_pay']); ?></li>
            <li class="list-group-item">Base Salary (<?php echo $studentCount; ?> × Rs.<?php echo number_format($teacher['per_student_pay']); ?>): Rs.<?php echo number_format($base); ?></li>
            <li class="list-group-item">Bonus: +Rs.<?php echo number_format($teacher['bonus']); ?></li>
            <li class="list-group-item">Increment: +Rs.<?php echo number_format($teacher['increment']); ?></li>
            <li class="list-group-item list-group-item-primary"><strong>Total Salary: Rs.<?php echo number_format($total); ?></strong></li>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>