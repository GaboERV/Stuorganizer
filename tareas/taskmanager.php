<?php
// includes/TaskManager.php

class TaskManager {
    private $conn;
    private $user_id;

    public function __construct($user_id) {
        // Configuración de la base de datos
        $db_server = 'localhost';
        $db_username = 'root';
        $db_password = '';
        $db_name = 'stuorganizer';

        // Conexión a la base de datos
        $this->conn = new mysqli($db_server, $db_username, $db_password, $db_name);
        if ($this->conn->connect_error) {
            die("ERROR: No se pudo conectar. " . $this->conn->connect_error);
        }

        $this->user_id = $user_id;
    }

    public function getTasks($order = 'due_date', $direction = 'ASC') {
        // Ajustar el orden si es prioridad
        if ($order === 'priority') {
            $order = 'priority_value';
        }

        // Validar los campos de ordenamiento para prevenir inyección SQL
        $allowed_orders = ['title', 'due_date', 'priority_value'];
        if (!in_array($order, $allowed_orders)) {
            $order = 'due_date';
        }

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY $order $direction";
        $tasks = [];

        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $this->user_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $tasks[] = $row;
                }
            }
            $stmt->close();
        }

        return $tasks;
    }

    public function createTask($title, $description, $due_date, $priority, $file = null) {
        $priority_value = $this->getPriorityValue($priority);

        $sql = "INSERT INTO tasks (user_id, title, description, due_date, priority, priority_value, file_name, file_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $this->conn->prepare($sql)) {
            $file_name = $file && $file['name'] ? $file['name'] : null;
            $file_data = $file && $file['tmp_name'] ? file_get_contents($file['tmp_name']) : null;
            $stmt->bind_param("issssiss", $this->user_id, $title, $description, $due_date, $priority, $priority_value, $file_name, $file_data);
            if ($stmt->execute()) {
                $_SESSION["task_success"] = "Tarea creada exitosamente.";
            } else {
                $_SESSION["task_err"] = "Algo salió mal al crear la tarea. Por favor, inténtalo de nuevo.";
            }
            $stmt->close();
        }
    }

    public function updateTask($task_id, $title, $description, $due_date, $priority, $file = null) {
        $priority_value = $this->getPriorityValue($priority);

        $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ?, priority_value = ?";
        $params = [$title, $description, $due_date, $priority, $priority_value];
        $types = "sssss";

        if ($file && $file['size'] > 0) {
            $sql .= ", file_name = ?, file_data = ?";
            $file_name = $file['name'];
            $file_data = file_get_contents($file['tmp_name']);
            $params[] = $file_name;
            $params[] = $file_data;
            $types .= "ss";
        }

        $sql .= " WHERE id = ? AND user_id = ?";
        $params[] = $task_id;
        $params[] = $this->user_id;
        $types .= "ii";

        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $_SESSION["task_success"] = "Tarea actualizada exitosamente.";
            } else {
                $_SESSION["task_err"] = "Algo salió mal al actualizar la tarea. Por favor, inténtalo de nuevo.";
            }
            $stmt->close();
        }
    }

    public function deleteTask($task_id) {
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("ii", $task_id, $this->user_id);
            if ($stmt->execute()) {
                $_SESSION["task_success"] = "Tarea eliminada con éxito.";
            } else {
                $_SESSION["task_err"] = "Algo salió mal al eliminar la tarea. Por favor, inténtalo de nuevo.";
            }
            $stmt->close();
        }
    }

    private function getPriorityValue($priority) {
        switch ($priority) {
            case 'Alta':
                return 1;
            case 'Media':
                return 2;
            case 'Baja':
            default:
                return 3;
        }
    }

    public function getTaskById($task_id) {
        $sql = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("ii", $task_id, $this->user_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }
            $stmt->close();
        }
        return null;
    }

    public function __destruct() {
        $this->conn->close();
    }
}
?>
