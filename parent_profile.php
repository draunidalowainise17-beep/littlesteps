<?php
session_start();
require_once 'config.php';

function determineAge($birthdate) {
    $dob = new DateTime($birthdate);
    $current = new DateTime();
    return $current->diff($dob)->y;
}

$activeParentId = $_SESSION['user_id'] ?? null;

if (!$activeParentId) {
    header("Location: login.php");
    exit();
}

try {
    // Parent Data
    $parentQry = $conn->prepare("SELECT name, email, contact_info, profile_image FROM users WHERE user_id = :id AND role = 'Parent'");
    $parentQry->execute([':id' => $activeParentId]);
    $parentRecord = $parentQry->fetch();

    if (!$parentRecord) {
        die("<script>alert('Account Error.'); window.location.href = 'index.php';</script>");
    }

    // Fixed path to uploaded_files/ and fallback to parent_1.png
    $parentAvatar = (!empty($parentRecord['profile_image']) && file_exists('uploaded_files/' . $parentRecord['profile_image']))
        ? 'uploaded_files/' . htmlspecialchars($parentRecord['profile_image'])
        : 'assets/media/parent_1.png';

    // Children Data
    $kidsQry = $conn->prepare("
        SELECT DISTINCT c.child_id, c.name, c.date_of_birth, cg.name AS caregiver_name
        FROM child c
        LEFT JOIN child_caregiver cc ON c.child_id = cc.child_id
        LEFT JOIN caregivers cg ON cc.caregiver_id = cg.caregiver_id
        WHERE c.user_id = :id
    ");
    $kidsQry->execute([':id' => $activeParentId]);
    $childrenList = $kidsQry->fetchAll();

    // Financial Records
    $financeQry = $conn->prepare("SELECT * FROM bill_payment WHERE user_id = :id ORDER BY bill_id DESC");
    $financeQry->execute([':id' => $activeParentId]);
    $invoiceList = $financeQry->fetchAll();

    // Alerts
    $alertQry = $conn->prepare("SELECT * FROM notification WHERE user_id = :id AND status = 'Unread'");
    $alertQry->execute([':id' => $activeParentId]);
    $unreadNotifs = $alertQry->fetchAll();

} catch (PDOException $e) {
    die("Database communication error.");
}

// Logic separation for billing
$unpaidInvoices = [];
$settledInvoices = [];

foreach ($invoiceList as $invoice) {
    if (strcasecmp(trim($invoice['payment_status']), 'paid') === 0) {
        $settledInvoices[] = $invoice;
    } else {
        $unpaidInvoices[] = $invoice;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal | Little Steps</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/parent_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .portal-modal { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); width:90%; max-width:500px; background:#fff; padding:25px; border-radius:8px; z-index:1000; box-shadow:0 10px 30px rgba(0,0,0,0.2); }
        .portal-modal .close-btn { float:right; cursor:pointer; font-size:1.5rem; color:#666; }
        .modal-bg { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:999; }
        .invoice-card { border-left: 4px solid #f0ad4e; padding: 15px; background: #fffdf5; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .history-card { border-left: 4px solid #5cb85c; padding: 15px; background: #f4fff4; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-action { display:inline-block; padding:10px 20px; border-radius:5px; text-decoration:none; font-weight:bold; transition:0.3s; }
        .btn-pay { background:#28a745; color:#fff; }
        .btn-pay:hover { background:#218838; }
        .btn-logout { background:#dc3545; color:#fff; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="parent-profile" style="padding: 100px 20px 40px; max-width: 1000px; margin: 0 auto;">
    <h1 style="text-align:center; color:#333; margin-bottom: 30px;">Welcome, <span style="color:#e9768f"><?= htmlspecialchars($parentRecord['name']); ?></span></h1>

    <!-- Identity Panel -->
    <section class="profile-details" style="text-align:center; margin-bottom:40px;">
        <img src="<?= $parentAvatar; ?>" 
             alt="Profile" 
             style="width:130px; height:130px; border-radius:50%; object-fit:cover; margin-bottom:15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 4px solid #f8b450;"
             onerror="this.src='assets/media/parent_1.png'">
        <p><strong>Email:</strong> <?= htmlspecialchars($parentRecord['email']); ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($parentRecord['contact_info'] ?: 'Unrecorded'); ?></p>
        <a href="logout.php" class="btn-action btn-logout" style="margin-top:15px;"><i class="fas fa-sign-out-alt"></i> Secure Logout</a>
    </section>

    <!-- Modal System -->
    <div id="modal-overlay" class="modal-bg"></div>
    <div id="notification-modal" class="portal-modal">
        <span class="close-btn">&times;</span>
        <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Recent Alerts</h3>
        <?php if (!empty($unreadNotifs)): ?>
            <ul style="list-style:none; padding:0;">
                <?php foreach ($unreadNotifs as $alert): ?>
                    <li style="padding:10px 0; border-bottom:1px solid #f5f5f5;">
                        <i class="fas fa-info-circle" style="color:#17a2b8;"></i> <?= htmlspecialchars($alert['message']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color:#666; text-align:center;">You're all caught up!</p>
        <?php endif; ?>
    </div>

    <!-- Roster Panel -->
    <section class="children-info" style="margin-bottom:40px;">
        <h2 style="border-bottom: 2px solid #f9ba60; padding-bottom:10px;">Registered Dependents</h2>
        <?php if (!empty($childrenList)): ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top:20px;">
                <?php foreach ($childrenList as $kid): ?>
                    <div style="background:#fff; border:1px solid #eee; padding:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                        <h3 style="margin-top:0; color:#e9768f;"><i class="fas fa-child"></i> <?= htmlspecialchars($kid['name']); ?></h3>
                        <p><strong>Age:</strong> <?= determineAge($kid['date_of_birth']); ?> yrs</p>
                        <p><strong>Primary Caregiver:</strong> <?= htmlspecialchars($kid['caregiver_name'] ?: 'Pending Assignment'); ?></p>
                        
                        <a href="child_profile.php?child_id=<?= $kid['child_id']; ?>" style="display:block; text-align:center; background:#f9ba60; color:#fff; padding:8px; border-radius:4px; text-decoration:none; margin: 15px 0;">View Full File</a>
                        
                        <form action="book_days.php" method="POST" style="background:#f9f9f9; padding:10px; border-radius:4px;">
                            <p style="margin:0 0 10px 0; font-weight:bold; font-size:0.9rem;">Schedule Care ($20/day)</p>
                            <input type="hidden" name="child_id" value="<?= $kid['child_id']; ?>">
                            <input type="text" name="selected_dates" class="calendar-input" placeholder="Pick dates..." required style="width:100%; padding:8px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                            <button type="submit" style="width:100%; background:#17a2b8; color:#fff; border:none; padding:8px; border-radius:4px; cursor:pointer;">Generate Bill</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="background:#f8f9fa; padding:15px; text-align:center;">No dependents found.</p>
        <?php endif; ?>
        
        <div style="text-align:center; margin-top: 25px;">
            <a href="child_registration.php" class="btn-action" style="background:#17a2b8; color:#fff;"><i class="fas fa-plus"></i> Enroll Another Child</a>
        </div>
    </section>

    <!-- Financial Panel -->
    <section class="billing-info">
        <h2 style="border-bottom: 2px solid #f9ba60; padding-bottom:10px;">Financial Dashboard</h2>

        <h3 style="color:#d9534f; margin-top:20px;">Requires Attention</h3>
        <?php if (!empty($unpaidInvoices)): ?>
            <?php foreach ($unpaidInvoices as $inv): 
                $due = max(0, (float)$inv['total_amount'] - (float)$inv['paid_amount']);
            ?>
                <div class="invoice-card">
                    <div style="font-size:1.2rem; color:#d9534f; font-weight:bold; margin-bottom:10px;">Total Due: $<?= number_format($due, 2); ?></div>
                    <p><strong>Invoice #<?= htmlspecialchars($inv['bill_id']); ?></strong></p>
                    <p><em><?= htmlspecialchars($inv['section_description']); ?></em></p>
                    <p>Generated On: <?= htmlspecialchars($inv['payment_date']); ?></p>
                    <?php if ($due > 0): ?>
                        <a href="pay.php?bill_id=<?= $inv['bill_id']; ?>" class="btn-action btn-pay" style="margin-top:10px;"><i class="fas fa-credit-card"></i> Proceed to Checkout</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="background:#d4edda; color:#155724; padding:15px; border-radius:5px;"><i class="fas fa-check-circle"></i> No outstanding balances.</p>
        <?php endif; ?>

        <h3 style="color:#28a745; margin-top:30px;">Settled Accounts</h3>
        <?php if (!empty($settledInvoices)): ?>
            <?php foreach ($settledInvoices as $inv): ?>
                <div class="history-card">
                    <p style="color:#28a745; font-weight:bold; margin-top:0;"><i class="fas fa-check-circle"></i> Paid Successfully</p>
                    <p><strong>Invoice #<?= htmlspecialchars($inv['bill_id']); ?>:</strong> <?= htmlspecialchars($inv['section_description']); ?></p>
                    <p>Amount: $<?= number_format($inv['total_amount'], 2); ?> | Cleared On: <?= htmlspecialchars($inv['payment_date']); ?></p>
                    <?php if (!empty($inv['receipt_document'])): ?>
                        <!-- Fixed receipt download path -->
                        <a href="uploaded_files/<?= htmlspecialchars($inv['receipt_document']); ?>" target="_blank" style="display:inline-block; margin-top:10px; color:#007bff; text-decoration:none;"><i class="fas fa-download"></i> Download Digital Receipt</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#666;">No historical transactions found.</p>
        <?php endif; ?>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr(".calendar-input", { mode: "multiple", dateFormat: "Y-m-d", minDate: "today" });

    // Handle Modal
    const pModal = document.getElementById('notification-modal');
    const pOverlay = document.getElementById('modal-overlay');
    if(pModal && pOverlay) {
        document.addEventListener('click', e => {
            if (e.target.closest('#notification-icon')) {
                pModal.style.display = 'block';
                pOverlay.style.display = 'block';
            } else if (e.target.matches('.close-btn') || e.target === pOverlay) {
                pModal.style.display = 'none';
                pOverlay.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>