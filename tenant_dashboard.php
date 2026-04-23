<?php
session_start();

// Check if user is logged in and is a TENANT
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }

        .header {
            background-color: #528ce4;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-logout {
            background-color: #a55656;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .flat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            border-left: 5px solid #528ce4;
        }

        h3 {
            margin-top: 0;
            color: #333;
        }

        .btn-request {
            background-color: #528ce4;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>Welcome Tenant, <?php echo $_SESSION['name']; ?>!</h1>
            <p>Find your perfect home.</p>
        </div>

        <div class="action-bar">
            <h2>Available Flats to Rent</h2>
            <a href="actions/logout_action.php" class="btn-logout">Logout</a>
        </div>

        <div class="flat-card">
            <h3>Flat ID: #205</h3>
            <p><strong>Area:</strong> Dhanmondi | <strong>Bedrooms:</strong> 3 | <strong>Asking Rent:</strong> 22,000 BDT</p>
            <p><strong>Owner Contact:</strong> (Hidden until agreement)</p>
            <a href="#" class="btn-request">Send Rent Request</a>
        </div>

        <div class="flat-card">
            <h3>Flat ID: #308</h3>
            <p><strong>Area:</strong> Mirpur | <strong>Bedrooms:</strong> 2 | <strong>Asking Rent:</strong> 15,000 BDT</p>
            <p><strong>Owner Contact:</strong> (Hidden until agreement)</p>
            <a href="#" class="btn-request">Send Rent Request</a>
        </div>
    </div>

</body>

</html>