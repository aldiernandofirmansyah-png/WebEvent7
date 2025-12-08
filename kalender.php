<?php
// ==================================================
// Nama File: kalender.php
// Deskripsi: Halaman kalender event kampus dengan navigasi bulan dan daftar event
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

require_once 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// ========== AMBIL BULAN & TAHUN ==========
$bulanSekarang = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahunSekarang = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Validasi input
$bulanSekarang = max(1, min(12, $bulanSekarang));
$tahunSekarang = max(2020, min(2100, $tahunSekarang));

// ========== NAVIGASI BULAN ==========
$bulanSebelum = $bulanSekarang - 1;
$tahunSebelum = $tahunSekarang;
if ($bulanSebelum < 1) {
    $bulanSebelum = 12;
    $tahunSebelum = $tahunSekarang - 1;
}

$bulanSesudah = $bulanSekarang + 1;
$tahunSesudah = $tahunSekarang;
if ($bulanSesudah > 12) {
    $bulanSesudah = 1;
    $tahunSesudah = $tahunSekarang + 1;
}

// ========== DATA KALENDER ==========
$daftarNamaBulan = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 
    5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS', 
    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];

$daftarNamaHari = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];

// ========== AMBIL EVENT BULAN INI ==========
$tanggalMulaiBulan = "$tahunSekarang-$bulanSekarang-01";
$tanggalAkhirBulan = date('Y-m-t', strtotime($tanggalMulaiBulan));

// Query dengan prepared statement untuk keamanan
$queryEvent = "SELECT * FROM events 
          WHERE status = 'aktif' 
          AND (
              (tanggal_mulai BETWEEN ? AND ?) 
              OR (tanggal_selesai BETWEEN ? AND ?)
              OR (? BETWEEN tanggal_mulai AND tanggal_selesai)
          )
          ORDER BY tanggal_mulai ASC";

$stmtEvent = mysqli_prepare($connection, $queryEvent);
mysqli_stmt_bind_param($stmtEvent, "sssss", $tanggalMulaiBulan, $tanggalAkhirBulan, $tanggalMulaiBulan, $tanggalAkhirBulan, $tanggalMulaiBulan);
mysqli_stmt_execute($stmtEvent);
$resultEvent = mysqli_stmt_get_result($stmtEvent);

$eventsBulanIni = [];
while ($dataEvent = mysqli_fetch_assoc($resultEvent)) {
    $eventsBulanIni[] = $dataEvent;
}
mysqli_stmt_close($stmtEvent);

// ========== BUAT KALENDER ==========
$hariPertamaBulan = date('w', strtotime($tanggalMulaiBulan));
$jumlahHariBulan = date('t', strtotime($tanggalMulaiBulan));
$tanggalHariIni = date('j');
$bulanHariIni = date('n');
$tahunHariIni = date('Y');

// Event per tanggal
$eventsPerTanggal = [];
foreach ($eventsBulanIni as $event) {
    $tanggalMulaiEvent = new DateTime($event['tanggal_mulai']);
    $tanggalSelesaiEvent = new DateTime($event['tanggal_selesai']);
    $tanggalSelesaiEvent->modify('+1 day');
    
    $intervalHari = new DateInterval('P1D');
    $periodeEvent = new DatePeriod($tanggalMulaiEvent, $intervalHari, $tanggalSelesaiEvent);
    
    foreach ($periodeEvent as $tanggal) {
        if ($tanggal->format('n') == $bulanSekarang && $tanggal->format('Y') == $tahunSekarang) {
            $tanggalEvent = (int)$tanggal->format('j');
            $eventsPerTanggal[$tanggalEvent][] = $event;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kalender Event Kampus</title>
    
    <!-- External Stylesheets -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    
    <!-- Internal CSS -->
    <style>
        body {
            background-color: #f8fafc;
            padding-top: 80px;
        }
        
        .navbar-brand img {
            width: 180px;
            height: auto;
        }
        
        .calendar-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .calendar-table th {
            background: #007bff;
            color: white;
            padding: 15px 5px;
            text-align: center;
            font-weight: bold;
        }
        
        .calendar-table td {
            padding: 15px 5px;
            text-align: center;
            border: 1px solid #dee2e6;
            height: 80px;
            vertical-align: top;
        }
        
        .calendar-table td.other-month {
            background: #f8f9fa;
            color: #adb5bd;
        }
        
        .calendar-table td.today {
            background: #e7f3ff;
            font-weight: bold;
        }
        
        .calendar-table td.has-event {
            background: #fff3cd;
        }
        
        .day-number {
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        
        .event-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 1px;
        }
        
        .event-dot.akademik { background: #28a745; }
        .event-dot.non-akademik { background: #dc3545; }
        
        .event-count {
            font-size: 0.7em;
            color: #6c757d;
            margin-top: 2px;
        }
        
        .calendar-nav {
            background: #007bff;
            color: white;
            padding: 15px 0;
            border-radius: 10px 10px 0 0;
        }
        
        .legend {
            padding: 8px 0;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logoo.png" alt="Logo Polibatam" />
            </a>

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

    <!-- MAIN CONTENT -->
    <div class="container py-5">
        <h2 class="text-center mb-4 fw-bold">KALENDER EVENT KAMPUS</h2>

        <!-- KALENDER -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="calendar-table">
                    
                    <!-- NAVIGASI -->
                    <div class="calendar-nav">
                        <div class="row align-items-center">
                            <div class="col text-center">
                                <a href="kalender.php?bulan=<?= $bulanSebelum ?>&tahun=<?= $tahunSebelum ?>" class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </div>
                            
                            <div class="col-6 text-center">
                                <h4 class="mb-0 fw-bold text-white">
                                    <?= htmlspecialchars($daftarNamaBulan[$bulanSekarang]) ?> <?= htmlspecialchars($tahunSekarang) ?>
                                </h4>
                            </div>
                            
                            <div class="col text-center">
                                <a href="kalender.php?bulan=<?= $bulanSesudah ?>&tahun=<?= $tahunSesudah ?>" class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- LEGEND -->
                    <div class="legend text-center">
                        <small class="text-muted">
                            <span class="me-3">
                                <span class="event-dot akademik d-inline-block me-1"></span> Akademik
                            </span>
                            <span>
                                <span class="event-dot non-akademik d-inline-block me-1"></span> Non Akademik
                            </span>
                        </small>
                    </div>
                    
                    <!-- TABLE KALENDER -->
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <?php foreach ($daftarNamaHari as $namaHari): ?>
                                    <th><?= htmlspecialchars($namaHari) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $counterTanggal = 1;
                            for ($minggu = 0; $minggu < 6; $minggu++):
                                if ($counterTanggal > $jumlahHariBulan) break;
                            ?>
                                <tr>
                                    <?php for ($hari = 0; $hari < 7; $hari++): 
                                        $isBulanLain = ($minggu == 0 && $hari < $hariPertamaBulan) || $counterTanggal > $jumlahHariBulan;
                                        $isHariIni = !$isBulanLain && $counterTanggal == $tanggalHariIni && $bulanSekarang == $bulanHariIni && $tahunSekarang == $tahunHariIni;
                                        $isAdaEvent = !$isBulanLain && isset($eventsPerTanggal[$counterTanggal]);
                                        
                                        $kelasSel = '';
                                        if ($isBulanLain) $kelasSel = 'other-month';
                                        if ($isHariIni) $kelasSel = 'today';
                                        if ($isAdaEvent) $kelasSel = 'has-event';
                                    ?>
                                        <td class="<?= $kelasSel ?>">
                                            <?php if (!$isBulanLain): ?>
                                                <div class="day-number"><?= $counterTanggal ?></div>
                                                
                                                <?php if ($isAdaEvent): ?>
                                                    <div>
                                                        <?php 
                                                        $daftarKategori = [];
                                                        foreach ($eventsPerTanggal[$counterTanggal] as $eventHariIni) {
                                                            $kategoriEvent = strtolower(str_replace(' ', '-', htmlspecialchars($eventHariIni['kategori'] ?? '')));
                                                            if (!empty($kategoriEvent)) {
                                                                $daftarKategori[$kategoriEvent] = true;
                                                            }
                                                        }
                                                        foreach ($daftarKategori as $kategori => $nilai): 
                                                            if ($kategori == 'akademik' || $kategori == 'non-akademik'):
                                                        ?>
                                                            <span class="event-dot <?= htmlspecialchars($kategori) ?>"></span>
                                                        <?php 
                                                            endif;
                                                        endforeach; 
                                                        ?>
                                                    </div>
                                                    <div class="event-count">
                                                        <?= count($eventsPerTanggal[$counterTanggal]) ?> event
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php $counterTanggal++; ?>
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

        <!-- DAFTAR EVENT -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Daftar Event <?= htmlspecialchars($daftarNamaBulan[$bulanSekarang]) ?> <?= htmlspecialchars($tahunSekarang) ?></h5>
                    </div>
                    
                    <div class="card-body">
                        <?php if (count($eventsBulanIni) > 0): ?>
                            <?php foreach ($eventsBulanIni as $eventDetail): 
                                $namaEventDetail = htmlspecialchars($eventDetail['nama_event'] ?? '', ENT_QUOTES, 'UTF-8');
                                $lokasiEventDetail = htmlspecialchars($eventDetail['lokasi'] ?? '', ENT_QUOTES, 'UTF-8');
                                $kategoriEventDetail = htmlspecialchars($eventDetail['kategori'] ?? '', ENT_QUOTES, 'UTF-8');
                            ?>
                                <div class="card mb-2">
                                    <div class="card-body py-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <strong>
                                                    <?= date('d M', strtotime($eventDetail['tanggal_mulai'])) ?>
                                                    <?php if ($eventDetail['tanggal_mulai'] != $eventDetail['tanggal_selesai']): ?>
                                                        - <?= date('d M', strtotime($eventDetail['tanggal_selesai'])) ?>
                                                    <?php endif; ?>
                                                </strong>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <h6 class="mb-1"><?= $namaEventDetail ?></h6>
                                                <p class="text-muted mb-0 small">
                                                    <i class="bi bi-geo-alt"></i> 
                                                    <?= $lokasiEventDetail ?>
                                                    <?php if (!empty($kategoriEventDetail)): ?>
                                                        <span class="badge bg-info ms-2"><?= $kategoriEventDetail ?></span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            
                                            <div class="col-md-1 text-end">
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
                                    Tidak ada event di bulan <?= htmlspecialchars($daftarNamaBulan[$bulanSekarang]) ?> <?= htmlspecialchars($tahunSekarang) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-light text-center py-3 mt-5">
        <p class="mb-0">© Informasi Event Kampus Polibatam 2025</p>
    </footer>

    <!-- External JavaScript -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
