<?php
session_start();

if (!isset($_SESSION['paid_bill_amount']) || !isset($_SESSION['paid_bill_month']) || !isset($_SESSION['paid_bill_location'])) {
    header('Location: tenant_bills.php');
    exit();
}

$total = $_SESSION['paid_bill_amount'];
$month = $_SESSION['paid_bill_month'];
$location = $_SESSION['paid_bill_location'];
$_SESSION['payment_message'] = 'Payment submitted successfully. The bill will remain Unpaid until the owner marks it as Paid.';
$_SESSION['payment_message_type'] = 'success';

unset($_SESSION['paid_bill_amount'], $_SESSION['paid_bill_month'], $_SESSION['paid_bill_location']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Submitted Successfully</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
        .success-container{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);padding:48px 32px;max-width:500px;text-align:center}
        .success-icon{font-size:56px;margin-bottom:16px;animation:scaleIn 0.4s ease-out}
        @keyframes scaleIn{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
        .success-title{font-size:24px;font-weight:700;color:#0f172a;margin:0 0 8px}
        .success-subtitle{font-size:14px;color:#6b7280;margin:0 0 24px}
        .info-box{background:#f9fafb;border-radius:8px;padding:20px;margin:24px 0;border-left:4px solid var(--primary)}
        .info-row{display:flex;justify-content:space-between;padding:10px 0;font-size:14px}
        .info-label{color:#6b7280;font-weight:600}
        .info-value{color:#1f2937;font-weight:500;text-align:right}
        .amount-row{color:var(--accent);font-weight:700;font-size:18px;margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb}
        .status-badge{display:inline-block;padding:8px 14px;background:#fff7ed;color:#92400e;border-radius:6px;font-weight:700;font-size:12px;margin-top:12px}
        .note-box{background:#ede9fe;border-left:4px solid #8b5cf6;padding:14px;border-radius:6px;margin:20px 0;text-align:left;font-size:13px;color:#5b21b6}
        .action-links{display:flex;gap:12px;margin-top:32px;flex-direction:column}
        .action-link{padding:12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;cursor:pointer;border:none;transition:all 0.2s;display:inline-flex;align-items:center;justify-content:center;gap:6px}
        .link-primary{background:var(--primary);color:#fff}
        .link-primary:hover{background:#1e40af;transform:translateY(-2px)}
        .link-secondary{background:var(--accent);color:#fff}
        .link-secondary:hover{background:#059669;transform:translateY(-2px)}
        @media(max-width:600px){.success-container{padding:32px 20px}.action-links{flex-direction:column}}
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1 class="success-title">Payment Submitted!</h1>
        <p class="success-subtitle">Your payment has been recorded successfully.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">📍 Property Location</span>
                <span class="info-value"><?php echo htmlspecialchars($location); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">📅 Billing Month</span>
                <span class="info-value"><?php echo htmlspecialchars($month); ?></span>
            </div>
            <div class="amount-row">
                💰 BDT <?php echo htmlspecialchars(number_format($total, 2)); ?>
            </div>
        </div>

        <div class="status-badge">⏳ Pending Owner Confirmation</div>

        <div class="note-box">
            <strong>📌 Note:</strong> Your payment has been submitted. The owner will verify and mark it as paid. You'll see the updated status in your bills.
        </div>

        <div class="action-links">
            <a href="tenant_bills.php" class="action-link link-primary">📋 View My Bills</a>
            <a href="tenant_dashboard.php" class="action-link link-secondary">🏠 Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
