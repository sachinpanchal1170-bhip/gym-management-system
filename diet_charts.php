<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $con->prepare("SELECT profile_photo, gender FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_photo, $user_gender);
$stmt->fetch();
$stmt->close();
$profile_photo = $profile_photo ?: "default.png";

// Fetch diet chart
$dietQuery = $con->prepare("
  SELECT d.*, t.full_name AS trainer_name, t.gender AS trainer_gender
  FROM diet_charts d
  JOIN trainers t ON d.trainer_id = t.id
  WHERE d.user_id = ?
  ORDER BY d.assigned_at DESC
");
$dietQuery->bind_param("i", $user_id);
$dietQuery->execute();
$diets = $dietQuery->get_result();

// Fetch custom meals
$customMeals = [];
$getMeals = $con->prepare("SELECT meal_type, item_name, quantity FROM user_diet_custom WHERE user_id = ?");
$getMeals->bind_param("i", $user_id);
$getMeals->execute();
$res = $getMeals->get_result();
while ($row = $res->fetch_assoc()) {
  $customMeals[$row['meal_type']][] = $row;
}
$getMeals->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diet Charts - GymEdge Fitness Center</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: linear-gradient(180deg, #000 0%, #111 40%, #000 100%);
      color: #fff;
      overflow-x: hidden;
    }

    /* ===== NAVBAR ===== */
    header.nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.85);
      border-bottom: 1px solid rgba(224, 163, 0, 0.3);
      z-index: 100;
    }

    .nav__inner {
      max-width: 1500px;
      margin: 0 auto;
      padding: 10px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
      text-decoration: none;
    }

    .brand__logo {
      width: 50px;
      border-radius: 50%;
      box-shadow: 0 0 10px #e0a300;
    }

    .brand__text {
      font-weight: 800;
      font-size: 14px;
      text-transform: uppercase;
    }

    .menu {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .menu a {
      color: #fff;
      text-decoration: none;
      font-weight: 700;
      font-size: 14px;
      transition: color .3s;
    }

    .menu a:hover {
      color: #e0a300;
    }

    .avatar__circle {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      border: 2px solid #e0a300;
      object-fit: cover;
    }

    .btn--primary {
      background: #e0a300;
      color: #000;
      font-weight: 800;
      padding: 10px 18px;
      border-radius: 999px;
      text-decoration: none;
      transition: all .3s;
    }

    /* ===== DIET SECTION ===== */
    .diet-section {
      padding: 140px 8%;
      min-height: 100vh;
      background: linear-gradient(180deg, rgba(10, 10, 10, 0.9), rgba(0, 0, 0, 0.95));
      animation: fadeIn 1.2s ease-in-out;
    }

    .diet-section h2 {
      color: #e0a300;
      font-size: 36px;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 30px;
      text-shadow: 0 0 10px rgba(224, 163, 0, 0.4);
      text-align: center;
    }

    .diet-card {
      background: rgba(20, 20, 20, 0.9);
      border: 1px solid rgba(224, 163, 0, 0.3);
      border-radius: 16px;
      box-shadow: 0 0 30px rgba(224, 163, 0, 0.2);
      padding: 30px;
      margin: 0 auto 40px;
      max-width: 850px;
      text-align: left;
      cursor: pointer;
      overflow: hidden;
      transition: all .4s ease;
    }

    .diet-card:hover {
      transform: scale(1.02);
      box-shadow: 0 0 25px rgba(224, 163, 0, 0.3);
    }

    .diet-card h3 {
      color: #e0a300;
      margin-bottom: 10px;
    }

    .diet-card small {
      color: #999;
    }

    .diet-preview,
    .diet-full {
      transition: all .3s ease;
    }

    .diet-full {
      display: none;
      padding-top: 10px;
    }

    .diet-preview p,
    .diet-full p {
      margin: 0;
      color: #ccc;
    }

    ul {
      margin: 5px 0 10px 20px;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>
  <header class="nav">
    <div class="nav__inner">
      <a class="brand" href="#">
        <img src="assets/img/gym_logo1.jpg" class="brand__logo" alt="">
        <span class="brand__text">GYMEDGE<br><small>FITNESS CENTER</small></span>
      </a>
      <nav class="menu">
        <a href="index.php">HOME</a>
        <a href="aboutus.php">ABOUT</a>
        <a href="workout.php">TRACKING</a>
        <a href="attendance.php">ATTENDANCE</a>
        <a href="notice.php">NOTICE</a>
        <a href="membership.php">MEMBERSHIPS</a>
        <a href="user_schedule.php">SCHEDULE</a>
        <a href="diet_charts.php" style="color:#e0a300;">DIET CHART</a>
        <a href="contactus.php">CONTACT US</a>
        <a href="profile.php"><img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" class="avatar__circle"></a>
        <a href="logout.php" class="btn--primary">LOGOUT</a>
      </nav>
    </div>
  </header>

  <section class="diet-section">
    <h2>Your Diet Chart</h2>

    <?php if ($diets->num_rows > 0): ?>
      <?php while ($d = $diets->fetch_assoc()): ?>
        <div class="diet-card" onclick="toggleExpand(this)">
          <h3>Assigned by <?= htmlspecialchars($d['trainer_name']) ?> (<?= htmlspecialchars($d['trainer_gender']) ?>)</h3>

          <div class="diet-preview">
            <?php
            $lines = explode("\n", trim($d['diet_chart']));
            $preview = array_slice($lines, 0, 2);
            ?>
            <p><?= nl2br(htmlspecialchars(implode("\n", $preview))) ?><?= count($lines) > 2 ? ' ...' : '' ?></p>
          </div>

          <div class="diet-full">
            <?php if (!empty($customMeals)): ?>
              <h4 style="color:#e0a300;">Your Customized Meal Plan</h4>
              <?php foreach ($customMeals as $mealType => $items): ?>
                <p><strong><?= htmlspecialchars($mealType) ?></strong></p>
                <ul>
                  <?php foreach ($items as $i): ?>
                    <li><?= htmlspecialchars($i['item_name']) ?> — <?= htmlspecialchars($i['quantity']) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endforeach; ?>
            <?php else: ?>
              <p><?= nl2br(htmlspecialchars($d['diet_chart'])) ?></p>
            <?php endif; ?>

            <small>Assigned at: <?= htmlspecialchars($d['assigned_at']) ?></small>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;">No diet chart assigned yet.</p>
    <?php endif; ?>
  </section>

  <script>
    function toggleExpand(card) {
      const full = card.querySelector('.diet-full');
      const preview = card.querySelector('.diet-preview');
      if (full.style.display === 'none' || full.style.display === '') {
        full.style.display = 'block';
        preview.style.display = 'none';
      } else {
        full.style.display = 'none';
        preview.style.display = 'block';
      }
    }
  </script>

</body>

</html>