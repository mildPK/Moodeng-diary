<?php
session_start();
require 'db_connection.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบสิทธิ์การลบโพสต์
function canDeletePost($postId, $username) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT username FROM diary_posts WHERE id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    return $post && $post['username'] === $username;
}

// ตรวจสอบสิทธิ์การลบคอมเมนต์
function canDeleteComment($commentId, $username) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT username FROM comments WHERE id = ?');
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();
    return $comment && $comment['username'] === $username;
}

// เพิ่มโพสต์
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    $postContent = $_POST['content'];
    $emoji = $_POST['emoji'];
    $caption = $_POST['caption'];
    $username = $_SESSION['username'];

    $stmt = $pdo->prepare('INSERT INTO diary_posts (username, content, emoji, caption) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, $postContent, $emoji, $caption]);
}

// เพิ่มคอมเมนต์
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $postId = $_POST['post_id'];
    $comment = $_POST['comment'];
    $username = $_SESSION['username'];

    $stmt = $pdo->prepare('INSERT INTO comments (post_id, username, comment) VALUES (?, ?, ?)');
    $stmt->execute([$postId, $username, $comment]);
}

// ลบโพสต์
if (isset($_GET['delete_post']) && canDeletePost($_GET['delete_post'], $_SESSION['username'])) {
    $postId = $_GET['delete_post'];

    $stmt = $pdo->prepare('DELETE FROM diary_posts WHERE id = ?');
    $stmt->execute([$postId]);
}

// ลบคอมเมนต์
if (isset($_GET['delete_comment']) && canDeleteComment($_GET['delete_comment'], $_SESSION['username'])) {
    $commentId = $_GET['delete_comment'];

    $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->execute([$commentId]);
}

// ล็อกเอาต์
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// แสดงโพสต์และคอมเมนต์
$posts = $pdo->query('SELECT * FROM diary_posts ORDER BY created_at DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Diary</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
    <h1>
    <span style="color: maroon;">M</span>
    <span style="color: red;">O</span>
    <span style="color: orange;">O</span>
    <span style="color: pink;">D</span>
    <span style="color: fuchsia;">E</span>
    <span style="color: lime;">N</span>
    <span style="color: cyan;">G</span>
</h1>
        <h1>ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
        <a href="?logout=true" class="logout-button">ออกจากระบบ</a>
        <button id="showMembersBtn">รายชื่อผู้จัดทำ</button>
        <div id="memberModal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%, -50%); padding:20px; border:1px solid #ccc; background-color:#fff;">
            <h3>รายชื่อสมาชิกกลุ่ม</h3>
            <ul id="memberList">
                <li>นายพุฒิเกียรติ แก้วกล้า เลขที่ 28</li>
                <li>นางสาวสุภาวิตา บุญโภคอุดม เลขที่ 30</li>
                <li>นางสาวณัฐชา เท้าสมบุญ เลขที่ 37</li>
                <li>นางสาวธัญชนก สุดธง เลขที่ 38</li>
                <li>นางสาวกัญญวรา สายโส เลขที่ 39</li>
            </ul>
            <button onclick="closeModal()">ปิดหน้าต่าง</button>
        </div>

<script>
document.getElementById("showMembersBtn").onclick = function() {
    document.getElementById("memberModal").style.display = "block";
};

function closeModal() {
    document.getElementById("memberModal").style.display = "none";
}
</script>

        <form method="POST">
            <textarea name="content" placeholder="วันนี้คุณรู้สึกอย่างไร..." required></textarea>
            <input type="text" name="caption" placeholder="เล่าบางอย่าง...">
            <select name="emoji" required>
                <option value="😊">😊</option>
                <option value="😢">😢</option>
                <option value="😡">😡</option>
                <option value="😂">😂</option>
                <option value="😍">😍</option>
            </select>
            <button type="submit">โพสต์</button>
        </form>

        <h2>ไดอารี่</h2>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <p>
                    <strong><?= htmlspecialchars($post['username']); ?></strong> <?= htmlspecialchars($post['emoji']); ?>: <?= htmlspecialchars($post['content']); ?>
                    <?php if ($post['caption']): ?>
                        <em><?= htmlspecialchars($post['caption']); ?></em>
                    <?php endif; ?>
                    <?php if ($post['username'] === $_SESSION['username']): ?>
                        <a href="?delete_post=<?= $post['id']; ?>" class="delete-button">ลบโพสต์</a>
                    <?php endif; ?>
                </p>

                <!-- แสดงคอมเมนต์ใต้โพสต์ -->
                <?php
                $stmt = $pdo->prepare('SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC');
                $stmt->execute([$post['id']]);
                $comments = $stmt->fetchAll();
                ?>
                <div class="comments">
                    <?php foreach ($comments as $comment): ?>
                        <p>
                            <strong><?= htmlspecialchars($comment['username']); ?>:</strong> <?= htmlspecialchars($comment['comment']); ?>
                            <?php if ($comment['username'] === $_SESSION['username']): ?>
                                <a href="?delete_comment=<?= $comment['id']; ?>&post_id=<?= $post['id']; ?>" class="delete-button">ลบ</a>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>

                <!-- เพิ่มคอมเมนต์ -->
                <form method="POST" class="comment-form">
                    <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                    <textarea name="comment" placeholder="แสดงความคิดเห็น..." required></textarea>
                    <button type="submit">แสดงความคิดเห็น</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
