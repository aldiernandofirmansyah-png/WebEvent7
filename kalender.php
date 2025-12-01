<?php
include 'koneksi.php';

// Set timezone ke Indonesia
date_default_timezone_set('Asia/Jakarta');

// Ambil bulan dan tahun saat ini, atau dari parameter URL
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Navigasi bulan
$bulan_sebelumnya = $bulan - 1;
$tahun_sebelumnya = $tahun;
if ($bulan_sebelumnya < 1) {
    $bulan_sebelumnya = 12;
    $tahun_sebelumnya = $tahun - 1;
}

$bulan_selanjutnya = $bulan + 1;
$tahun_selanjutnya = $tahun;
if ($bulan_selanjutnya > 12) {
    $bulan_selanjutnya = 1;
    $tahun_selanjutnya = $tahun + 1;
}

// Nama bulan dalam Bahasa Indonesia
$nama_bulan = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL', 
    5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS', 
    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];

// Hari dalam Bahasa Indonesia (singkat)
$nama_hari = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];

// Ambil event untuk bulan ini
$tanggal_awal = "$tahun-$bulan-01";
$tanggal_akhir = "$tahun-$bulan-" . date('t', strtotime($tanggal_awal));

$events_query = mysqli_query($connection, 
    "SELECT * FROM events 
     WHERE status='aktif' 
     AND (
         (tanggal_mulai BETWEEN '$tanggal_awal' AND '$tanggal_akhir') 
         OR (tanggal_selesai BETWEEN '$tanggal_awal' AND '$tanggal_akhir')
         OR ('$tanggal_awal' BETWEEN tanggal_mulai AND tanggal_selesai)
     )
     ORDER BY tanggal_mulai ASC"
);

$events_bulan_ini = [];
while($event = mysqli_fetch_assoc($events_query)) {
    $events_bulan_ini[] = $event;
}

// Buat kalender
$hari_pertama = date('w', strtotime($tanggal_awal)); // 0=Minggu, 6=Sabtu
$jumlah_hari = date('t', strtotime($tanggal_awal));
$tanggal_hari_ini = date('j');
$bulan_hari_ini = date('n');
$tahun_hari_ini = date('Y');

// Array untuk menyimpan event per tanggal
$events_per_tanggal = [];
foreach($events_bulan_ini as $event) {
    $start = new DateTime($event['tanggal_mulai']);
    $end = new DateTime($event['tanggal_selesai']);
    $end->modify('+1 day'); // Include end date
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach($period as $date) {
        if($date->format('n') == $bulan && $date->format('Y') == $tahun) {
            $tanggal = (int)$date->format('j');
            $events_per_tanggal[$tanggal][] = $event;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kalender Event Kampus</title>
  
  <!-- Bootstrap -->
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  
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

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="logoo.png" alt="Logo Polibatam">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarNav" aria-controls="navbarNav"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="lading_page.php">Beranda</a>
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

  <!-- KONTEN KALENDER -->
  <div class="container py-5">
    <h2 class="text-center mb-4 fw-bold">KALENDER EVENT KAMPUS</h2>

    <!-- KALENDER -->
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <div class="calendar-table">
          <!-- HEADER BULAN -->
          <div class="calendar-nav">
            <div class="row align-items-center">
              <div class="col text-center">
                <a href="kalender.php?bulan=<?= $bulan_sebelumnya ?>&tahun=<?= $tahun_sebelumnya ?>" 
                   class="btn btn-sm btn-light">
                  <i class="bi bi-chevron-left"></i>
                </a>
              </div>
              <div class="col-6 text-center">
                <h4 class="mb-0 fw-bold text-white"><?= $nama_bulan[$bulan] ?> <?= $tahun ?></h4>
              </div>
              <div class="col text-center">
                <a href="kalender.php?bulan=<?= $bulan_selanjutnya ?>&tahun=<?= $tahun_selanjutnya ?>" 
                   class="btn btn-sm btn-light">
                  <i class="bi bi-chevron-right"></i>
                </a>
              </div>
            </div>
          </div>
          
          <!-- TABEL KALENDER -->
          <table class="table table-bordered mb-0">
            <thead>
              <tr>
                <?php foreach($nama_hari as $hari): ?>
                  <th><?= $hari ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              $tanggal = 1;
              
              for($minggu = 0; $minggu < 6; $minggu++):
                if($tanggal > $jumlah_hari) break;
              ?>
                <tr>
                  <?php for($hari = 0; $hari < 7; $hari++): 
                    $is_other_month = ($minggu == 0 && $hari < $hari_pertama) || $tanggal > $jumlah_hari;
                    $is_today = !$is_other_month && $tanggal == $tanggal_hari_ini && $bulan == $bulan_hari_ini && $tahun == $tahun_hari_ini;
                    $has_event = !$is_other_month && isset($events_per_tanggal[$tanggal]);
                    
                    $cell_class = '';
                    if($is_other_month) $cell_class = 'other-month';
                    if($is_today) $cell_class = 'today';
                    if($has_event) $cell_class = 'has-event';
                  ?>
                    <td class="<?= $cell_class ?>">
                      <?php if(!$is_other_month): ?>
                        <div class="day-number"><?= $tanggal ?></div>
                        
                        <?php if($has_event): ?>
                          <div>
                            <?php 
                            $event_categories = [];
                            foreach($events_per_tanggal[$tanggal] as $event) {
                              $event_categories[$event['kategori']] = true;
                            }
                            foreach($event_categories as $category => $val): 
                            ?>
                              <span class="event-dot <?= strtolower(str_replace(' ', '-', $category)) ?>"></span>
                            <?php endforeach; ?>
                          </div>
                          <div class="event-count">
                            <?= count($events_per_tanggal[$tanggal]) ?> event
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

    <!-- DAFTAR EVENT BULAN INI -->
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Event <?= $nama_bulan[$bulan] ?> <?= $tahun ?></h5>
          </div>
          <div class="card-body">
            <?php if(count($events_bulan_ini) > 0): ?>
              <?php foreach($events_bulan_ini as $event): ?>
                <div class="card mb-2">
                  <div class="card-body py-3">
                    <div class="row align-items-center">
                      <div class="col-md-2">
                        <strong><?= date('d M', strtotime($event['tanggal_mulai'])) ?></strong>
                      </div>
                      <div class="col-md-6">
                        <h6 class="mb-1"><?= htmlspecialchars($event['nama_event']) ?></h6>
                        <p class="text-muted mb-0 small">
                          <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['lokasi']) ?>
                          • 
                          <span class="badge bg-info"><?= $event['kategori'] ?></span>
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
                <div class="modal fade" id="modal<?= $event['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><?= htmlspecialchars($event['nama_event']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <p><strong>Kategori:</strong> <span class="badge bg-info"><?= $event['kategori'] ?></span></p>
                        <p><strong>Lokasi:</strong> <?= htmlspecialchars($event['lokasi']) ?></p>
                        <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($event['tanggal_selesai'])) ?></p>
                        <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($event['deskripsi'])) ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">Tidak ada event di bulan <?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
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

  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
