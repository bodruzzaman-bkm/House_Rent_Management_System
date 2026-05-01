<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];
$flat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($flat_id <= 0) {
    header("Location: tenant_dashboard.php");
    exit();
}

$query = "SELECT owner_id FROM flat WHERE flat_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $flat_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();
$stmt->close();

if (!$owner) {
    header("Location: tenant_dashboard.php");
    exit();
}

$insert = "INSERT INTO request (date, request_status, tenant_id, flat_id)
           VALUES (CURDATE(), 'Pending', ?, ?)";
$stmt = $conn->prepare($insert);
$stmt->bind_param('ii', $tenant_id, $flat_id);
$stmt->execute();
$stmt->close();

header("Location: tenant_dashboard.php");
