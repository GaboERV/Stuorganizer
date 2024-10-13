<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '58875887');
define('DB_NAME', 'stuorganizer');

// Establecer la conexión con la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Funcionalidad de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if ($email && $password) {
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["email"] = $email;
                            header("location: /Proyecto/tareas/tareas.php");
                            exit();
                        } else {
                            $_SESSION["login_err"] = "Contraseña inválida.";
                        }
                    }
                } else {
                    $_SESSION["login_err"] = "No se encontró una cuenta con ese correo electrónico.";
                }
            } else {
                $_SESSION["login_err"] = "Oops! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            }
            $stmt->close();
        }
    }
    header("location: /Proyecto/login/index.php");
    exit();
}

// Funcionalidad de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);    
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($username && $email && $password && $confirm_password) {
        if ($password === $confirm_password) {
            $sql = "SELECT id FROM users WHERE email = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                
                if ($stmt->execute()) {
                    $stmt->store_result();
                    
                    if ($stmt->num_rows == 0) {
                        $stmt->close();
                        
                        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                        
                        if ($stmt = $conn->prepare($sql)) {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt->bind_param("sss", $username, $email, $hashed_password);
                            
                            if ($stmt->execute()) {
                                $_SESSION["register_success"] = "Registro exitoso. Ahora puedes iniciar sesión.";
                            } else {
                                $_SESSION["register_err"] = "Algo salió mal. Por favor, inténtalo de nuevo.";
                            }
                        }
                    } else {
                        $_SESSION["register_err"] = "Ya existe una cuenta con este correo electrónico.";
                    }
                } else {
                    $_SESSION["register_err"] = "Oops! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
                }
                $stmt->close();
            }
        } else {
            $_SESSION["register_err"] = "Las contraseñas no coinciden.";
        }
    }
    header("location: index.php");
    exit();
}

// Funcionalidad de creación de tareas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_task'])) {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: index.php");
        exit();
    }

    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $due_date = $_POST['due_date'];
    $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $file = $_FILES['file'];

    if ($title && $description && $due_date && $priority) {
        $sql = "INSERT INTO tasks (user_id, title, description, due_date, priority, file_name, file_data) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $user_id = $_SESSION['id'];
            $file_name = $file['name'];
            $file_data = file_get_contents($file['tmp_name']);
            $stmt->bind_param("issssss", $user_id, $title, $description, $due_date, $priority, $file_name, $file_data);

            if ($stmt->execute()) {
                $_SESSION["task_success"] = "Tarea creada exitosamente.";
            } else {
                $_SESSION["task_err"] = "Algo salió mal. Por favor, inténtalo de nuevo.";
            }
            $stmt->close();
        }
    }
    header("location: /Proyecto/tareas/tareas.php");
    exit();
}

$conn->close();

// Incluir el archivo HTML
include 'index.html';
?>
