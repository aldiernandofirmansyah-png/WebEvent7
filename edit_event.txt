<?php
session_start();
include 'koneksi.php';

// Validasi session dan CSRF
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: lading_page.php');
    exit();
}

if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Token CSRF tidak valid!";
    header('Location: dashboard.php');
    exit();
}

// Ambil data dari form
$nama_event = mysqli_real_escape_string($connection, $_POST['nama_event']);
$kategori = mysqli_real_escape_string($connection, $_POST['kategori']);
$lokasi = mysqli_real_escape_string($connection, $_POST['lokasi']);
$status = mysqli_real_escape_string($connection, $_POST['status']);
$tanggal_mulai = mysqli_real_escape_string($connection, $_POST['tanggal_mulai']);
$tanggal_selesai = mysqli_real_escape_string($connection, $_POST['tanggal_selesai']);
$deskripsi = mysqli_real_escape_string($connection, $_POST['deskripsi']);

// Validasi input
if(empty($nama_event) || empty($kategori) || empty($lokasi) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($deskripsi)) {
    $_SESSION['error'] = "Semua field wajib diisi!";
    header('Location: dashboard.php');
    exit();
}

// Validasi tanggal
if ($tanggal_selesai < $tanggal_mulai) {
    $_SESSION['error'] = "Tanggal selesai tidak boleh lebih awal dari tanggal mulai!";
    header('Location: dashboard.php');
    exit();
}

// Handle upload gambar
$gambar = '';
if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['gambar']['type'];
    
    if(!in_array($file_type, $allowed_types)) {
        $_SESSION['error'] = "Format file tidak didukung! Hanya JPG, PNG, dan GIF yang diizinkan.";
        header('Location: dashboard.php');
        exit();
    }
    
    if($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Ukuran file terlalu besar! Maksimal 2MB.";
        header('Location: dashboard.php');
        exit();
    }
    
    $target_dir = "uploads/";
    if(!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $filename;
    
    if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
        $gambar = $target_file;
    }
}

// Insert ke database dengan prepared statement
$query = "INSERT INTO events (nama_event, kategori, gambar, lokasi, status, tanggal_mulai, tanggal_selesai, deskripsi) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "ssssssss", $nama_event, $kategori, $gambar, $lokasi, $status, $tanggal_mulai, $tanggal_selesai, $deskripsi);

if(mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Event berhasil ditambahkan!";
} else {
    $_SESSION['error'] = "Gagal menambahkan event: " . mysqli_error($connection);
}

mysqli_stmt_close($stmt);

// Regenerate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

header('Location: dashboard.php');
exit();
?>
