<?php
session_start();
require_once(__DIR__ . '/../config/bd.php');

$response = ['error' => true];

if (isset($_POST['id']) && isset($_POST['cantidad'])) {
    $id = $_POST['id'];
    $cantidad = max(1, intval($_POST['cantidad']));
    $_SESSION['carrito'][$id] = $cantidad;

    // Conexion
    $bd = new BaseDeDatos();
    $conexion = $bd->obtenerConexion();

    $stmt = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    $subtotal = $producto['precio'] * $cantidad;

    // Total general
    $totalGeneral = 0;
    foreach ($_SESSION['carrito'] as $prodID => $cant) {
        $stmt = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmt->execute([$prodID]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalGeneral += $p['precio'] * $cant;
    }

    // Total por unidad
    $totalCantidad = array_sum($_SESSION['carrito']);

    $response = [
        'error' => false,
        'subtotal' => number_format($subtotal, 2),
        'total' => number_format($totalGeneral, 2),
        'cantidad_total' => $totalCantidad
    ];
}

if (isset($_POST['eliminar_id'])) {
    $id = $_POST['eliminar_id'];
    unset($_SESSION['carrito'][$id]);

    // Recalcular total y cantidad
    $bd = new BaseDeDatos();
    $conexion = $bd->obtenerConexion();
    $totalGeneral = 0;
    $totalCantidad = 0;

    foreach ($_SESSION['carrito'] as $prodID => $cant) {
        $stmt = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmt->execute([$prodID]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalGeneral += $p['precio'] * $cant;
        $totalCantidad += $cant;
    }

    $response = [
        'error' => false,
        'total' => number_format($totalGeneral, 2),
        'cantidad_total' => $totalCantidad
    ];

    echo json_encode($response);
    exit;
}

if (isset($_POST['agregar_id'])) {
    $id = $_POST['agregar_id'];

    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;

    $totalCantidad = array_sum($_SESSION['carrito']);

    echo json_encode([
        'error' => false,
        'cantidad_total' => $totalCantidad
    ]);
    exit;
}


echo json_encode($response);
