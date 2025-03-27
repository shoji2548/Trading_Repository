<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('❌ Invalid Request');
}

$portid = $_POST['portid'];
$symbol = $_POST['symbol'];
$trade_style = $_POST['trade_style'];
$quantity = (int) $_POST['quantity'];
$price = (float) $_POST['price'];
$transaction_type = $_POST['transaction_type'];
$transaction_date = !empty($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d');

if (!$portid || !$symbol || !$trade_style || !$quantity || !$price || !$transaction_type) {
    echo "<script>alert('❌ Missing Required Fields'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
}

// ดึง stockid จาก symbol
$sql = "SELECT stockid FROM stock WHERE symbol = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $symbol);
$stmt->execute();
$result = $stmt->get_result();
$stock = $result->fetch_assoc();
$stmt->close();

if (!$stock) {
    echo "<script>alert('❌ Invalid Stock Symbol'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
}
$stockid = $stock['stockid'];

// เช็คว่ามีพอร์ตหรือไม่
$sql = "SELECT balance FROM portfolio WHERE portid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $portid);
$stmt->execute();
$result = $stmt->get_result();
$portfolio = $result->fetch_assoc();
$stmt->close();

if (!$portfolio) {
    echo "<script>alert('❌ Invalid Portfolio'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
}
$balance = $portfolio['balance'];

$total_cost = $quantity * $price;

if ($transaction_type === 'BUY') {
    if ($balance < $total_cost) {
        echo "<script>alert('❌ Not Enough Money'); window.location.href = 'port_details.php?portid=$portid';</script>";
        exit();
    }

    $sql = "INSERT INTO stock_transaction (portid, stockid, trade_style, transaction_type, quantity, price, transaction_date)
            VALUES (?, ?, ?, 'BUY', ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisids", $portid, $stockid, $trade_style, $quantity, $price, $transaction_date);
    $stmt->execute();
    $stmt->close();

    $sql = "SELECT quantity, average_buy_price FROM stock_lot WHERE portid = ? AND stockid = ? AND trade_style = ? AND status = 'OPEN'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $portid, $stockid, $trade_style);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_lot = $result->fetch_assoc();
    $stmt->close();

    if ($existing_lot) {
        $new_quantity = $existing_lot['quantity'] + $quantity;
        $new_avg_price = (($existing_lot['quantity'] * $existing_lot['average_buy_price']) + ($quantity * $price)) / $new_quantity;
        $sql = "UPDATE stock_lot SET quantity = ?, average_buy_price = ?, total_invested = total_invested + ?
                WHERE portid = ? AND stockid = ? AND trade_style = ? AND status = 'OPEN'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iddsis", $new_quantity, $new_avg_price, $total_cost, $portid, $stockid, $trade_style);
        $stmt->execute();
        $stmt->close();
    } else {
        $sql = "INSERT INTO stock_lot (portid, stockid, trade_style, quantity, average_buy_price, total_invested, buy_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'OPEN')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisidss", $portid, $stockid, $trade_style, $quantity, $price, $total_cost, $transaction_date);
        $stmt->execute();
        $stmt->close();
    }

    $sql = "UPDATE portfolio SET balance = balance - ? WHERE portid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ds", $total_cost, $portid);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ Buy Success!'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
} elseif ($transaction_type === 'SELL') {
    $sql = "SELECT quantity, average_buy_price FROM stock_lot WHERE portid = ? AND stockid = ? AND trade_style = ? AND status = 'OPEN'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $portid, $stockid, $trade_style);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_lot = $result->fetch_assoc();
    $stmt->close();

    if (!$existing_lot || $existing_lot['quantity'] < $quantity) {
        echo "<script>alert('❌ Not Enough Shares'); window.location.href = 'port_details.php?portid=$portid';</script>";
        exit();
    }

    $sql = "INSERT INTO stock_transaction (portid, stockid, trade_style, transaction_type, quantity, price, transaction_date)
            VALUES (?, ?, ?, 'SELL', ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisids", $portid, $stockid, $trade_style, $quantity, $price, $transaction_date);
    $stmt->execute();
    $stmt->close();

    $profit_loss = ($price - $existing_lot['average_buy_price']) * $quantity;
    $remaining_quantity = $existing_lot['quantity'] - $quantity;
    $status = ($remaining_quantity == 0) ? 'CLOSED' : 'OPEN';

    $sql = "UPDATE stock_lot SET quantity = ?, profit_loss = profit_loss + ?, sell_date = ?, status = ? 
            WHERE portid = ? AND stockid = ? AND trade_style = ? AND status = 'OPEN'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsssis", $remaining_quantity, $profit_loss, $transaction_date, $status, $portid, $stockid, $trade_style);
    $stmt->execute();
    $stmt->close();

    $sell_income = $quantity * $price;
    $sql = "UPDATE portfolio SET balance = balance + ? WHERE portid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ds", $sell_income, $portid);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ Sell Success!'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
} else {
    echo "<script>alert('❌ Invalid Transaction Type'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
}
?>
