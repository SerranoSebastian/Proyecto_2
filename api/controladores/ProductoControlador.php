<?php

require_once(__DIR__ . '/../config/bd.php');


class ProductoControlador
{
    private $conexion;

    public function __construct()
    {
        $bd = new BaseDeDatos();
        $this->conexion = $bd->obtenerConexion();
    }

    public function agregarProducto($datos, $archivos)
    {
        $nombre = $datos['nombre'];
        $descripcion = $datos['descripcion'];
        $precio = $datos['precio'];
        $stock = $datos['stock'];

        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock) 
        VALUES (:nombre, :descripcion, :precio, :stock)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);

        if ($stmt->execute()) {
            $productoId = $this->conexion->lastInsertId();

            if (isset($archivos['imagenes'])) {
                $total = count($archivos['imagenes']['name']);

                for ($i = 0; $i < $total; $i++) {
                    $nombreTmp = $archivos['imagenes']['tmp_name'][$i];
                    $destino = "../public/img/" . $productoId . "_" . ($i + 1) . ".jpg";

                    if (is_uploaded_file($nombreTmp)) {
                        move_uploaded_file($nombreTmp, $destino);
                    }
                }
            }

            header("Location: ../public/admin/productos.php?success=1");
            exit();

        } else {
            echo "Error al agregar el producto.";
        }
    }

    public function editarProducto($datos)
    {
        $id = $datos['id'];
        $nombre = $datos['nombre'];
        $descripcion = $datos['descripcion'];
        $precio = $datos['precio'];
        $stock = $datos['stock'];

        $sql = "UPDATE productos 
            SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock 
            WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);

        if ($stmt->execute()) {
            header("Location: ../public/admin/productos.php?updated=1");
            exit();
        } else {
            echo "Error al actualizar el producto.";
        }
    }


    public function eliminarProducto($id)
    {
        // Eliminar imágenes
        for ($i = 1; $i <= 5; $i++) {
            $ruta = "../public/img/" . $id . "_" . $i . ".jpg";
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }

        // Eliminar producto de la BD
        $sql = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: ../public/admin/productos.php?deleted=1");
            exit();

        } else {
            echo "Error al eliminar el producto.";
        }
    }
}
