<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();

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

$total = count($data);
$judul_filter = '';
if ($f_dusun) $judul_filter .= ' Dusun: ' . $f_dusun;
if ($f_rt) $judul_filter .= ' RT: ' . $f_rt;
if ($f_status) $judul_filter .= ' Status: ' . $f_status;

// Include FPDF
require_once '../../assets/vendor/fpdf/fpdf.php';

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, 'LAPORAN DATA PENDUDUK', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, htmlspecialchars($profil['nama_desa']), 0, 1, 'C');
$pdf->Cell(0, 6, 'Kec. ' . htmlspecialchars($profil['kecamatan']) . ', ' . htmlspecialchars($profil['kabupaten']) . ', ' . htmlspecialchars($profil['provinsi']), 0, 1, 'C');
$pdf->Cell(0, 6, 'Periode: ' . formatTanggal($tgl_awal) . ' s/d ' . formatTanggal($tgl_akhir) . $judul_filter, 0, 1, 'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(10, 31, 63);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(10, 7, 'No', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'NIK', 1, 0, 'C', true);
$pdf->Cell(50, 7, 'Nama', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Tgl Lahir', 1, 0, 'C', true);
$pdf->Cell(15, 7, 'JK', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Dusun', 1, 0, 'C', true);
$pdf->Cell(15, 7, 'RT/RW', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Agama', 1, 0, 'C', true);
$pdf->Cell(20, 7, 'Status', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Tgl Input', 1, 1, 'C', true);

$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 8);
$no = 1;
foreach ($data as $d) {
    $pdf->Cell(10, 6, $no++, 1, 0, 'C');
    $pdf->Cell(35, 6, $d['nik'], 1);
    $pdf->Cell(50, 6, $d['nama'], 1);
    $pdf->Cell(30, 6, $d['tanggal_lahir'] ? formatTanggal($d['tanggal_lahir']) : '-', 1, 0, 'C');
    $pdf->Cell(15, 6, $d['jenis_kelamin'], 1, 0, 'C');
    $pdf->Cell(35, 6, $d['dusun'], 1);
    $pdf->Cell(15, 6, $d['rt'] . '/' . $d['rw'], 1, 0, 'C');
    $pdf->Cell(30, 6, $d['agama'], 1);
    $pdf->Cell(20, 6, $d['status_label'], 1, 0, 'C');
    $pdf->Cell(25, 6, formatTanggal($d['tgl_input']), 1, 1, 'C');
}

// Summary
$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Ringkasan:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Total Data: ' . $total . ' penduduk', 0, 1);
$pdf->Cell(0, 6, 'Tanggal Cetak: ' . formatTanggalWaktu(date('Y-m-d H:i:s')), 0, 1);

$pdf->Output('I', 'Laporan_Penduduk_' . $tgl_awal . '_sd_' . $tgl_akhir . '.pdf');
