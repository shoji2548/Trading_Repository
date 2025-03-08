<?php
include 'db_connect.php';

if (!isset($_GET['portid'])) {
    echo "<script>alert('❌ ไม่พบ Port ID!'); window.location.href = 'dashboard.php';</script>";
    exit();
}

$portid = $_GET['portid'];
$username = $_SESSION['username'];

// 🔹 ดึงข้อมูลพอร์ต
$sql = "SELECT p.portid, b.broker_name, p.balance 
        FROM portfolio p
        JOIN broker b ON p.brokerid = b.brokerid
        WHERE p.portid = ? AND p.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $portid, $username);
$stmt->execute();
$result = $stmt->get_result();
$portfolio = $result->fetch_assoc();
$stmt->close();

if (!$portfolio) {
    echo "<script>alert('❌ ไม่พบพอร์ตนี้ หรือคุณไม่มีสิทธิ์เข้าถึง!'); window.location.href = 'dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>รายละเอียดพอร์ต</title>
</head>
<body>
    <h2>📊 รายละเอียดพอร์ต</h2>
    <p><strong>Port ID:</strong> <?php echo $portfolio['portid']; ?></p>
    <p><strong>Broker Name:</strong> <?php echo $portfolio['broker_name']; ?></p>
    <p><strong>Balance:</strong> <?php echo number_format($portfolio['balance'], 2); ?> บาท</p>

    <h3>📋 หุ้นที่ถืออยู่</h3>
    <table border="1">
        <tr>
            <th>Stock Lot ID</th>
            <th>Symbol</th>
            <th>Trade Style</th>
            <th>Quantity</th>
            <th>Average Buy Price</th>
            <th>Total Invested</th>
            <th>Buy Date</th>
            <th>Sell Date</th>
            <th>Profit/Loss</th>
            <th>Status</th>
        </tr>
        <?php
        $sql = "SELECT sl.lotid, s.symbol, sl.trade_style, sl.quantity, sl.average_buy_price, 
                sl.total_invested, sl.buy_date, sl.sell_date, sl.profit_loss, sl.status
        FROM stock_lot sl
        JOIN stock s ON sl.stockid = s.stockid
        WHERE sl.portid = ?
        ORDER BY sl.lotid ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $portid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['lotid']}</td>
                        <td>{$row['symbol']}</td>
                        <td>{$row['trade_style']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['average_buy_price']}</td>
                        <td>{$row['total_invested']}</td>
                        <td>{$row['buy_date']}</td>
                        <td>".($row['sell_date'] ? $row['sell_date'] : "-")."</td>
                        <td>{$row['profit_loss']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='10'>❌ ไม่มีหุ้นที่ถืออยู่</td></tr>";
        }
        $stmt->close();
        ?>
    </table>

    <h3>📜 ประวัติการซื้อขายหุ้น</h3>
    <a href="add_stock.php?portid=<?php echo $portid; ?>">
    <button>➕ เพิ่มหุ้นใหม่</button>
    </a>
    <br><br>
    <table border="1">
        <tr>
            <th>Transaction ID</th>
            <th>Symbol</th>
            <th>Trade Style</th>
            <th>Transaction Type</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Date</th>
        </tr>
        <?php
        $sql = "SELECT st.transacid, s.symbol, st.trade_style, st.transaction_type, st.quantity, st.price, st.transaction_date
                FROM stock_transaction st
                JOIN stock s ON st.stockid = s.stockid
                WHERE st.portid = ?
                ORDER BY st.transaction_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $portid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['transacid']}</td>
                        <td>{$row['symbol']}</td>
                        <td>{$row['trade_style']}</td>
                        <td>{$row['transaction_type']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['transaction_date']}</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>❌ ไม่มีประวัติการซื้อขาย</td></tr>";
        }
        $stmt->close();
        ?>
    </table>

    <h3>📈 รายชื่อหุ้นในระบบ</h3>
    <table border="1">
        <tr>
            <th>Stock ID</th>
            <th>Symbol</th>
            <th>Company Name</th>
        </tr>
        <?php
        $sql = "SELECT stockid, symbol, company_name FROM stock";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['stockid']}</td>
                        <td>{$row['symbol']}</td>
                        <td>{$row['company_name']}</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>❌ ไม่มีหุ้นในระบบ</td></tr>";
        }
        ?>
    </table>
    
    <h3>🔎 ดูข้อมูลหุ้น</h3>
    <form method="GET" action="stock_details.php">
        <input type="hidden" name="portid" value="<?php echo $portfolio['portid']; ?>">
        
        <label>สัญลักษณ์หุ้น (Symbol):</label>
        <input type="text" name="symbol" required>
        
        <button type="submit">🔍 ค้นหา</button>
    </form>


    <h3>📊 ดูผลการลงทุน</h3>
    <a href="investment_result_port.php?portid=<?php echo $portid; ?>">
        <button>📈 แสดงผลการลงทุน</button>
    </a>


    <h3>💸 ซื้อ/ขายหุ้น</h3>
    <form method="POST" action="stock_transaction.php">
        <input type="hidden" name="portid" value="<?php echo $portfolio['portid']; ?>">

        <label>สัญลักษณ์หุ้น (Symbol):</label>
        <input type="text" name="symbol" required><br>

        <label>ประเภทการซื้อขาย:</label>
        <select name="trade_style">
            <option value="swing_trade">Swing Trade</option>
            <option value="day_trade">Day Trade</option>
            <option value="run_trend">Run Trend</option>
        </select><br>

        <label>จำนวนหุ้น:</label>
        <input type="number" name="quantity" required><br>

        <label>ราคาต่อหุ้น:</label>
        <input type="number" step="0.01" name="price" required><br>

        <label>เลือกประเภทธุรกรรม:</label>
        <select name="transaction_type">
            <option value="BUY">ซื้อหุ้น</option>
            <option value="SELL">ขายหุ้น</option>
        </select><br>

        <label>วันที่ทำธุรกรรม:</label>
        <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required><br>

        <button type="submit">📈 ดำเนินการ</button>
    </form>
    
    <h3>💵 ถอนเงินจาก Port</h3>
    <form method="POST" action="withdraw_funds.php">
        <input type="hidden" name="portid" value="<?php echo $portfolio['portid']; ?>">

        <p><strong>บัญชีธนาคารที่ใช้ถอน:</strong>  
            <?php
            $sql = "SELECT account_number FROM portfolio WHERE portid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $portfolio['portid']);
            $stmt->execute();
            $result = $stmt->get_result();
            $account = $result->fetch_assoc();
            $stmt->close();
            
            if ($account && $account['account_number']) {
                echo $account['account_number'];
                echo "<input type='hidden' name='account_number' value='{$account['account_number']}'>";
            } else {
                echo "❌ ไม่มีบัญชีที่เชื่อมกับพอร์ตนี้!";
            }
            ?>
        </p>

        <label>จำนวนเงินที่ต้องการถอน:</label>
        <input type="number" step="0.01" name="amount" required><br>

        <button type="submit">💸 ถอนเงิน</button>
    </form>

    <br>
    <a href="dashboard.php">⬅️ กลับไปที่ Dashboard</a>
</body>
</html>
