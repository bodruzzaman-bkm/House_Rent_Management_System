<?php
session_start();
require_once '../config/db_connect.php';

// সিকিউরিটি চেক
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ফর্ম থেকে ডেটা গ্রহণ
    $location = trim($_POST['location']);
    $detailed_location = trim($_POST['detailed_location']);
    $area = $_POST['area'];
    $bedroom = $_POST['bedroom'];
    $floor = $_POST['floor'];
    $asking_rent = $_POST['asking_rent'];

    // ডিফল্ট মান
    $status = 'Available';
    $owner_id = $_SESSION['user_id'];

    // SQL কোয়েরি (৮টি কলাম)
    $sql = "INSERT INTO flat (area, location, asking_rent, bedroom, floor, detailed_location, status, owner_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // ডেটা টাইপ ম্যাপিং: 
    // area(i), location(s), asking_rent(i), bedroom(i), floor(i), detailed_location(s), status(s), owner_id(i)
    // স্ট্রিংটি হবে: "isiiissi"
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
