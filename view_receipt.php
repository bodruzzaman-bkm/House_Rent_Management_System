<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$agreement_id = isset($_GET['agreement_id']) ? intval($_GET['agreement_id']) : 0;
$billing_month = isset($_GET['billing_month']) ? trim($_GET['billing_month']) : '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($agreement_id <= 0 || $billing_month === '') {
    echo "Invalid request.";
    exit();
}

// fetch receipt
// ensure receipts table exists to avoid fatal errors when table is missing
$tblCheck = $conn->query("SHOW TABLES LIKE 'receipts'");
if (!$tblCheck || $tblCheck->num_rows === 0) {
    echo "Receipt not found.";
    exit();
}

if ($token !== '') {
    $sql = "SELECT r.*, ut.name AS tenant_name, ut.phone AS tenant_phone, ut.nid AS tenant_nid,
                   uo.name AS owner_name, o.bank_name AS owner_bank, o.account_number AS owner_account
            FROM receipts r
            LEFT JOIN user ut ON r.tenant_id = ut.user_id
            LEFT JOIN user uo ON r.owner_id = uo.user_id
            LEFT JOIN owner o ON r.owner_id = o.user_id
            WHERE r.receipt_number = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $receipt = $result->fetch_assoc();
    $stmt->close();
} else {
    $sql = "SELECT r.*, ut.name AS tenant_name, ut.phone AS tenant_phone, ut.nid AS tenant_nid,
                   uo.name AS owner_name, o.bank_name AS owner_bank, o.account_number AS owner_account
            FROM receipts r
            LEFT JOIN user ut ON r.tenant_id = ut.user_id
            LEFT JOIN user uo ON r.owner_id = uo.user_id
            LEFT JOIN owner o ON r.owner_id = o.user_id
            WHERE r.agreement_id = ? AND r.billing_month = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $agreement_id, $billing_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $receipt = $result->fetch_assoc();
    $stmt->close();
}

if (!$receipt) {
    // no receipt found — offer owner a chance to generate it, otherwise show friendly message
    $role = $_SESSION['role'] ?? '';
    if ($role === 'owner') {
        // ensure owner is authorized for this agreement before showing generate link
        $check = $conn->prepare("SELECT 1 FROM agreement a WHERE a.agreement_id = ? AND a.owner_id = ? LIMIT 1");
        $check->bind_param('ii', $agreement_id, $_SESSION['user_id']);
        $check->execute();
        $cr = $check->get_result();
        $check->close();
        if ($cr && $cr->num_rows > 0) {
            echo '<div style="max-width:800px;margin:30px auto;padding:16px;background:#fff;border:1px solid #eee;border-radius:6px;text-align:center;">';
            echo '<p>No receipt was found for this bill yet.</p>';
            echo '<p><a class="print-btn" href="actions/generate_receipt.php?agreement_id=' . urlencode($agreement_id) . '&billing_month=' . urlencode($billing_month) . '">Generate Receipt Now</a></p>';
            echo '<p style="margin-top:8px;"><a href="owner_bills.php">Back to Bills</a></p>';
            echo '</div>';
            exit();
        }
    }

    // not owner or not authorized
    echo '<div style="max-width:800px;margin:30px auto;padding:16px;background:#fff;border:1px solid #eee;border-radius:6px;text-align:center;">';
    echo '<p>Receipt not found. If you are a tenant, please contact your owner to generate the receipt.</p>';
    echo '<p><a href="tenant_bills.php">Back to Bills</a></p>';
    echo '</div>';
    exit();
}

// authorization: tenant who owns it, or owner who owns it
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

if ($role === 'tenant' && $receipt['tenant_id'] != $user_id) {
    echo "Not authorized to view this receipt.";
    exit();
}

if ($role === 'owner' && $receipt['owner_id'] != $user_id) {
    echo "Not authorized to view this receipt.";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo htmlspecialchars($receipt['receipt_number']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; }
        .receipt-wrap { max-width: 800px; margin: 20px auto; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .r-header { background: #263447; color: #fff; padding: 18px 24px; display:flex; align-items:center; justify-content:space-between; }
        .r-brand { display:flex; align-items:center; gap:12px; }
        .r-brand .logo { width:56px; height:36px; background:linear-gradient(135deg,#2bb7ff,#0052d4); border-radius:6px; display:inline-block; }
        .r-title { font-size:18px; font-weight:700; }
        .r-sub { background:#556377; color:#fff; padding:8px 24px; }
        .receipt { padding:24px; }
        .receipt h2 { margin:6px 0 14px 0; font-size:20px; }
        .details { display:grid; grid-template-columns:1fr 1fr; gap:8px 24px; }
        .label { color:#6b6f76; }
        .value { text-align:right; font-weight:600; }
        .full-row { grid-column:1 / -1; }
        .note { margin-top:18px; color:#6b6f76; font-size:13px; }
        .print-actions { margin-top:18px; }
        .print-btn { display: inline-block; padding: 10px 14px; background: #007BFF; color: #fff; border-radius: 4px; text-decoration: none; }
        .footer-bar { background:#1f2733; color:#cfd6dd; padding:10px 24px; font-size:12px; }
        @media print { .no-print { display: none; } .receipt-wrap { box-shadow:none; margin:0; } }
    </style>
</head>
<body>
<div class="receipt-wrap">
    <div class="r-header">
        <div class="r-brand">
            <div class="logo" aria-hidden="true"></div>
            <div>
                <div class="r-title">ZigBank</div>
                <div style="font-size:12px; color:#c7d0d8;">Payment Receipt</div>
            </div>
        </div>
        <div style="text-align:right; font-size:13px;">
            <?php echo date('d M Y H:i:s', strtotime($receipt['paid_at'])); ?>
        </div>
    </div>
    <div class="r-sub"><?php echo htmlspecialchars($receipt['owner_name'] ?? ''); ?></div>
    <div class="receipt">
        <h2>INTERNAL PAY NOW</h2>
        <div class="details">
            <div class="label">Reference Number</div><div class="value"><?php echo htmlspecialchars($receipt['receipt_number']); ?></div>
            <div class="label">Transfer to</div><div class="value"><?php echo htmlspecialchars($receipt['owner_name']); ?></div>
            <div class="label">Account Type</div><div class="value">Internal</div>
            <div class="label">Account Number</div><div class="value"><?php echo htmlspecialchars(substr($receipt['owner_account'] ?? 'N/A', -4) ? str_repeat('X', max(0, strlen($receipt['owner_account'] ?? '')-4)).substr($receipt['owner_account'] ?? '', -4) : 'N/A'); ?></div>
            <div class="label">Account Name</div><div class="value"><?php echo htmlspecialchars($receipt['owner_name']); ?></div>
            <div class="label">Transfer From</div><div class="value"><?php
                $tp = $receipt['tenant_phone'] ?? '';
                echo $tp ? (str_repeat('X', max(0, strlen($tp)-4)) . substr($tp, -4)) : 'N/A';
            ?></div>
            <div class="label">Amount</div><div class="value">BDT <?php echo number_format($receipt['amount']); ?></div>
            <div class="label">Transfer When</div><div class="value"><?php echo htmlspecialchars($receipt['paid_at']); ?></div>
            <div class="label full-row">Purpose</div><div class="value full-row" style="text-align:left; font-weight:400; color:#333;">Rent payment for <?php echo htmlspecialchars($receipt['billing_month']); ?> (Agreement <?php echo htmlspecialchars($receipt['agreement_id']); ?>)</div>
        </div>

        <p class="note">This is computer generated receipt no signature required. Electronic Receipt owns no official legal effect, You may go to branch to get the paper receipt.</p>

        <div class="no-print print-actions">
            <button onclick="window.print()" class="print-btn">Print Receipt</button>
            <a href="tenant_bills.php" style="margin-left:12px; color:#333;">Back to Bills</a>
        </div>
    </div>
    <div class="footer-bar">Copyright © <?php echo date('Y'); ?> House Rent Management System. All rights reserved.</div>
</div>
</body>
</html>
