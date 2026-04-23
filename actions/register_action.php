<?php
// 1. Include database connection file
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Receive and sanitize data from the form
    $name = trim($_POST['name']);
    $nid = trim($_POST['nid']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt password for security
    $role = $_POST['role'];

    // 3. Start Database Transaction (Crucial to prevent partial data saving)
    $conn->begin_transaction();

    try {
        // 4. Insert data into the main 'user' table first
        $sql_user = "INSERT INTO user (name, nid, phone, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_user);
        $stmt->bind_param("sssss", $name, $nid, $phone, $email, $password);
        $stmt->execute();

        // 5. Get the newly created user's auto-generated ID
        $user_id = $conn->insert_id;

        // 6. Insert data into respective tables based on user role
        if ($role === 'owner') {
            $bank_name = $_POST['bank_name'];
            $account_number = $_POST['account_number'];
            $branch_name = $_POST['branch_name'];

            $sql_owner = "INSERT INTO owner (user_id, bank_name, account_number, branch_name) VALUES (?, ?, ?, ?)";
            $stmt_owner = $conn->prepare($sql_owner);
            $stmt_owner->bind_param("isss", $user_id, $bank_name, $account_number, $branch_name);
            $stmt_owner->execute();
        } else if ($role === 'tenant') {
            $permanent_address = $_POST['permanent_address'];
            $occupation = $_POST['occupation'];

            $sql_tenant = "INSERT INTO tenant (user_id, permanent_address, occupation) VALUES (?, ?, ?)";
            $stmt_tenant = $conn->prepare($sql_tenant);
            $stmt_tenant->bind_param("iss", $user_id, $permanent_address, $occupation);
            $stmt_tenant->execute();
        }

        // 7. Commit changes to the database permanently if everything is successful
        $conn->commit();

        echo "<script>
                alert('Registration successful! Now you can login.');
                window.location.href='../login.php';
              </script>";
    } catch (Exception $e) {
        // Rollback to the previous state if any error occurs
        $conn->rollback();

        // Print actual error message for debugging purposes
        echo "<h3>Database Error:</h3>";
        echo $e->getMessage();
    }

    $conn->close();
}
