<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "config/conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Producto no valido.");
}

// Desactivar producto sin eliminarlo
$sql = "UPDATE productos
        SET estado = 0
        WHERE id_producto = ? AND estado = 1";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);

header("Location: productos.php");
exit;
?>
