<?php
session_start();
include 'koneksi.php';

if(isset($_POST['username']) && isset($_POST['password'])) {
    
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);
    
    $query = "SELECT * FROM login WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($connection, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $username;
        
        // ✅ KIRIM RESPONSE 'success' UNTUK AJAX
        echo "success";
    } else {
        // ✅ KIRIM RESPONSE 'error' UNTUK AJAX
        echo "error";
    }
} else {
    echo "error";
}

mysqli_close($connection);
?>
