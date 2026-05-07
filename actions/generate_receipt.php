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
    $_SESSION['owner_payment_message'] = 'Invalid parameters for generating receipt.';
    $_SESSION['owner_payment_message_type'] = 'error';
    header('Location: ../owner_bills.php');
    exit();
}

// verify owner owns the agreement and bill exists and is Paid
$verify = "SELECT mb.total_amount, a.owner_id, l.tenant_id, mb.payment_status
           FROM monthly_bill mb
           JOIN agreement a ON mb.agreement_id = a.agreement_id
           JOIN links l ON a.agreement_id = l.agreement_id
           WHERE mb.agreement_id = ? AND mb.billing_month = ? AND a.owner_id = ? LIMIT 1";
$stmt = $conn->prepare($verify);
$stmt->bind_param('isi', $agreement_id, $billing_month, $owner_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    $_SESSION['owner_payment_message'] = 'Bill not found or not authorized.';
    $_SESSION['owner_payment_message_type'] = 'error';
    header('Location: ../owner_bills.php');
    exit();
}
$row = $res->fetch_assoc();
$stmt->close();

if (strtolower($row['payment_status']) !== 'paid') {
    $_SESSION['owner_payment_message'] = 'Cannot generate receipt for unpaid bill.';
    $_SESSION['owner_payment_message_type'] = 'error';
    header('Location: ../owner_bills.php');
    exit();
}

$tenant_id = intval($row['tenant_id']);
$amount = intval($row['total_amount']);

// create receipts table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS receipts (
    receipt_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(100) NOT NULL,
    agreement_id INT NOT NULL,
    tenant_id INT NOT NULL,
    owner_id INT NOT NULL,
    billing_month VARCHAR(50) NOT NULL,
    amount INT NOT NULL,
    paid_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTable);

$receipt_number = 'RCPT-' . time() . '-' . rand(100,999);
$paid_at = date('Y-m-d H:i:s');

$ins = "INSERT INTO receipts (receipt_number, agreement_id, tenant_id, owner_id, billing_month, amount, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($ins);
$stmt->bind_param('siiisis', $receipt_number, $agreement_id, $tenant_id, $owner_id, $billing_month, $amount, $paid_at);
$stmt->execute();
$stmt->close();

// redirect to view page with token
header('Location: ../view_receipt.php?token=' . urlencode($receipt_number));
exit();

?>
