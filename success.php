<?php
session_start();

if (!isset($_SESSION['bill_total']) || !isset($_SESSION['bill_month'])) {
    header("Location: generate_bill.php");
    exit();
}

$total = $_SESSION['bill_total'];
$month = $_SESSION['bill_month'];

unset($_SESSION['bill_total'], $_SESSION['bill_month']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>House Rent System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>House Rent Management System</h1>
        <h2>Bill Generated Successfully</h2>
        <p>Month: <?php echo htmlspecialchars($month); ?></p>
        <p>Total Amount: <?php echo htmlspecialchars(number_format($total, 2)); ?></p>
        <p>Status: Unpaid</p>
        <br>
        <a href="generate_bill.php">Generate Another Bill</a>
    </div>
</body>

</html>