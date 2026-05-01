<?php
session_start();

if (!isset($_SESSION['bill_total']) || !isset($_SESSION['bill_month'])) {
    header("Location: generate_bill.php");
    exit();
}

$total = $_SESSION['bill_total'];
$month = $_SESSION['bill_month'];
$message = $_SESSION['bill_message'] ?? '';

unset($_SESSION['bill_total'], $_SESSION['bill_month'], $_SESSION['bill_message']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>House Rent System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .action-link {
            display: inline-block;
            margin-right: 10px;
            padding: 10px 16px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            background: #6c757d;
        }

        .action-link.secondary {
            background: #007BFF;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>House Rent Management System</h1>
        <h2>Bill Generated Successfully</h2>
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <p>Month: <?php echo htmlspecialchars($month); ?></p>
        <p>Total Amount: <?php echo htmlspecialchars(number_format($total, 2)); ?></p>
        <p>Status: Unpaid</p>
        <br>
        <a href="#" class="action-link" onclick="window.history.back(); return false;">Back</a>
        <a href="generate_bill.php" class="action-link secondary">Generate Another Bill</a>
    </div>
</body>

</html>