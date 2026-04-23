<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "house_rent_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
