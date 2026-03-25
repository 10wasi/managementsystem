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

<div class="mb-3">
    <h4 class="mb-0">💰 My Salary</h4>
    <small class="text-muted">Salary breakdown</small>
</div>

<div class="card" style="max-width: 760px;">
    <div class="card-header bg-white">
        <h5 class="mb-0">Salary Breakdown</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <tbody>
                    <tr>
                        <td>Students Assigned</td>
                        <td><span class="badge bg-info text-dark"><?php echo (int) $studentCount; ?> students</span></td>
                    </tr>
                    <tr>
                        <td>Per Student Pay</td>
                        <td>Rs.<?php echo number_format((float) $teacher['per_student_pay']); ?></td>
                    </tr>
                    <tr>
                        <td>Base Salary (<?php echo (int) $studentCount; ?> × Rs.<?php echo number_format((float) $teacher['per_student_pay']); ?>)</td>
                        <td><strong>Rs.<?php echo number_format((float) $base); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Bonus</td>
                        <td class="text-success"><strong>+Rs.<?php echo number_format((float) $teacher['bonus']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Increment</td>
                        <td class="text-success"><strong>+Rs.<?php echo number_format((float) $teacher['increment']); ?></strong></td>
                    </tr>
                    <tr class="table-light">
                        <td><strong>🔥 Total Salary</strong></td>
                        <td class="text-success"><strong>Rs.<?php echo number_format((float) $total); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>