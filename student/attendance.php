<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['student']);

$student_id = $_SESSION['user']['id'];
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

$daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', (int) $year, (int) $month)));
$attendance = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $date = "$year-$month-" . str_pad($d,2,'0',STR_PAD_LEFT);
    $rec = fetchOne("SELECT status FROM attendance WHERE student_id = ? AND date = ?", [$student_id, $date]);
    $attendance[$d] = $rec ? $rec['status'] : '';
}

$present = count(array_filter($attendance, fn($s) => $s == 'P'));
$absent = count(array_filter($attendance, fn($s) => $s == 'A'));
$leave = count(array_filter($attendance, fn($s) => $s == 'L'));

$pageTitle = 'My Attendance';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5><?php echo date('F Y', strtotime("$year-$month-01")); ?> — Attendance Record</h5>
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
        <div class="row mb-3">
            <div class="col text-center">
                <h5>Present: <?php echo $present; ?></h5>
            </div>
            <div class="col text-center">
                <h5>Absent: <?php echo $absent; ?></h5>
            </div>
            <div class="col text-center">
                <h5>Leave: <?php echo $leave; ?></h5>
            </div>
            <div class="col text-center">
                <h5>Total Days: <?php echo $daysInMonth; ?></h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                            <th><?php echo $d; ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                            <td class="text-center">
                                <?php
                                $status = $attendance[$d];
                                if ($status == 'P') echo '<span class="text-success">✓</span>';
                                elseif ($status == 'A') echo '<span class="text-danger">✗</span>';
                                elseif ($status == 'L') echo '<span class="text-warning">L</span>';
                                else echo '-';
                                ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>