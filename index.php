<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StuOrganizer</title>
    <link rel="stylesheet" href="Proyecto/Styles/normalice.css" />
    <link rel="stylesheet" href="/Proyecto/Styles/style.css?v=2.0" />
    <link rel="icon" href="/Proyecto/images/icono.png?v=1.0" type="image/png">
  </head>
  <body>
    <header>
      <div class="container">
        <a href="/Proyecto/inicio/inicio.php"><img src="/Proyecto/images/logo.png" alt="logo" class="logo" /></a>
        <nav>
          <a href="">Inicio</a>
          <a href="#Sobre_nosotros">Sobre nosotros</a>
          <a href="#contactos">Contactos</a>
        </nav>
      
      </div>
    </header>
    <main>
      <div class="container">
        <div class="izquierda">
          I
        </div>
        <div class="Derecha">
          <h2>StuOrganizer</h2>
          <p>
            "StuOrganizer" es una aplicación web que ofrece <br />una agenda
            personalizable, diseñada para ayudar <br />a los estudiantes a
            organizar sus tiempo y mejorar su eficiencia.
          </p>
          <button class="Boton_saber_mas"><a href="/Proyecto/login/index.php">Iniciar sesion</a></button>
        </div>
      </div>
    </main>
    <section>
    <div id="Sobre_nosotros">
      <div class="section_gris">
      <h2 >Sobre nostros</h2>
      <div class="container">
        <div class="caja_de_texto">
          <ul>
            <li><strong>Vision</strong></li>
          </ul>
          <p>
            Ser la plataforma líder en organización estudiantil, empoderando a
            los estudiantes con herramientas intuitivas y efectivas que
            optimicen su tiempo y maximicen su productividad académica
          </p>
        </div>
        <img class="images" src="/Proyecto/images/image_estudiante.png" alt="" />
      </div>
      </div>
      <div class="section_blanca">
      <div class="container">
        <img class="images"
          src="/Proyecto/images/image_Libreta.png"
          alt=""
        />
        <div class="caja_de_texto_2">
          <ul>
            <li><strong>Mision</strong></li>
          </ul>
          <p>
            Desarrollar una aplicación web accesible y fácil de usar que permita
            a los estudiantes gestionar de manera eficiente su horario, tareas y
            calendario, facilitando la personalización y adaptación a sus
            necesidades individuales para mejorar su rendimiento académico y
            equilibrio personal
          </p>
        </div>
      </div>
    </div>
    </div>
    </section>
    <section id="contactos" class="section_gris">
      <h2>contacto</h2>
      <div class="contactos">
      <div class="contacto">
        <p><strong>Lider de proyecto</strong></p>
        <p>Gabriel Eduardo Ruiz Velasco <br>gewadod132@kinsef.com</p>
      </div>
      <div class="contacto">
        <p><strong>Programador</strong></p>
        <p>Moises de Jesús Pech López <br>meiffoupiddottu-4643@yopmail.com</p>
      </div>
      <div class="contacto">
        <p><strong>Programador</strong></p>
        <p>Diamore Yicel Vargas López <br>satrezaffale-9210@yopmail.com<br></p>
      </div>
      </div>
    </section>
    <?php
    define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/Proyecto');
    include ROOT_DIR . '/footer.php';
    ?>
  </body>
  </html>