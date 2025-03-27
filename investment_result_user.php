<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// ดึงข้อมูลพอร์ตทั้งหมดของผู้ใช้
$sql = "SELECT portid, balance FROM portfolio WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$portfolios = [];
$total_balance = 0;

while ($row = $result->fetch_assoc()) {
    $portfolios[] = $row;
    $total_balance += $row['balance'];
}
$stmt->close();

if (empty($portfolios)) {
    echo "<script>alert('❌ คุณไม่มีพอร์ตลงทุน!'); window.location.href = 'dashboard.php';</script>";
    exit();
}

// คำนวณ Net Profit/Loss รวมของทุกพอร์ต
$total_profit_loss = 0;
foreach ($portfolios as $portfolio) {
    $sql = "SELECT SUM(profit_loss) AS profit_loss FROM stock_lot WHERE portid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $portfolio['portid']);
    $stmt->execute();
    $result = $stmt->get_result();
    $profit_data = $result->fetch_assoc();
    $stmt->close();

    $total_profit_loss += $profit_data['profit_loss'] ?? 0;
}

// คำนวณ Win Rate รวมของทุกพอร์ต
$trade_styles = ['swing_trade', 'day_trade', 'run_trend'];
$win_rates = [];
$trade_counts = [];
$total_wins = 0;
$total_loses = 0;
$total_trades = 0;

foreach ($trade_styles as $style) {
    $total_trade_count = 0;
    $win_count = 0;

    foreach ($portfolios as $portfolio) {
        // ดึงจำนวนไม้ที่ปิดแล้วของ Trade Style นี้
        $sql = "SELECT COUNT(*) AS total_trades FROM stock_lot WHERE portid = ? AND trade_style = ? AND status = 'CLOSED'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $portfolio['portid'], $style);
        $stmt->execute();
        $result = $stmt->get_result();
        $trade_data = $result->fetch_assoc();
        $stmt->close();

        $total_trade_count += $trade_data['total_trades'] ?? 0;

        // นับจำนวนไม้ที่มีกำไร (`profit_loss > 0`) = ชนะ
        $sql = "SELECT COUNT(*) AS win_trades FROM stock_lot WHERE portid = ? AND trade_style = ? AND status = 'CLOSED' AND profit_loss > 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $portfolio['portid'], $style);
        $stmt->execute();
        $result = $stmt->get_result();
        $win_data = $result->fetch_assoc();
        $stmt->close();

        $win_count += $win_data['win_trades'] ?? 0;
    }

    $lose_count = $total_trade_count - $win_count;

    // คำนวณ Win Rate
    $win_rate = ($total_trade_count > 0) ? ($win_count / $total_trade_count) * 100 : 0;
    $win_rates[$style] = round($win_rate, 2);
    $trade_counts[$style] = [
        "total" => $total_trade_count,
        "win" => $win_count,
        "lose" => $lose_count
    ];

    // 🔹 รวมค่าคำนวณ Win Rate รวม
    $total_wins += $win_count;
    $total_loses += $lose_count;
    $total_trades += $total_trade_count;
}

// คำนวณ Win Rate รวม
$total_win_rate = ($total_trades > 0) ? ($total_wins / $total_trades) * 100 : 0;
$total_win_rate = round($total_win_rate, 2);

?>

<!DOCTYPE html>
<html>
<head>
    <title>ผลการลงทุนของผู้ใช้</title>
</head>
<body>
    <h2>📊 ผลการลงทุนของคุณ (รวมทุกพอร์ต)</h2>

    <h3>💰 สถานะทางการเงิน</h3>
    <p><strong>💵 เงินสดคงเหลือรวม:</strong> <?php echo number_format($total_balance, 2); ?> บาท</p>
    <p><strong>📈 กำไร/ขาดทุนสุทธิ (Net Profit/Loss):</strong> 
        <span style="color: <?php echo ($total_profit_loss >= 0) ? 'green' : 'red'; ?>">
            <?php echo number_format($total_profit_loss, 2); ?> บาท
        </span>
    </p>

    <h3>📊 Win Rate แยกตาม Trade Style</h3>
    <table border="1">
        <tr>
            <th>Trade Style</th>
            <th>เทรดทั้งหมด</th>
            <th>ชนะ</th>
            <th>แพ้</th>
            <th>Win Rate (%)</th>
        </tr>
        <?php foreach ($trade_styles as $style): ?>
        <tr>
            <td><?php echo ucfirst(str_replace('_', ' ', $style)); ?></td>
            <td><?php echo $trade_counts[$style]["total"]; ?></td>
            <td><?php echo $trade_counts[$style]["win"]; ?></td>
            <td><?php echo $trade_counts[$style]["lose"]; ?></td>
            <td><?php echo $win_rates[$style]; ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>📈 Win Rate รวม</h3>
    <p><strong>🎯 เทรดทั้งหมด:</strong> <?php echo $total_trades; ?> ไม้</p>
    <p><strong>✅ ชนะ:</strong> <?php echo $total_wins; ?> ไม้</p>
    <p><strong>❌ แพ้:</strong> <?php echo $total_loses; ?> ไม้</p>
    <p><strong>🏆 Win Rate รวมทั้งหมด:</strong> <?php echo $total_win_rate; ?>%</p>

    <br>
    <a href="dashboard.php">⬅️ กลับไปที่ Dashboard</a>
</body>
</html>
