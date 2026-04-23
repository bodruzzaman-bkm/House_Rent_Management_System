<?php
// Check if a role is selected from the URL
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - House Rent System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333333;
            border-bottom: 2px solid #528ce4;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #528ce4;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #528ce4;
        }

        .section-title {
            font-size: 18px;
            color: #528ce4;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-top: 15px;
            border-top: 1px dashed #aaa;
        }

        /* CSS for the initial role selection buttons */
        .role-selection-box {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }

        .role-btn {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            text-align: center;
            width: 40%;
        }

        .btn-owner {
            background-color: #66a35a;
        }

        .btn-owner:hover {
            background-color: #66a35a;
        }

        .btn-tenant {
            background-color: #528ce4;
        }

        .btn-tenant:hover {
            background-color: #528ce4;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #528ce4;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="form-container">

        <?php if ($role == ''): ?>
            <h2>Choose Your Registration Type</h2>
            <div class="role-selection-box">
                <a href="register.php?role=owner" class="role-btn btn-owner">I am an Owner</a>
                <a href="register.php?role=tenant" class="role-btn btn-tenant">I am a Tenant</a>
            </div>
            <a href="login.php" class="back-link">Already have an account? Login here</a>

        <?php else: ?>

            <h2><?php echo ucfirst($role); ?> Registration</h2>

            <form action="actions/register_action.php" method="POST">

                <input type="hidden" name="role" value="<?php echo $role; ?>">

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>NID Number *</label>
                    <input type="text" name="nid" required>
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>

                <?php if ($role == 'owner'): ?>
                    <div class="section-title">Bank Information</div>
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name">
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number">
                    </div>
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" name="branch_name">
                    </div>
                <?php endif; ?>

                <?php if ($role == 'tenant'): ?>
                    <div class="section-title">Additional Info</div>
                    <div class="form-group">
                        <label>Permanent Address</label>
                        <input type="text" name="permanent_address">
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation">
                    </div>
                <?php endif; ?>

                <button type="submit">Complete Registration</button>
                <a href="register.php" class="back-link">← Go Back to Role Selection</a>
            </form>

        <?php endif; ?>

    </div>

</body>

</html>