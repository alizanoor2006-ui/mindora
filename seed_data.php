<?php
require 'config.php';

// Check if empty
$check = $conn->query("SELECT COUNT(*) as count FROM insights_content");
$count = $check->fetch_assoc()['count'];

if ($count == 0) {
    echo "Seeding data...\n";
    $seeds = [
        ['title' => 'Mindfulness for Students', 'category' => 'Video', 'topic' => 'Grounding', 'url' => 'https://www.youtube.com/watch?v=ssssssssss'],
        ['title' => 'Managing Exam Anxiety', 'category' => 'Article', 'topic' => 'Anxiety', 'url' => 'https://example.com/anxiety'],
        ['title' => 'The Science of Sleep', 'category' => 'Video', 'topic' => 'Healing', 'url' => 'https://www.youtube.com/watch?v=zzzzzzzzz'],
        ['title' => 'Productivity Tips for Better Mental Health', 'category' => 'Article', 'topic' => 'Productivity', 'url' => 'https://example.com/productivity']
    ];

    $stmt = $conn->prepare("INSERT INTO insights_content (title, category, topic, url) VALUES (?, ?, ?, ?)");
    foreach ($seeds as $s) {
        $stmt->bind_param("ssss", $s['title'], $s['category'], $s['topic'], $s['url']);
        $stmt->execute();
    }
    echo "Done seeding " . count($seeds) . " resources.";
} else {
    echo "Table already has $count items.";
}
?>
