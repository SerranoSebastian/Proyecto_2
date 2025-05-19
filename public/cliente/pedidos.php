<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}

require_once '../../api/config/bd.php';

$bd = new BaseDeDatos();
$conexion = $bd->obtenerConexion();

$id_usuario = $_SESSION['usuario_id'];

$sql = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_usuario]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <link rel="stylesheet" href="../css/estilo_tienda.css">
</head>

<body>

    <header>
        <div class="logo">Tienda<span>Xochiahua</span></div>
        <button class="menu-toggle" id="menuToggle">&#9776;</button>
        <nav id="navbar">
            <a href="../tienda.php">Volver a la tienda</a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                <a href="cliente/pedidos.php">Mis pedidos</a>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
            <?php endif; ?>
        </nav>
    </header>

    <main style="max-width: 900px; margin: 0 auto; padding: 40px;">
        <h1 style="text-align: center;">Mis Pedidos</h1>

        <?php if (count($pedidos) === 0): ?>
            <p style="text-align:center; color:#4a5568;">Aún no has realizado ningún pedido.</p>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="card" style="margin-bottom: 30px;">
                    <p><strong>Pedido #<?= $pedido['id'] ?></strong></p>
                    <p><strong>Fecha:</strong> <?= $pedido['fecha'] ?></p>
                    <p><strong>Total:</strong> $<?= number_format($pedido['total'], 2) ?></p>

                    <h4 style="margin-top: 10px;">Productos:</h4>
                    <ul style="padding-left: 20px;">
                        <?php
                        $stmt_detalles = $conexion->prepare("
                        SELECT d.*, p.nombre 
                        FROM detalles_pedido d
                        JOIN productos p ON d.id_producto = p.id
                        WHERE d.id_pedido = ?
                    ");
                        $stmt_detalles->execute([$pedido['id']]);
                        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($detalles as $detalle):
                            ?>
                            <li>
                                <?= htmlspecialchars($detalle['nombre']) ?> –
                                Cantidad: <?= $detalle['cantidad'] ?> –
                                Precio: $<?= number_format($detalle['precio_unitario'], 2) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script>
        const toggleBtn = document.getElementById('menuToggle');
        const navbar = document.getElementById('navbar');

        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('abierto');
        });
    </script>

</body>

</html>