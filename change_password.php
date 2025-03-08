<?php
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $current_password = $_POST['current_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // ดึงรหัสผ่านปัจจุบันจาก Database
    $sql = "SELECT password_hash FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // ตรวจสอบว่ารหัสผ่านปัจจุบันถูกต้องหรือไม่
    if (password_verify($current_password, $hashed_password)) {
        // อัปเดตรหัสผ่านใหม่
        $update_sql = "UPDATE users SET password_hash = ? WHERE username = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ss", $new_password, $username);

        if ($stmt->execute()) {
            echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จ! กรุณา Login ใหม่'); window.location.href = 'logout.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด!');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('รหัสผ่านปัจจุบันไม่ถูกต้อง!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>เปลี่ยนรหัสผ่าน</title>
</head>
<body>
    <h2>🔑 เปลี่ยนรหัสผ่าน</h2>
    <form method="POST">
        <label>รหัสผ่านปัจจุบัน:</label>
        <input type="password" name="current_password" required><br>

        <label>รหัสผ่านใหม่:</label>
        <input type="password" name="new_password" required><br>

        <button type="submit">ยืนยันการเปลี่ยนรหัสผ่าน</button>
    </form>
    <br>
    <a href="dashboard.php">⬅️ กลับไปที่ Dashboard</a>
</body>
</html>
