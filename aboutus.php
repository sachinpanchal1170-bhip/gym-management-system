<?php
session_start();
require_once "db.php";

$profile_photo = "default.png";
if (isset($_SESSION['user_id'])) {
    $stmt = $con->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($db_photo);
    if ($stmt->fetch() && !empty($db_photo)) {
        $profile_photo = $db_photo;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - GymEdge Fitness Center</title>
    <style>
        /* ---------- Global Styles ---------- */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #000;
            color: #fff;
            line-height: 1.6;
            overflow-x: hidden;
            animation: fadeInBody 1s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ---------- Navbar ---------- */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(6px);
            animation: slideDownNav 0.8s ease-in-out;
        }

        @keyframes slideDownNav {
            from {
                transform: translateY(-60px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .nav__inner {
            max-width: 1300px;
            margin: auto;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            gap: 10px;
            align-items: center;
            text-decoration: none;
            color: #fff;
        }

        .brand__logo {
            width: 50px;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .brand__logo:hover {
            transform: rotate(10deg) scale(1.05);
        }

        .brand__text {
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
        }

        .menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .menu a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: color 0.3s;
        }

        .menu a::after {
            content: '';
            position: absolute;
            width: 0%;
            height: 2px;
            background: #e0a300;
            left: 0;
            bottom: -5px;
            transition: width 0.3s;
        }

        .menu a:hover {
            color: #e0a300;
        }

        .menu a:hover::after {
            width: 100%;
        }

        .avatar__circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #e0a300;
            object-fit: cover;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .avatar__circle:hover {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(224, 163, 0, 0.6);
        }

        /* ---------- Section ---------- */
        .section {
            padding: 120px 20px 60px;
            max-width: 1100px;
            margin: auto;
            animation: fadeInSection 1s ease-in-out;
        }

        @keyframes fadeInSection {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section h1 {
            text-align: center;
            font-size: 40px;
            margin-bottom: 20px;
            color: #e0a300;
            animation: glowText 2s ease-in-out infinite alternate;
        }

        @keyframes glowText {
            from {
                text-shadow: 0 0 10px #e0a300;
            }

            to {
                text-shadow: 0 0 25px #ffcc33, 0 0 10px #e0a300;
            }
        }

        .section p {
            font-size: 18px;
            text-align: center;
            color: #ccc;
            margin-bottom: 40px;
            animation: fadeInText 1.2s ease-in-out;
        }

        @keyframes fadeInText {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ---------- Team Section ---------- */
        .team {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .team-member {
            background: #111;
            border-radius: 14px;
            padding: 25px;
            text-align: center;
            transition: transform 0.4s, box-shadow 0.4s;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUpMember 1s ease forwards;
        }

        .team-member:nth-child(1) {
            animation-delay: 0.3s;
        }

        .team-member:nth-child(2) {
            animation-delay: 0.5s;
        }

        .team-member:nth-child(3) {
            animation-delay: 0.7s;
        }

        @keyframes fadeUpMember {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: 0 0 25px rgba(224, 163, 0, 0.3);
        }

        .team-member img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #e0a300;
            object-fit: cover;
            margin-bottom: 15px;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .team-member img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(224, 163, 0, 0.4);
        }

        .team-member h3 {
            margin: 0;
            color: #fff;
        }

        .team-member p {
            color: #bbb;
            font-size: 14px;
        }

        /* ---------- Footer ---------- */
        footer {
            text-align: center;
            padding: 30px 20px;
            background: #0a0a0a;
            border-top: 1px solid #222;
            color: #aaa;
            font-size: 14px;
            margin-top: 60px;
            animation: fadeInFooter 1.2s ease-in-out;
        }

        @keyframes fadeInFooter {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="nav">
        <div class="nav__inner">
            <a class="brand" href="index.php">
                <img src="assets/img/gym_logo1.jpg" alt="Logo" class="brand__logo">
                <span class="brand__text">GYMEDGE<br><small>FITNESS CENTER</small></span>
            </a>
            <nav class="menu">
                <a href="index.php">HOME</a>
                <a href="aboutus.php">ABOUT</a>
                <a href="workout.php">TRACKING</a>
                <a href="attendance.php">ATTENDANCE</a>
                <a href="notice.php">NOTICE</a>
                <a href="membership.php">MEMBERSHIPS</a>
                <a href="contactus.php">CONTACT US</a>
                <a class="avatar" href="profile.php">
                    <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile" class="avatar__circle">
                </a>
            </nav>
        </div>
    </header>

    <!-- About Section -->
    <section class="section">
        <h1>About Us</h1>
        <p>At GymEdge Fitness Center, we believe fitness is not just about workouts – it's a lifestyle.
            Our mission is to empower you with the right environment, expert guidance, and motivation to achieve your health goals.</p>

        <div class="team">
            <div class="team-member">
                <img src="assets/img/Profile.jpeg" alt="Trainer">
                <h3>Darji Neel</h3>
                <p>Head Trainer & Nutritionist</p>
            </div>
            <div class="team-member">
                <img src="assets/img/Profile2.jpg" alt="Trainer">
                <h3>Panchal Sachin</h3>
                <p>Strength & Conditioning Coach</p>
            </div>
            <div class="team-member">
                <img src="assets/img/Profile3.jpeg" alt="Trainer">
                <h3>Patel Jaimil</h3>
                <p>Cardio & Endurance Specialist</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        &copy; <?= date("Y") ?> GymEdge Fitness Center. All Rights Reserved.
    </footer>

</body>

</html>