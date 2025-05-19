<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>

    <header class="admin-header">
        <nav>
            <a href="tienda.php">Volver a la tienda</a>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Iniciar Sesión</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="mensajeexito">Registro exitoso.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="mensajeerror">Correo o contraseña incorrectos.</div>
        <?php endif; ?>


        <form action="../api/index.php?accion=login" method="POST">
            <label for="correo">Correo:</label>
            <input type="email" name="correo" required><br>

            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" required><br>

            <button type="submit">Entrar</button>
        </form>

        <p style="text-align:center; margin-top:20px;">
            ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
        </p>
    </main>

</body>

</html>