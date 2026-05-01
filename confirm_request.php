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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    $advance = isset($_POST['advance']) ? floatval($_POST['advance']) : 0;
    $start_date = trim($_POST['start_date'] ?? '');

    if ($request_id <= 0) {
        $error = 'Invalid request selected.';
    }
    if ($start_date === '') {
        $error = 'Please enter a valid start date.';
    }

    if (!$error) {
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

    if (!$error) {
        $conn->begin_transaction();
        try {
            $updateRequest = $conn->prepare("UPDATE request SET request_status='Approved' WHERE request_id = ?");
            $updateRequest->bind_param('i', $request_id);
            $updateRequest->execute();
            $updateRequest->close();

            $updateFlat = $conn->prepare("UPDATE flat SET status='Rented' WHERE flat_id = ?");
            $updateFlat->bind_param('i', $request['flat_id']);
            $updateFlat->execute();
            $updateFlat->close();

            $insertAgreement = $conn->prepare("INSERT INTO agreement (advance, start_date, owner_id) VALUES (?, ?, ?)");
            $insertAgreement->bind_param('dsi', $advance, $start_date, $owner_id);
            $insertAgreement->execute();
            $agreement_id = $conn->insert_id;
            $insertAgreement->close();

            $insertLink = $conn->prepare("INSERT INTO links (agreement_id, tenant_id, flat_id) VALUES (?, ?, ?)");
            $insertLink->bind_param('iii', $agreement_id, $request['tenant_id'], $request['flat_id']);
            $insertLink->execute();
            $insertLink->close();

            $billing_month = date('F-Y', strtotime($start_date));
            $base_rent = intval($request['asking_rent']);
            $total_amount = $base_rent;
            $payment_status = 'Unpaid';

            $insertBill = $conn->prepare("INSERT INTO monthly_bill
                (agreement_id, billing_month, base_rent, maintanance, electricity, gas, water, service_charge, total_amount, payment_status)
                VALUES (?, ?, ?, 0, 0, 0, 0, 0, ?, ?)");
            $insertBill->bind_param('isiis', $agreement_id, $billing_month, $base_rent, $total_amount, $payment_status);
            $insertBill->execute();
            $insertBill->close();

            $conn->commit();
            $success = 'Tenant confirmed, agreement created, and first month bill recorded.';
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Unable to confirm request: ' . $e->getMessage();
        }
    }
} else {
    $request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
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
        <p><strong>Tenant:</strong> <?php echo htmlspecialchars($request['tenant_name']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($request['location']); ?></p>
        <p><strong>Request Status:</strong> <?php echo htmlspecialchars($request['request_status']); ?></p>

        <?php if ($request['request_status'] === 'Pending'): ?>
            <form method="post">
                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">

                <label>Security Deposit (Advance)</label>
                <input type="number" name="advance" min="0" step="0.01" value="0" required>

                <label>Agreement Start Date</label>
                <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>

                <button type="submit">Confirm Tenant & Create Agreement</button>
            </form>
        <?php else: ?>
            <p>This request has already been processed.</p>
            <a href="view_requests.php">Back to requests</a>
        <?php endif; ?>
    <?php else: ?>
        <p>Request not found.</p>
        <a href="view_requests.php">Back to requests</a>
    <?php endif; ?>
</div>
</body>
</html>
