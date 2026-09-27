<?php
// Include the database configuration file
include '../config.php';

// FPDF library - plain PHP, no external dependencies, works on shared hosting
require '../fpdf/fpdf.php';

if (!isset($_GET['bill_id'])) {
    echo "No bill specified.";
    exit;
}

$bill_id = (int) $_GET['bill_id'];

try {
    $stmt = $conn->prepare("
        SELECT b.*, u.name AS parent_name
        FROM bill_payment b
        LEFT JOIN users u ON b.user_id = u.user_id
        WHERE b.bill_id = :bill_id
    ");
    $stmt->bindParam(':bill_id', $bill_id, PDO::PARAM_INT);
    $stmt->execute();
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit;
}

$conn = null;

if (!$bill) {
    echo "Bill not found.";
    exit;
}

// --- Build the PDF ---
$pdf = new FPDF('P', 'mm', 'A5'); // small receipt-sized page
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, 'Little Steps Day Care Centre', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'Payment Receipt', 0, 1, 'C');
$pdf->Ln(4);
$pdf->SetDrawColor(50, 50, 50);
$pdf->Line(15, $pdf->GetY(), 133, $pdf->GetY());
$pdf->Ln(6);

// Details
$rows = [
    ['Receipt No.', '#' . $bill['bill_id']],
    ['Parent', $bill['parent_name'] ?? 'N/A'],
    ['Date', $bill['payment_date']],
    [$bill['section_type'], $bill['section_description']],
];

foreach ($rows as $row) {
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(45, 8, $row[0], 0, 0);
    $pdf->Cell(0, 8, (string) $row[1], 0, 1);
}

$pdf->Ln(4);
$pdf->SetDrawColor(50, 50, 50);
$pdf->Line(15, $pdf->GetY(), 133, $pdf->GetY());
$pdf->Ln(6);

// Amount Paid (highlighted)
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(33, 136, 56); // green
$pdf->Cell(45, 10, 'Amount Paid', 0, 0);
$pdf->Cell(0, 10, '$' . number_format($bill['paid_amount'], 2), 0, 1);
$pdf->SetTextColor(0, 0, 0);

// Output: opens in the browser's built-in PDF viewer, where the parent can
// print it or use "Save as..." / the download icon to save the file.
// Change 'I' to 'D' below if you'd rather force an immediate download instead.
$pdf->Output('I', 'Receipt_' . $bill['bill_id'] . '.pdf');
