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

$verify = "SELECT request_id FROM request WHERE request_id = ? AND tenant_id = ? AND request_status = 'In Process'";
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
$stmt->close();

$updateRequest = $conn->prepare("UPDATE request SET request_status = 'Declined' WHERE request_id = ?");
$updateRequest->bind_param('i', $request_id);
$updateRequest->execute();
$updateRequest->close();

$_SESSION['request_message'] = 'Offer declined. The flat will remain available.';
$_SESSION['request_message_type'] = 'success';
header('Location: ../tenant_dashboard.php');
exit();