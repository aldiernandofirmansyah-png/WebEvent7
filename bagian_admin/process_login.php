<?php
// ==================================================
// Nama File: process_login.php
// Deskripsi: File untuk memproses login admin dengan validasi dan prepared statement
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

session_start();
require_once 'koneksi.php';

// Cek jika request method adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validasi input
    if (isset($_POST['username']) && isset($_POST['password'])) {
        
        $username = mysqli_real_escape_string($connection, trim($_POST['username']));
        $password = $_POST['password'];
        
        // PREPARED STATEMENT untuk mencegah SQL injection
        $stmt = mysqli_prepare($connection, "SELECT * FROM login WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Jika user ditemukan
        if ($result && mysqli_num_rows($result) > 0) {
            
            $user = mysqli_fetch_assoc($result);
            
            // CEK PASSWORD dengan dua metode:
            // 1. password_verify() untuk password yang di-hash
            // 2. Perbandingan plain text untuk backward compatibility
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                
                // Login SUKSES - set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['username'] = $username;
                $_SESSION['login_time'] = time();
                
                echo "success";
                
            } else {
                echo "error"; // Password salah
            }
            
        } else {
            echo "error"; // Username tidak ditemukan
        }
        
        mysqli_stmt_close($stmt);
        
    } else {
        echo "error"; // Input tidak lengkap
    }
    
} else {
    echo "error"; // Method bukan POST
}
?>
