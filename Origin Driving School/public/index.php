<?php
// /public/index.php
session_start();
$title = "Origin Driving School — Learn with confidence";
$loggedIn = !empty($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <!-- Đường link theo Cách 1 -->
  <link rel="stylesheet" href="/public/assets/app.css">
  <link rel="icon" href="/public/assets/favicon.ico">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">
      <img src="/public/assets/logo.png" alt="Logo" style="height:28px">
      Origin Driving <span class="badge">AU</span>
    </a>
    <nav id="mainNav" class="nav" aria-label="Primary">
      <?php if(!$loggedIn): ?>
        <a href="/public/index.php" aria-current="page">Home</a>
        <a href="/public/courses.php">Courses</a>
        <a href="/public/login.php">Login</a>
        <a class="btn wattle" href="/public/register.php">Register</a>
      <?php else: ?>
        <?php if($role==='student'): ?>
          <a href="/public/student/dashboard.php">Dashboard</a>
          <a href="/public/student/book.php">Book Lesson</a>
          <a href="/public/student/invoices.php">Invoices</a>
        <?php elseif($role==='instructor'): ?>
          <a href="/public/instructor/dashboard.php">Today</a>
          <a href="/public/instructor/schedule.php">My Schedule</a>
        <?php else: ?>
          <a href="/public/admin/dashboard.php">Admin</a>
          <a href="/public/admin/scheduling-board.php">Scheduling</a>
          <a href="/public/admin/invoices-list.php">Invoices</a>
        <?php endif; ?>
        <a class="btn ghost" href="/public/logout.php">Logout</a>
      <?php endif; ?>
    </nav>
  </div>

  <div class="container hero">
    <h1>Learn to drive with confidence on Victoria’s roads</h1>
    <p class="lede">
      Professional instructors, city & bayside coverage, and lesson plans aligned with VicRoads drive test criteria.
    </p>
    <div class="cta">
      <a class="btn primary" href="/public/register.php">Start learning</a>
      <a class="btn" href="/public/courses.php">View courses</a>
    </div>
    <div class="wave"></div>
  </div>
</header>

<main class="container">
  <section class="cards" aria-label="Highlights">
    <article class="card">
      <h3>Qualified Instructors</h3>
      <p class="help">Certificate IV accreditation, WWC cards, and ADTAV membership.</p>
    </article>
    <article class="card">
      <h3>Smart Scheduling</h3>
      <p class="help">Mobile-first booking with double-booking protection.</p>
    </article>
    <article class="card">
      <h3>Progress & Attachments</h3>
      <p class="help">Upload record sheets & practice tests; track progress easily.</p>
    </article>
  </section>
</main>

<footer class="footer">
  <div class="container">
    <p class="ack">
      We acknowledge the Traditional Owners of the land and pay respect to Elders past and present.
    </p>
  </div>
</footer>


<script src="/public/assets/app.js" defer></script>
</body>
</html>
