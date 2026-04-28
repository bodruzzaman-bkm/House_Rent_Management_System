<?php
session_start();
require_once 'config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="dashboard-container">
        <div class="header-tenant">
            <h1>Welcome Tenant, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p>Find your perfect home.</p>
        </div>

        <div class="action-bar">
            <h2>Available Flats to Rent</h2>
            <a href="actions/logout_action.php" class="btn-logout">Logout</a>
        </div>

        <?php
        $sql = "SELECT * FROM flat WHERE status = 'Available' ORDER BY flat_id DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($flat = $result->fetch_assoc()) {
        ?>

                <div class="flat-card border-tenant">
                    <h3>Flat Location: <?php echo htmlspecialchars($flat['location']); ?></h3>
                    <p><strong>Detailed Address:</strong> <?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                    <p>
                        <strong>Floor:</strong> <?php echo $flat['floor']; ?> |
                        <strong>Area:</strong> <?php echo $flat['area']; ?> sq ft |
                        <strong>Bedrooms:</strong> <?php echo $flat['bedroom']; ?>
                    </p>
                    <p style="font-size: 18px; color: #28a745;"><strong>Asking Rent:</strong> <?php echo number_format($flat['asking_rent']); ?> BDT</p>

                    <p><strong>Owner Contact:</strong> (Hidden until agreement is confirmed)</p>

                    <a href="actions/request_flat_action.php?flat_id=<?php echo $flat['flat_id']; ?>" class="btn-request">Send Rent Request</a>
                </div>

        <?php
            }
        } else {
            echo "<p style='text-align: center; padding: 20px; background-color: white; border-radius: 5px;'>Sorry, no flats are available for rent right now. Please check back later!</p>";
        }
        ?>

    </div>

</body>

</html>