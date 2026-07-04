<?php
$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));
$isAdmin = isAdmin();
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <?php if ($profil['logo'] && file_exists($profil['logo'])): ?>
            <img src="../../<?= htmlspecialchars($profil['logo']) ?>" alt="Logo">
        <?php else: ?>
            <i class="bi bi-building" style="font-size: 40px; opacity: 0.7;"></i>
        <?php endif; ?>
        <h6><?= htmlspecialchars($profil['nama_desa']) ?></h6>
        <small>Sistem Informasi Penduduk</small>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Menu Utama</div>
        <a href="../../index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' && basename(dirname($_SERVER['PHP_SELF'])) == '' ? 'active' : '' ?>"><i class="bi bi-house-door"></i> Dashboard</a>
        <?php if ($isAdmin): ?>
        <a href="../penduduk/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'penduduk') !== false ? 'active' : '' ?>"><i class="bi bi-people"></i> Data Penduduk</a>
        <a href="../keluarga/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'keluarga') !== false ? 'active' : '' ?>"><i class="bi bi-house-heart"></i> Data Keluarga</a>
        <a href="../kematian/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'kematian') !== false ? 'active' : '' ?>"><i class="bi bi-heartbreak"></i> Kematian</a>
        <a href="../pindah/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'pindah') !== false ? 'active' : '' ?>"><i class="bi bi-box-arrow-right"></i> Pindah</a>
        <div class="menu-label">Laporan</div>
        <a href="../laporan/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'laporan') !== false ? 'active' : '' ?>"><i class="bi bi-file-earmark-text"></i> Rekapan & Laporan</a>
        <div class="menu-label">Pengaturan</div>
        <a href="../restore/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'restore') !== false ? 'active' : '' ?>"><i class="bi bi-cloud-upload"></i> Restore Data</a>
        <a href="../profil/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'profil') !== false ? 'active' : '' ?>"><i class="bi bi-building-gear"></i> Profil Desa</a>
        <a href="../users/index.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : '' ?>"><i class="bi bi-shield-lock"></i> Manajemen User</a>
        <?php endif; ?>
        <div class="menu-label">Akun</div>
        <a href="../../logout.php" class="nav-item"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>
