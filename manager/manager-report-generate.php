<?php 
require_once('../pdf_mc_table.php');
require_once("../config.php");





function formatTanggal($tanggal) {
    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $date ? $date->format('d-m-Y') : $tanggal;
}

function formatTanggalLengkap($tanggal) {
    $bulanIndo = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];

    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$date) return $tanggal;
    
    $bulan = $bulanIndo[$date->format('F')];
    return $date->format('d') . ' ' . $bulan . ' ' . $date->format('Y');
}

function formatTanggalDenganWaktu($datetime) {
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
    return $date ? $date->format('d-m-Y H:i:s') : $datetime;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filter = $_POST['filter'];
    $startRange = $_POST['start-range'];
    $endRange = $_POST['end-range'];
    $status = $_POST['status'];
    $prioritas = $_POST['prioritas'];
    $ditugaskan = $_POST['ditugaskan'];

    if (empty($startRange) || empty($endRange)) {
        echo "<script>alert('Rentang tanggal harus diisi keduanya.'); history.back();</script>";
        exit();
    }

    if (!DateTime::createFromFormat('Y-m-d', $startRange) || !DateTime::createFromFormat('Y-m-d', $endRange)) {
        echo "<script>alert('Format tanggal tidak valid. Gunakan format YYYY-MM-DD.'); history.back();</script>";
        exit();
    }

    $displayStart = $_POST['start-range'];
    $displayEnd = $_POST['end-range'];

    $startRange = $displayStart;
    $endRange = $displayEnd;

    if ($startRange > $endRange) {
        $temp = $startRange;
        $startRange = $endRange;
        $endRange = $temp;
    }

    $query = "SELECT t.nama_tugas, t.tanggal_mulai, t.deadline, t.waktu_selesai, t.status, t.prioritas, t.file_pengumpulan, t.ditugaskan, u.name
    FROM tasks t
    LEFT JOIN users u ON t.ditugaskan = u.username
    WHERE 1=1";


    if ($filter == 'start-date') {
        $query .= " AND tanggal_mulai BETWEEN '$startRange' AND '$endRange' ORDER BY tanggal_mulai ASC";
    } elseif ($filter == 'deadline') {
        $query .= " AND deadline BETWEEN '$startRange' AND '$endRange' ORDER BY deadline ASC";

    }

    if (!empty($status)) $query .= " AND status = '$status'";
    if (!empty($prioritas)) $query .= " AND prioritas = '$prioritas'";
    if (!empty($ditugaskan)) $query .= " AND ditugaskan = '$ditugaskan'";

    $formattedStartRange = formatTanggal($displayStart);
    $formattedEndRange = formatTanggal($displayEnd);

    $result = $conn->query($query);
    $tasks = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }

    if (!empty($tasks)) {
        $pdf = new PDF_MC_Table();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // HEADER
        $pdf->Image('../images/TA-Logo.png', 5, -10, 40);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'PT TELKOM AKSES CIBITUNG', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 6, ' Jl. Raya Setu No.27, Cibuntu, Kec. Cibitung, Kabupaten Bekasi, Jawa Barat 17520', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Email: info@telkomakses.co.id | Telp: (021) 12345678', 0, 1, 'C');
        $pdf->Line(10, 35, 200, 35);
        $pdf->Ln(10);

        // JUDUL & FILTER
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 10, 'LAPORAN REKAPITULASI DATA TUGAS SEMUA USER', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, "Filter: $filter | $formattedStartRange s/d $formattedEndRange", 0, 1, 'C');

        $pdf->Ln(5);

        // TABEL
        $pdf->SetWidths([35, 25, 25, 30, 27, 17, 30]);
        $pdf->SetLineHeight(6);







        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(35, 10, 'Nama Tugas', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'Tanggal Mulai', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'Deadline', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Waktu Selesai', 1, 0, 'C', true);
        $pdf->Cell(27, 10, 'Status', 1, 0, 'C', true);
        $pdf->Cell(17, 10, 'Prioritas', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Ditugaskan', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 10);
        foreach ($tasks as $task) {
            $tanggalMulai = formatTanggalDenganWaktu($task['tanggal_mulai']);
            $deadline = formatTanggal($task['deadline']);
            $waktuSelesai = (!empty($task['waktu_selesai']) && $task['status'] === 'Selesai') 
            ? formatTanggalDenganWaktu($task['waktu_selesai']) 
            : 'Belum selesai';

            $pdf->Row([
                $task['nama_tugas'],
                $tanggalMulai,
                $deadline,
                $waktuSelesai,
                $task['status'],
                $task['prioritas'],
                $task['ditugaskan'] . ' (' . $task['name'] . ')'
            ]);
            
        }


        // FOOTER TTD
    // FOOTER TTD
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(130, 6, '', 0, 0); // Kosongkan bagian kiri
        $pdf->Cell(60, 6, 'Bekasi, ' . formatTanggalLengkap(date('Y-m-d')), 0, 1, 'C'); // Bekasi di tengah
        $pdf->Cell(130, 6, '', 0, 0); // Kosongkan lagi bagian kiri
        $pdf->Cell(60, 6, 'General Manager', 0, 1, 'C'); // General Manager di tengah, sejajar dengan tanggal        
        $pdf->Ln(20);
        $pdf->Cell(130, 6, '', 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(60, 6, 'Faiz Daffa Rafiansyah', 0, 1, 'C');
        $pdf->Cell(130, 6, '', 0, 0);
        $pdf->SetFont('Arial', '', 10);

      

    

        $pdf->Output();
    } else {
        echo "<script>alert('Tidak ada tugas yang ditemukan.'); history.back();</script>";
    }
} else {
    echo "Metode request tidak valid.";
}
?>
