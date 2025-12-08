<?php
// ==================================================
// Nama File: hapus_event.php
// Deskripsi: File untuk menghapus event dari database dan sistem file
// Dibuat oleh: Adetyas Fauzia - NIM: 3312511023
// Tanggal: 
// ==================================================

session_start();
require_once 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

// AMBIL ID EVENT
$idEvent = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// AMBIL PATH GAMBAR SEBELUM HAPUS
$stmtCekGambar = $connection->prepare("SELECT gambar FROM events WHERE id = ?");
$stmtCekGambar->bind_param("i", $idEvent);
$stmtCekGambar->execute();
$stmtCekGambar->bind_result($pathGambar);
$stmtCekGambar->fetch();
$stmtCekGambar->close();

// HAPUS FILE GAMBAR JIKA ADA
if ($pathGambar && file_exists($pathGambar) && strpos($pathGambar, 'uploads/') !== false) {
    unlink($pathGambar);
}

// HAPUS DARI DATABASE (PREPARED STATEMENT)
$stmtHapus = $connection->prepare("DELETE FROM events WHERE id = ?");
$stmtHapus->bind_param("i", $idEvent);

if ($stmtHapus->execute()) {
    $_SESSION['success'] = "Event berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus!";
}

$stmtHapus->close();
header('Location: dashboard.php');
exit();
?>
