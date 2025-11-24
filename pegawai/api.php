<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

include_once '../config/database.php';

// Inisialisasi koneksi database
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(503);
    echo json_encode(["message" => "Tidak dapat terhubung ke database."]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

// Logika routing untuk menangani berbagai jenis permintaan.
// Pertama, kita periksa parameter kueri (misal: ?action=gaji&id=1)
$action = isset($_GET['action']) ? $_GET['action'] : null;
$pegawai_id_from_query = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Jika tidak ada dari query, kita coba dari path (misal: /api.php/1)
$api_index = array_search('api.php', $uri);
$pegawai_id = null;
if ($api_index !== false && isset($uri[$api_index + 1]) && is_numeric($uri[$api_index + 1])) {
    $pegawai_id = (int)$uri[$api_index + 1];
}

switch ($method) {
    case 'GET':
        if ($action === 'gaji' && $pegawai_id_from_query) {
            // Menangani GET pegawai/api.php?action=gaji&id={id}&...
            getGajiPegawai($db, $pegawai_id_from_query);
        } elseif ($pegawai_id) {
            // Menangani GET pegawai/api.php/{id}
            getPegawaiById($db, $pegawai_id);
        } else {
            // GET /pegawai
            getAllPegawai($db);
        }
        break;

    case 'POST':
        // POST /pegawai
        createPegawai($db);
        break;

    case 'PUT':
        // PUT /pegawai/{id}
        if ($pegawai_id) {
            updatePegawai($db, $pegawai_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID Pegawai dibutuhkan."]);
        }
        break;

    case 'DELETE':
        // DELETE /pegawai/{id}
        if ($pegawai_id) {
            deletePegawai($db, $pegawai_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID Pegawai dibutuhkan."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Metode tidak diizinkan."]);
        break;
}

function getAllPegawai($db)
{
    $query = "SELECT id_pegawai, nama, gelar, id_jabatan,FROM pegawai";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($pegawai);
}

function getPegawaiById($db, $id)
{
    $query = "SELECT id_pegawai, nama, gelar, id_jabatan,FROM pegawai WHERE id_pegawai = :id_pegawai";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_pegawai', $id);
    $stmt->execute();
    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pegawai) {
        echo json_encode($pegawai);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Pegawai tidak ditemukan."]);
    }
}

function createPegawai($db)
{
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->nama) && !empty($data->id_jabatan) && !empty($data->tgl_masuk)) {
        $query = "INSERT INTO pegawai (id_pegawai, nama, gelar, id_jabatan) VALUES (:id_pegawai, :nama, :gelar, :id_jabatan)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':id_pegawai', $data->id_pegawai);
        $stmt->bindParam(':nama', $data->nama);
        $stmt->bindParam(':gelar', $data->gelar);
        $stmt->bindParam(':id_jabatan', $data->id_jabatan);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Pegawai berhasil dibuat.", "id_pegawai" => $db->lastInsertId()]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal membuat pegawai."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Data tidak lengkap."]);
    }
}

function updatePegawai($db, $id)
{
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->nama) && !empty($data->id_jabatan) && !empty($data->tgl_masuk)) {
        $query = "UPDATE pegawai SET id_pegawai = :id_pegawai, nama = :nama, gelar = :gelar, id_jabatan = :id_jabatan WHERE id_pegawai = :id_pegawai";
        $stmt = $db->prepare($query);

       $stmt->bindParam(':id_pegawai', $data->id_pegawai);
        $stmt->bindParam(':nama', $data->nama);
        $stmt->bindParam(':gelar', $data->gelar);
        $stmt->bindParam(':id_jabatan', $data->id_jabatan);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Pegawai berhasil diperbarui."]);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Pegawai tidak ditemukan atau data tidak berubah."]);
            }
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal memperbarui pegawai."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Data tidak lengkap."]);
    }
}

function deletePegawai($db, $id)
{
    $query = "DELETE FROM pegawai WHERE id_pegawai = :id_pegawai";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_pegawai', $id_pegawai);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Pegawai berhasil dihapus."]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Pegawai tidak ditemukan."]);
        }
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Gagal menghapus pegawai."]);
    }
}

function getGajiPegawai($db, $id)
{
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
    $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

    // Contoh logika rekap gaji sederhana
    // Anda perlu menyesuaikan query ini dengan struktur tabel 'jabatan' dan 'presensi' Anda.
    $query = "SELECT p.nama, j.nama_jabatan, j.gaji_pokok, j.tunjangan 
              FROM pegawai p 
              JOIN jabatan j ON p.id_jabatan = j.id 
              WHERE p.id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pegawai) {
        // --- Logika Kalkulasi Gaji Dinamis (Contoh) ---
        // Di dunia nyata, Anda akan melakukan query ke tabel 'presensi' dan 'lembur'.
        // Di sini kita simulasikan untuk tujuan demonstrasi.

        // Contoh: Hitung potongan berdasarkan jumlah hari kerja di bulan tersebut.
        // Misal, potongan 50rb untuk setiap hari tidak masuk (simulasi acak).
        $jumlah_hari_kerja = cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun);
        $simulasi_absen = rand(0, 2); // Simulasi pegawai absen 0-2 hari
        $potongan_per_hari = 50000;
        $total_potongan = $simulasi_absen * $potongan_per_hari;

        // Contoh: Hitung lembur (simulasi acak).
        $simulasi_jam_lembur = rand(0, 10); // Simulasi 0-10 jam lembur
        $upah_lembur_per_jam = 25000;
        $total_lembur = $simulasi_jam_lembur * $upah_lembur_per_jam;

        $gaji_bersih = ($pegawai['gaji_pokok'] + $pegawai['tunjangan'] + $total_lembur) - $total_potongan;

        $rekap = [
            "pegawai_id" => $id,
            "nama" => $pegawai['nama'],
            "jabatan" => $pegawai['nama_jabatan'],
            "periode" => "$bulan-$tahun",
            "gaji_pokok" => (float)$pegawai['gaji_pokok'],
            "tunjangan" => (float)$pegawai['tunjangan'],
            "lembur" => $total_lembur,
            "potongan" => $total_potongan,
            "gaji_bersih" => $gaji_bersih
        ];
        echo json_encode($rekap); // Mengembalikan satu objek gaji
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Pegawai tidak ditemukan."]);
    }
}
?>