<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch user photo
$profile_photo = "default.png";
$stmt = $con->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_photo);
if ($stmt->fetch() && !empty($db_photo)) {
  $profile_photo = $db_photo;
}
$stmt->close();

// ✅ Check today's attendance only
$attendance_status = 'not_marked'; // Default if not marked today
$today = date('Y-m-d');

$check = $con->prepare("
  SELECT TRIM(LOWER(status))
  FROM attendance
  WHERE user_id = ? AND DATE(attendance_date) = ?
  ORDER BY id DESC
  LIMIT 1
");
$check->bind_param("is", $user_id, $today);
$check->execute();
$check->bind_result($db_status);

if ($check->fetch()) {
  $attendance_status = $db_status;
}
$check->close();

// ✅ Decide badge color and label
$badge_color = '#888'; // default gray
$badge_text = 'Not Marked Yet';

if ($attendance_status === 'present') {
  $badge_color = '#28a745'; // green
  $badge_text = 'Present';
} elseif ($attendance_status === 'absent') {
  $badge_color = '#dc3545'; // red
  $badge_text = 'Absent';
}

// ✅ Fetch videos only if Absent
$videos = null;
if ($attendance_status === 'absent') {
  $videos = $con->query("SELECT * FROM videos ORDER BY created_at ASC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GymEdge Fitness Center - FitBot</title>
  <link rel="icon" href="data:," />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      height: 100%;
      font-family: system-ui, sans-serif;
      background: #000;
      color: #fff;
      overflow-x: hidden;
    }

    /* NAVBAR */
    .nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(6px);
      z-index: 100;
      animation: slideDown 0.8s ease forwards;
    }

    .nav__inner {
      max-width: 1500px;
      margin: 0 auto;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .brand {
      color: #fff;
      display: flex;
      gap: 10px;
      align-items: center;
      text-decoration: none;
      transition: transform .3s ease;
      margin-left: -70px;
    }

    .brand:hover {
      transform: scale(1.05);
    }

    .brand__logo {
      width: 55px;
      border-radius: 50%;
      box-shadow: 0 0 10px #e0a300;
    }

    .brand__text {
      font-weight: 800;
      letter-spacing: 1px;
      font-size: 14px;
      text-transform: uppercase;
    }

    .brand__text small {
      font-weight: 600;
      opacity: .8;
    }

    .menu {
      display: flex;
      align-items: center;
      gap: 50px;
    }

    .menu a {
      color: #fff;
      text-decoration: none;
      font-weight: 800;
      letter-spacing: .8px;
      font-size: 14px;
      position: relative;
      transition: color .3s ease;
    }

    .menu a::after {
      content: "";
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 0;
      height: 2px;
      background: #e0a300;
      transition: width .3s ease;
    }

    .menu a:hover::after {
      width: 100%;
    }

    .menu a:hover {
      color: #e0a300;
    }

    .avatar__circle {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      border: 2px solid #e0a300;
      object-fit: cover;
      transition: transform .3s ease, box-shadow .3s ease;
    }

    .avatar__circle:hover {
      transform: scale(1.1);
      box-shadow: 0 0 10px #e0a300;
    }

    .btn {
      font-weight: 800;
      padding: 12px 20px;
      border-radius: 999px;
      text-decoration: none;
      letter-spacing: .6px;
      border: 0;
      cursor: pointer;
      transition: all .3s ease;
    }

    .btn--primary {
      background: #e0a300;
      color: #000;
      box-shadow: 0 0 10px #e0a30050;
    }

    .btn--primary:hover {
      transform: scale(1.05);
      background-color: #fff;
      box-shadow: 0 0 15px #e0a30080;
    }

    /* HERO SECTION */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 100vh;
      padding: 0 8%;
      background: #000;
    }

    .hero__image {
      flex: 1;
      background: url("assets/img/hero.jpg") center/cover no-repeat;
      height: 100%;
      transform: scale(1.05);
      transition: transform 2s ease;
    }

    .hero:hover .hero__image {
      transform: scale(1.1);
    }

    .hero-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      max-width: 600px;
      padding-left: 40px;
      animation: slideUp 1.2s ease forwards;
    }

    .eyebrow {
      color: #e0a300;
      font-weight: 700;
      letter-spacing: .8px;
      margin-bottom: 18px;
    }

    .headline {
      font-size: clamp(48px, 9vw, 90px);
      font-weight: 900;
      line-height: 1;
      letter-spacing: 1px;
      margin-bottom: 20px;
    }

    .sub {
      color: #cfcfcf;
      font-size: 18px;
      margin-bottom: 32px;
    }

    .cta {
      display: flex;
      gap: 16px;
    }

    /* VIDEO GALLERY */
    .video-section {
      padding: 100px 8%;
      background: linear-gradient(180deg, #000 0%, #0a0a0a 100%);
      text-align: center;
    }

    .video-section h2 {
      color: #e0a300;
      font-size: 32px;
      margin-bottom: 20px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .video-section p {
      color: #ccc;
      font-size: 18px;
      margin-bottom: 40px;
    }

    .filter-bar {
      margin-bottom: 40px;
    }

    .filter-btn {
      background: #111;
      color: #e0a300;
      border: 1px solid #e0a300;
      padding: 10px 18px;
      margin: 5px;
      border-radius: 999px;
      font-weight: 700;
      cursor: pointer;
      transition: all .3s ease;
    }

    .filter-btn.active,
    .filter-btn:hover {
      background: #e0a300;
      color: #000;
    }

    .video-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      justify-content: center;
    }

    .video-card {
      background: #111;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 0 15px rgba(224, 163, 0, 0.3);
      transition: transform .3s ease, opacity .3s ease;
    }

    .video-card.hide {
      opacity: 0;
      transform: scale(0.95);
      pointer-events: none;
    }

    .video-card iframe {
      width: 100%;
      height: 200px;
      border: none;
    }

    .video-card h3 {
      padding: 15px;
      color: #e0a300;
      font-size: 18px;
      text-transform: uppercase;
      background: #000;
    }

    /* TRAINER SECTION */
    .trainer-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 100px 8%;
      background: #0a0a0a;
      gap: 40px;
    }

    .trainer-info {
      flex: 1;
    }

    .trainer-info h2 {
      color: #e0a300;
      font-size: 34px;
      margin-bottom: 20px;
    }

    .trainer-info p {
      color: #ccc;
      line-height: 1.6;
      font-size: 18px;
    }

    .trainer-carousel {
      flex: 1;
      position: relative;
      overflow: hidden;
      border-radius: 20px;
      box-shadow: 0 0 15px rgba(224, 163, 0, 0.4);
      height: 400px;
    }

    .trainer-carousel img {
      width: 100%;
      height: 400px;
      object-fit: cover;
      border-radius: 20px;
      position: absolute;
      top: 0;
      left: 100%;
      opacity: 0;
      transition: all 1s ease;
    }

    .trainer-carousel img.active {
      left: 0;
      opacity: 1;
    }

    @keyframes slideDown {
      from {
        transform: translateY(-100%);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(60px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* GYM INFO + SLIDER */
    .gym-info-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 2rem;
      padding: 80px 8%;
      background: #000;
      flex-wrap: wrap;
    }

    .gym-info-left {
      flex: 1;
      max-width: 500px;
    }

    .gym-info-left h2 {
      color: #e0a300;
      font-size: 32px;
      margin-bottom: 15px;
    }

    .gym-info-left p {
      color: #ccc;
      line-height: 1.6;
      margin-bottom: 20px;
      font-size: 18px;
    }

    .gym-slider {
      flex: 1;
      max-width: 700px;
      height: 500px;
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 0 25px rgba(224, 163, 0, 0.3);
    }

    .gym-info {
      flex: 1;
    }

    .gym-info p {
      color: #ccc;
      line-height: 2.0;
      font-size: 18px;
    }

    .gym-info h2 {
      color: #e0a300;
      font-size: 34px;
      margin-bottom: 20px;
    }

    .gym-slider img {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 1s ease-in-out;
    }

    .gym-slider img.active {
      opacity: 1;
    }

    .gym-look {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 100px 8%;
      background: #0a0a0a;
      gap: 40px;
    }

    /* small responsive tweaks so chatbot doesn't block small screens */
    @media (max-width: 700px) {
      .nav__inner {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .hero {
        flex-direction: column;
        height: auto;
        padding-top: 90px;
      }

      .hero__image {
        display: none;
      }
    }
  </style>
</head>

<body>
  <!-- NAVBAR -->
  <header class="nav">
    <div class="nav__inner">
      <a class="brand" href="index.php">
        <img src="assets/img/gym_logo1.jpg" alt="Logo" class="brand__logo">
        <span class="brand__text">GYMEDGE<br><small>FITNESS CENTER</small></span>
      </a>

      <nav class="menu" id="menu">
        <a href="index.php">HOME</a>
        <a href="aboutus.php">ABOUT</a>
        <a href="workout.php">TRACKING</a>
        <a href="attendance.php">ATTENDANCE</a>
        <a href="notice.php">NOTICE</a>
        <a href="membership.php">MEMBERSHIPS</a>
        <a href="user_schedule.php">SCHEDULE</a>
        <a href="diet_charts.php">DIET CHART</a>
        <a href="contactus.php">CONTACT US</a>

        <div style="display: flex; align-items: center; gap: 15px;">
          <a href="profile.php" class="avatar" title="Account">
            <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="avatar__circle">
          </a>
          <a href="logout.php" class="btn btn--primary">LOGOUT</a>
        </div>
      </nav>
    </div>
  </header>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">
      <p class="eyebrow">KEEP YOUR BODY FITNESS WITH WORKOUTS</p>
      <h1 class="headline">YOUR FITNESS<br />YOUR VICTORY</h1>
      <p class="sub">READY TO CHANGE YOUR PHYSIQUE</p>
      <div class="cta">
        <a href="membership.php" class="btn btn--primary">JOIN WITH US</a>
        <a href="#videos" class="btn btn--primary" style="background:#222;color:#e0a300;border:1px solid #e0a300;">WATCH WORKOUTS</a>
      </div>
    </div>

    <!-- hero image restored -->
    <div class="hero__image"></div>
  </section>

  <!-- ✅ Conditional Video Section -->
  <?php if ($attendance_status === 'absent'): ?>
    <section class="video-section" id="videos">
      <h2>Workout Video Gallery</h2>
      <p>Train smart and get motivated with our expert workout tutorials.</p>

      <div class="filter-bar">
        <button class="filter-btn active" data-filter="All">All</button>
        <button class="filter-btn" data-filter="Chest">Chest</button>
        <button class="filter-btn" data-filter="Legs">Legs</button>
        <button class="filter-btn" data-filter="Cardio">Cardio</button>
        <button class="filter-btn" data-filter="Back & Shoulders">Back & Shoulders</button>
        <button class="filter-btn" data-filter="Meditation & Yoga">Meditation & Yoga</button>
        <button class="filter-btn" data-filter="Biceps">Biceps</button>
        <button class="filter-btn" data-filter="Triceps">Triceps</button>
        <button class="filter-btn" data-filter="Abs">Abs</button>
      </div>

      <div class="video-grid">
        <?php if ($videos): while ($row = $videos->fetch_assoc()): ?>
            <div class="video-card" data-category="<?= htmlspecialchars($row['category']) ?>">
              <iframe src="<?= htmlspecialchars($row['video_url']) ?>" allowfullscreen></iframe>
              <h3><?= htmlspecialchars($row['title']) ?></h3>
            </div>
        <?php endwhile;
        endif; ?>
      </div>
    </section>
  <?php elseif ($attendance_status === 'present'): ?>
    <section class="video-section" id="videos" style="text-align:center;padding:120px 0;">
      <h2 style="color:#28a745;">You're Present Today!</h2>
      <p style="color:#ccc;font-size:18px;">Since you attended the gym, workout videos are not visible for today. 💪</p>
    </section>
  <?php else: ?>
    <section class="video-section" id="videos" style="text-align:center;padding:120px 0;">
      <h2 style="color:#888;">Attendance Not Marked Yet</h2>
      <p style="color:#ccc;font-size:18px;">Your attendance has not been recorded yet. Please check back later.</p>
    </section>
  <?php endif; ?>

  <!-- 💪 New Gym Info + Image Slider Section -->
  <section class="gym-look">
    <div class="gym-info">
      <h2>GymEdge Experience</h2>
      <p>Step inside GymEdge and experience the energy, discipline, and dedication that drives every workout.
        Every rep, every drop of sweat, and every goal achieved makes you stronger than yesterday.</p>
    </div>

    <div class="gym-slider">
      <!-- restored images (ensure these files exist in assets/img/) -->
      <img src="assets/img/gymimg.jpg" class="active" alt="Gym Image 1">
      <img src="assets/img/gymimg2.jpg" alt="Gym Image 2">
      <img src="assets/img/gymimg3.jpg" alt="Gym Image 3">
      <img src="assets/img/gymimg4.jpg" alt="Gym Image 4">
      <img src="assets/img/gymimg5.jpg" alt="Gym Image 5">
      <img src="assets/img/gymimg6.jpg" alt="Gym Image 6">
      <img src="assets/img/gymimg7.jpg" alt="Gym Image 7">
    </div>
  </section>

  <!-- Trainer Section -->
  <section class="trainer-section">
    <div class="trainer-info">
      <h2>Meet Our Expert Trainer</h2>
      <p>Our trainers are certified professionals with years of experience helping clients achieve their fitness goals. Personalized training, expert advice, and constant motivation—everything you need to succeed!</p>
    </div>
    <div class="trainer-carousel">
      <img src="assets/img/trainer1.jpg" class="active" alt="Trainer 1">
      <img src="assets/img/trainer2.jpg" alt="Trainer 2">
      <img src="assets/img/trainer3.jpg" alt="Trainer 3">
    </div>
  </section>
  <!-- 🧠 FitBot Chat Bubble -->
  <div id="fitbot-bubble">💬</div>

  <!-- Chat Window -->
  <div id="fitbot-window">
    <div class="fitbot-header">
      🤖 FitBot <span id="fitbot-close">&times;</span>
    </div>
    <div id="fitbot-messages"></div>
    <div class="fitbot-input">
      <input type="text" id="fitbot-msg" placeholder="Ask something about the gym..." />
      <button id="fitbot-send">Send</button>
    </div>
  </div>

  <style>
    /* 🟡 Chat Bubble */
    #fitbot-bubble {
      position: fixed;
      bottom: 25px;
      right: 25px;
      background: #e0a300;
      color: #000;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      font-weight: bold;
      cursor: pointer;
      box-shadow: 0 0 10px #e0a300;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      z-index: 9999;
    }

    #fitbot-bubble:hover {
      transform: scale(1.1);
      box-shadow: 0 0 20px #e0a30080;
    }

    /* 🟡 Chat Window */
    #fitbot-window {
      position: fixed;
      bottom: 100px;
      right: 25px;
      width: 340px;
      height: 450px;
      background: #111;
      border: 2px solid #e0a300;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(224, 163, 0, 0.4);
      display: none;
      flex-direction: column;
      overflow: hidden;
      z-index: 9999;
    }

    .fitbot-header {
      background: #e0a300;
      color: #000;
      font-weight: bold;
      padding: 12px;
      text-align: center;
      position: relative;
    }

    .fitbot-header #fitbot-close {
      position: absolute;
      right: 15px;
      top: 0;
      font-size: 22px;
      cursor: pointer;
    }

    #fitbot-messages {
      flex: 1;
      padding: 10px;
      overflow-y: auto;
      font-size: 14px;
    }

    .fitbot-msg {
      margin: 8px 0;
      padding: 8px 12px;
      border-radius: 12px;
      max-width: 85%;
      line-height: 1.4;
      word-wrap: break-word;
    }

    .fitbot-user {
      background: #e0a300;
      color: #000;
      margin-left: auto;
      border-bottom-right-radius: 0;
    }

    .fitbot-bot {
      background: #333;
      color: #fff;
      border-bottom-left-radius: 0;
    }

    .fitbot-input {
      display: flex;
      border-top: 1px solid #333;
    }

    .fitbot-input input {
      flex: 1;
      padding: 10px;
      background: #000;
      color: #fff;
      border: none;
      outline: none;
      border-radius: 0;
    }

    .fitbot-input button {
      background: #e0a300;
      border: none;
      color: #000;
      font-weight: bold;
      cursor: pointer;
      padding: 10px 15px;
      transition: background 0.3s;
    }

    .fitbot-input button:hover {
      background: #fff;
    }
  </style>
  <script>
    const bubble = document.getElementById("fitbot-bubble");
    const windowBox = document.getElementById("fitbot-window");
    const closeBtn = document.getElementById("fitbot-close");
    const sendBtn = document.getElementById("fitbot-send");
    const input = document.getElementById("fitbot-msg");
    const msgBox = document.getElementById("fitbot-messages");

    // Open / Close bubble
    bubble.addEventListener("click", () => {
      windowBox.style.display = "flex";
      bubble.style.display = "none";

      // 🧠 Show welcome message when opened
      msgBox.innerHTML = "";
      addMessage("Hi, I'm FitBot! 🤖 How can I help you today?", "bot");
    });

    closeBtn.addEventListener("click", () => {
      windowBox.style.display = "none";
      bubble.style.display = "flex";
    });

    // Send message
    sendBtn.addEventListener("click", sendMessage);
    input.addEventListener("keypress", e => {
      if (e.key === "Enter") sendMessage();
    });

    async function sendMessage() {
      const text = input.value.trim();
      if (!text) return;
      addMessage(text, "user");
      input.value = "";

      const res = await fetch("chatbot.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          message: text
        })
      });

      const data = await res.json();
      addMessage(data.reply, "bot");
    }

    function addMessage(text, sender) {
      const div = document.createElement("div");
      div.className = `fitbot-msg fitbot-${sender}`;
      div.textContent = text;
      msgBox.appendChild(div);
      msgBox.scrollTop = msgBox.scrollHeight;
    }
  </script>

</body>

</html>