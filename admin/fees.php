<?php
require_once '../config.php';
require_once '../includes/auth.php';
checkLogin();
checkRole(['admin']);

$year = $_GET['year'] ?? date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payments = $_POST['payments'] ?? [];
    $db = getDB();
    $db->beginTransaction();
    try {
        foreach ($payments as $studentId => $months) {
            foreach ($months as $month => $status) {
                $stmt = $db->prepare("INSERT OR REPLACE INTO fee_payments (student_id, year, month, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$studentId, $year, $month, $status]);
            }
        }
        $db->commit();
        $success = "Fee records updated.";
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get all students with their yearly fee
$students = fetchAll("SELECT u.id, u.name, s.grade, s.yearly_fee FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student' ORDER BY u.name");

$pageTitle = 'Fee Collection';
include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5>Fee Collection - <?php echo $year; ?></h5>
        <form method="get" class="row g-3 mt-2">
            <div class="col-auto">
                <label>Year</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y')-2; $y <= date('Y')+2; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
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
                            <th>Yearly Fee</th>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <th><?php echo date('M', mktime(0,0,0,$m,1)); ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']) . ' ' . $student['grade']; ?></td>
                                <td>Rs.<?php echo number_format($student['yearly_fee']); ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <?php
                                    $fee = fetchOne("SELECT status FROM fee_payments WHERE student_id = ? AND year = ? AND month = ?", [$student['id'], $year, $m]);
                                    $status = $fee ? $fee['status'] : 'unpaid';
                                    ?>
                                    <td>
                                        <select name="payments[<?php echo $student['id']; ?>][<?php echo $m; ?>]" class="form-select form-select-sm">
                                            <option value="unpaid" <?php echo $status == 'unpaid' ? 'selected' : ''; ?>>Not Paid</option>
                                            <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        </select>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>