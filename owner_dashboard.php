<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="dashboard-container">
        <div class="header-owner">
            <h1>Welcome Owner, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p>Manage your properties efficiently.</p>
        </div>

        <div class="action-bar">
            <h2>My Listed Flats</h2>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="post_flat.php" class="btn-post">+ Post New Flat</a>
            <a href="view_requests.php" class="btn-post">View Rental Requests</a>
            <a href="generate_bill.php" class="btn-post">Generate Monthly Bill</a>
            <a href="owner_bills.php" class="btn-post">Manage Bills</a>

            <a href="actions/logout_action.php" class="btn-logout" style="margin-left: auto;">Logout</a>
        </div>

        <?php
        $sql = "SELECT * FROM flat WHERE owner_id = ? ORDER BY flat_id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($flat = $result->fetch_assoc()) {
                $flat_status = !empty($flat['status']) ? $flat['status'] : 'Unknown';
                $status_color = (strtolower($flat_status) === 'available') ? 'green' : 'red';
        ?>

                <div class="flat-card border-owner">
                    <h3>Flat ID: #<?php echo htmlspecialchars($flat['flat_id']); ?>
                        <span style="font-size: 14px; float: right; color: <?php echo $status_color; ?>;">
                            [<?php echo htmlspecialchars($flat_status); ?>]
                        </span>
                    </h3>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($flat['location']); ?></p>
                    <p><strong>Detailed Address:</strong> <?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                    <p>
                        <strong>Floor:</strong> <?php echo $flat['floor']; ?> |
                        <strong>Area:</strong> <?php echo $flat['area']; ?> sq ft |
                        <strong>Bedrooms:</strong> <?php echo $flat['bedroom']; ?>
                    </p>
                    <p style="font-size: 18px; color: #007BFF;"><strong>Asking Rent:</strong> <?php echo number_format($flat['asking_rent']); ?> BDT</p>
                </div>

        <?php
            }
        } else {
            echo "<p style='text-align: center; padding: 20px; background-color: white; border-radius: 5px;'>You haven't posted any flats yet. Click '+ Post New Flat' to get started!</p>";
        }
        $stmt->close();
        ?>

    </div>

</body>

</html>