<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>จัดการบัญชีธนาคาร</title>
</head>
<body>
    <h2>🏦 จัดการบัญชีธนาคาร</h2>

    <h3>➕ เพิ่มธนาคารใหม่</h3>
    <form method="POST" action="add_bank.php">
        <label>ชื่อธนาคาร:</label>
        <input type="text" name="bank_name" required><br>
        <label>ชื่อย่อธนาคาร:</label>
        <input type="text" name="bank_shortname" required><br>
        <button type="submit">เพิ่มธนาคาร</button>
    </form>

    <h3>➕ สร้างบัญชีธนาคาร</h3>
    <form method="POST" action="add_bank_account.php">
        <label>เลขบัญชี:</label>
        <input type="text" name="account_number" required><br>
        <label>ธนาคาร:</label>
        <select name="bankid">
            <?php
            $result = $conn->query("SELECT bankid, bank_name FROM bank");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['bankid']}'>{$row['bank_name']}</option>";
            }
            ?>
        </select><br>
        <button type="submit">สร้างบัญชีธนาคาร</button>
    </form>

    <h3>📋 รายการบัญชีที่เชื่อมกับ Port</h3>
    <table border="1">
        <tr>
            <th>Port ID</th>
            <th>เลขบัญชีธนาคาร</th>
            <th>ชื่อย่อธนาคาร</th>
            <th>ยอดเงินในพอร์ต (Balance)</th>
        </tr>
        <?php
        $sql = "SELECT p.portid, b.account_number, bk.bank_shortname, p.balance 
                FROM portfolio p
                JOIN bank_account b ON p.account_number = b.account_number
                JOIN bank bk ON b.bankid = bk.bankid";

        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['portid']}</td>
                        <td>{$row['account_number']}</td>
                        <td>{$row['bank_shortname']}</td>
                        <td>".number_format($row['balance'], 2)." บาท</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>❌ ไม่มีข้อมูลบัญชีที่เชื่อมกับ Port</td></tr>";
        }
        ?>
    </table>

    <h3>💰 ฝากเงินเข้า Port</h3>
    <form method="POST" action="bank_transaction.php">
        <label>Port ID:</label>
        <input type="text" name="portid" required><br>
        
        <label>เลขบัญชีธนาคาร:</label>
        <select name="account_number">
            <?php
            $result = $conn->query("SELECT account_number FROM bank_account");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['account_number']}'>{$row['account_number']}</option>";
            }
            ?>
        </select><br>

        <label>จำนวนเงิน:</label>
        <input type="number" step="0.01" name="amount" required><br>

        <button type="submit">ฝากเงิน</button>
    </form>

    <h3>📜 รายการธุรกรรม Port</h3>
    <table border="1">
        <tr>
            <th>Transaction ID</th>
            <th>Port ID</th>
            <th>เลขบัญชี</th>
            <th>ประเภทธุรกรรม</th>
            <th>จำนวนเงิน</th>
            <th>วันที่ทำรายการ</th>
        </tr>
        <?php
        $sql = "SELECT bt.bank_transacid, bt.portid, bt.account_number, bt.transaction_type, bt.amount, bt.transaction_date
                FROM bank_transaction bt
                JOIN portfolio p ON bt.portid = p.portid
                WHERE p.username = ? 
                ORDER BY bt.bank_transacid ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['bank_transacid']}</td>
                        <td>{$row['portid']}</td>
                        <td>{$row['account_number']}</td>
                        <td>{$row['transaction_type']}</td>
                        <td>".number_format($row['amount'], 2)." บาท</td>
                        <td>{$row['transaction_date']}</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>❌ ไม่มีธุรกรรมฝากเงิน</td></tr>";
        }
        $stmt->close();
        ?>
    </table>

    <br>
    <a href="dashboard.php">⬅️ กลับไปที่ Dashboard</a>
</body>
</html>
