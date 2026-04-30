<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

$query = "SELECT r.request_id, u.name, f.location, r.request_status
          FROM request r
          JOIN flat f ON r.flat_id = f.flat_id
          JOIN user u ON r.tenant_id = u.user_id
          WHERE f.owner_id = '$owner_id'";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Requests</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Rental Requests</h1>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <p>
            Tenant: <?php echo htmlspecialchars($row['name']); ?><br>
            Location: <?php echo htmlspecialchars($row['location']); ?><br>
            Status: <?php echo htmlspecialchars($row['request_status']); ?><br>

            <?php if ($row['request_status'] == 'Pending') { ?>
                <a href="confirm_request.php?id=<?php echo $row['request_id']; ?>">Confirm</a>
            <?php } ?>
        </p>
        <hr>
    <?php } ?>

    <a href="owner_dashboard.php">Back</a>
</div>
</body>
</html>
