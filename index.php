<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';
checkLogin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$role = $_SESSION['user_role'];
$inisial = strtoupper(substr($username, 0, 1));
$isAdmin = isAdmin();

$no_kk = null;
if ($role === 'penduduk' && !empty($_SESSION['user_no_kk'])) {
    $no_kk = $_SESSION['user_no_kk'];
}

$totalPenduduk = getTotalPenduduk($no_kk);
$totalMeninggal = getTotalMeninggal($no_kk);
$totalPindah = getTotalPindah($no_kk);
$totalKeseluruhan = getTotalPendudukKeseluruhan($no_kk);
$perDusun = getPendudukPerDusun($no_kk);
$perJK = getPendudukPerJK($no_kk);
$perAgama = getPendudukPerAgama($no_kk);
$perPekerjaan = getPendudukPerPekerjaan($no_kk);
$usiaStats = getUsiaStats($no_kk);

$error_profil = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profil']) && $isAdmin) {
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

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'logo_' . time() . '.' . $ext;
            $dest = 'uploads/logo/' . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                $data['logo'] = 'uploads/logo/' . $filename;
            } else {
                $error_profil = 'Gagal upload logo!';
            }
        } else {
            $error_profil = 'Format logo harus JPG, PNG, GIF, atau WebP!';
        }
    }

    if (!$error_profil) {
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
        $profil = getProfilDesa();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <?php if ($profil['logo'] && file_exists($profil['logo'])): ?>
                <img src="<?= htmlspecialchars($profil['logo']) ?>" alt="Logo">
            <?php else: ?>
                <i class="bi bi-building" style="font-size: 40px; opacity: 0.7;"></i>
            <?php endif; ?>
            <h6><?= htmlspecialchars($profil['nama_desa']) ?></h6>
            <small>Sistem Informasi Penduduk</small>
        </div>
        <div class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="index.php" class="nav-item active"><i class="bi bi-house-door"></i> Dashboard</a>
            <?php if ($isAdmin): ?>
            <a href="admin/penduduk/index.php" class="nav-item"><i class="bi bi-people"></i> Data Penduduk</a>
            <a href="admin/kematian/index.php" class="nav-item"><i class="bi bi-heartbreak"></i> Kematian</a>
            <a href="admin/pindah/index.php" class="nav-item"><i class="bi bi-box-arrow-right"></i> Pindah</a>
            <div class="menu-label">Laporan</div>
            <a href="admin/laporan/index.php" class="nav-item"><i class="bi bi-file-earmark-text"></i> Rekapan & Laporan</a>
            <div class="menu-label">Pengaturan</div>
            <a href="#profil-desa" class="nav-item"><i class="bi bi-building-gear"></i> Profil Desa</a>
            <a href="admin/users/index.php" class="nav-item"><i class="bi bi-shield-lock"></i> Manajemen User</a>
            <?php endif; ?>
            <div class="menu-label">Akun</div>
            <a href="logout.php" class="nav-item"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <button class="toggle-sidebar"><i class="bi bi-list"></i></button>
            <div></div>
            <div class="user-info">
                <div class="user-detail text-end">
                    <div class="name"><?= htmlspecialchars($username) ?></div>
                    <div class="role"><?= $isAdmin ? 'Administrator' : 'Penduduk' ?></div>
                </div>
                <div class="avatar"><?= $inisial ?></div>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show"><?= $_SESSION['flash']['msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="hero-decoration"></div>
                <div class="hero-content">
                    <?php if ($role === 'penduduk' && $no_kk): ?>
                        <?php $keluarga = getKeluargaByKK($no_kk); ?>
                        <h4 class="hero-title">Keluarga <span class="text-gradient"><?= htmlspecialchars($no_kk) ?></span></h4>
                        <p class="hero-subtitle">Selamat datang, <strong><?= htmlspecialchars($username) ?></strong>! Berikut data anggota keluarga Anda.</p>
                        <?php if ($keluarga): ?>
                        <p class="hero-subtitle" style="margin-top:4px"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($keluarga['dusun'] ?: '-') ?>, RT <?= htmlspecialchars($keluarga['rt'] ?: '-') ?>/RW <?= htmlspecialchars($keluarga['rw'] ?: '-') ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                    <h4 class="hero-title">Dashboard <span class="text-gradient"><?= htmlspecialchars($profil['nama_desa']) ?></span></h4>
                    <p class="hero-subtitle">Selamat datang, <strong><?= htmlspecialchars($username) ?></strong>! Berikut ringkasan data penduduk desa.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profil Desa -->
            <div class="card card-modern mb-4" id="profil-desa">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-building-gear"></i> <?= htmlspecialchars($profil['nama_desa'] ?: 'Profil Desa') ?></span>
                    <?php if ($isAdmin): ?>
                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editProfilModal">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="profil-sidebar text-center">
                                <?php if ($profil['logo'] && file_exists($profil['logo'])): ?>
                                    <img src="<?= htmlspecialchars($profil['logo']) ?>" alt="Logo Desa" style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid white;box-shadow:0 4px 15px rgba(13,110,253,0.15);margin-bottom:16px;">
                                <?php else: ?>
                                    <div class="profil-logo-wrap">
                                        <i class="bi bi-building"></i>
                                    </div>
                                <?php endif; ?>
                                <h5 class="fw-bold"><?= htmlspecialchars($profil['nama_desa']) ?></h5>
                                <span class="location-badge"><i class="bi bi-geo-alt"></i> Kec. <?= htmlspecialchars($profil['kecamatan']) ?>, <?= htmlspecialchars($profil['kabupaten']) ?></span>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="profil-section">
                                <h6 class="profil-section-title"><i class="bi bi-book"></i> Sejarah Desa</h6>
                                <p class="profil-section-text"><?= nl2br(htmlspecialchars($profil['sejarah'] ?: '-')) ?></p>
                            </div>
                            <div class="profil-section">
                                <h6 class="profil-section-title"><i class="bi bi-eye"></i> Visi & Misi</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="vm-mini-card">
                                            <span class="vm-mini-label">Visi</span>
                                            <p class="vm-mini-text"><?= nl2br(htmlspecialchars($profil['visi'] ?: '-')) ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="vm-mini-card">
                                            <span class="vm-mini-label">Misi</span>
                                            <p class="vm-mini-text"><?= nl2br(htmlspecialchars($profil['misi'] ?: '-')) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="profil-section">
                                <h6 class="profil-section-title"><i class="bi bi-telephone"></i> Kontak Desa</h6>
                                <div class="row g-2">
                                    <div class="col-sm-4">
                                        <div class="kontak-mini-card">
                                            <i class="bi bi-telephone-fill"></i>
                                            <span class="kontak-mini-label">Telepon</span>
                                            <span class="kontak-mini-value"><?= htmlspecialchars($profil['telepon'] ?: '-') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="kontak-mini-card">
                                            <i class="bi bi-envelope-fill"></i>
                                            <span class="kontak-mini-label">Email</span>
                                            <span class="kontak-mini-value"><?= htmlspecialchars($profil['email'] ?: '-') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="kontak-mini-card">
                                            <i class="bi bi-globe"></i>
                                            <span class="kontak-mini-label">Website</span>
                                            <span class="kontak-mini-value"><?= htmlspecialchars($profil['website'] ?: '-') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="profil-section">
                                <h6 class="profil-section-title"><i class="bi bi-geo-alt"></i> Alamat Desa</h6>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="alamat-mini-card">
                                            <span class="alamat-mini-label">Alamat</span>
                                            <span class="alamat-mini-value"><?= nl2br(htmlspecialchars($profil['alamat'] ?: '-')) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row g-2">
                                            <div class="col-6"><div class="alamat-chip"><span class="ac-label">Kecamatan</span><span class="ac-value"><?= htmlspecialchars($profil['kecamatan'] ?: '-') ?></span></div></div>
                                            <div class="col-6"><div class="alamat-chip"><span class="ac-label">Kabupaten</span><span class="ac-value"><?= htmlspecialchars($profil['kabupaten'] ?: '-') ?></span></div></div>
                                            <div class="col-6"><div class="alamat-chip"><span class="ac-label">Provinsi</span><span class="ac-value"><?= htmlspecialchars($profil['provinsi'] ?: '-') ?></span></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($role === 'penduduk' && $no_kk): $anggota = getAnggotaKeluarga($no_kk); $keluarga = getKeluargaByKK($no_kk); ?>
            <div class="card card-modern mb-4">
                <div class="card-header"><i class="bi bi-people"></i> Anggota Keluarga</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tempat, Tgl Lahir</th>
                                    <th>Hubungan</th>
                                    <th>Pekerjaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($anggota as $a): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($a['nik']) ?></td>
                                    <td><?= htmlspecialchars($a['nama']) ?></td>
                                    <td><?= $a['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                    <td><?= htmlspecialchars($a['tempat_lahir']) ?>, <?= formatTanggal($a['tanggal_lahir']) ?> (<?= usia($a['tanggal_lahir']) ?> thn)</td>
                                    <td><span class="badge bg-<?= $a['hubungan_keluarga'] == 'KEPALA KELUARGA' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($a['hubungan_keluarga']) ?></span></td>
                                    <td><?= htmlspecialchars($a['pekerjaan'] ?: '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4 row-cols-2 row-cols-md-3 row-cols-xl-5">
                <div class="col">
                    <div class="stat-card-modern stat-navy-modern" style="border-radius:10px;padding:24px;background:linear-gradient(135deg,#0A1F3F,#0D2B4E);color:white;min-height:130px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden">
                        <i class="bi bi-people-fill stat-bg-icon"></i>
                        <div class="stat-icon-wrap" style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.12);color:#7EB8FF;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px"><i class="bi bi-people-fill"></i></div>
                        <div class="stat-label" style="font-size:13px;font-weight:600;margin-bottom:6px;opacity:0.85">Total Penduduk</div>
                        <div class="stat-value" style="font-size:36px;font-weight:800;line-height:1;letter-spacing:-1px"><?= $totalPenduduk ?></div>
                        <div class="stat-detail" style="font-size:12px;opacity:0.7;margin-top:6px">Dari <?= $totalKeseluruhan ?></div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card-modern stat-green-modern" style="border-radius:10px;padding:24px;background:linear-gradient(135deg,#1B8A6B,#2ECC71);color:white;min-height:130px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden">
                        <i class="bi bi-person-plus-fill stat-bg-icon"></i>
                        <div class="stat-icon-wrap" style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.15);color:#A8FFD6;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px"><i class="bi bi-person-plus-fill"></i></div>
                        <div class="stat-label" style="font-size:13px;font-weight:600;margin-bottom:6px;opacity:0.85">Laki-laki</div>
                        <?php $lk = 0; foreach($perJK as $j) { if($j['jenis_kelamin']=='L') $lk=$j['jumlah']; } ?>
                        <div class="stat-value" style="font-size:36px;font-weight:800;line-height:1;letter-spacing:-1px"><?= $lk ?></div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card-modern stat-blue-modern" style="border-radius:10px;padding:24px;background:linear-gradient(135deg,#0D6EFD,#2196F3);color:white;min-height:130px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden">
                        <i class="bi bi-person-fill stat-bg-icon"></i>
                        <div class="stat-icon-wrap" style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.15);color:#B8D9FF;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px"><i class="bi bi-person-fill"></i></div>
                        <div class="stat-label" style="font-size:13px;font-weight:600;margin-bottom:6px;opacity:0.85">Perempuan</div>
                        <?php $pr = 0; foreach($perJK as $j) { if($j['jenis_kelamin']=='P') $pr=$j['jumlah']; } ?>
                        <div class="stat-value" style="font-size:36px;font-weight:800;line-height:1;letter-spacing:-1px"><?= $pr ?></div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card-modern stat-orange-modern" style="border-radius:10px;padding:24px;background:linear-gradient(135deg,#D4762A,#F39C12);color:white;min-height:130px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden">
                        <i class="bi bi-box-arrow-right stat-bg-icon"></i>
                        <div class="stat-icon-wrap" style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.15);color:#FFE0A8;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px"><i class="bi bi-box-arrow-right"></i></div>
                        <div class="stat-label" style="font-size:13px;font-weight:600;margin-bottom:6px;opacity:0.85">Pindah</div>
                        <div class="stat-value" style="font-size:36px;font-weight:800;line-height:1;letter-spacing:-1px"><?= $totalPindah ?></div>
                    </div>
                </div>
                <div class="col">
                    <div class="stat-card-modern stat-red-modern" style="border-radius:10px;padding:24px;background:linear-gradient(135deg,#B03A2E,#E74C3C);color:white;min-height:130px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden">
                        <i class="bi bi-heartbreak-fill stat-bg-icon"></i>
                        <div class="stat-icon-wrap" style="width:48px;height:48px;border-radius:12px;background:rgba(255,255,255,0.15);color:#FFB0A8;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px"><i class="bi bi-heartbreak-fill"></i></div>
                        <div class="stat-label" style="font-size:13px;font-weight:600;margin-bottom:6px;opacity:0.85">Meninggal</div>
                        <div class="stat-value" style="font-size:36px;font-weight:800;line-height:1;letter-spacing:-1px"><?= $totalMeninggal ?></div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3">
                <div class="col-xl-6">
                    <div class="card chart-card">
                        <div class="card-header"><i class="bi bi-bar-chart-fill"></i> Data Penduduk per Dusun</div>
                        <div class="card-body">
                            <canvas id="chartDusun" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card chart-card">
                        <div class="card-header"><i class="bi bi-bar-chart-fill"></i> Kelompok Usia</div>
                        <div class="card-body">
                            <canvas id="chartUsia" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card chart-card">
                        <div class="card-header"><i class="bi bi-pie-chart-fill"></i> Agama</div>
                        <div class="card-body">
                            <canvas id="chartAgama" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card chart-card">
                        <div class="card-header"><i class="bi bi-pie-chart-fill"></i> Pekerjaan</div>
                        <div class="card-body">
                            <canvas id="chartPekerjaan" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    // Chart Dusun
    new Chart(document.getElementById('chartDusun'), {
        type: 'bar',
        data: {
            labels: [<?php foreach($perDusun as $d) { echo "'" . addslashes($d['dusun']) . "',"; } ?>],
            datasets: [{
                label: 'Jumlah',
                data: [<?php foreach($perDusun as $d) { echo $d['jumlah'] . ","; } ?>],
                backgroundColor: ['#0A1F3F','#0D6EFD','#2196F3','#1B8A6B','#D4762A','#B03A2E','#6F42C1'],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: '#eef1f5' } } }
        }
    });

    // Chart Usia
    new Chart(document.getElementById('chartUsia'), {
        type: 'bar',
        data: {
            labels: [<?php foreach($usiaStats as $k=>$v) { echo "'$k',"; } ?>],
            datasets: [{
                label: 'Jumlah',
                data: [<?php foreach($usiaStats as $v) { echo "$v,"; } ?>],
                backgroundColor: ['#0D6EFD','#2196F3','#0A1F3F','#1B8A6B','#D4762A','#B03A2E','#6F42C1','#E74C3C'],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: '#eef1f5' } } }
        }
    });

    // Chart Agama
    new Chart(document.getElementById('chartAgama'), {
        type: 'pie',
        data: {
            labels: [<?php foreach($perAgama as $d) { echo "'" . addslashes($d['agama'] ?: 'Tidak diisi') . "',"; } ?>],
            datasets: [{
                data: [<?php foreach($perAgama as $d) { echo $d['jumlah'] . ","; } ?>],
                backgroundColor: ['#0A1F3F','#0D6EFD','#2196F3','#1B8A6B','#D4762A','#B03A2E','#6F42C1']
            }]
        }
    });

    // Chart Pekerjaan
    new Chart(document.getElementById('chartPekerjaan'), {
        type: 'doughnut',
        data: {
            labels: [<?php foreach($perPekerjaan as $d) { echo "'" . addslashes($d['pekerjaan'] ?: 'Tidak diisi') . "',"; } ?>],
            datasets: [{
                data: [<?php foreach($perPekerjaan as $d) { echo $d['jumlah'] . ","; } ?>],
                backgroundColor: ['#0A1F3F','#0D6EFD','#2196F3','#1B8A6B','#D4762A','#B03A2E','#6F42C1','#E74C3C','#17A2B8','#28A745']
            }]
        }
    });
    </script>

<?php if ($isAdmin): ?>
<!-- Modal Edit Profil Desa -->
<div class="modal fade" id="editProfilModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_profil" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profil Desa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($error_profil): ?>
                    <div class="alert alert-danger"><?= $error_profil ?></div>
                    <?php endif; ?>
                    <div class="row g-3">
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
                            <small class="text-muted">Logo saat ini: <img src="<?= $profil['logo'] ?>" style="max-height: 30px; vertical-align: middle;"> Biarkan kosong jika tidak diganti</small>
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($error_profil): ?>
<script>
var editProfilModal = new bootstrap.Modal(document.getElementById('editProfilModal'));
editProfilModal.show();
</script>
<?php endif; ?>
<?php endif; ?>

</body>
</html>
