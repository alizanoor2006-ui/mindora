<?php
require 'config.php';
$res = $conn->query("SELECT * FROM insights_content");
echo "Count: " . $res->num_rows . "\n";
while($row = $res->fetch_assoc()) {
    echo " - [" . $row['id'] . "] " . $row['title'] . " (" . $row['topic'] . ")\n";
}
?>
