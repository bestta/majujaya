<?php
require '../vendor/autoload.php';
include '../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil parameter bulan & tahun
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Query rekap gaji
$sql = "SELECT
    k.nama AS 'Pegawai',
    j.nama_jabatan AS 'Jabatan',
    j.gaji_pokok AS 'Gaji Pokok',
    COALESCE(pb.total_potongan, 0) AS 'Total Potongan',
    COALESCE(pb.total_lembur, 0) AS 'Total Lembur',
    (j.gaji_pokok - COALESCE(pb.total_potongan, 0) + COALESCE(pb.total_lembur, 0)) 
        AS 'Gaji Bersih'
FROM pegawai k
JOIN jabatan j ON k.id_jabatan = j.id_jabatan

LEFT JOIN (
    SELECT
        id_pegawai,
        (SUM(CASE WHEN status_hadir = 'alpa' THEN 1 ELSE 0 END) * 100000)
            + (SUM(COALESCE(terlambat_menit, 0)) * 2000) AS total_potongan,
        SUM(COALESCE(lembur_menit, 0)) * 1000 AS total_lembur
    FROM presensi
    WHERE MONTH(tanggal) = ?
      AND YEAR(tanggal) = ?
    GROUP BY id_pegawai
) pb ON k.id_pegawai = pb.id_pegawai
ORDER BY k.nama;
";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ii", $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

// Membuat file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header kolom
$sheet->setCellValue('A1', 'Nama');
$sheet->setCellValue('B1', 'Jabatan');
$sheet->setCellValue('C1', 'Gaji Pokok');
$sheet->setCellValue('D1', 'Potongan');
$sheet->setCellValue('E1', 'Lembur');
$sheet->setCellValue('F1', 'Gaji Bersih');

// Isi data
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['nama']);
    $sheet->setCellValue('B' . $row, $data['nama_jabatan']);
    $sheet->setCellValue('C' . $row, $data['gaji_pokok']);
    $sheet->setCellValue('D' . $row, $data['total_potongan']);
    $sheet->setCellValue('E' . $row, $data['total_lembur']);
    $sheet->setCellValue('F' . $row, $data['gaji_bersih']);
    $row++;
}

// Export file
$filename = "rekap_gaji_{$bulan}_{$tahun}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
