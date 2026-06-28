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
    $data = [
        'nama_desa' => trim($_POST['nama_desa']),
        'kecamatan' => trim($_POST['kecamatan']),
        'kabupaten' => trim($_POST['kabupaten']),
        'provinsi' => trim($_POST['provinsi']),
        'alamat' => trim($_POST['alamat']),
        'telepon' => trim($_POST['telepon']),
        'email' => trim($_POST['email']),
        'website' => trim($_POST['website']),
        'visi' => trim($_POST['visi']),
        'misi' => trim($_POST['misi']),
        'sejarah' => trim($_POST['sejarah']),
    ];

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'logo_' . time() . '.' . $ext;
            $dest = '../../uploads/logo/' . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                $data['logo'] = 'uploads/logo/' . $filename;
            } else {
                $error = 'Gagal upload logo!';
            }
        } else {
            $error = 'Format logo harus JPG, PNG, GIF, atau WebP!';
        }
    }

    if (!$error) {
        $fields = [];
        $vals = [];
        foreach ($data as $k => $v) {
            $fields[] = "$k = ?";
            $vals[] = $v;
        }
        $vals[] = 1;
        $sql = "UPDATE profil_desa SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profil desa berhasil diupdate!'];
        redirect('index.php');
    }
}

$stmt = $pdo->query("SELECT * FROM profil_desa WHERE id = 1");
$profil = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Desa - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Edit Profil Desa</h4>
                    <p>Ubah informasi identitas desa</p>
                </div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Desa</label>
                            <input type="text" name="nama_desa" class="form-control" value="<?= htmlspecialchars($profil['nama_desa']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" value="<?= htmlspecialchars($profil['kecamatan']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kabupaten</label>
                            <input type="text" name="kabupaten" class="form-control" value="<?= htmlspecialchars($profil['kabupaten']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($profil['provinsi']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($profil['telepon']) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alamat Kantor Desa</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($profil['alamat']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profil['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($profil['website']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo Desa</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <?php if ($profil['logo'] && file_exists($profil['logo'])): ?>
                                <small class="text-muted">Logo saat ini: <img src="../../<?= $profil['logo'] ?>" style="max-height: 30px; vertical-align: middle;"> Biarkan kosong jika tidak diganti</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Visi</label>
                            <textarea name="visi" class="form-control" rows="3"><?= htmlspecialchars($profil['visi']) ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Misi</label>
                            <textarea name="misi" class="form-control" rows="4"><?= htmlspecialchars($profil['misi']) ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Sejarah Desa</label>
                            <textarea name="sejarah" class="form-control" rows="5"><?= htmlspecialchars($profil['sejarah']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
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
