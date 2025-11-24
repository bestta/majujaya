<?php
include '../config/database.php';

// Cek apakah ID ada di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk menghapus data
    $query = "DELETE FROM jabatan WHERE id_jabatan=$id";

    if (mysqli_query($koneksi, $query)) {
        // Redirect kembali ke halaman utama jika berhasil
        header("Location: index.php");
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($koneksi);
    }
} else {
    // Jika tidak ada ID, redirect ke halaman utama
    header("Location: index.php");
    exit();
}
?>
