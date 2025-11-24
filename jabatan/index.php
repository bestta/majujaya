<?php
include '../config/database.php';
include '../templates/header.php';

// Query untuk mengambil semua data jabatan
$query = "SELECT * FROM jabatan ORDER BY id_jabatan ASC";
$result = mysqli_query($koneksi, $query);
?>

<h1 class="mb-4">Data Jabatan</h1>
<a href="tambah.php" class="btn btn-success mb-3">Tambah Jabatan Baru</a>

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nama Jabatan</th>
            <th>Gaji Pokok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id_jabatan']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_jabatan']); ?></td>
                    <td>Rp <?php echo number_format($row['gaji_pokok'], 2, ',', '.'); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id_jabatan']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="hapus.php?id=<?php echo $row['id_jabatan']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus data ini?');">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">Belum ada data jabatan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../templates/footer.php'; ?>
