<?php
session_start();
require_once __DIR__ . '/db.php';

// ✅ Handle Add Video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_video'])) {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $video_url = trim($_POST['video_url']);

    if (!empty($title) && !empty($category) && !empty($video_url)) {
        $stmt = $con->prepare("INSERT INTO videos (title, category, video_url, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $title, $category, $video_url);
        $stmt->execute();
        $_SESSION['msg'] = "✅ Video added successfully!";
    } else {
        $_SESSION['msg'] = "⚠️ Please fill all fields.";
    }
    header("Location: manage_videos.php");
    exit;
}

// ✅ Handle Delete Video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $con->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = "🗑️ Video deleted successfully!";
    header("Location: manage_videos.php");
    exit;
}

// ✅ Handle Update Video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_video'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $video_url = trim($_POST['video_url']);

    if (!empty($title) && !empty($category) && !empty($video_url)) {
        $stmt = $con->prepare("UPDATE videos SET title = ?, category = ?, video_url = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $category, $video_url, $id);
        $stmt->execute();
        $_SESSION['msg'] = "✅ Video updated successfully!";
    } else {
        $_SESSION['msg'] = "⚠️ Please fill all fields.";
    }
    header("Location: manage_videos.php");
    exit;
}

// ✅ Handle Reset ID Sequence
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_ids'])) {
    $con->query("SET @count = 0");
    $con->query("UPDATE videos SET id = (@count := @count + 1) ORDER BY id");
    $con->query("ALTER TABLE videos AUTO_INCREMENT = 1");
    $_SESSION['msg'] = "🔄 Video IDs reset successfully!";
    header("Location: manage_videos.php");
    exit;
}

// ✅ Fetch Videos
$result = $con->query("SELECT * FROM videos ORDER BY created_at ASC");
$message = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Videos - Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #000;
            color: #fff;
            padding: 40px;
            overflow-x: hidden;
        }

        h1 {
            color: #e0a300;
            text-align: center;
            font-size: 2rem;
            animation: fadeDown 1s ease;
        }

        @keyframes fadeDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-box {
            background: #111;
            padding: 20px;
            border-radius: 10px;
            margin: 40px auto;
            max-width: 800px;
            box-shadow: 0 0 15px rgba(224, 163, 0, 0.3);
            animation: fadeUp 1s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #e0a300;
        }

        input,
        select {
            padding: 10px;
            border-radius: 6px;
            border: none;
            width: 100%;
            margin-bottom: 15px;
            background: #222;
            color: #fff;
            transition: box-shadow 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 10px #e0a300;
        }

        .btn {
            background: #e0a300;
            color: #000;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin: 2px;
            transition: all .3s ease;
        }

        .btn:hover {
            background: #fff;
            transform: scale(1.05);
        }

        .message {
            background: #111;
            color: #e0a300;
            padding: 10px 16px;
            border-radius: 6px;
            margin: 20px auto;
            text-align: center;
            width: fit-content;
            box-shadow: 0 0 10px #e0a30070;
            animation: blink 1.5s ease;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .5;
                transform: scale(1.05);
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            animation: fadeUp 1s ease;
        }

        th,
        td {
            padding: 14px;
            border-bottom: 1px solid #333;
            text-align: center;
        }

        th {
            color: #e0a300;
            font-size: 15px;
        }

        tr {
            transition: background .3s ease, transform .2s ease;
        }

        tr:hover {
            background: #111;
            transform: scale(1.01);
        }

        iframe {
            width: 220px;
            height: 130px;
            border-radius: 8px;
            border: 2px solid #222;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        iframe:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(224, 163, 0, 0.5);
        }

        .reset-form {
            text-align: center;
            margin: 20px 0;
            animation: fadeUp 1s ease;
        }
    </style>
</head>

<body>

    <h1>🎬 Manage Workout Videos</h1>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <!-- Add / Edit Form -->
    <div class="form-box">
        <form method="POST" id="videoForm">
            <input type="hidden" name="id" id="edit_id">

            <label>Title:</label>
            <input type="text" name="title" id="edit_title" required>

            <label>Category:</label>
            <select name="category" id="edit_category" required>
                <option value="">-- Select Category --</option>
                <option value="Chest">Chest</option>
                <option value="Legs">Legs</option>
                <option value="Cardio">Cardio</option>
                <option value="Back & Shoulder">Back & Shoulders</option>
                <option value="Meditation & Yoga">Meditation & Yoga</option>
                <option value="Biceps">Biceps</option>
                <option value="Triceps">Triceps</option>
                <option value="Abs">Abs</option>
            </select>

            <label>YouTube Link:</label>
            <input type="text" name="video_url" id="edit_video_url" placeholder="Paste any YouTube link..." required>

            <div style="text-align:center;">
                <button type="submit" name="add_video" class="btn" id="addBtn">➕ Add Video</button>
                <button type="button" class="btn" onclick="window.location.href='admin.php'">↩️ Back</button>
                <button type="submit" name="update_video" class="btn" id="updateBtn" style="display:none;">💾 Update</button>
                <button type="button" class="btn" id="cancelBtn" style="display:none;" onclick="cancelEdit()">❌ Cancel</button>
            </div>
        </form>
    </div>

    <!-- 🔄 Reset ID Sequence -->
    <form method="POST" class="reset-form">
        <button type="submit" name="reset_ids" class="btn" onclick="return confirm('Are you sure you want to resequence all video IDs? This cannot be undone!')">
            🔄 Reset ID Sequence
        </button>
    </form>

    <!-- Video List -->
    <table>
        <tr>
            <th>ID</th>
            <th>Preview</th>
            <th>Title</th>
            <th>Category</th>
            <th>Video URL</th>
            <th>Actions</th>
        </tr>

        <?php $delay = 0;
        while ($row = $result->fetch_assoc()):
            $delay += 0.1; ?>
            <tr style="animation: fadeUp .6s ease <?php echo $delay; ?>s both;">
                <td><?= $row['id'] ?></td>
                <td><iframe src="<?= htmlspecialchars($row['video_url']) ?>" allowfullscreen></iframe></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td style="word-break:break-all;"><?= htmlspecialchars($row['video_url']) ?></td>
                <td>
                    <button class="btn" onclick="editVideo(<?= $row['id'] ?>, '<?= addslashes($row['title']) ?>', '<?= addslashes($row['category']) ?>', '<?= addslashes($row['video_url']) ?>')">✏️ Edit</button>

                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this video?')">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn">🗑️ Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <script>
        // Convert YouTube link to embed format
        document.getElementById("videoForm").addEventListener("submit", function() {
            const input = document.getElementById("edit_video_url");
            let url = input.value.trim();
            const watchPattern = /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/;
            const shortPattern = /youtu\.be\/([a-zA-Z0-9_-]+)/;
            let videoId = null;

            if (watchPattern.test(url)) videoId = url.match(watchPattern)[1];
            else if (shortPattern.test(url)) videoId = url.match(shortPattern)[1];
            if (videoId) input.value = "https://www.youtube.com/embed/" + videoId;
        });

        // Edit mode
        function editVideo(id, title, category, url) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_video_url').value = url;

            document.getElementById('addBtn').style.display = 'none';
            document.getElementById('updateBtn').style.display = 'inline-block';
            document.getElementById('cancelBtn').style.display = 'inline-block';
        }

        // Cancel edit
        function cancelEdit() {
            document.getElementById('videoForm').reset();
            document.getElementById('edit_id').value = '';
            document.getElementById('addBtn').style.display = 'inline-block';
            document.getElementById('updateBtn').style.display = 'none';
            document.getElementById('cancelBtn').style.display = 'none';
        }
    </script>

</body>

</html>