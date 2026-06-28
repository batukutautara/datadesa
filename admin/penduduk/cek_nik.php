<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$nik = $_POST['nik'] ?? '';
$id = $_POST['id'] ?? '';

if ($nik) {
    if ($id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk WHERE nik = ? AND id != ?");
        $stmt->execute([$nik, $id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk WHERE nik = ?");
        $stmt->execute([$nik]);
    }
    $count = $stmt->fetchColumn();
    echo json_encode(['duplicate' => $count > 0]);
} else {
    echo json_encode(['duplicate' => false]);
}
