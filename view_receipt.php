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
        :root{--primary:#2563eb;--accent:#10b981;--dark:#0f172a;--light:#f4f6f8}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--light);margin:0;padding:16px}
        .receipt-wrap{max-width:800px;margin:20px auto;background:#fff;box-shadow:0 8px 32px rgba(0,0,0,0.1);border-radius:12px;overflow:hidden}
        .r-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:28px 24px;display:flex;align-items:center;justify-content:space-between}
        .r-brand{display:flex;align-items:center;gap:14px}
        .r-brand .logo{width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px}
        .r-title{font-size:20px;font-weight:700;letter-spacing:-0.5px}
        .r-subtitle{font-size:12px;color:rgba(255,255,255,0.85);margin-top:2px}
        .r-date{text-align:right;font-size:13px;color:rgba(255,255,255,0.9)}
        .r-sub{background:rgba(102,126,234,0.1);color:var(--dark);padding:12px 24px;font-weight:600;border-left:4px solid var(--accent)}
        .receipt{padding:32px 24px}
        .receipt h2{margin:0 0 20px;font-size:18px;font-weight:700;color:var(--dark);letter-spacing:0.5px}
        .details{display:grid;grid-template-columns:1fr 1fr;gap:14px 24px}
        .label{color:#6b7280;font-weight:600;font-size:13px;text-transform:uppercase;letter-spacing:0.5px}
        .value{text-align:right;font-weight:600;color:var(--dark);font-size:14px}
        .amount-value{color:var(--accent);font-size:18px;font-weight:700}
        .full-row{grid-column:1 / -1}
        .note{margin-top:24px;padding:12px 14px;background:var(--light);border-left:4px solid var(--accent);color:#6b7280;font-size:13px;border-radius:6px;line-height:1.5}
        .print-actions{margin-top:24px;display:flex;gap:12px;align-items:center}
        .print-btn{padding:11px 18px;background:linear-gradient(135deg,var(--accent),#059669);color:#fff;border:none;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:6px}
        .print-btn:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(16,185,129,0.3)}
        .back-link{color:var(--primary);text-decoration:none;font-weight:600;font-size:14px;transition:color 0.2s}
        .back-link:hover{color:#1e40af;text-decoration:underline}
        .footer-bar{background:linear-gradient(135deg,#0f172a,#1f2937);color:#d1d5db;padding:14px 24px;font-size:12px;text-align:center}
        @media print{.no-print{display:none}.receipt-wrap{box-shadow:none;margin:0;border-radius:0}.print-actions{display:none}}
        @media(max-width:600px){.receipt-wrap{border-radius:0;box-shadow:none;margin:0}.r-header{flex-direction:column;gap:12px;text-align:center}.r-date{text-align:center}.details{grid-template-columns:1fr;gap:10px}.value{text-align:left}}
    </style>
</head>
<body>
<div class="receipt-wrap">
    <div class="r-header">
        <div class="r-brand">
            <div class="logo">🏦</div>
            <div>
                <div class="r-title">House Rent System</div>
                <div class="r-subtitle">Official Payment Receipt</div>
            </div>
        </div>
        <div class="r-date">
            📅 <?php echo date('d M Y • H:i', strtotime($receipt['paid_at'])); ?>
        </div>
    </div>
    <div class="r-sub">👤 Recipient: <?php echo htmlspecialchars($receipt['owner_name'] ?? 'N/A'); ?></div>
    <div class="receipt">
        <h2>✓ Payment Receipt</h2>
        <div class="details">
            <div class="label">Receipt Number</div><div class="value"><?php echo htmlspecialchars($receipt['receipt_number']); ?></div>
            
            <div class="label">Paid By (Tenant)</div><div class="value"><?php echo htmlspecialchars($receipt['tenant_name'] ?? 'N/A'); ?></div>
            
            <div class="label">Paid To (Owner)</div><div class="value"><?php echo htmlspecialchars($receipt['owner_name']); ?></div>
            
            <div class="label">Billing Period</div><div class="value"><?php echo htmlspecialchars($receipt['billing_month']); ?></div>
            
            <div class="label">Payment Amount</div><div class="value amount-value">BDT <?php echo number_format($receipt['amount']); ?></div>
            
            <div class="label">Payment Date</div><div class="value"><?php echo htmlspecialchars($receipt['paid_at']); ?></div>
            
            <div class="label">Agreement ID</div><div class="value">#<?php echo htmlspecialchars($receipt['agreement_id']); ?></div>
            
            <div class="label">Payment Status</div><div class="value" style="color:#10b981;font-weight:700">✓ Paid</div>
            
            <div class="label full-row">📝 Description</div>
            <div class="value full-row" style="text-align:left;font-weight:400;color:#4b5563">Rent payment for <?php echo htmlspecialchars($receipt['billing_month']); ?> | Agreement ID: <?php echo htmlspecialchars($receipt['agreement_id']); ?></div>
        </div>

        <p class="note">
            💡 <strong>This is a computer-generated receipt.</strong> No signature required. Electronic receipts are valid proof of payment. For a paper receipt, please contact your property manager or visit the office.
        </p>

        <div class="no-print print-actions">
            <button onclick="window.print()" class="print-btn">🖨️ Print Receipt</button>
            <a href="tenant_bills.php" class="back-link">← Back to Bills</a>
        </div>
    </div>
    <div class="footer-bar">© <?php echo date('Y'); ?> House Rent Management System. All rights reserved. | Secure Document</div>
</div>
</body>
</html>
