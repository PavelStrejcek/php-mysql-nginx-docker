<?php
// Simple index to verify PHP-FPM, Nginx, and MySQL setup

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$phpVersion = PHP_VERSION;
$dbInfo = [
    'host' => 'mysql', // service name in docker-compose
    'port' => getenv('MYSQL_PORT') ?: '3306',
    'db'   => getenv('MYSQL_DATABASE') ?: 'app',
    'user' => getenv('MYSQL_USER') ?: 'app',
    'pass' => getenv('MYSQL_PASSWORD') ?: 'app',
];

$pdoStatus = 'not attempted';
$mysqlVersion = null;
$error = null;

try {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbInfo['host'], $dbInfo['port'], $dbInfo['db']);
    $pdo = new PDO($dsn, $dbInfo['user'], $dbInfo['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2,
    ]);
    $pdoStatus = 'connected';
    $mysqlVersion = $pdo->query('SELECT VERSION() AS v')->fetch()['v'] ?? null;
} catch (Throwable $e) {
    $pdoStatus = 'failed';
    $error = $e->getMessage();
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PHP 8.4 FPM + Nginx + MySQL 8.4</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;max-width:900px;margin:2rem auto;padding:0 1rem;line-height:1.5}
    code{background:#f5f5f5;padding:.1rem .3rem;border-radius:4px}
    pre{background:#f5f5f5;padding:1rem;border-radius:6px;overflow:auto}
    .ok{color:#0a7a0a}
    .bad{color:#b00020}
  </style>
</head>
<body>
<h1>It works!</h1>
<p>You are running <strong>PHP <?= htmlspecialchars($phpVersion, ENT_QUOTES) ?></strong> behind Nginx (FPM).</p>

<h2>Database connectivity</h2>
<ul>
  <li>Host: <code><?= htmlspecialchars($dbInfo['host']) ?></code></li>
  <li>Port: <code><?= htmlspecialchars($dbInfo['port']) ?></code></li>
  <li>DB: <code><?= htmlspecialchars($dbInfo['db']) ?></code></li>
  <li>User: <code><?= htmlspecialchars($dbInfo['user']) ?></code></li>
</ul>
<p>Status: <strong class="<?= $pdoStatus === 'connected' ? 'ok' : 'bad' ?>"><?= htmlspecialchars($pdoStatus) ?></strong></p>
<?php if ($mysqlVersion): ?>
  <p>MySQL version: <code><?= htmlspecialchars($mysqlVersion) ?></code></p>
<?php endif; ?>
<?php if ($error): ?>
  <details open>
    <summary>Error details</summary>
    <pre><?= htmlspecialchars($error) ?></pre>
  </details>
<?php endif; ?>

<p>Edit this file at <code>src/public/index.php</code> to start building your app. Point your framework's document root to <code>/var/www/html/public</code>.</p>
</body>
</html>
