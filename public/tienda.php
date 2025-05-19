<?php
session_start();

require_once '../api/config/bd.php';

$bd = new BaseDeDatos();
$conexion = $bd->obtenerConexion();

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

// Obtener productos
$stmt = $conexion->prepare("SELECT * FROM productos WHERE stock > 0");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);


//Alertas aleatorias
$nombres = ['Juanito Pérez', 'Pepito Lopez', 'Carlos Santana', 'Billie Eilish', 'Luis Ortega'];

$nombreAleatorio = $nombres[array_rand($nombres)];
$productoAleatorio = $productos[array_rand($productos)];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tienda Xochiahua</title>
    <link rel="stylesheet" href="css/estilo_tienda.css">
</head>

<body>

    <header>
        <div class="logo">Tienda<span>Xochiahua</span></div>

        <button class="menu-toggle" id="menuToggle">&#9776;</button>

        <nav id="navbar">
            <a href="contacto.php">Contactanos</a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                <a href="cliente/pedidos.php">Mis pedidos</a>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
            <?php endif; ?>
        </nav>
    </header>



    <main>
        <section class="hero-portada">
            <div class="contenido-hero">
                <h1>Bienvenido a Tienda Xochiahua</h1>
                <p>La mejor tecnología al alcance de todos.</p>
            </div>
        </section>

        <h1>Productos disponibles</h1>
        <section class="carrusel-productos">
            <div class="carrusel-contenedor">
                <?php foreach ($productos as $producto): ?>
                    <div class="producto-card">
                        <a href="producto.php?id=<?= $producto['id'] ?>">
                            <img src="img/<?= $producto['id'] . "_" . "1" ?>.jpg" alt="<?= $producto['nombre'] ?>">
                        </a>
                        <div class="contenido">
                            <a href="producto.php?id=<?= $producto['id'] ?>">
                                <h3><?= $producto['nombre'] ?></h3>
                            </a>
                            <p class="precio">$<?= number_format($producto['precio'], 2) ?></p>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                                <button class="btn agregar-carrito" data-id="<?= $producto['id'] ?>">Agregar al carrito</button>
                            <?php else: ?>
                                <a href="login.php" class="btn" style="background-color:#e53e3e;">Inicia sesión para
                                    comprar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>


        <section class="vision">
            <h2>Nuestra Visión</h2>
            <p>
                En Tienda Xochiahua creemos en la tecnología como herramienta de transformación.
                Nos esforzamos por ofrecer productos de calidad, accesibles, y con el mejor soporte posible.
                Nuestra misión es hacer que cada cliente sienta que invierte en algo más que hardware: invierte en su
                futuro.
            </p>
        </section>



    </main>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
        <?php
        $totalCantidad = 0;
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $cantidad) {
                $totalCantidad += $cantidad;
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


    <footer id="contacto">
        <h3>Contacto</h3>
        <p>tiendaxochiahua@contacto.com.mx
        <p>
        <p>&copy; 2025 - Not Copyright Intended</p>
    </footer>

</body>

</html>