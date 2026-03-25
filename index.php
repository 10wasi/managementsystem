<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$counts = [
    'students' => (int) (fetchOne("SELECT COUNT(*) AS total FROM users WHERE role = 'student'")['total'] ?? 0),
    'teachers' => (int) (fetchOne("SELECT COUNT(*) AS total FROM users WHERE role = 'teacher'")['total'] ?? 0),
    'classes' => (int) (fetchOne("SELECT COUNT(DISTINCT grade) AS total FROM students WHERE grade IS NOT NULL AND grade <> ''")['total'] ?? 0),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="container">
        <h1><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Starter project scaffold with role-based folders and SQLite setup.</p>

        <section class="cards">
            <article class="card">
                <h2>Students</h2>
                <p><?= $counts['students'] ?></p>
            </article>
            <article class="card">
                <h2>Teachers</h2>
                <p><?= $counts['teachers'] ?></p>
            </article>
            <article class="card">
                <h2>Classes</h2>
                <p><?= $counts['classes'] ?></p>
            </article>
        </section>

        <section class="links">
            <a href="admin/dashboard.php">Admin Dashboard</a>
            <a href="teacher/dashboard.php">Teacher Dashboard</a>
            <a href="student/dashboard.php">Student Dashboard</a>
        </section>
    </main>

    <script src="assets/js/app.js"></script>
</body>
</html>
