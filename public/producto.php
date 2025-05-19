<?php
session_start();
require_once '../api/config/bd.php';

$id = $_GET['id'];

$bd = new BaseDeDatos();
$conexion = $bd->obtenerConexion();

$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtRelacionados = $conexion->prepare("SELECT * FROM productos WHERE id != ? LIMIT 10");
$stmtRelacionados->execute([$id]);
$relacionados = $stmtRelacionados->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_GET['id'])) {
    echo "ID de producto no especificado.";
    exit;
}

// Eliminar producto del carrito
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id]);
    header("Location: tienda.php");
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar producto al carrito
if (isset($_GET['agregar'])) {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente') {
        $id = $_GET['agregar'];
        $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;
    } else {
        header("Location: login.html");
        exit();
    }
    header("Location: tienda.php");
    exit();
}

// Actualizar cantidad del carrito
if (isset($_POST['actualizar']) && isset($_POST['cantidad'])) {
    $id = $_POST['actualizar'];
    $cantidad = max(1, intval($_POST['cantidad']));
    $_SESSION['carrito'][$id] = $cantidad;
    header("Location: tienda.php");
    exit();
}



//Alertas aleatorias
//Recabando todos los productos
$stmtTodos = $conexion->prepare("SELECT id, nombre FROM productos");
$stmtTodos->execute();
$productosTodos = $stmtTodos->fetchAll(PDO::FETCH_ASSOC);

$nombres = ['The Weekend', 'Cristiano Ronaldo', 'Carlos Santana', 'Billie Eilish', 'Leonel Messi'];
$nombreAleatorio = $nombres[array_rand($nombres)];
$productoAleatorio = $productosTodos[array_rand($productosTodos)];



if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($producto['nombre']) ?> - Tienda Xochiahua</title>
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

    <main style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
        <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center;">
            <div class="carrusel-imagenes">
                <button class="prev" onclick="moverCarrusel(-1)">&#10094;</button>
                <div class="imagenes">
                    <?php
                    $ruta = "img/";
                    for ($i = 1; $i <= 5; $i++) {
                        $archivo = $ruta . $producto['id'] . "_" . $i . ".jpg";
                        if (file_exists($archivo)) {
                            echo "<img src='$archivo' alt='Imagen $i'>";
                        }
                    }
                    ?>
                </div>
                <button class="next" onclick="moverCarrusel(1)">&#10095;</button>
            </div>

            <div>
                <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
                <p><?= htmlspecialchars($producto['descripcion']) ?></p>
                <p style="font-size: 1.3em; font-weight: bold; color: #2b6cb0;">
                    $<?= number_format($producto['precio'], 2) ?>
                </p>

                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                    <button class="btn agregar-carrito" data-id="<?= $producto['id'] ?>">Agregar al carrito</button>
                <?php else: ?>
                    <a href="login.php" class="btn" style="background-color:#e53e3e;">Inicia sesión para comprar</a>
                <?php endif; ?>
            </div>
        </div>

        <section class="carrusel-productos">
            <h2 style="text-align:center; margin-bottom: 20px; color:#2b6cb0;">También te puede interesar</h2>
            <div class="carrusel-contenedor">
                <?php foreach ($relacionados as $p): ?>
                    <div class="producto-card">
                        <a href="producto.php?id=<?= $p['id'] ?>">
                            <img src="img/<?= $p['id'] ?>_1.jpg" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        </a>
                        <div class="contenido">
                            <a href="producto.php?id=<?= $p['id'] ?>">
                                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                            </a>
                            <p><?= htmlspecialchars($p['descripcion']) ?></p>
                            <div class="precio">$<?= number_format($p['precio'], 2) ?></div>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                                <button class="btn agregar-carrito" data-id="<?= $p['id'] ?>">Agregar al carrito</button>
                            <?php else: ?>
                                <a href="login.php" class="btn" style="background-color:#e53e3e;">Inicia sesión para comprar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        const toggleBtn = document.getElementById('menuToggle');
        const navbar = document.getElementById('navbar');
        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('abierto');
        });
        let indice = 0;
        const contenedor = document.querySelector('.imagenes');
        const imagenes = document.querySelectorAll('.imagenes img');
        function moverCarrusel(direccion) {
            indice += direccion;
            if (indice < 0) indice = imagenes.length - 1;
            if (indice >= imagenes.length) indice = 0;
            contenedor.style.transform = `translateX(-${indice * 100}%)`;
        }
    </script>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
        <?php
        $totalCantidad = 0;
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $item) {
                if (is_array($item) && isset($item['cantidad'])) {
                    $totalCantidad += $item['cantidad'];
                }
            }
        }
        ?>

        <button id="abrirCarrito" class="boton-carrito">
            Ver Carrito<?= ($totalCantidad > 0) ? " ({$totalCantidad})" : "" ?>
        </button>

    <?php endif; ?>



    <div id="panelCarrito" class="carrito-panel">
        <div class="carrito-header">
            <h3>Tu Carrito</h3>
            <button id="cerrarCarrito">✖</button>
        </div>
        <div class="carrito-contenido">
            <?php if (empty($_SESSION['carrito'])): ?>
                <p>Tu carrito está vacío.</p>
            <?php else: ?>
                <?php $total = 0; ?>
                <ul class='carrito-lista'>
                    <?php foreach ($_SESSION['carrito'] as $id => $cantidad):
                        $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
                        $stmt->execute([$id]);
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                        $subtotal = $producto['precio'] * $cantidad;
                        $total += $subtotal;
                        ?>
                        <li class='item-carrito'>
                            <img src='img/<?= $producto['id'] ?>_1.jpg' alt='producto'>
                            <div class='info'>
                                <h4><?= $producto['nombre'] ?></h4>
                                <form action="tienda.php" method="POST" class="form-cantidad">
                                    <input type="hidden" name="actualizar" value="<?= $producto['id'] ?>">
                                    <input type="number" name="cantidad" value="<?= $cantidad ?>" min="1" class="input-cantidad"
                                        data-id="<?= $producto['id'] ?>" data-subtotal-id="subtotal-<?= $producto['id'] ?>">
                                </form>
                                <p id="subtotal-<?= $producto['id'] ?>">Subtotal: $<?= number_format($subtotal, 2) ?></p>
                                <button class="btn-eliminar" data-id="<?= $producto['id'] ?>">Eliminar</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class='carrito-total'>
                    <p><strong>Total:</strong> $<span id="total-carrito"><?= number_format($total, 2) ?></span></p>
                    <form action='../api/index.php?accion=confirmar_pedido' method='POST'>
                        <button type='submit' class='btn'>Confirmar Pedido</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <script>
        const abrirBtn = document.getElementById("abrirCarrito");
        const cerrarBtn = document.getElementById("cerrarCarrito");
        const panel = document.getElementById("panelCarrito");

        abrirBtn?.addEventListener("click", () => {
            panel.classList.add("abierto");
        });

        cerrarBtn?.addEventListener("click", () => {
            panel.classList.remove("abierto");
        });
    </script>

    <script>
        const toggleBtn = document.getElementById('menuToggle');
        const navbar = document.getElementById('navbar');

        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('abierto');
        });
    </script>

    <script>
        document.querySelectorAll('.input-cantidad').forEach(input => {
            input.addEventListener('change', () => {
                const id = input.dataset.id;
                const cantidad = input.value;
                const subtotalSpan = document.getElementById(input.dataset.subtotalId);

                fetch('../api/controladores/CarritoControlador.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&cantidad=${cantidad}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            subtotalSpan.innerText = `Subtotal: $${data.subtotal}`;
                            document.getElementById('total-carrito').innerText = data.total;
                            document.getElementById('abrirCarrito').innerText = `Ver Carrito (${data.cantidad_total})`;
                        }
                    });
            });
        });
    </script>

    <script>
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', e => {
                e.preventDefault();
                const id = button.dataset.id;
                const item = button.closest('.item-carrito');

                fetch('../api/controladores/CarritoControlador.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `eliminar_id=${id}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            item.remove();
                            document.getElementById('total-carrito').innerText = data.total;
                            document.getElementById('abrirCarrito').innerText = `Ver Carrito (${data.cantidad_total})`;

                            // Si ya no quedan productos
                            if (document.querySelectorAll('.item-carrito').length === 0) {
                                document.querySelector('.carrito-contenido').innerHTML = "<p>Tu carrito está vacío.</p>";
                            }
                        }
                    });
            });
        });
    </script>

    <script>
        document.querySelectorAll('.agregar-carrito').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const id = btn.dataset.id;

                fetch('../api/controladores/CarritoControlador.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `agregar_id=${id}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.error) {
                            document.getElementById('abrirCarrito').innerText = `Ver Carrito (${data.cantidad_total})`;
                            if (document.getElementById("panelCarrito").classList.contains("abierto")) {
                                location.reload();
                            }
                        }
                    });
            });
        });
    </script>

    <div id="notificacion-compra" class="toast">
        <img src="img/<?= $productoAleatorio['id'] ?>_1.jpg"
            alt="<?= htmlspecialchars($productoAleatorio['nombre']) ?>" />
        <div>
            <strong><?= $nombreAleatorio ?></strong><br>
            acaba de comprar <em><?= htmlspecialchars($productoAleatorio['nombre']) ?></em>
        </div>
    </div>


    <script>
        setTimeout(() => {
            const toast = document.getElementById("notificacion-compra");
            toast.style.animation = "desaparecer 0.5s forwards";
        }, 6000);
    </script>

    <footer id="contacto" style="margin-top: 80px;">
        <h3>Contacto</h3>
        <p>tiendaxochiahua@contacto.com.mx</p>
        <p>&copy; 2025 - Not Copyright Intended</p>
    </footer>

</body>

</html>