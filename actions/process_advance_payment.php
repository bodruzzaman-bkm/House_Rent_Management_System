<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: ../login.php');
    exit();
}

$tenant_id = $_SESSION['user_id'];
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$confirm = isset($_POST['confirm_payment']) ? intval($_POST['confirm_payment']) : 0;

if ($request_id <= 0 || $confirm !== 1 || !isset($_SESSION['pending_advance'])) {
    $_SESSION['request_message'] = 'Invalid payment confirmation.';
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../tenant_dashboard.php');
    exit();
}

$pending = $_SESSION['pending_advance'];

try {
    $conn->begin_transaction();

    // Verify request exists and is pending payment
    $verify = $conn->prepare("SELECT r.request_id, r.flat_id, r.offer_advance, r.offer_start_date, f.owner_id, f.asking_rent
                              FROM request r
                              JOIN flat f ON r.flat_id = f.flat_id
                              WHERE r.request_id = ? AND r.tenant_id = ? AND r.request_status = 'Pending Advance Payment'");
    $verify->bind_param('ii', $request_id, $tenant_id);
    $verify->execute();
    $result = $verify->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Rental offer not found or already processed.');
    }

    $offer = $result->fetch_assoc();
    $verify->close();

    // Create agreement
    $first_month_rent = intval($offer['asking_rent']);
    $offer_advance = floatval($offer['offer_advance']);
    $start_date = $offer['offer_start_date'];
    $billing_month = date('F-Y', strtotime($start_date));

    $insertAgreement = $conn->prepare("INSERT INTO agreement (advance, start_date, first_month_rent, owner_id) VALUES (?, ?, ?, ?)");
    $insertAgreement->bind_param('dsii', $offer_advance, $start_date, $first_month_rent, $offer['owner_id']);
    $insertAgreement->execute();
    $agreement_id = $conn->insert_id;
    $insertAgreement->close();

    // Create link between agreement, tenant, and flat
    $insertLink = $conn->prepare("INSERT INTO links (agreement_id, tenant_id, flat_id) VALUES (?, ?, ?)");
    $insertLink->bind_param('iii', $agreement_id, $tenant_id, $offer['flat_id']);
    $insertLink->execute();
    $insertLink->close();

    // Create first month's bill
    $insertBill = $conn->prepare("INSERT INTO monthly_bill
        (agreement_id, billing_month, base_rent, maintanance, electricity, gas, water, service_charge, total_amount, payment_status)
        VALUES (?, ?, ?, 0, 0, 0, 0, 0, ?, 'Unpaid')");
    $insertBill->bind_param('isii', $agreement_id, $billing_month, $first_month_rent, $first_month_rent);
    $insertBill->execute();
    $insertBill->close();

    // Update request status to approved
    $updateRequest = $conn->prepare("UPDATE request SET request_status = 'Approved' WHERE request_id = ?");
    $updateRequest->bind_param('i', $request_id);
    $updateRequest->execute();
    $updateRequest->close();

    // Update flat status to rented
    $updateFlat = $conn->prepare("UPDATE flat SET status = 'Rented' WHERE flat_id = ?");
    $updateFlat->bind_param('i', $offer['flat_id']);
    $updateFlat->execute();
    $updateFlat->close();

    $conn->commit();

    // Clear pending advance from session
    unset($_SESSION['pending_advance']);

    $_SESSION['request_message'] = '✓ Congratulations! Your rental agreement has been confirmed. Your first month bill has been generated.';
    $_SESSION['request_message_type'] = 'success';
    header('Location: ../tenant_dashboard.php');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['request_message'] = 'Error confirming agreement: ' . $e->getMessage();
    $_SESSION['request_message_type'] = 'error';
    header('Location: ../pay_advance.php?request_id=' . urlencode($request_id));
    exit();
}
