<?php
include '../config/database.php';

// Check for status messages from other pages
$status = $_GET['status'] ?? '';

$query = "SELECT 
            p.id_presensi, 
            pg.nama AS nama_pegawai, 
            p.tanggal, 
            p.status_hadir, 
            p.jam_masuk, 
            p.jam_keluar, 
            p.terlambat_menit, 
            p.lembur_menit 
          FROM presensi p
          JOIN pegawai pg ON p.id_pegawai = pg.id_pegawai
          ORDER BY p.tanggal DESC, pg.nama ASC";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}

include '../templates/header.php';
?>

<h1 class="mb-4">Manajemen Presensi</h1>

<?php if ($status === 'success_create'): ?>
    <div class="alert alert-success">Data presensi berhasil ditambahkan.</div>
<?php elseif ($status === 'success_update'): ?>
    <div class="alert alert-success">Data presensi berhasil diperbarui.</div>
<?php elseif ($status === 'success_delete'): ?>
    <div class="alert alert-success">Data presensi berhasil dihapus.</div>
<?php elseif ($status === 'error_duplicate'): ?>
    <div class="alert alert-danger">Error: Data presensi untuk pegawai tersebut pada tanggal yang sama sudah ada.</div>
<?php endif; ?>

<a href="create.php" class="btn btn-primary mb-3">Tambah Presensi</a>
<a href="export_gaji.php?bulan=01&tahun=2025" class="btn btn-success">Export Excel</a>


<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Nama Pegawai</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Terlambat (menit)</th>
            <th>Lembur (menit)</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0) : ?>
            <?php $no = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pegawai']); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo ucfirst($row['status_hadir']); ?></td>
                    <td><?php echo $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                    <td><?php echo $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>
                    <td><?php echo $row['terlambat_menit']; ?></td>
                    <td><?php echo $row['lembur_menit']; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id_presensi']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete.php?id=<?php echo $row['id_presensi']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="9" class="text-center">Tidak ada data presensi.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
mysqli_close($koneksi);
include '../templates/footer.php';
?>