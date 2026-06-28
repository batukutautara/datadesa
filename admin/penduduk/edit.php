<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM penduduk WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Data tidak ditemukan!'];
    redirect('index.php');
}

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = trim($_POST['nik']);
    $nama = trim($_POST['nama']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'] ?: null;
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $rt = trim($_POST['rt']);
    $rw = trim($_POST['rw']);
    $dusun = trim($_POST['dusun']);
    $agama = trim($_POST['agama']);
    $status_perkawinan = trim($_POST['status_perkawinan']);
    $pekerjaan = trim($_POST['pekerjaan']);
    $kewarganegaraan = trim($_POST['kewarganegaraan']);

    if (!$nik || !$nama) {
        $error = 'NIK dan Nama wajib diisi!';
    } elseif (cekNikDuplikat($nik, $id)) {
        $error = 'NIK ' . $nik . ' sudah terdaftar!';
    } else {
        $stmt = $pdo->prepare("UPDATE penduduk SET nik=?, nama=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, rt=?, rw=?, dusun=?, agama=?, status_perkawinan=?, pekerjaan=?, kewarganegaraan=? WHERE id=?");
        $stmt->execute([$nik, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $rt, $rw, $dusun, $agama, $status_perkawinan, $pekerjaan, $kewarganegaraan, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data penduduk berhasil diupdate!'];
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penduduk - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Edit Penduduk</h4>
                    <p>Ubah data penduduk</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nik" id="nik" class="form-control" maxlength="16" value="<?= htmlspecialchars($p['nik']) ?>" required>
                            <input type="hidden" id="penduduk_id" value="<?= $p['id'] ?>">
                            <div id="nik-duplicate-msg" class="nik-duplicate"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($p['nama']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($p['tempat_lahir']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="<?= $p['tanggal_lahir'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L" <?= $p['jenis_kelamin']=='L'?'selected':'' ?>>Laki-laki</option>
                                <option value="P" <?= $p['jenis_kelamin']=='P'?'selected':'' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($p['alamat']) ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dusun</label>
                            <input type="text" name="dusun" class="form-control" value="<?= htmlspecialchars($p['dusun']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RT</label>
                            <input type="text" name="rt" class="form-control" maxlength="3" value="<?= htmlspecialchars($p['rt']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RW</label>
                            <input type="text" name="rw" class="form-control" maxlength="3" value="<?= htmlspecialchars($p['rw']) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Agama</label>
                            <select name="agama" class="form-select">
                                <option value="">Pilih</option>
                                <option <?= $p['agama']=='Islam'?'selected':'' ?>>Islam</option>
                                <option <?= $p['agama']=='Kristen'?'selected':'' ?>>Kristen</option>
                                <option <?= $p['agama']=='Katolik'?'selected':'' ?>>Katolik</option>
                                <option <?= $p['agama']=='Hindu'?'selected':'' ?>>Hindu</option>
                                <option <?= $p['agama']=='Buddha'?'selected':'' ?>>Buddha</option>
                                <option <?= $p['agama']=='Konghucu'?'selected':'' ?>>Konghucu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Perkawinan</label>
                            <select name="status_perkawinan" class="form-select">
                                <option value="">Pilih</option>
                                <option <?= $p['status_perkawinan']=='Belum Kawin'?'selected':'' ?>>Belum Kawin</option>
                                <option <?= $p['status_perkawinan']=='Kawin'?'selected':'' ?>>Kawin</option>
                                <option <?= $p['status_perkawinan']=='Cerai Hidup'?'selected':'' ?>>Cerai Hidup</option>
                                <option <?= $p['status_perkawinan']=='Cerai Mati'?'selected':'' ?>>Cerai Mati</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control" value="<?= htmlspecialchars($p['pekerjaan']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kewarganegaraan</label>
                            <input type="text" name="kewarganegaraan" class="form-control" value="<?= htmlspecialchars($p['kewarganegaraan']) ?>">
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
