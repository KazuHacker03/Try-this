<?php
session_start();
require 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"]  = $user["id"];
        $_SESSION["username"] = $user["username"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "ACCESS DENIED: Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Harold - Secure Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="grid-bg"></div>
  <div class="scanline"></div>

  <main class="wrapper">

    <div class="robot-area">
      <div class="speech">
        <span class="typed">Welcome.</span>
      </div>

      <div class="robot">
        <div class="antenna"><div class="ball"></div></div>
        <div class="head">
          <div class="visor">
            <span class="eye"></span>
            <span class="eye"></span>
          </div>
          <div class="mouth"></div>
        </div>
        <div class="body">
          <div class="core"></div>
          <div class="arm left"></div>
          <div class="arm right"></div>
        </div>
      </div>
    </div>

    <section class="panel">
      <h1 class="glitch" data-text="SECURE LOGIN">SECURE LOGIN</h1>
      <p class="sub">// AUTHORIZED PERSONNEL ONLY</p>

      <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <label>&gt; USERNAME</label>
        <input type="text" name="username" required autofocus>

        <label>&gt; PASSWORD</label>
        <input type="password" name="password" required>

        <button type="submit">ACCESS SYSTEM</button>
      </form>

      <a href="signup.php" class="signup-btn">CREATE NEW IDENTITY</a>

      <p class="footer">ENCRYPTED CONNECTION // v1.0</p>
    </section>

  </main>
</body>
</html>