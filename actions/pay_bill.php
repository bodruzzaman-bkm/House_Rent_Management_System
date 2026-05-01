<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: ../login.php');
    exit();
}

$agreement_id = isset($_GET['agreement_id']) ? intval($_GET['agreement_id']) : 0;
$billing_month = isset($_GET['billing_month']) ? trim($_GET['billing_month']) : '';
$tenant_id = $_SESSION['user_id'];

if ($agreement_id <= 0 || $billing_month === '') {
    $_SESSION['payment_message'] = 'Invalid bill selected.';
    $_SESSION['payment_message_type'] = 'error';
    header('Location: ../tenant_bills.php');
    exit();
}

$verify = "SELECT mb.payment_status, mb.total_amount, mb.billing_month, f.location
           FROM monthly_bill mb
           JOIN agreement a ON mb.agreement_id = a.agreement_id
           JOIN links l ON a.agreement_id = l.agreement_id
           JOIN flat f ON l.flat_id = f.flat_id
           WHERE mb.agreement_id = ? AND mb.billing_month = ? AND l.tenant_id = ?";
$stmt = $conn->prepare($verify);
$stmt->bind_param('isi', $agreement_id, $billing_month, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['payment_message'] = 'Bill not found or not authorized.';
    $_SESSION['payment_message_type'] = 'error';
    header('Location: ../tenant_bills.php');
    exit();
}
$row = $result->fetch_assoc();
$stmt->close();

if ($row['payment_status'] === 'Paid') {
    $_SESSION['payment_message'] = 'This bill is already paid.';
    $_SESSION['payment_message_type'] = 'info';
    header('Location: ../tenant_bills.php');
    exit();
}

$update = "UPDATE monthly_bill SET payment_status = 'Paid' WHERE agreement_id = ? AND billing_month = ?";
$stmt = $conn->prepare($update);
$stmt->bind_param('is', $agreement_id, $billing_month);
$stmt->execute();
$stmt->close();

$_SESSION['paid_bill_amount'] = $row['total_amount'];
$_SESSION['paid_bill_month'] = $row['billing_month'];
$_SESSION['paid_bill_location'] = $row['location'];
header('Location: ../pay_success.php');
exit();
