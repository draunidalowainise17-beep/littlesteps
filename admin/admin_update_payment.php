<?php
session_start();
include '../config.php';

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

// Validate bill_id
$bill_id = filter_input(INPUT_GET, 'bill_id', FILTER_VALIDATE_INT);
if (!$bill_id || $bill_id < 1) {
    die('Invalid bill ID.');
}

// Fetch bill
$stmt = $conn->prepare("SELECT * FROM bill_payment WHERE bill_id = ?");
$stmt->execute([$bill_id]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die('Bill not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid session token. Please refresh and try again.';
    }

    $amount_paid_now = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);

    if ($amount_paid_now === false || $amount_paid_now === null || $amount_paid_now <= 0) {
        $errors[] = 'Amount must be greater than 0.';
    } elseif ($amount_paid_now > 100000) {
        $errors[] = 'Amount is unrealistically large.';
    } else {
        $balance = $bill['total_amount'] - $bill['paid_amount'];
        if ($amount_paid_now > $balance + 0.001) {
            $errors[] = 'Amount exceeds remaining balance of $' . number_format($balance, 2) . '.';
        }
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $new_paid_amount = $bill['paid_amount'] + $amount_paid_now;
            if ($new_paid_amount > $bill['total_amount']) {
                $new_paid_amount = $bill['total_amount'];
            }
            $status = ($new_paid_amount >= $bill['total_amount']) ? 'Paid' : 'Pending';

            $upd = $conn->prepare("UPDATE bill_payment SET paid_amount = ?, payment_status = ? WHERE bill_id = ?");
            $upd->execute([$new_paid_amount, $status, $bill_id]);

            $conn->commit();
            header('Location: admin_payments.php?payment=updated');
            exit;

        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Payment</title>
    <link rel="stylesheet" href="../admin/admin_css/children.css">
</head>
<body style="padding: 40px; text-align: center;">

    <h2>Update Payment for Bill #<?= htmlspecialchars($bill['bill_id']) ?></h2>

    <p>Total Due: <strong>$<?= number_format($bill['total_amount'], 2) ?></strong></p>
    <p>Paid So Far: <strong>$<?= number_format($bill['paid_amount'], 2) ?></strong></p>
    <p>Balance Remaining: <strong>$<?= number_format($bill['total_amount'] - $bill['paid_amount'], 2) ?></strong></p>

    <?php if (!empty($errors)): ?>
        <div style="max-width:400px;margin:15px auto;color:#721c24;background:#f8d7da;padding:10px;border-radius:6px;text-align:left;">
            <?php foreach ($errors as $e): ?>
                <p style="margin:5px 0;"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width: 400px; margin: auto; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div style="margin-bottom: 15px; text-align: left;">
            <label>Amount Paid Now ($):</label><br>
            <input type="number" step="0.01" min="0.01" name="amount_paid" required style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">Log Payment</button>
        <button type="button" onclick="window.history.back()" style="padding: 10px 20px; background: #ccc; border: none; cursor: pointer;">Cancel</button>
    </form>

</body>
</html>