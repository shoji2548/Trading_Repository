<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// คำสั่ง SQL เพื่อลบผู้ใช้
$sql = "DELETE FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    session_destroy(); // เคลียร์ Session
    echo "<script>alert('ลบบัญชีสำเร็จ!'); window.location.href = 'index.php';</script>";
    exit();
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในการลบบัญชี!'); window.location.href = 'dashboard.php';</script>";
}

$stmt->close();
?>
