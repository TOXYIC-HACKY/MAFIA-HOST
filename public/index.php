<?php
// public/index.php - login page
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errors[] = "Provide username and password.";
        } else {
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username']
                ];
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "Invalid credentials.";
            }
        }
    }
}

$csrf = generate_csrf_token();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container auth">
    <h1>Login</h1>
    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e): ?><div><?=htmlspecialchars($e)?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="index.php" autocomplete="off">
      <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">
      <label>Username
        <input type="text" name="username" required>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
  </div>
</body>
</html>
