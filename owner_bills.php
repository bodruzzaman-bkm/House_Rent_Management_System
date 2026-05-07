<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: login.php');
    exit();
}

$message = $_SESSION['owner_payment_message'] ?? '';
$message_type = $_SESSION['owner_payment_message_type'] ?? '';
unset($_SESSION['owner_payment_message'], $_SESSION['owner_payment_message_type']);

$owner_id = $_SESSION['user_id'];

$sql = "SELECT mb.agreement_id, mb.billing_month, mb.base_rent, mb.maintanance, mb.electricity, mb.gas, mb.water, mb.service_charge, mb.total_amount, mb.payment_status, f.location, u.name AS tenant_name
        FROM monthly_bill mb
        JOIN agreement a ON mb.agreement_id = a.agreement_id
        JOIN links l ON a.agreement_id = l.agreement_id
        JOIN flat f ON l.flat_id = f.flat_id
        JOIN user u ON l.tenant_id = u.user_id
        WHERE a.owner_id = ?
        ORDER BY mb.billing_month DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Bill Management</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2b7cff;--accent:#16a34a;--muted:#6b7280;--card:#ffffff}
        body{background:#f4f6f8}
        .container { max-width:1000px;margin:28px auto;padding:0 18px }
        h1{font-size:22px;margin-bottom:6px;color:#0f172a}
        p.lead{color:var(--muted);margin-top:0}
        .bill-table { width: 100%; border-collapse: collapse; margin-top: 18px; background:var(--card); border-radius:10px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,0.06); }
        .bill-table thead th{ text-align:left;padding:14px 16px;font-weight:600;color:#374151;background:linear-gradient(180deg,#fbfdff,#f5f8ff)}
        .bill-table tbody td{ padding:12px 16px;border-top:1px solid #eef2f7;color:#0f172a;vertical-align:middle }
        .bill-table tbody tr:hover{ background:#fcfeff }
        .status-paid{ background:#ecfdf5;color:#065f46;padding:6px 10px;border-radius:999px;font-weight:700 }
        .status-unpaid{ background:#fff7ed;color:#92400e;padding:6px 10px;border-radius:999px;font-weight:700 }
        .btn-action{ display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;color:#fff;text-decoration:none;font-weight:600 }
        .btn-mark{ background:var(--primary) }
        .btn-view{ background:var(--accent) }
        .message-inline{ max-width:700px;margin:12px auto;padding:12px 16px;border-radius:8px }
        @media (max-width:820px){ .bill-table thead{display:none} .bill-table, .bill-table tbody, .bill-table tr, .bill-table td{display:block;width:100%} .bill-table tr{margin-bottom:12px;border-radius:8px;background:#fff;padding:12px;box-shadow:0 4px 10px rgba(15,23,42,0.04)} .bill-table td{border:none;padding:8px 10px} .bill-table td:before{content:attr(data-label);float:left;font-weight:600;color:#6b7280} }
    </style>
</head>
<body>
<div class="container">
    <h1>Owner Bill Management</h1>
    <p class="lead">Review generated bills, mark unpaid bills as paid, and view receipts.</p>

    <?php if ($message): ?>
        <div class="message-inline" style="background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; border:1px solid <?php echo $message_type === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>; color:<?php echo $message_type === 'success' ? '#065f46' : '#721c24'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php if (!empty($_SESSION['owner_receipt_number'])): ?>
            <div class="message-inline" style="background:#ecfdf5;border:1px solid #bbf7d0;color:#065f46;">
                Receipt generated — <a href="view_receipt.php?token=<?php echo urlencode($_SESSION['owner_receipt_number']); ?>">View Receipt</a>
            </div>
            <?php unset($_SESSION['owner_receipt_number']); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p>No bills found. Generate monthly bills from the dashboard.</p>
    <?php else: ?>
        <table class="bill-table">
            <thead>
                <tr>
                    <th>Agreement</th>
                    <th>Tenant</th>
                    <th>Location</th>
                    <th>Billing Month</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Agreement"><?php echo htmlspecialchars($row['agreement_id']); ?></td>
                        <td data-label="Tenant"><?php echo htmlspecialchars($row['tenant_name']); ?></td>
                        <td data-label="Location"><?php echo htmlspecialchars($row['location']); ?></td>
                        <td data-label="Billing Month"><?php echo htmlspecialchars($row['billing_month']); ?></td>
                        <td data-label="Total">BDT <?php echo number_format($row['total_amount']); ?></td>
                        <td data-label="Status"><span class="status-<?php echo strtolower($row['payment_status']); ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span></td>
                        <td data-label="Action">
                            <?php if ($row['payment_status'] === 'Unpaid'): ?>
                                <a href="actions/mark_bill_paid.php?agreement_id=<?php echo urlencode($row['agreement_id']); ?>&billing_month=<?php echo urlencode($row['billing_month']); ?>" class="btn-action btn-mark">
                                    <!-- mark icon -->
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M21 7H3v10h18V7z" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 3v4" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 3v4" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>Mark Paid</span>
                                </a>
                            <?php else: ?>
                                <a href="view_receipt.php?agreement_id=<?php echo urlencode($row['agreement_id']); ?>&billing_month=<?php echo urlencode($row['billing_month']); ?>" class="btn-action btn-view">
                                    <!-- view icon -->
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M21 6l-2 2-2-2-2 2-2-2-2 2-2-2-2 2v10h18V6z" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>View Receipt</span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="owner_dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>
