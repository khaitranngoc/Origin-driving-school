<?php
// /public/courses.php
session_start();
$title = "Courses — Origin Driving School";
$loggedIn = !empty($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
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
    <nav class="nav">
      <a href="/public/index.php">Home</a>
      <a href="/public/courses.php" aria-current="page">Courses</a>
      <?php if(!$loggedIn): ?>
        <a href="/public/login.php">Login</a>
        <a class="btn wattle" href="/public/register.php">Register</a>
      <?php else: ?>
        <a href="/public/logout.php">Logout</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Our Driving Courses</h1>
  <p class="help">Choose a package that suits your needs. All courses are delivered by certified instructors.</p>

  <section class="cards">
    <article class="card">
      <h3>Starter Pack</h3>
      <p>3 lessons · Perfect for beginners just starting out.</p>
      <p class="price">$180</p>
      <div class="actions"><a class="btn wattle" href="/public/register.php">Book now</a></div>
    </article>

    <article class="card">
      <h3>Test Ready</h3>
      <p>5 lessons + mock test · Get ready for your VicRoads drive test.</p>
      <p class="price">$280</p>
      <div class="actions"><a class="btn wattle" href="/public/register.php">Book now</a></div>
    </article>

    <article class="card">
      <h3>Refresher</h3>
      <p>1–2 lessons · For returning drivers needing confidence boost.</p>
      <p class="price">$70 / lesson</p>
      <div class="actions"><a class="btn wattle" href="/public/register.php">Book now</a></div>
    </article>

    <article class="card">
      <h3>Overseas Licence Conversion</h3>
      <p>Custom lessons to prepare overseas licence holders for Victorian tests.</p>
      <p class="price">$90 / lesson</p>
      <div class="actions"><a class="btn wattle" href="/public/register.php">Book now</a></div>
    </article>
  </section>
</main>

<footer class="footer">
  <div class="container">
    <p class="ack">We acknowledge the Traditional Owners of the land and pay respect to Elders past and present.</p>
  </div>
</footer>
<script src="/public/assets/app.js" defer></script>
</body>
</html>
