<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['notice'])) {
    $noticeText = trim($_POST['notice']);
    $createdAt = date('Y-m-d H:i:s');
    $authorRole = 'admin';
    $authorName = $adminUsername;

    if ($noticeText === '') {
        $errorMessage = 'Notice cannot be empty.';
    } else {
        $stmt = $con->prepare("INSERT INTO notices (notice_text, full_name, author_role, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $noticeText, $authorName, $authorRole, $createdAt);
        if ($stmt->execute()) {
            header("Location: admin_notices.php");
            exit;
        } else {
            $errorMessage = "Failed to post notice.";
        }
    }
}

$noticesResult = $con->query("
    SELECT notice_text, full_name, author_role, created_at 
    FROM notices 
    WHERE author_role IN ('admin', 'trainer') 
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Notices</title>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body {
            background: #0d0d0d;
            color: #eee;
            font-family: 'Poppins', sans-serif;
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }

        h1,
        h2 {
            text-align: center;
            color: #f0b400;
            letter-spacing: 1px;
        }

        form {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 0 15px rgba(255, 165, 0, 0.15);
            animation: fadeInUp 0.6s ease;
        }

        textarea {
            width: 100%;
            height: 90px;
            padding: 12px;
            border-radius: 10px;
            border: none;
            resize: none;
            font-size: 1rem;
            background: #252525;
            color: #f1f1f1;
            outline: none;
        }

        button {
            background: #f0b400;
            border: none;
            color: #111;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: #d99d00;
            transform: translateY(-2px);
        }

        .message {
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
        }

        .success {
            color: #a6d608;
        }

        .error {
            color: #ff4c4c;
        }

        .notice-list {
            margin-top: 20px;
            animation: fadeInUp 0.8s ease;
        }

        .notice {
            background: #181818;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: 0.3s ease;
            animation: fadeInUp 0.6s ease;
            position: relative;
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.05);
        }

        .notice:hover {
            background: #202020;
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.2);
        }

        .notice:first-child {
            border: 1px solid #f0b400;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.3);
        }

        .text {
            font-size: 1rem;
            line-height: 1.5;
            color: #f3f3f3;
        }

        .meta {
            font-size: 0.9rem;
            color: #bbb;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        /* 🧑‍💼 Role badges for clarity */
        .role-badge {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            color: #fff;
            font-weight: bold;
            margin-right: 8px;
        }

        .admin {
            background: #ff9800;
        }

        .trainer {
            background: #28a745;
        }

        .author {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .datetime {
            font-style: italic;
            color: #aaa;
        }

        .back-btn {
            display: inline-block;
            background: #444;
            color: #fff;
            padding: 10px 15px;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #666;
        }
    </style>
</head>

<body>
    <h1>📢 Admin Notice Board</h1>

    <?php if ($successMessage): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="notice"><strong>Write a new notice:</strong></label><br />
        <textarea name="notice" id="notice" placeholder="Type your notice here..." required></textarea><br />
        <button type="submit">Post Notice</button>
        <a href="admin.php" class="back-btn">⬅ Back</a>
    </form>

    <h2>🕒 Recent Notices</h2>
    <div class="notice-list">
        <?php if ($noticesResult->num_rows === 0): ?>
            <p style="text-align:center;color:#888;">No notices yet.</p>
        <?php else: ?>
            <?php while ($notice = $noticesResult->fetch_assoc()): ?>
                <div class="notice">
                    <div class="text"><?= nl2br(htmlspecialchars($notice['notice_text'])) ?></div>
                    <div class="meta">
                        <div class="author">
                            <span class="role-badge <?= htmlspecialchars($notice['author_role']) ?>">
                                <?= ucfirst(htmlspecialchars($notice['author_role'])) ?>
                            </span>
                            <?= htmlspecialchars($notice['full_name']) ?>
                        </div>
                        <div class="datetime"><?= date('d M Y, h:i A', strtotime($notice['created_at'])) ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>

</html>