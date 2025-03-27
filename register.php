<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 🔹 เข้ารหัสรหัสผ่าน
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];

    // ตรวจสอบว่า username มีอยู่แล้วหรือไม่
    $sql = "SELECT username FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('❌ Username นี้มีอยู่แล้ว! กรุณาเลือกชื่ออื่น'); window.location.href = 'register.php';</script>";
        exit();
    }
    $stmt->close();

    // เพิ่มข้อมูลลงในฐานข้อมูล
    $sql = "INSERT INTO users (username, password_hash, email, phone, firstname, lastname) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $password, $email, $phone, $firstname, $lastname);

    if ($stmt->execute()) {
        echo "<script>alert('✅ สมัครสมาชิกสำเร็จ!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('❌ เกิดข้อผิดพลาด! กรุณาลองใหม่'); window.location.href = 'register.php';</script>";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form method="POST">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Email: <input type="email" name="email" required><br>
        Phone: <input type="text" name="phone"><br>
        Firstname: <input type="text" name="firstname"><br>
        Lastname: <input type="text" name="lastname"><br>
        <button type="submit">สมัครสมาชิก</button>
    </form>
    <br>
    <a href="index.php">กลับไปหน้า Login</a>
</body>
</html>
