<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pegawai = (int)$_POST['id_pegawai'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $gelar = mysqli_real_escape_string($conn, $_POST['gelar']);
    $id_jabatan = (int)$_POST['id_jabatan'];

    $query = "UPDATE pegawai SET nama='$nama', gelar='$gelar', id_jabatan=$id_jabatan WHERE id_pegawai=$id_pegawai";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php?status=success_update");
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>