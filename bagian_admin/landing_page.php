<?php
// NAMA FILE: landing_page.php
// DESKRIPSI: Halaman utama website informasi event kampus dengan form login admin
// DIBUAT OLEH: [Nama Kamu] - NIM: [NIM Kamu]
// TANGGAL: [Tanggal Pembuatan]

session_start();

// Cek jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web Informasi Event Kampus</title>
    
    <!-- External Stylesheets -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    
    <!-- Internal CSS -->
    <style>
        /* Hero Section */
        .hero {
            height: 100vh;
            background: url("latar.jpg") center/cover no-repeat;
            position: relative;
        }
        
        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: #fff;
        }
        
        /* Body Styling */
        body {
            padding-top: 80px;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logoo.png" width="200" alt="Logo Event Kampus" />
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
                        <a class="nav-link active" href="landing_page.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="event.php">Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kalender.php">Kalender</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-dark px-3" 
                           href="#" 
                           data-bs-toggle="modal" 
                           data-bs-target="#loginModal">
                            Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero d-flex align-items-center text-center">
        <div class="container hero-content">
            <h1 class="display-5 fw-bold">Selamat Datang di Informasi Event Kampus Polibatam</h1>
            <p class="mt-3 fs-5">
                Silahkan melihat lihat event yang anda tunggu tunggu di Website ini yaaaaa.
            </p>
        </div>
    </section>

    <!-- TENTANG EVENT SECTION -->
    <section id="tentang" class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Tentang Informasi Event Kampus Polibatam</h2>
            <p class="text-muted mx-auto" style="max-width: 750px;">
               Informasi Event Kampus Polibatam adalah platform digital yang dirancang untuk memberikan kemudahan bagi mahasiswa, dosen, dan civitas akademika dalam mengakses berbagai kegiatan dan perlombaan yang diselenggarakan di lingkungan Politeknik Negeri Batam.
            </p>
        </div>
    </section>

    <!-- FORM HUBUNGI KAMI -->
    <section id="hubungi" class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Hubungi Kami</h2>
                <p class="text-muted">Ada pertanyaan atau saran terkait event ya? Silakan kirim pesan Anda di bawah ini.</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form class="p-4 border rounded-3 bg-white shadow-sm">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama" 
                                   placeholder="Masukkan nama Anda" 
                                   required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   placeholder="Masukkan email Anda" 
                                   required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="pesan" class="form-label">Pesan</label>
                            <textarea class="form-control" 
                                      id="pesan" 
                                      rows="4" 
                                      placeholder="Tulis pesan Anda di sini..." 
                                      required></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-light text-center py-3">
        <p class="mb-0">© Informasi Event Kampus Polibatam 2025</p>
    </footer>

    <!-- MODAL LOGIN ADMIN -->
    <div class="modal fade" 
         id="loginModal" 
         tabindex="-1" 
         aria-labelledby="loginModalLabel" 
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="loginModalLabel">
                        <i class="bi bi-person-circle me-2"></i>LOGIN ADMIN
                    </h5>
                    <button type="button" 
                            class="btn-close btn-close-white" 
                            data-bs-dismiss="modal" 
                            aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold">Username</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Masukkan username" 
                                   required />
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan password" 
                                   required />
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>LOGIN
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
        // LOGIN FORM HANDLER - FIXED VERSION
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Loading...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            
            fetch('process_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Login gagal! Username atau password salah.');
                    this.reset();
                    
                    // Close modal after failed login
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            })
            .catch(error => {
                alert('Terjadi error: ' + error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // CONTACT FORM HANDLER
        const contactForm = document.querySelector('form');
        if (contactForm && !contactForm.id) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Terima kasih! Pesan Anda telah dikirim.');
                this.reset();
            });
        }
    </script>
</body>
</html>
