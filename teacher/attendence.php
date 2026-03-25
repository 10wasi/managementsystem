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

$daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', (int) $year, (int) $month)));

$pageTitle = 'Mark Attendance';
include '../includes/header.php';
?>

<div class="mb-3">
    <h4 class="mb-0">📋 Mark Attendance</h4>
    <small class="text-muted">Mark attendance for your students</small>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1">YEAR</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo (int) $y === (int) $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-1">MONTH</label>
                <select name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo str_pad((string) $m, 2, '0', STR_PAD_LEFT); ?>" <?php echo (int) $m === (int) $month ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">🔎 View</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><?php echo date('F Y', strtotime("$year-$month-01")); ?></h5>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
        <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <form method="post">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Gr.</th>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                <th><?php echo $d; ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars((string) $student['grade']); ?></span></td>
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
            <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>