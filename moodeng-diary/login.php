<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลการเข้าสู่ระบบ
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['username'] = $username;
        header("Location: diary.php");
        exit();
    } else {
        $error = "ชื่อผู้ใช้ หรือ รหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
        <h2>เข้าสู่ระบบ</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <button type="submit">เข้าสู่ระบบ</button>
            <p>หากยังไม่มีบัญชี <a href="register.php">สมัครใช้งาน</a></p>
        </form>
    </div>
</body>
</html>
