<?php
// NAMA FILE: kalender.php
// DESKRIPSI: Halaman kalender event untuk melihat jadwal event berdasarkan bulan dan tahun
// DIBUAT OLEH: [Nama Kamu] - NIM: [NIM Kamu]
// TANGGAL: [Tanggal Pembuatan]

require_once 'koneksi.php';

// Set timezone ke Indonesia
date_default_timezone_set('Asia/Jakarta');

// Ambil bulan dan tahun saat ini, atau dari parameter URL
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Validasi bulan dan tahun
$bulan = max(1, min(12, $bulan));
$tahun = max(2020, min(2100, $tahun));

// Navigasi bulan
$bulanSebelumnya = $bulan - 1;
$tahunSebelumnya = $tahun;

if ($bulanSebelumnya < 1) {
    $bulanSebelumnya = 12;
    $tahunSebelumnya = $tahun - 1;
}

$bulanSelanjutnya = $bulan + 1;
$tahunSelanjutnya = $tahun;

if ($bulanSelanjutnya > 12) {
    $bulanSelanjutnya = 1;
    $tahunSelanjutnya = $tahun + 1;
}

// Nama bulan dalam Bahasa Indonesia
$namaBulan = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 
    5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS', 
    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];

// Hari dalam Bahasa Indonesia (singkat)
$namaHari = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];

// Ambil event untuk bulan ini
$tanggalAwal = "$tahun-$bulan-01";
$tanggalAkhir = "$tahun-$bulan-" . date('t', strtotime($tanggalAwal));

// Query events dengan prepared statement untuk keamanan
$query = "SELECT * FROM events 
          WHERE status = 'aktif' 
          AND (
              (tanggal_mulai BETWEEN ? AND ?) 
              OR (tanggal_selesai BETWEEN ? AND ?)
              OR (? BETWEEN tanggal_mulai AND tanggal_selesai)
          )
          ORDER BY tanggal_mulai ASC";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "sssss", $tanggalAwal, $tanggalAkhir, $tanggalAwal, $tanggalAkhir, $tanggalAwal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$eventsBulanIni = [];
while ($event = mysqli_fetch_assoc($result)) {
    $eventsBulanIni[] = $event;
}
mysqli_stmt_close($stmt);

// Buat kalender
$hariPertama = date('w', strtotime($tanggalAwal)); // 0=Minggu, 6=Sabtu
$jumlahHari = date('t', strtotime($tanggalAwal));
$tanggalHariIni = date('j');
$bulanHariIni = date('n');
$tahunHariIni = date('Y');

// Array untuk menyimpan event per tanggal
$eventsPerTanggal = [];
foreach ($eventsBulanIni as $event) {
    $start = new DateTime($event['tanggal_mulai']);
    $end = new DateTime($event['tanggal_selesai']);
    $end->modify('+1 day'); // Include end date
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach ($period as $date) {
        if ($date->format('n') == $bulan && $date->format('Y') == $tahun) {
            $tanggal = (int)$date->format('j');
            $eventsPerTanggal[$tanggal][] = $event;
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
        /* Body Styling */
        body {
            background-color: #f8fafc;
            padding-top: 80px;
        }
        
        /* Navbar Brand */
        .navbar-brand img {
            width: 180px;
            height: auto;
        }
        
        /* Calendar Table */
        .calendar-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
            position: relative;
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
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 1px;
        }
        
        .event-dot.akademik {
            background: #28a745;
        }
        
        .event-dot.non-akademik {
            background: #dc3545;
        }
        
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
    </style>
</head>
<body>

    <!-- NAVBAR SECTION -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logoo.png" alt="Logo Polibatam" />
            </a>

            <button class="navbar-toggler" 
                    type="button" 
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" 
                    aria-controls="navbarNav"
                    aria-expanded="false" 
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="landing_page.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="event.php">Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kalender.php">Kalender</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT SECTION -->
    <div class="container py-5">
        <h2 class="text-center mb-4 fw-bold">KALENDER EVENT KAMPUS</h2>

        <!-- CALENDAR SECTION -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="calendar-table">
                    
                    <!-- MONTH NAVIGATION -->
                    <div class="calendar-nav">
                        <div class="row align-items-center">
                            <div class="col text-center">
                                <a href="kalender.php?bulan=<?= $bulanSebelumnya ?>&tahun=<?= $tahunSebelumnya ?>" 
                                   class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </div>
                            
                            <div class="col-6 text-center">
                                <h4 class="mb-0 fw-bold text-white">
                                    <?= $namaBulan[$bulan] ?> <?= $tahun ?>
                                </h4>
                            </div>
                            
                            <div class="col text-center">
                                <a href="kalender.php?bulan=<?= $bulanSelanjutnya ?>&tahun=<?= $tahunSelanjutnya ?>" 
                                   class="btn btn-sm btn-light">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CALENDAR TABLE -->
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <?php foreach ($namaHari as $hari): ?>
                                    <th><?= $hari ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tanggal = 1;
                            
                            for ($minggu = 0; $minggu < 6; $minggu++):
                                if ($tanggal > $jumlahHari) break;
                            ?>
                                <tr>
                                    <?php for ($hari = 0; $hari < 7; $hari++): 
                                        $isOtherMonth = ($minggu == 0 && $hari < $hariPertama) || $tanggal > $jumlahHari;
                                        $isToday = !$isOtherMonth && $tanggal == $tanggalHariIni && $bulan == $bulanHariIni && $tahun == $tahunHariIni;
                                        $hasEvent = !$isOtherMonth && isset($eventsPerTanggal[$tanggal]);
                                        
                                        $cellClass = '';
                                        if ($isOtherMonth) $cellClass = 'other-month';
                                        if ($isToday) $cellClass = 'today';
                                        if ($hasEvent) $cellClass = 'has-event';
                                    ?>
                                        <td class="<?= $cellClass ?>">
                                            <?php if (!$isOtherMonth): ?>
                                                <div class="day-number"><?= $tanggal ?></div>
                                                
                                                <?php if ($hasEvent): ?>
                                                    <div>
                                                        <?php 
                                                        $eventCategories = [];
                                                        foreach ($eventsPerTanggal[$tanggal] as $event) {
                                                            $categoryKey = strtolower(str_replace(' ', '-', $event['kategori']));
                                                            $eventCategories[$categoryKey] = true;
                                                        }
                                                        
                                                        foreach ($eventCategories as $category => $val): 
                                                        ?>
                                                            <span class="event-dot <?= $category ?>"></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="event-count">
                                                        <?= count($eventsPerTanggal[$tanggal]) ?> event
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php $tanggal++; ?>
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

        <!-- EVENTS LIST SECTION -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            Daftar Event <?= $namaBulan[$bulan] ?> <?= $tahun ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <?php if (count($eventsBulanIni) > 0): ?>
                            <?php foreach ($eventsBulanIni as $event): ?>
                                <div class="card mb-2">
                                    <div class="card-body py-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <strong>
                                                    <?= date('d M', strtotime($event['tanggal_mulai'])) ?>
                                                </strong>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6 class="mb-1">
                                                    <?= htmlspecialchars($event['nama_event']) ?>
                                                </h6>
                                                <p class="text-muted mb-0 small">
                                                    <i class="bi bi-geo-alt"></i> 
                                                    <?= htmlspecialchars($event['lokasi']) ?>
                                                    • 
                                                    <span class="badge bg-info">
                                                        <?= htmlspecialchars($event['kategori']) ?>
                                                    </span>
                                                </p>
                                            </div>
                                            
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-success">Aktif</span>
                                                <button class="btn btn-sm btn-outline-primary ms-2" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modal<?= $event['id'] ?>">
                                                    Detail
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL DETAIL EVENT -->
                                <div class="modal fade" 
                                     id="modal<?= $event['id'] ?>" 
                                     tabindex="-1" 
                                     aria-labelledby="modalLabel<?= $event['id'] ?>"
                                     aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalLabel<?= $event['id'] ?>">
                                                    <?= htmlspecialchars($event['nama_event']) ?>
                                                </h5>
                                                <button type="button" 
                                                        class="btn-close" 
                                                        data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <p>
                                                    <strong>Kategori:</strong> 
                                                    <span class="badge bg-info">
                                                        <?= htmlspecialchars($event['kategori']) ?>
                                                    </span>
                                                </p>
                                                
                                                <p>
                                                    <strong>Lokasi:</strong> 
                                                    <?= htmlspecialchars($event['lokasi']) ?>
                                                </p>
                                                
                                                <p>
                                                    <strong>Tanggal:</strong> 
                                                    <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?> 
                                                    - 
                                                    <?= date('d M Y', strtotime($event['tanggal_selesai'])) ?>
                                                </p>
                                                
                                                <p>
                                                    <strong>Deskripsi:</strong><br>
                                                    <?= nl2br(htmlspecialchars($event['deskripsi'])) ?>
                                                </p>
                                            </div>
                                            
                                            <div class="modal-footer">
                                                <button type="button" 
                                                        class="btn btn-secondary" 
                                                        data-bs-dismiss="modal">
                                                    Tutup
                                                </button>
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

    <!-- FOOTER SECTION -->
    <footer class="bg-light text-center py-3 mt-5">
        <p class="mb-0">© Informasi Event Kampus Polibatam 2025</p>
    </footer>

    <!-- External JavaScript -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
