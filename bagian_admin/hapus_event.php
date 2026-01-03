<?php
// ==================================================
// Nama File: hapus_event.php
// Deskripsi: File untuk menghapus event dari database dan sistem file
// Dibuat oleh: Maria Putri Agustina Tamba - NIM: 3312511025
// Tanggal:  07/11/2025 - 03/01/2026
// ==================================================

session_start();
require_once 'koneksi.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: landing_page.php');
    exit();
}

// AMBIL ID EVENT DARI URL
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

// HAPUS DATA DARI DATABASE DENGAN PREPARED STATEMENT
$stmtHapus = $connection->prepare("DELETE FROM events WHERE id = ?");
$stmtHapus->bind_param("i", $idEvent);

// EKSEKUSI HAPUS DAN BERI FEEDBACK
if ($stmtHapus->execute()) {
    $_SESSION['success'] = "Event berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus event!";
}

$stmtHapus->close();
header('Location: dashboard.php');
exit();
?>
