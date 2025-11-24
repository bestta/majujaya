<?php
include '../config/database.php';

$id = (int)$_GET['id'];

// Ambil data pegawai yang akan diedit
$pegawai_query = "SELECT * FROM pegawai WHERE id_pegawai = $id";
$pegawai_result = mysqli_query($koneksi, $pegawai_query);
$pegawai = mysqli_fetch_assoc($pegawai_result);

if (!$pegawai) {
    echo "Pegawai tidak ditemukan.";
    exit;
}

// Ambil data jabatan untuk dropdown
$jabatan_query = "SELECT id_jabatan, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC";
$jabatan_result = mysqli_query($koneksi, $jabatan_query);
?>

<?php include '../templates/header.php'; ?>

<h1 class="mb-4">Edit Data Pegawai</h1>

<a href="index.php" class="btn btn-secondary mb-3">Kembali ke Daftar Pegawai</a>

<form action="update.php" method="POST">
    <input type="hidden" name="id_pegawai" value="<?php echo $pegawai['id_pegawai']; ?>">
    <div class="mb-3">
        <label for="nama" class="form-label">Nama Pegawai</label>
        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($pegawai['nama']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="gelar" class="form-label">Gelar (Opsional)</label>
        <input type="text" class="form-control" id="gelar" name="gelar" value="<?php echo htmlspecialchars($pegawai['gelar']); ?>">
    </div>
    <div class="mb-3">
        <label for="id_jabatan" class="form-label">Jabatan</label>
        <select class="form-select" id="id_jabatan" name="id_jabatan" required>
            <option value="" disabled>Pilih Jabatan</option>
            <?php
            if (mysqli_num_rows($jabatan_result) > 0) {
                while ($jabatan = mysqli_fetch_assoc($jabatan_result)) {
                    $selected = ($jabatan['id_jabatan'] == $pegawai['id_jabatan']) ? 'selected' : '';
                    echo "<option value='" . $jabatan['id_jabatan'] . "' $selected>" . htmlspecialchars($jabatan['nama_jabatan']) . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>

<?php
mysqli_close($koneksi);
include '../templates/footer.php';
?>