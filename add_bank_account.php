<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_number = $_POST['account_number'];
    $bankid = $_POST['bankid'];

    $sql = "INSERT INTO bank_account (account_number, bankid) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $account_number, $bankid);

    if ($stmt->execute()) {
        echo "<script>alert('สร้างบัญชีธนาคารสำเร็จ!'); window.location.href = 'manage_bank.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด!');</script>";
    }

    $stmt->close();
}
?>
