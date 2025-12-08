<?php
// ==================================================
// Nama File: event.php
// Deskripsi: Halaman untuk menampilkan daftar event kampus dengan filter dan detail
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

require_once 'koneksi.php';

// Ambil events dari database
$eventsQuery = mysqli_query($connection, "SELECT * FROM events WHERE status = 'aktif' ORDER BY tanggal_mulai DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Event Kampus Polibatam</title>

    <!-- External Stylesheets -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Internal CSS -->
    <style>
        body {
            background-color: #f8fafc;
            padding-top: 80px;
        }

        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.03);
        }

        .navbar-brand img {
            width: 180px;
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        /* MODAL STYLE TETAP SAMA */
        .modal-detail-item {
            margin-bottom: 15px;
        }
        
        .modal-detail-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .modal-detail-value {
            color: #666;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .status-badge {
            font-size: 0.8em;
            padding: 4px 8px;
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
                    <li class="nav-item"><a class="nav-link active" href="event.php">Event</a></li>
                    <li class="nav-item"><a class="nav-link" href="kalender.php">Kalender</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="container py-5">
        <h2 class="text-center mb-5 fw-bold">Informasi Event Kampus</h2>

        <!-- FILTER -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <input type="text" id="inputFilterKataKunci" class="form-control shadow-sm" placeholder="Cari event..." />
            </div>
            <div class="col-md-4">
                <select id="selectFilterKategori" class="form-select shadow-sm">
                    <option value="">Semua Kategori</option>
                    <option value="akademik">Akademik</option>
                    <option value="non akademik">Non Akademik</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="date" id="inputFilterTanggal" class="form-control shadow-sm" />
            </div>
        </div>

        <!-- EVENTS LIST -->
        <div class="row" id="containerEventList">
            <?php if (mysqli_num_rows($eventsQuery) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($eventsQuery)): ?>
                    <?php
                    // Escape data untuk keamanan
                    $eventId = (int)$event['id'];
                    $eventTitle = htmlspecialchars($event['nama_event'], ENT_QUOTES, 'UTF-8');
                    $eventTitleLower = strtolower($eventTitle);
                    $eventDate = $event['tanggal_mulai'];
                    $eventCategory = !empty($event['kategori']) ? 
                        strtolower(htmlspecialchars($event['kategori'], ENT_QUOTES, 'UTF-8')) : '';
                    $eventLocation = htmlspecialchars($event['lokasi'], ENT_QUOTES, 'UTF-8');
                    $eventImage = !empty($event['gambar']) ? 
                        htmlspecialchars($event['gambar'], ENT_QUOTES, 'UTF-8') : '';
                    ?>
                    
                    <div class="col-md-4 mb-4 event-card" 
                         data-judul="<?= $eventTitleLower ?>" 
                         data-tanggal="<?= $eventDate ?>"
                         data-kategori="<?= $eventCategory ?>">
                        
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($eventImage)): ?>
                                <img src="<?= $eventImage ?>" 
                                     class="card-img-top" 
                                     alt="<?= $eventTitle ?>" />
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= $eventTitle ?></h5>
                                
                                <?php if (!empty($event['kategori'])): ?>
                                    <span class="badge bg-info text-dark mb-2">
                                        <?= htmlspecialchars($event['kategori'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                                
                                <p class="text-muted mb-1">
                                    <i class="bi bi-geo-alt"></i> <?= $eventLocation ?>
                                </p>
                                
                                <p class="text-muted">
                                    <i class="bi bi-calendar"></i> 
                                    <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?>
                                </p>
                                
                                <span class="badge bg-success status-badge">Aktif</span>
                                
                                <button class="btn btn-outline-primary w-100 mt-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modal<?= $eventId ?>">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL DETAIL -->
                    <div class="modal fade" 
                         id="modal<?= $eventId ?>" 
                         tabindex="-1" 
                         aria-labelledby="labelModal<?= $eventId ?>" 
                         aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="labelModal<?= $eventId ?>">
                                        <?= $eventTitle ?>
                                    </h5>
                                    <button type="button" 
                                            class="btn-close" 
                                            data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                
                                <div class="modal-body">
                                    <!-- Category -->
                                    <div class="modal-detail-item">
                                        <div class="modal-detail-label">Kategori</div>
                                        <div class="modal-detail-value">
                                            <?php if (!empty($event['kategori'])): ?>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($event['kategori'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Location -->
                                    <div class="modal-detail-item">
                                        <div class="modal-detail-label">Lokasi</div>
                                        <div class="modal-detail-value">
                                            <?= $eventLocation ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Start Date -->
                                    <div class="modal-detail-item">
                                        <div class="modal-detail-label">Tanggal Mulai</div>
                                        <div class="modal-detail-value">
                                            <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?>
                                        </div>
                                    </div>
                                    
                                    <!-- End Date -->
                                    <div class="modal-detail-item">
                                        <div class="modal-detail-label">Tanggal Selesai</div>
                                        <div class="modal-detail-value">
                                            <?= date('d M Y', strtotime($event['tanggal_selesai'])) ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="modal-detail-item">
                                        <div class="modal-detail-label">Status</div>
                                        <div class="modal-detail-value">
                                            <span class="badge bg-success">Aktif</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="modal-detail-item">
                                        <div class="modal-detail-label">Deskripsi</div>
                                        <div class="modal-detail-value">
                                            <?= nl2br(htmlspecialchars($event['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    </div>
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
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Belum ada event yang tersedia.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-light text-center py-3">
        <p class="mb-0">© Informasi Event Kampus Polibatam 2025</p>
    </footer>

    <!-- External JavaScript -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Internal JavaScript -->
    <script>
        // FILTER EVENTS FUNCTION
        function filterEvents() {
            const inputKataKunci = document.getElementById('inputFilterKataKunci').value.toLowerCase();
            const selectKategori = document.getElementById('selectFilterKategori').value.toLowerCase();
            const inputTanggal = document.getElementById('inputFilterTanggal').value;
            
            document.querySelectorAll('.event-card').forEach(eventCard => {
                const judulEvent = eventCard.getAttribute('data-judul');
                const kategoriEvent = eventCard.getAttribute('data-kategori');
                const tanggalEvent = eventCard.getAttribute('data-tanggal');
                
                const showEvent = (!inputKataKunci || judulEvent.includes(inputKataKunci)) &&
                                 (!selectKategori || kategoriEvent === selectKategori) &&
                                 (!inputTanggal || tanggalEvent === inputTanggal);
                
                eventCard.style.display = showEvent ? 'block' : 'none';
            });
        }

        // EVENT LISTENERS UNTUK FILTER
        document.getElementById('inputFilterKataKunci').addEventListener('input', filterEvents);
        document.getElementById('selectFilterKategori').addEventListener('change', filterEvents);
        document.getElementById('inputFilterTanggal').addEventListener('change', filterEvents);
    </script>

</body>
</html>
