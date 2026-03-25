<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

// Handle update of bonus/increment via edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = (int) ($_POST['teacher_id'] ?? 0);
    $bonus = (float) ($_POST['bonus'] ?? 0);
    $increment = (float) ($_POST['increment'] ?? 0);
    update('teachers', ['bonus' => $bonus, 'increment' => $increment], 'user_id = :id', ['id' => $teacher_id]);
    $success = "Teacher salary updated.";
}

$teachers = fetchAll("SELECT u.id, u.name, t.per_student_pay, t.bonus, t.increment, COUNT(s.user_id) as student_count FROM users u JOIN teachers t ON u.id = t.user_id LEFT JOIN students s ON s.assigned_teacher_id = u.id WHERE u.role = 'teacher' GROUP BY u.id");

$pageTitle = 'Salary Management';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Teacher Salaries</h5>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th><th>Teacher</th><th>Students</th><th>Per Student Pay</th><th>Base Salary</th><th>Bonus</th><th>Increment</th><th>Total Salary</th><th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($teachers as $t): 
                        $base = $t['per_student_pay'] * $t['student_count'];
                        $total = $base + $t['bonus'] + $t['increment'];
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td><?php echo $t['student_count']; ?> students</td>
                        <td>Rs.<?php echo number_format($t['per_student_pay']); ?></td>
                        <td>Rs.<?php echo number_format($base); ?></td>
                        <td>+Rs.<?php echo $t['bonus']; ?></td>
                        <td>+Rs.<?php echo $t['increment']; ?></td>
                        <td>Rs.<?php echo number_format($total); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $t['id']; ?>">Edit</button>
                        </td>
                    </tr>
                    <!-- Modal for editing bonus/increment -->
                    <div class="modal fade" id="editModal<?php echo $t['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Salary: <?php echo htmlspecialchars($t['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="teacher_id" value="<?php echo $t['id']; ?>">
                                        <div class="mb-3">
                                            <label>Bonus (Rs.)</label>
                                            <input type="number" name="bonus" class="form-control" value="<?php echo $t['bonus']; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label>Increment (Rs.)</label>
                                            <input type="number" name="increment" class="form-control" value="<?php echo $t['increment']; ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>