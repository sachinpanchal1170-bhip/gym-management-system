<?php
session_start();
require_once "db.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt = $con->prepare("SELECT full_name, email, phone, age, gender, profile_photo FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $age       = trim($_POST['age']);
    $gender    = trim($_POST['gender']);

    
    $profile_photo = $user['profile_photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $profile_photo = time() . "_" . basename($_FILES['photo']['name']);
        $target_path   = $upload_dir . $profile_photo;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
            
            if (!empty($user['profile_photo']) && file_exists($upload_dir . $user['profile_photo'])) {
                unlink($upload_dir . $user['profile_photo']);
            }
        } else {
            $profile_photo = $user['profile_photo']; 
        }
    }

    
    $stmt = $con->prepare("UPDATE users SET full_name=?, phone=?, age=?, gender=?, profile_photo=? WHERE user_id=?");
    $stmt->bind_param("ssissi", $full_name, $phone, $age, $gender, $profile_photo, $user_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Profile</title>
    <style>
        <?php 
        ?>body {
            font-family: Arial, sans-serif;
            background: #000;
            color: #eee;
        }

        .container {
            width: 400px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #1c1c1c;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        h2 {
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
            color: #FFA500;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 15px;
            border: 2px solid #FFA500;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #555;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
            background: #2c2c2c;
            color: #fff;
        }

        input[type="file"] {
            padding: 3px;
            background: #1c1c1c;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: black;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: scale(1.02);
        }

        .back-btn {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            margin-top: 10px;
            color: black;
        }

        .back-btn:hover {
            transform: scale(1.02);
        }

        small {
            color: #aaa;
        }

        p {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>My Profile</h2>

        <?php if (!empty($_SESSION['message'])): ?>
            <p style="color: #4CAF50; text-align:center;">
                <?php echo $_SESSION['message'];
                unset($_SESSION['message']); ?>
            </p>
        <?php endif; ?>

        <p>
            <a href="uploads/<?php echo htmlspecialchars($user['profile_photo'] ?: 'default.png'); ?>" target="_blank" rel="noopener noreferrer">
                <img class="profile-pic" src="uploads/<?php echo htmlspecialchars($user['profile_photo'] ?: 'default.png'); ?>" alt="Profile Photo">
            </a>
        </p>

        
        <form method="POST" enctype="multipart/form-data">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <label>Age:</label>
            <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>

            <label>Gender:</label>
            <input type="text" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>" required>

            <label>Change Profile Photo:</label>
            <input type="file" name="photo" accept="image/*">
            <?php if (!empty($user['profile_photo'])): ?>
                <small>Current file: <?php echo htmlspecialchars($user['profile_photo']); ?></small>
            <?php endif; ?>

            <button type="submit">Update Profile</button>
        </form>

        <form action="index.php">
            <button type="submit" class="back-btn">Back</button>
        </form>
    </div>
    <script>
        const profilePic = document.querySelector('.profile-pic');
        profilePic.addEventListener('click', function(event) {
            event.preventDefault();
            const url = this.parentElement.getAttribute('href');
            const img = new Image();
            img.src = url;
            img.onload = function() {
                const width = this.naturalWidth;
                const height = this.naturalHeight;
                window.open(url, '_blank', `width=${width},height=${height},resizable=yes,scrollbars=yes`);
            }
        });
    </script>

</body>

</html>