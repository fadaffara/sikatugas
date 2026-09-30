<?php 
require_once('../pdf_mc_table.php');
require_once("../config.php");
session_start();


if (!isset($_SESSION['username_user'])) {
    header("Location: login-user.php");
    exit;
}

$username = $_SESSION['username_user'];
$name     = $_SESSION['name_user'];

function formatTanggal($tanggal) {
    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $date ? $date->format('d-m-Y') : $tanggal;
}

function formatTanggalDenganWaktu($datetime) {
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
    return $date ? $date->format('d-m-Y H:i:s') : $datetime;
}

function formatTanggalLengkap($tanggal) {
    $bulanIndo = [
        'January'   => 'Januari', 'February' => 'Februari', 'March'    => 'Maret',
        'April'     => 'April',   'May'      => 'Mei',      'June'     => 'Juni',
        'July'      => 'Juli',    'August'   => 'Agustus',  'September'=> 'September',
        'October'   => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];
    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$date) return $tanggal;
    $bulan = $bulanIndo[$date->format('F')];
    return $date->format('d') . ' ' . $bulan . ' ' . $date->format('Y');
}

// Mulai proses jika ada request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filter     = $_POST['filter'];
    $startRange = $_POST['start-range'];
    $endRange   = $_POST['end-range'];
    $status     = $_POST['status'];
    $prioritas  = $_POST['prioritas'];

    // Validasi tanggal
    if (empty($startRange) || empty($endRange)) {
        echo "<script>alert('Rentang tanggal harus diisi keduanya.'); history.back();</script>";
        exit();
    }

    if (!DateTime::createFromFormat('Y-m-d', $startRange) || !DateTime::createFromFormat('Y-m-d', $endRange)) {
        echo "<script>alert('Format tanggal tidak valid. Gunakan format YYYY-MM-DD.'); history.back();</script>";
        exit();
    }

    $displayStart = $startRange;
    $displayEnd   = $endRange;

    // Tukar jika urutan tanggal terbalik
    if ($startRange > $endRange) {
        $temp       = $startRange;
        $startRange = $endRange;
        $endRange   = $temp;
    }

    // Query data
    $query = "SELECT nama_tugas, tanggal_mulai, deadline, waktu_selesai, status, prioritas 
              FROM tasks
              WHERE ditugaskan = '$username'";

    if ($filter == 'start-date') {
        $query .= " AND tanggal_mulai BETWEEN '$startRange' AND '$endRange' ORDER BY tanggal_mulai DESC";
    } elseif ($filter == 'deadline') {
        $query .= " AND deadline BETWEEN '$startRange' AND '$endRange' ORDER BY deadline DESC";
    }

    if ($status != 'Semua') {
        $query .= " AND status = '$status'";
    }

    if ($prioritas != 'Semua') {
        $query .= " AND prioritas = '$prioritas'";
    }

    $result = $conn->query($query);
    $tasks  = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }

    // Generate PDF jika data ada
    if (!empty($tasks)) {
        $pdf = new PDF_MC_Table();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Header
        $pdf->Image('../images/TA-Logo.png', 5, -10, 40);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'PT TELKOM AKSES CIBITUNG', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 6, 'Jl. Raya Setu No.27, Cibuntu, Kec. Cibitung, Kabupaten Bekasi, Jawa Barat 17520', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Email: info@telkomakses.co.id | Telp: (021) 12345678', 0, 1, 'C');
        $pdf->Line(10, 35, 200, 35);
        $pdf->Ln(10);

        // Judul dan info filter
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 10, 'LAPORAN REKAPITULASI DATA TUGAS', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, "Filter: $filter | " . formatTanggal($displayStart) . " s/d " . formatTanggal($displayEnd), 0, 1, 'C');
        $pdf->Ln(5);




        // Header tabel
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230); // Biru muda
        $pdf->SetWidths([50, 30, 30, 30, 30, 20]);
        $pdf->SetLineHeight(6);
        $pdf->Row(['Nama Tugas', 'Waktu Mulai', 'Deadline', 'Waktu Selesai', 'Status', 'Prioritas'], true);

        // Isi tabel
        $pdf->SetFont('Arial', '', 10);
        foreach ($tasks as $task) {
            $waktuSelesai = $task['status'] != 'Selesai' ? 'Belum Selesai' : formatTanggalDenganWaktu($task['waktu_selesai']);
            $pdf->Row([
                $task['nama_tugas'],
                formatTanggalDenganWaktu($task['tanggal_mulai']),
                formatTanggal($task['deadline']),
                $waktuSelesai,
                $task['status'],
                $task['prioritas']
            ]);
        }
        
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(130, 6, '', 0, 0);
$pdf->Cell(60, 6, 'Bekasi, ' . formatTanggalLengkap(date('Y-m-d')), 0, 1, 'C');

$pdf->Ln(20);
$pdf->Cell(130, 6, '', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 6, $name, 0, 1, 'C'); 

$pdf->Cell(130, 6, '', 0, 0);
$pdf->SetFont('Arial', 'B', 10); 
$pdf->Cell(60, 6, '(' . $username . ')', 0, 1, 'C'); 

$pdf->Cell(130, 6, '', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 6, 'Staff', 0, 1, 'C');

    
        $pdf->Output();
    } else {
        echo "<script>alert('Tidak ada tugas yang ditemukan.'); history.back();</script>";
    }
} else {
    echo "Metode request tidak valid.";
}
