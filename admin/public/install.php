<?php
/**
 * N05 Tyre & MOT — Easy Installation Wizard
 * Visit /install.php to run. Delete this file after installation.
 */
$base = dirname(__DIR__);
$isInstalled = file_exists($base . '/.installed');
if ($isInstalled && empty($_GET['force'])) {
    header('Location: /');
    exit;
}

$step = (int)($_GET['step'] ?? 1);
$action = $_POST['action'] ?? '';
$errors = [];
$messages = [];
$hasVendor = file_exists($base . '/vendor/autoload.php');
$hasEnv = file_exists($base . '/.env');
$hasVendor = file_exists($base . '/vendor/autoload.php');
$hasEnv = file_exists($base . '/.env');

function runCmd($cmd, $cwd) {
    $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open($cmd, $descriptorSpec, $pipes, $cwd, null, ['bypass_shell' => true]);
    if (!is_resource($proc)) return [false, 'Could not run command'];
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($proc);
    return [$code === 0, trim($out . "\n" . $err)];
}

// Handle actions
if ($action === 'create_env') {
    $envExample = $base . '/.env.example';
    $env = $base . '/.env';
    if (!file_exists($envExample)) {
        $errors[] = '.env.example not found';
    } elseif (file_exists($env)) {
        $messages[] = '.env already exists (skipped)';
    } else {
        if (copy($envExample, $env)) {
            $messages[] = '.env created successfully';
        } else {
            $errors[] = 'Could not create .env (check permissions)';
        }
    }
}

if ($action === 'create_dirs') {
    $dirs = [
        $base . '/storage/logs',
        $base . '/storage/framework/sessions',
        $base . '/storage/framework/views',
        $base . '/storage/framework/cache',
        $base . '/bootstrap/cache',
    ];
    foreach ($dirs as $d) {
        if (!is_dir($d)) {
            if (!mkdir($d, 0755, true)) {
                $errors[] = "Could not create: $d";
            }
        }
    }
    if (empty($errors)) {
        $messages[] = 'Directories created';
    }
}

$hasVendor = file_exists($base . '/vendor/autoload.php');
$hasEnv = file_exists($base . '/.env');

if ($action === 'composer') {
    $vendor = $base . '/vendor/autoload.php';
    if (file_exists($vendor)) {
        $messages[] = 'Composer already installed (vendor exists)';
    } else {
        $php = 'php';
        $cmd = "cd " . escapeshellarg($base) . " && curl -sS https://getcomposer.org/installer | " . $php . " && " . $php . " composer.phar install --no-dev 2>&1";
        list($ok, $out) = runCmd($cmd, $base);
        if ($ok || file_exists($vendor)) {
            $messages[] = 'Composer install completed';
            @unlink($base . '/composer.phar');
        } else {
            $errors[] = 'Composer failed. Run manually in Terminal (see Step 2).';
        }
    }
}

if ($action === 'laravel_setup' && file_exists($base . '/vendor/autoload.php')) {
    $artisan = $base . '/artisan';
    $ok1 = $ok2 = $ok3 = false;
    if (file_exists($artisan)) {
        list($ok1, ) = runCmd('php artisan key:generate --force', $base);
        list($ok2, ) = runCmd('php artisan migrate --force', $base);
        list($ok3, ) = runCmd('php artisan config:cache', $base);
    }
    if ($ok1 || $ok2 || $ok3) {
        $messages[] = 'Laravel setup completed. Visit your site!';
    } else {
        $errors[] = 'Could not run artisan. Run the commands manually in Terminal.';
    }
}

$hasVendor = file_exists($base . '/vendor/autoload.php');
$hasEnv = file_exists($base . '/.env');
$phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
$extensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'fileinfo'];
$missingExt = [];
foreach ($extensions as $e) {
    if (!extension_loaded($e)) $missingExt[] = $e;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N05 Install Wizard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: Inter, sans-serif; margin: 0; background: #1B263B; color: #fff; min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 560px; margin: 0 auto; }
        h1 { font-size: 1.75rem; margin: 0 0 8px; color: #fede00; }
        .sub { color: rgba(255,255,255,.6); font-size: 14px; margin-bottom: 28px; }
        .card { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 12px; padding: 24px; margin-bottom: 16px; }
        .card h2 { font-size: 1rem; margin: 0 0 12px; color: #fede00; display: flex; align-items: center; gap: 8px; }
        .card h2 .badge { background: #22c55e; color: #000; font-size: 10px; padding: 2px 8px; border-radius: 10px; }
        .card h2 .badge.fail { background: #e11d48; }
        .card p { margin: 0 0 12px; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,.85); }
        code { background: rgba(0,0,0,.4); padding: 2px 8px; border-radius: 4px; font-size: 13px; word-break: break-all; }
        pre { background: #0d1117; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 13px; margin: 12px 0; border: 1px solid rgba(255,255,255,.1); }
        .btn { display: inline-block; background: #fede00; color: #1B263B; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; font-size: 14px; font-family: inherit; }
        .btn:hover { background: #facc15; }
        .btn-secondary { background: rgba(255,255,255,.15); color: #fff; }
        .msg { padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
        .msg.ok { background: rgba(34,197,94,.2); border: 1px solid #22c55e; }
        .msg.err { background: rgba(225,29,72,.2); border: 1px solid #e11d48; }
        .step-num { width: 28px; height: 28px; border-radius: 50%; background: rgba(254,222,0,.2); color: #fede00; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
        a { color: #fede00; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 N05 Install Wizard</h1>
    <p class="sub">Easy setup for cPanel and shared hosting</p>

    <?php foreach ($messages as $m): ?>
        <div class="msg ok"><?= htmlspecialchars($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <div class="msg err"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <!-- Step 1: System Check -->
    <div class="card">
        <h2><span class="step-num">1</span> System Check <?= $phpOk && empty($missingExt) ? '<span class="badge">OK</span>' : '<span class="badge fail">Fix</span>' ?></h2>
        <p>PHP <?= PHP_VERSION ?> <?= $phpOk ? '✓' : '— need 8.1+' ?></p>
        <?php if (!empty($missingExt)): ?>
            <p class="msg err">Missing extensions: <?= implode(', ', $missingExt) ?>. Enable them in cPanel → PHP Extensions.</p>
        <?php endif; ?>
    </div>

    <!-- Step 2: Composer -->
    <div class="card">
        <h2><span class="step-num">2</span> Install Dependencies <?= $hasVendor ? '<span class="badge">OK</span>' : '' ?></h2>
        <?php if ($hasVendor): ?>
            <p>Composer packages are installed.</p>
        <?php else: ?>
            <p>Run this in <strong>cPanel → Terminal</strong> (or SSH):</p>
            <pre>cd ~/tyre/admin && curl -sS https://getcomposer.org/installer | php && php composer.phar install --no-dev</pre>
            <p>Or if Composer is installed: <code>cd ~/tyre/admin && composer install --no-dev</code></p>
            <form method="post"><input type="hidden" name="action" value="composer"><button type="submit" class="btn">Try auto-install</button></form>
        <?php endif; ?>
    </div>

    <!-- Step 3: .env -->
    <div class="card">
        <h2><span class="step-num">3</span> Configuration <?= $hasEnv ? '<span class="badge">OK</span>' : '' ?></h2>
        <?php if ($hasEnv): ?>
            <p>.env exists. Edit it in <code>tyre/admin/.env</code> for Stripe, mail, etc.</p>
        <?php else: ?>
            <form method="post"><input type="hidden" name="action" value="create_env"><button type="submit" class="btn">Create .env</button></form>
        <?php endif; ?>
    </div>

    <!-- Step 4: Storage -->
    <div class="card">
        <h2><span class="step-num">4</span> Storage & Cache</h2>
        <form method="post"><input type="hidden" name="action" value="create_dirs"><button type="submit" class="btn">Create directories</button></form>
    </div>

    <!-- Step 5: Laravel Setup (only when vendor exists) -->
    <?php if ($hasVendor): ?>
    <div class="card">
        <h2><span class="step-num">5</span> Laravel Setup</h2>
        <form method="post" style="margin-bottom:12px"><input type="hidden" name="action" value="laravel_setup"><button type="submit" class="btn">Run setup (key, migrate, cache)</button></form>
        <p>Or run in Terminal:</p>
        <pre>cd ~/tyre/admin
php artisan key:generate
php artisan migrate --force
php artisan config:cache</pre>
        <p><a href="/">← Visit site</a></p>
    </div>
    <?php endif; ?>

    <p style="margin-top: 24px; font-size: 12px; color: rgba(255,255,255,.4);">
        After installation, delete <code>install.php</code> for security. Document root: <code>/home/no5tyreandmotco/tyre/admin/public</code>
    </p>
</div>
</body>
</html>
