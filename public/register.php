<?php
// public/register.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $errors[] = "All fields are required.";
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
            $errors[] = "Username must be 3–50 chars and may contain letters, numbers, underscores.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        } elseif ($password !== $password2) {
            $errors[] = "Passwords do not match.";
        } else {
            // Check username uniqueness
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username is already taken.";
            }

            // Check email uniqueness
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email is already registered.";
            }

            if (empty($errors)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
                $ins->execute([$username, $email, $hash]);
                $success = "Registration successful! You can <a href='index.php'>login now</a>.";
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
  <title>Register</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container auth">
    <h1>Register</h1>
    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach ($errors as $e): ?><div><?=htmlspecialchars($e)?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= $success ?></div>
    <?php else: ?>
      <form method="post" action="register.php" autocomplete="off" id="registerForm">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">
        <label>Username
          <input type="text" name="username" id="username" required>
          <small id="username-status" aria-live="polite"></small>
        </label>
        <label>Email
          <input type="email" name="email" required>
        </label>
        <label>Password
          <input type="password" name="password" required>
        </label>
        <label>Confirm Password
          <input type="password" name="password2" required>
        </label>
        <button type="submit">Register</button>
      </form>
    <?php endif; ?>

    <p>Already have an account? <a href="index.php">Login</a></p>
  </div>

  <script>
  // Live username availability check with debounce
  document.addEventListener('DOMContentLoaded', () => {
    const usernameInput = document.getElementById('username');
    const statusElem = document.getElementById('username-status');
    let timeout = null;

    if (!usernameInput) return;

    usernameInput.addEventListener('input', () => {
      clearTimeout(timeout);
      statusElem.textContent = '';
      statusElem.style.color = '';

      const username = usernameInput.value.trim();
      if (username.length === 0) return;

      if (username.length < 3) {
        statusElem.textContent = 'Username must be at least 3 characters';
        statusElem.style.color = 'red';
        return;
      }

      // delay the request to reduce load
      timeout = setTimeout(() => {
        fetch(`check_username.php?username=${encodeURIComponent(username)}`, {cache: 'no-store'})
          .then(res => res.json())
          .then(data => {
            statusElem.textContent = data.message || '';
            statusElem.style.color = (data.status === 'available') ? 'green' : 'red';
          })
          .catch(() => {
            statusElem.textContent = 'Error checking username';
            statusElem.style.color = 'red';
          });
      }, 300);
    });

    // Optional: prevent submit if username is known taken (extra client-side)
    const form = document.getElementById('registerForm');
    form && form.addEventListener('submit', (e) => {
      if (statusElem && statusElem.textContent.toLowerCase().includes('taken')) {
        e.preventDefault();
        alert('Please choose another username — this one is taken.');
      }
    });
  });
  </script>
</body>
</html>
