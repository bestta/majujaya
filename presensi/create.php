<?php
include '../config/database.php';

// Ambil data pegawai untuk dropdown
$pegawai_query = "SELECT id_pegawai, nama FROM pegawai ORDER BY nama ASC";
$pegawai_result = mysqli_query($koneksi, $pegawai_query);

include '../templates/header.php';
?>

<h1 class="mb-4">Tambah Presensi Baru</h1>

<a href="index.php" class="btn btn-secondary mb-3">Kembali ke Daftar Presensi</a>

<form action="store.php" method="POST">
    <div class="mb-3">
        <label for="id_pegawai" class="form-label">Pegawai</label>
        <select class="form-select" id="id_pegawai" name="id_pegawai" required>
            <option value="" selected disabled>Pilih Pegawai</option>
            <?php
            if (mysqli_num_rows($pegawai_result) > 0) {
                while ($pegawai = mysqli_fetch_assoc($pegawai_result)) {
                    echo "<option value='" . $pegawai['id_pegawai'] . "'>" . htmlspecialchars($pegawai['nama']) . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="tanggal" class="form-label">Tanggal</label>
        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <div class="mb-3">
        <label for="status_hadir" class="form-label">Status Kehadiran</label>
        <select class="form-select" id="status_hadir" name="status_hadir" required>
            <option value="hadir">Hadir</option>
            <option value="alpa">Alpa</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="jam_masuk" class="form-label">Jam Masuk (Opsional)</label>
        <input type="time" class="form-control" id="jam_masuk" name="jam_masuk">
    </div>
    <div class="mb-3">
        <label for="jam_keluar" class="form-label">Jam Keluar (Opsional)</label>
        <input type="time" class="form-control" id="jam_keluar" name="jam_keluar">
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

<?php
mysqli_close($koneksi);
include '../templates/footer.php';
?>