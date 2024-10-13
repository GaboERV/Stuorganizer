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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if ($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $token = bin2hex(random_bytes(16));
                    $expire = date("Y-m-d H:i:s", strtotime("+1 hour"));

                    $stmt->close();

                    $sql = "INSERT INTO password_resets (email, token, expire) VALUES (?, ?, ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("sss", $email, $token, $expire);
                        
                        if ($stmt->execute()) {
                            $reset_link = "http://localhost/Proyecto/Login/Recuperar_contraseña/reset-password.php?token=" . $token;

                            // Aquí enviarías el correo electrónico con EmailJS desde el front-end.
                            echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var templateParams = {
                                        user_email: '$email',
                                        reset_link: '$reset_link'
                                    };

                                    emailjs.send('service_jpfnqbp', 'template_sma9bt8', templateParams)
                                        .then(function(response) {
                                            alert('Correo enviado con éxito!');
                                        }, function(error) {
                                            alert('Fallo en el envío del correo: ' + JSON.stringify(error));
                                        });
                                });
                            </script>";
                        } else {
                            echo "<script>alert('Error al insertar token en la base de datos');</script>";
                        }
                        $stmt->close();
                    }
                } else {
                    echo "<script>alert('Correo no encontrado');</script>";
                }
            } else {
                echo "<script>alert('Error al ejecutar la consulta');</script>";
            }
        }
    } else {
        echo "<script>alert('Correo inválido');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StuOrganizer - Recuperar Contraseña</title>
    <link rel="stylesheet" href="/Proyecto/Login/Recuperar_contraseña/style_recuperar.css?v=1.0">
    <link rel="icon" href="/images/icono.png" type="image/png">
    <script src="https://cdn.emailjs.com/dist/email.min.js"></script>
    <script>
        emailjs.init("RpUousMSaEdADf12G");
    </script>
</head>
<body>
    <div class="container">
        <h1>Recuperar Contraseña</h1>
        <form id="recover-form" method="post">
            <label for="email">Introduce tu correo electrónico:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Enviar</button>
        </form>
        <p>¿No tienes una cuenta? <a href="/Proyecto/Login/index.php">Regístrate</a></p>
    </div>
</body>
</html>
