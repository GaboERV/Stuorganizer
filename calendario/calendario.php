<?php
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

// Establecer conexión a la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: No se pudo conectar. " . $conn->connect_error);
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

// Función para sanitizar la entrada
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Gestión de eventos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_event'])) {
        $title = sanitize_input($_POST['title']);
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $color = sanitize_input($_POST['color']);
        $user_id = $_SESSION['id']; // Usando sesión para el ID de usuario

        if ($startDate <= $endDate) {
            $sql = "INSERT INTO events (user_id, title, startDate, endDate, color) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("issss", $user_id, $title, $startDate, $endDate, $color);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif (isset($_POST['update_event'])) {
        $id = $_POST['event_id'];
        $title = sanitize_input($_POST['title']);
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $color = sanitize_input($_POST['color']);
        $user_id = $_SESSION['id']; // Usando sesión para el ID de usuario

        if ($startDate <= $endDate) {
            $sql = "UPDATE events SET title = ?, startDate = ?, endDate = ?, color = ? WHERE id = ? AND user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssii", $title, $startDate, $endDate, $color, $id, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif (isset($_POST['delete_event'])) {
        $id = $_POST['event_id'];
        $user_id = $_SESSION['id']; // Usando sesión para el ID de usuario

        $sql = "DELETE FROM events WHERE id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redireccionar para prevenir reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_event'])) {
        $event_id = $_POST['event_id'];
        $title = sanitize_input($_POST['title']);
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $color = sanitize_input($_POST['color']);
        $user_id = $_SESSION['id']; // Asegurarse de que solo el usuario propietario pueda modificar el evento

        if ($startDate <= $endDate) {
            $sql = "UPDATE events SET title = ?, startDate = ?, endDate = ?, color = ? WHERE id = ? AND user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssii", $title, $startDate, $endDate, $color, $event_id, $user_id);
                if ($stmt->execute()) {
                    echo "Evento actualizado con éxito.";
                } else {
                    echo "Error al actualizar el evento: " . $conn->error;
                }
                $stmt->close();
            }
        } else {
            echo "Error: La fecha de inicio debe ser anterior o igual a la fecha de fin.";
        }
    }
    $stmt->close();

}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Eventos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 100px 0 0 0;
            background-image: url(/Proyecto/Login/IMG/Paisaje_Hermoso.jpg);
            background-repeat: no-repeat;
            background-size: cover;
        }

        .container {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            margin-bottom: 50px;
        }

        #calendar {
            flex: 2;
            margin-right: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }

        .sidebar {
            flex: 1;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }

        .add-event-form,
        .update-event-form {
            margin-bottom: 20px;
        }

        .add-event-form input,
        .add-event-form button,
        .update-event-form input,
        .update-event-form button {
            margin-bottom: 10px;
            padding: 8px;
            width: calc(100% - 16px);
        }
        

        .add-event-form button,
        .update-event-form button {
            background-color: #6a1b9a;
            color: white;
            border: none;
            cursor: pointer;
        }

        .event-list {
            list-style-type: none;
            padding: 0;
        }

        .event-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .event-item button {
            margin-left: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .event-item .modify-btn {
            background-color: #2196F3;
            color: white;
        }

        .event-item .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .update-event-form {
            display: none;
        }

        .tooltip {
            position: absolute;
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }
        .color input{
            padding: 0;
        }
        .tooltip-event {
            margin-bottom: 5px;
        }

        .fc-day-grid-event .fc-content {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fc-day:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }

        .order-buttons {
            margin-bottom: 10px;
        }

        .order-buttons button {
            margin-right: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            background-color: #6a1b9a;
            color: white;
        }
    </style>
    <link rel="stylesheet" href="/../Proyecto/Styles/normalice.css?version=7.0">
    <link rel="icon" href="/Proyecto/images/icono.png" type="image/png">

</head>

<body>
    <?php
    define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/Proyecto');
    include ROOT_DIR . '/header.php';
    ?>
    <div class="container">
        <div id="calendar"></div>
        <div class="sidebar">
            <div class="add-event-form">
                <h3>Agregar Evento</h3>
                <form id="createEventForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="text" name="title" placeholder="Título del Evento" required>
                    <input type="date" name="startDate" required>
                    <input type="date" name="endDate" required>
                    <input class="color" type="color" name="color" value="#ff0000" required>
                    <button type="submit" name="create_event">Agregar Evento</button>
                </form>
            </div>
            <div class="update-event-form" style="display: none;">
                <h3>Modificar Evento</h3>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="event_id" id="update_event_id">
                    <input type="text" name="title" id="update_title" placeholder="Título del evento" required>
                    <input type="date" name="startDate" id="update_startDate" required>
                    <input type="date" name="endDate" id="update_endDate" required>
                    <input class="color" type="color" name="color" id="update_color" required>
                    <button type="submit" name="update_event">Actualizar Evento</button>
                </form>
            </div>
            <h3>Lista de Eventos</h3>
            <div class="order-buttons">
                <button onclick="window.location.href='?order=title'">Ordenar por Nombre</button>
                <button onclick="window.location.href='?order=startDate'">Ordenar por Fecha</button>
            </div>
            <ul class="event-list">
                <?php foreach ($events as $event): ?>
                    <li class="event-item">
                        <div>
                            <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                            <?php echo $event['start']; ?> - <?php echo $event['end']; ?><br>
                            <span style="color: <?php echo htmlspecialchars($event['color']); ?>;">■</span>
                        </div>
                        <div>
                            <button class="modify-btn" data-id="<?php echo htmlspecialchars($event['id']); ?>"
                                data-title="<?php echo htmlspecialchars($event['title']); ?>"
                                data-start="<?php echo htmlspecialchars($event['start']); ?>"
                                data-end="<?php echo htmlspecialchars($event['end']); ?>"
                                data-color="<?php echo htmlspecialchars($event['color']); ?>">
                                Modificar
                            </button>

                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                style="display:inline;">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" name="delete_event" class="delete-btn">Eliminar</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="tooltip" id="eventTooltip">
        <div class="tooltip-event" id="tooltipEventTitle"></div>
        <div class="tooltip-event" id="tooltipEventDate"></div>
    </div>
    <?php
    require_once ROOT_DIR . '/footer.php';
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script>
        $(document).ready(function () {
            var calendar = $('#calendar').fullCalendar({
                locale: 'es',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                editable: false,
                eventLimit: true,
                events: <?php echo json_encode($events); ?>,
                eventRender: function (event, element) {
                    element.attr('title', event.title);
                    element.css('background-color', event.color);

                    element.hover(function () {
                        $('#tooltipEventTitle').text(event.title);
                        $('#tooltipEventDate').text(moment(event.start).format('DD/MM/YYYY') + ' a ' + moment(event.end).format('DD/MM/YYYY'));
                        $('#eventTooltip').css({
                            top: event.jsEvent.pageY + 10,
                            left: event.jsEvent.pageX + 10
                        }).show();
                    }, function () {
                        $('#eventTooltip').hide();
                    });
                },
                dayRender: function (date, cell) {
                    var events = $('#calendar').fullCalendar('clientEvents', function (event) {
                        return (date.isSame(event.start, 'day') || date.isBetween(event.start, event.end, 'day', '[]'));
                    });
                    if (events.length > 0) {
                        cell.attr('title', events.map(e => e.title).join(', '));
                    }
                },
                dayClick: function (date, jsEvent, view) {
                    $('#createEventForm [name="startDate"]').val(date.format('YYYY-MM-DD'));
                    $('#createEventForm [name="endDate"]').val(date.format('YYYY-MM-DD'));
                }
            });

            $('#cancelUpdateBtn').click(function () {
                $('.update-event-form').hide();
                $('.add-event-form').show();
            });

            $('.delete-form').on('submit', function (e) {
                if (!confirm('¿Estás seguro de que quieres eliminar este evento?')) {
                    e.preventDefault();
                }
            });

            $('.modify-btn').on('click', function () {
                var eventId = $(this).data('event-id');
                var eventItem = $(this).closest('.event-item');
                var eventTitle = eventItem.find('span').eq(0).text().split(' (')[0];
                var eventDates = eventItem.find('span').eq(0).text().split(' (')[1].split(' a ');
                var eventColor = eventItem.css('background-color');

                $('#update_event_id').val(eventId);
                $('#update_event_title').val(eventTitle);
                $('#update_event_start_date').val(eventDates[0]);
                $('#update_event_end_date').val(eventDates[1].replace(')', ''));
                $('#update_event_color').val(eventColor);

                $('.add-event-form').hide();
                $('.update-event-form').show();
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const tooltip = document.getElementById('event-tooltip');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'interaction'],
                locale: 'es',
                events: <?php echo json_encode($events); ?>,
                eventMouseEnter: function (info) {
                    const event = info.event;
                    const start = moment(event.start).format('DD/MM/YYYY');
                    const end = event.end ? moment(event.end).format('DD/MM/YYYY') : start;
                    const html = `
                <div class="tooltip-event"><strong>${event.title}</strong></div>
                <div class="tooltip-event">${start} - ${end}</div>
                <div class="tooltip-event"><span style="color: ${event.backgroundColor};">■</span></div>
            `;
                    tooltip.innerHTML = html;
                    tooltip.style.display = 'block';
                    tooltip.style.left = `${info.jsEvent.pageX}px`;
                    tooltip.style.top = `${info.jsEvent.pageY}px`;
                },
                eventMouseLeave: function (info) {
                    tooltip.style.display = 'none';
                }
            });
            calendar.render();
        });

        function fillUpdateForm(id, title, startDate, endDate, color) {
            // Mostrar el formulario de actualización y ocultar el de creación
            document.querySelector('.update-event-form').style.display = 'block';
            document.querySelector('.add-event-form').style.display = 'none';

            // Llenar los campos del formulario con los datos del evento
            document.getElementById('update_event_id').value = id;
            document.getElementById('update_title').value = title;
            document.getElementById('update_startDate').value = startDate;
            document.getElementById('update_endDate').value = endDate;
            document.getElementById('update_color').value = color;

            // Hacer scroll hasta el formulario de actualización
            document.querySelector('.update-event-form').scrollIntoView({ behavior: 'smooth' });
        }

        // Añadir event listeners a todos los botones "Modificar"
        document.querySelectorAll('.modify-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const startDate = this.getAttribute('data-start');
                const endDate = this.getAttribute('data-end');
                const color = this.getAttribute('data-color');
                fillUpdateForm(id, title, startDate, endDate, color);
            });
        });
    </script>
</body>

</html>