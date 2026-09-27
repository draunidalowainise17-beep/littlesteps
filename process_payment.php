<?php
session_start();
require_once 'config.php';

$activeUserId = $_SESSION['user_id'] ?? null;

if (!$activeUserId) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: parent_profile.php");
    exit();
}

$invoiceId = $_POST['bill_id'] ?? null;

if (!$invoiceId || !is_numeric($invoiceId)) {
    echo "<script>alert('Invalid payment request.'); window.location.href = 'parent_profile.php';</script>";
    exit();
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("
        SELECT * FROM bill_payment 
        WHERE bill_id = :bill_id AND user_id = :user_id AND payment_status != 'Paid' 
        LIMIT 1 FOR UPDATE
    ");
    $stmt->execute([
        ':bill_id' => $invoiceId,
        ':user_id' => $activeUserId
    ]);
    
    $invoiceRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoiceRecord) {
        $conn->rollBack();
        echo "<script>alert('Invoice not found, already settled, or access denied.'); window.location.href = 'parent_profile.php';</script>";
        exit();
    }

    $totalFee = (float)$invoiceRecord['total_amount'];
    $paidSoFar = (float)$invoiceRecord['paid_amount'];
    $balanceDue = $totalFee - $paidSoFar;

    if ($balanceDue <= 0) {
        $conn->rollBack();
        echo "<script>alert('This invoice has already been cleared.'); window.location.href = 'parent_profile.php';</script>";
        exit();
    }

    $updateStmt = $conn->prepare("
        UPDATE bill_payment 
        SET paid_amount = :paid_amount, payment_status = :payment_status 
        WHERE bill_id = :bill_id AND user_id = :user_id
    ");

    $updateStmt->execute([
        ':paid_amount' => $totalFee,
        ':payment_status' => 'Paid',
        ':bill_id' => $invoiceId,
        ':user_id' => $activeUserId
    ]);

    $conn->commit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("Transaction failure: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Complete</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; justify-content: center; align-items: center; background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
        .receipt-card { background: white; width: 100%; max-width: 450px; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        .icon-circle { width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; background: #27ae60; color: white; font-size: 40px; line-height: 80px; }
        h1 { color: #27ae60; margin-bottom: 10px; font-size: 1.8rem; }
        .text-muted { color: #7f8c8d; line-height: 1.6; }
        .amount-display { font-size: 36px; font-weight: bold; color: #2c3e50; margin: 20px 0; }
        .btn-return { display: inline-block; padding: 14px 24px; background: #27ae60; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition: 0.3s; margin-top: 15px; }
        .btn-return:hover { background: #219150; }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="icon-circle">✓</div>
        <h1>Payment Successful!</h1>
        <p class="text-muted">Your transaction has been securely processed and recorded in our systems.</p>
        
        <div class="amount-display">
            $<?= number_format($balanceDue, 2); ?>
        </div>
        
        <p style="color: #7f8c8d; margin-bottom: 25px;">
            <strong>Reference ID:</strong> #<?= htmlspecialchars($invoiceId); ?>
        </p>
        
        <a href="parent_profile.php" class="btn-return">Return to Portal</a>
    </div>
</body>
</html>