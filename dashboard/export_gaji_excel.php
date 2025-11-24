<?php
/* Export Excel */
require '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// pastikan variabel koneksi benar
if (!isset($koneksi)) {
    die("Koneksi database tidak ditemukan. Pastikan variabel \$koneksi tersedia.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1','Pegawai');
$sheet->setCellValue('B1','Jabatan');
$sheet->setCellValue('C1','Gaji Pokok');
$sheet->setCellValue('D1','Total Potongan');
$sheet->setCellValue('E1','Total Lembur');
$sheet->setCellValue('F1','Gaji Bersih');

$query = "
SELECT 
    k.nama AS Pegawai, 
    j.nama_jabatan AS Jabatan, 
    j.gaji_pokok AS Gaji_Pokok,
    COALESCE(pb.total_potongan,0) AS Total_Potongan, 
    COALESCE(pb.total_lembur,0) AS Total_Lembur,
    (j.gaji_pokok - COALESCE(pb.total_potongan,0) + COALESCE(pb.total_lembur,0)) AS Gaji_Bersih
FROM pegawai k
JOIN jabatan j ON k.id_jabatan = j.id_jabatan
LEFT JOIN (
    SELECT 
        id_pegawai,
        (SUM(CASE WHEN status_hadir = 'alpa' THEN 1 ELSE 0 END) * 100000)
             + (SUM(COALESCE(terlambat_menit,0)) * 2000) AS total_potongan,
        SUM(COALESCE(lembur_menit,0)) * 1000 AS total_lembur
    FROM presensi
    WHERE MONTH(tanggal) = 1 
      AND YEAR(tanggal) = 2025
    GROUP BY id_pegawai
) pb ON k.id_pegawai = pb.id_pegawai
ORDER BY k.nama
";

$result = $koneksi->query($query);

// cek error
if (!$result) {
    die("Query Error: " . $koneksi->error);
}

$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A'.$rowNum, $row['Pegawai']);
    $sheet->setCellValue('B'.$rowNum, $row['Jabatan']);
    $sheet->setCellValue('C'.$rowNum, $row['Gaji_Pokok']);
    $sheet->setCellValue('D'.$rowNum, $row['Total_Potongan']);
    $sheet->setCellValue('E'.$rowNum, $row['Total_Lembur']);
    $sheet->setCellValue('F'.$rowNum, $row['Gaji_Bersih']);
    $rowNum++;
}

$writer = new Xlsx($spreadsheet);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laporan_gaji.xlsx"');

$writer->save('php://output');
exit;
?>
