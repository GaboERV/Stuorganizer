<a?php
// header.php
function getActivePage()
{
  $currentPage = $_SERVER['PHP_SELF'];
  return basename($currentPage, '.php');
}
function isActive($pageName)
{
  return getActivePage() === $pageName ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <style>
    img {
      width: 100px;
    }

    header {
      display: flex;
      justify-content: space-between;
      flex-direction: row;
      align-items: center;
      justify-content: space-around;
      padding: 0.9rem;
      background-color: #6a1b9a;
      position: fixed;
      /* Agregamos esta propiedad */
      top: 0;
      /* Y esta propiedad para que se quede en la parte superior */
      width: 100%;
      /* Aseguramos que ocupe todo el ancho */
      z-index: 1;
    }
    header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000; /* Un valor alto para asegurar que esté por encima de otros elementos */
    background-color: #6a1b9a; /* O el color que prefieras */
}

    header img {
      width: 100px;
    }

    .flex {
      display: flex;
      justify-content: space-between;
    }

    .sesion {
      display: flex;
      justify-content: space-between;
      width: 7%;
    }

    .sesion .user-info {
      margin-top: 10px;
      color: #fff;
    }

    .nav-list {
      text-decoration: none;
      list-style: none;
      display: flex;
      justify-content: space-between;
      width: 120%;
    }

    .nav-list a {
      display: flex;
      text-decoration: none;
      color: #fff;
    }
  </style>
  <link rel="icon" href="/Proyecto/images/icono.png" type="image/png">
</head>

<body>
  <header>
    <a href="/Proyecto"><img class="logo" src="/Proyecto/images/logo.png" alt="logo"></a>
    <nav class="nav" id="nav">
      <ul class="nav-list">
        <li><a class="Blanco" href="/../Proyecto/inicio/inicio.php">inicio</a></li>
        <li><a class="Blanco" href="/../Proyecto/calendario/calendario.php">Calendario</a></li>
        <li><a class="Blanco" href="/../Proyecto/tareas/tareas.php">Tareas</a></li>
      </ul>
    </nav>
    <div class="sesion">
      <div class="user-info">
        <?php echo htmlspecialchars($_SESSION["username"]); ?>
      </div>
      <a href="/Proyecto/configuracion/cofiguracion.php">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-circle" width="40" height="40"
          viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round"
          stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
          <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
          <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
        </svg>
      </a>
    </div>
  </header>
</body>

</html>