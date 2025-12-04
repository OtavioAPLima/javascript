<?php

$servername = "localhost";
$username = "root";
$password = "SenhaForte";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->close();
?>