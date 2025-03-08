<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $portid = $_POST['portid'];
    $account_number = $_POST['account_number'];
    $amount = $_POST['amount'];

    // 🔹 ตรวจสอบว่า Port ID มีอยู่จริงหรือไม่
    $sql_check_port = "SELECT portid FROM portfolio WHERE portid = ?";
    $stmt_port = $conn->prepare($sql_check_port);
    $stmt_port->bind_param("s", $portid);
    $stmt_port->execute();
    $stmt_port->store_result();
    
    if ($stmt_port->num_rows == 0) {
        echo "<script>alert('❌ ไม่พบ Port ID นี้!'); window.location.href = 'manage_bank.php';</script>";
        exit();
    }
    $stmt_port->close();

    // 🔹 ตรวจสอบว่า Account Number มีอยู่จริงหรือไม่
    $sql_check_account = "SELECT account_number FROM bank_account WHERE account_number = ?";
    $stmt_account = $conn->prepare($sql_check_account);
    $stmt_account->bind_param("s", $account_number);
    $stmt_account->execute();
    $stmt_account->store_result();
    
    if ($stmt_account->num_rows == 0) {
        echo "<script>alert('❌ ไม่พบเลขบัญชีธนาคารนี้!'); window.location.href = 'manage_bank.php';</script>";
        exit();
    }
    $stmt_account->close();

    // 🔹 ทำธุรกรรมฝากเงินเข้า Port
    $sql = "INSERT INTO bank_transaction (portid, account_number, transaction_type, amount, transaction_date) 
            VALUES (?, ?, 'DEPOSIT', ?, CURRENT_DATE)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssd", $portid, $account_number, $amount);

    if ($stmt->execute()) {
        // 🔹 อัปเดตยอดเงินใน Portfolio
        $sql_update_balance = "UPDATE portfolio SET balance = balance + ? WHERE portid = ?";
        $stmt_update = $conn->prepare($sql_update_balance);
        $stmt_update->bind_param("ds", $amount, $portid);
        $stmt_update->execute();
        $stmt_update->close();

        echo "<script>alert('✅ ฝากเงินสำเร็จ! ยอดเงินใน Port อัปเดตแล้ว'); window.location.href = 'manage_bank.php';</script>";
    } else {
        echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่');</script>";
    }

    $stmt->close();
}
?>
