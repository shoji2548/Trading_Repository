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
    <title>จัดการพอร์ตลงทุน</title>
</head>
<body>
    <h2>➕ จัดการพอร์ตลงทุน</h2>

    <h3>🏛 เพิ่มโบรกเกอร์ใหม่</h3>
    <form method="POST" action="add_broker.php">
        <label>ชื่อโบรกเกอร์:</label>
        <input type="text" name="broker_name" required><br>
        <button type="submit">เพิ่มโบรกเกอร์</button>
    </form>

    <h3>📋 รายชื่อโบรกเกอร์ที่มีอยู่</h3>
    <table border="1">
        <tr>
            <th>Broker ID</th>
            <th>Broker Name</th>
        </tr>
        <?php
        $sql = "SELECT brokerid, broker_name FROM broker";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['brokerid']}</td>
                        <td>{$row['broker_name']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>❌ ไม่มีข้อมูลโบรกเกอร์</td></tr>";
        }
        ?>
    </table>

    <h3>📋 รายการบัญชีที่เชื่อมอยู่</h3>
    <table border="1">
        <tr>
            <th>เลขบัญชีธนาคาร</th>
            <th>ชื่อย่อธนาคาร</th>
        </tr>
        <?php
        $sql = "SELECT b.account_number, bk.bank_shortname 
                FROM bank_account b 
                JOIN bank bk ON b.bankid = bk.bankid";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['account_number']}</td>
                        <td>{$row['bank_shortname']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>❌ ไม่มีข้อมูลบัญชีธนาคาร</td></tr>";
        }
        ?>
    </table>
    
    <h3>➕ สร้างพอร์ตการลงทุน</h3>
    <form method="POST" action="add_portfolio.php">
        <label>Port ID:</label>
        <input type="text" name="portid" required><br>

        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $_SESSION['username']; ?>" readonly><br>

        <label>เลือกโบรกเกอร์:</label>
        <select name="brokerid">
            <?php
            $result = $conn->query("SELECT brokerid, broker_name FROM broker");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['brokerid']}'>{$row['broker_name']}</option>";
            }
            ?>
        </select><br>

        <label>เลือกบัญชีธนาคาร:</label>
        <select name="account_number">
            <?php
            $result = $conn->query("SELECT b.account_number, bk.bank_shortname FROM bank_account b 
                                    JOIN bank bk ON b.bankid = bk.bankid");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['account_number']}'>{$row['account_number']} ({$row['bank_shortname']})</option>";
            }
            ?>
        </select><br>

        <label>Balance เริ่มต้น:</label>
        <input type="number" step="0.01" name="balance" required><br>

        <button type="submit">สร้างพอร์ตลงทุน</button>
    </form>


    <br>
    <a href="dashboard.php">⬅️ กลับไปที่ Dashboard</a>
</body>
</html>
