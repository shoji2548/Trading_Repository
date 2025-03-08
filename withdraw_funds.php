<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $portid = $_POST['portid'];
    $amount = $_POST['amount'];

    // 🔹 ดึงบัญชีธนาคารที่เชื่อมกับพอร์ต
    $sql_get_account = "SELECT account_number, balance FROM portfolio WHERE portid = ?";
    $stmt_account = $conn->prepare($sql_get_account);
    $stmt_account->bind_param("s", $portid);
    $stmt_account->execute();
    $result = $stmt_account->get_result();
    $portfolio = $result->fetch_assoc();
    $stmt_account->close();

    if (!$portfolio) {
        echo "<script>alert('❌ ไม่พบพอร์ตนี้!'); window.location.href = 'port_details.php?portid=$portid';</script>";
        exit();
    }

    $account_number = $portfolio['account_number'];
    $current_balance = $portfolio['balance'];

    // 🔹 ตรวจสอบว่ายอดเงินพอสำหรับการถอน
    if ($amount > $current_balance) {
        echo "<script>alert('❌ ยอดเงินในพอร์ตไม่พอสำหรับการถอน!'); window.location.href = 'port_details.php?portid=$portid';</script>";
        exit();
    }

    // 🔹 เพิ่มรายการถอนเงินลงใน `bank_transaction`
    $sql_insert_transaction = "INSERT INTO bank_transaction (portid, account_number, transaction_type, amount, transaction_date) 
                               VALUES (?, ?, 'WITHDRAW', ?, CURRENT_DATE)";
    $stmt_insert = $conn->prepare($sql_insert_transaction);
    $stmt_insert->bind_param("ssd", $portid, $account_number, $amount);

    if ($stmt_insert->execute()) {
        // 🔹 หักเงินจาก `balance` ใน `portfolio`
        $sql_update_balance = "UPDATE portfolio SET balance = balance - ? WHERE portid = ?";
        $stmt_update = $conn->prepare($sql_update_balance);
        $stmt_update->bind_param("ds", $amount, $portid);
        $stmt_update->execute();
        $stmt_update->close();

        echo "<script>alert('✅ ถอนเงินสำเร็จ! ยอดเงินใน Port อัปเดตแล้ว'); window.location.href = 'port_details.php?portid=$portid';</script>";
    } else {
        echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่'); window.location.href = 'port_details.php?portid=$portid';</script>";
    }

    $stmt_insert->close();
}
?>
