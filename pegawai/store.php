<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $gelar = mysqli_real_escape_string($conn, $_POST['gelar']);
    $id_jabatan = (int)$_POST['id_jabatan'];

    $query = "INSERT INTO pegawai (nama, gelar, id_jabatan) VALUES ('$nama', '$gelar', $id_jabatan)";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php?status=success_create");
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>