<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['portid'])) {
    $portid = $_GET['portid'];
    $username = $_SESSION['username'];

    // ตรวจสอบว่าพอร์ตเป็นของผู้ใช้ที่ล็อกอินอยู่
    $sql_check = "SELECT portid FROM portfolio WHERE portid = ? AND username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $portid, $username);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();

        // ลบพอร์ต
        $sql_delete = "DELETE FROM portfolio WHERE portid = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("s", $portid);

        if ($stmt_delete->execute()) {
            echo "<script>alert('✅ ลบพอร์ตสำเร็จ!'); window.location.href = 'dashboard.php';</script>";
        } else {
            echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่'); window.location.href = 'dashboard.php';</script>";
        }
        $stmt_delete->close();
    } else {
        echo "<script>alert('❌ ไม่พบพอร์ตนี้ หรือคุณไม่มีสิทธิ์ลบ'); window.location.href = 'dashboard.php';</script>";
    }
}
?>
