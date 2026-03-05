<?php
session_start();
require_once __DIR__ . '/db.php';
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["reply" => "Please log in first to chat with FitBot."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = "User";

// Fetch user name
$stmt = $con->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($r = $res->fetch_assoc()) $user_name = $r['full_name'];
$stmt->close();

// Read user input
$data = json_decode(file_get_contents("php://input"), true);
$user_message = strtolower(trim($data['message'] ?? ''));

// Default fallback
$reply = "🤔 Sorry {$user_name}, I couldn't find anything related to that. You can ask about your membership, attendance, diet chart, or trainer schedule.";

// Keyword-based table detection
$keywords = [
    'memberships' => ['membership', 'plan', 'package', 'renew', 'joined'],
    'membership_payments' => ['payment', 'fee', 'amount', 'paid', 'bill'],
    'attendance' => ['attendance', 'present', 'absent', 'checkin'],
    'diet_charts' => ['diet', 'meal', 'food', 'nutrition'],
    'trainer_schedule' => ['trainer', 'schedule', 'coach', 'training'],
    'user_schedule' => ['workout', 'routine', 'exercise'],
    'notices' => ['notice', 'announcement', 'news'],
    'videos' => ['video', 'tutorial', 'demo'],
    'membership_plans' => ['plan list', 'available plans', 'packages']
];

$matched_table = null;
foreach ($keywords as $table => $words) {
    foreach ($words as $word) {
        if (strpos($user_message, $word) !== false) {
            $matched_table = $table;
            break 2;
        }
    }
}

// Helper: Get full summary
function get_summary($con, $user_id, $user_name)
{
    $reply = "📊 Here's your complete GymEdge summary, {$user_name}:\n\n";

    // Membership
    $sql = "SELECT plan_name, start_date, end_date FROM memberships WHERE user_id=? ORDER BY id DESC LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $reply .= $r ? "💪 Membership: {$r['plan_name']} ({$r['start_date']} → {$r['end_date']})\n" : "💪 No membership yet.\n";
    $stmt->close();

    // Attendance
    $sql = "SELECT attendance_date, status FROM attendance WHERE user_id=? ORDER BY attendance_date DESC LIMIT 3";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $reply .= "📅 Recent Attendance:\n";
    if ($res->num_rows > 0) while ($a = $res->fetch_assoc()) $reply .= "- {$a['attendance_date']} ({$a['status']})\n";
    else $reply .= "No attendance found.\n";
    $stmt->close();

    // Diet
    $sql = "SELECT chart_name, description FROM diet_charts WHERE user_id=? LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $reply .= $r ? "🥗 Diet Chart: {$r['chart_name']} — {$r['description']}\n" : "🥗 No diet chart.\n";
    $stmt->close();

    return $reply;
}

// MAIN LOGIC
if (preg_match('/everything|all info|my account/i', $user_message)) {
    $reply = get_summary($con, $user_id, $user_name);
} elseif ($matched_table) {
    switch ($matched_table) {
        case 'memberships':
            $sql = "SELECT plan_name, start_date, end_date FROM memberships WHERE user_id=? ORDER BY id DESC LIMIT 1";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $reply = $r
                ? "💪 {$user_name}, your membership plan is '{$r['plan_name']}' from {$r['start_date']} to {$r['end_date']}."
                : "You haven’t selected any membership plan yet, {$user_name}.";
            $stmt->close();
            break;

        case 'attendance':
            $sql = "SELECT attendance_date, status FROM attendance WHERE user_id=? ORDER BY attendance_date DESC LIMIT 5";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $reply = "📅 Attendance:\n";
            if ($res->num_rows > 0)
                while ($a = $res->fetch_assoc()) $reply .= "- {$a['attendance_date']} ({$a['status']})\n";
            else $reply .= "No attendance records.";
            $stmt->close();
            break;

        case 'diet_charts':
            $sql = "SELECT chart_name, description FROM diet_charts WHERE user_id=? LIMIT 1";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $reply = $r ? "🥗 Diet Chart: {$r['chart_name']} — {$r['description']}" : "No diet chart found for you.";
            $stmt->close();
            break;

        case 'trainer_schedule':
            $sql = "SELECT trainer_name, day, time_slot FROM trainer_schedule WHERE user_id=? ORDER BY id DESC LIMIT 3";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $reply = "🏋️ Trainer Schedule:\n";
            if ($res->num_rows > 0)
                while ($t = $res->fetch_assoc()) $reply .= "{$t['trainer_name']} - {$t['day']} ({$t['time_slot']})\n";
            else $reply .= "No trainer schedule found.";
            $stmt->close();
            break;

        case 'notices':
            $res = $con->query("SELECT title, message FROM notices ORDER BY id DESC LIMIT 3");
            $reply = "📢 Notices:\n";
            if ($res->num_rows > 0)
                while ($n = $res->fetch_assoc()) $reply .= "- {$n['title']}: {$n['message']}\n";
            else $reply .= "No notices available.";
            break;

        default:
            $reply = "I found something related to {$matched_table}, but I don’t have custom data for it yet.";
    }
} elseif (strpos($user_message, 'hi') !== false || strpos($user_message, 'hello') !== false) {
    $reply = "👋 Hi {$user_name}! I'm FitBot. Ask me about your membership, attendance, or diet chart!";
} elseif (strpos($user_message, 'nice') !== false) {
    $reply = "Nice! 😄 Keep going, {$user_name}!";
} elseif (strpos($user_message, 'thank') !== false) {
    $reply = "You're welcome, {$user_name}! 💪";
} elseif (strpos($user_message, 'bye') !== false) {
    $reply = "Goodbye {$user_name}! Stay fit and consistent 💪";
}

// Log chat
$stmt = $con->prepare("INSERT INTO fitbot_chat (user_id, user_name, message, reply) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $user_name, $user_message, $reply);
$stmt->execute();
$stmt->close();

// Return JSON
echo json_encode(["reply" => $reply]);
