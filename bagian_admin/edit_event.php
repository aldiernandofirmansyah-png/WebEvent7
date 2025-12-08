<?php
// ==================================================
// Nama File: edit_event.php  
// Deskripsi: File untuk memproses update data event yang ada
// Dibuat oleh: Adetyas fauzia - NIM: 3312511023
// Tanggal: 
// ==================================================

session_start();
require_once 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

$idEvent = (int)$_POST['id'];
$dataInput = array_map('trim', $_POST);

// VALIDASI INPUT
if (empty($dataInput['nama_event']) || empty($dataInput['tanggal_mulai'])) {
    $_SESSION['error'] = "Field wajib tidak boleh kosong!";
    header('Location: dashboard.php');
    exit();
}

// CEK GAMBAR LAMA
$pathGambarLama = '';
$stmtCekGambar = $connection->prepare("SELECT gambar FROM events WHERE id = ?");
$stmtCekGambar->bind_param("i", $idEvent);
$stmtCekGambar->execute();
$stmtCekGambar->bind_result($pathGambarLama);
$stmtCekGambar->fetch();
$stmtCekGambar->close();

// UPLOAD GAMBAR BARU (JIKA ADA)
$pathGambarBaru = $pathGambarLama;
if ($_FILES['gambar']['error'] == 0) {
    $ekstensiFile = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    if (in_array($ekstensiFile, ['jpg','jpeg','png','gif']) && $_FILES['gambar']['size'] <= 2097152) {
        $namaFileBaru = 'event_' . time() . '.' . $ekstensiFile;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $namaFileBaru);
        $pathGambarBaru = 'uploads/' . $namaFileBaru;
        
        // HAPUS GAMBAR LAMA
        if ($pathGambarLama && file_exists($pathGambarLama) && strpos($pathGambarLama, 'uploads/') !== false) {
            unlink($pathGambarLama);
        }
    }
}

// UPDATE DATABASE (PREPARED STATEMENT)
$stmtUpdate = $connection->prepare("UPDATE events SET nama_event=?, kategori=?, gambar=?, lokasi=?, status=?, tanggal_mulai=?, tanggal_selesai=?, deskripsi=? WHERE id=?");
$stmtUpdate->bind_param("ssssssssi", 
    $dataInput['nama_event'], 
    $dataInput['kategori'], 
    $pathGambarBaru, 
    $dataInput['lokasi'], 
    $dataInput['status'], 
    $dataInput['tanggal_mulai'], 
    $dataInput['tanggal_selesai'], 
    $dataInput['deskripsi'],
    $idEvent
);

if ($stmtUpdate->execute()) {
    $_SESSION['success'] = "Event berhasil diupdate!";
} else {
    $_SESSION['error'] = "Gagal update!";
}

$stmtUpdate->close();
header('Location: dashboard.php');
exit();
?>
