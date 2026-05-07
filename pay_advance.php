<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: login.php');
    exit();
}

$tenant_id = $_SESSION['user_id'];
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;

// Get payment details from session (if coming from accept_rental_offer) or database (if directly accessed)
$pending = isset($_SESSION['pending_advance']) ? $_SESSION['pending_advance'] : null;

if ($request_id <= 0) {
    $_SESSION['request_message'] = 'Invalid advance payment request.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: tenant_dashboard.php');
    exit();
}

// Fetch request details from database
$stmt = $conn->prepare("SELECT r.request_id, r.offer_advance, r.offer_start_date, r.request_status, f.location, f.asking_rent, f.flat_id, f.owner_id
                        FROM request r
                        JOIN flat f ON r.flat_id = f.flat_id
                        WHERE r.request_id = ? AND r.tenant_id = ?");
$stmt->bind_param('ii', $request_id, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$request_row = $result->fetch_assoc();
$stmt->close();

if (!$request_row) {
    $_SESSION['request_message'] = 'Rental offer not found.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: tenant_dashboard.php');
    exit();
}

// Update session with latest data if not already set
if (!$pending) {
    $_SESSION['pending_advance'] = [
        'request_id' => $request_row['request_id'],
        'offer_advance' => $request_row['offer_advance'],
        'offer_start_date' => $request_row['offer_start_date'],
        'flat_id' => $request_row['flat_id'],
        'owner_id' => $request_row['owner_id'],
        'asking_rent' => $request_row['asking_rent'],
        'location' => $request_row['location']
    ];
    $pending = $_SESSION['pending_advance'];
}

// Check request status
if ($request_row['request_status'] === 'Approved') {
    $_SESSION['request_message'] = '✓ Payment already confirmed and agreement created.';
    $_SESSION['request_message_type'] = 'success';
    header('Location: tenant_dashboard.php');
    exit();
} elseif ($request_row['request_status'] !== 'Pending Advance Payment' && $request_row['request_status'] !== 'In Process') {
    $_SESSION['request_message'] = 'This rental offer is no longer available.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: tenant_dashboard.php');
    exit();
}

$advance_amount = $request_row['offer_advance'];
$location = $request_row['location'];
$start_date = $request_row['offer_start_date'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Advance - Rental Agreement</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .page-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:32px;text-align:center}
        .page-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .page-header p{font-size:15px;opacity:0.9;margin:0}
        .container{max-width:700px;margin:0 auto;padding:0 16px 32px}
        .card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:28px;margin-bottom:20px}
        .card-title{font-size:18px;font-weight:700;color:#0f172a;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f0f0}
        .info-row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e5e7eb}
        .info-row:last-child{border-bottom:none}
        .info-label{color:#6b7280;font-weight:600;font-size:14px}
        .info-value{color:#1f2937;font-weight:500;text-align:right}
        .amount-box{background:linear-gradient(135deg,#f0f9ff,#ecfdf5);border:2px solid var(--primary);border-radius:10px;padding:20px;margin:20px 0;text-align:center}
        .amount-label{color:#6b7280;font-size:13px;font-weight:600}
        .amount-value{font-size:32px;font-weight:700;color:var(--accent);margin:8px 0}
        .warning-box{background:#fff7ed;border-left:4px solid #f59e0b;padding:14px;border-radius:6px;margin:20px 0;color:#92400e;font-size:14px}
        .success-box{background:#ecfdf5;border-left:4px solid var(--accent);padding:14px;border-radius:6px;margin:20px 0;color:#065f46;font-size:14px}
        .button-group{display:flex;gap:12px;margin-top:20px}
        .btn-pay{flex:1;padding:13px;background:linear-gradient(135deg,var(--accent),#059669);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:14px;cursor:pointer;transition:all 0.2s}
        .btn-pay:hover{transform:translateY(-2px);box-shadow:0 8px 16px rgba(16,185,129,0.3)}
        .btn-cancel{flex:1;padding:13px;background:#6b7280;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:all 0.2s}
        .btn-cancel:hover{background:#4b5563;transform:translateY(-2px)}
        .status-badge{display:inline-block;padding:6px 12px;background:#fef3c7;color:#92400e;border-radius:6px;font-weight:700;font-size:12px;margin-top:10px}
    </style>
</head>
<body>
<div class="page-header">
    <h1>💳 Pay Advance Amount</h1>
    <p>Complete your rental agreement payment</p>
</div>

<div class="container">
    <?php if ($request_row['request_status'] === 'Approved'): ?>
        <div class="card">
            <div class="card-title">✓ Payment Already Completed</div>
            <div class="success-box">
                🎉 Your rental agreement has been confirmed! Your advance payment was successfully processed and your agreement is now active.
            </div>
            <div style="text-align:center;margin-top:20px">
                <a href="tenant_dashboard.php" class="btn-pay" style="display:inline-block;width:auto;padding:10px 20px">← Back to Dashboard</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-title">📍 Property Details</div>
            <div class="info-row">
                <span class="info-label">Location</span>
                <span class="info-value"><?php echo htmlspecialchars($location); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Agreement Start Date</span>
                <span class="info-value"><?php echo htmlspecialchars($start_date); ?></span>
            </div>
        </div>

        <div class="card">
            <div class="card-title">💰 Payment Required</div>
            <div class="amount-box">
                <div class="amount-label">Advance Payment (Security Deposit)</div>
                <div class="amount-value">BDT <?php echo number_format($advance_amount, 0); ?></div>
            </div>
            <div class="warning-box">
                ⚠️ <strong>Important:</strong> You must pay this advance amount to confirm your rental agreement. After payment, your agreement will be activated and your first monthly bill will be generated.
            </div>
        </div>

        <div class="card">
            <div class="card-title">🔒 Payment Method</div>
            <p style="color:#6b7280;margin:0;font-size:14px;line-height:1.6">
                Please contact your property owner to arrange payment. You can pay through:
                <br>• Bank transfer
                <br>• Mobile banking
                <br>• Cash (if owner prefers)
                <br>• Any other agreed method
            </p>

            <form action="actions/process_advance_payment.php" method="POST" style="margin-top:20px">
                <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                <div style="display:flex;align-items:center;gap:12px;padding:14px;background:#f9fafb;border-radius:8px;margin-bottom:16px">
                    <input type="checkbox" id="confirm_payment" name="confirm_payment" value="1" required style="width:18px;height:18px;cursor:pointer">
                    <label for="confirm_payment" style="margin:0;cursor:pointer;font-size:14px;color:#4b5563">
                        ✓ I confirm that I have paid BDT <?php echo number_format($advance_amount, 0); ?> to the owner
                    </label>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn-pay">✓ Confirm Payment</button>
                    <a href="tenant_dashboard.php" class="btn-cancel">✕ Cancel</a>
                </div>
            </form>

            <p style="font-size:13px;color:#6b7280;margin-top:12px;text-align:center">
                After you confirm payment, your rental agreement will be activated.
            </p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
