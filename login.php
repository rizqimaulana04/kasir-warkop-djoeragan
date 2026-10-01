<?php require_once __DIR__ . '/config/auth.php';
if (user())
    redirect(dashboard_path());
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    verify_csrf();
    $stmt = db()->prepare('SELECT id,name,username,password,role,branch_id,is_active FROM users WHERE username=? LIMIT 1');
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $u = $stmt->fetch();
    if ($u && $u['is_active'] && password_verify($_POST['password'] ?? '', $u['password']))
    {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        redirect(dashboard_path($u['role']));
    }
    $error = 'Username atau kata sandi tidak sesuai.';
} ?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#704326">
    <title>Masuk · <?= APP_NAME ?></title>
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/icons/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/branding.css">
</head>

<body class="login-body">
    <main class="login-card">
        <div class="login-art"><img class="login-logo" src="<?= BASE_URL ?>/assets/images/logo.png"
                alt="Logo Warkop Djoeragan">
            <h1>Selamat datang!</h1>
            <p>Kelola pesanan Warkop Djoeragan dengan cepat dan mudah.</p>
        </div>
        <form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div><?php endif; ?><label>Username<div class="input-icon"><i
                        class="bi bi-person"></i><input name="username" autocomplete="username" required
                        placeholder="Masukkan username"></div></label><label>Kata sandi<div class="input-icon"><i
                        class="bi bi-lock"></i><input id="password" type="password" name="password"
                        autocomplete="current-password" required placeholder="Masukkan kata sandi"><button type="button"
                        class="toggle-password"><i class="bi bi-eye"></i></button></div></label><button
                class="btn primary wide" type="submit">Masuk</button>
        </form>
    </main>
    <script>window.APP_BASE = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>

</html>
