<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$error = '';
$success = '';

$stmt = $pdo->query("SELECT id, nik, nama FROM penduduk WHERE status_hidup = 'hidup' ORDER BY nama");
$penduduk_hidup = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $penduduk_id = $_POST['penduduk_id'];
    $tgl_meninggal = $_POST['tgl_meninggal'];
    $tempat_meninggal = trim($_POST['tempat_meninggal']);
    $penyebab = trim($_POST['penyebab']);
    $keterangan = trim($_POST['keterangan']);

    if (!$penduduk_id || !$tgl_meninggal) {
        $error = 'Pilih penduduk dan tanggal meninggal!';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO kematian (penduduk_id, tgl_meninggal, tempat_meninggal, penyebab, keterangan) VALUES (?,?,?,?,?)");
            $stmt->execute([$penduduk_id, $tgl_meninggal, $tempat_meninggal, $penyebab, $keterangan]);

            $stmt = $pdo->prepare("UPDATE penduduk SET status_hidup = 'meninggal' WHERE id = ?");
            $stmt->execute([$penduduk_id]);

            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data kematian berhasil dicatat!'];
            redirect('index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kematian - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Tambah Kematian</h4>
                    <p>Catat penduduk yang meninggal</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pilih Penduduk <span class="text-danger">*</span></label>
                            <select name="penduduk_id" class="form-select select2" required>
                                <option value="">-- Pilih Penduduk --</option>
                                <?php foreach ($penduduk_hidup as $ph): ?>
                                <option value="<?= $ph['id'] ?>"><?= htmlspecialchars($ph['nik']) ?> - <?= htmlspecialchars($ph['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Meninggal <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_meninggal" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tempat Meninggal</label>
                            <input type="text" name="tempat_meninggal" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Penyebab</label>
                            <input type="text" name="penyebab" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger"><i class="bi bi-save"></i> Simpan</button>
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
    <script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'classic', width: '100%' });
    });
    </script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
