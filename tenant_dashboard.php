<?php
session_start();
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

        <div class="flat-card border-tenant">
            <h3>Flat ID: #205</h3>
            <p><strong>Area:</strong> Dhanmondi | <strong>Bedrooms:</strong> 3 | <strong>Asking Rent:</strong> 22,000 BDT</p>
            <p><strong>Owner Contact:</strong> (Hidden until agreement)</p>
            <a href="#" class="btn-request">Send Rent Request</a>
        </div>

        <div class="flat-card border-tenant">
            <h3>Flat ID: #308</h3>
            <p><strong>Area:</strong> Mirpur | <strong>Bedrooms:</strong> 2 | <strong>Asking Rent:</strong> 15,000 BDT</p>
            <p><strong>Owner Contact:</strong> (Hidden until agreement)</p>
            <a href="#" class="btn-request">Send Rent Request</a>
        </div>
    </div>

</body>

</html>