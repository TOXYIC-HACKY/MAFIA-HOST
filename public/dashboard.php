<?php
// public/dashboard.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_login();

$user = $_SESSION['user'];

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - <?=htmlspecialchars($user['username'])?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">MyApp</div>
    <div class="right">
      <span>Welcome, <?=htmlspecialchars($user['username'])?></span>
      <a class="btn-ghost" href="logout.php">Logout</a>
    </div>
  </header>

  <main class="container">
    <aside class="sidebar">
      <button class="tab-btn active" data-target="tab-home">Home</button>
      <button class="tab-btn" data-target="tab-profile">Profile</button>
      <button class="tab-btn" data-target="tab-settings">Settings</button>
      <!-- add more buttons for your features -->
    </aside>

    <section class="content">
      <div id="tab-home" class="tab-panel active">
        <h2>Home</h2>
        <p>Welcome to your dashboard. Click tabs to switch sections.</p>
      </div>

      <div id="tab-profile" class="tab-panel">
        <h2>Profile</h2>
        <p>Username: <strong><?=htmlspecialchars($user['username'])?></strong></p>
        <?php
        $stmt = $pdo->prepare('SELECT created_at, email FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        if ($row) {
            echo '<p>Member since: ' . htmlspecialchars($row['created_at']) . '</p>';
            echo '<p>Email: ' . htmlspecialchars($row['email']) . '</p>';
        }
        ?>
      </div>

      <div id="tab-settings" class="tab-panel">
        <h2>Settings</h2>
        <p>Place settings or feature toggles here.</p>
      </div>

      <!-- Create more tab panels as needed -->
    </section>
  </main>

  <script src="assets/app.js"></script>
</body>
</html>
