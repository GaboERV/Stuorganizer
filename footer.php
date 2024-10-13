<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    footer {
        background-color: #1c1c1c;
        color: #ffffff;
        padding: 30px 0;
        font-family: Arial, sans-serif;
    }
    
    .footer-container {
        display: flex;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-section {
        flex: 1;
        margin-right: 20px;
    }

    .footer-section:last-child {
        margin-right: 0;
    }

    .footer-section h3 {
        color: #cd96f0;
        margin-right: 10px;
        font-size: 18px;
        margin-bottom: 15px;
    }

    .footer-section p {
        font-size: 14px;
        line-height: 1.5;
    }

    .footer-section ul {
        list-style-type: none;
        padding: 0;
    }


    .footer-section a {
        color: #ffffff;
        text-decoration: none;
    }

    .footer-section a:hover {
        text-decoration: underline;
    }

    .footer-copyright {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #333333;
        font-size: 12px;
    }

    footer p {
        margin: 0;
    }

    h3 {
        margin: 0;
    }

    li {
        margin: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
        }

        .footer-section {
            margin-right: 0;
            margin-bottom: 20px;
        }
    }
</style>

<body>
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>Acerca de nosotros</h3>
                <p>Stu Organizer es una aplicación web que ofrece una agenda personalizable, diseñada para ayudar a los
                    estudiantes a organizar su tiempo y mejorar su eficiencia.</p>
            </div>
            <div class="Grid">
                <div class="footer-section">
                    <h3>Redes sociales</h3>
                    <ul>
                        <li><a href="https://www.facebook.com/">Facebook</a></li>
                        <li><a href="https://www.instagram.com/">Instagram</a></li>
                        <li><a href="https://x.com/">Twitter</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-section">
                <h3>Política de privacidad</h3>
                <p>Stu Organizer se compromete a proteger la privacidad de nuestros usuarios. Consulta nuestra política
                    de privacidad para obtener más información.</p>
                <a href="/politica_pri.html">Leer más</a>
            </div>
            <div class="footer-section">
                <h3>Integrantes</h3>
                <p>Gabriel Eduardo Ruiz Velasco<br>Moises de Jesús Pech López<br>Diamore Yicel Vargas López</p>
            </div>
        </div>
        <div class="footer-copyright">
            <p>Copyright 2024 Stu Organizer. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>

</html>