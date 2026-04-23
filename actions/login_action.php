<?php
// Start session (Required to keep the user logged in across pages)
session_start();

// Include database connection file
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Check if the email exists in the database
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // 2. Verify if the entered password matches the hashed password
        if (password_verify($password, $user['password'])) {

            // 3. Save user data in Session variables upon successful login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];

            // 4. Check the role (Owner or Tenant) and redirect accordingly
            $owner_check = $conn->query("SELECT * FROM owner WHERE user_id = " . $user['user_id']);
            if ($owner_check->num_rows > 0) {
                $_SESSION['role'] = 'owner';
                $redirect_page = '../owner_dashboard.php'; // Owner's page
            } else {
                $_SESSION['role'] = 'tenant';
                $redirect_page = '../tenant_dashboard.php'; // Tenant's page
            }

            // Redirect to the specific dashboard
            echo "<script>
                    alert('Login Successful!');
                    window.location.href='$redirect_page';
                  </script>";
        } else {
            // Case: Incorrect password
            echo "<script>
                    alert('Incorrect Password! Please try again.');
                    window.location.href='../login.php';
                  </script>";
        }
    } else {
        // Case: Email not found
        echo "<script>
                alert('Email not found! Please register first.');
                window.location.href='../register.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
