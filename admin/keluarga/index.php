<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$keluarga = getSemuaKeluarga();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Keluarga - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show"><?= $_SESSION['flash']['msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>Data Keluarga</h4>
                    <p>Kelola Kartu Keluarga (KK)</p>
                </div>
                <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah KK</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. KK</th>
                                    <th>Kepala Keluarga</th>
                                    <th>Jumlah Anggota</th>
                                    <th>Dusun</th>
                                    <th>RT/RW</th>
                                    <th>Desa/Kel</th>
                                    <th>Kecamatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($keluarga as $k):
                                    $stmt = $pdo->prepare("SELECT nama FROM penduduk WHERE no_kk = ? AND hubungan_keluarga = 'KEPALA KELUARGA' LIMIT 1");
                                    $stmt->execute([$k['no_kk']]);
                                    $kepala = $stmt->fetchColumn();
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($k['no_kk']) ?></td>
                                    <td><?= htmlspecialchars($kepala ?: '-') ?></td>
                                    <td><span class="badge bg-info"><?= $k['jumlah_anggota'] ?> Orang</span></td>
                                    <td><?= htmlspecialchars($k['dusun']) ?></td>
                                    <td><?= htmlspecialchars($k['rt']) ?>/<?= htmlspecialchars($k['rw']) ?></td>
                                    <td><?= htmlspecialchars($k['desa_kel']) ?></td>
                                    <td><?= htmlspecialchars($k['kecamatan']) ?></td>
                                    <td>
                                        <a href="detail.php?no_kk=<?= urlencode($k['no_kk']) ?>" class="btn btn-sm btn-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?action=delete&id=<?= $k['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus" onclick="return confirm('Hapus keluarga ini? Data penduduk tetap tersimpan.')"><i class="bi bi-trash"></i></a>
                                    </td>
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
