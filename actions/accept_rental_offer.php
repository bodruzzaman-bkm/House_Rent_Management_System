<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: ../login.php');
    exit();
}

$tenant_id = $_SESSION['user_id'];
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;

if ($request_id <= 0) {
    $_SESSION['request_message'] = 'Invalid rental offer selected.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
    exit();
}

$verify = "SELECT r.request_id, r.flat_id, r.offer_advance, r.offer_start_date, r.request_status, f.owner_id, f.asking_rent, f.location
           FROM request r
           JOIN flat f ON r.flat_id = f.flat_id
           WHERE r.request_id = ? AND r.tenant_id = ? AND r.request_status = 'In Process'";
$stmt = $conn->prepare($verify);
$stmt->bind_param('ii', $request_id, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['request_message'] = 'Rental offer not found or already handled.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
    exit();
}

$offer = $result->fetch_assoc();
$stmt->close();

// Mark the request as "Pending Advance Payment" instead of directly approving
$updateRequest = $conn->prepare("UPDATE request SET request_status = 'Pending Advance Payment' WHERE request_id = ?");
$updateRequest->bind_param('i', $request_id);
$updateRequest->execute();
$updateRequest->close();

// Store advance payment details in session for payment page
$_SESSION['pending_advance'] = [
    'request_id' => $request_id,
    'offer_advance' => $offer['offer_advance'],
    'offer_start_date' => $offer['offer_start_date'],
    'flat_id' => $offer['flat_id'],
    'owner_id' => $offer['owner_id'],
    'asking_rent' => $offer['asking_rent'],
    'location' => $offer['location']
];

$_SESSION['request_message'] = 'Offer accepted! Please pay the advance amount to complete your registration.';
$_SESSION['request_message_type'] = 'info';

// Redirect to advance payment page
header('Location: ../pay_advance.php?request_id=' . urlencode($request_id));
exit();