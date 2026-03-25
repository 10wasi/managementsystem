<?php
$role = $_SESSION['user']['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <?php
            $logo = getSetting('sidebar_logo');
            if ($logo) {
                echo '<img src="' . BASE_URL . 'assets/uploads/' . htmlspecialchars($logo) . '" alt="Logo" class="img-fluid" style="max-height: 80px;">';
            } else {
                echo '<h4 class="text-white">Islamic Life</h4>';
            }
            ?>
        </div>
        <ul class="nav flex-column">
            <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'students.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'teachers.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/teachers.php">
                        <i class="fas fa-chalkboard-user"></i> Teachers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'attendance.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/attendance.php">
                        <i class="fas fa-calendar-check"></i> Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'fees.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/fees.php">
                        <i class="fas fa-dollar-sign"></i> Fee Collection
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'salary.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/salary.php">
                        <i class="fas fa-money-bill-wave"></i> Salary
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'exam_marks.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/exam_marks.php">
                        <i class="fas fa-pen"></i> Exam Marks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'notifications.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'messages.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/messages.php">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            <?php elseif ($role === 'teacher'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>teacher/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'attendence.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>teacher/attendence.php">
                        <i class="fas fa-calendar-check"></i> Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'salary.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>teacher/salary.php">
                        <i class="fas fa-money-bill-wave"></i> My Salary
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'notification.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>teacher/notification.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'message.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>teacher/message.php">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                </li>
            <?php elseif ($role === 'student'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>student/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> My Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'attendance.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>student/attendance.php">
                        <i class="fas fa-calendar-check"></i> My Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'marks.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>student/marks.php">
                        <i class="fas fa-pen"></i> My Marks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'notification.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>student/notification.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'message.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>student/message.php">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="mt-auto p-3">
            <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-danger w-100">Logout</a>
        </div>
    </div>
</div>