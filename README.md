# INSTALEN WAMP O SINO SON GAYS IGUAL QUE CHINO. LA CARPETA ALA QUE SE LE VAN A SER PRUEBAS VA A SER /tareas/taskmanager.php 
#WAMP DEIDAD
<h2>Base de datos</h2>
<p>CREATE DATABASE StuOrganizer;

USE StuOrganizer;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    due_date DATE NOT NULL,
    priority VARCHAR(10) NOT NULL,
    file_name VARCHAR(255),
    file_data LONGBLOB,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    color VARCHAR(7) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);</p>