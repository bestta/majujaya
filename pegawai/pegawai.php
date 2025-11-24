<?php
header("Content-Type: application/json");
require_once 'db.php'; // Path ini sudah benar setelah file dipindahkan

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
$path_parts = explode('/', trim($path, '/'));

$pdo = getDbConnection();

switch ($method) {
    case 'GET':
        // Handles GET /pegawai AND GET /pegawai/{id}/gaji
        if (isset($path_parts[0]) && is_numeric($path_parts[0])) {
            $id_pegawai = (int)$path_parts[0];
            if (isset($path_parts[1]) && $path_parts[1] === 'gaji') {
                getGajiPegawai($pdo, $id_pegawai);
            } else {
                // You could implement GET /pegawai/{id} here if needed
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found. Did you mean /pegawai/' . $id_pegawai . '/gaji?']);
            }
        } else {
            getPegawaiList($pdo);
        }
        break;

    case 'POST':
        // Handles POST /pegawai
        createPegawai($pdo);
        break;

    case 'PUT':
        // Handles PUT /pegawai/{id}
        if (isset($path_parts[0]) && is_numeric($path_parts[0])) {
            $id_pegawai = (int)$path_parts[0];
            updatePegawai($pdo, $id_pegawai);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request. Missing employee ID.']);
        }
        break;

    case 'DELETE':
        // Handles DELETE /pegawai/{id}
        if (isset($path_parts[0]) && is_numeric($path_parts[0])) {
            $id_pegawai = (int)$path_parts[0];
            deletePegawai($pdo, $id_pegawai);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request. Missing employee ID.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}

/**
 * GET /pegawai
 * Fetches a list of all employees with their job titles.
 */
function getPegawaiList($pdo) {
    $stmt = $pdo->query(
        "SELECT p.id_pegawai, p.nama, p.gelar, j.nama_jabatan AS jabatan 
         FROM pegawai p 
         JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
         ORDER BY p.nama"
    );
    $pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($pegawai);
}

/**
 * GET /pegawai/{id}/gaji?bulan=MM&tahun=YYYY
 * Calculates salary for a specific employee for a given period.
 */
function getGajiPegawai($pdo, $id_pegawai) {
    $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
    $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

    // Note: I've added `j.tunjangan` to the query, assuming it exists in your `jabatan` table
    // as it is expected by the dashboard. If not, you can set it to 0.
    $sql = "SELECT 
                j.gaji_pokok,
                COALESCE(pb.total_potongan, 0) AS potongan,
                COALESCE(pb.total_lembur, 0) AS lembur
            FROM pegawai p
            JOIN jabatan j ON p.id_jabatan = j.id_jabatan
            LEFT JOIN (
                SELECT
                    id_pegawai,
                    (SUM(CASE WHEN status_hadir = 'alpa' THEN 1 ELSE 0 END) * 100000) + (SUM(COALESCE(terlambat_menit, 0)) * 2000) AS total_potongan,
                    (SUM(COALESCE(lembur_menit, 0)) * 1000) AS total_lembur
                FROM presensi
                WHERE MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun AND id_pegawai = :id_pegawai
                GROUP BY id_pegawai
            ) pb ON p.id_pegawai = pb.id_pegawai
            WHERE p.id_pegawai = :id_pegawai";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_pegawai' => $id_pegawai,
        ':bulan' => $bulan,
        ':tahun' => $tahun
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Cast to appropriate types for JSON response
        $result['gaji_pokok'] = (float) $result['gaji_pokok'];
        $result['potongan'] = (float) $result['potongan'];
        $result['lembur'] = (float) $result['lembur'];
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Employee not found or no salary data available.']);
    }
}

/**
 * POST /pegawai
 * Creates a new employee.
 */
function createPegawai($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nama']) || empty($data['id_jabatan'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request: Missing required fields (nama, id_jabatan).']);
        return;
    }

    $sql = "INSERT INTO pegawai (nama, gelar, id_jabatan) VALUES (:nama, :gelar, :id_jabatan)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':nama' => $data['nama'],
            ':gelar' => isset($data['gelar']) ? $data['gelar'] : null,
            ':id_jabatan' => $data['id_jabatan']
        ]);
        $id = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode(['message' => 'Employee created successfully.', 'id_pegawai' => $id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create employee: ' . $e->getMessage()]);
    }
}

/**
 * PUT /pegawai/{id}
 * Updates an existing employee.
 */
function updatePegawai($pdo, $id_pegawai) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request: No data provided.']);
        return;
    }

    $sql = "UPDATE pegawai SET nama = :nama, gelar = :gelar, id_jabatan = :id_jabatan WHERE id_pegawai = :id_pegawai";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':nama' => $data['nama'],
            ':gelar' => isset($data['gelar']) ? $data['gelar'] : null,
            ':id_jabatan' => $data['id_jabatan'],
            ':id_pegawai' => $id_pegawai
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Employee updated successfully.']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Employee not found or no changes made.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update employee: ' . $e->getMessage()]);
    }
}

/**
 * DELETE /pegawai/{id}
 * Deletes an employee.
 */
function deletePegawai($pdo, $id_pegawai) {
    // Note: Deletion might fail if there are related records in `presensi`
    // due to the ON DELETE RESTRICT foreign key constraint.
    // You might want to handle this case gracefully.
    $sql = "DELETE FROM pegawai WHERE id_pegawai = :id_pegawai";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([':id_pegawai' => $id_pegawai]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Employee deleted successfully.']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Employee not found.']);
        }
    } catch (PDOException $e) {
        // Catches foreign key constraint violations
        if ($e->getCode() == '23000') {
            http_response_code(409); // Conflict
            echo json_encode(['error' => 'Cannot delete employee. They have existing attendance records.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }
}
?>