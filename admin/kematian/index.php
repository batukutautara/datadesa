<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$stmt = $pdo->query("SELECT k.*, p.nama, p.nik FROM kematian k JOIN penduduk p ON k.penduduk_id = p.id ORDER BY k.id DESC");
$kematian = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kematian - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../layout/topbar.php'; ?>
        <div class="content-wrapper">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>Data Kematian</h4>
                    <p>Catatan penduduk yang meninggal</p>
                </div>
                <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Kematian</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Tgl Meninggal</th>
                                    <th>Tempat</th>
                                    <th>Penyebab</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($kematian as $k): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($k['nik']) ?></td>
                                    <td><?= htmlspecialchars($k['nama']) ?></td>
                                    <td><?= formatTanggal($k['tgl_meninggal']) ?></td>
                                    <td><?= htmlspecialchars($k['tempat_meninggal']) ?></td>
                                    <td><?= htmlspecialchars($k['penyebab']) ?></td>
                                    <td><?= htmlspecialchars($k['keterangan']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
