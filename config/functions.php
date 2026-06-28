<?php
require_once 'database.php';
require_once 'session.php';

function getProfilDesa() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM profil_desa WHERE id = 1");
    return $stmt->fetch();
}

function getTotalPenduduk() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM penduduk WHERE status_hidup = 'hidup'");
    return $stmt->fetchColumn();
}

function getTotalMeninggal() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM kematian");
    return $stmt->fetchColumn();
}

function getTotalPindah() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM pindah");
    return $stmt->fetchColumn();
}

function getTotalPendudukKeseluruhan() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM penduduk");
    return $stmt->fetchColumn();
}

function getPendudukPerDusun() {
    global $pdo;
    $stmt = $pdo->query("SELECT dusun, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY dusun ORDER BY jumlah DESC");
    return $stmt->fetchAll();
}

function getPendudukPerAgama() {
    global $pdo;
    $stmt = $pdo->query("SELECT agama, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY agama ORDER BY jumlah DESC");
    return $stmt->fetchAll();
}

function getPendudukPerPekerjaan() {
    global $pdo;
    $stmt = $pdo->query("SELECT pekerjaan, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY pekerjaan ORDER BY jumlah DESC");
    return $stmt->fetchAll();
}

function getPendudukPerJK() {
    global $pdo;
    $stmt = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY jenis_kelamin");
    return $stmt->fetchAll();
}

function formatTanggal($date) {
    if (!$date || $date == '0000-00-00') return '-';
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = date_parse($date);
    return $d['day'] . ' ' . $months[$d['month']] . ' ' . $d['year'];
}

function formatTanggalWaktu($datetime) {
    if (!$datetime) return '-';
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = date_parse($datetime);
    return $d['day'] . ' ' . $months[$d['month']] . ' ' . $d['year'] . ' ' . ($d['hour'] < 10 ? '0' : '') . $d['hour'] . ':' . ($d['minute'] < 10 ? '0' : '') . $d['minute'];
}

function usia($tanggal_lahir) {
    if (!$tanggal_lahir) return 0;
    $tgl = new DateTime($tanggal_lahir);
    $now = new DateTime();
    return $now->diff($tgl)->y;
}

function cekNikDuplikat($nik, $id = null) {
    global $pdo;
    if ($id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk WHERE nik = ? AND id != ?");
        $stmt->execute([$nik, $id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk WHERE nik = ?");
        $stmt->execute([$nik]);
    }
    return $stmt->fetchColumn() > 0;
}

function getUsiaStats() {
    global $pdo;
    $stmt = $pdo->query("SELECT tanggal_lahir FROM penduduk WHERE status_hidup = 'hidup' AND tanggal_lahir IS NOT NULL");
    $data = $stmt->fetchAll();
    
    $stats = ['0-5' => 0, '6-12' => 0, '13-17' => 0, '18-25' => 0, '26-35' => 0, '36-50' => 0, '51-60' => 0, '60+' => 0];
    foreach ($data as $row) {
        $u = usia($row['tanggal_lahir']);
        if ($u <= 5) $stats['0-5']++;
        elseif ($u <= 12) $stats['6-12']++;
        elseif ($u <= 17) $stats['13-17']++;
        elseif ($u <= 25) $stats['18-25']++;
        elseif ($u <= 35) $stats['26-35']++;
        elseif ($u <= 50) $stats['36-50']++;
        elseif ($u <= 60) $stats['51-60']++;
        else $stats['60+']++;
    }
    return $stats;
}
