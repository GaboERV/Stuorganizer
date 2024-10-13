<?php
session_start();
header('Content-Type: application/json');
ob_start();

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'StuOrganizer');

// Database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn === false) {
    echo json_encode(["error" => "ERROR: No se pudo conectar. " . mysqli_connect_error()]);
    exit;
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM events WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $events = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
        echo json_encode($events);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $title = $data['title'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $color = $data['color'];
        
        $sql = "INSERT INTO events (user_id, title, startDate, endDate, color) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $title, $startDate, $endDate, $color);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["id" => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(["error" => mysqli_error($conn)]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $title = $data['title'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];
        $color = $data['color'];
        
        $sql = "UPDATE events SET title = ?, startDate = ?, endDate = ?, color = ? WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssii", $title, $startDate, $endDate, $color, $id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["message" => "Event updated"]);
        } else {
            echo json_encode(["error" => mysqli_error($conn)]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        
        $sql = "DELETE FROM events WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["message" => "Event deleted"]);
        } else {
            echo json_encode(["error" => mysqli_error($conn)]);
        }
        break;
}

$output = ob_get_clean();
echo $output;

mysqli_close($conn);
?>
