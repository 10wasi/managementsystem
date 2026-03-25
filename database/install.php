<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

// Read schema
$schema = file_get_contents(__DIR__ . '/schema.sql');
$db->exec($schema);

// Insert default admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password, role, name, display_name) VALUES (?, ?, 'admin', ?, ?)");
$stmt->execute(['admin', $admin_password, 'Admin', 'Admin']);

// Insert a demo teacher
$teacher_password = password_hash('teacher123', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password, role, name, display_name) VALUES (?, ?, 'teacher', ?, ?)");
$stmt->execute(['ustaz_ahmed', $teacher_password, 'Ustaz Ahmed Ali', 'Ustaz Ahmed Ali']);
$teacher_id = $db->lastInsertId();
if ($teacher_id) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO teachers (user_id, per_student_pay, bonus, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacher_id, 2000, 500, '0300-1234567']);
}

// Insert a demo student
$student_password = password_hash('student123', PASSWORD_DEFAULT);
$stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password, role, name, display_name) VALUES (?, ?, 'student', ?, ?)");
$stmt->execute(['abdullah', $student_password, 'Abdullah Khan', 'Abdullah Khan']);
$student_id = $db->lastInsertId();
if ($student_id) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO students (user_id, grade, class_time, yearly_fee, phone, assigned_teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, 'Grade 3', '08:00 AM to 08:15pm', 24000, '0300-9876543', $teacher_id]);
}

echo "Database installed successfully!";