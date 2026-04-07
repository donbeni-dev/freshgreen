<?php
$conn = new mysqli('localhost', 'root', '', 'freshgreen');

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>