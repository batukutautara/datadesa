<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$error = '';
$success = '';

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
    $no_kk = trim($_POST['no_kk']) ?: null;
    $hubungan_keluarga = $_POST['hubungan_keluarga'] ?? 'LAINNYA';

    if (!$nik || !$nama) {
        $error = 'NIK dan Nama wajib diisi!';
    } elseif (cekNikDuplikat($nik)) {
        $error = 'NIK ' . $nik . ' sudah terdaftar!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO penduduk (nik, no_kk, hubungan_keluarga, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, dusun, agama, status_perkawinan, pekerjaan, kewarganegaraan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$nik, $no_kk, $hubungan_keluarga, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $rt, $rw, $dusun, $agama, $status_perkawinan, $pekerjaan, $kewarganegaraan]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data penduduk berhasil ditambahkan!'];
        redirect('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penduduk - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Tambah Penduduk</h4>
                    <p>Input data penduduk baru</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nik" id="nik" class="form-control" maxlength="16" required>
                            <div id="nik-duplicate-msg" class="nik-duplicate"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. Kartu Keluarga (KK)</label>
                            <div class="input-group">
                                <input type="text" name="no_kk" class="form-control" maxlength="16" value="<?= htmlspecialchars($_GET['no_kk'] ?? '') ?>">
                                <a href="../keluarga/tambah.php" class="btn btn-outline-primary" target="_blank" title="Tambah KK Baru"><i class="bi bi-plus"></i></a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hubungan</label>
                            <select name="hubungan_keluarga" class="form-select">
                                <option value="KEPALA KELUARGA">Kepala Keluarga</option>
                                <option value="SUAMI">Suami</option>
                                <option value="ISTRI">Istri</option>
                                <option value="ANAK">Anak</option>
                                <option value="MENANTU">Menantu</option>
                                <option value="CUCU">Cucu</option>
                                <option value="ORANG TUA">Orang Tua</option>
                                <option value="MERTUA">Mertua</option>
                                <option value="FAMILI LAIN">Famili Lain</option>
                                <option value="LAINNYA">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dusun</label>
                            <input type="text" name="dusun" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RT</label>
                            <input type="text" name="rt" class="form-control" maxlength="3">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RW</label>
                            <input type="text" name="rw" class="form-control" maxlength="3">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Agama</label>
                            <select name="agama" class="form-select">
                                <option value="">Pilih</option>
                                <option>Islam</option>
                                <option>Kristen</option>
                                <option>Katolik</option>
                                <option>Hindu</option>
                                <option>Buddha</option>
                                <option>Konghucu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Perkawinan</label>
                            <select name="status_perkawinan" class="form-select">
                                <option value="">Pilih</option>
                                <option>Belum Kawin</option>
                                <option>Kawin</option>
                                <option>Cerai Hidup</option>
                                <option>Cerai Mati</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kewarganegaraan</label>
                            <input type="text" name="kewarganegaraan" class="form-control" value="WNI">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
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
