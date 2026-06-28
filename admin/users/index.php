<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$stmt = $pdo->query("SELECT u.*, p.nama as nama_penduduk, p.nik FROM users u LEFT JOIN penduduk p ON u.penduduk_id = p.id ORDER BY u.id");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Manajemen User</h4>
                    <p>Kelola pengguna sistem</p>
                </div>
                <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?>"><?= $_SESSION['flash']['msg'] ?></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Nama Penduduk</th>
                                    <th>NIK</th>
                                    <th>Last Login</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td>
                                        <?php if ($u['role'] == 'admin'): ?>
                                            <span class="badge bg-navy">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Penduduk</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['nama_penduduk'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($u['nik'] ?: '-') ?></td>
                                    <td><?= $u['last_login'] ? formatTanggalWaktu($u['last_login']) : 'Belum login' ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-pencil"></i></a>
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
