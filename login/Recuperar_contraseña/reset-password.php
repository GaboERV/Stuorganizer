<?php
session_start();

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'StuOrganizer');

// Establecer la conexión con la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

$token = $_GET['token'] ?? '';

// Redirigir si no hay token
if (empty($token)) {
    header("Location: /Proyecto/Login/Recuperar_contraseña/Recuperar_contra.php");
    exit();
}

// Verificar si el token existe en la base de datos
$sql = "SELECT email FROM password_resets WHERE token = ? AND expire > NOW()";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $token);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            // El token no existe o ha expirado, redirigir a la página de recuperación de contraseña
            header("Location: /Proyecto/Login/Recuperar_contraseña/Recuperar_contra.php");
            exit();
        }
    } else {
        die("Error al ejecutar la consulta: " . $conn->error);
    }
    $stmt->close();
} else {
    die("Error al preparar la consulta: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password && $confirm_password) {
        if ($new_password === $confirm_password) {
            $sql = "SELECT email FROM password_resets WHERE token = ? AND expire > NOW()";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $token);

                if ($stmt->execute()) {
                    $stmt->bind_result($email);
                    if ($stmt->fetch()) {
                        $stmt->close();

                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET password = ? WHERE email = ?";
                        if ($stmt = $conn->prepare( $sql)) {
                            $stmt->bind_param("ss", $hashed_password, $email);

                            if ($stmt->execute()) {
                                $stmt->close();
                                $sql = "DELETE FROM password_resets WHERE email = ?";
                                if ($stmt = $conn->prepare($sql)) {
                                    $stmt->bind_param("s", $email);
                                    $stmt->execute();
                                }
                                echo "<script>alert('Contraseña actualizada exitosamente.'); window.location.href = '/Proyecto/Login/index.php';</script>";
                            } else {
                                echo "<script>alert('Error al actualizar la contraseña');</script>";
                            }
                        }
                    } else {
                        echo "<script>alert('Token inválido o expirado'); window.location.href = '/Proyecto/Login/Recuperar_contraseña/index.php';</script>";
                    }
                } else {
                    echo "<script>alert('Error al ejecutar la consulta');</script>";
                }
            }
        } else {
            echo "<script>alert('Las contraseñas no coinciden');</script>";
        }
    } else {
        echo "<script>alert('Por favor, completa todos los campos');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer - Restablecer Contraseña</title>
    <link rel="stylesheet" href="/Proyecto/Login/Recuperar_contraseña/style_reset.css?v=2.0">
    <link rel="icon" href="/images/icono.png" type="image/png">
</head>
<body>
    <div class="container">
        <h1>Restablecer Contraseña</h1>
        <form method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <label for="new_password">Nueva Contraseña:</label>
            <input type="password" id="new_password" name="new_password" required>
            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Restablecer</button>
        </form>
    </div>
</body>
</html>