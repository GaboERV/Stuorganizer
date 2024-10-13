<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado."]);
    exit();
}

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'StuOrganizer');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn === false) {
    die("ERROR: No se pudo conectar. " . mysqli_connect_error());
}

$data = json_decode(file_get_contents("php://input"), true);
$taskId = $data['id'];

$sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $param_id, $param_user_id);
    $param_id = $taskId;
    $param_user_id = $_SESSION['id'];

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar la tarea."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Error en la preparación de la declaración."]);
}

mysqli_close($conn);
?>
