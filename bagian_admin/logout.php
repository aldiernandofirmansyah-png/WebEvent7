<?php
// ==================================================
// Nama File: logout.php
// Deskripsi: File untuk proses logout admin dengan clean session
// Dibuat oleh: Maria Putri Agustina Tamba - NIM: 3312511025
// Tanggal:  07/11/2025 - 03/01/2026
// ==================================================

session_start();

// HANCURKAN SESSION
session_destroy();

// REDIRECT KE HALAMAN LANDING PAGE
header('Location: landing_page.php');
exit();
?>
