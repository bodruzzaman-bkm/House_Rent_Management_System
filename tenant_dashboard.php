<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$message = $_SESSION['request_message'] ?? '';
$message_type = $_SESSION['request_message_type'] ?? '';
unset($_SESSION['request_message'], $_SESSION['request_message_type']);

$payment_message = $_SESSION['payment_message'] ?? '';
$payment_message_type = $_SESSION['payment_message_type'] ?? '';
unset($_SESSION['payment_message'], $_SESSION['payment_message_type']);

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

ensureRequestOfferColumns($conn);

$latestBillSql = "SELECT mb.agreement_id, mb.billing_month, mb.total_amount, mb.payment_status, f.location, uo.name AS owner_name
                  FROM monthly_bill mb
                  JOIN agreement a ON mb.agreement_id = a.agreement_id
                  JOIN links l ON a.agreement_id = l.agreement_id
                  JOIN flat f ON l.flat_id = f.flat_id
                  JOIN user uo ON a.owner_id = uo.user_id
                  WHERE l.tenant_id = ?
                  ORDER BY STR_TO_DATE(mb.billing_month, '%M-%Y') DESC, mb.agreement_id DESC
                  LIMIT 1";
$latestBillStmt = $conn->prepare($latestBillSql);
$latestBillStmt->bind_param('i', $_SESSION['user_id']);
$latestBillStmt->execute();
$latestBillResult = $latestBillStmt->get_result();
$latestBill = $latestBillResult->fetch_assoc();
$latestBillStmt->close();

$latestBillStatus = $latestBill['payment_status'] ?? '';
$latestBillIsPaid = strtolower((string) $latestBillStatus) === 'paid';

$offerSql = "SELECT r.request_id, r.offer_advance, r.offer_start_date, f.location, uo.name AS owner_name
             FROM request r
             JOIN flat f ON r.flat_id = f.flat_id
             JOIN user uo ON f.owner_id = uo.user_id
             WHERE r.tenant_id = ? AND r.request_status = 'In Process'
             ORDER BY r.date DESC";
$offerStmt = $conn->prepare($offerSql);
$offerStmt->bind_param('i', $_SESSION['user_id']);
$offerStmt->execute();
$offerResult = $offerStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg)}
        .dashboard-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:24px}
        .dashboard-header h1{font-size:28px;margin-bottom:4px}
        .dashboard-header p{font-size:15px;opacity:0.9}
        .navbar{background:#fff;padding:16px 24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin:0 24px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
        .navbar h2{margin:0;font-size:16px}
        .navbar-actions{display:flex;gap:10px;flex-wrap:wrap}
        .btn-nav{padding:10px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;transition:all 0.2s}
        .btn-browse{background:var(--primary);color:#fff}
        .btn-bills{background:var(--accent);color:#fff}
        .btn-logout{background:#ef4444;color:#fff}
        .btn-nav:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,0.1)}
        .dashboard-container{max-width:1000px;margin:0 auto;padding:0 16px 24px}
        .card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:18px;margin-bottom:24px}
        .dashboard-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:20px}
        .card-title{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px}
        .card-status-paid{background:#ecfdf5;color:#065f46;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;display:inline-block}
        .card-status-unpaid{background:#fff7ed;color:#92400e;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;display:inline-block}
        .card-row{display:flex;justify-content:space-between;margin:8px 0;font-size:14px}
        .card-label{color:#6b7280;font-weight:600}
        .card-value{color:#1f2937}
        .card-btn{display:inline-block;padding:10px 14px;background:var(--primary);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;margin-top:12px;transition:all 0.2s}
        .card-btn:hover{background:#1e40af}
        .btn-accept{background:var(--accent)}
        .btn-decline{background:#ef4444;margin-left:8px}
        .flat-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:20px;margin-bottom:16px;border-left:4px solid var(--primary)}
        .flat-card h3{margin-bottom:8px;color:#0f172a}
        .flat-card p{margin:6px 0;color:#4b5563;font-size:14px}
        .message-alert{margin-bottom:16px;padding:14px;border-radius:8px;font-size:14px;max-width:100%}
        .message-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
        .message-error{background:#fff7ed;border:1px solid #fed7aa;color:#92400e}
        @media(max-width:800px){.navbar{flex-direction:column;align-items:flex-start}.card-grid{grid-template-columns:1fr}}
    </style>
</head>

<body>

    <div class="dashboard-header">
        <h1>👋 Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>Manage your rental properties and bills</p>
    </div>

    <div class="navbar">
        <h2>Dashboard</h2>
        <div class="navbar-actions">
            <a href="browse_flats.php" class="btn-nav btn-browse">🏠 Browse Flats</a>
            <a href="tenant_bills.php" class="btn-nav btn-bills">💰 View Bills</a>
            <a href="actions/logout_action.php" class="btn-nav btn-logout">🚪 Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <?php if ($message): ?>
            <div class="message-alert <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($payment_message): ?>
            <div class="message-alert <?php echo $payment_message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <?php echo htmlspecialchars($payment_message); ?>
            </div>
        <?php endif; ?>

        <div class="card-grid">
            <div class="dashboard-card">
                <h3 class="card-title">📊 Latest Monthly Bill</h3>
                <?php if ($latestBill): ?>
                    <?php $latestBillPayUrl = 'actions/pay_bill.php?agreement_id=' . urlencode($latestBill['agreement_id']) . '&billing_month=' . urlencode($latestBill['billing_month']); ?>
                    <div class="card-row">
                        <span class="card-label">Owner:</span>
                        <span class="card-value"><?php echo htmlspecialchars($latestBill['owner_name']); ?></span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Location:</span>
                        <span class="card-value"><?php echo htmlspecialchars($latestBill['location']); ?></span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Billing Month:</span>
                        <span class="card-value"><?php echo htmlspecialchars($latestBill['billing_month']); ?></span>
                    </div>
                    <div class="card-row" style="margin:12px 0">
                        <span class="card-label">Total Amount:</span>
                        <span class="card-value" style="font-weight:700;color:#059669">BDT <?php echo number_format($latestBill['total_amount']); ?></span>
                    </div>
                    <div class="card-row">
                        <span class="card-label">Status:</span>
                        <span class="<?php echo $latestBillIsPaid ? 'card-status-paid' : 'card-status-unpaid'; ?>"><?php echo $latestBillIsPaid ? '✓ Paid' : '⏱ Unpaid'; ?></span>
                    </div>
                    <?php if (!$latestBillIsPaid): ?>
                        <a href="<?php echo htmlspecialchars($latestBillPayUrl); ?>" class="card-btn">💳 Pay Now</a>
                    <?php endif; ?>
                    <a href="tenant_bills.php" class="card-btn" style="background:#6b7280">📋 View All Bills</a>
                <?php else: ?>
                    <p>No monthly bill has been generated for you yet.</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h3 class="card-title">🎯 Rental Offers</h3>
                <?php if ($offerResult->num_rows > 0): ?>
                    <?php while ($offer = $offerResult->fetch_assoc()): ?>
                        <div style="padding:12px;background:#f9fafb;border-radius:8px;margin-bottom:10px">
                            <div class="card-row">
                                <span class="card-label">Owner:</span>
                                <span class="card-value"><?php echo htmlspecialchars($offer['owner_name']); ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Location:</span>
                                <span class="card-value"><?php echo htmlspecialchars($offer['location']); ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Advance:</span>
                                <span class="card-value">BDT <?php echo number_format((float) $offer['offer_advance'], 2); ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Start Date:</span>
                                <span class="card-value"><?php echo htmlspecialchars($offer['offer_start_date']); ?></span>
                            </div>
                            <div style="margin-top:10px;display:flex;gap:8px">
                                <a href="actions/accept_rental_offer.php?request_id=<?php echo urlencode($offer['request_id']); ?>" class="card-btn btn-accept" style="margin-top:0;flex:1;text-align:center">✓ Accept</a>
                                <a href="actions/decline_rental_offer.php?request_id=<?php echo urlencode($offer['request_id']); ?>" class="card-btn btn-decline" style="margin-top:0;flex:1;text-align:center">✕ Decline</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#6b7280">No rental offers pending.</p>
                <?php endif; ?>
                <?php $offerStmt->close(); ?>
            </div>
        </div>

        <h2 style="margin:24px 0 16px;color:#0f172a">🏘️ Available Flats for Rent</h2>
            <?php else: ?>
                <p>No rental offers are waiting for your response.</p>
            <?php endif; ?>
            <?php $offerStmt->close(); ?>
        </div>

        <?php
        $sql = "SELECT * FROM flat WHERE status IN ('Available', 'available', 'AVAILABLE') ORDER BY flat_id DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">';
            while ($flat = $result->fetch_assoc()) {
        ?>

                <div class="flat-card">
                    <h3>🏠 <?php echo htmlspecialchars($flat['location']); ?></h3>
                    <p><?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                    <div style="display:flex;gap:12px;font-size:13px;color:#6b7280;margin:12px 0">
                        <span>📏 <?php echo $flat['area']; ?> sqft</span>
                        <span>🛏️ <?php echo $flat['bedroom']; ?> bed</span>
                        <span>📍 Floor <?php echo $flat['floor']; ?></span>
                    </div>
                    <p style="font-size:18px;color:#059669;font-weight:700;margin:12px 0">💰 BDT <?php echo number_format($flat['asking_rent']); ?></p>
                    <a href="actions/request_flat_action.php?flat_id=<?php echo $flat['flat_id']; ?>" class="card-btn" style="width:100%;text-align:center;margin-top:12px">📬 Send Request</a>
                </div>

        <?php
            }
            echo '</div>';
        } else {
            echo "<div style='text-align:center;padding:40px;background:#fff;border-radius:12px'><p style='color:#6b7280'>No flats are available right now. Please check back later!</p></div>";
        }
        ?>
    </div>

</body>