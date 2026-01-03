<?php
// ==================================================
// Nama File: event.php
// Deskripsi: Halaman untuk menampilkan daftar event kampus
// Dibuat oleh: Lusiana Hotmauli Panggabean - NIM: 3312511024
// Tanggal: 03 Januari 2026
// ==================================================

require_once 'koneksi.php';

// AMBIL SEMUA EVENT YANG STATUSNYA AKTIF
$eventsQuery = mysqli_query($connection, "SELECT * FROM events WHERE status = 'aktif' ORDER BY tanggal_mulai DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Event Kampus Polibatam</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body { padding-top: 80px; background-color: #f8fafc; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: scale(1.03); }
        .card-img-top { height: 200px; object-fit: cover; }
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
                    <li class="nav-item"><a class="nav-link active" href="event.php">Event</a></li>
                    <li class="nav-item"><a class="nav-link" href="kalender.php">Kalender</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA HALAMAN -->
    <div class="container py-5">
        <h2 class="text-center mb-5 fw-bold">Informasi Event Kampus</h2>

        <!-- FILTER PENCARIAN EVENT -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <input type="text" id="inputCari" class="form-control shadow-sm" placeholder="Cari event..." />
            </div>
            <div class="col-md-4">
                <select id="selectKategori" class="form-select shadow-sm">
                    <option value="">Semua Kategori</option>
                    <option value="Akademik">Akademik</option>
                    <option value="Non Akademik">Non Akademik</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="date" id="inputTanggal" class="form-control shadow-sm" />
            </div>
        </div>

        <!-- DAFTAR EVENT -->
        <div class="row" id="containerEvent">
            <?php if (mysqli_num_rows($eventsQuery) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($eventsQuery)): ?>
                    <?php
                    // FORMAT DATA UNTUK KEAMANAN
                    $eventId = (int)$event['id'];
                    $eventNama = htmlspecialchars($event['nama_event'] ?? '');
                    $eventNamaLower = strtolower($eventNama);
                    $eventKategori = strtolower(htmlspecialchars($event['kategori'] ?? ''));
                    $eventLokasi = htmlspecialchars($event['lokasi'] ?? '');
                    $eventGambar = htmlspecialchars($event['gambar'] ?? '');
                    ?>
                    
                    <!-- KARTU EVENT -->
                    <div class="col-md-4 mb-4 event-card" 
                         data-nama="<?= $eventNamaLower ?>" 
                         data-tanggal="<?= $event['tanggal_mulai'] ?>"
                         data-kategori="<?= $eventKategori ?>">
                        
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($eventGambar)): ?>
                                <img src="<?= $eventGambar ?>" class="card-img-top" alt="<?= $eventNama ?>" />
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= $eventNama ?></h5>
                                
                                <?php if (!empty($event['kategori'])): ?>
                                    <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($event['kategori']) ?></span>
                                <?php endif; ?>
                                
                                <!-- INFO WAKTU EVENT -->
                                <p class="text-muted mb-1">
                                    <i class="bi bi-clock"></i> 
                                    <?= date('H:i', strtotime($event['waktu_mulai'])) ?> - 
                                    <?= date('H:i', strtotime($event['waktu_selesai'])) ?>
                                </p>
                                
                                <p class="text-muted mb-1">
                                    <i class="bi bi-geo-alt"></i> <?= $eventLokasi ?>
                                </p>
                                
                                <p class="text-muted">
                                    <i class="bi bi-calendar"></i> 
                                    <?= date('d/m/Y', strtotime($event['tanggal_mulai'])) ?>
                                </p>
                                
                                <span class="badge bg-success">Aktif</span>
                                
                                <!-- TOMBOL LIHAT DETAIL -->
                                <button class="btn btn-outline-primary w-100 mt-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetail<?= $eventId ?>">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL DETAIL EVENT -->
                    <div class="modal fade" id="modalDetail<?= $eventId ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><?= $eventNama ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                
                                <div class="modal-body">
                                    <!-- KATEGORI EVENT -->
                                    <div class="mb-3">
                                        <strong>Kategori:</strong>
                                        <?php if (!empty($event['kategori'])): ?>
                                            <span class="badge bg-info ms-2"><?= htmlspecialchars($event['kategori']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted ms-2">-</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- LOKASI EVENT -->
                                    <div class="mb-3">
                                        <strong>Lokasi:</strong>
                                        <p class="mb-0"><?= $eventLokasi ?></p>
                                    </div>
                                    
                                    <!-- TANGGAL EVENT -->
                                    <div class="mb-3">
                                        <strong>Tanggal:</strong>
                                        <p class="mb-0">
                                            <?= date('d/m/Y', strtotime($event['tanggal_mulai'])) ?>
                                            <?php if ($event['tanggal_mulai'] != $event['tanggal_selesai']): ?>
                                                - <?= date('d/m/Y', strtotime($event['tanggal_selesai'])) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    
                                    <!-- WAKTU EVENT -->
                                    <div class="mb-3">
                                        <strong>Waktu:</strong>
                                        <p class="mb-0">
                                            <?= date('H:i', strtotime($event['waktu_mulai'])) ?> - 
                                            <?= date('H:i', strtotime($event['waktu_selesai'])) ?>
                                        </p>
                                    </div>
                                    
                                    <!-- STATUS EVENT -->
                                    <div class="mb-3">
                                        <strong>Status:</strong>
                                        <span class="badge bg-success ms-2">Aktif</span>
                                    </div>
                                    
                                    <!-- DESKRIPSI EVENT -->
                                    <div class="mb-3">
                                        <strong>Deskripsi:</strong>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($event['deskripsi'] ?? '')) ?></p>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- PESAN JIKA TIDAK ADA EVENT -->
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Belum ada event yang tersedia.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER HALAMAN -->
    <footer class="bg-light text-center py-3">
        <p class="mb-0">© Informasi Event Kampus Polibatam 2025</p>
    </footer>

    <!-- SCRIPT BOOTSTRAP -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT FILTER EVENT -->
    <script>
        // FUNGSI UNTUK FILTER EVENT BERDASARKAN KRITERIA PENCARIAN
        function filterEvents() {
            const cari = document.getElementById('inputCari').value.toLowerCase();
            const kategori = document.getElementById('selectKategori').value.toLowerCase();
            const tanggal = document.getElementById('inputTanggal').value;
            
            document.querySelectorAll('.event-card').forEach(kartu => {
                const show = (!cari || kartu.getAttribute('data-nama').includes(cari)) &&
                            (!kategori || kartu.getAttribute('data-kategori') === kategori) &&
                            (!tanggal || kartu.getAttribute('data-tanggal') === tanggal);
                kartu.style.display = show ? 'block' : 'none';
            });
        }

        // EVENT LISTENER UNTUK TRIGGER FILTER
        document.getElementById('inputCari').addEventListener('input', filterEvents);
        document.getElementById('selectKategori').addEventListener('change', filterEvents);
        document.getElementById('inputTanggal').addEventListener('change', filterEvents);
    </script>
</body>
</html>
