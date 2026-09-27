<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$activeUserId = $_SESSION['user_id'];

$notifications = [];
$isBooked = false;
$receiptDetails = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $childId = filter_input(INPUT_POST, 'child_id', FILTER_SANITIZE_NUMBER_INT);
    $childName = filter_input(INPUT_POST, 'child_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pickedDates = filter_input(INPUT_POST, 'selected_dates', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $payMethod = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Cash';

    if (empty($childName) && !empty($childId)) {
        $stmtName = $conn->prepare("SELECT name FROM child WHERE child_id = :cid AND user_id = :uid");
        $stmtName->execute([':cid' => $childId, ':uid' => $activeUserId]);
        $fetchedChild = $stmtName->fetch(PDO::FETCH_ASSOC);
        $childName = $fetchedChild ? $fetchedChild['name'] : '';
    }

    if (!$childId || !$childName || !$pickedDates) {
        $notifications[] = 'Error: Missing required booking information.';
    } else {
        $dateArr = explode(',', $pickedDates);
        $daysCount = count($dateArr);
        $dailyRate = 20;
        $totalCost = $daysCount * $dailyRate;

        $desc = "Booking for {$childName} (ID: {$childId}) – {$daysCount} day(s) on " . implode(', ', $dateArr) . ". Payment: {$payMethod}";
        
        $status = ($payMethod === 'Card') ? 'Paid' : 'Pending';
        $amountPaid = ($status === 'Paid') ? $totalCost : 0;

        $stmtBooking = $conn->prepare("
            INSERT INTO bill_payment 
            (user_id, section_description, total_amount, paid_amount, payment_status, payment_date)
            VALUES (:uid, :desc, :total, :paid, :status, NOW())
        ");
        
        if ($stmtBooking->execute([
            ':uid' => $activeUserId, 
            ':desc' => $desc, 
            ':total' => $totalCost, 
            ':paid' => $amountPaid, 
            ':status' => $status
        ])) {
            $isBooked = true;
            $receiptDetails = [
                'transaction_id' => $conn->lastInsertId(),
                'child' => $childName,
                'dates' => $dateArr,
                'days' => $daysCount,
                'rate' => $dailyRate,
                'total' => $totalCost,
                'method' => $payMethod,
                'status' => $status
            ];
        } else {
            $notifications[] = 'System error: Booking could not be processed.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Care</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/caregiver_registration.css">
    <style>
        .booking-page-wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2rem 1rem; background: #f9f9f9; }
        .booking-panel { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 600px; }
        .form-control { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-primary { background: #17a2b8; color: #fff; padding: 12px 30px; border-radius: 50px; cursor: pointer; width: 100%; border: none; margin-top: 15px;}
        .receipt-card { background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); max-width: 600px; margin: 2rem auto; }
        @media print { .no-print { display: none !important; } .receipt-card { box-shadow: none; border: 1px solid #ddd; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="booking-page-wrapper">
    <?php if ($isBooked): ?>
        <div class="receipt-card">
            <h2 style="text-align: center; color: #17a2b8;"><i class="fas fa-receipt"></i> Official Receipt</h2>
            <hr>
            <p><strong>Transaction ID:</strong> <?= $receiptDetails['transaction_id'] ?></p>
            <p><strong>Child Name:</strong> <?= htmlspecialchars($receiptDetails['child']) ?></p>
            <p><strong>Total Days:</strong> <?= $receiptDetails['days'] ?></p>
            <p><strong>Rate/Day:</strong> $<?= number_format($receiptDetails['rate'], 2) ?></p>
            <p><strong>Total Due:</strong> $<?= number_format($receiptDetails['total'], 2) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($receiptDetails['method']) ?></p>
            <p><strong>Status:</strong> <?= $receiptDetails['status'] ?></p>
            <hr>
            <div class="no-print" style="text-align:center; margin-top: 15px;">
                <button class="btn-primary" onclick="window.print()" style="width:auto;"><i class="fas fa-print"></i> Print</button>
                <a href="parent_profile.php" class="btn-primary" style="text-decoration:none; display:inline-block; width:auto; background:#6c757d;">Back</a>
            </div>
        </div>
    <?php else: ?>
        <div class="booking-panel">
            <h3 style="text-align: center;"><i class="fas fa-calendar-plus"></i> Schedule Care Days</h3>
            <?php foreach ($notifications as $note): ?>
                <div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-bottom: 15px;"><?= htmlspecialchars($note) ?></div>
            <?php endforeach; ?>
            
            <form action="" method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Select Child</label>
                    <select name="child_id" class="form-control" required>
                        <option value="">-- Choose --</option>
                        <?php
                        $childQuery = $conn->prepare("SELECT child_id, name FROM child WHERE user_id = ?");
                        $childQuery->execute([$activeUserId]);
                        $children = $childQuery->fetchAll();
                        foreach ($children as $c) {
                            echo "<option value='{$c['child_id']}'>" . htmlspecialchars($c['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Dates Required</label>
                    <input type="text" id="date_picker" name="selected_dates" class="form-control" placeholder="Select dates..." required readonly>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="Cash">Pay via Cash</option>
                        <option value="Card">Pay via Card</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Confirm Booking</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#date_picker", { mode: "multiple", dateFormat: "Y-m-d", minDate: "today" });
</script>
</body>
</html>