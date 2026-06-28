<?php
require_once 'config/functions.php';

if (isLogin()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT u.*, p.nama FROM users u LEFT JOIN penduduk p ON u.penduduk_id = p.id WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nama'] = $user['nama'] ?: $user['username'];

            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            redirect('index.php');
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Silakan isi username dan password!';
    }
}

$profil = getProfilDesa();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="logo-login">
            <?php if ($profil['logo'] && file_exists($profil['logo'])): ?>
                <img src="<?= htmlspecialchars($profil['logo']) ?>" alt="Logo Desa">
            <?php else: ?>
                <i class="bi bi-building" style="font-size: 60px; color: var(--navy);"></i>
            <?php endif; ?>
            <h4>Sistem Informasi Penduduk</h4>
            <p><?= htmlspecialchars($profil['nama_desa']) ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">MASUK</button>
        </form>
    </div>
</body>
</html>
