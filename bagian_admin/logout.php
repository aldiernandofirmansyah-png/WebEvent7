<?php
// ==================================================
// Nama File: logout.php
// Deskripsi: File untuk proses logout admin dengan clean session
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal: 
// ==================================================

session_start();

// HANCURKAN SESSION
session_destroy();

// REDIRECT KE HALAMAN LANDING PAGE
header('Location: landing_page.php');
exit();
?>
