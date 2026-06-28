<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';
checkLogin();

$profil = getProfilDesa();
$totalPenduduk = getTotalPenduduk();
$totalMeninggal = getTotalMeninggal();
$totalPindah = getTotalPindah();
$totalKeseluruhan = getTotalPendudukKeseluruhan();
$perDusun = getPendudukPerDusun();
$perJK = getPendudukPerJK();
$perAgama = getPendudukPerAgama();
$perPekerjaan = getPendudukPerPekerjaan();
$usiaStats = getUsiaStats();

$username = $_SESSION['user_nama'];
$role = $_SESSION['user_role'];
$inisial = strtoupper(substr($username, 0, 1));

$isAdmin = isAdmin();
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
    <link rel="stylesheet" href="assets/css/style.css">
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
            <a href="admin/profil/index.php" class="nav-item"><i class="bi bi-building-gear"></i> Profil Desa</a>
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
            <div class="page-header">
                <h4>Dashboard</h4>
                <p>Selamat datang, <?= htmlspecialchars($username) ?>! Ringkasan data penduduk <?= htmlspecialchars($profil['nama_desa']) ?>.</p>
            </div>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-navy">
                        <i class="bi bi-people-fill stat-icon"></i>
                        <div class="stat-label">Total Penduduk Hidup</div>
                        <div class="stat-value"><?= $totalPenduduk ?></div>
                        <div class="stat-detail">Dari <?= $totalKeseluruhan ?> keseluruhan</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-green">
                        <i class="bi bi-person-plus-fill stat-icon"></i>
                        <div class="stat-label">Laki-laki</div>
                        <?php $lk = 0; foreach($perJK as $j) { if($j['jenis_kelamin']=='L') $lk=$j['jumlah']; } ?>
                        <div class="stat-value"><?= $lk ?></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-blue">
                        <i class="bi bi-person-fill stat-icon"></i>
                        <div class="stat-label">Perempuan</div>
                        <?php $pr = 0; foreach($perJK as $j) { if($j['jenis_kelamin']=='P') $pr=$j['jumlah']; } ?>
                        <div class="stat-value"><?= $pr ?></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-orange">
                        <i class="bi bi-box-arrow-right stat-icon"></i>
                        <div class="stat-label">Pindah</div>
                        <div class="stat-value"><?= $totalPindah ?></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-red">
                        <i class="bi bi-heartbreak-fill stat-icon"></i>
                        <div class="stat-label">Meninggal</div>
                        <div class="stat-value"><?= $totalMeninggal ?></div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">Data Penduduk per Dusun</div>
                        <div class="card-body">
                            <canvas id="chartDusun" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">Kelompok Usia</div>
                        <div class="card-body">
                            <canvas id="chartUsia" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">Agama</div>
                        <div class="card-body">
                            <canvas id="chartAgama" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">Pekerjaan</div>
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
</body>
</html>
