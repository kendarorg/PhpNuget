<?php
// Standalone setup wizard — no external dependencies

if (file_exists(__DIR__ . '/../config/config.php')) {
    header('Location: ../index.php');
    exit;
}

$log    = [];
$errors = [];
$success = false;
$posted = ($_SERVER['REQUEST_METHOD'] === 'POST');

// ── helpers ──────────────────────────────────────────────────────────────────

function setupLog(&$log, $msg) {
    $log[] = $msg;
}

function writeFile(&$writtenFiles, &$log, &$errors, $path, $content) {
    $backup = file_exists($path) ? file_get_contents($path) : null;
    if (file_put_contents($path, $content) !== false) {
        $writtenFiles[] = ['path' => $path, 'backup' => $backup];
        setupLog($log, 'OK  written ' . basename($path));
        return true;
    }
    $errors[] = 'Cannot write ' . basename($path);
    return false;
}

function rollback($pdo, $createdTables, $writtenFiles, &$log) {
    if ($pdo && !empty($createdTables)) {
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            foreach (array_reverse($createdTables) as $t) {
                $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
                setupLog($log, 'RB  dropped table `' . $t . '`');
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        } catch (Exception $e) {
            setupLog($log, 'RB  table drop error: ' . $e->getMessage());
        }
    }
    foreach (array_reverse($writtenFiles) as $f) {
        if ($f['backup'] !== null) {
            file_put_contents($f['path'], $f['backup']);
            setupLog($log, 'RB  restored ' . basename($f['path']));
        } elseif (file_exists($f['path'])) {
            unlink($f['path']);
            setupLog($log, 'RB  deleted ' . basename($f['path']));
        }
    }
}

// ── process POST ─────────────────────────────────────────────────────────────

if ($posted) {
    $dbHost  = trim($_POST['db_host'] ?? 'localhost');
    $dbPort  = trim($_POST['db_port'] ?? '3306');
    $dbName  = trim($_POST['db_name'] ?? '');
    $dbUser  = trim($_POST['db_user'] ?? '');
    $dbPass  = $_POST['db_pass'] ?? '';
    $hostUrl = rtrim(trim($_POST['host_url'] ?? ''), '/');
    $coName  = trim($_POST['company_name'] ?? '');
    $coVat   = trim($_POST['company_vat'] ?? '');
    $saUser  = trim($_POST['sa_username'] ?? '');
    $saEmail = trim($_POST['sa_email'] ?? '');
    $saPw    = $_POST['sa_password'] ?? '';
    $saCf    = $_POST['sa_confirm'] ?? '';

    // validate
    if (!$dbHost)  $errors[] = 'DB host required';
    if (!$dbName)  $errors[] = 'DB name required';
    if (!$dbUser)  $errors[] = 'DB user required';
    if (!$hostUrl) $errors[] = 'Host URL required';
    if (!$coName)  $errors[] = 'Company name required';
    if (!$saUser)  $errors[] = 'Admin username required';
    if (!filter_var($saEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email required';
    if (strlen($saPw) < 8) $errors[] = 'Password min 8 chars';
    if ($saPw !== $saCf)   $errors[] = 'Passwords do not match';

    $pdo           = null;
    $createdTables = [];
    $writtenFiles  = [];

    // ── step 1: connect ───────────────────────────────────────────────────────
    if (empty($errors)) {
        try {
            $hostPort = ($dbPort !== '') ? "{$dbHost}:{$dbPort}" : $dbHost;
            $dsn = "mysql:host={$hostPort};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            setupLog($log, "OK  connected to '{$dbName}'");
        } catch (PDOException $e) {
            $errors[] = 'DB connection failed: ' . $e->getMessage();
        }
    }

    // ── step 2: execute setup.sql ─────────────────────────────────────────────
    if (empty($errors)) {
        $sqlPath = __DIR__ . '/setup.sql';
        if (!file_exists($sqlPath)) {
            $errors[] = 'setup.sql not found';
        } else {
            $raw   = file_get_contents($sqlPath);
            $stmts = array_values(array_filter(array_map('trim', explode(';', $raw))));

            $creates = [];
            $inserts = [];
            foreach ($stmts as $s) {
                if (preg_match('/^CREATE\s+TABLE/i', $s))  $creates[] = $s;
                elseif (preg_match('/^INSERT/i', $s))      $inserts[] = $s;
            }

            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

                foreach ($creates as $s) {
                    $pdo->exec($s);
                    if (preg_match('/CREATE\s+TABLE\s+`?(\w+)`?/i', $s, $m)) {
                        $createdTables[] = $m[1];
                        setupLog($log, 'OK  created table `' . $m[1] . '`');
                    }
                }

                foreach ($inserts as $s) {
                    $pdo->exec($s);
                    if (preg_match('/INSERT\s+INTO\s+`?(\w+)`?/i', $s, $m)) {
                        setupLog($log, 'OK  seeded `' . $m[1] . '`');
                    }
                }

                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

                $pdo->exec("INSERT INTO counters (table_name, counter) VALUES ('users', 1)");
                setupLog($log, 'OK  initialized counters');

                $hash   = password_hash($saPw, PASSWORD_DEFAULT);
                $apiKey = strtoupper(bin2hex(random_bytes(16)));
                $st   = $pdo->prepare(
                    "INSERT INTO users
                       (id, username, password, email, apiKey, relationKind, livello, role, permissions, locked)
                     VALUES
                       (1, ?, ?, ?, ?, 'Altro', 'altro', 'SuperAdmin', '[]', 0)"
                );
                $st->execute([$saUser, $hash, $saEmail, $apiKey]);
                setupLog($log, "OK  created SuperAdmin '{$saUser}' (upload API key: {$apiKey})");

            } catch (PDOException $e) {
                $errors[] = 'SQL error: ' . $e->getMessage();
            }
        }
    }

    // ── step 3: write config/config.php ──────────────────────────────────────
    if (empty($errors)) {
        $hostPort  = ($dbPort !== '') ? "{$dbHost}:{$dbPort}" : $dbHost;
        $cfgPath   = realpath(__DIR__ . '/../config') . '/config.php';
        $cfgContent = "<?php\n\n"
            . "GlobalRegistry::registerValue('DB_HOST', "    . var_export($hostPort, true) . ");\n"
            . "GlobalRegistry::registerValue('DB_NAME', "    . var_export($dbName,   true) . ");\n"
            . "GlobalRegistry::registerValue('DB_USER', "    . var_export($dbUser,   true) . ");\n"
            . "GlobalRegistry::registerValue('DB_PASS', "    . var_export($dbPass,   true) . ");\n"
            . "GlobalRegistry::registerValue('DB_CHARSET', 'utf8mb4');\n"
            . "GlobalRegistry::registerValue('SHOW_EXACT_ERRORS', false);\n";
        writeFile($writtenFiles, $log, $errors, $cfgPath, $cfgContent);
    }

    // ── step 4: write config/company_config.php ───────────────────────────────
    if (empty($errors)) {
        $coCfgPath    = realpath(__DIR__ . '/../config') . '/company_config.php';
        $coCfgContent = "<?php /** @noinspection PhpUndefinedConstantInspection */\n\n"
            . "GlobalRegistry::registerValue('HOST_PATH', "    . var_export($hostUrl, true) . ");\n"
            . "GlobalRegistry::registerValue('COMPANY_LOGO', HOST_PATH . '/public/images/logo2.png');\n"
            . "GlobalRegistry::registerValue('COMPANY_NAME', " . var_export($coName,  true) . ");\n"
            . "GlobalRegistry::registerValue('COMPANY_VAT', "  . var_export($coVat,   true) . ");\n"
            . "GlobalRegistry::registerValue('COMPANY_ID', 1);\n";
        writeFile($writtenFiles, $log, $errors, $coCfgPath, $coCfgContent);
    }

    // ── step 4b: write nuget protocol config (src/conf/properties.json) ───────
    // The nuget OData/upload side (src/api/**, src/upload) reads this file via
    // lib\utils\Properties; point it at the same MySQL database as the UI.
    if (empty($errors)) {
        $confDir = realpath(__DIR__ . '/../../conf');
        if ($confDir === false) {
            $errors[] = 'src/conf directory not found';
        } else {
            $props = [
                'packagesRoot' => '/var/phpNuget/data/packages',
                'databaseRoot' => '/var/phpNuget/data/db',
                'siteRoot'     => $hostUrl . '/',
                'db.name'      => $dbName,
                'db.host'      => $dbHost,
                'db.port'      => ($dbPort !== '') ? (int)$dbPort : 3306,
                'db.user'      => $dbUser,
                'db.password'  => $dbPass,
                'dbtype'       => 'mysql',
            ];
            writeFile($writtenFiles, $log, $errors, $confDir . '/properties.json',
                json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    // ── step 5: lock setup directory ─────────────────────────────────────────
    if (empty($errors)) {
        $htPath = __DIR__ . '/.htaccess';
        if (writeFile($writtenFiles, $log, $errors, $htPath, "Order deny,allow\nDeny from all\n")) {
            $success = true;
            setupLog($log, 'OK  setup complete');
        }
    }

    // ── rollback on any failure ───────────────────────────────────────────────
    if (!empty($errors)) {
        rollback($pdo, $createdTables, $writtenFiles, $log);
    }
}

// ── helpers for HTML output ───────────────────────────────────────────────────

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function val($key, $default = '') { return h($_POST[$key] ?? $default); }

function detectedHostPath() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $uri    = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    $base   = preg_replace('#/setup/setup\.php$#i', '', $uri);
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $base;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Setup</title>
<style>
  *, *::before, *::after { box-sizing: border-box; }
  body {
    font-family: system-ui, sans-serif;
    font-size: 14px;
    color: #222;
    background: #f4f4f4;
    margin: 0;
    padding: 2rem;
  }
  .card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 2rem;
    max-width: 560px;
    margin: 0 auto;
  }
  h1 { font-size: 1.4rem; margin: 0 0 1.5rem; }
  h2 { font-size: 1rem; margin: 1.5rem 0 0.5rem; color: #555; border-bottom: 1px solid #eee; padding-bottom: 4px; }
  table { border-collapse: collapse; width: 100%; }
  td { padding: 5px 6px; vertical-align: middle; }
  td:first-child { width: 140px; color: #444; }
  input[type=text], input[type=password], input[type=email], input:not([type]) {
    width: 100%;
    padding: 5px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 13px;
  }
  input[type=submit] {
    margin-top: 1.5rem;
    padding: 8px 24px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
  }
  input[type=submit]:hover { background: #1d4ed8; }
  pre {
    background: #f8f8f8;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 10px 12px;
    font-size: 12px;
    overflow-x: auto;
    white-space: pre-wrap;
  }
  .errors { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 4px; padding: 10px 14px; margin-top: 1rem; }
  .errors ul { margin: 0; padding-left: 1.2rem; }
  .errors li { color: #b91c1c; margin: 2px 0; }
  .success { background: #f0fdf4; border: 1px solid #86efac; border-radius: 4px; padding: 12px 16px; }
  .success a { color: #15803d; font-weight: 600; }
  small { color: #888; }
</style>
</head>
<body>
<div class="card">
<h1>Application Setup</h1>

<?php if ($posted && !empty($log)): ?>
<h2>Log</h2>
<pre><?php foreach ($log as $l) echo h($l) . "\n"; ?></pre>
<?php endif; ?>

<?php if ($posted && !empty($errors)): ?>
<div class="errors"><ul><?php foreach ($errors as $e) echo '<li>' . h($e) . '</li>'; ?></ul></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="success"><strong>Setup complete.</strong> <a href="../index.php">Open application &rarr;</a></div>
<?php else: ?>
<form method="post">

<h2>Database</h2>
<table>
<tr><td>Host</td><td><input name="db_host" value="<?= val('db_host', 'localhost') ?>"></td></tr>
<tr><td>Port</td><td><input name="db_port" value="<?= val('db_port', '3306') ?>" style="width:80px"></td></tr>
<tr><td>Database name</td><td><input name="db_name" value="<?= val('db_name') ?>" required></td></tr>
<tr><td>User</td><td><input name="db_user" value="<?= val('db_user') ?>" required></td></tr>
<tr><td>Password</td><td><input type="password" name="db_pass"></td></tr>
</table>

<h2>Company</h2>
<table>
<tr><td>Name</td><td><input name="company_name" value="<?= val('company_name') ?>" required></td></tr>
<tr><td>VAT</td><td><input name="company_vat" value="<?= val('company_vat') ?>"></td></tr>
<tr><td>Host URL</td><td><input name="host_url" value="<?= val('host_url', detectedHostPath()) ?>" required></td></tr>
</table>

<h2>Super Admin</h2>
<table>
<tr><td>Username</td><td><input name="sa_username" value="<?= val('sa_username') ?>" required></td></tr>
<tr><td>Email</td><td><input type="email" name="sa_email" value="<?= val('sa_email') ?>" required></td></tr>
<tr><td>Password</td><td><input type="password" name="sa_password" required> <small>min 8 chars</small></td></tr>
<tr><td>Confirm</td><td><input type="password" name="sa_confirm" required></td></tr>
</table>

<input type="submit" value="Run Setup">
</form>
<?php endif; ?>
</div>
</body>
</html>
