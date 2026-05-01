<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: ../login.php');
    exit();
}

$tenant_id = $_SESSION['user_id'];
$flat_id = isset($_GET['flat_id']) ? intval($_GET['flat_id']) : 0;

if ($flat_id <= 0) {
    $_SESSION['request_message'] = 'Invalid flat selected.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
    exit();
}

$query = "SELECT flat_id FROM flat WHERE flat_id = ? AND status = 'Available'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $flat_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['request_message'] = 'This flat is not available or does not exist.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
    exit();
}
$stmt->close();

$duplicateQuery = "SELECT request_id FROM request WHERE tenant_id = ? AND flat_id = ? AND request_status IN ('Pending','In Process','Approved')";
$stmt = $conn->prepare($duplicateQuery);
$stmt->bind_param('ii', $tenant_id, $flat_id);
$stmt->execute();
$duplicateResult = $stmt->get_result();

if ($duplicateResult->num_rows > 0) {
    $_SESSION['request_message'] = 'You already have an active request for this flat.';
    $_SESSION['request_message_type'] = 'error';
    $stmt->close();
    header('Location: ../tenant_dashboard.php');
    exit();
}
$stmt->close();

$insert = "INSERT INTO request (date, request_status, tenant_id, flat_id) VALUES (CURDATE(), 'Pending', ?, ?)";
$stmt = $conn->prepare($insert);
$stmt->bind_param('ii', $tenant_id, $flat_id);

if ($stmt->execute()) {
    $_SESSION['request_message'] = 'Request Sent Successfully';
    $_SESSION['request_message_type'] = 'success';
    header('Location: ../tenant_dashboard.php');
} else {
    $_SESSION['request_message'] = 'Error sending request.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
}
exit();

$stmt->close();
$conn->close();
