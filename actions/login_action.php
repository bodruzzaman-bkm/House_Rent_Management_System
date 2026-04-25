<?php
session_start();

require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];

            $owner_check = $conn->query("SELECT * FROM owner WHERE user_id = " . $user['user_id']);
            if ($owner_check->num_rows > 0) {
                $_SESSION['role'] = 'owner';
                $redirect_page = '../owner_dashboard.php';
            } else {
                $_SESSION['role'] = 'tenant';
                $redirect_page = '../tenant_dashboard.php';
            }

            echo "<script>
                    alert('Login Successful!');
                    window.location.href='$redirect_page';
                  </script>";
        } else {
            echo "<script>
                    alert('Incorrect Password! Please try again.');
                    window.location.href='../login.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Email not found! Please register first.');
                window.location.href='../register.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
