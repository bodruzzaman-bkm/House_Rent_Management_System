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
            $success = 'Rental offer sent to the tenant. The request will remain In Process until the tenant accepts or declines.';
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
    <title>Confirm Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Confirm Tenant Request</h1>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <p><a href="view_requests.php">Back to requests</a></p>
    <?php elseif ($request): ?>
        <?php
            $requestId = isset($request['request_id']) ? (int) $request['request_id'] : 0;
            $askingRent = isset($request['asking_rent']) ? (int) $request['asking_rent'] : 0;
        ?>
        <p><strong>Tenant:</strong> <?php echo htmlspecialchars($request['tenant_name']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($request['location']); ?></p>
        <p><strong>Request Status:</strong> <?php echo htmlspecialchars($request['request_status']); ?></p>

        <?php if ($request['request_status'] === 'Pending'): ?>
            <form method="post">
                <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">

                <label>Security Deposit (Advance)</label>
                <input type="number" name="advance" min="0" step="0.01" value="0" required>

                <label>First Month's Initial Rent</label>
                <input type="number" value="<?php echo htmlspecialchars((string) $askingRent); ?>" readonly>

                <label>Agreement Start Date</label>
                <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>

                <button type="submit">Send Rental Offer</button>
            </form>
        <?php else: ?>
            <p>This request is already in progress or has been processed.</p>
            <a href="view_requests.php">Back to requests</a>
        <?php endif; ?>
    <?php else: ?>
        <p>Request not found.</p>
        <a href="view_requests.php">Back to requests</a>
    <?php endif; ?>
</div>
</body>
</html>
