<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['teacher']);

$teacher_id = $_SESSION['user']['id'];
$students = fetchAll("SELECT u.id, u.name, s.grade FROM students s JOIN users u ON s.user_id = u.id WHERE s.assigned_teacher_id = ? ORDER BY u.name", [$teacher_id]);

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance = $_POST['attendance'] ?? [];
    $db = getDB();
    $db->beginTransaction();
    try {
        foreach ($attendance as $studentId => $days) {
            foreach ($days as $day => $status) {
                if ($status === '' || $status === null) {
                    continue;
                }
                $date = "$year-$month-$day";
                $stmt = $db->prepare("INSERT OR REPLACE INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
                $stmt->execute([(int) $studentId, $date, $status]);
            }
        }
        $db->commit();
        $success = "Attendance saved.";
    } catch (Throwable $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);

$pageTitle = 'My Students Attendance';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Attendance - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h5>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <form method="post">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                <th><?php echo $d; ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']) . ' (' . htmlspecialchars($student['grade']) . ')'; ?></td>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                $day = str_pad((string) $d, 2, '0', STR_PAD_LEFT);
                                $date = "$year-$month-$day";
                                $att = fetchOne("SELECT status FROM attendance WHERE student_id = ? AND date = ?", [$student['id'], $date]);
                                $status = $att['status'] ?? '';
                                ?>
                                <td>
                                    <select name="attendance[<?php echo $student['id']; ?>][<?php echo $day; ?>]" class="form-select form-select-sm">
                                        <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>-</option>
                                        <option value="P" <?php echo $status === 'P' ? 'selected' : ''; ?>>P</option>
                                        <option value="A" <?php echo $status === 'A' ? 'selected' : ''; ?>>A</option>
                                        <option value="L" <?php echo $status === 'L' ? 'selected' : ''; ?>>L</option>
                                    </select>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Save Attendance</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>