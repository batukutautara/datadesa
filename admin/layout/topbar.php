<?php
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));
$isAdmin = isAdmin();
?>
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
