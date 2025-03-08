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

// เมื่อกดปุ่มอัปเดตข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_email = $_POST['email'];
    $new_phone = $_POST['phone'];
    $new_firstname = $_POST['firstname'];
    $new_lastname = $_POST['lastname'];

    $update_sql = "UPDATE users 
                   SET email = ?, phone = ?, firstname = ?, lastname = ? 
                   WHERE username = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssss", $new_email, $new_phone, $new_firstname, $new_lastname, $username);

    if ($stmt->execute()) {
        echo "<script>alert('อัปเดตข้อมูลสำเร็จ!'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด! กรุณาลองใหม่');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>อัปเดตข้อมูลผู้ใช้</title>
</head>
<body>
    <h2>✏️ อัปเดตข้อมูลผู้ใช้</h2>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required><br>

        <label>Firstname:</label>
        <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required><br>

        <label>Lastname:</label>
        <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required><br>

        <button type="submit">อัปเดตข้อมูล</button>
    </form>
    <br>
    <a href="dashboard.php">⬅️ กลับไปที่ Dashboard</a>
</body>
</html>
