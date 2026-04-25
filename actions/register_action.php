<?php
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $nid = trim($_POST['nid']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt password for security
    $role = $_POST['role'];

    $conn->begin_transaction();

    try {
        $sql_user = "INSERT INTO user (name, nid, phone, email, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_user);
        $stmt->bind_param("sssss", $name, $nid, $phone, $email, $password);
        $stmt->execute();

        $user_id = $conn->insert_id;

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

        $conn->commit();

        echo "<script>
                alert('Registration successful! Now you can login.');
                window.location.href='../login.php';
              </script>";
    } catch (Exception $e) {
        $conn->rollback();

        echo "<h3>Database Error:</h3>";
        echo $e->getMessage();
    }

    $conn->close();
}
