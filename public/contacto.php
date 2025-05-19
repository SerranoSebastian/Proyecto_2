<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - Tienda Xochiahua</title>
    <link rel="stylesheet" href="css/estilo_tienda.css">
</head>
<body>

<header>
    <div class="logo">Tienda<span>Xochiahua</span></div>
    <nav>
        <a href="tienda.php">Volver a la tienda</a>
    </nav>
</header>

<main>
    <form class="formulario-contacto" id="formContacto">
        <h2>Formulario de Contacto</h2>

        <label for="correo">Correo electrónico:</label>
        <input type="email" id="correo" name="correo" required>

        <label for="comentarios">Comentarios:</label>
        <textarea id="comentarios" name="comentarios" rows="5" required></textarea>

        <div>
            <button type="submit" class="enviar">Enviar</button>
            <button type="reset" class="cancelar">Cancelar</button>
        </div>

        <div id="mensaje" style="display:none;"></div>
    </form>
</main>

<script>
document.getElementById("formContacto").addEventListener("submit", function(e) {
    e.preventDefault();
    const correo = document.getElementById("correo").value.trim();
    const comentarios = document.getElementById("comentarios").value.trim();
    const mensaje = document.getElementById("mensaje");

    if (correo === "" || comentarios === "") {
        mensaje.style.display = "block";
        mensaje.className = "mensaje-error";
        mensaje.innerText = "Por favor completa todos los campos.";
    } else {
        mensaje.style.display = "block";
        mensaje.className = "mensaje-exito";
        mensaje.innerText = "Gracias por tu mensaje. ¡Te contactaremos pronto!";
        this.reset();
    }
});
</script>

<footer id="contacto" style="margin-top: 60px;">
    <h3>Contacto</h3>
    <p>tiendaxochiahua@contacto.com.mx</p>
    <p>&copy; 2025 - Not Copyright Intended</p>
</footer>

</body>
</html>
