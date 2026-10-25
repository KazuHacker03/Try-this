<?php
session_start();
require 'db.php';

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm"] ?? "";

    if ($username === "" || $password === "" || $confirm === "") {
        $error = "ALL FIELDS ARE REQUIRED.";
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $error = "USERNAME: 3-50 LETTERS, NUMBERS OR _ ONLY.";
    } elseif (strlen($password) < 6) {
        $error = "PASSWORD MUST BE AT LEAST 6 CHARACTERS.";
    } elseif ($password !== $confirm) {
        $error = "PASSWORDS DO NOT MATCH.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);

        if ($check->fetch()) {
            $error = "USERNAME ALREADY TAKEN.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $success = "IDENTITY CREATED. You can now log in.";
        }
    }
}

$result = $success ? "granted" : ($error ? "denied" : "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Harold - Sign Up</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Scan overlay */
    .scan-overlay {
      position: fixed; inset: 0;
      background: rgba(0, 0, 0, 0.92);
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 100;
      font-family: monospace;
      color: #0f0;
      overflow: hidden;
    }
    .scan-overlay.active { display: flex; }
    .scan-bar {
      position: absolute; left: 0; right: 0; height: 3px;
      background: #0f0;
      box-shadow: 0 0 12px #0f0, 0 0 30px #0f0;
      animation: sweep 1.2s linear infinite;
    }
    @keyframes sweep {
      0%   { top: 0; }
      100% { top: 100%; }
    }
    .scan-log {
      white-space: pre;
      font-size: 14px;
      line-height: 1.6;
      min-height: 6em;
      text-shadow: 0 0 6px #0f0;
    }

    /* Result stamp */
    .stamp {
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%) scale(2);
      font-family: monospace;
      font-size: clamp(26px, 6vw, 60px);
      font-weight: bold;
      letter-spacing: 4px;
      padding: 12px 24px;
      border: 4px solid;
      background: rgba(0, 0, 0, 0.7);
      opacity: 0;
      pointer-events: none;
      z-index: 101;
      white-space: nowrap;
    }
    .stamp.show { animation: stampIn 0.5s ease-out forwards; }
    .stamp.denied  { color: #ff2a2a; border-color: #ff2a2a; text-shadow: 0 0 10px #ff2a2a; }
    .stamp.granted { color: #00ff66; border-color: #00ff66; text-shadow: 0 0 10px #00ff66; }
    @keyframes stampIn {
      0%   { opacity: 0; transform: translate(-50%, -50%) scale(2); }
      60%  { opacity: 1; transform: translate(-50%, -50%) scale(0.95); }
      100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    .stamp.fade { transition: opacity 0.6s; opacity: 0; }

    /* Shake panel on denied */
    .panel.shake { animation: shake 0.4s; }
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-8px); }
      40%, 80% { transform: translateX(8px); }
    }
  </style>
</head>
<body>
  <div class="grid-bg"></div>
  <div class="scanline"></div>

  <div class="scan-overlay" id="scan-overlay">
    <div class="scan-bar"></div>
    <div class="scan-log" id="scan-log"></div>
  </div>

  <main class="wrapper" id="wrapper" data-result="<?= $result ?>">
    <section class="panel" id="panel">
      <h1 class="glitch" data-text="NEW IDENTITY">NEW IDENTITY</h1>
      <p class="sub">// CREATE SECURE ACCOUNT</p>

      <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
        <p class="footer"><a href="login.php">&gt; GO TO LOGIN</a></p>
      <?php else: ?>
        <form method="POST" autocomplete="off" id="signup-form">
          <label>&gt; USERNAME</label>
          <input type="text" name="username" required autofocus>

          <label>&gt; PASSWORD</label>
          <input type="password" name="password" required>

          <label>&gt; RE-ENTER PASSWORD</label>
          <input type="password" name="confirm" required>

          <button type="submit">CREATE ACCOUNT</button>
        </form>

        <p class="footer">ALREADY REGISTERED? <a href="login.php">LOGIN</a></p>
      <?php endif; ?>
    </section>
  </main>

  <script>
    const form    = document.getElementById('signup-form');
    const overlay = document.getElementById('scan-overlay');
    const logEl   = document.getElementById('scan-log');
    const wrapper = document.getElementById('wrapper');
    const panel   = document.getElementById('panel');

    // Scanning animation on submit, then send the real form
    if (form) {
      form.addEventListener('submit', (e) => {
        if (!form.reportValidity()) return;
        e.preventDefault();

        overlay.classList.add('active');
        logEl.textContent = '';

        const lines = [
          '> INITIALIZING UPLINK...',
          '> BYPASSING FIREWALL...',
          '> SCANNING IDENTITY DATABASE...',
          '> VERIFYING CREDENTIALS...'
        ];
        let i = 0;
        const typer = setInterval(() => {
          if (i < lines.length) {
            logEl.textContent += lines[i++] + '\n';
          }
        }, 350);

        setTimeout(() => {
          clearInterval(typer);
          form.submit(); // submit() doesn't re-fire the submit event, so no loop
        }, 1600);
      });
    }

    // Show result stamp after the page reloads
    const result = wrapper.dataset.result;
    if (result) {
      const stamp = document.createElement('div');
      stamp.className = 'stamp ' + result;
      stamp.textContent = result === 'granted' ? 'ACCESS CONFIRMED' : 'ACCESS DENIED';
      document.body.appendChild(stamp);

      if (result === 'denied') {
        panel.classList.add('shake');
      }

      requestAnimationFrame(() => stamp.classList.add('show'));
      setTimeout(() => stamp.classList.add('fade'), 2200);
      setTimeout(() => stamp.remove(), 2900);
    }
  </script>
</body>
</html>