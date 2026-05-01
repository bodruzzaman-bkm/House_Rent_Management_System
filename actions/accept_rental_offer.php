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

$verify = "SELECT r.request_id, r.flat_id, r.offer_advance, r.offer_start_date, r.request_status, f.owner_id, f.asking_rent
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

$conn->begin_transaction();
try {
    $first_month_rent = intval($offer['asking_rent']);
    $offer_advance = floatval($offer['offer_advance']);
    $start_date = $offer['offer_start_date'];
    $billing_month = date('F-Y', strtotime($start_date));

    $insertAgreement = $conn->prepare("INSERT INTO agreement (advance, start_date, first_month_rent, owner_id) VALUES (?, ?, ?, ?)");
    $insertAgreement->bind_param('dsii', $offer_advance, $start_date, $first_month_rent, $offer['owner_id']);
    $insertAgreement->execute();
    $agreement_id = $conn->insert_id;
    $insertAgreement->close();

    $insertLink = $conn->prepare("INSERT INTO links (agreement_id, tenant_id, flat_id) VALUES (?, ?, ?)");
    $insertLink->bind_param('iii', $agreement_id, $tenant_id, $offer['flat_id']);
    $insertLink->execute();
    $insertLink->close();

    $insertBill = $conn->prepare("INSERT INTO monthly_bill
        (agreement_id, billing_month, base_rent, maintanance, electricity, gas, water, service_charge, total_amount, payment_status)
        VALUES (?, ?, ?, 0, 0, 0, 0, 0, ?, 'Unpaid')");
    $insertBill->bind_param('isii', $agreement_id, $billing_month, $first_month_rent, $first_month_rent);
    $insertBill->execute();
    $insertBill->close();

    $updateRequest = $conn->prepare("UPDATE request SET request_status = 'Approved' WHERE request_id = ?");
    $updateRequest->bind_param('i', $request_id);
    $updateRequest->execute();
    $updateRequest->close();

    $updateFlat = $conn->prepare("UPDATE flat SET status='Rented' WHERE flat_id = ?");
    $updateFlat->bind_param('i', $offer['flat_id']);
    $updateFlat->execute();
    $updateFlat->close();

    $conn->commit();
    $_SESSION['request_message'] = 'Offer accepted. The flat is now rented and the first bill has been created.';
    $_SESSION['request_message_type'] = 'success';
    header('Location: ../tenant_dashboard.php');
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['request_message'] = 'Unable to accept the offer: ' . $e->getMessage();
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
    exit();
}