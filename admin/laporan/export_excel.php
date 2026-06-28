<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');
$f_dusun = $_GET['dusun'] ?? '';
$f_rt = $_GET['rt'] ?? '';
$f_status = $_GET['status'] ?? '';

$where = ["p.tgl_input BETWEEN ? AND ?"];
$params = [$tgl_awal . ' 00:00:00', $tgl_akhir . ' 23:59:59'];
if ($f_dusun) { $where[] = "p.dusun = ?"; $params[] = $f_dusun; }
if ($f_rt) { $where[] = "p.rt = ?"; $params[] = $f_rt; }
if ($f_status) { $where[] = "p.status_hidup = ?"; $params[] = $f_status; }

$sql = "SELECT p.*, 
    CASE WHEN p.status_hidup = 'hidup' THEN 'Hidup' WHEN p.status_hidup = 'meninggal' THEN 'Meninggal' ELSE 'Pindah' END as status_label
    FROM penduduk p WHERE " . implode(" AND ", $where) . " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Output Excel (CSV format compatible with Excel)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Penduduk_' . $tgl_awal . '_sd_' . $tgl_akhir . '.xls"');
header('Cache-Control: max-age=0');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8"><style>td, th { border: 1px solid #000; padding: 4px; } th { background: #0A1F3F; color: #fff; }</style></head>';
echo '<body><table>';
echo '<tr><th colspan="8" style="font-size:16px;text-align:center;background:#fff;color:#000;">LAPORAN DATA PENDUDUK</th></tr>';
echo '<tr><td colspan="8" style="text-align:center;border:none;">Periode: ' . formatTanggal($tgl_awal) . ' s/d ' . formatTanggal($tgl_akhir) . '</td></tr>';
echo '<tr><td colspan="8" style="border:none;"></td></tr>';

echo '<tr>';
echo '<th>No</th><th>NIK</th><th>Nama</th><th>Tgl Lahir</th><th>JK</th><th>Dusun</th><th>RT/RW</th><th>Status</th>';
echo '</tr>';

$no = 1;
foreach ($data as $d) {
    echo '<tr>';
    echo '<td>' . $no++ . '</td>';
    echo '<td>' . htmlspecialchars($d['nik']) . '</td>';
    echo '<td>' . htmlspecialchars($d['nama']) . '</td>';
    echo '<td>' . formatTanggal($d['tanggal_lahir']) . '</td>';
    echo '<td>' . $d['jenis_kelamin'] . '</td>';
    echo '<td>' . htmlspecialchars($d['dusun']) . '</td>';
    echo '<td>' . htmlspecialchars($d['rt']) . '/' . htmlspecialchars($d['rw']) . '</td>';
    echo '<td>' . $d['status_label'] . '</td>';
    echo '</tr>';
}

echo '<tr><td colspan="8" style="border:none;"></td></tr>';
echo '<tr><td colspan="8" style="border:none;">Total: ' . count($data) . ' penduduk</td></tr>';
echo '<tr><td colspan="8" style="border:none;">Tanggal Cetak: ' . formatTanggalWaktu(date('Y-m-d H:i:s')) . '</td></tr>';
echo '</table></body></html>';
