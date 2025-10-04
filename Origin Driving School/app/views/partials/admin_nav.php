<?php
require_once __DIR__ . '/../../Core/DB.php';
$pdo = DB::pdo();

$counts = [
  'students'    => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
  'instructors' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn(),
  'vehicles'    => (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn(),
  'courses'     => (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
  'branches'    => (int)$pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn(),
  'invoices'    => (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
];

$kpi_overdue_invoices = (int)$pdo->query("
  SELECT COUNT(*) FROM invoices
  WHERE status='Overdue' OR (status='Unpaid' AND due_date IS NOT NULL AND due_date < CURDATE())
")->fetchColumn();

$kpi_open_slots_today = (int)$pdo->query("
  SELECT COUNT(*) FROM schedules
  WHERE status='Open' AND DATE(start_time)=CURDATE()
")->fetchColumn();
?>
<nav class="nav">
   <a href="/public/admin/dashboard.php" aria-current="page">Dashboard</a>
  <a href="/public/admin/student_list.php">Students</a>
  <a href="/public/admin/instructor_list.php">Instructors</a>
  <a href="/public/admin/vehicle_list.php">Vehicles</a>
  <a href="/public/admin/courses_list.php">Courses</a>
  <a href="/public/admin/branch_list.php">Branches</a>
  <a href="/public/admin/invoices_list.php">Invoices</a>
  <a href="/public/admin/schedule_board.php">Scheduling</a>
  <a href="/public/admin/reports.php">Reports</a>
  <a class="btn ghost" href="/public/logout.php">Logout</a>
</nav>
