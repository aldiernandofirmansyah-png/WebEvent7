<?php
// ==================================================
// Nama File: process_login.php
// Deskripsi: File untuk memproses login admin dengan validasi sederhana
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

// Mulai session untuk menyimpan status login
session_start();

// Hubungkan ke database
require_once 'koneksi.php';

// Pastikan form dikirim dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Ambil data dari form login
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // PREPARED STATEMENT untuk keamanan (mencegah SQL injection)
    $stmt = mysqli_prepare($connection, "SELECT password FROM login WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $db_password);
    
    // Jika username ditemukan di database
    if (mysqli_stmt_fetch($stmt)) {
        
        // VERIFIKASI PASSWORD dengan dua metode:
        // 1. password_verify() untuk password yang sudah di-hash
        // 2. Perbandingan langsung untuk password plain text (backward compatibility)
        if (password_verify($password, $db_password) || $password === $db_password) {
            
            // LOGIN SUKSES - set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $username;
            
            // Kirim respon 'success' ke JavaScript
            echo "success";
            
        } else {
            // Password tidak cocok
            echo "error";
        }
        
    } else {
        // Username tidak ditemukan di database
        echo "error";
    }
    
    // Tutup prepared statement
    mysqli_stmt_close($stmt);
    
}
?>