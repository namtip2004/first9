<?php
// tag_input.php = API แบบ AJAX

$conn = new mysqli("127.0.0.1", "root", "", "first9");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT tag_id, tag_name FROM tag WHERE tag_name LIKE '%$q%' LIMIT 10";
    $result = $conn->query($sql);

    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($tags);
    exit;
}
?>
