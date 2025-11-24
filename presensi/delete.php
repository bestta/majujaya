<?php
include '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM presensi WHERE id_presensi = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success_delete");
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }

    mysqli_close($koneksi);
}
?>