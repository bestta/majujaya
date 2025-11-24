<?php
include '../config/database.php';

// Cek jika form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_jabatan = $_POST['nama_jabatan'];
    $gaji_pokok = $_POST['gaji_pokok'];
 
    // Gunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($koneksi, "INSERT INTO jabatan (nama_jabatan, gaji_pokok) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "sd", $nama_jabatan, $gaji_pokok);
 
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, redirect ke halaman utama
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
}

include '../templates/header.php';
?>

<h1 class="mb-4">Tambah Jabatan</h1>

<form action="tambah.php" method="post">
    <div class="mb-3">
        <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
        <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" required>
    </div>
    <div class="mb-3">
        <label for="gaji_pokok" class="form-label">Gaji Pokok</label>
        <input type="number" step="0.01" class="form-control" id="gaji_pokok" name="gaji_pokok" placeholder="Contoh: 5000000.00" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
</form>

<?php include '../templates/footer.php'; ?>
