<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$conn->query("CREATE TABLE IF NOT EXISTS chatbot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$user_id = $_SESSION['user_id'] ?? $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'auto_sync_screen':
        $date = date('Y-m-d');
        $add_seconds = floatval($_POST['seconds'] ?? 0);
        $add_hours = $add_seconds / 3600;

        // Fetch current screen time
        $check = $conn->prepare("SELECT id, screen_time FROM screen_sleep_data WHERE user_id = ? AND date = ?");
        $check->bind_param("is", $user_id, $date);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $new_time = $row['screen_time'] + $add_hours;
            $stmt = $conn->prepare("UPDATE screen_sleep_data SET screen_time = ?, last_sync_at = NOW() WHERE id = ?");
            $stmt->bind_param("di", $new_time, $row['id']);
        } else {
            $new_time = $add_hours;
            $stmt = $conn->prepare("INSERT INTO screen_sleep_data (user_id, date, screen_time, sleep_time, last_sync_at) VALUES (?, ?, ?, ?, NOW())");
            $sleep_default = 0;
            $stmt->bind_param("isdd", $user_id, $date, $add_hours, $sleep_default);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'current_total' => round($new_time, 2)]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;
    case 'save_screen_sleep':
        $date = date('Y-m-d');
        $screen_time = floatval($_POST['screen_time']);
        $sleep_time = floatval($_POST['sleep_time']);

        // Check if entry for today exists
        $check = $conn->prepare("SELECT id FROM screen_sleep_data WHERE user_id = ? AND date = ?");
        $check->bind_param("is", $user_id, $date);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE screen_sleep_data SET screen_time = ?, sleep_time = ? WHERE user_id = ? AND date = ?");
            $stmt->bind_param("ddis", $screen_time, $sleep_time, $user_id, $date);
        }
        else {
            $stmt = $conn->prepare("INSERT INTO screen_sleep_data (user_id, date, screen_time, sleep_time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isdd", $user_id, $date, $screen_time, $sleep_time);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        }
        else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'get_screen_sleep_history':
        $stmt = $conn->prepare("SELECT date, screen_time, sleep_time FROM screen_sleep_data WHERE user_id = ? ORDER BY date DESC LIMIT 7");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => array_reverse($data)]);
        break;

    case 'save_mood':
        $date = date('Y-m-d');
        $mood = $_POST['mood'];

        $stmt = $conn->prepare("INSERT INTO mood_data (user_id, date, mood) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $date, $mood);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        }
        else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'save_quiz':
        $type = $_POST['type'];
        $score = intval($_POST['score']);
        $stress_level = $_POST['stress_level'];
        $date = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, type, score, stress_level, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $user_id, $type, $score, $stress_level, $date);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        }
        else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'save_journal':
        $content = $_POST['content'];
        $sentiment_score = floatval($_POST['sentiment_score']);
        $sentiment_label = $_POST['sentiment_label'];
        $ai_insight = $_POST['ai_insight'] ?? '';
        $expression_type = $_POST['expression_type'] ?? 'unexpressed';

        $stmt = $conn->prepare("INSERT INTO journals (user_id, content, sentiment_score, sentiment_label, expression_type, ai_insight) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsss", $user_id, $content, $sentiment_score, $sentiment_label, $expression_type, $ai_insight);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        }
        else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'save_chat':
        $message = $_POST['message'];
        $response = $_POST['response'];
        $stmt = $conn->prepare("INSERT INTO chatbot_logs (user_id, message, response) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $message, $response);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'get_journals':
        $stmt = $conn->prepare("SELECT content, sentiment_score, sentiment_label, expression_type, ai_insight, date FROM journals WHERE user_id = ? ORDER BY date DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'get_ai_suggestions':
        // Fetch today's metrics
        $stmt = $conn->prepare("SELECT screen_time, sleep_time FROM screen_sleep_data WHERE user_id = ? AND date = CURDATE()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $metrics = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("SELECT mood FROM mood_data WHERE user_id = ? AND date = CURDATE() ORDER BY date DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $mood_data = $stmt->get_result()->fetch_assoc();

        echo json_encode([
            'screen_time' => $metrics['screen_time'] ?? 0,
            'sleep_time' => $metrics['sleep_time'] ?? 0,
            'mood' => $mood_data['mood'] ?? 'Neutral'
        ]);
        break;

    case 'get_comprehensive_data':
        // 1. Mood
        $stmt = $conn->prepare("SELECT mood FROM mood_data WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $mood = $stmt->get_result()->fetch_assoc()['mood'] ?? 'Neutral';

        // 2. Recent Journals
        $stmt = $conn->prepare("SELECT content FROM journals WHERE user_id = ? ORDER BY id DESC LIMIT 3");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $journals = [];
        $res = $stmt->get_result();
        while($r = $res->fetch_assoc()) $journals[] = $r['content'];

        // 3. Recent Chat
        $stmt = $conn->prepare("SELECT message, response FROM chatbot_logs WHERE user_id = ? ORDER BY id DESC LIMIT 3");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $chats = [];
        $res = $stmt->get_result();
        while($r = $res->fetch_assoc()) $chats[] = $r['message'] . " -> " . $r['response'];

        // 4. Metrics
        $stmt = $conn->prepare("SELECT screen_time, sleep_time FROM screen_sleep_data WHERE user_id = ? AND date = CURDATE()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $metrics = $stmt->get_result()->fetch_assoc();

        // 5. Available Admin Resources
        $stmt = $conn->prepare("SELECT id, title, topic, category, url FROM insights_content ORDER BY id DESC LIMIT 20");
        $stmt->execute();
        $admin_resources = [];
        $res = $stmt->get_result();
        while($r = $res->fetch_assoc()) $admin_resources[] = $r;

        echo json_encode([
            'status' => 'success',
            'data' => [
                'mood' => $mood,
                'journals' => $journals,
                'chats' => $chats,
                'screen_time' => $metrics['screen_time'] ?? 0,
                'sleep_time' => $metrics['sleep_time'] ?? 0,
                'available_resources' => $admin_resources
            ]
        ]);
        break;

    case 'get_sync_status':
        $stmt = $conn->prepare("SELECT last_sync_at FROM screen_sleep_data WHERE user_id = ? AND date = CURDATE()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        $active = false;
        if ($res && $res['last_sync_at']) {
            $last = strtotime($res['last_sync_at']);
            // Active if updated in the last 2 minutes
            if (time() - $last < 120) $active = true;
        }
        echo json_encode(['status' => 'success', 'active' => $active]);
        break;

    case 'get_chat_history':
        $stmt = $conn->prepare("SELECT message, response, timestamp FROM chatbot_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 50");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>
