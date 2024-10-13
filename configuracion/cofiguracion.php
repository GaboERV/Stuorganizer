// Archivo de configuración para la conexión a la base de datos
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /Proyecto/login/index.php");
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/login/config.php';
$update_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Modificar nombre de usuario
    if (isset($_POST['update_username'])) {
        $new_username = trim($_POST["username"]);

        if (!empty($new_username)) {
            $sql = "UPDATE users SET username = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("si", $new_username, $_SESSION["id"]);
                if ($stmt->execute()) {
                    $_SESSION["username"] = $new_username;
                    $update_msg = "Nombre de usuario actualizado con éxito.";
                } else {
                    $error_msg = "Error al actualizar el nombre de usuario.";
                }
                $stmt->close();
            }
        } else {
            $error_msg = "El nombre de usuario no puede estar vacío.";
        }
    }

    // Modificar contraseña
    if (isset($_POST['update_password'])) {
        $current_password = trim($_POST["current_password"]);
        $new_password = trim($_POST["new_password"]);
        $confirm_password = trim($_POST["confirm_password"]);

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = "Por favor complete todos los campos.";
        } elseif ($new_password != $confirm_password) {
            $error_msg = "Las nuevas contraseñas no coinciden.";
        } else {
            $sql = "SELECT password FROM users WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("i", $_SESSION["id"]);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($current_password, $hashed_password)) {
                                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $sql_update = "UPDATE users SET password = ? WHERE id = ?";
                                if ($stmt_update = $mysqli->prepare($sql_update)) {
                                    $stmt_update->bind_param("si", $new_hashed_password, $_SESSION["id"]);
                                    if ($stmt_update->execute()) {
                                        $update_msg = "Contraseña actualizada con éxito.";
                                    } else {
                                        $error_msg = "Error al actualizar la contraseña.";
                                    }
                                    $stmt_update->close();
                                }
                            } else {
                                $error_msg = "La contraseña actual es incorrecta.";
                            }
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Eliminar cuenta
    if (isset($_POST['delete_account'])) {
        $password = trim($_POST["password"]);
        if (empty($password)) {
            $error_msg = "Por favor ingrese su contraseña para eliminar la cuenta.";
        } else {
            $sql = "SELECT password FROM users WHERE id = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("i", $_SESSION["id"]);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed_password)) {
                                $sql_delete = "DELETE FROM users WHERE id = ?";
                                if ($stmt_delete = $mysqli->prepare($sql_delete)) {
                                    $stmt_delete->bind_param("i", $_SESSION["id"]);
                                    if ($stmt_delete->execute()) {
                                        session_destroy();
                                        header("location: /Proyecto/login/index.php");
                                        exit();
                                    } else {
                                        $error_msg = "Error al eliminar la cuenta.";
                                    }
                                    $stmt_delete->close();
                                }
                            } else {
                                $error_msg = "No se ha podido eliminar la cuenta por que la contraseña es incorrecta.";
                            }
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer</title>
    <link rel="stylesheet" href="/Proyecto/configuracion/configuracion.css?v=7.0">
    <link rel="icon" href="/Proyecto/images/icono.png" type="image/png">
</head>

<body>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto/header.php'; ?>

    <div class="container">
        <h1>Configuración</h1>

        <div class="section">
            <h2>Modificar Nombre de Usuario</h2>
            <?php
            if (!empty($update_msg)) {
                echo '<p class="success">' . $update_msg . '</p>';
            }
            if (!empty($error_msg)) {
                echo '<p class="error">' . $error_msg . '</p>';
            }
            ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" name="username" id="username" placeholder="Nuevo nombre de usuario" required>
                <button type="submit" name="update_username">Actualizar Nombre de Usuario</button>
            </form>
        </div>

        <div class="section">
            <h2>Modificar Contraseña</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="password" name="current_password" id="current-password" placeholder="Contraseña actual"
                    required>
                <input type="password" name="new_password" id="new-password" placeholder="Nueva contraseña" required>
                <input type="password" name="confirm_password" id="confirm-password"
                    placeholder="Confirmar nueva contraseña" required>
                <button type="submit" name="update_password">Actualizar Contraseña</button>
            </form>
        </div>

        <div class="section">
            <h2>Eliminar Cuenta</h2>
            <p>¡Advertencia! Esta acción no se puede deshacer.</p>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="password" name="password" id="password" placeholder="Contraseña actual" required>
                <button class="danger" type="submit" name="delete_account">Eliminar Cuenta</button>
            </form>
        </div>

        <div class="section">
            <form action="logout.php" method="post">
                <button type="submit">Cerrar Sesión</button>
            </form>
        </div>
    </div>
    <?php
    define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/Proyecto');
    include ROOT_DIR . '/footer.php';
    ?>
    <script>
        document.querySelectorAll('button[type="submit"]').forEach(button => {
            button.addEventListener('click', function (event) {
                if (!confirm("¿Estás seguro de que quieres realizar esta acción?")) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>