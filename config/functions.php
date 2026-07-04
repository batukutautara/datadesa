<?php
require_once 'database.php';
require_once 'session.php';

function getProfilDesa() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM profil_desa WHERE id = 1");
    return $stmt->fetch();
}

function getTotalPenduduk($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk WHERE status_hidup = 'hidup' AND no_kk = ?");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM penduduk WHERE status_hidup = 'hidup'");
    }
    return $stmt->fetchColumn();
}

function getTotalMeninggal($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kematian k JOIN penduduk p ON k.penduduk_id = p.id WHERE p.no_kk = ?");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM kematian");
    }
    return $stmt->fetchColumn();
}

function getTotalPindah($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pindah pd JOIN penduduk p ON pd.penduduk_id = p.id WHERE p.no_kk = ?");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM pindah");
    }
    return $stmt->fetchColumn();
}

function getTotalPendudukKeseluruhan($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penduduk WHERE no_kk = ?");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM penduduk");
    }
    return $stmt->fetchColumn();
}

function getPendudukPerDusun($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT dusun, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' AND no_kk = ? GROUP BY dusun ORDER BY jumlah DESC");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT dusun, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY dusun ORDER BY jumlah DESC");
    }
    return $stmt->fetchAll();
}

function getPendudukPerAgama($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT agama, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' AND no_kk = ? GROUP BY agama ORDER BY jumlah DESC");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT agama, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY agama ORDER BY jumlah DESC");
    }
    return $stmt->fetchAll();
}

function getPendudukPerPekerjaan($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT pekerjaan, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' AND no_kk = ? GROUP BY pekerjaan ORDER BY jumlah DESC");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT pekerjaan, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY pekerjaan ORDER BY jumlah DESC");
    }
    return $stmt->fetchAll();
}

function getPendudukPerJK($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT jenis_kelamin, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' AND no_kk = ? GROUP BY jenis_kelamin");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT jenis_kelamin, COUNT(*) as jumlah FROM penduduk WHERE status_hidup = 'hidup' GROUP BY jenis_kelamin");
    }
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

function getUsiaStats($no_kk = null) {
    global $pdo;
    if ($no_kk) {
        $stmt = $pdo->prepare("SELECT tanggal_lahir FROM penduduk WHERE status_hidup = 'hidup' AND tanggal_lahir IS NOT NULL AND no_kk = ?");
        $stmt->execute([$no_kk]);
    } else {
        $stmt = $pdo->query("SELECT tanggal_lahir FROM penduduk WHERE status_hidup = 'hidup' AND tanggal_lahir IS NOT NULL");
    }
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

function getKeluargaByKK($no_kk) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM keluarga WHERE no_kk = ?");
    $stmt->execute([$no_kk]);
    return $stmt->fetch();
}

function getAnggotaKeluarga($no_kk) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM penduduk WHERE no_kk = ? ORDER BY FIELD(hubungan_keluarga, 'KEPALA KELUARGA','SUAMI','ISTRI','ANAK','MENANTU','CUCU','ORANG TUA','MERTUA','FAMILI LAIN','LAINNYA')");
    $stmt->execute([$no_kk]);
    return $stmt->fetchAll();
}

function getSemuaKeluarga() {
    global $pdo;
    $stmt = $pdo->query("SELECT k.*, (SELECT COUNT(*) FROM penduduk WHERE no_kk = k.no_kk) as jumlah_anggota FROM keluarga k ORDER BY k.no_kk");
    return $stmt->fetchAll();
}

function cekKKDuplikat($no_kk, $id = null) {
    global $pdo;
    if ($id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM keluarga WHERE no_kk = ? AND id != ?");
        $stmt->execute([$no_kk, $id]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM keluarga WHERE no_kk = ?");
        $stmt->execute([$no_kk]);
    }
    return $stmt->fetchColumn() > 0;
}

function cekUserByKK($no_kk) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE no_kk = ?");
    $stmt->execute([$no_kk]);
    return $stmt->fetchColumn() > 0;
}
