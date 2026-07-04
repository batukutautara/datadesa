<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$no_kk = $_GET['no_kk'] ?? '';
$keluarga = getKeluargaByKK($no_kk);

if (!$keluarga) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Data tidak ditemukan!'];
    redirect('index.php');
}

$anggota = getAnggotaKeluarga($no_kk);

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Keluarga - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
                    <h4>Detail Keluarga</h4>
                    <p>No. KK: <?= htmlspecialchars($no_kk) ?></p>
                </div>
                <div>
                    <a href="edit.php?id=<?= $keluarga['id'] ?>" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit KK</a>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-house"></i> Informasi KK</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>No. KK</strong><br><?= htmlspecialchars($keluarga['no_kk']) ?></div>
                        <div class="col-md-3"><strong>Dusun</strong><br><?= htmlspecialchars($keluarga['dusun'] ?: '-') ?></div>
                        <div class="col-md-2"><strong>RT/RW</strong><br><?= htmlspecialchars($keluarga['rt'] ?: '-') ?>/<?= htmlspecialchars($keluarga['rw'] ?: '-') ?></div>
                        <div class="col-md-4"><strong>Desa/Kel - Kecamatan</strong><br><?= htmlspecialchars($keluarga['desa_kel'] ?: '-') ?>, <?= htmlspecialchars($keluarga['kecamatan'] ?: '-') ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><strong>Alamat</strong><br><?= nl2br(htmlspecialchars($keluarga['alamat'] ?: '-')) ?></div>
                        <div class="col-md-3"><strong>Kabupaten</strong><br><?= htmlspecialchars($keluarga['kabupaten'] ?: '-') ?></div>
                        <div class="col-md-3"><strong>Provinsi</strong><br><?= htmlspecialchars($keluarga['provinsi'] ?: '-') ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people"></i> Anggota Keluarga (<?= count($anggota) ?>)</span>
                    <a href="../penduduk/tambah.php?no_kk=<?= urlencode($no_kk) ?>" class="btn btn-sm btn-primary"><i class="bi bi-person-plus"></i> Tambah Anggota</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tempat, Tgl Lahir</th>
                                    <th>Hubungan Keluarga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($anggota as $a): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($a['nik']) ?></td>
                                    <td><?= htmlspecialchars($a['nama']) ?></td>
                                    <td><?= $a['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                    <td><?= htmlspecialchars($a['tempat_lahir']) ?>, <?= formatTanggal($a['tanggal_lahir']) ?></td>
                                    <td><span class="badge bg-<?= $a['hubungan_keluarga'] == 'KEPALA KELUARGA' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($a['hubungan_keluarga']) ?></span></td>
                                    <td>
                                        <?php if ($a['status_hidup'] == 'hidup'): ?>
                                            <span class="badge bg-success">Hidup</span>
                                        <?php elseif ($a['status_hidup'] == 'meninggal'): ?>
                                            <span class="badge bg-danger">Meninggal</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pindah</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../penduduk/edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($anggota)): ?>
                                <tr><td colspan="8" class="text-center">Belum ada anggota keluarga</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
