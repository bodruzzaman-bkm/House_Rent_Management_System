<?php
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - House Rent System</title>
    <link rel="stylesheet" href="css/style.css">
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