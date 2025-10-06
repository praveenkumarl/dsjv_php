<?php
require('../fpdf/fpdf.php');
require_once 'db_connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM profiles WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Profile Details', 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'பெயர்: ' . $profile['full_name'], 0, 1);
        $pdf->Cell(0, 10, 'பிறந்த தேதி: ' . $profile['date_of_birth'], 0, 1);
        // Add more fields here

        $pdf->Output();
    }
}
?>