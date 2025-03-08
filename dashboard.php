<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$sql = "SELECT email, phone, firstname, lastname FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>ยินดีต้อนรับ, <?php echo $user['firstname'] . " " . $user['lastname']; ?>!</h2>
    <p><strong>Username:</strong> <?php echo $username; ?></p>
    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
    <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>

    <h3>การตั้งค่าผู้ใช้</h3>
    <a href="change_password.php"><button>🔑 เปลี่ยนรหัสผ่าน</button></a>
    <a href="update_userdetails.php"><button>✏️ อัปเดตข้อมูลผู้ใช้</button></a>
    <a href="manage_portfolio.php"><button>➕ เพิ่มพอร์ตลงทุน</button></a>
    <a href="manage_bank.php"><button>🏦 จัดการบัญชีธนาคาร</button></a>
    <br><br>

    <h3>ลบบัญชีผู้ใช้</h3>
    <a href="delete_user.php" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบบัญชีนี้?');">
        <button>❌ ลบบัญชีผู้ใช้</button>
    </a>

    <h3>พอร์ตของคุณ</h3>
    <table border="1">
        <tr>
            <th>Port ID</th>
            <th>Broker Name</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>
        <?php
        $sql = "SELECT p.portid, b.broker_name, p.balance 
                FROM portfolio p
                JOIN broker b ON p.brokerid = b.brokerid
                WHERE p.username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['portid']}</td>
                        <td>{$row['broker_name']}</td>
                        <td>{$row['balance']}</td>
                        <td>
                            <a href='delete_portfolio.php?portid={$row['portid']}' onclick='return confirm(\"คุณแน่ใจหรือไม่ว่าต้องการลบพอร์ตนี้?\");'>
                                ❌ Delete Port
                            </a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>❌ ไม่มีพอร์ตที่เชื่อมกับบัญชีของคุณ</td></tr>";
        }
        $stmt->close();
        ?>
    </table>
    <br><br>
    
    <h3>📊 ผลการลงทุนของคุณ</h3>
    <a href="investment_result_user.php">
        <button>📈 แสดงผลการลงทุนรวม</button>
    </a>

    <h3>🔥 หุ้นที่คุณซื้อขายมากที่สุด (Top 3)</h3>
    <table border="1">
        <tr>
            <th>อันดับ</th>
            <th>Symbol</th>
            <th>Company Name</th>
            <th>จำนวนครั้งที่ซื้อขาย</th>
        </tr>
        <?php
        $sql = "SELECT s.symbol, s.company_name, COUNT(st.transacid) AS trade_count
                FROM stock_transaction st
                JOIN stock s ON st.stockid = s.stockid
                JOIN portfolio p ON st.portid = p.portid
                WHERE p.username = ?
                GROUP BY s.symbol, s.company_name
                ORDER BY trade_count DESC
                LIMIT 3";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rank = 1;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$rank}</td>
                        <td>{$row['symbol']}</td>
                        <td>{$row['company_name']}</td>
                        <td>{$row['trade_count']}</td>
                      </tr>";
                $rank++;
            }
        } else {
            echo "<tr><td colspan='4'>❌ คุณยังไม่มีประวัติการซื้อขาย</td></tr>";
        }
        $stmt->close();
        ?>
    </table>

    <h3>💰 หุ้นที่คุณลงทุนมากที่สุด (Top 3)</h3>
    <table border="1">
        <tr>
            <th>อันดับ</th>
            <th>Symbol</th>
            <th>Company Name</th>
            <th>จำนวนเงินลงทุน</th>
        </tr>
        <?php
        $sql = "SELECT s.symbol, s.company_name, SUM(sl.total_invested) AS total_invested
                FROM stock_lot sl
                JOIN stock s ON sl.stockid = s.stockid
                JOIN portfolio p ON sl.portid = p.portid
                WHERE p.username = ?
                GROUP BY s.symbol, s.company_name
                ORDER BY total_invested DESC
                LIMIT 3";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rank = 1;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$rank}</td>
                        <td>{$row['symbol']}</td>
                        <td>{$row['company_name']}</td>
                        <td>".number_format($row['total_invested'], 2)." บาท</td>
                      </tr>";
                $rank++;
            }
        } else {
            echo "<tr><td colspan='4'>❌ คุณยังไม่มีการลงทุน</td></tr>";
        }
        $stmt->close();
        ?>
    </table>

    <h3>📅 คำนวณ Net Profit/Loss ในช่วงที่กำหนด</h3>
    <form method="GET" action="">
        <label>📆 วันที่เริ่มต้น:</label>
        <input type="date" name="start_date" required>

        <label>📆 วันที่สิ้นสุด:</label>
        <input type="date" name="end_date" required>

        <button type="submit">🔍 คำนวณ</button>
    </form>

    <?php
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];

        // ✅ ดึง Net Profit/Loss เฉพาะหุ้นที่ปิดการขายในช่วงวันที่กำหนด
        $sql = "SELECT SUM(profit_loss) AS net_profit_loss 
                FROM stock_lot 
                WHERE portid IN (SELECT portid FROM portfolio WHERE username = ?) 
                AND status = 'CLOSED'
                AND sell_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $profit_data = $result->fetch_assoc();
        $stmt->close();

        $net_profit_loss = $profit_data['net_profit_loss'] ?? 0;
        $profit_color = ($net_profit_loss >= 0) ? 'green' : 'red';

        echo "<h4>📈 ผลกำไร/ขาดทุนในช่วง <strong>$start_date</strong> ถึง <strong>$end_date</strong></h4>";
        echo "<p><strong>💰 Net Profit/Loss:</strong> 
            <span style='color: $profit_color;'>".number_format($net_profit_loss, 2)." บาท</span></p>";
    }
    ?>

    <h3>🔍 ค้นหาพอร์ตของคุณ</h3>
    <form method="GET" action="port_details.php">
        <label>กรอก Port ID:</label>
        <input type="text" name="portid" required>
        <button type="submit">🔎 ค้นหา</button>
    </form>

    <br><br>
    <a href="logout.php">Logout</a>
</body>
</html>
