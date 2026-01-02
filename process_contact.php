<?php
// ==================================================
// Nama File: process_contact.php
// Deskripsi: Proses form hubungi kami - simpan ke file text
// Dibuat oleh: Aldi Ernando Firmansyah - NIM: 3312511026
// Tanggal:  07/11/2025 - 02/01/2026
// ==================================================

// Hanya terima request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("error");
}

// Ambil dan bersihkan data dari form
$nama  = htmlspecialchars(trim($_POST['namaLengkap'] ?? ''));
$email = htmlspecialchars(trim($_POST['emailPengguna'] ?? ''));
$pesan = htmlspecialchars(trim($_POST['pesanPengguna'] ?? ''));

// Validasi: semua field harus diisi
if (empty($nama) || empty($email) || empty($pesan)) {
    die("error_empty");
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("error_email");
}

// Format data untuk disimpan ke file
$data = "[" . date('Y-m-d H:i:s') . "] $nama ($email)\n";
$data .= "Pesan: $pesan\n";
$data .= str_repeat("-", 50) . "\n\n";

// Simpan ke file pesan_kontak.txt
$file = 'pesan_kontak.txt';
if (file_put_contents($file, $data, FILE_APPEND | LOCK_EX)) {
    echo "success";
} else {
    echo "error_save";
}
?>
