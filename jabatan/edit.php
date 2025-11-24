<?php
include '../config/database.php';

// Ambil ID dari URL
$id = $_GET['id'];

// Cek jika form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_jabatan = mysqli_real_escape_string($koneksi, $_POST['nama_jabatan']);
    $gaji_pokok = mysqli_real_escape_string($koneksi, $_POST['gaji_pokok']);
    
    // Query untuk update data
    $query = "UPDATE jabatan SET nama_jabatan='$nama_jabatan', gaji_pokok='$gaji_pokok' WHERE id_jabatan=$id";

    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($koneksi);
    }
}

// Ambil data jabatan yang akan diedit
$query_get = "SELECT * FROM jabatan WHERE id_jabatan=$id";
$result_get = mysqli_query($koneksi, $query_get);
$data = mysqli_fetch_assoc($result_get);

include '../templates/header.php';
?>

<h1 class="mb-4">Edit Jabatan</h1>

<form action="edit.php?id=<?php echo $id; ?>" method="post">
    <div class="mb-3">
        <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
        <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" value="<?php echo htmlspecialchars($data['nama_jabatan']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="gaji_pokok" class="form-label">Gaji Pokok</label>
        <input type="number" step="0.01" class="form-control" id="gaji_pokok" name="gaji_pokok" value="<?php echo $data['gaji_pokok']; ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
</form>

<?php include '../templates/footer.php'; ?>
