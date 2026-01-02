<?php
// ==================================================
// Nama File: koneksi.php
// Deskripsi: File untuk koneksi ke database event kampus
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 07/11/2025 - 02/01/2026
// ==================================================

// Konfigurasi server database
$servername = "localhost"; // Host database (localhost untuk development)
$username   = "root";      // Username database
$password   = "";          // Password database (kosong untuk XAMPP/Laragon default)
$dbname     = "event_kampus"; // Nama database yang digunakan

// Membuat koneksi ke database
$connection = mysqli_connect($servername, $username, $password, $dbname);

// Cek apakah koneksi berhasil
if (!$connection) {  
    // Jika koneksi gagal, tampilkan pesan error dan hentikan eksekusi
    die("Database connection failed. Please try again later.");
}

// Set karakter encoding ke UTF-8 untuk menghindari masalah karakter
mysqli_set_charset($connection, "utf8");

?>
