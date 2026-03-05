<?php
session_start();
require_once "db.php";

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

/* ================= USER INFO ================= */
$stmt = $con->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email);
$stmt->fetch();
$stmt->close();

/* ================= GET LATEST MEMBERSHIP ================= */
$stmt = $con->prepare("
    SELECT plan, amount, start_date, end_date
    FROM memberships
    WHERE email = ?
    ORDER BY end_date DESC
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$membership = $result->fetch_assoc();
$stmt->close();

/* ================= DETERMINE STATUS ================= */
$status = null;
if ($membership) {
  $status = ($membership['end_date'] < date('Y-m-d')) ? "Expired" : "Active";
}

/* ================= SHOW PLANS IF NEW OR RENEW ================= */
$showPlans = false;
if (!$membership || isset($_POST['renew'])) {
  $showPlans = true;
}

/* ================= FETCH PLANS ================= */
$plans = [];
if ($showPlans) {
  $res = $con->query("
        SELECT plan_id, name, duration_months, price, description
        FROM membership_plans
        ORDER BY price ASC
    ");
  while ($row = $res->fetch_assoc()) {
    $plans[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>GymEdge | Membership</title>

  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: radial-gradient(circle at top, #111 0%, #000 60%);
      color: #fff;
    }

    .page-container {
      max-width: 1150px;
      margin: auto;
      padding: 60px 20px;
    }

    .plan-card {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      padding: 30px;
      border: 1px solid rgba(245, 180, 0, .25);
      margin-bottom: 25px;
      animation: fadeUp .6s ease;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(25px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .price {
      font-size: 26px;
      font-weight: bold;
      color: #f5b400;
    }

    .status-active {
      color: #2aff2a;
      font-weight: bold;
    }

    .status-expired {
      color: #ff4d4d;
      font-weight: bold;
    }

    button {
      padding: 10px 20px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      background: #f5b400;
      font-weight: bold;
      transition: .3s;
    }

    button:hover {
      transform: translateY(-2px);
    }

    a {
      color: #f5b400;
      text-decoration: none;
    }

    .qr-box {
      margin-top: 30px;
      text-align: center;
      padding: 25px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 15px;
      border: 1px solid rgba(245, 180, 0, .3);
      animation: fadeUp .8s ease;
    }

    .qr-box img {
      margin-top: 15px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(245, 180, 0, .4);
    }

    .qr-box p {
      color: #aaa;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="page-container">

    <a href="index.php">← Back</a>

    <h1>Welcome, <?= strtoupper(htmlspecialchars($full_name)); ?> 👋</h1>
    <p>Email: <?= htmlspecialchars($email); ?></p>

    <?php if ($membership && !$showPlans): ?>

      <div class="plan-card">
        <h2>🏋️ <?= htmlspecialchars($membership['plan']); ?> Plan</h2>
        <p class="price">₹<?= number_format($membership['amount'], 2); ?></p>

        <p>📅 Start: <?= date('d M Y', strtotime($membership['start_date'])); ?></p>
        <p>⏳ End: <?= date('d M Y', strtotime($membership['end_date'])); ?></p>

        <p>Status:
          <span class="<?= $status === 'Active' ? 'status-active' : 'status-expired'; ?>">
            <?= $status; ?>
          </span>
        </p>

        <?php if ($status === "Expired"): ?>
          <form method="POST">
            <button name="renew">🔄 Renew Membership</button>
          </form>
        <?php endif; ?>

        <br><br>
        <a href="renewal_history.php">📜 View Renewal History</a>

    <?php endif; ?>


    <?php if ($showPlans): ?>

      <h2 style="margin-top:40px;">Choose Your Membership Plan</h2>

      <?php foreach ($plans as $plan): ?>
        <div class="plan-card">
          <h2><?= htmlspecialchars($plan['name']); ?></h2>
          <p>Duration: <?= $plan['duration_months']; ?> Month(s)</p>
          <p class="price">₹<?= number_format($plan['price'], 2); ?></p>

          <ul>
            <?php foreach (explode("\n", $plan['description']) as $d): ?>
              <li><?= htmlspecialchars($d); ?></li>
            <?php endforeach; ?>
          </ul>

          <form method="POST" action="payment.php">
            <input type="hidden" name="plan_id" value="<?= $plan['plan_id']; ?>">
            <button type="submit">Choose Plan</button>
          </form>
        </div>
      <?php endforeach; ?>

    <?php endif; ?>

  </div>
</body>

</html>