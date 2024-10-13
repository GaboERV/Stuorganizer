<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /Proyecto/login/index.php");
    exit();
}

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'stuorganizer');

// Establecer conexión con la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Crear tabla de eventos si no existe
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    color VARCHAR(7) NOT NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear la tabla: " . $conn->error);
}

// Crear tabla de tareas si no existe
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    due_date DATE NOT NULL,
    priority ENUM('Alta', 'Media', 'Baja') NOT NULL,
    file_name VARCHAR(255),
    file_data LONGBLOB
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear la tabla: " . $conn->error);
}

$direction = isset($_GET['direction']) ? $_GET['direction'] : 'ASC';
// Obtener los parámetros de orden y dirección de la URL
$order = isset($_GET['order']) ? $_GET['order'] : 'title';
$direction = isset($_GET['direction']) ? strtoupper($_GET['direction']) : 'ASC';

// Validar los parámetros
$valid_columns = ['title', 'due_date', 'priority_value'];
if (!in_array($order, $valid_columns)) {
    $order = 'title';
}

if ($direction !== 'ASC' && $direction !== 'DESC') {
    $direction = 'ASC';
}

// Consultar las tareas ordenadas
$query = "SELECT * FROM tasks ORDER BY $order $direction";
$result = mysqli_query($conn, $query);

$tasks = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }
}
// Obtener eventos
$events = [];
$user_id = $_SESSION['id']; // Usando sesión para el ID de usuario
$order_by = isset($_GET['order']) ? $_GET['order'] : 'startDate';
$sql = "SELECT * FROM events WHERE user_id = ? ORDER BY $order_by";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'start' => $row['startDate'],
                'end' => $row['endDate'],
                'color' => $row['color']
            ];
        }
    }
    $stmt->close();
}

// Obtener tareas
$tasks = [];
$sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC";
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
$conn->close();

$user_id = $_SESSION['id'];
$today = date('Y-m-d', strtotime('-0 day'));
$start_of_week = date('Y-m-d', strtotime('monday this week -1 day'));
$end_of_week = date('Y-m-d', strtotime('sunday this week -1 day'));

$today_events = array_filter($events, function ($event) use ($today) {
    return $event['start'] <= $today && $event['end'] >= $today;
});

$today_tasks = array_filter($tasks, function ($task) use ($today) {
    return $task['due_date'] == $today;
});

$week_events = array_filter($events, function ($event) use ($start_of_week, $end_of_week) {
    return $event['start'] <= $end_of_week && $event['end'] >= $start_of_week;
});

$week_tasks = array_filter($tasks, function ($task) use ($start_of_week, $end_of_week) {
    return $task['due_date'] >= $start_of_week && $task['due_date'] <= $end_of_week;
});

// Función para sanitizar la entrada
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer - Calendario y Tareas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            background-image: url(/Proyecto/login/IMG/Paisaje_Hermoso.jpg);
            background-size: cover;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
        }

        .flex {
            display: flex;
            gap: 10px;
        }

        .container {
            display: flex;
            justify-content: space-between;
            max-width: 1100px;
            margin: 0px;
            margin-bottom: 0;
        }

        #calendar {
            flex: 1.5;
            margin-right: 20px;
            min-width: 600px;
            background-color: white;
            height: fit-content;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .task-list-container {
            flex: 1;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            height: 500px;
            width: 20%;
            border-radius: 15px;
        }

        .task-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .task-item h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .task-item p {
            margin: 5px 0;
        }

        .event-info {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .event-info h2 {
            text-align: center;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .event-info ul {
            list-style: none;
            padding: 0;
        }

        .event-info li {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }

        .event-title {
            font-weight: bold;
            color: #2c3e50;
        }

        .event-time,
        .task-due {
            font-size: 0.9em;
            color: #7f8c8d;
        }

        .task-list {
            list-style-type: none;
            padding: 0;
            height: 400px;
            overflow-y: auto;
        }

        .task-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .task-title {
            font-weight: bold;
            font-size: 1.2em;
        }

        .task-date,
        .task-priority,
        .task-file {
            margin-top: 5px;
            font-size: 0.9em;
        }

        .eventos {
            width: 1000px;
        }

        .task-priority {
            background-color: #ff4d4d;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            display: inline-block;
        }

        .task-priority.alta {
            background-color: #ff4d4d;
        }

        .task-priority.media {
            background-color: #ffa500;
        }

        .task-priority.baja {
            background-color: #00bfff;
        }

        .task-file a {
            color: #007bff;
            text-decoration: none;
        }

        .task-file a:hover {
            text-decoration: underline;
        }

        p {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .more-text {
            display: none;
        }

        .read-more,
        .read-less {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            display: none;
        }

        main {
            margin-top: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        /* Media Queries */
        @media screen and (max-width: 768px) {


            .container {
                flex-direction: column;
            }

            #calendar {
                width: 50%;
                margin: 10px 15vh;
                margin-right: 0;
                margin-bottom: 20px;
            }

            .task-list-container {
                width: 40%;
                margin: 10px auto;
            }

            .eventos {
                width: 80%;
            }
        }

        @media screen and (max-width: 480px) {
            .flex {
                flex-direction: column;
            }
        }
    </style>
    <link rel="icon" href="/images/icono.png" type="image/png">
</head>

<body>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/header.php'; ?>
    <main>
        <div class="container">
            <div id="calendar"></div>
            <div class="task-list-container">
                <h2>Lista de Tareas</h2>
                <div class="sorting">
                </div>
                <ul class="task-list" id="taskList">

                    <?php foreach ($tasks as $task): ?>
                        <li class="task-item">
                            <div class="task-content">
                                <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <p>
                                    <?php
                                    $description = htmlspecialchars($task['description']);
                                    if (strlen($description) > 100) {
                                        echo substr($description, 0, 100) . '<span class="dots">...</span><span class="more-text">' . substr($description, 100) . '</span>';
                                        echo '<a class="read-more" style="display:inline;"> leer más</a>';
                                        echo '<a class="read-less"> leer menos</a>';
                                    } else {
                                        echo $description;
                                    }
                                    ?>
                                </p>
                                <p class="task-date">Fecha: <?php echo $task['due_date']; ?></p>
                                <span class="task-priority <?php echo strtolower($task['priority']); ?>">
                                    <?php echo htmlspecialchars($task['priority']); ?>
                                </span>
                                <?php if ($task['file_name']): ?>
                                    <p class="task-file">Archivo: <a
                                            href="download.php?id=<?php echo $task['id']; ?>"><?php echo htmlspecialchars($task['file_name']); ?></a>
                                    </p>
                                <?php endif; ?>
                            </div>

                        </li>
                    <?php endforeach; ?>
                </ul>

                <script>
                    document.querySelectorAll('.read-more').forEach(function (button) {
                        button.addEventListener('click', function () {
                            const taskContent = this.previousElementSibling;
                            taskContent.style.display = 'inline';
                            this.style.display = 'none';
                            this.nextElementSibling.style.display = 'inline';
                            taskContent.previousElementSibling.style.display = 'none'; // hide the dots
                        });
                    });

                    document.querySelectorAll('.read-less').forEach(function (button) {
                        button.addEventListener('click', function () {
                            const taskContent = this.previousElementSibling.previousElementSibling;
                            taskContent.style.display = 'none';
                            this.style.display = 'none';
                            this.previousElementSibling.style.display = 'inline';
                            taskContent.previousElementSibling.style.display = 'inline'; // show the dots
                        });
                    });

                    document.addEventListener('DOMContentLoaded', function () {
                        document.querySelectorAll('.task-item').forEach(function (task) {
                            const description = task.querySelector('p span.more-text');
                            if (description && description.textContent.length > 0) {
                                task.querySelector('.read-more').style.display = 'inline';
                            }
                        });
                    });
                </script>
            </div>
        </div>

        <div class="container eventos">
            <div class="event-info">
                <h2>Pendientes hoy</h2>
                <div class="flex">
                    <div>
                        <h3>Eventos de hoy</h3>
                        <ul>
                            <?php if (empty($today_events) && empty($today_tasks)): ?>
                                <li>No hay eventos ni tareas programados para hoy.</li>
                            <?php else: ?>
                                <?php foreach ($today_events as $event): ?>
                                    <li>
                                        <span class="event-title"><?php echo htmlspecialchars($event['title']); ?></span>
                                        <br>
                                        <span class="event-time"><?php echo $event['start']; ?> -
                                            <?php echo $event['end']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div>
                        <h3>Tareas de hoy</h3>
                        <ul>
                            <?php if (empty($today_tasks)): ?>
                                <li>No hay tareas para hoy.</li>
                            <?php else: ?>
                                <?php foreach ($today_tasks as $task): ?>
                                    <li>
                                        <span class="event-title"><?php echo htmlspecialchars($task['title']); ?></span>
                                        <br>
                                        <span class="task-due">Vence hoy</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="event-info">
                <h2>Pendientes esta semana</h2>
                <div class="flex">
                    <div>
                        <h3>Eventos de la semana</h3>
                        <ul>
                            <?php if (empty($week_events) && empty($week_tasks)): ?>
                                <li>No hay eventos ni tareas programados para esta semana.</li>
                            <?php else: ?>
                                <?php foreach ($week_events as $event): ?>
                                    <li>
                                        <span class="event-title"><?php echo htmlspecialchars($event['title']); ?></span>
                                        <br>
                                        <span class="event-time"><?php echo $event['start']; ?> -
                                            <?php echo $event['end']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div>
                        <h3>Tareas de la semana</h3>
                        <ul>
                            <?php foreach ($week_tasks as $task): ?>
                                <li>
                                    <span class="event-title"><?php echo htmlspecialchars($task['title']); ?></span>
                                    <br>
                                    <span class="task-due">Fecha de vencimiento: <?php echo $task['due_date']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/Proyecto');
    include ROOT_DIR . '/footer.php';
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                },
                locale: 'es',
                events: <?php echo json_encode($events); ?>,
                editable: true,
                droppable: true,
                eventClick: function (event) {
                    alert(event.title);
                }
            });
        });
    </script>
</body>

</html>