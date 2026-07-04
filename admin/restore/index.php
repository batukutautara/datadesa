<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../helpers/XlsxReader.php';
checkAdmin();

$profil = getProfilDesa();
$username = $_SESSION['user_nama'];
$inisial = strtoupper(substr($username, 0, 1));

$step = 'upload';
$preview = [];
$headers = [];
$totalRows = 0;
$imported = 0;
$errors = [];
$columnMap = [];

$fieldOptions = [
    'nik' => 'NIK',
    'no_kk' => 'No. KK',
    'hubungan_keluarga' => 'Hubungan Keluarga',
    'nama' => 'Nama Lengkap',
    'tempat_lahir' => 'Tempat Lahir',
    'tanggal_lahir' => 'Tanggal Lahir',
    'jenis_kelamin' => 'Jenis Kelamin',
    'alamat' => 'Alamat',
    'rt' => 'RT',
    'rw' => 'RW',
    'dusun' => 'Dusun',
    'agama' => 'Agama',
    'status_perkawinan' => 'Status Perkawinan',
    'pekerjaan' => 'Pekerjaan',
    'kewarganegaraan' => 'Kewarganegaraan',
    '-skip-' => '[Lewati]',
];

$statusOptions = ['hidup' => 'Hidup', 'meninggal' => 'Meninggal', 'pindah' => 'Pindah'];

// Auto-detect mapping
function autoDetectMap($headers) {
    $map = [];
    $rules = [
        'nik' => ['nik', 'no.nik', 'no nik', 'nomor nik', 'no induk', 'n i k'],
        'no_kk' => ['no.kk', 'no kk', 'nomor kk', 'kk', 'no-kk', 'nokk'],
        'hubungan_keluarga' => ['hubungan keluarga', 'hubungan', 'status keluarga', 'hub kel', 'hub_kel'],
        'nama' => ['nama', 'nama lengkap', 'nama_lengkap', 'nm'],
        'tempat_lahir' => ['tempat lahir', 'tempat_lahir', 'tpt lahir', 't.t.l', 'ttl'],
        'tanggal_lahir' => ['tanggal lahir', 'tgl lahir', 'tanggal_lahir', 'tgl_lahir', 'lahir', 'birth', 'tgl_lhr'],
        'jenis_kelamin' => ['jenis kelamin', 'jns kelamin', 'jk', 'kelamin', 'jenis_kelamin'],
        'alamat' => ['alamat', 'address', 'almt'],
        'rt' => ['rt', 'r.t'],
        'rw' => ['rw', 'r.w'],
        'dusun' => ['dusun', 'dukuh', 'lingkungan', 'kampung'],
        'agama' => ['agama', 'religi', 'religion'],
        'status_perkawinan' => ['status perkawinan', 'status kawin', 'kawin', 'perkawinan', 'status_perkawinan', 'nikah'],
        'pekerjaan' => ['pekerjaan', 'kerja', 'profesi', 'job', 'pekerjaan'],
        'kewarganegaraan' => ['kewarganegaraan', 'wn', 'warga negara', 'warganegara', 'kewarganegaraan'],
    ];

    foreach ($headers as $col => $header) {
        $headerClean = strtolower(trim($header));
        $headerClean = preg_replace('/[^a-z0-9 ]/', '', $headerClean);
        $headerClean = preg_replace('/\s+/', ' ', $headerClean);

        $found = false;
        foreach ($rules as $field => $aliases) {
            foreach ($aliases as $alias) {
                $aliasClean = preg_replace('/[^a-z0-9 ]/', '', $alias);
                if ($headerClean === $aliasClean || strpos($headerClean, $aliasClean) !== false) {
                    $map[$col] = $field;
                    $found = true;
                    break 2;
                }
            }
        }
        if (!$found) {
            $map[$col] = '-skip-';
        }
    }

    return $map;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_import'])) {
        $step = 'import';
        $columnMap = $_POST['map'] ?? [];
        $statusDefault = $_POST['status_default'] ?? 'hidup';
        $batchSize = 100;
        $imported = 0;
        $errors = [];

        $reader = new XlsxReader();
        if (!$reader->load($_SESSION['import_file'])) {
            $errors[] = 'Gagal membaca file: ' . $reader->getError();
        } else {
            $rows = $reader->getRowsAsArray();
            $headers = $reader->getHeaders();
            $headerKeys = [];
            foreach ($headers as $colIdx => $h) {
                $headerKeys[$colIdx] = $h;
            }

            $pdo->beginTransaction();
            try {
                foreach ($rows as $idx => $row) {
                    $data = [];
                    foreach ($columnMap as $colIdx => $field) {
                        if ($field !== '-skip-' && isset($headerKeys[$colIdx]) && isset($row[$headerKeys[$colIdx]])) {
                            $data[$field] = trim($row[$headerKeys[$colIdx]]);
                        }
                    }

                    if (empty($data['nik'])) {
                        $errors[] = 'Baris ' . ($idx + 2) . ': NIK kosong, dilewati';
                        continue;
                    }

                    if (cekNikDuplikat($data['nik'])) {
                        $errors[] = 'Baris ' . ($idx + 2) . ': NIK ' . $data['nik'] . ' sudah terdaftar, dilewati';
                        continue;
                    }

                    $nama = $data['nama'] ?? '';
                    $tempat_lahir = $data['tempat_lahir'] ?? '';
                    $tanggal_lahir = null;
                    if (!empty($data['tanggal_lahir'])) {
                        $tgl = $data['tanggal_lahir'];
                        if (is_numeric($tgl)) {
                            $tanggal_lahir = date('Y-m-d', strtotime('1899-12-30 +' . intval($tgl) . ' days'));
                        } else {
                            $tgl = str_replace(['/', '.'], '-', $tgl);
                            $d = date_parse($tgl);
                            if ($d['error_count'] === 0 && checkdate($d['month'], $d['day'], $d['year'])) {
                                $tanggal_lahir = sprintf('%04d-%02d-%02d', $d['year'], $d['month'], $d['day']);
                            }
                        }
                    }

                    $jk = strtoupper(substr($data['jenis_kelamin'] ?? '', 0, 1));
                    if ($jk !== 'L' && $jk !== 'P') $jk = 'L';

                    $agama = $data['agama'] ?? '';
                    $agamaMap = ['islam' => 'Islam', 'kristen' => 'Kristen', 'katolik' => 'Katolik', 'hindu' => 'Hindu', 'buddha' => 'Buddha', 'konghucu' => 'Konghucu'];
                    $agamaLower = strtolower($agama);
                    if (isset($agamaMap[$agamaLower])) $agama = $agamaMap[$agamaLower];

                    $no_kk = $data['no_kk'] ?? null;
                    $hubungan = strtoupper(str_replace(' ', '_', trim($data['hubungan_keluarga'] ?? 'LAINNYA')));
                    $hubunganValid = ['KEPALA KELUARGA', 'SUAMI', 'ISTRI', 'ANAK', 'MENANTU', 'CUCU', 'ORANG TUA', 'MERTUA', 'FAMILI LAIN', 'LAINNYA'];
                    if (!in_array($hubungan, $hubunganValid)) $hubungan = 'LAINNYA';

                    if ($no_kk) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM keluarga WHERE no_kk = ?");
                        $stmt->execute([$no_kk]);
                        $kkExists = $stmt->fetchColumn() > 0;
                        if (!$kkExists) {
                            $stmt = $pdo->prepare("INSERT INTO keluarga (no_kk, alamat, rt, rw, dusun) VALUES (?,?,?,?,?)");
                            $stmt->execute([$no_kk, $data['alamat'] ?? '', $data['rt'] ?? '', $data['rw'] ?? '', $data['dusun'] ?? '']);
                        }

                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE no_kk = ?");
                        $stmt->execute([$no_kk]);
                        if ($stmt->fetchColumn() == 0) {
                            $hash = password_hash('kk' . $no_kk, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, no_kk) VALUES (?, ?, 'penduduk', ?)");
                            $stmt->execute([$no_kk, $hash, $no_kk]);
                        }
                    }

                    $stmt = $pdo->prepare("INSERT INTO penduduk (nik, no_kk, hubungan_keluarga, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, rt, rw, dusun, agama, status_perkawinan, pekerjaan, kewarganegaraan, status_hidup) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([
                        $data['nik'],
                        $no_kk,
                        $hubungan,
                        $nama,
                        $tempat_lahir,
                        $tanggal_lahir,
                        $jk,
                        $data['alamat'] ?? '',
                        $data['rt'] ?? '',
                        $data['rw'] ?? '',
                        $data['dusun'] ?? '',
                        $agama,
                        $data['status_perkawinan'] ?? '',
                        $data['pekerjaan'] ?? '',
                        $data['kewarganegaraan'] ?? 'WNI',
                        $statusDefault,
                    ]);
                    $imported++;

                    if ($imported % $batchSize === 0) {
                        $pdo->commit();
                        $pdo->beginTransaction();
                    }
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Gagal import: ' . $e->getMessage();
            }
        }

        @unlink($_SESSION['import_file']);
        unset($_SESSION['import_file']);
        $step = 'result';
    } elseif (isset($_POST['preview'])) {
        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Pilih file Excel (.xlsx) untuk diupload';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'xlsx') {
                $errors[] = 'Format file harus .xlsx';
            } else {
                $uploadDir = '../../uploads/temp/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $dest = $uploadDir . 'import_' . time() . '.xlsx';
                move_uploaded_file($file['tmp_name'], $dest);

                $reader = new XlsxReader();
                if (!$reader->load($dest)) {
                    $errors[] = 'Gagal membaca file: ' . $reader->getError();
                    @unlink($dest);
                } else {
                    $step = 'preview';
                    $_SESSION['import_file'] = $dest;
                    $headers = $reader->getHeaders();
                    $preview = array_slice($reader->getRowsAsArray(), 0, 10);
                    $totalRows = $reader->getRowCount();
                    $columnMap = autoDetectMap($headers);
                }
            }
        }
    } elseif (isset($_POST['reload_map'])) {
        $step = 'preview';
        $columnMap = $_POST['map'] ?? [];
        $reader = new XlsxReader();
        if ($reader->load($_SESSION['import_file'])) {
            $headers = $reader->getHeaders();
            $preview = array_slice($reader->getRowsAsArray(), 0, 10);
            $totalRows = $reader->getRowCount();
        }
    }
}

$statusOptionsSelect = ['hidup' => 'Hidup', 'meninggal' => 'Meninggal', 'pindah' => 'Pindah'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Data - <?= htmlspecialchars($profil['nama_desa']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .map-card { border: 1px solid #e0e4ea; border-radius: 10px; padding: 16px; margin-bottom: 12px; background: #fafbfc; }
        .map-card .col-example { font-size: 12px; color: #888; margin-top: 4px; }
        .map-card .col-header { font-weight: 600; font-size: 14px; }
        .map-badge { display: inline-block; padding: 2px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .map-badge-matched { background: #d4edda; color: #155724; }
        .map-badge-skip { background: #fff3cd; color: #856404; }
        .preview-table { font-size: 13px; }
        .preview-table th { white-space: nowrap; }
        .result-icon { font-size: 48px; }
    </style>
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

            <div class="page-header">
                <h4><i class="bi bi-cloud-upload"></i> Restore Data Penduduk</h4>
                <p>Import data penduduk lama dari file Excel (.xlsx)</p>
            </div>

            <?php if (!empty($errors) && $step !== 'import'): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <!-- Step 1: Upload -->
            <?php if ($step === 'upload'): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4" style="font-size:64px;color:var(--blue-primary);"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                    <h5>Upload File Excel</h5>
                    <p class="text-muted">Pilih file .xlsx yang berisi data penduduk untuk diimport</p>
                    <form method="POST" enctype="multipart/form-data" class="mt-4">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <input type="file" name="file" class="form-control form-control-lg mb-3" accept=".xlsx" required>
                                <button type="submit" name="preview" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-eye"></i> Lihat Preview Data
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr class="my-4" style="max-width:400px;margin:20px auto;">
                    <div class="text-start" style="max-width:500px;margin:0 auto;">
                        <h6><i class="bi bi-info-circle"></i> Panduan</h6>
                        <ul class="text-muted small">
                            <li>File harus berformat <strong>.xlsx</strong> (Excel 2007+)</li>
                            <li>Baris pertama harus berisi <strong>nama kolom</strong> (header)</li>
                            <li>Sistem akan otomatis mendeteksi mapping kolom</li>
                            <li>Data dengan NIK duplikat akan dilewati</li>
                            <li>Maksimal data yang diproses: <strong>tidak terbatas</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Step 2: Preview & Mapping -->
            <?php if ($step === 'preview'): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Ditemukan <strong><?= $totalRows ?></strong> baris data. Periksa mapping kolom di bawah sebelum import.
            </div>

            <form method="POST" id="importForm">
                <!-- Column Mapping -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-diagram-3"></i> Mapping Kolom</span>
                        <span class="badge bg-secondary"><?= count($headers) ?> kolom ditemukan</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                                <?php foreach ($headers as $colIdx => $header):
                                $example = !empty($preview[0]) ? ($preview[0][$header] ?? '') : '';
                                $mapped = $columnMap[$colIdx] ?? '-skip-';
                                $mappedLabel = $fieldOptions[$mapped] ?? '[Lewati]';
                                $isMatched = $mapped !== '-skip-';
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="map-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="col-header"><?= htmlspecialchars($header) ?></div>
                                        <span class="map-badge <?= $isMatched ? 'map-badge-matched' : 'map-badge-skip' ?>">
                                            <?= $isMatched ? 'Terpetakan' : 'Dilewati' ?>
                                        </span>
                                    </div>
                                    <?php if ($example): ?>
                                    <div class="col-example">Contoh: <?= htmlspecialchars(mb_substr($example, 0, 50)) ?></div>
                                    <?php endif; ?>
                                    <select name="map[<?= $colIdx ?>]" class="form-select form-select-sm mt-2">
                                        <?php foreach ($fieldOptions as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($mapped === $val) ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Status Default -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Status Default Penduduk</label>
                                <select name="status_default" class="form-select">
                                    <option value="hidup">Hidup</option>
                                    <option value="meninggal">Meninggal</option>
                                    <option value="pindah">Pindah</option>
                                </select>
                                <small class="text-muted">Status untuk data yang tidak memiliki kolom status</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-table"></i> Preview Data (10 baris pertama)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom preview-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <?php foreach ($headers as $h): ?>
                                        <th><?= htmlspecialchars($h) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($preview as $row): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <?php foreach ($headers as $colIdx => $h): ?>
                                        <td><?= htmlspecialchars(mb_substr($row[$h] ?? '', 0, 60)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Upload Ulang</a>
                    <button type="submit" name="confirm_import" class="btn btn-success btn-lg" onclick="return confirm('Import <?= $totalRows ?> data? Pastikan mapping kolom sudah benar.')">
                        <i class="bi bi-cloud-upload"></i> Import <?= $totalRows ?> Data
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <!-- Step 3: Result -->
            <?php if ($step === 'result'): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <?php if ($imported > 0): ?>
                    <div class="result-icon text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
                    <h4 class="text-success">Import Berhasil!</h4>
                    <p class="display-6 fw-bold text-success"><?= $imported ?></p>
                    <p class="text-muted">data berhasil diimport ke database</p>
                    <?php else: ?>
                    <div class="result-icon text-warning mb-3"><i class="bi bi-exclamation-circle-fill"></i></div>
                    <h4 class="text-warning">Tidak Ada Data Diimport</h4>
                    <p class="text-muted">Semua data mungkin sudah terdaftar atau terjadi error</p>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                    <div class="mt-4 text-start" style="max-width:600px;margin:0 auto;">
                        <div class="alert alert-warning">
                            <strong><i class="bi bi-exclamation-triangle"></i> Catatan (<?= count($errors) ?>):</strong>
                            <ul class="mb-0 small"><?php $i=0; foreach ($errors as $e): if(++$i>20) break; ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; if(count($errors)>20): ?><li>...dan <?= count($errors)-20 ?> lainnya</li><?php endif; ?></ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Import Lagi</a>
                        <a href="../penduduk/index.php" class="btn btn-outline-secondary"><i class="bi bi-people"></i> Lihat Data Penduduk</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>
