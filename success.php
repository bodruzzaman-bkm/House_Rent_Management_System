<?php
session_start();

if (!isset($_SESSION['bill_total']) || !isset($_SESSION['bill_month'])) {
    header("Location: generate_bill.php");
    exit();
}

$total = $_SESSION['bill_total'];
$month = $_SESSION['bill_month'];
$message = $_SESSION['bill_message'] ?? '';

unset($_SESSION['bill_total'], $_SESSION['bill_month'], $_SESSION['bill_message']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Generated Successfully</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
        .success-container{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.08);padding:48px 32px;max-width:500px;text-align:center}
        .success-icon{font-size:56px;margin-bottom:16px;animation:scaleIn 0.4s ease-out}
        @keyframes scaleIn{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
        .success-title{font-size:24px;font-weight:700;color:#0f172a;margin:0 0 8px}
        .success-subtitle{font-size:14px;color:#6b7280;margin:0 0 24px}
        .bill-details{background:#f9fafb;border-radius:8px;padding:20px;margin:24px 0;border-left:4px solid var(--accent)}
        .detail-row{display:flex;justify-content:space-between;padding:10px 0;font-size:14px}
        .detail-label{color:#6b7280;font-weight:600}
        .detail-value{color:#1f2937;font-weight:500}
        .detail-amount{color:var(--accent);font-weight:700;font-size:18px}
        .detail-row:not(:last-child){border-bottom:1px solid #e5e7eb}
        .status-badge{display:inline-block;padding:6px 12px;background:#fff7ed;color:#92400e;border-radius:6px;font-weight:700;font-size:12px;margin-top:12px}
        .action-links{display:flex;gap:12px;margin-top:32px;flex-wrap:wrap}
        .action-link{flex:1;min-width:140px;padding:12px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;cursor:pointer;border:none;transition:all 0.2s;display:inline-flex;align-items:center;justify-content:center;gap:6px}
        .link-primary{background:var(--primary);color:#fff}
        .link-primary:hover{background:#1e40af;transform:translateY(-2px)}
        .link-secondary{background:var(--accent);color:#fff}
        .link-secondary:hover{background:#059669;transform:translateY(-2px)}
        .link-tertiary{background:#6b7280;color:#fff}
        .link-tertiary:hover{background:#4b5563;transform:translateY(-2px)}
        @media(max-width:600px){.success-container{padding:32px 20px}.action-links{flex-direction:column}.action-link{width:100%}}
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1 class="success-title">Bill Generated Successfully!</h1>
        <p class="success-subtitle">The bill has been created and is pending payment.</p>

        <?php if ($message): ?>
            <p style="color:#10b981;font-weight:600;margin:12px 0"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="bill-details">
            <div class="detail-row">
                <span class="detail-label">📅 Month</span>
                <span class="detail-value"><?php echo htmlspecialchars($month); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">💰 Total Amount</span>
                <span class="detail-amount">BDT <?php echo htmlspecialchars(number_format($total, 2)); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">📊 Status</span>
                <span class="status-badge">⏱ Unpaid</span>
            </div>
        </div>

        <div class="action-links">
            <button class="action-link link-tertiary" onclick="window.history.back()">← Go Back</button>
            <a href="generate_bill.php" class="action-link link-secondary">📝 Generate Another</a>
        </div>
    </div>
</body>

</html>