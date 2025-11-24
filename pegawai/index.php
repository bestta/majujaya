<?php
include '../config/database.php';

// Check for status messages from other pages
$status = $_GET['status'] ?? '';

$query = "SELECT pegawai.id_pegawai, pegawai.nama, pegawai.gelar, jabatan.nama_jabatan 
          FROM pegawai 
          JOIN jabatan ON pegawai.id_jabatan = jabatan.id_jabatan 
          ORDER BY pegawai.nama ASC";
$result = mysqli_query($koneksi, $query);

// Check if query was successful
if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<?php include '../templates/header.php'; ?>

<h1 class="mb-4">Manajemen Pegawai</h1>

<?php if ($status === 'success_create'): ?>
    <div class="alert alert-success">Data pegawai berhasil ditambahkan.</div>
<?php elseif ($status === 'success_update'): ?>
    <div class="alert alert-success">Data pegawai berhasil diperbarui.</div>
<?php elseif ($status === 'success_delete'): ?>
    <div class="alert alert-success">Data pegawai berhasil dihapus.</div>
<?php endif; ?>

<a href="create.php" class="btn btn-primary mb-3">Tambah Pegawai</a>

<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Gelar</th>
            <th>Jabatan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0) : ?>
            <?php $no = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars($row['gelar']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_jabatan']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete.php?id=<?php echo $row['id_pegawai']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="5" class="text-center">Tidak ada data pegawai.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
mysqli_close($koneksi);
include '../templates/footer.php';
?>