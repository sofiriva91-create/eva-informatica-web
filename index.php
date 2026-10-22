<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVA Informática - UPTAAPC</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- BARRA DE NAVEGACIÓN SUPERIOR -->
    <header>
        <div class="logo-seccion">
            <img src="LOGO-UPTAAPC" alt="Logo UPTAAPC" onerror="this.src='https://via.placeholder.com/50?text=UPTAAPC'">
            <span>EVA Informática</span>
        </div>
        <nav>
            <a href="#servicios">Servicios</a>
            <a href="#universidad">Universidad</a>
            <a href="#curso">Curso</a>
            <a href="login.php" class="btn-login">Iniciar Sesión</a>
            <a href="registros.php" class="btn-registros">Registrarse aquí</a>
        </nav>
    </header>

    <!-- PORTADA / HERO PRINCIPAL -->
    <section class="portada">
        <h1>Entorno Virtual de Aprendizaje</h1>
        <h2>Universidad Politécnica Territorial del Alto Apure "Pedro Camejo"</h2>
        <div class="botones-portada">
            <a href="login.php" class="btn-login">Iniciar Sesión</a>
            <a href="registros.php" class="btn-registros">Registrarse aquí</a>
        </div>
    </section>

    <!-- SECCIÓN DE SERVICIOS EDUCATIVOS -->
    <section class="contenedor" id="servicios">
        <h2 class="titulo-seccion">Servicios Educativos</h2>
        <div class="tarjetas-grid">
            <div class="tarjeta">
                <i class="fa-solid fa-laptop-code"></i>
                <h3>Aulas Virtuales</h3>
                <p>Acceso a contenidos interactivos, clases y evaluaciones en línea para tu formación académica.</p>
            </div>
            <div class="tarjeta">
                <i class="fa-solid fa-book-bookmark"></i>
                <h3>Material Didáctico</h3>
                <p>Guías multimedia, bibliografía y documentación técnica de estudio recomendada.</p>
            </div>
            <div class="tarjeta">
                <i class="fa-solid fa-graduation-cap"></i>
                <h3>Formación Digital</h3>
                <p>Capacitación tecnológica adaptada a los requerimientos del programa universitario.</p>
            </div>
        </div>
    </section>

    <!-- SECCIÓN SOBRE LA UNIVERSIDAD -->
    <section class="contenedor" id="universidad">
        <h2 class="titulo-seccion">Nuestra Universidad</h2>
        <p style="text-align: center; max-width: 800px; margin: 0 auto; line-height: 1.8; color: #555;">
            La <strong>Universidad Politécnica Territorial del Alto Apure "Pedro Camejo" (UPTAAPC)</strong> promueve la educación universitaria de cualificación técnica, científica y humanista, impulsando la transformación digital y el desarrollo socioproductivo de la región a través de entornos virtuales de aprendizaje innovadores.
        </p>
    </section>

    <!-- SECCIÓN DEL CURSO DE INFORMÁTICA -->
    <section class="contenedor" id="curso">
        <div class="banner-curso">
            <h3>Curso: Introducción a la Informática</h3>
            <p>Espacio académico fundamental diseñado para que los estudiantes dominen los conceptos básicos de la computación, arquitectura de sistemas, herramientas ofimáticas y lógica de programación indispensable para su carrera.</p>
        </div>
    </section>

    <!-- PIE DE PÁGINA -->
    <footer>
        <p>&copy; 2026 EVA Informática - Universidad Politécnica Territorial del Alto Apure "Pedro Camejo". Todos los derechos reservados.</p>
    </footer>

</body>
</html>