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
</head>

<body>

    <div class="dashboard-container">
        <div class="header-tenant">
            <h1>Welcome Tenant, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p>Find your perfect home.</p>
        </div>

        <div class="action-bar">
            <h2>Available Flats to Rent</h2>
            <div>
                <a href="browse_flats.php" class="btn-post">Browse Flats</a>
                <a href="tenant_bills.php" class="btn-post">View My Bills</a>
                <a href="actions/logout_action.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div style="margin: 20px auto; max-width: 700px; padding: 12px 16px; border-radius: 6px; background: <?php echo $message_type === 'success' ? '#d4edda' : ($message_type === 'error' ? '#f8d7da' : '#fff3cd'); ?>; border: 1px solid <?php echo $message_type === 'success' ? '#c3e6cb' : ($message_type === 'error' ? '#f5c6cb' : '#ffeeba'); ?>; color: <?php echo $message_type === 'success' ? '#155724' : ($message_type === 'error' ? '#721c24' : '#856404'); ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($payment_message): ?>
            <div style="margin: 20px auto; max-width: 700px; padding: 12px 16px; border-radius: 6px; background: <?php echo $payment_message_type === 'success' ? '#d4edda' : ($payment_message_type === 'error' ? '#f8d7da' : '#fff3cd'); ?>; border: 1px solid <?php echo $payment_message_type === 'success' ? '#c3e6cb' : ($payment_message_type === 'error' ? '#f5c6cb' : '#ffeeba'); ?>; color: <?php echo $payment_message_type === 'success' ? '#155724' : ($payment_message_type === 'error' ? '#721c24' : '#856404'); ?>;">
                <?php echo htmlspecialchars($payment_message); ?>
            </div>
        <?php endif; ?>

        <div style="margin: 20px auto; max-width: 700px; padding: 16px; border-radius: 8px; background: #ffffff; border: 1px solid #e6e6e6; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <h3 style="margin-top: 0;">Latest Monthly Bill</h3>
            <?php if ($latestBill): ?>
                <?php $latestBillPayUrl = 'actions/pay_bill.php?agreement_id=' . urlencode($latestBill['agreement_id']) . '&billing_month=' . urlencode($latestBill['billing_month']); ?>
                <p><strong>Owner:</strong> <?php echo htmlspecialchars($latestBill['owner_name']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($latestBill['location']); ?></p>
                <p><strong>Month:</strong> <?php echo htmlspecialchars($latestBill['billing_month']); ?></p>
                <p><strong>Total Amount:</strong> <?php echo htmlspecialchars(number_format($latestBill['total_amount'], 2)); ?> BDT</p>
                <p><strong>Status:</strong> <?php echo $latestBillIsPaid ? 'Paid' : 'Unpaid'; ?></p>
                <?php if (!$latestBillIsPaid): ?>
                    <p>
                        <a href="<?php echo htmlspecialchars($latestBillPayUrl); ?>" class="btn-request" style="display: inline-block; padding: 10px 16px; text-decoration: none;">Pay This Bill</a>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p>No monthly bill has been generated for you yet.</p>
            <?php endif; ?>
            <p style="margin-bottom: 0;"><a href="tenant_bills.php">View All Bills</a></p>
        </div>

        <div style="margin: 20px auto; max-width: 700px; padding: 16px; border-radius: 8px; background: #ffffff; border: 1px solid #e6e6e6; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <h3 style="margin-top: 0;">Rental Offers</h3>
            <?php if ($offerResult->num_rows > 0): ?>
                <?php while ($offer = $offerResult->fetch_assoc()): ?>
                    <div style="padding: 12px 0; border-bottom: 1px solid #eee;">
                        <p><strong>Owner:</strong> <?php echo htmlspecialchars($offer['owner_name']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($offer['location']); ?></p>
                        <p><strong>Advance:</strong> <?php echo htmlspecialchars(number_format((float) $offer['offer_advance'], 2)); ?> BDT</p>
                        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($offer['offer_start_date']); ?></p>
                        <p><strong>Status:</strong> In Process</p>
                        <p>
                            <a href="actions/accept_rental_offer.php?request_id=<?php echo urlencode($offer['request_id']); ?>" class="btn-request" style="display: inline-block; padding: 10px 16px; text-decoration: none; margin-right: 8px;">Accept</a>
                            <a href="actions/decline_rental_offer.php?request_id=<?php echo urlencode($offer['request_id']); ?>" class="btn-logout" style="display: inline-block; padding: 10px 16px; text-decoration: none;">Decline</a>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rental offers are waiting for your response.</p>
            <?php endif; ?>
            <?php $offerStmt->close(); ?>
        </div>

        <?php
        $sql = "SELECT * FROM flat WHERE status IN ('Available', 'available', 'AVAILABLE') ORDER BY flat_id DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($flat = $result->fetch_assoc()) {
        ?>

                <div class="flat-card border-tenant">
                    <h3>Flat Location: <?php echo htmlspecialchars($flat['location']); ?></h3>
                    <p><strong>Detailed Address:</strong> <?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                    <p>
                        <strong>Floor:</strong> <?php echo $flat['floor']; ?> |
                        <strong>Area:</strong> <?php echo $flat['area']; ?> sq ft |
                        <strong>Bedrooms:</strong> <?php echo $flat['bedroom']; ?>
                    </p>
                    <p style="font-size: 18px; color: #28a745;"><strong>Asking Rent:</strong> <?php echo number_format($flat['asking_rent']); ?> BDT</p>

                    <p><strong>Owner Contact:</strong> (Hidden until agreement is confirmed)</p>

                    <a href="actions/request_flat_action.php?flat_id=<?php echo $flat['flat_id']; ?>" class="btn-request">Send Rent Request</a>
                </div>

        <?php
            }
        } else {
            echo "<p style='text-align: center; padding: 20px; background-color: white; border-radius: 5px;'>Sorry, no flats are available for rent right now. Please check back later!</p>";
        }
        ?>

    </div>

</body>

</html>