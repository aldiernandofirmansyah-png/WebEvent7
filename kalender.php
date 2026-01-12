<?php
// ==================================================
// Nama File: kalender.php
// Deskripsi: Halaman kalender event kampus
// Dibuat oleh: Lusiana Hotmauli Panggabean - NIM: 3312511024
// Tanggal: 03-01-2026
// ==================================================

require_once 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// AMBIL BULAN DAN TAHUN DARI URL
$bulan = isset($_GET['bulan']) ? max(1, min(12, (int)$_GET['bulan'])) : date('n');
$tahun = isset($_GET['tahun']) ? max(2020, min(2100, (int)$_GET['tahun'])) : date('Y');

// HITUNG BULAN SEBELUM DAN SESUDAH UNTUK NAVIGASI
$bulanPrev = $bulan - 1;
$tahunPrev = $tahun;
if ($bulanPrev < 1) {
    $bulanPrev = 12;
    $tahunPrev = $tahun - 1;
}

$bulanNext = $bulan + 1;
$tahunNext = $tahun;
if ($bulanNext > 12) {
    $bulanNext = 1;
    $tahunNext = $tahun + 1;
}

// DATA NAMA BULAN DAN HARI
$namaBulan = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 
    5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS', 
    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];
$namaHari = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];

// AMBIL EVENT BULAN INI DARI DATABASE
$tanggalAwal = "$tahun-$bulan-01";
$tanggalAkhir = date('Y-m-t', strtotime($tanggalAwal));

$stmt = mysqli_prepare($connection, 
    "SELECT * FROM events 
     WHERE status='aktif' 
     AND tanggal_mulai <= ? 
     AND tanggal_selesai >= ? 
     ORDER BY tanggal_mulai ASC");

mysqli_stmt_bind_param($stmt, "ss", $tanggalAkhir, $tanggalAwal);
mysqli_stmt_execute($stmt);
$events = mysqli_stmt_get_result($stmt);
$eventsBulanIni = mysqli_fetch_all($events, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// KELOMPOKKAN EVENT BERDASARKAN TANGGAL
$eventsPerTanggal = [];
foreach ($eventsBulanIni as $event) {
    $start = new DateTime($event['tanggal_mulai']);
    $end = new DateTime($event['tanggal_selesai']);
    $end->modify('+1 day');
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach ($period as $date) {
        if ($date->format('n') == $bulan && $date->format('Y') == $tahun) {
            $day = (int)$date->format('j');
            $eventsPerTanggal[$day][] = $event;
        }
    }
}

// DATA UNTUK KALENDER
$hariPertama = date('w', strtotime($tanggalAwal));
$jumlahHari = date('t', strtotime($tanggalAwal));
$hariIni = date('j');
$bulanIni = date('n');
$tahunIni = date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kalender Event Kampus</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body { padding-top: 80px; background-color: #f8fafc; }
        .kalender { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .kalender th { background: #007bff; color: white; padding: 15px; text-align: center; }
        .kalender td { padding: 15px; text-align: center; border: 1px solid #dee2e6; height: 80px; }
        .kalender td.bulan-lain { background: #f8f9fa; color: #adb5bd; }
        .kalender td.hari-ini { background: #e7f3ff; font-weight: bold; }
        .kalender td.ada-event { background: #fff3cd; }
        .dot-event { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin: 0 1px; }
        .dot-akademik { background: #28a745; }
        .dot-non-akademik { background: #dc3545; }
    </style>
</head>
<body>
    <!-- NAVBAR UTAMA -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><img src="logoo.png" alt="Logo Polibatam" width="180" /></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="landing_page.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="event.php">Event</a></li>
                    <li class="nav-item"><a class="nav-link active" href="kalender.php">Kalender</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container py-5">
        <h2 class="text-center mb-4 fw-bold">KALENDER EVENT KAMPUS</h2>

        <!-- KALENDER -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="kalender">
                    <!-- NAVIGASI BULAN -->
                    <div class="bg-primary text-white p-3 rounded-top">
                        <div class="row align-items-center">
                            <div class="col text-center">
                                <a href="kalender.php?bulan=<?= $bulanPrev ?>&tahun=<?= $tahunPrev ?>" class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </div>
                            <div class="col-6 text-center">
                                <h4 class="mb-0 fw-bold"><?= $namaBulan[$bulan] ?> <?= $tahun ?></h4>
                            </div>
                            <div class="col text-center">
                                <a href="kalender.php?bulan=<?= $bulanNext ?>&tahun=<?= $tahunNext ?>" class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- LEGEND KATEGORI -->
                    <div class="text-center py-2 bg-light border-bottom">
                        <small class="text-muted">
                            <span class="me-3"><span class="dot-event dot-akademik d-inline-block me-1"></span> Akademik</span>
                            <span><span class="dot-event dot-non-akademik d-inline-block me-1"></span> Non Akademik</span>
                        </small>
                    </div>
                    
                    <!-- TABEL KALENDER -->
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <?php foreach ($namaHari as $hari): ?>
                                    <th><?= $hari ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $hariKe = 1; ?>
                            <?php for ($minggu = 0; $minggu < 6; $minggu++): ?>
                                <?php if ($hariKe > $jumlahHari) break; ?>
                                <tr>
                                    <?php for ($indexHari = 0; $indexHari < 7; $indexHari++): ?>
                                        <?php
                                        $isBulanLain = ($minggu == 0 && $indexHari < $hariPertama) || $hariKe > $jumlahHari;
                                        $isHariIni = !$isBulanLain && $hariKe == $hariIni && $bulan == $bulanIni && $tahun == $tahunIni;
                                        $isAdaEvent = !$isBulanLain && isset($eventsPerTanggal[$hariKe]);
                                        
                                        $kelas = '';
                                        if ($isBulanLain) $kelas = 'bulan-lain';
                                        if ($isHariIni) $kelas = 'hari-ini';
                                        if ($isAdaEvent) $kelas = 'ada-event';
                                        ?>
                                        <td class="<?= $kelas ?>">
                                            <?php if (!$isBulanLain): ?>
                                                <div class="fw-bold"><?= $hariKe ?></div>
                                                
                                                <?php if ($isAdaEvent): ?>
                                                    <div>
                                                        <?php
                                                        $kategoriUnik = [];
                                                        foreach ($eventsPerTanggal[$hariKe] as $ev) {
                                                            $kat = strtolower($ev['kategori'] ?? '');
                                                            if ($kat && !in_array($kat, $kategoriUnik)) {
                                                                $kategoriUnik[] = $kat;
                                                            }
                                                        }
                                                        foreach ($kategoriUnik as $kat):
                                                            if ($kat == 'akademik'): ?>
                                                                <span class="dot-event dot-akademik"></span>
                                                            <?php elseif ($kat == 'non akademik'): ?>
                                                                <span class="dot-event dot-non-akademik"></span>
                                                            <?php endif;
                                                        endforeach;
                                                        ?>
                                                    </div>
                                                    <small class="text-muted"><?= count($eventsPerTanggal[$hariKe]) ?> event</small>
                                                <?php endif; ?>
                                                
                                                <?php $hariKe++; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DAFTAR EVENT BULAN INI -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Daftar Event <?= $namaBulan[$bulan] ?> <?= $tahun ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($eventsBulanIni) > 0): ?>
                            <?php foreach ($eventsBulanIni as $ev): ?>
                                <div class="card mb-2">
                                    <div class="card-body py-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <strong>
                                                    <?= date('d M', strtotime($ev['tanggal_mulai'])) ?>
                                                    <?php if ($ev['tanggal_mulai'] != $ev['tanggal_selesai']): ?>
                                                        - <?= date('d M', strtotime($ev['tanggal_selesai'])) ?>
                                                    <?php endif; ?>
                                                </strong>
                                            </div>
                                            <div class="col-md-7">
                                                <h6 class="mb-1"><?= htmlspecialchars($ev['nama_event'] ?? '') ?></h6>
                                                <p class="text-muted mb-0 small">
                                                    <i class="bi bi-clock"></i> 
                                                    <?= date('H:i', strtotime($ev['waktu_mulai'])) ?> - 
                                                    <?= date('H:i', strtotime($ev['waktu_selesai'])) ?>
                                                    <br>
                                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($ev['lokasi'] ?? '') ?>
                                                    <?php if (!empty($ev['kategori'])): ?>
                                                        <span class="badge bg-info ms-2"><?= htmlspecialchars($ev['kategori']) ?></span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <span class="badge bg-success">Aktif</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">
                                    Tidak ada event di bulan <?= $namaBulan[$bulan] ?> <?= $tahun ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER HALAMAN -->
    <footer class="bg-light text-center py-3 mt-5">
        <p class="mb-0">© Informasi Event Kampus Polibatam 2025</p>
    </footer>

    <!-- SCRIPT BOOTSTRAP -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
