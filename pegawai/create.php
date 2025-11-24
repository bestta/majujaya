<?php
include '../config/database.php';

// Ambil data jabatan untuk dropdown
$jabatan_query = "SELECT id_jabatan, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC";
$jabatan_result = mysqli_query($koneksi, $jabatan_query);
?>

<?php include '../templates/header.php'; ?>

<h1 class="mb-4">Tambah Pegawai Baru</h1>

<a href="index.php" class="btn btn-secondary mb-3">Kembali ke Daftar Pegawai</a>

<form action="store.php" method="POST">
    <div class="mb-3">
        <label for="nama" class="form-label">Nama Pegawai</label>
        <input type="text" class="form-control" id="nama" name="nama" required>
    </div>
    <div class="mb-3">
        <label for="gelar" class="form-label">Gelar (Opsional)</label>
        <input type="text" class="form-control" id="gelar" name="gelar">
    </div>
    <div class="mb-3">
        <label for="id_jabatan" class="form-label">Jabatan</label>
        <select class="form-select" id="id_jabatan" name="id_jabatan" required>
            <option value="" selected disabled>Pilih Jabatan</option>
            <?php
            if (mysqli_num_rows($jabatan_result) > 0) {
                while ($jabatan = mysqli_fetch_assoc($jabatan_result)) {
                    echo "<option value='" . $jabatan['id_jabatan'] . "'>" . htmlspecialchars($jabatan['nama_jabatan']) . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

<?php
mysqli_close($conn);
include '../templates/footer.php';
?>