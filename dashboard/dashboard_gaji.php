<?php
include 'db.php';
include '../config/database.php';

$query = "SELECT
    k.nama AS Pegawai,
    j.nama_jabatan AS Jabatan,
    j.gaji_pokok AS Gaji_Pokok,
    COALESCE(pb.total_potongan, 0) AS Total_Potongan,
    COALESCE(pb.total_lembur, 0) AS Total_Lembur,
    (j.gaji_pokok - COALESCE(pb.total_potongan, 0) + COALESCE(pb.total_lembur, 0)) AS Gaji_Bersih
FROM pegawai k
JOIN jabatan j ON k.id_jabatan = j.id_jabatan
LEFT JOIN (
    SELECT
        id_pegawai,
        (SUM(CASE WHEN status_hadir = 'alpa' THEN 1 ELSE 0 END) * 100000)
            + (SUM(COALESCE(terlambat_menit, 0)) * 2000) AS total_potongan,
        SUM(COALESCE(lembur_menit, 0)) * 1000 AS total_lembur
    FROM presensi
    WHERE MONTH(tanggal) = 1 AND YEAR(tanggal) = 2025
    GROUP BY id_pegawai
) pb ON k.id_pegawai = pb.id_pegawai
ORDER BY k.nama";

$result = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gaji Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4 text-center">Dashboard Gaji Pegawai</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Pegawai</th>
                            <th>Jabatan</th>
                            <th>Gaji Pokok</th>
                            <th>Total Potongan</th>
                            <th>Total Lembur</th>
                            <th>Gaji Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['Pegawai']; ?></td>
                            <td><?= $row['Jabatan']; ?></td>
                            <td>Rp <?= number_format($row['Gaji_Pokok'], 0, ',', '.'); ?></td>
                            <td class="text-danger">Rp <?= number_format($row['Total_Potongan'], 0, ',', '.'); ?></td>
                            <td class="text-success">Rp <?= number_format($row['Total_Lembur'], 0, ',', '.'); ?></td>
                            <td class="fw-bold">Rp <?= number_format($row['Gaji_Bersih'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <!-- Tombol Export -->
                <div class="container mt-4 d-flex gap-3 justify-content-center">
                    <a href="export_gaji_excel.php" class="btn btn-success">Export Excel</a>
                </div>
            </div>
        </div>
</body>
</html>
