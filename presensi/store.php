<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pegawai = (int)$_POST['id_pegawai'];
    $tanggal = $_POST['tanggal'];
    $status_hadir = $_POST['status_hadir'];
    $jam_masuk = !empty($_POST['jam_masuk']) ? $_POST['jam_masuk'] : null;
    $jam_keluar = !empty($_POST['jam_keluar']) ? $_POST['jam_keluar'] : null;

    $terlambat_menit = 0;
    $lembur_menit = 0;

    if ($status_hadir === 'hadir' && $jam_masuk) {
        $jam_masuk_normal = '09:00:00';
        $jam_keluar_normal = '17:00:00';

        $time_masuk = strtotime($jam_masuk);
        $time_masuk_normal = strtotime($jam_masuk_normal);

        if ($time_masuk > $time_masuk_normal) {
            $terlambat_menit = round(($time_masuk - $time_masuk_normal) / 60);
        }

        if ($jam_keluar) {
            $time_keluar = strtotime($jam_keluar);
            $time_keluar_normal = strtotime($jam_keluar_normal);

            if ($time_keluar > $time_keluar_normal) {
                $lembur_menit = round(($time_keluar - $time_keluar_normal) / 60);
            }
        }
    } else {
        // Jika alpa, jam masuk dan keluar di-NULL-kan
        $jam_masuk = null;
        $jam_keluar = null;
    }

    // Menggunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($koneksi, "INSERT INTO presensi (id_pegawai, tanggal, status_hadir, jam_masuk, jam_keluar, terlambat_menit, lembur_menit) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssii", $id_pegawai, $tanggal, $status_hadir, $jam_masuk, $jam_keluar, $terlambat_menit, $lembur_menit);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success_create");
    } else {
        // Cek jika error karena duplikat entri (UNIQUE KEY constraint)
        if (mysqli_errno($koneksi) == 1062) {
            header("Location: index.php?status=error_duplicate");
        } else {
            echo "Error: " . mysqli_error($koneksi);
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);
}
?>