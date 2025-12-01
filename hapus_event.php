<?php
session_start();
include 'koneksi.php';

// Check login
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: lading_page.php');
    exit();
}

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connection, $_GET['id']);
    
    // Get image path before deleting
    $query = mysqli_query($connection, "SELECT gambar FROM events WHERE id = '$id'");
    $event = mysqli_fetch_assoc($query);
    
    // Delete event
    $delete_query = mysqli_query($connection, "DELETE FROM events WHERE id = '$id'");
    
    if($delete_query) {
        // Delete image file if exists
        if(!empty($event['gambar']) && file_exists($event['gambar'])) {
            unlink($event['gambar']);
        }
        $_SESSION['success'] = "Event berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus event: " . mysqli_error($connection);
    }
} else {
    $_SESSION['error'] = "ID event tidak valid!";
}

header('Location: dashboard.php');
exit();
?>
