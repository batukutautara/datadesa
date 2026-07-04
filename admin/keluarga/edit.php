<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM keluarga WHERE id = ?");
$stmt->execute([$id]);
$k = $stmt->fetch();

if (!$k) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Data tidak ditemukan!'];
    redirect('index.php');
}

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_kk = trim($_POST['no_kk']);
    $alamat = trim($_POST['alamat']);
    $rt = trim($_POST['rt']);
    $rw = trim($_POST['rw']);
    $dusun = trim($_POST['dusun']);
    $desa_kel = trim($_POST['desa_kel']);
    $kecamatan = trim($_POST['kecamatan']);
    $kabupaten = trim($_POST['kabupaten']);
    $provinsi = trim($_POST['provinsi']);
    $password = trim($_POST['password']);

    if (!$no_kk) {
        $error = 'Nomor KK wajib diisi!';
    } elseif (cekKKDuplikat($no_kk, $id)) {
        $error = 'Nomor KK ' . $no_kk . ' sudah terdaftar!';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE keluarga SET no_kk=?, alamat=?, rt=?, rw=?, dusun=?, desa_kel=?, kecamatan=?, kabupaten=?, provinsi=? WHERE id=?");
            $stmt->execute([$no_kk, $alamat, $rt, $rw, $dusun, $desa_kel, $kecamatan, $kabupaten, $provinsi, $id]);

            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE no_kk = ? AND role = 'penduduk'");
                $stmt->execute([$hash, $k['no_kk']]);
            }

            if ($no_kk != $k['no_kk']) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, no_kk = ? WHERE no_kk = ? AND role = 'penduduk'");
                $stmt->execute([$no_kk, $no_kk, $k['no_kk']]);
                $stmt = $pdo->prepare("UPDATE penduduk SET no_kk = ? WHERE no_kk = ?");
                $stmt->execute([$no_kk, $k['no_kk']]);
            }

            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data KK berhasil diupdate!'];
            redirect('index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit KK - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../layout/topbar.php'; ?>
        <div class="content-wrapper">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>Edit Kartu Keluarga</h4>
                    <p>Ubah data KK</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">No. Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                            <input type="text" name="no_kk" class="form-control" maxlength="16" value="<?= htmlspecialchars($k['no_kk']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Login (kosongkan jika tidak diubah)</label>
                            <input type="text" name="password" class="form-control">
                            <small class="text-muted">Kosongkan jika tidak diubah. Isi <code>kk<?= htmlspecialchars($k['no_kk']) ?></code> untuk reset ke default.</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($k['alamat']) ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dusun</label>
                            <input type="text" name="dusun" class="form-control" value="<?= htmlspecialchars($k['dusun']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RT</label>
                            <input type="text" name="rt" class="form-control" maxlength="3" value="<?= htmlspecialchars($k['rt']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RW</label>
                            <input type="text" name="rw" class="form-control" maxlength="3" value="<?= htmlspecialchars($k['rw']) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Desa/Kelurahan</label>
                            <input type="text" name="desa_kel" class="form-control" value="<?= htmlspecialchars($k['desa_kel']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" value="<?= htmlspecialchars($k['kecamatan']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kabupaten</label>
                            <input type="text" name="kabupaten" class="form-control" value="<?= htmlspecialchars($k['kabupaten']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($k['provinsi']) ?>">
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
    <script src="../../assets/js/script.js"></script>
</body>
</html>
