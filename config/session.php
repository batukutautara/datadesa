<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLogin() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLogin() && $_SESSION['user_role'] === 'admin';
}

function isPenduduk() {
    return isLogin() && $_SESSION['user_role'] === 'penduduk';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function checkLogin() {
    if (!isLogin()) {
        $path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '../../login.php' : 'login.php';
        redirect($path);
    }
}

function checkAdmin() {
    checkLogin();
    if (!isAdmin()) {
        echo "<script>alert('Akses ditolak! Hanya untuk admin.'); window.location.href='../../index.php';</script>";
        exit;
    }
}
