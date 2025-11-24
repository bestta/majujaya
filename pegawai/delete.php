<?php
include '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $query = "DELETE FROM pegawai WHERE id_pegawai = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php?status=success_delete");
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>