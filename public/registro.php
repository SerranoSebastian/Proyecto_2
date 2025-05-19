<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .mensaje-error {
            background-color: #fed7d7;
            color: #c53030;
            padding: 10px;
            border-radius: 6px;
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <header class="admin-header">
        <nav>
            <a href="tienda.php">Volver a la tienda</a>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Registro de Usuario</h1>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'correo'): ?>
            <div class="mensaje-error">El correo ya está registrado.</div>
        <?php endif; ?>

        <form action="../api/index.php?accion=registrar" method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" required><br>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" required><br>

            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" required><br>

            <button type="submit">Registrarse</button>
        </form>

        <p style="text-align:center; margin-top:20px;">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </p>
    </main>

</body>

</html>