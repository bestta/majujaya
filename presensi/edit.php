<?php
include '../config/database.php';

$id = (int)$_GET['id'];

// Ambil data presensi yang akan diedit
$presensi_query = "SELECT * FROM presensi WHERE id_presensi = $id";
$presensi_result = mysqli_query($koneksi, $presensi_query);
$presensi = mysqli_fetch_assoc($presensi_result);

if (!$presensi) {
    echo "Data presensi tidak ditemukan.";
    exit;
}

// Ambil data pegawai untuk dropdown
$pegawai_query = "SELECT id_pegawai, nama FROM pegawai ORDER BY nama ASC";
$pegawai_result = mysqli_query($koneksi, $pegawai_query);

include '../templates/header.php';
?>

<h1 class="mb-4">Edit Data Presensi</h1>

<a href="index.php" class="btn btn-secondary mb-3">Kembali ke Daftar Presensi</a>

<form action="update.php" method="POST">
    <input type="hidden" name="id_presensi" value="<?php echo $presensi['id_presensi']; ?>">
    <div class="mb-3">
        <label for="id_pegawai" class="form-label">Pegawai</label>
        <select class="form-select" id="id_pegawai" name="id_pegawai" required>
            <option value="" disabled>Pilih Pegawai</option>
            <?php
            if (mysqli_num_rows($pegawai_result) > 0) {
                while ($pegawai = mysqli_fetch_assoc($pegawai_result)) {
                    $selected = ($pegawai['id_pegawai'] == $presensi['id_pegawai']) ? 'selected' : '';
                    echo "<option value='" . $pegawai['id_pegawai'] . "' $selected>" . htmlspecialchars($pegawai['nama']) . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="tanggal" class="form-label">Tanggal</label>
        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo $presensi['tanggal']; ?>" required>
    </div>
    <div class="mb-3">
        <label for="status_hadir" class="form-label">Status Kehadiran</label>
        <select class="form-select" id="status_hadir" name="status_hadir" required>
            <option value="hadir" <?php echo ($presensi['status_hadir'] == 'hadir') ? 'selected' : ''; ?>>Hadir</option>
            <option value="alpa" <?php echo ($presensi['status_hadir'] == 'alpa') ? 'selected' : ''; ?>>Alpa</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="jam_masuk" class="form-label">Jam Masuk (Opsional)</label>
        <input type="time" class="form-control" id="jam_masuk" name="jam_masuk" value="<?php echo $presensi['jam_masuk']; ?>">
    </div>
    <div class="mb-3">
        <label for="jam_keluar" class="form-label">Jam Keluar (Opsional)</label>
        <input type="time" class="form-control" id="jam_keluar" name="jam_keluar" value="<?php echo $presensi['jam_keluar']; ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>

<?php
mysqli_close($koneksi);
include '../templates/footer.php';
?>