<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - House Rent System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="form-container login-container">
        <h2 style="border-bottom: 2px solid #528ce4;">User Login</h2>

        <form action="actions/login_action.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" style="background-color: #528ce4;">Login Now</button>
        </form>

        <div class="text-center">
            Don't have an account? <a href="register.php">Register Here</a>
        </div>
    </div>

</body>

</html>