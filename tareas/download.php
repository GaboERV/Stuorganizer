<?php
// download.php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /Proyecto/login/index.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/tareas/taskmanager.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $user_id = $_SESSION['id'];
    $taskManager = new TaskManager($user_id);
    $task = $taskManager->getTaskById($task_id);

    if ($task && $task['file_data'] && $task['file_name']) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($task['file_name']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($task['file_data']));
        echo $task['file_data'];
        exit();
    } else {
        echo "Archivo no encontrado o no tienes permiso para acceder a él.";
    }
} else {
    echo "ID de tarea no especificado.";
}
?>
