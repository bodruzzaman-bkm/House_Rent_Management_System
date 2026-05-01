<?php
session_start();

if (!isset($_SESSION['paid_bill_amount']) || !isset($_SESSION['paid_bill_month']) || !isset($_SESSION['paid_bill_location'])) {
    header('Location: tenant_bills.php');
    exit();
}

$total = $_SESSION['paid_bill_amount'];
$month = $_SESSION['paid_bill_month'];
$location = $_SESSION['paid_bill_location'];
$_SESSION['payment_message'] = 'Payment submitted successfully. The bill will remain Unpaid until the owner marks it as Paid.';
$_SESSION['payment_message_type'] = 'success';

unset($_SESSION['paid_bill_amount'], $_SESSION['paid_bill_month'], $_SESSION['paid_bill_location']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Payment Submitted</h1>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>
        <p><strong>Billing Month:</strong> <?php echo htmlspecialchars($month); ?></p>
        <p><strong>Amount Paid:</strong> <?php echo htmlspecialchars(number_format($total, 2)); ?> BDT</p>
        <p><strong>Status:</strong> Pending Owner Confirmation</p>
        <br>
        <a href="tenant_bills.php">View My Bills</a>
        <br><br>
        <a href="tenant_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
