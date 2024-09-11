<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการลงทะเบียน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบว่าผู้ใช้มีอยู่แล้วหรือไม่
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $error = "ชื่อผู้ใช้มีผู้อื่นใช้แล้ว ลองใช้ชื่ออื่น";
    } else {
        // เพิ่มผู้ใช้ใหม่
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $password]);
        $_SESSION['username'] = $username;
        header("Location: diary.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
    <h1>
    <span style="color: maroon;">M</span>
    <span style="color: red;">O</span>
    <span style="color: orange;">O</span>
    <span style="color: pink;">D</span>
    <span style="color: fuchsia;">E</span>
    <span style="color: lime;">N</span>
    <span style="color: cyan;">G</span>
</h1>
        <h2>สมัครสมาชิก</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <button type="submit">สร้างบัญชีใหม่</button>
            <p>มีบัญชีผู้ใช้แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
        </form>
    </div>
</body>
</html>
