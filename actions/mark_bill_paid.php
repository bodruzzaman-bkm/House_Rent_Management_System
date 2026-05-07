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

// fetch tenant and amount for the bill
$infoSql = "SELECT l.tenant_id, mb.total_amount, a.owner_id
            FROM monthly_bill mb
            JOIN agreement a ON mb.agreement_id = a.agreement_id
            JOIN links l ON a.agreement_id = l.agreement_id
            WHERE mb.agreement_id = ? AND mb.billing_month = ? LIMIT 1";
$stmt = $conn->prepare($infoSql);
$stmt->bind_param('is', $agreement_id, $billing_month);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row) {
    $tenant_id = intval($row['tenant_id']);
    $amount = intval($row['total_amount']);
    $owner_id_db = intval($row['owner_id']);
    // check if a receipt already exists for this agreement + billing month
    $checkSql = "SELECT receipt_number FROM receipts WHERE agreement_id = ? AND billing_month = ? LIMIT 1";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('is', $agreement_id, $billing_month);
    $stmt->execute();
    $resChk = $stmt->get_result();
    $existing = $resChk->fetch_assoc();
    $stmt->close();

    if ($existing && !empty($existing['receipt_number'])) {
        $receipt_number = $existing['receipt_number'];
    } else {
        $receipt_number = 'RCPT-' . time() . '-' . rand(100,999);
        $paid_at = date('Y-m-d H:i:s');

        $ins = "INSERT INTO receipts (receipt_number, agreement_id, tenant_id, owner_id, billing_month, amount, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($ins);
        $stmt->bind_param('siiisis', $receipt_number, $agreement_id, $tenant_id, $owner_id_db, $billing_month, $amount, $paid_at);
        $stmt->execute();
        $stmt->close();
    }
}

// expose receipt token to owner so they can view/copy the receipt link
$_SESSION['owner_receipt_number'] = $receipt_number ?? null;
$_SESSION['owner_payment_message'] = 'Bill marked as paid and receipt generated.';
$_SESSION['owner_payment_message_type'] = 'success';
header('Location: ../owner_bills.php');
exit();
