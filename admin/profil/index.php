<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Desa - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Profil Desa</h4>
                    <p>Informasi dan identitas desa</p>
                </div>
                <a href="edit.php" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit Profil</a>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <?php if ($profil['logo'] && file_exists($profil['logo'])): ?>
                                <img src="../../<?= htmlspecialchars($profil['logo']) ?>" alt="Logo Desa" style="max-height: 120px; margin-bottom: 15px;">
                            <?php else: ?>
                                <i class="bi bi-building" style="font-size: 80px; color: var(--navy); margin-bottom: 15px; display: block;"></i>
                                <p class="text-muted">Belum ada logo</p>
                            <?php endif; ?>
                            <h5 class="fw-bold"><?= htmlspecialchars($profil['nama_desa']) ?></h5>
                            <p class="text-muted">Kec. <?= htmlspecialchars($profil['kecamatan']) ?>, <?= htmlspecialchars($profil['kabupaten']) ?>, <?= htmlspecialchars($profil['provinsi']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">Detail Informasi</div>
                        <div class="card-body">
                            <table class="table table-custom">
                                <tr>
                                    <th style="width: 200px;">Nama Desa</th>
                                    <td><?= htmlspecialchars($profil['nama_desa'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Kecamatan</th>
                                    <td><?= htmlspecialchars($profil['kecamatan'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Kabupaten</th>
                                    <td><?= htmlspecialchars($profil['kabupaten'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Provinsi</th>
                                    <td><?= htmlspecialchars($profil['provinsi'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td><?= nl2br(htmlspecialchars($profil['alamat'] ?: '-')) ?></td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td><?= htmlspecialchars($profil['telepon'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?= htmlspecialchars($profil['email'] ?: '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Website</th>
                                    <td><?= htmlspecialchars($profil['website'] ?: '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">Visi & Misi</div>
                        <div class="card-body">
                            <h6 class="fw-bold">Visi</h6>
                            <p><?= nl2br(htmlspecialchars($profil['visi'] ?: '-')) ?></p>
                            <h6 class="fw-bold mt-3">Misi</h6>
                            <p><?= nl2br(htmlspecialchars($profil['misi'] ?: '-')) ?></p>
                        </div>
                    </div>

                    <?php if ($profil['sejarah']): ?>
                    <div class="card mt-3">
                        <div class="card-header">Sejarah Desa</div>
                        <div class="card-body">
                            <p><?= nl2br(htmlspecialchars($profil['sejarah'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
