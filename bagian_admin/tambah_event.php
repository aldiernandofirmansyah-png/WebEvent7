<?php
// ==================================================
// Nama File: tambah_event.php
// Deskripsi: File untuk memproses penambahan event baru ke database
// Dibuat oleh: Adetyas Fauzia - NIM: 3312511023
// Tanggal: 03/01/2025
// ==================================================

session_start();
require_once 'koneksi.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

// PROSES DATA INPUT DARI FORM
$dataInput = array_map('trim', $_POST);

// VALIDASI DATA WAJIB
if (empty($dataInput['nama_event']) || empty($dataInput['tanggal_mulai'])) {
    $_SESSION['error'] = "Data tidak valid!";
    header('Location: dashboard.php');
    exit();
}

// UPLOAD GAMBAR JIKA ADA
$pathGambar = '';
if ($_FILES['gambar']['error'] == 0) {
    $ekstensiFile = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $formatValid = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($ekstensiFile, $formatValid) && $_FILES['gambar']['size'] <= 2097152) {
        $namaFileBaru = 'event_' . time() . '.' . $ekstensiFile;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $namaFileBaru);
        $pathGambar = 'uploads/' . $namaFileBaru;
    }
}

// SIMPAN KE DATABASE DENGAN PREPARED STATEMENT
$stmtInsert = $connection->prepare("INSERT INTO events 
    (nama_event, kategori, gambar, lokasi, status, tanggal_mulai, tanggal_selesai, waktu_mulai, waktu_selesai, deskripsi) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmtInsert->bind_param("ssssssssss", 
    $dataInput['nama_event'], 
    $dataInput['kategori'], 
    $pathGambar, 
    $dataInput['lokasi'], 
    $dataInput['status'], 
    $dataInput['tanggal_mulai'], 
    $dataInput['tanggal_selesai'],
    $dataInput['waktu_mulai'],
    $dataInput['waktu_selesai'],
    $dataInput['deskripsi']
);

// EKSEKUSI QUERY DAN BERI FEEDBACK
if ($stmtInsert->execute()) {
    $_SESSION['success'] = "Event berhasil ditambahkan!";
} else {
    $_SESSION['error'] = "Gagal menyimpan event!";
}

$stmtInsert->close();
header('Location: dashboard.php');
exit();
?>
