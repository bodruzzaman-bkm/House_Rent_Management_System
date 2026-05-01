<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];
$agreement_id = isset($_GET['agreement_id']) ? intval($_GET['agreement_id']) : 0;
$billing_month = isset($_GET['billing_month']) ? trim($_GET['billing_month']) : '';

if ($agreement_id <= 0 || $billing_month === '') {
    $_SESSION['owner_payment_message'] = 'Invalid bill selected.';
    $_SESSION['owner_payment_message_type'] = 'error';
    header('Location: ../owner_bills.php');
    exit();
}

$verify = "SELECT 1
           FROM monthly_bill mb
           JOIN agreement a ON mb.agreement_id = a.agreement_id
           WHERE mb.agreement_id = ? AND mb.billing_month = ? AND a.owner_id = ?";
$stmt = $conn->prepare($verify);
$stmt->bind_param('isi', $agreement_id, $billing_month, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['owner_payment_message'] = 'Bill not found or not authorized.';
    $_SESSION['owner_payment_message_type'] = 'error';
    header('Location: ../owner_bills.php');
    exit();
}
$stmt->close();

$update = "UPDATE monthly_bill SET payment_status = 'Paid' WHERE agreement_id = ? AND billing_month = ?";
$stmt = $conn->prepare($update);
$stmt->bind_param('is', $agreement_id, $billing_month);
$stmt->execute();
$stmt->close();

$_SESSION['owner_payment_message'] = 'Bill marked as paid.';
$_SESSION['owner_payment_message_type'] = 'success';
header('Location: ../owner_bills.php');
exit();
