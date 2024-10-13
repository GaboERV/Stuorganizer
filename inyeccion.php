<?php
// Script de prueba simple para la inyección de SQL
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'stuorganizer');

// Conectar a la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: No se pudo conectar. " . $conn->connect_error);
}

// Probar inyección de SQL
$email = "test@example.com' OR '1'='1";
$password = "anypassword";

// Consulta usando prepared statements
$sql = "SELECT id, username, email, password FROM users WHERE email = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "¡Posible vulnerabilidad detectada!";
    } else {
        echo "No se detectaron vulnerabilidades.";
    }
    $stmt->close();
} else {
    echo "Error en la preparación de la consulta.";
}

$conn->close();
?>
