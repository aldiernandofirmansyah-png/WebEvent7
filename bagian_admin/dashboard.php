<?php
// ==================================================
// Nama File: dashboard.php
// Deskripsi: Dashboard admin untuk mengelola event kampus dengan CRUD
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

session_start();
require_once 'koneksi.php';

// ========== CEK LOGIN ==========
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

// ========== CSRF TOKEN ==========
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ========== STATISTIK ==========
// Gunakan prepared statement untuk keamanan
$stmtTotalEvent = $connection->prepare("SELECT COUNT(*) AS total FROM events");
$stmtTotalEvent->execute();
$totalEvent = $stmtTotalEvent->get_result()->fetch_assoc()['total'];
$stmtTotalEvent->close();

$stmtEventAktif = $connection->prepare("SELECT COUNT(*) AS aktif FROM events WHERE status = 'aktif'");
$stmtEventAktif->execute();
$eventAktif = $stmtEventAktif->get_result()->fetch_assoc()['aktif'];
$stmtEventAktif->close();

$stmtEventDraft = $connection->prepare("SELECT COUNT(*) AS draft FROM events WHERE status = 'draft'");
$stmtEventDraft->execute();
$eventDraft = $stmtEventDraft->get_result()->fetch_assoc()['draft'];
$stmtEventDraft->close();

// ========== AMBIL EVENT ==========
$eventsQuery = $connection->query("SELECT * FROM events ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin - Informasi Event Kampus</title>
    
    <!-- External Stylesheets -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar text-white min-vh-100">
                <div class="text-center py-3 border-bottom">
                    <img src="logoo.png" alt="Logo Polibatam" width="160" class="img-fluid mb-2" />
                </div>
                
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Manajemen Event
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <button class="btn btn-outline-light w-75 ms-3" onclick="if(confirm('Yakin mau logout?')) window.location.href='logout.php'">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </li>
                </ul>
            </nav>

            <!-- MAIN CONTENT -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- ALERTS -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark">Selamat Datang, admin <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#modalTambahEvent">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Event
                    </button>
                </div>

                <!-- STATISTIK -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary text-center">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Total Event</h6>
                                <h3 class="fw-bold text-primary"><?= htmlspecialchars($totalEvent) ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success text-center">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Event Aktif</h6>
                                <h3 class="fw-bold text-success"><?= htmlspecialchars($eventAktif) ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-warning text-center">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Event Draft</h6>
                                <h3 class="fw-bold text-warning"><?= htmlspecialchars($eventDraft) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE EVENT -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Daftar Event Kampus</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Gambar</th>
                                        <th>Nama Event</th>
                                        <th>Kategori</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Lokasi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    <?php if ($eventsQuery && mysqli_num_rows($eventsQuery) > 0): ?>
                                        <?php $nomor = 1; while ($dataEvent = mysqli_fetch_assoc($eventsQuery)): 
                                            // Escape semua data untuk keamanan
                                            $idEvent = (int)$dataEvent['id'];
                                            $namaEvent = htmlspecialchars($dataEvent['nama_event'], ENT_QUOTES, 'UTF-8');
                                            $kategoriEvent = htmlspecialchars($dataEvent['kategori'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $lokasiEvent = htmlspecialchars($dataEvent['lokasi'], ENT_QUOTES, 'UTF-8');
                                            $gambarEvent = htmlspecialchars($dataEvent['gambar'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $deskripsiEvent = htmlspecialchars($dataEvent['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $statusEvent = $dataEvent['status'];
                                        ?>
                                            <tr>
                                                <td><?= $nomor++ ?></td>
                                                <td>
                                                    <?php if (!empty($gambarEvent)): ?>
                                                        <img src="<?= $gambarEvent ?>" 
                                                             alt="<?= $namaEvent ?>" 
                                                             class="img-fluid rounded" 
                                                             width="80" 
                                                             style="object-fit: cover; height: 60px;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                             style="width: 80px; height: 60px;">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td><?= $namaEvent ?></td>
                                                
                                                <td>
                                                    <?php if (!empty($kategoriEvent)): ?>
                                                        <span class="badge bg-info"><?= $kategoriEvent ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td><?= date('d M Y', strtotime($dataEvent['tanggal_mulai'])) ?></td>
                                                <td><?= date('d M Y', strtotime($dataEvent['tanggal_selesai'])) ?></td>
                                                <td><?= $lokasiEvent ?></td>
                                                
                                                <td>
                                                    <span class="badge bg-<?= $statusEvent == 'aktif' ? 'success' : 'warning' ?>">
                                                        <?= $statusEvent == 'aktif' ? 'Aktif' : 'Draft' ?>
                                                    </span>
                                                </td>
                                                
                                                <td>
                                                    <button class="btn btn-sm btn-warning me-1" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalEditEvent" 
                                                            onclick="setDataEdit(
                                                                <?= $idEvent ?>, 
                                                                '<?= addslashes($namaEvent) ?>', 
                                                                '<?= addslashes($lokasiEvent) ?>', 
                                                                '<?= $statusEvent ?>', 
                                                                '<?= $dataEvent['tanggal_mulai'] ?>', 
                                                                '<?= $dataEvent['tanggal_selesai'] ?>', 
                                                                `<?= addslashes($dataEvent['deskripsi']) ?>`, 
                                                                '<?= addslashes($kategoriEvent) ?>'
                                                            )">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    
                                                    <a href="hapus_event.php?id=<?= $idEvent ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Yakin hapus event <?= addslashes($namaEvent) ?>?')">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                Belum ada event yang ditambahkan.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <footer class="text-center mt-4 text-muted small">
                    <hr />
                    <p class="mb-0">© 2025 Informasi Event Kampus Polibatam</p>
                </footer>
            </main>
        </div>
    </div>

    <!-- MODAL TAMBAH EVENT -->
    <div class="modal fade" id="modalTambahEvent" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Event Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <form method="POST" action="tambah_event.php" enctype="multipart/form-data" id="formTambahEvent">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_event" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gambar Event</label>
                                <input type="file" class="form-control" name="gambar" accept="image/*">
                                <div class="form-text small">Format: JPG, PNG, GIF. Maksimal 2MB</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Non Akademik">Non Akademik</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_mulai" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lokasi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_selesai" required>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="aktif">Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Event <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="deskripsi" rows="4" placeholder="Deskripsikan event secara detail..." required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT EVENT -->
    <div class="modal fade" id="modalEditEvent" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Edit Event
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <form method="POST" action="edit_event.php" enctype="multipart/form-data" id="formEditEvent">
                        <input type="hidden" id="inputEditId" name="id">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nama Event <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputEditNamaEvent" name="nama_event" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gambar Event</label>
                                <input type="file" class="form-control" id="inputEditGambar" name="gambar" accept="image/*">
                                <div class="form-text small">Kosongkan jika tidak ingin mengubah gambar</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="selectEditKategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Non Akademik">Non Akademik</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="inputEditTanggalMulai" name="tanggal_mulai" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="inputEditLokasi" name="lokasi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="inputEditTanggalSelesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="selectEditStatus" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="aktif">Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi Event <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="textareaEditDeskripsi" name="deskripsi" rows="4" required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle me-1"></i> Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Internal JavaScript -->
    <script>
        // SET DATA EDIT FUNCTION
        function setDataEdit(id, nama, lokasi, status, tanggalMulai, tanggalSelesai, deskripsi, kategori) {
            document.getElementById('inputEditId').value = id;
            document.getElementById('inputEditNamaEvent').value = nama;
            document.getElementById('inputEditLokasi').value = lokasi;
            document.getElementById('selectEditStatus').value = status;
            document.getElementById('inputEditTanggalMulai').value = tanggalMulai;
            document.getElementById('inputEditTanggalSelesai').value = tanggalSelesai;
            document.getElementById('textareaEditDeskripsi').value = deskripsi;
            document.getElementById('selectEditKategori').value = kategori;
        }

        // VALIDASI TANGGAL
        document.addEventListener('DOMContentLoaded', function() {
            const tanggalHariIni = new Date().toISOString().split('T')[0];
            
            // Validasi form tambah
            const formTambah = document.getElementById('formTambahEvent');
            if (formTambah) {
                const inputTanggalMulai = formTambah.querySelector('input[name="tanggal_mulai"]');
                const inputTanggalSelesai = formTambah.querySelector('input[name="tanggal_selesai"]');
                
                if (inputTanggalMulai) {
                    inputTanggalMulai.min = tanggalHariIni;
                    inputTanggalMulai.addEventListener('change', function() {
                        if (inputTanggalSelesai) inputTanggalSelesai.min = this.value;
                    });
                }
            }
            
            // Validasi form edit
            const formEdit = document.getElementById('formEditEvent');
            if (formEdit) {
                const editInputTanggalMulai = formEdit.querySelector('#inputEditTanggalMulai');
                const editInputTanggalSelesai = formEdit.querySelector('#inputEditTanggalSelesai');
                
                if (editInputTanggalMulai) {
                    editInputTanggalMulai.addEventListener('change', function() {
                        if (editInputTanggalSelesai) editInputTanggalSelesai.min = this.value;
                    });
                }
            }
        });
    </script>
</body>
</html>
