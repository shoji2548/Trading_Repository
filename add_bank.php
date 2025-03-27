<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bank_name = trim($_POST['bank_name']);
    $bank_shortname = trim($_POST['bank_shortname']);

    if (empty($bank_name) || empty($bank_shortname)) {
        echo "<script>alert('❌ กรุณากรอกข้อมูลให้ครบถ้วน!'); window.location.href = 'manage_bank.php';</script>";
        exit();
    }

    $sql = "SELECT bankid FROM bank WHERE bank_name = ? OR bank_shortname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bank_name, $bank_shortname);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('❌ ธนาคารนี้มีอยู่แล้ว!'); window.location.href = 'manage_bank.php';</script>";
        $stmt->close();
        exit();
    }
    $stmt->close();

    $sql = "INSERT INTO bank (bank_name, bank_shortname) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bank_name, $bank_shortname);

    if ($stmt->execute()) {
        echo "<script>alert('✅ เพิ่มธนาคารสำเร็จ!'); window.location.href = 'manage_bank.php';</script>";
    } else {
        echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่'); window.location.href = 'manage_bank.php';</script>";
    }

    $stmt->close();
}
?>
