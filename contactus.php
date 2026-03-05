<?php
$host = "localhost";
$dbname = "gymedge";
$username = "root";
$password = "";

$messageSent = false;
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        $errorMessage = "Connection failed: " . $conn->connect_error;
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $message = trim($_POST['message']);
        if (empty($name) || empty($email)) {
            $errorMessage = "Name and email are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Invalid email format.";
        } else {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $message);
            if ($stmt->execute()) {
                $messageSent = true;
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GymEdge Contact</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top left, #0b0b0b, #121212);
            color: white;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            animation: fadeInBody 1s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2,
        h4 {
            text-align: center;
            color: #ffb700;
            text-shadow: 0 0 10px rgba(255, 183, 0, 0.3);
            animation: slideDown 1s ease-in-out;
        }

        h4 {
            color: #ddd;
            margin-bottom: 40px;
            font-weight: 400;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto;
            padding-bottom: 80px;
            animation: fadeInUp 1.2s ease-in-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-box {
            background: #1c1c1c;
            border-radius: 20px;
            padding: 50px;
            width: 850px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            box-shadow: 0 0 20px rgba(255, 183, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 25px rgba(255, 183, 0, 0.25);
        }

        form {
            flex: 1 1 350px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: slideLeft 1.2s ease;
        }

        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        label {
            font-weight: 600;
            color: #ffb700;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 13px;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid transparent;
            background: #fff;
            color: black;
            font-weight: bold;
            font-size: 15px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #ffb700;
            box-shadow: 0 0 10px rgba(255, 183, 0, 0.5);
            outline: none;
        }

        textarea {
            height: 130px;
            resize: vertical;
        }

        button {
            cursor: pointer;
            background: linear-gradient(135deg, #ffb700, #ff9500);
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            color: black;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.4s ease;
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.3);
        }

        button:hover {
            transform: scale(1.07);
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.6);
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .status-message {
            font-weight: bold;
            text-align: center;
            color: #ffb700;
            background: rgba(255, 183, 0, 0.1);
            padding: 10px;
            border-radius: 8px;
            animation: fadeInStatus 1s ease-in-out;
        }

        @keyframes fadeInStatus {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .error {
            color: #ff6666;
            background: rgba(255, 50, 50, 0.1);
        }

        .info-text {
            flex: 1 1 350px;
            text-align: center;
            animation: slideRight 1.2s ease;
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .brand__logo {
            width: 230px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(255, 183, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .brand__logo:hover {
            transform: scale(1.05);
            box-shadow: 0 0 25px rgba(255, 183, 0, 0.4);
        }

        .info-text p {
            color: #ddd;
            line-height: 1.8em;
            font-weight: 500;
        }

        @media (max-width: 900px) {
            .contact-box {
                flex-direction: column;
                width: 90%;
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <h2>GET IN TOUCH</h2>
    <h4>We'd love to hear from you. Reach out for memberships, inquiries, or support.</h4>

    <div class="container">
        <div class="contact-box">
            <form id="contactForm" method="POST" action="" onsubmit="return validateForm()">
                <h2 style="color:#ffb700;">LET'S TALK</h2>

                <?php if ($messageSent): ?>
                    <div class="status-message">✅ Thank you for reaching out! We'll get back to you soon.</div>
                <?php elseif (!empty($errorMessage)): ?>
                    <div class="status-message error">⚠️ <?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required />

                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required />

                <label for="message">Your Message</label>
                <textarea id="message" name="message" placeholder="Type something if you want..."></textarea>

                <div class="button-group">
                    <button type="submit">Submit</button>
                    <button type="button" onclick="window.location.href='index.php';">Back</button>
                </div>
            </form>

            <div class="info-text">
                <img src="assets/img/gym_logo1.jpg" alt="Logo" class="brand__logo">
                <p>
                    📍 XYZ Gym, Near City Center, Ahmedabad<br>
                    📞 +91 98765 43210<br>
                    ✉️ support@gymedge.com
                </p>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            if (!name) {
                alert('Please enter your name.');
                return false;
            }
            if (!email) {
                alert('Please enter your email.');
                return false;
            }
            const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>