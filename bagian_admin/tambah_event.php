<?php
// ==================================================
// Nama File: tambah_event.php
// Deskripsi: File untuk memproses penambahan event baru ke database
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

session_start();
require_once 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

// PROSES DATA INPUT
$dataInput = array_map('trim', $_POST);

// VALIDASI DATA
if (empty($dataInput['nama_event']) || $dataInput['tanggal_selesai'] < $dataInput['tanggal_mulai']) {
    $_SESSION['error'] = "Data tidak valid!";
    header('Location: dashboard.php');
    exit();
}

// UPLOAD GAMBAR
$pathGambar = '';
if ($_FILES['gambar']['error'] == 0) {
    $ekstensiFile = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    if (in_array($ekstensiFile, ['jpg','jpeg','png','gif']) && $_FILES['gambar']['size'] <= 2097152) {
        $namaFileBaru = 'event_' . time() . '.' . $ekstensiFile;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $namaFileBaru);
        $pathGambar = 'uploads/' . $namaFileBaru;
    }
}

// SIMPAN KE DATABASE (PREPARED STATEMENT)
$stmtInsert = $connection->prepare("INSERT INTO events (nama_event, kategori, gambar, lokasi, status, tanggal_mulai, tanggal_selesai, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmtInsert->bind_param("ssssssss", 
    $dataInput['nama_event'], 
    $dataInput['kategori'], 
    $pathGambar, 
    $dataInput['lokasi'], 
    $dataInput['status'], 
    $dataInput['tanggal_mulai'], 
    $dataInput['tanggal_selesai'], 
    $dataInput['deskripsi']
);

if ($stmtInsert->execute()) {
    $_SESSION['success'] = "Event berhasil ditambahkan!";
} else {
    $_SESSION['error'] = "Gagal menyimpan!";
}

$stmtInsert->close();
header('Location: dashboard.php');
exit();
?>
