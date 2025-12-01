<?php
// NAMA FILE: hapus_event.php
// DESKRIPSI: File untuk menangani proses penghapusan event oleh admin
// DIBUAT OLEH: [Nama Kamu] - NIM: [NIM Kamu]
// TANGGAL: [Tanggal Pembuatan]

session_start();
require_once 'koneksi.php';

// Validasi session admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: landing_page.php');
    exit();
}

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $_SESSION['error'] = "Metode request tidak valid!";
    header('Location: dashboard.php');
    exit();
}

// Validasi ID event
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID event tidak valid!";
    header('Location: dashboard.php');
    exit();
}

$eventId = (int)$_GET['id'];

// Validasi referer (opsional untuk keamanan tambahan)
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'dashboard.php') === false) {
    $_SESSION['error'] = "Akses tidak valid!";
    header('Location: dashboard.php');
    exit();
}

// Ambil data gambar untuk dihapus
$imageQuery = mysqli_query($connection, "SELECT gambar FROM events WHERE id = $eventId");
if ($imageRow = mysqli_fetch_assoc($imageQuery)) {
    $imagePath = $imageRow['gambar'];
    
    // Hapus file gambar jika ada dan bukan string kosong
    if (!empty($imagePath) && file_exists($imagePath) && strpos($imagePath, 'uploads/') !== false) {
        unlink($imagePath);
    }
}

// Hapus dari database dengan prepared statement
$query = "DELETE FROM events WHERE id = ?";
$stmt = mysqli_prepare($connection, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $eventId);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Event berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus event: " . mysqli_error($connection);
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Error preparing statement!";
}

// Redirect ke dashboard
header('Location: dashboard.php');
exit();
?>
