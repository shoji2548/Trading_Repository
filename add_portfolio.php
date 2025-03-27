<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $portid = $_POST['portid'];
    $username = $_POST['username'];
    $brokerid = $_POST['brokerid'];
    $account_number = $_POST['account_number'];
    $balance = $_POST['balance'];

    // ตรวจสอบว่า Broker ID มีอยู่จริงหรือไม่
    $sql_check_broker = "SELECT brokerid FROM broker WHERE brokerid = ?";
    $stmt_broker = $conn->prepare($sql_check_broker);
    $stmt_broker->bind_param("i", $brokerid);
    $stmt_broker->execute();
    $stmt_broker->store_result();
    
    if ($stmt_broker->num_rows == 0) {
        echo "<script>alert('❌ ไม่พบ Broker ID นี้!'); window.location.href = 'manage_portfolio.php';</script>";
        exit();
    }
    $stmt_broker->close();

    // ตรวจสอบว่า Account Number มีอยู่จริงหรือไม่
    $sql_check_account = "SELECT account_number FROM bank_account WHERE account_number = ?";
    $stmt_account = $conn->prepare($sql_check_account);
    $stmt_account->bind_param("s", $account_number);
    $stmt_account->execute();
    $stmt_account->store_result();
    
    if ($stmt_account->num_rows == 0) {
        echo "<script>alert('❌ ไม่พบเลขบัญชีธนาคารนี้!'); window.location.href = 'manage_portfolio.php';</script>";
        exit();
    }
    $stmt_account->close();

    // เพิ่มพอร์ตลงทุนเมื่อข้อมูลถูกต้อง
    $sql = "INSERT INTO portfolio (portid, username, brokerid, account_number, balance) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisd", $portid, $username, $brokerid, $account_number, $balance);

    if ($stmt->execute()) {
        echo "<script>alert('✅ สร้างพอร์ตลงทุนสำเร็จ!'); window.location.href = 'manage_portfolio.php';</script>";
    } else {
        echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่');</script>";
    }

    $stmt->close();
}
?>
