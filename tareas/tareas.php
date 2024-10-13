<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// tasks/tareas.php
header('Content-Type: text/html; charset=UTF-8');
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /Proyecto/login/index.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/tareas/taskmanager.php';

// Inicializar TaskManager
$user_id = $_SESSION['id'];
$taskManager = new TaskManager($user_id);

// Obtener parámetros de ordenamiento
$order = isset($_GET['order']) ? $_GET['order'] : 'due_date';
$direction = isset($_GET['direction']) && $_GET['direction'] == 'desc' ? 'desc' : 'asc';

// Manejar solicitudes POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_task'])) {
        // Crear tarea
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $due_date = $_POST['due_date'];
        $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        if ($title && $description && $due_date && $priority) {
            $taskManager->createTask($title, $description, $due_date, $priority, $file);
        }
        header("location: tareas.php");
        exit();
    }

    if (isset($_POST['update_task'])) {
        // Actualizar tarea
        $task_id = isset($_POST['task_id']) ? $_POST['task_id'] : null;
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $due_date = $_POST['due_date'];
        $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $file = isset($_FILES['file']) ? $_FILES['file'] : null;

        if ($task_id && $title && $description && $due_date && $priority) {
            $taskManager->updateTask($task_id, $title, $description, $due_date, $priority, $file);
        }
        header("location: tareas.php");
        exit();
    }

    if (isset($_POST['delete_task'])) {
        // Eliminar tarea
        $task_id = $_POST['task_id'];
        if ($task_id) {
            $taskManager->deleteTask($task_id);
        }
        header("location: tareas.php");
        exit();
    }
}

// Obtener todas las tareas
$tasks = $taskManager->getTasks($order, $direction);

// Obtener tarea para editar si se solicita
$edit_task = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_task = $taskManager->getTaskById($edit_id);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer - Tareas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            background-image: url(/Proyecto/login/IMG/Paisaje_Hermoso.jpg);
            background-size: cover;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            min-height: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            justify-content: space-evenly;
            margin-top: 100px;
        }

        h1,
        h2 {
            color: #2c3e50;
            text-align: center;
        }

        .form-container,
        .task-list-container {
            margin-bottom: 30px;
            width: 48%;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .task-list {
            list-style-type: none;
            padding: 0;
            height: 600px;
            overflow-y: auto;
        }

        .task-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 15px;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .task-title {
            font-size: 18px;
            font-weight: bold;
        }

        .task-priority {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .priority-Alta {
            background-color: #e74c3c;
            color: white;
        }

        .priority-Media {
            background-color: #f39c12;
            color: white;
        }

        .priority-Baja {
            background-color: #3498db;
            color: white;
        }

        .task-details {
            margin-bottom: 10px;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .sorting {
            margin-bottom: 15px;
            text-align: center;
        }

        .sorting a {
            text-decoration: none;
            color: #3498db;
            margin-right: 10px;
        }

        .sorting a:hover {
            text-decoration: underline;
        }

        .description {
            max-height: 50px;
            overflow: hidden;
            position: relative;
        }

        .description.expand {
            max-height: none;
        }

        .read-more {
            color: #3498db;
            cursor: pointer;
            position: absolute;
            bottom: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.8);
            padding: 0 5px;
        }

        p {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        textarea {
            resize: none;
            height: 170px;
        }

        /* Media Queries */
        @media screen and (max-width: 1024px) {
            .container {
                max-width: 90%;
            }
        }

        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }

            .form-container,
            .task-list-container {
                width: 100%;
            }

            .task-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                font-size: 14px;
            }

            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }

            .task-title {
                font-size: 16px;
            }

            .task-priority {
                font-size: 10px;
            }

            .sorting a {
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
    <link rel="icon" href="/Proyecto/images/icono.png" type="image/png">
</head>

<body>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/includes/header.php'; ?>
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
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($edit_task['id']); ?>">
                <?php else: ?>
                    <input type="hidden" name="create_task" value="1">
                <?php endif; ?>
                <div class="form-group">
                    <label for="title">Título:</label>
                    <input type="text" id="title" name="title"
                        value="<?php echo $edit_task ? htmlspecialchars($edit_task['title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <textarea id="description" name="description"
                        required><?php echo $edit_task ? htmlspecialchars($edit_task['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="due_date">Fecha de vencimiento:</label>
                    <input type="date" id="due_date" name="due_date"
                        value="<?php echo $edit_task ? htmlspecialchars($edit_task['due_date']) : ''; ?>" required>
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
                        <p>Sube un nuevo archivo para reemplazar el existente, o deja este campo vacío para mantener el
                            archivo actual.</p>
                    <?php endif; ?>
                </div>
                <button type="submit"
                    class="btn btn-primary"><?php echo $edit_task ? 'Actualizar Tarea' : 'Crear Tarea'; ?></button>
                <?php if ($edit_task): ?>
                    <a href="tareas.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="task-list-container">
            <h2>Lista de Tareas</h2>
            <div class="sorting">
                <a
                    href="?order=title&direction=<?php echo $order == 'title' && $direction == 'ASC' ? 'desc' : 'asc'; ?>">Título</a>
                |
                <a
                    href="?order=due_date&direction=<?php echo $order == 'due_date' && $direction == 'ASC' ? 'desc' : 'asc'; ?>">Fecha
                    de Vencimiento</a> |
                <a
                    href="?order=priority&direction=<?php echo $order == 'priority_value' && $direction == 'ASC' ? 'desc' : 'asc'; ?>">Prioridad</a>
            </div>
            <ul class="task-list">
                <?php foreach ($tasks as $task): ?>
                    <li class="task-item">
                        <div class="task-header">
                            <span class="task-title"><?php echo htmlspecialchars($task['title']); ?></span>
                            <span
                                class="task-priority priority-<?php echo htmlspecialchars($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></span>
                        </div>
                        <div class="task-details">
                            <p class="description">
                                <?php
                                $description = nl2br(htmlspecialchars($task['description']));
                                echo (strlen($task['description']) > 100) ? substr($description, 0, 100) . '...' : $description;
                                ?>
                                <?php if (strlen($task['description']) > 100): ?>
                                    <span class="read-more">... leer más</span>
                                <?php endif; ?>
                            </p>
                            <p>Fecha: <?php echo htmlspecialchars($task['due_date']); ?></p>
                            <?php if ($task['file_name']): ?>
                                <p>Archivo: <a href="download.php?id=<?php echo $task['id']; ?>"
                                        target="_blank"><?php echo htmlspecialchars($task['file_name']); ?></a></p>
                            <?php endif; ?>
                        </div>
                        <div class="task-actions">
                            <a href="?edit=<?php echo htmlspecialchars($task['id']); ?>" class="btn btn-primary">Actualizar</a>
                            <form action="tareas.php" method="POST" style="display:inline;">
                                <input type="hidden" name="delete_task" value="1">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta tarea?');">Eliminar</button>
                            </form>
                            <button class="btn btn-success send-ema
                            il" data-task-id="<?php echo htmlspecialchars($task['id']); ?>"
                                data-title="<?php echo htmlspecialchars($task['title'] ?? ''); ?>"
                                data-description="<?php echo htmlspecialchars($task['description'] ?? ''); ?>"
                                data-due-date="<?php echo htmlspecialchars($task['due_date'] ?? ''); ?>"
                                data-priority="<?php echo htmlspecialchars($task['priority'] ?? ''); ?>"
                                data-file-name="<?php echo htmlspecialchars($task['file_name'] ?? ''); ?>"
                                data-file-data="<?php echo isset($task['file_data_base64']) ? htmlspecialchars($task['file_data_base64']) : ''; ?>">
                                Enviar por correo
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/footer.php';
    ?>
    <script src="https://cdn.emailjs.com/dist/email.min.js"></script>
    <script>
        (function () {
            emailjs.init("RpUousMSaEdADf12G");

            document.addEventListener('click', function (e) {
                if (e.target && e.target.classList.contains('send-email')) {
                    var taskData = e.target.dataset;
                    var to_email = prompt("Por favor, ingrese la dirección de correo electrónico:");
                    if (to_email) {
                        var templateParams = {
                            to_email: to_email,
                            task_title: taskData.title,
                            task_description: taskData.description,
                            task_due_date: taskData.dueDate,
                            task_priority: taskData.priority,
                            task_file_name: taskData.fileName,
                            task_file_data: taskData.fileData
                        };
                        Object.keys(templateParams).forEach(key => {
                            if (templateParams[key] === undefined || templateParams[key] === null) {
                                templateParams[key] = '';
                            }
                        });
                        emailjs.send('service_jpfnqbp', 'template_fi27z0h', templateParams)
                            .then(function (response) {
                                alert('Correo enviado con éxito!');
                            }, function (error) {
                                console.error('Error al enviar el correo:', error);
                                alert('Error al enviar el correo: ' + JSON.stringify(error));
                            });
                    }
                }

                // Manejo de "leer más"
                if (e.target && e.target.classList.contains('read-more')) {
                    var description = e.target.parentElement;
                    description.classList.toggle('expand');
                    e.target.textContent = description.classList.contains('expand') ? 'leer menos' : '... leer más';
                }
            });
        })();
    </script>
</body>

</html>
