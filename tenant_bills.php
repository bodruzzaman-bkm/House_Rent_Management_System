<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: login.php');
    exit();
}

$message = $_SESSION['payment_message'] ?? '';
$message_type = $_SESSION['payment_message_type'] ?? '';
unset($_SESSION['payment_message'], $_SESSION['payment_message_type']);

$tenant_id = $_SESSION['user_id'];

$sql = "SELECT mb.agreement_id, mb.billing_month, mb.base_rent, mb.maintanance, mb.electricity, mb.gas, mb.water, mb.service_charge, mb.total_amount, mb.payment_status, f.location, a.advance, uo.name AS owner_name
        FROM monthly_bill mb
        JOIN agreement a ON mb.agreement_id = a.agreement_id
        JOIN links l ON a.agreement_id = l.agreement_id
        JOIN flat f ON l.flat_id = f.flat_id
    JOIN user uo ON a.owner_id = uo.user_id
        WHERE l.tenant_id = ?
        ORDER BY mb.billing_month DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Bills</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2b7cff;--accent:#28a745;--muted:#6b7280;--bg:#f6f8fa}
        body{background:var(--bg)}
        .bill-table { width: 100%; border-collapse: collapse; margin-top: 18px; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 6px 18px rgba(31,41,55,0.06); }
        .bill-table thead th { text-align:left; padding:14px 16px; font-weight:600; font-size:14px; color:#374151; background:linear-gradient(180deg,#fbfdff,#f5f8ff); }
        .bill-table tbody td { padding:12px 16px; border-top:1px solid #eef2f7; color:#111827; vertical-align:middle }
        .bill-table tbody tr:hover { background: #fbfdff }
        .status-paid { background:#e6ffed; color:#017a2e; padding:6px 10px; border-radius:999px; display:inline-block; font-weight:700; font-size:13px }
        .status-unpaid { background:#fff7ed; color:#7a4b00; padding:6px 10px; border-radius:999px; display:inline-block; font-weight:700; font-size:13px }
        .btn-pay, .btn-receipt { display:inline-block; padding:8px 12px; color:#fff; border-radius:6px; text-decoration:none; font-size:13px }
        .btn-pay { background:var(--accent) }
        .btn-receipt { background:var(--primary) }
        .actions { display:flex; gap:8px; align-items:center }
        .container { max-width:980px; margin:30px auto; padding:0 16px }
        h1{color:#111827;margin-bottom:6px}
        p.lead{color:var(--muted);margin-top:0}
        @media (max-width:800px){ .bill-table thead{display:none} .bill-table, .bill-table tbody, .bill-table tr, .bill-table td{display:block;width:100%} .bill-table tr{margin-bottom:12px;border-radius:8px;background:#fff;padding:12px;box-shadow:0 4px 10px rgba(31,41,55,0.04)} .bill-table td{border:none;padding:8px 10px} .bill-table td:before{content:attr(data-label);float:left;font-weight:600;color:#6b7280} .actions{justify-content:flex-start} }
    </style>
</head>
<body>
    <div class="container">
    <h1>Your Bills</h1>
    <p class="lead">Review monthly bills and download receipts for payments.</p>

    <?php if ($message): ?>
        <div style="max-width: 700px; margin: 20px auto; padding: 12px 16px; border-radius: 6px; background: <?php echo $message_type === 'success' ? '#d4edda' : ($message_type === 'error' ? '#f8d7da' : '#fff3cd'); ?>; border: 1px solid <?php echo $message_type === 'success' ? '#c3e6cb' : ($message_type === 'error' ? '#f5c6cb' : '#ffeeba'); ?>; color: <?php echo $message_type === 'success' ? '#155724' : ($message_type === 'error' ? '#721c24' : '#856404'); ?>;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p>No bills found yet. Please check back after your owner generates monthly bills.</p>
    <?php else: ?>
        <table class="bill-table">
            <thead>
                <tr>
                    <th>Billing Month</th>
                    <th>Location</th>
                    <th>Owner</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="main-row" data-agreement="<?php echo htmlspecialchars($row['agreement_id']); ?>">
                        <td data-label="Billing Month"><?php echo htmlspecialchars($row['billing_month']); ?></td>
                        <td data-label="Location"><?php echo htmlspecialchars($row['location']); ?></td>
                        <td data-label="Owner"><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td data-label="Total">BDT <?php echo number_format($row['total_amount']); ?></td>
                        <td data-label="Status"><span class="status-<?php echo strtolower($row['payment_status']); ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span></td>
                        <td data-label="Action">
                            <div class="actions">
                                <?php if ($row['payment_status'] === 'Unpaid'): ?>
                                    <a href="actions/pay_bill.php?agreement_id=<?php echo urlencode($row['agreement_id']); ?>&billing_month=<?php echo urlencode($row['billing_month']); ?>" class="btn-pay" title="Pay">
                                        <!-- pay icon -->
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:6px"><path d="M21 7H3v10h18V7z" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3v4" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 3v4" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        Pay
                                    </a>
                                <?php else: ?>
                                    <a href="view_receipt.php?agreement_id=<?php echo urlencode($row['agreement_id']); ?>&billing_month=<?php echo urlencode($row['billing_month']); ?>" class="btn-receipt" title="View Receipt">
                                        <!-- receipt icon -->
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:6px"><path d="M21 6l-2 2-2-2-2 2-2-2-2 2-2-2-2 2v10h18V6z" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        View Receipt
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="tenant_dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>
