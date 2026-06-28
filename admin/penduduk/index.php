<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkAdmin();

$action = $_GET['action'] ?? '';

if ($action == 'get') {
    $stmt = $pdo->query("SELECT p.*, 
        CASE WHEN p.status_hidup = 'hidup' THEN 'Hidup' WHEN p.status_hidup = 'meninggal' THEN 'Meninggal' ELSE 'Pindah' END as status_label
        FROM penduduk p ORDER BY p.id DESC");
    $data = $stmt->fetchAll();
    echo json_encode(['data' => $data]);
    exit;
}

if ($action == 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM penduduk WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data penduduk berhasil dihapus!'];
    redirect('index.php');
}

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

// Filter params
$f_dusun = $_GET['dusun'] ?? '';
$f_rt = $_GET['rt'] ?? '';
$f_status = $_GET['status'] ?? '';

$where = [];
$params = [];
if ($f_dusun) { $where[] = "p.dusun = ?"; $params[] = $f_dusun; }
if ($f_rt) { $where[] = "p.rt = ?"; $params[] = $f_rt; }
if ($f_status) { $where[] = "p.status_hidup = ?"; $params[] = $f_status; }

$sql = "SELECT p.*, 
    CASE WHEN p.status_hidup = 'hidup' THEN 'Hidup' WHEN p.status_hidup = 'meninggal' THEN 'Meninggal' ELSE 'Pindah' END as status_label
    FROM penduduk p";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$penduduk = $stmt->fetchAll();

$dusunList = $pdo->query("SELECT DISTINCT dusun FROM penduduk WHERE dusun != '' ORDER BY dusun")->fetchAll();
$rtList = $pdo->query("SELECT DISTINCT rt FROM penduduk WHERE rt != '' ORDER BY rt")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penduduk - <?= htmlspecialchars($profil['nama_desa']) ?></title>
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
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show"><?= $_SESSION['flash']['msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>Data Penduduk</h4>
                    <p>Kelola data penduduk desa</p>
                </div>
                <a href="tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Penduduk</a>
            </div>

            <!-- Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Dusun</label>
                            <select name="dusun" class="form-select">
                                <option value="">Semua Dusun</option>
                                <?php foreach ($dusunList as $d): ?>
                                <option value="<?= htmlspecialchars($d['dusun']) ?>" <?= $f_dusun == $d['dusun'] ? 'selected' : '' ?>><?= htmlspecialchars($d['dusun']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RT</label>
                            <select name="rt" class="form-select">
                                <option value="">Semua</option>
                                <?php foreach ($rtList as $r): ?>
                                <option value="<?= htmlspecialchars($r['rt']) ?>" <?= $f_rt == $r['rt'] ? 'selected' : '' ?>><?= htmlspecialchars($r['rt']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua</option>
                                <option value="hidup" <?= $f_status == 'hidup' ? 'selected' : '' ?>>Hidup</option>
                                <option value="meninggal" <?= $f_status == 'meninggal' ? 'selected' : '' ?>>Meninggal</option>
                                <option value="pindah" <?= $f_status == 'pindah' ? 'selected' : '' ?>>Pindah</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-navy w-100"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        <div class="col-md-3">
                            <a href="index.php" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Tempat, Tgl Lahir</th>
                                    <th>JK</th>
                                    <th>Dusun</th>
                                    <th>RT/RW</th>
                                    <th>Agama</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($penduduk as $p): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($p['nik']) ?></td>
                                    <td><?= htmlspecialchars($p['nama']) ?></td>
                                    <td><?= htmlspecialchars($p['tempat_lahir']) ?>, <?= formatTanggal($p['tanggal_lahir']) ?></td>
                                    <td><?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                    <td><?= htmlspecialchars($p['dusun']) ?></td>
                                    <td><?= htmlspecialchars($p['rt']) ?>/<?= htmlspecialchars($p['rw']) ?></td>
                                    <td><?= htmlspecialchars($p['agama']) ?></td>
                                    <td>
                                        <?php if ($p['status_hidup'] == 'hidup'): ?>
                                            <span class="badge bg-success">Hidup</span>
                                        <?php elseif ($p['status_hidup'] == 'meninggal'): ?>
                                            <span class="badge bg-danger">Meninggal</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pindah</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Hapus"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
