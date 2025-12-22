<?php
// ==================================================
// Nama File: edit_event.php  
// Deskripsi: File untuk memproses update data event yang ada
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

session_start();
require_once 'koneksi.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

// AMBIL ID EVENT DAN DATA INPUT
$idEvent = (int)$_POST['id'];
$dataInput = array_map('trim', $_POST);

// VALIDASI INPUT WAJIB
if (empty($dataInput['nama_event']) || empty($dataInput['tanggal_mulai'])) {
    $_SESSION['error'] = "Field wajib tidak boleh kosong!";
    header('Location: dashboard.php');
    exit();
}

// AMBIL PATH GAMBAR LAMA DARI DATABASE
$pathGambarLama = '';
$stmtCekGambar = $connection->prepare("SELECT gambar FROM events WHERE id = ?");
$stmtCekGambar->bind_param("i", $idEvent);
$stmtCekGambar->execute();
$stmtCekGambar->bind_result($pathGambarLama);
$stmtCekGambar->fetch();
$stmtCekGambar->close();

// PROSES UPLOAD GAMBAR BARU (JIKA ADA)
$pathGambarBaru = $pathGambarLama;
if ($_FILES['gambar']['error'] == 0) {
    $ekstensiFile = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $formatValid = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($ekstensiFile, $formatValid) && $_FILES['gambar']['size'] <= 2097152) {
        $namaFileBaru = 'event_' . time() . '.' . $ekstensiFile;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $namaFileBaru);
        $pathGambarBaru = 'uploads/' . $namaFileBaru;
        
        // HAPUS GAMBAR LAMA JIKA ADA
        if ($pathGambarLama && file_exists($pathGambarLama) && strpos($pathGambarLama, 'uploads/') !== false) {
            unlink($pathGambarLama);
        }
    }
}

// UPDATE DATA KE DATABASE DENGAN PREPARED STATEMENT
$stmtUpdate = $connection->prepare("UPDATE events SET 
    nama_event=?, kategori=?, gambar=?, lokasi=?, status=?, 
    tanggal_mulai=?, tanggal_selesai=?, waktu_mulai=?, waktu_selesai=?, deskripsi=? 
    WHERE id=?");

$stmtUpdate->bind_param("ssssssssssi", 
    $dataInput['nama_event'], 
    $dataInput['kategori'], 
    $pathGambarBaru, 
    $dataInput['lokasi'], 
    $dataInput['status'], 
    $dataInput['tanggal_mulai'], 
    $dataInput['tanggal_selesai'],
    $dataInput['waktu_mulai'],
    $dataInput['waktu_selesai'],
    $dataInput['deskripsi'],
    $idEvent
);

// EKSEKUSI UPDATE DAN BERI FEEDBACK
if ($stmtUpdate->execute()) {
    $_SESSION['success'] = "Event berhasil diupdate!";
} else {
    $_SESSION['error'] = "Gagal update event!";
}

$stmtUpdate->close();
header('Location: dashboard.php');
exit();
?>