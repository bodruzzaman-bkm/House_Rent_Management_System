<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}
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
            <div>
                <a href="#" class="btn-post">+ Post New Flat</a>
                <a href="actions/logout_action.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <div class="flat-card border-owner">
            <h3>Flat ID: #101</h3>
            <p><strong>Area:</strong> Banani | <strong>Bedrooms:</strong> 3 | <strong>Asking Rent:</strong> 25,000 BDT</p>
            <p><strong>Status:</strong> Available</p>
        </div>

        <div class="flat-card border-owner">
            <h3>Flat ID: #102</h3>
            <p><strong>Area:</strong> Gulshan | <strong>Bedrooms:</strong> 4 | <strong>Asking Rent:</strong> 40,000 BDT</p>
            <p><strong>Status:</strong> Rented</p>
        </div>
    </div>

</body>

</html>