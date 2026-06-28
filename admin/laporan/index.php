<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$dusunList = $pdo->query("SELECT DISTINCT dusun FROM penduduk WHERE dusun != '' ORDER BY dusun")->fetchAll();
$rtList = $pdo->query("SELECT DISTINCT rt FROM penduduk WHERE rt != '' ORDER BY rt")->fetchAll();

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');
$f_dusun = $_GET['dusun'] ?? '';
$f_rt = $_GET['rt'] ?? '';
$f_status = $_GET['status'] ?? '';
$tampil = isset($_GET['tampil']);

// Main query
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

// Stats for selected filter
$total_filter = count($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../layout/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../layout/topbar.php'; ?>
        <div class="content-wrapper">
            <div class="page-header">
                <h4>Rekapan & Laporan</h4>
                <p>Filter data penduduk dan download laporan</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dusun</label>
                            <select name="dusun" class="form-select">
                                <option value="">Semua</option>
                                <?php foreach ($dusunList as $d): ?>
                                <option value="<?= htmlspecialchars($d['dusun']) ?>" <?= $f_dusun==$d['dusun']?'selected':'' ?>><?= htmlspecialchars($d['dusun']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RT</label>
                            <select name="rt" class="form-select">
                                <option value="">Semua</option>
                                <?php foreach ($rtList as $r): ?>
                                <option value="<?= htmlspecialchars($r['rt']) ?>" <?= $f_rt==$r['rt']?'selected':'' ?>><?= htmlspecialchars($r['rt']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua</option>
                                <option value="hidup" <?= $f_status=='hidup'?'selected':'' ?>>Hidup</option>
                                <option value="meninggal" <?= $f_status=='meninggal'?'selected':'' ?>>Meninggal</option>
                                <option value="pindah" <?= $f_status=='pindah'?'selected':'' ?>>Pindah</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="tampil" value="1" class="btn btn-navy w-100"><i class="bi bi-search"></i> Tampilkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($tampil): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Hasil Laporan (<?= $total_filter ?> data ditemukan)</span>
                    <div>
                        <a href="cetak_pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&dusun=<?= $f_dusun ?>&rt=<?= $f_rt ?>&status=<?= $f_status ?>" class="btn btn-sm btn-danger" target="_blank"><i class="bi bi-filetype-pdf"></i> PDF</a>
                        <a href="export_excel.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&dusun=<?= $f_dusun ?>&rt=<?= $f_rt ?>&status=<?= $f_status ?>" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Tgl Lahir</th>
                                    <th>JK</th>
                                    <th>Dusun</th>
                                    <th>RT/RW</th>
                                    <th>Status</th>
                                    <th>Tgl Input</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($data as $d): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($d['nik']) ?></td>
                                    <td><?= htmlspecialchars($d['nama']) ?></td>
                                    <td><?= formatTanggal($d['tanggal_lahir']) ?></td>
                                    <td><?= $d['jenis_kelamin'] ?></td>
                                    <td><?= htmlspecialchars($d['dusun']) ?></td>
                                    <td><?= htmlspecialchars($d['rt']) ?>/<?= htmlspecialchars($d['rw']) ?></td>
                                    <td>
                                        <?php if ($d['status_hidup'] == 'hidup'): ?><span class="badge bg-success">Hidup</span>
                                        <?php elseif ($d['status_hidup'] == 'meninggal'): ?><span class="badge bg-danger">Meninggal</span>
                                        <?php else: ?><span class="badge bg-warning text-dark">Pindah</span><?php endif; ?>
                                    </td>
                                    <td><?= formatTanggal($d['tgl_input']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
