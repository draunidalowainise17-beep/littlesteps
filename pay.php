<?php
session_start();
require_once 'config.php';

$activeUserId = $_SESSION['user_id'] ?? null;

if (!$activeUserId) {
    header("Location: login.php");
    exit();
}

$invoiceId = filter_input(INPUT_GET, 'bill_id', FILTER_VALIDATE_INT);

if (!$invoiceId) {
    echo "<script>alert('Invalid transaction request.'); window.location.href = 'parent_profile.php';</script>";
    exit();
}

try {
    // Security check: ensure bill belongs to this parent and is unpaid
    $verifyQry = $conn->prepare("
        SELECT * FROM bill_payment 
        WHERE bill_id = :bill_id AND user_id = :user_id AND payment_status != 'Paid' 
        LIMIT 1
    ");
    $verifyQry->execute([
        ':bill_id' => $invoiceId,
        ':user_id' => $activeUserId
    ]);
    $invoiceData = $verifyQry->fetch(PDO::FETCH_ASSOC);

    if (!$invoiceData) {
        echo "<script>alert('Invoice inaccessible or already settled.'); window.location.href = 'parent_profile.php';</script>";
        exit();
    }

    // Calculate Due
    $totalFee = (float)$invoiceData['total_amount'];
    $paidSoFar = (float)$invoiceData['paid_amount'];
    $balanceDue = max(0, $totalFee - $paidSoFar);

} catch (PDOException $e) {
    die("Transaction interface error. Please contact support.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { margin: 0; padding: 40px 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; }
        .checkout-wrapper { max-width: 500px; margin: 0 auto; background: #fff; padding: 35px; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        .header-title { text-align: center; color: #2c3e50; margin-bottom: 5px; }
        .header-sub { text-align: center; color: #7f8c8d; margin-bottom: 30px; font-size: 0.95rem; }
        .balance-card { background: #e8f8f5; padding: 25px; border-radius: 8px; text-align: center; margin-bottom: 25px; border: 1px solid #d1f2eb; }
        .balance-label { display: block; color: #16a085; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .balance-amount { color: #1abc9c; font-size: 42px; font-weight: bold; }
        .invoice-summary { background: #fdfefe; border: 1px solid #ecf0f1; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
        .invoice-summary p { margin: 10px 0; color: #34495e; font-size: 0.95rem; display: flex; justify-content: space-between;}
        .invoice-summary p strong { color: #2c3e50; }
        .alert-box { background: #fcf3cf; border-left: 4px solid #f1c40f; color: #7d6608; padding: 15px; border-radius: 4px; margin-bottom: 25px; font-size: 0.9rem; line-height: 1.5; }
        .btn-confirm { width: 100%; padding: 16px; border: none; border-radius: 6px; background: #27ae60; color: white; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-confirm:hover { background: #219150; }
        .btn-back { display: block; text-align: center; margin-top: 20px; color: #95a5a6; text-decoration: none; font-size: 0.9rem; }
        .btn-back:hover { color: #34495e; }
    </style>
</head>
<body>

<div class="checkout-wrapper">
    <h1 class="header-title"><i class="fas fa-lock"></i> Secure Checkout</h1>
    <p class="header-sub">Verify your invoice details before proceeding.</p>

    <div class="balance-card">
        <span class="balance-label">Outstanding Balance</span>
        <div class="balance-amount">$<?= number_format($balanceDue, 2); ?></div>
    </div>

    <div class="invoice-summary">
        <p><strong>Transaction ID:</strong> <span>#<?= htmlspecialchars($invoiceData['bill_id']); ?></span></p>
        <div style="margin: 10px 0; padding: 10px; background: #f4f6f7; border-radius: 4px; font-size: 0.85rem; color: #555;">
            <?= htmlspecialchars($invoiceData['section_description']); ?>
        </div>
        <p><strong>Generated On:</strong> <span><?= htmlspecialchars($invoiceData['payment_date']); ?></span></p>
        <p><strong>Total Billed:</strong> <span>$<?= number_format($totalFee, 2); ?></span></p>
        <p><strong>Amount Cleared:</strong> <span>$<?= number_format($paidSoFar, 2); ?></span></p>
        <hr style="border: 0; border-top: 1px solid #ecf0f1; margin: 15px 0;">
        <p style="font-size: 1.1rem;"><strong>Current Status:</strong> <span style="color:#e67e22; font-weight:bold;"><?= htmlspecialchars($invoiceData['payment_status']); ?></span></p>
    </div>

    <div class="alert-box">
        <i class="fas fa-shield-alt"></i> <strong>Payment Verification</strong><br>
        By confirming this payment, you authorize Little Steps Daycare to process the outstanding balance.
    </div>

    <form action="process_payment.php" method="POST" onsubmit="return confirm('Please confirm you wish to finalize this payment of $<?= number_format($balanceDue, 2); ?>.');">
        <input type="hidden" name="bill_id" value="<?= htmlspecialchars($invoiceData['bill_id']); ?>">
        <button type="submit" class="btn-confirm">
            <i class="fas fa-check-circle"></i> Authorize $<?= number_format($balanceDue, 2); ?>
        </button>
    </form>

    <a href="parent_profile.php" class="btn-back"><i class="fas fa-arrow-left"></i> Return to Portal</a>
</div>

</body>
</html>