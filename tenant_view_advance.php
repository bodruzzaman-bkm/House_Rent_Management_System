<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

$query = "SELECT a.advance, a.first_month_rent, f.location
          FROM agreement a
          JOIN links l ON a.agreement_id = l.agreement_id
          JOIN flat f ON l.flat_id = f.flat_id
          WHERE l.tenant_id = '$tenant_id'";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Advance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Your Advance Payment</h1>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <p>
            Location: <?php echo htmlspecialchars($row['location']); ?><br>
            Advance: <?php echo htmlspecialchars($row['advance']); ?><br>
            First Month Rent: <?php echo htmlspecialchars($row['first_month_rent']); ?>
        </p>
        <hr>
    <?php } ?>

    <a href="tenant_dashboard.php">Back</a>
</div>
</body>
</html>
