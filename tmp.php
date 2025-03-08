<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $portid = $_POST['portid'];
    $symbol = $_POST['symbol'];
    $trade_style = $_POST['trade_style'];
    $quantity = (int) $_POST['quantity'];
    $price = (float) $_POST['price'];
    $total_cost = $quantity * $price;
    $transaction_date = isset($_POST['transaction_date']) && !empty($_POST['transaction_date']) ? $_POST['transaction_date'] : date("Y-m-d");

    // ✅ ตรวจสอบค่าที่รับเข้ามา (Validation)
    if ($quantity <= 0 || $price <= 0) {
        die("❌ จำนวนหุ้นและราคาต้องมากกว่า 0!");
    }

    // ✅ ดึง balance ของพอร์ต
    $stmt = $conn->prepare("SELECT balance FROM portfolio WHERE portid = ?");
    $stmt->bind_param("s", $portid);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();

    if ($balance < $total_cost) {
        die("❌ ยอดเงินในพอร์ตไม่พอ!");
    }

    // ✅ ดึง stockid จาก symbol
    $stmt = $conn->prepare("SELECT stockid FROM stock WHERE symbol = ?");
    $stmt->bind_param("s", $symbol);
    $stmt->execute();
    $stmt->bind_result($stockid);
    $stmt->fetch();
    $stmt->close();

    if (!$stockid) {
        die("❌ ไม่พบหุ้นนี้ในระบบ!");
    }

    // ✅ เพิ่มข้อมูลลงใน stock_transaction (แก้ไข transaction_date)
    $stmt = $conn->prepare("INSERT INTO stock_transaction (portid, stockid, trade_style, transaction_type, quantity, price, transaction_date)
                            VALUES (?, ?, ?, 'BUY', ?, ?, ?)");
    $stmt->bind_param("sisisd", $portid, $stockid, $trade_style, $quantity, $price, $transaction_date);

    if (!$stmt->execute()) {
        die("❌ เกิดข้อผิดพลาดขณะบันทึกธุรกรรมซื้อหุ้น!");
    }
    $stmt->close();

    // ✅ อัปเดตข้อมูลหุ้นใน stock_lot หรือเพิ่มใหม่
    $stmt = $conn->prepare("SELECT quantity FROM stock_lot WHERE portid = ? AND stockid = ? AND trade_style = ? AND status = 'OPEN'");
    $stmt->bind_param("sis", $portid, $stockid, $trade_style);
    $stmt->execute();
    $stmt->bind_result($existing_quantity);
    $stmt->fetch();
    $stmt->close();

    if ($existing_quantity) {
        $stmt = $conn->prepare("UPDATE stock_lot SET 
                                quantity = quantity + ?, 
                                average_buy_price = ((quantity * average_buy_price) + (? * ?)) / (quantity + ?), 
                                total_invested = total_invested + ? 
                                WHERE portid = ? AND stockid = ? AND trade_style = ? AND status = 'OPEN'");
        $stmt->bind_param("iididssis", $quantity, $quantity, $price, $quantity, $total_cost, $portid, $stockid, $trade_style);
    } else {
        $stmt = $conn->prepare("INSERT INTO stock_lot (portid, stockid, trade_style, quantity, average_buy_price, total_invested, buy_date, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'OPEN')");
        $stmt->bind_param("sisisds", $portid, $stockid, $trade_style, $quantity, $price, $total_cost, $transaction_date);
    }
    $stmt->execute();
    $stmt->close();

    // ✅ หักเงินจาก balance
    $stmt = $conn->prepare("UPDATE portfolio SET balance = balance - ? WHERE portid = ?");
    $stmt->bind_param("ds", $total_cost, $portid);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ ซื้อหุ้นสำเร็จ! ใช้เงินไป: " . number_format($total_cost, 2) . " บาท'); 
    window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
}
?>
