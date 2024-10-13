<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /Proyecto/login/index.php");
    exit();
}

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'StuOrganizer');

// Establish database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

$user_id = $_SESSION['id'];
$order = isset($_GET['order']) ? $_GET['order'] : 'due_date';
$direction = isset($_GET['direction']) && $_GET['direction'] == 'desc' ? 'DESC' : 'ASC';

// Fetch tasks
$sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY $order $direction";
$tasks = [];

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    $stmt->close();
}

// Create or Update task
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['create_task']) || isset($_POST['update_task']))) {
    $task_id = isset($_POST['task_id']) ? $_POST['task_id'] : null;
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $due_date = $_POST['due_date'];
    $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $file = isset($_FILES['file']) ? $_FILES['file'] : null;

    if ($title && $description && $due_date && $priority) {
        if ($task_id) {
            // Update existing task
            $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ?";
            $params = [$title, $description, $due_date, $priority];
            
            // Check if a new file is uploaded
            if ($file && $file['size'] > 0) {
                $file_name = $file['name'];
                $file_data = file_get_contents($file['tmp_name']);
                $sql .= ", file_name = ?, file_data = ?";
                $params[] = $file_name;
                $params[] = $file_data;
            }
            
            $sql .= " WHERE id = ? AND user_id = ?";
            $params[] = $task_id;
            $params[] = $user_id;

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
                if ($stmt->execute()) {
                    $_SESSION["task_success"] = "Tarea actualizada exitosamente.";
                } else {
                    $_SESSION["task_err"] = "Algo salió mal al actualizar la tarea. Por favor, inténtalo de nuevo.";
                }
                $stmt->close();
            }
        } else {
            // Create new task
            $sql = "INSERT INTO tasks (user_id, title, description, due_date, priority, file_name, file_data) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $file_name = $file && $file['name'] ? $file['name'] : null;
                $file_data = $file && $file['tmp_name'] ? file_get_contents($file['tmp_name']) : null;
                $stmt->bind_param("issssss", $user_id, $title, $description, $due_date, $priority, $file_name, $file_data);
                if ($stmt->execute()) {
                    $_SESSION["task_success"] = "Tarea creada exitosamente.";
                } else {
                    $_SESSION["task_err"] = "Algo salió mal al crear la tarea. Por favor, inténtalo de nuevo.";
                }
                $stmt->close();
            }
        }
    }
    header("location: tareas.php");
    exit();
}

// Delete task
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];

    if ($task_id) {
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $task_id, $user_id);

            if ($stmt->execute()) {
                $_SESSION["task_success"] = "Tarea eliminada con éxito.";
            } else {
                $_SESSION["task_err"] = "Algo salió mal al eliminar la tarea. Por favor, inténtalo de nuevo.";
            }
            $stmt->close();
        }
    }
    header("location: tareas.php");
    exit();
}

$conn->close();

// Get task for editing
$edit_task = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($tasks as $task) {
        if ($task['id'] == $edit_id) {
            $edit_task = $task;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer - Tareas</title>
    <link rel="stylesheet" href="style.css?v=4.0">

</head>
<body>
<?php
define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT']. '/Proyecto');
include ROOT_DIR. '/header.php';
?>
    <div class="container">
        <div class="form-container">
            <h1>StuOrganizer - Tareas</h1>
            <?php
            if (isset($_SESSION["task_success"])) {
                echo "<p class='success'>" . $_SESSION["task_success"] . "</p>";
                unset($_SESSION["task_success"]);
            }
            if (isset($_SESSION["task_err"])) {
                echo "<p class='error'>" . $_SESSION["task_err"] . "</p>";
                unset($_SESSION["task_err"]);
            }
            ?>
            <h2><?php echo $edit_task ? 'Actualizar Tarea' : 'Crear Tarea'; ?></h2>
            <form id="taskForm" action="tareas.php" method="POST" enctype="multipart/form-data">
                <?php if ($edit_task): ?>
                    <input type="hidden" name="update_task" value="1">
                    <input type="hidden" name="task_id" value="<?php echo $edit_task['id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="create_task" value="1">
                <?php endif; ?>
                <div class="form-group">
                    <label for="title">Título:</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_task ? htmlspecialchars($edit_task['title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description" required><?php echo $edit_task ? htmlspecialchars($edit_task['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="due_date">Fecha de vencimiento:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo $edit_task ? $edit_task['due_date'] : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="priority">Prioridad:</label>
                    <select id="priority" name="priority" required>
                        <option value="Alta" <?php echo ($edit_task && $edit_task['priority'] == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                        <option value="Media" <?php echo ($edit_task && $edit_task['priority'] == 'Media') ? 'selected' : ''; ?>>Media</option>
                        <option value="Baja" <?php echo ($edit_task && $edit_task['priority'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="file">Archivo (opcional):</label>
                    <input type="file" id="file" name="file">
                    <?php if ($edit_task && $edit_task['file_name']): ?>
                        <p>Archivo actual: <?php echo htmlspecialchars($edit_task['file_name']); ?></p>
                        <p>Sube un nuevo archivo para reemplazar el existente, o deja este campo vacío para mantener el archivo actual.</p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $edit_task ? 'Actualizar Tarea' : 'Crear Tarea'; ?></button>
                <?php if ($edit_task): ?>
                    <a href="tareas.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="task-list-container">
            <h2>Lista de Tareas</h2>
            <div class="sorting">
                <a href="?order=title&direction=<?php echo $order == 'title' && $direction == 'ASC' ? 'desc' : 'asc'; ?>">Título</a> |
                <a href="?order=due_date&direction=<?php echo $order == 'due_date' && $direction == 'ASC' ? 'desc' : 'asc'; ?>">Fecha de Vencimiento</a> |
                <a href="?order=priority&direction=<?php echo $order == 'priority' && $direction == 'ASC' ? 'desc' : 'asc'; ?>">Prioridad</a>
            </div>
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
                                <a href="download.php?id=<?php echo $task['id']; ?>">
                                    <img src="download_icon.png" alt="Descargar archivo" width="20">
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="task-actions">
                            <a href="?edit=<?php echo $task['id']; ?>" class="btn btn-primary">Actualizar</a>
                            <form action="tareas.php" method="POST" style="display:inline;">
                                <input type="hidden" name="delete_task" value="1">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                            <button class="btn btn-success" onclick="promptEmail(<?php echo htmlspecialchars(json_encode($task)); ?>)">Enviar por correo</button>
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
            <div class="footer-section">
                <h3>Redes sociales</h3>
                <ul>
                    <li><a href="https://www.facebook.com/">Facebook</a></li>
                    <li><a href="https://www.instagram.com/">Instagram</a></li>
                    <li><a href="https://x.com/">Twitter</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Política de privacidad</h3>
                <p>Stu Organizer se compromete a proteger la privacidad de nuestros usuarios. Consulta nuestra política de privacidad para obtener más información.</p>
                <a href="/politica_pri.html">Leer más</a>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; 2024 Stu Organizer. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.emailjs.com/dist/email.min.js"></script>
    <script>
        (function() {
            emailjs.init('RpUousMSaEdADf12G');
        })();

        function promptEmail(task) {
            var email = prompt("Introduce la dirección de correo electrónico a la que deseas enviar la tarea:");

            if (email) {
                sendEmail(task, email);
            }
        }

        function sendEmail(task, email) {
            var templateParams = {
                email: email,
                title: task.title,
                description: task.description,
                due_date: task.due_date,
                priority: task.priority
            };

            emailjs.send('service_jpfnqbp', 'template_fi27z0h', templateParams)
                .then(function(response) {
                    alert('Correo enviado con éxito!');
                }, function(error) {
                    alert('Error al enviar el correo: ' + JSON.stringify(error));
                });
        }

        document.getElementById('taskForm').addEventListener('submit', function(event) {
            event.preventDefault();

            emailjs.sendForm('service_jpfnqbp', 'template_fi27z0h', this)
                .then(function() {
                    console.log('Correo enviado con éxito!');
                }, function(error) {
                    console.error('Error al enviar el correo:', error);
                });

            this.submit();
        });
    </script>
</body>
</html>
