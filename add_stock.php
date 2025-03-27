<?php
include 'db_connect.php';

if (!isset($_GET['portid'])) {
    echo "<script>alert('❌ ไม่พบ Port ID!'); window.location.href = 'dashboard.php';</script>";
    exit();
}

$portid = $_GET['portid'];
$message = ""; // ตัวแปรสำหรับแสดงข้อความแจ้งเตือน

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $symbol = strtoupper(trim($_POST['symbol']));
    $company_name = trim($_POST['company_name']);

    // ตรวจสอบว่าหุ้นนี้มีอยู่แล้วหรือไม่
    $sql_check = "SELECT stockid FROM stock WHERE symbol = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("s", $symbol);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "❌ หุ้นนี้มีอยู่แล้วในระบบ!";
    } else {
        $stmt->close();

        // เพิ่มหุ้นใหม่เข้าสู่ระบบ
        $sql_insert = "INSERT INTO stock (symbol, company_name) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("ss", $symbol, $company_name);

        if ($stmt->execute()) {
            $message = "✅ เพิ่มหุ้นสำเร็จ!";
        } else {
            $message = "❌ เกิดข้อผิดพลาด! กรุณาลองใหม่";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>เพิ่มหุ้นใหม่</title>
</head>
<body>
    <h2>➕ เพิ่มหุ้นใหม่</h2>

    <?php if (!empty($message)): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <label>สัญลักษณ์หุ้น (Symbol):</label>
        <input type="text" name="symbol" required><br>

        <label>ชื่อบริษัท:</label>
        <input type="text" name="company_name" required><br>

        <button type="submit">เพิ่มหุ้น</button>
    </form>
    <br>
    <a href="port_details.php?portid=<?php echo $portid; ?>">⬅️ กลับไปที่รายละเอียดพอร์ต</a>
</body>
</html>
