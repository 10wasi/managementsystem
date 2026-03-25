<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save attendance
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
                $stmt->execute([$studentId, $date, $status]);
            }
        }
        $db->commit();
        $success = "Attendance saved.";
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get all students
$students = fetchAll("SELECT u.id, u.name, s.grade FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student' ORDER BY u.name");

// Get days in month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('N', strtotime("$year-$month-01")); // 1=Monday, 7=Sunday
// We'll use a simple table with columns for each day.

$pageTitle = 'Attendance Management';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Attendance - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h5>
        <form method="get" class="row g-3 mt-2">
            <div class="col-auto">
                <label>Year</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y')-2; $y <= date('Y')+2; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <label>Month</label>
                <select name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo str_pad($m,2,'0',STR_PAD_LEFT); ?>" <?php echo $m == $month ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto align-self-end">
                <button type="submit" class="btn btn-primary">View</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

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
                                <td><?php echo htmlspecialchars($student['name']) . ' (' . $student['grade'] . ')'; ?></td>
                                <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                    <?php
                                    $date = "$year-$month-" . str_pad($d,2,'0',STR_PAD_LEFT);
                                    $att = fetchOne("SELECT status FROM attendance WHERE student_id = ? AND date = ?", [$student['id'], $date]);
                                    $status = $att ? $att['status'] : '';
                                    ?>
                                    <td>
                                        <select name="attendance[<?php echo $student['id']; ?>][<?php echo str_pad($d,2,'0',STR_PAD_LEFT); ?>]" class="form-select form-select-sm">
                                            <option value="">-</option>
                                            <option value="P" <?php echo $status == 'P' ? 'selected' : ''; ?>>Present</option>
                                            <option value="A" <?php echo $status == 'A' ? 'selected' : ''; ?>>Absent</option>
                                            <option value="L" <?php echo $status == 'L' ? 'selected' : ''; ?>>Leave</option>
                                        </select>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Save Attendance</button>
            <button type="button" class="btn btn-secondary" onclick="window.print()">Print / Export PDF</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>