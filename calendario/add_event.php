<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: /Proyecto/login/index.php");
        exit();
    }

    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'StuOrganizer');

    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("ERROR: Could not connect. " . $conn->connect_error);
    }

    $title = $_POST['title'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $color = $_POST['color'];
    $user_id = $_SESSION['id'];

    $sql = "INSERT INTO events (user_id, title, start, end, color) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("issss", $user_id, $title, $start, $end, $color);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Error al agregar evento."]);
        }
        $stmt->close();
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request."]);
}
?>
