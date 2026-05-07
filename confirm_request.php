<?php
session_start();
include("db.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$error = '';
$success = '';
$request = null;
$request_id = 0;

function ensureRequestOfferColumns($conn)
{
    $offerAdvanceCheck = "SELECT COUNT(*) AS column_count
                          FROM INFORMATION_SCHEMA.COLUMNS
                          WHERE TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = 'request'
                            AND COLUMN_NAME = 'offer_advance'";
    $offerStartDateCheck = "SELECT COUNT(*) AS column_count
                            FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE()
                              AND TABLE_NAME = 'request'
                              AND COLUMN_NAME = 'offer_start_date'";

    $advanceResult = $conn->query($offerAdvanceCheck);
    $advanceRow = $advanceResult ? $advanceResult->fetch_assoc() : null;
    if (!$advanceRow || intval($advanceRow['column_count']) === 0) {
        $conn->query("ALTER TABLE request ADD COLUMN offer_advance decimal(10,2) DEFAULT NULL AFTER request_status");
    }

    $dateResult = $conn->query($offerStartDateCheck);
    $dateRow = $dateResult ? $dateResult->fetch_assoc() : null;
    if (!$dateRow || intval($dateRow['column_count']) === 0) {
        $conn->query("ALTER TABLE request ADD COLUMN offer_start_date date DEFAULT NULL AFTER offer_advance");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    if ($request_id <= 0 && isset($_GET['id'])) {
        $request_id = intval($_GET['id']);
    }
} else {
    $request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $advance = isset($_POST['advance']) ? floatval($_POST['advance']) : 0;
    $start_date = trim($_POST['start_date'] ?? '');

    if ($request_id <= 0) {
        $error = 'Invalid request selected.';
    }
    if ($start_date === '') {
        $error = 'Please enter a valid start date.';
    }

    if (!$error) {
        ensureRequestOfferColumns($conn);

        $stmt = $conn->prepare("SELECT r.tenant_id, r.flat_id, r.request_status, f.owner_id, f.location, f.asking_rent, u.name AS tenant_name
                                 FROM request r
                                 JOIN flat f ON r.flat_id = f.flat_id
                                 JOIN user u ON r.tenant_id = u.user_id
                                 WHERE r.request_id = ?");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if (!$request) {
            $error = 'Request not found.';
        } elseif ($request['owner_id'] != $owner_id) {
            $error = 'You are not authorized to confirm this request.';
        } elseif ($request['request_status'] !== 'Pending') {
            $error = 'This request is already processed.';
        }
    }

    if ($error && $request_id > 0 && !$request) {
        $stmt = $conn->prepare("SELECT r.request_id, r.tenant_id, r.flat_id, r.request_status, f.owner_id, f.location, f.asking_rent, u.name AS tenant_name
                                 FROM request r
                                 JOIN flat f ON r.flat_id = f.flat_id
                                 JOIN user u ON r.tenant_id = u.user_id
                                 WHERE r.request_id = ?");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$error) {
        $conn->begin_transaction();
        try {
            $updateRequest = $conn->prepare("UPDATE request SET request_status='In Process', offer_advance = ?, offer_start_date = ? WHERE request_id = ?");
            $updateRequest->bind_param('dsi', $advance, $start_date, $request_id);
            $updateRequest->execute();
            $updateRequest->close();

            $conn->commit();
            $success = '✓ Offer sent successfully! The tenant will see this offer in their dashboard. They will need to accept the offer and pay the advance amount (BDT ' . number_format($advance) . ') to confirm the rental agreement.';
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Unable to confirm request: ' . $e->getMessage();
        }
    }
} else {
    if ($request_id > 0) {
        $stmt = $conn->prepare("SELECT r.request_id, r.tenant_id, r.flat_id, r.request_status, f.owner_id, f.location, f.asking_rent, u.name AS tenant_name
                                 FROM request r
                                 JOIN flat f ON r.flat_id = f.flat_id
                                 JOIN user u ON r.tenant_id = u.user_id
                                 WHERE r.request_id = ?");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if (!$request || $request['owner_id'] != $owner_id) {
            header("Location: view_requests.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Rental Request</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8;--success:#d1fae5;--error:#fee2e2}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .page-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:32px;text-align:center}
        .page-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .form-container{max-width:600px;margin:0 auto;padding:0 16px 32px;background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:32px}
        .message-alert{padding:14px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;border-left:4px solid}
        .success-message{background:var(--success);border-color:var(--accent);color:#065f46}
        .error-message{background:var(--error);border-color:#ef4444;color:#991b1b}
        .form-section{margin-bottom:24px}
        .form-section-title{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #e5e7eb}
        .info-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #e5e7eb}
        .info-row:last-child{border-bottom:none}
        .info-label{color:#6b7280;font-weight:600;font-size:14px}
        .info-value{color:#1f2937;font-weight:500;font-size:14px}
        .form-group{margin-bottom:20px;display:flex;flex-direction:column}
        .form-group label{margin-bottom:8px;font-weight:600;color:#0f172a;font-size:14px}
        .form-group input{padding:12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,0.1)}
        .form-group input:read-only{background:#f3f4f6;color:#6b7280;cursor:not-allowed}
        .form-actions{display:flex;gap:12px;margin-top:24px}
        .btn-submit{flex:1;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;transition:all 0.2s}
        .btn-submit:hover{background:#059669;transform:translateY(-2px);box-shadow:0 6px 16px rgba(16,185,129,0.3)}
        .btn-back{flex:1;padding:12px;background:#6b7280;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:all 0.2s}
        .btn-back:hover{background:#4b5563;transform:translateY(-2px)}
        .form-title{font-size:20px;font-weight:700;color:#0f172a;margin-bottom:24px;text-align:center}
        .back-link{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;background:#f3f4f6;border-radius:8px;text-decoration:none;color:var(--primary);font-weight:600;margin-top:16px;transition:all 0.2s}
        .back-link:hover{background:#e5e7eb;transform:translateY(-2px)}
        @media(max-width:600px){.form-container{padding:24px}.form-actions{flex-direction:column}}
    </style>
</head>
<body>
<div class="page-header">
    <h1>🤝 Confirm Rental Request</h1>
</div>

<div class="form-container">
    <?php if ($error): ?>
        <div class="message-alert error-message">❌ <?php echo htmlspecialchars($error); ?></div>
        <a href="view_requests.php" class="back-link">← Back to Requests</a>
    <?php elseif ($success): ?>
        <div class="message-alert success-message">✓ <?php echo htmlspecialchars($success); ?></div>
        <a href="view_requests.php" class="back-link">← Back to Requests</a>
    <?php elseif ($request): ?>
        <h2 class="form-title">📋 Make Rental Offer</h2>
        
        <div class="form-section">
            <div class="form-section-title">Tenant & Property Details</div>
            <div class="info-row">
                <span class="info-label">👤 Tenant Name</span>
                <span class="info-value"><?php echo htmlspecialchars($request['tenant_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">🏠 Property Location</span>
                <span class="info-value"><?php echo htmlspecialchars($request['location']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">📊 Request Status</span>
                <span class="info-value"><strong><?php echo htmlspecialchars($request['request_status']); ?></strong></span>
            </div>
        </div>

        <?php if ($request['request_status'] === 'Pending'): ?>
            <form method="post">
                <div class="form-section">
                    <div class="form-section-title">Offer Terms</div>
                    
                    <input type="hidden" name="request_id" value="<?php echo intval($request['request_id']); ?>">

                    <div class="form-group">
                        <label for="advance">💰 Security Deposit (Advance) *</label>
                        <input type="number" id="advance" name="advance" min="0" step="0.01" value="0" required placeholder="e.g., 50000">
                        <small style="color:#6b7280;font-size:12px;margin-top:4px;display:block">The tenant must pay this amount before the agreement is finalized</small>

                    <div class="form-group">
                        <label for="start_date">📆 Agreement Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">✓ Send Offer</button>
                        <a href="view_requests.php" class="btn-back">✕ Cancel</a>
                    </div>
                </div>
            </form>
            <p style="font-size:13px;color:#6b7280;margin-top:16px;background:#f9fafb;padding:10px;border-radius:6px">
                📌 <strong>Note:</strong> After you send this offer, the tenant will see it in their dashboard. They will need to accept and pay the advance amount to confirm the agreement.
            </p>
        <?php else: ?>
            <div class="message-alert error-message">
                ⚠️ This request is already in progress or has been processed.
            </div>
            <a href="view_requests.php" class="back-link">← Back to Requests</a>
        <?php endif; ?>
    <?php else: ?>
        <div class="message-alert error-message">
            ❌ Request not found or has been deleted.
        </div>
        <a href="view_requests.php" class="back-link">← Back to Requests</a>
    <?php endif; ?>
</div>
</body>
</html>
