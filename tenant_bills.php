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

$sql = "SELECT mb.agreement_id, mb.billing_month, mb.base_rent, mb.maintanance, mb.electricity, mb.gas, mb.water, mb.service_charge, mb.total_amount, mb.payment_status, f.location, a.advance
        FROM monthly_bill mb
        JOIN agreement a ON mb.agreement_id = a.agreement_id
        JOIN links l ON a.agreement_id = l.agreement_id
        JOIN flat f ON l.flat_id = f.flat_id
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
        .bill-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .bill-table th, .bill-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .bill-table th { background: #f5f5f5; }
        .status-paid { color: #155724; font-weight: 700; }
        .status-unpaid { color: #856404; font-weight: 700; }
        .btn-pay { display: inline-block; padding: 6px 12px; background: #28a745; color: #fff; border-radius: 4px; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>Your Bills</h1>
    <p>Review your rent and utility bill details with payment status.</p>

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
                    <th>Base Rent</th>
                    <th>Electricity</th>
                    <th>Gas</th>
                    <th>Water</th>
                    <th>Service Charge</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['billing_month']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo number_format($row['base_rent']); ?></td>
                        <td><?php echo number_format($row['electricity']); ?></td>
                        <td><?php echo number_format($row['gas']); ?></td>
                        <td><?php echo number_format($row['water']); ?></td>
                        <td><?php echo number_format($row['service_charge']); ?></td>
                        <td><?php echo number_format($row['total_amount']); ?></td>
                        <td class="status-<?php echo strtolower($row['payment_status']); ?>"><?php echo htmlspecialchars($row['payment_status']); ?></td>
                        <td>
                            <?php if ($row['payment_status'] === 'Unpaid'): ?>
                                <a href="actions/pay_bill.php?agreement_id=<?php echo urlencode($row['agreement_id']); ?>&billing_month=<?php echo urlencode($row['billing_month']); ?>" class="btn-pay">Pay</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
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
