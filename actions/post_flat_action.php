<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $location = trim($_POST['location']);
    $detailed_location = trim($_POST['detailed_location']);
    $area = $_POST['area'];
    $bedroom = $_POST['bedroom'];
    $floor = $_POST['floor'];
    $asking_rent = $_POST['asking_rent'];

    $status = 'Available';
    $owner_id = $_SESSION['user_id'];

    $sql = "INSERT INTO flat (area, location, asking_rent, bedroom, floor, detailed_location, status, owner_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("isiiissi", $area, $location, $asking_rent, $bedroom, $floor, $detailed_location, $status, $owner_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Flat advertisement posted successfully!');
                window.location.href='../owner_dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Error: Could not post advertisement.');
                window.location.href='../post_flat.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
