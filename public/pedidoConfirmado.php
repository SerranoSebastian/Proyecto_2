<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.html");
    exit();
}

if (!isset($_SESSION['pedido_id'])) {
    header("Location: tienda.php");
    exit();
}

require_once '../api/config/bd.php';
$bd = new BaseDeDatos();
$conexion = $bd->obtenerConexion();

$pedido_id = $_SESSION['pedido_id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del pedido
$stmt = $conexion->prepare("SELECT * FROM pedidos WHERE id = ? AND id_usuario = ?");
$stmt->execute([$pedido_id, $usuario_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener detalles del pedido
$stmt_detalles = $conexion->prepare("
    SELECT d.*, p.nombre 
    FROM detalles_pedido d
    JOIN productos p ON d.id_producto = p.id
    WHERE d.id_pedido = ?
");
$stmt_detalles->execute([$pedido_id]);
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pedido Confirmado</title>
    <link rel="stylesheet" href="css/estilo_tienda.css">
</head>

<body>

    <header>
        <div class="logo">Tienda<span>Xochiahua</span></div>

        <button class="menu-toggle" id="menuToggle">&#9776;</button>

        <nav id="navbar">
            <a href="contacto.php">Contactanos</a>
            <a href="tienda.php">Volver a la tienda</a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                <a href="cliente/pedidos.php">Mis pedidos</a>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="main-confirmado">
        <h1 class="h1-confirmado">Pedido Confirmado</h1>
        <p><strong>Muchas gracias por tu confianza y esperamos que pronto te tengamos de vuelta</strong></p>
        <div>
            <p><strong>Número de pedido:</strong> <?= $pedido['id'] ?></p>
            <p><strong>Fecha:</strong> <?= $pedido['fecha'] ?></p>
            <p><strong>Total pagado:</strong> $<?= number_format($pedido['total'], 2) ?></p>

            <h3 class="h3-confirmado">Productos comprados:</h3>
            <ul>
                <?php foreach ($detalles as $detalle): ?>
                    <li>
                        <?= htmlspecialchars($detalle['nombre']) ?> –
                        Cantidad: <?= $detalle['cantidad'] ?> –
                        Precio: $<?= number_format($detalle['precio_unitario'], 2) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>



        </main>

        <footer id="contacto">
            <h3>Contacto</h3>
            <p>tiendaxochiahua@contacto.com.mx</p>
            <p>&copy; 2025 - Not Copyright Intended</p>
        </footer>

    <script>
        const toggleBtn = document.getElementById('menuToggle');
        const navbar = document.getElementById('navbar');

        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('abierto');
        });
    </script>

</body>

</html>