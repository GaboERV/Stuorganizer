<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /Proyecto/login/index.php");
    exit();
}

// Asegúrate de que la ruta sea correcta
require_once $_SERVER['DOCUMENT_ROOT']. '/Proyecto/login/config.php';

// Función para obtener todas las tareas
function getTasks($mysqli) {
    $sql = "SELECT * FROM tasks ORDER BY due_date ASC";
    $result = $mysqli->query($sql);
    $tasks = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    return $tasks;
}


// Función para crear una nueva tarea
function createTask($conn, $title, $description, $due_date, $priority, $file_name, $file_data) {
    $sql = "INSERT INTO tasks (title, description, due_date, priority, file_name, file_data) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $title, $description, $due_date, $priority, $file_name, $file_data);
    return $stmt->execute();
}

// Función para actualizar una tarea existente
function updateTask($conn, $id, $title, $description, $due_date, $priority, $file_name, $file_data) {
    $sql = "UPDATE tasks SET title=?, description=?, due_date=?, priority=?, file_name=?, file_data=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $title, $description, $due_date, $priority, $file_name, $file_data, $id);
    return $stmt->execute();
}

// Función para eliminar una tarea
function deleteTask($conn, $id) {
    $sql = "DELETE FROM tasks WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Función para obtener una tarea específica
function getTask($conn, $id) {
    $sql = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Manejo de acciones POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $title = $_POST['title'];
            $description = $_POST['description'];
            $due_date = $_POST['due_date'];
            $priority = $_POST['priority'];
            
            $file_name = '';
            $file_data = '';
            if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $file_name = $_FILES['file']['name'];
                $file_data = file_get_contents($_FILES['file']['tmp_name']);
            }

            if(createTask($conn, $title, $description, $due_date, $priority, $file_name, $file_data)) {
                echo "Task created successfully";
            } else {
                echo "Failed to create task";
            }
            break;

        case 'update':
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $due_date = $_POST['due_date'];
            $priority = $_POST['priority'];
            
            $task = getTask($conn, $id);
            $file_name = $task['file_name'];
            $file_data = $task['file_data'];
            
            if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $file_name = $_FILES['file']['name'];
                $file_data = file_get_contents($_FILES['file']['tmp_name']);
            }

            if(updateTask($conn, $id, $title, $description, $due_date, $priority, $file_name, $file_data)) {
                echo "Task updated successfully";
            } else {
                echo "Failed to update task";
            }
            break;

        case 'delete':
            $id = $_POST['id'];
            if(deleteTask($conn, $id)) {
                echo "Task deleted successfully";
            } else {
                echo "Failed to delete task";
            }
            break;
    }

    exit;
}

// Manejo de acciones GET
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get_task':
            if(isset($_GET['id'])) {
                $task = getTask($conn, $_GET['id']);
                echo json_encode($task);
            }
            exit;
            break;

        case 'download':
            if(isset($_GET['id'])) {
                $task = getTask($conn, $_GET['id']);
                if($task && $task['file_name'] && $task['file_data']) {
                    header("Content-Type: application/octet-stream");
                    header("Content-Disposition: attachment; filename=\"" . $task['file_name'] . "\"");
                    echo $task['file_data'];
                    exit;
                }
            }
            break;
    }
}

// Si no es una acción POST o GET específica, mostramos la página principal
$tasks = getTasks($mysqli);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer - Tareas</title>
    <link rel="stylesheet" href="style.css?version=1.0.2">
    <link rel="icon" href="/Proyecto/images/icono.png" type="image/png">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
</head>
<body>
<?php
define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT']. '/Proyecto');
include ROOT_DIR. '/header.php';
?>
    
    <div class="container">
        <div class="form-container">
            <h1>StuOrganizer - Tareas</h1>
            <h2>Crear Tarea</h2>
            <form id="taskForm">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="title">Título:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="due_date">Fecha de vencimiento:</label>
                    <input type="date" id="due_date" name="due_date" required>
                </div>
                <div class="form-group">
                    <label for="priority">Prioridad:</label>
                    <select id="priority" name="priority" required>
                        <option value="Alta">Alta</option>
                        <option value="Media">Media</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="file">Archivo:</label>
                    <input type="file" id="file" name="file">
                </div>
                <button type="submit">Crear Tarea</button>
            </form>
        </div>
        <div class="task-list-container">
            <h2>Lista de Tareas</h2>  
            <ul id="taskList">
                <?php foreach ($tasks as $task): ?>
                    <li>
                        <div class="caja_tareas">
                            <span>
                                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                                <p>Nota:<br><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                                <p>Fecha: <?php echo htmlspecialchars($task['due_date']); ?></p>
                                <p>Prioridad: <?php echo htmlspecialchars($task['priority']); ?></p>
                            </span>
                            <?php if ($task['file_name']): ?>
                                <a href="?action=download&id=<?php echo $task['id']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                        <path d="M7 11l5 5l5 -5" />
                                        <path d="M12 4l0 12" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="task-actions">
                            <button class="update" data-task-id="<?php echo $task['id']; ?>">Actualizar</button>
                            <button onclick="deleteTask(<?php echo $task['id']; ?>)">Eliminar</button>
                            <button onclick="emailTask(<?php echo $task['id']; ?>)">Enviar por correo</button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Acerca de nosotros</h3>
                <p>Stu Organizer es una aplicación web que ofrece una agenda personalizable, diseñada para ayudar a los estudiantes a organizar su tiempo y mejorar su eficiencia.</p>
            </div>
            <div class="Grid">
                <div class="footer-section">
                    <h3>Redes sociales</h3>
                    <ul>
                        <li><a href="https://www.facebook.com/">Facebook</a></li>
                        <li><a href="https://www.instagram.com/">Instagram</a></li>
                        <li><a href="https://x.com/">Twitter</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-section">
                <h3>Política de privacidad</h3>
                <p>Stu Organizer se compromete a proteger la privacidad de nuestros usuarios. Consulta nuestra política de privacidad para obtener más información.</p>
                <a href="/politica_pri.html">Leer más</a>
            </div>
        </div>
        <div class="footer-copyright">
            <p>Copyright 2024 Stu Organizer. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>