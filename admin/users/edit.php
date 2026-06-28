<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'User tidak ditemukan!'];
    redirect('index.php');
}

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$error = '';
$success = '';

$stmt = $pdo->query("SELECT id, nik, nama FROM penduduk ORDER BY nama");
$penduduk = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_baru = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $penduduk_id = $_POST['penduduk_id'] ?: null;

    if (!$username_baru) {
        $error = 'Username wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username_baru, $id]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, role=?, penduduk_id=? WHERE id=?");
                $stmt->execute([$username_baru, $hash, $role, $penduduk_id, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username=?, role=?, penduduk_id=? WHERE id=?");
                $stmt->execute([$username_baru, $role, $penduduk_id, $id]);
            }
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'User berhasil diupdate!'];
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../layout/topbar.php'; ?>
        <div class="content-wrapper">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>Edit User</h4>
                    <p>Ubah data pengguna</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password (biarkan kosong jika tidak diubah)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select">
                                <option value="penduduk" <?= $user['role']=='penduduk'?'selected':'' ?>>Penduduk</option>
                                <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Relasi Penduduk</label>
                            <select name="penduduk_id" class="form-select select2">
                                <option value="">-- Tidak ada --</option>
                                <?php foreach ($penduduk as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $user['penduduk_id']==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nik']) ?> - <?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                            <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>$(document).ready(function() { $('.select2').select2({ theme: 'classic', width: '100%' }); });</script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
