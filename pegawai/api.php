<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight OPTIONS request for CORS
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
$path_parts = explode('/', trim($path, '/'));

function send_response($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

switch ($method) {
    case 'GET':
        if (isset($path_parts[0]) && $path_parts[0] !== '' && is_numeric($path_parts[0])) {
            $id_pegawai = intval($path_parts[0]);
            // Cek apakah request untuk rekap gaji: GET /pegawai/{id}/gaji
            if (isset($path_parts[1]) && $path_parts[1] == 'gaji') {
                $bulan = $_GET['bulan'] ?? null;
                $tahun = $_GET['tahun'] ?? null;

                if (!$bulan || !$tahun) {
                    send_response(['error' => 'Parameter bulan dan tahun diperlukan'], 400);
                }
                
                // Asumsi ada tabel 'gaji' dengan struktur (id_gaji, id_pegawai, bulan, tahun, total_gaji, dll)
                // Query ini hanyalah contoh, sesuaikan dengan struktur tabel gaji Anda.
                $query = "SELECT * FROM gaji WHERE id_pegawai = ? AND bulan = ? AND tahun = ?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, 'iii', $id_pegawai, $bulan, $tahun);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $gaji = mysqli_fetch_assoc($result);

                if ($gaji) {
                    send_response($gaji);
                } else {
                    send_response(['message' => 'Data gaji tidak ditemukan'], 404);
                }
            } else {
                // GET /pegawai/{id}
                $query = "SELECT p.id_pegawai, p.nama, p.gelar, p.id_jabatan, j.nama_jabatan 
                          FROM pegawai p
                          JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                          WHERE p.id_pegawai = ?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, 'i', $id_pegawai);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $pegawai = mysqli_fetch_assoc($result);

                if ($pegawai) {
                    send_response($pegawai);
                } else {
                    send_response(['error' => 'Pegawai tidak ditemukan'], 404);
                }
            }
        } else {
            // GET /pegawai
            $query = "SELECT p.id_pegawai, p.nama, p.gelar, j.nama_jabatan 
                      FROM pegawai p
                      JOIN jabatan j ON p.id_jabatan = j.id_jabatan
                      ORDER BY p.nama ASC";
            $result = mysqli_query($koneksi, $query);
            $pegawai_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
            send_response($pegawai_list);
        }
        break;

    case 'POST':
        // POST /pegawai
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nama']) || empty($data['id_jabatan'])) {
            send_response(['error' => 'Nama dan id_jabatan tidak boleh kosong'], 400);
        }

        $nama = $data['nama'];
        $gelar = $data['gelar'] ?? null;
        $id_jabatan = $data['id_jabatan'];

        $query = "INSERT INTO pegawai (nama, gelar, id_jabatan) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $nama, $gelar, $id_jabatan);
        
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($koneksi);
            send_response(['id_pegawai' => $new_id, 'message' => 'Pegawai berhasil ditambahkan'], 201);
        } else {
            send_response(['error' => 'Gagal menambahkan pegawai: ' . mysqli_error($koneksi)], 500);
        }
        break;

    case 'PUT':
        // PUT /pegawai/{id}
        if (isset($path_parts[0]) && is_numeric($path_parts[0])) {
            $id_pegawai = intval($path_parts[0]);
            $data = json_decode(file_get_contents("php://input"), true);

            if (empty($data['nama']) || empty($data['id_jabatan'])) {
                send_response(['error' => 'Nama dan id_jabatan tidak boleh kosong'], 400);
            }

            $nama = $data['nama'];
            $gelar = $data['gelar'] ?? null;
            $id_jabatan = $data['id_jabatan'];

            $query = "UPDATE pegawai SET nama = ?, gelar = ?, id_jabatan = ? WHERE id_pegawai = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, 'ssii', $nama, $gelar, $id_jabatan, $id_pegawai);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    send_response(['message' => 'Data pegawai berhasil diperbarui']);
                } else {
                    send_response(['message' => 'Tidak ada data yang diperbarui atau pegawai tidak ditemukan'], 404);
                }
            } else {
                send_response(['error' => 'Gagal memperbarui data: ' . mysqli_error($koneksi)], 500);
            }
        } else {
            send_response(['error' => 'ID Pegawai tidak valid'], 400);
        }
        break;

    case 'DELETE':
        // DELETE /pegawai/{id}
        if (isset($path_parts[0]) && is_numeric($path_parts[0])) {
            $id_pegawai = intval($path_parts[0]);

            // Periksa apakah ada record terkait di tabel lain yang memiliki ON DELETE RESTRICT
            // Contoh: tabel gaji. Jika ada, hapus dulu record di sana atau ubah constraint.
            // Untuk contoh ini, kita asumsikan bisa langsung dihapus.

            $query = "DELETE FROM pegawai WHERE id_pegawai = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id_pegawai);

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    send_response(['message' => 'Pegawai berhasil dihapus']);
                } else {
                    send_response(['error' => 'Pegawai tidak ditemukan'], 404);
                }
            } else {
                // Error ini kemungkinan besar terjadi karena constraint ON DELETE RESTRICT
                if(mysqli_errno($koneksi) == 1451) { // Foreign key constraint fails
                    send_response(['error' => 'Gagal menghapus pegawai karena data terkait masih ada di tabel lain (misal: gaji).'], 409); // 409 Conflict
                }
                send_response(['error' => 'Gagal menghapus pegawai: ' . mysqli_error($koneksi)], 500);
            }
        } else {
            send_response(['error' => 'ID Pegawai tidak valid'], 400);
        }
        break;

    default:
        send_response(['error' => 'Metode tidak diizinkan'], 405);
        break;
}

mysqli_close($koneksi);
?>