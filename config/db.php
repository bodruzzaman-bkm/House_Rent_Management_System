<?php
$conn = new mysqli("localhost", "root", "", "house_rent_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
