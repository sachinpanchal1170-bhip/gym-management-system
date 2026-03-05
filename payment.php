<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$email     = $_SESSION['email'] ?? '';

$plan_id = $_POST['plan_id'] ?? 0;

if (!$plan_id) {
    die("No plan selected.");
}

$stmt = $con->prepare("
    SELECT name, price, duration_months
    FROM membership_plans
    WHERE plan_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();
$planData = $result->fetch_assoc();
$stmt->close();

if (!$planData) {
    die("Invalid plan selected.");
}

$plan     = $planData['name'];
$amount   = $planData['price'];
$duration = $planData['duration_months'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {

    $payment_method = $_POST['payment_method'];

    $txn_id = uniqid("TXN");

    $stmt = $con->prepare("
        INSERT INTO membership_payments
        (user_id, membership_type, amount, payment_method, txn_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isdss",
        $user_id,
        $plan,
        $amount,
        $payment_method,
        $txn_id
    );
    $stmt->execute();
    $stmt->close();

    $stmt = $con->prepare("
        SELECT end_date FROM memberships
        WHERE email = ?
        ORDER BY end_date DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastMembership = $result->fetch_assoc();
    $stmt->close();

    $start_date = ($lastMembership && $lastMembership['end_date'] >= date('Y-m-d'))
        ? $lastMembership['end_date']
        : date('Y-m-d');

    $end_date = date('Y-m-d', strtotime("+$duration months", strtotime($start_date)));

    $stmt = $con->prepare("
        INSERT INTO memberships
        (email, plan, amount, start_date, end_date)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssdss",
        $email,
        $plan,
        $amount,
        $start_date,
        $end_date
    );
    $stmt->execute();
    $stmt->close();

    header("Location: success.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Payment</title>

    <style>
        body {
            background: radial-gradient(circle at top, #111, #000);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #eee;
        }

        .container {
            width: 100%;
            max-width: 480px;
            padding: 30px;
            background: #141414;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(255, 215, 0, .15);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #333;
            background: #222;
            color: #fff;
        }

        input:focus,
        select:focus {
            border-color: gold;
            outline: none;
        }

        button {
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            font-weight: bold;
            cursor: pointer;
        }

        .payment-section {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: all .4s ease;
        }

        .payment-section.active {
            max-height: 400px;
            opacity: 1;
        }

        .error {
            color: #ff4d4d;
            font-size: 13px;
        }

        .card-info {
            color: gold;
            font-weight: bold;
        }
    </style>

    <script>
        function togglePaymentFields() {
            const method = document.getElementById('payment_method').value;
            document.querySelectorAll('.payment-section').forEach(el => el.classList.remove('active'));

            if (method === "Card") document.getElementById('cardSection').classList.add('active');
            if (method === "UPI") document.getElementById('upiSection').classList.add('active');
            if (method === "Netbanking") document.getElementById('netbankingSection').classList.add('active');
        }

        /* ALL CARD TYPES */
        function detectCardType() {
            const num = document.getElementById("card_number").value.replace(/\D/g, '');
            const display = document.getElementById("card_type");
            let type = "";

            if (/^4/.test(num)) type = "Visa";
            else if (/^5[1-5]/.test(num)) type = "MasterCard";
            else if (/^3[47]/.test(num)) type = "American Express";
            else if (/^6(?:011|5)/.test(num)) type = "Discover";
            else if (/^6/.test(num)) type = "RuPay";
            else if (/^35/.test(num)) type = "JCB";
            else if (/^30[0-5]/.test(num)) type = "Diners Club";
            else if (num.length > 0) type = "Unknown Card";

            display.innerText = type ? "Detected: " + type : "";
        }

        /* VALIDATION */
        function validateForm() {

            const method = document.getElementById("payment_method").value;
            if (method !== "Card") return true;

            let valid = true;

            const number = document.getElementById("card_number").value.trim();
            const expiry = document.getElementById("card_expiry").value.trim();
            const cvv = document.getElementById("card_cvv").value.trim();

            document.getElementById("num_err").innerText = "";
            document.getElementById("exp_err").innerText = "";
            document.getElementById("cvv_err").innerText = "";

            if (number === "") {
                document.getElementById("num_err").innerText = "Enter card number";
                valid = false;
            }

            if (expiry === "") {
                document.getElementById("exp_err").innerText = "Enter expiry date";
                valid = false;
            }

            if (cvv === "") {
                document.getElementById("cvv_err").innerText = "Enter CVV";
                valid = false;
            }

            if (expiry !== "") {
                const today = new Date();
                const currentMonth = today.getMonth() + 1;
                const currentYear = today.getFullYear() % 100;

                let parts = expiry.split("/");
                if (parts.length === 2) {
                    let month = parseInt(parts[0]);
                    let year = parseInt(parts[1]);

                    if (year < currentYear || (year === currentYear && month < currentMonth)) {
                        document.getElementById("exp_err").innerText = "Card expired";
                        valid = false;
                    }
                }
            }

            return valid;
        }
    </script>
</head>

<body>

    <div class="container">
        <h2>💳 Payment</h2>

        <p><strong><?= htmlspecialchars($plan) ?></strong></p>
        <p><?= $duration ?> Month(s)</p>
        <p>₹<?= number_format((float)$amount, 2) ?></p>

        <form method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="plan_id" value="<?= $plan_id ?>">

            <select name="payment_method" id="payment_method" onchange="togglePaymentFields()" required>
                <option value="">-- Select --</option>
                <option value="Card">Card</option>
                <option value="UPI">UPI</option>
                <option value="Netbanking">Netbanking</option>
            </select>

            <div id="cardSection" class="payment-section">
                <input type="text" id="card_number" placeholder="Card Number" onkeyup="detectCardType()">
                <div id="num_err" class="error"></div>
                <div id="card_type" class="card-info"></div>

                <input type="text" id="card_expiry" placeholder="MM/YY">
                <div id="exp_err" class="error"></div>

                <input type="text" id="card_cvv" placeholder="CVV">
                <div id="cvv_err" class="error"></div>
            </div>

            <div id="upiSection" class="payment-section">
                <input type="text" placeholder="yourupi@bank">
            </div>

            <div id="netbankingSection" class="payment-section">
                <select>
                    <option>Select Bank</option>
                    <option>HDFC</option>
                    <option>SBI</option>
                    <option>ICICI</option>
                    <option>AXIS</option>
                </select>
            </div>

            <button type="submit">Pay Now</button>
        </form>
    </div>

</body>

</html>