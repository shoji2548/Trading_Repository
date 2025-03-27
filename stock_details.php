<?php
include 'db_connect.php';

if (!isset($_GET['portid']) || !isset($_GET['symbol'])) {
    echo "<script>alert('❌ ไม่พบข้อมูลที่ต้องการ!'); window.location.href = 'dashboard.php';</script>";
    exit();
}

$portid = $_GET['portid'];
$symbol = $_GET['symbol'];

// ดึงข้อมูลบริษัทจากตาราง `stock`
$sql = "SELECT stockid, company_name FROM stock WHERE symbol = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $symbol);
$stmt->execute();
$result = $stmt->get_result();
$stock = $result->fetch_assoc();
$stmt->close();

if (!$stock) {
    echo "<script>alert('❌ ไม่พบหุ้นนี้ในระบบ!'); window.location.href = 'port_details.php?portid=$portid';</script>";
    exit();
}

$stockid = $stock['stockid'];
$company_name = $stock['company_name'];

// นับจำนวนไม้ที่เทรดไปทั้งหมด และดึงข้อมูลที่เกี่ยวข้อง
$sql = "SELECT COUNT(*) AS total_trades, 
               SUM(CASE WHEN profit_loss > 0 THEN 1 ELSE 0 END) AS win_trades, 
               SUM(CASE WHEN profit_loss < 0 THEN 1 ELSE 0 END) AS lose_trades, 
               SUM(profit_loss) AS total_profit_loss,
               SUM(total_invested) AS total_invest
        FROM stock_lot WHERE portid = ? AND stockid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $portid, $stockid);
$stmt->execute();
$result = $stmt->get_result();
$trade_data = $result->fetch_assoc();
$stmt->close();

$total_trades = $trade_data['total_trades'] ?? 0;
$win_trades = $trade_data['win_trades'] ?? 0;
$lose_trades = $trade_data['lose_trades'] ?? 0;
$total_profit_loss = $trade_data['total_profit_loss'] ?? 0;
$total_invest = $trade_data['total_invest'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>รายละเอียดหุ้น</title>
</head>
<body>
    <h2>📊 รายละเอียดหุ้น: <?php echo htmlspecialchars($symbol); ?></h2>
    <p><strong>🏢 ชื่อบริษัท:</strong> <?php echo htmlspecialchars($company_name); ?></p>

    <h3>📈 ข้อมูลการเทรด</h3>
    <p><strong>📊 เทรดทั้งหมด:</strong> <?php echo $total_trades; ?> ไม้</p>
    <p><strong>✅ ชนะ:</strong> <?php echo $win_trades; ?> ไม้</p>
    <p><strong>❌ แพ้:</strong> <?php echo $lose_trades; ?> ไม้</p>
    <p><strong>💰 Total Invest:</strong> <?php echo number_format($total_invest, 2); ?> บาท</p>
    <p><strong>📈 ผลรวมกำไร/ขาดทุน:</strong> 
        <span style="color: <?php echo ($total_profit_loss >= 0) ? 'green' : 'red'; ?>">
            <?php echo number_format($total_profit_loss, 2); ?> บาท
        </span>
    </p>

    <br>
    <a href="port_details.php?portid=<?php echo $portid; ?>">⬅️ กลับไปที่รายละเอียดพอร์ต</a>
</body>
</html>
