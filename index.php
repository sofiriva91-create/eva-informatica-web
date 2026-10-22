<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVA Informática - UPTAAPC</title>
    <link rel="stylesheet" href="style.css">
</head>

    <!-- BARRA DE NAVEGACIÓN SUPERIOR -->
    <header>
        <div class="logo-seccion">
            <i class="fa-solid fa-laptop-code logo-icono"><span>EVA</span></i>
            <span class="logo-texto">Informática UPTAAPC</span>
        </div>
        <nav>
            <a href="#servicios" title="Ver servicios educativos disponibles">Servicios</a>
            <a href="#universidad" title="Conoce más sobre nuestra institución">Universidad</a>
            <a href="#curso" title="Información del curso de informática">Curso</a>
            <a href="login.php" class="btn-login" title="Inicie sesión aquí para acceder al aula virtual">Iniciar Sesión</a>
            <a href="registros.php" class="btn-registros" title="Cree una nueva cuenta de estudiante aquí">Registrarse aquí</a>
        </nav>
    </header>

    <!-- PORTADA / HERO PRINCIPAL -->
    <section class="portada">
        <div class="portada-contenido">
            <h1>Entorno Virtual de Aprendizaje</h1>
            <h2>Universidad Politécnica Territorial del Alto Apure "Pedro Camejo"</h2>
            <div class="botones-portada">
                <a href="login.php" class="btn-login btn-grande" title="Inicie sesión aquí para entrar al sistema">Iniciar Sesión</a>
                <a href="registros.php" class="btn-registros btn-grande" title="Cree su cuenta de usuario aquí">Registrarse aquí</a>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE SERVICIOS EDUCATIVOS -->
    <section class="contenedor" id="servicios">
        <h2 class="titulo-seccion">Servicios Educativos</h2>
        <div class="tarjetas-grid">
            <div class="tarjeta">
                <i class="fa-solid fa-laptop-code tarjeta-icono"></i>
                <h3>Aulas Virtuales</h3>
                <p>Acceso a contenidos interactivos, clases y evaluaciones en línea para tu formación académica.</p>
                <a href="login.php" class="enlace-mas">Más información &rarr;</a>
            </div>
            <div class="tarjeta">
                <i class="fa-solid fa-book-bookmark tarjeta-icono"></i>
                <h3>Material Didáctico</h3>
                <p>Guías multimedia, bibliografía y documentación técnica de estudio recomendada.</p>
                <a href="login.php" class="enlace-mas">Más información &rarr;</a>
            </div>
            <div class="tarjeta">
                <i class="fa-solid fa-graduation-cap tarjeta-icono"></i>
                <h3>Formación Digital</h3>
                <p>Capacitación tecnológica adaptada a los requerimientos del programa universitario.</p>
                <a href="login.php" class="enlace-mas">Más información &rarr;</a>
            </div>
        </div>
    </section>

    <!-- SECCIÓN SOBRE LA UNIVERSIDAD -->
    <section class="contenedor" id="universidad">
        <h2 class="titulo-seccion">Nuestra Universidad</h2>
        <div class="caja-universidad">
            <p>
                La <strong>Universidad Politécnica Territorial del Alto Apure "Pedro Camejo" (UPTAAPC)</strong> promueve la educación universitaria de cualificación técnica, científica y humanista, impulsando la transformación digital y el desarrollo socioproductivo de la región a través de entornos virtuales de aprendizaje innovadores.
            </p>
        </div>
    </section>

    <!-- SECCIÓN DEL CURSO DE INFORMÁTICA -->
    <section class="contenedor" id="curso">
        <div class="banner-curso">
            <h3>Curso: Introducción a la Informática</h3>
            <p>Espacio académico fundamental diseñado para que los estudiantes dominen los conceptos básicos de la computación, arquitectura de sistemas, herramientas ofimáticas y lógica de programación indispensable para su carrera.</p>
            <a href="login.php" class="btn-curso-mas">Más información del curso</a>
        </div>
    </section>

    <!-- PIE DE PÁGINA -->
    <footer>
        <p>&copy; 2026 EVA Informática - Universidad Politécnica Territorial del Alto Apure "Pedro Camejo". Todos los derechos reservados.</p>
    </footer>

</body>
</html>