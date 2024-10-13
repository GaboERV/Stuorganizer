<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: /Proyecto/login/index.php");
        exit();
    }

    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'stuorganizer');

    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("ERROR: Could not connect. " . $conn->connect_error);
    }

    $id = $_POST['id'];

    $sql = "DELETE FROM events WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Error al eliminar evento."]);
        }
        $stmt->close();
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request."]);
}
?>
