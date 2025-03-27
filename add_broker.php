<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $broker_name = trim($_POST['broker_name']);

    if (empty($broker_name)) {
        echo "<script>alert('❌ กรุณากรอกชื่อโบรกเกอร์!'); window.location.href = 'manage_portfolio.php';</script>";
        exit();
    }

    // ตรวจสอบว่าตาราง broker มีอยู่หรือไม่
    $sql_check_table = "SHOW TABLES LIKE 'broker'";
    $result = $conn->query($sql_check_table);

    if ($result->num_rows == 0) {
        echo "<script>alert('❌ ตาราง broker ไม่พบในฐานข้อมูล! กรุณาตรวจสอบฐานข้อมูลของคุณ'); window.location.href = 'manage_portfolio.php';</script>";
        exit();
    }

    // ตรวจสอบว่าโบรกเกอร์มีอยู่แล้วหรือไม่
    $sql = "SELECT brokerid FROM broker WHERE broker_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $broker_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('❌ โบรกเกอร์นี้มีอยู่แล้ว!'); window.location.href = 'manage_portfolio.php';</script>";
        $stmt->close();
        exit();
    }
    $stmt->close();

    // เพิ่มโบรกเกอร์ใหม่
    $sql = "INSERT INTO broker (broker_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $broker_name);

    if ($stmt->execute()) {
        echo "<script>alert('✅ เพิ่มโบรกเกอร์สำเร็จ!'); window.location.href = 'manage_portfolio.php';</script>";
    } else {
        echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่'); window.location.href = 'manage_portfolio.php';</script>";
    }

    $stmt->close();
}
?>
