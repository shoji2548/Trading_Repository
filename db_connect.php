<?php
// ✅ ป้องกัน session_start() ซ้ำ
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "stock_portfolio";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
?>
