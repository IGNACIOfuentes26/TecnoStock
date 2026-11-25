<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "config/conexion.php";

$mensaje = "";
$error = "";

// Registrar movimiento
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_producto = $_POST["id_producto"] ?? "";
    $tipo = $_POST["tipo"] ?? "";
    $cantidad = $_POST["cantidad"] ?? "";
    $observacion = trim($_POST["observacion"] ?? "");

    if (
        !ctype_digit((string)$id_producto) ||
        !ctype_digit((string)$cantidad) ||
        $cantidad < 1 ||
        !in_array($tipo, ["entrada", "salida"], true)
    ) {

        $error = "Verifica los datos ingresados.";

    } else {

        try {

            $conexion->beginTransaction();

            // Consultar y bloquear producto
            $sql = "SELECT stock_actual FROM productos
                    WHERE id_producto = ? AND estado = 1
                    FOR UPDATE";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_producto]);

            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {

                throw new Exception("Producto no encontrado o inactivo.");

            }

            $stock = (int)$producto["stock_actual"];
            $cantidad = (int)$cantidad;

            if ($tipo == "salida" && $cantidad > $stock) {

                throw new Exception("No hay stock suficiente para esta salida.");

            }

            // Calcular nuevo stock
            if ($tipo == "entrada") {
                $nuevoStock = $stock + $cantidad;
            } else {
                $nuevoStock = $stock - $cantidad;
            }

            // Actualizar stock
            $actualizar = $conexion->prepare(
                "UPDATE productos
                 SET stock_actual = ?
                 WHERE id_producto = ?"
            );

            $actualizar->execute([
                $nuevoStock,
                $id_producto
            ]);

            // Guardar movimiento
            $sqlMovimiento = "INSERT INTO movimientos
                (id_producto, tipo, cantidad, observacion, id_usuario)
                VALUES (?, ?, ?, ?, ?)";

            $movimiento = $conexion->prepare($sqlMovimiento);

            $movimiento->execute([
                $id_producto,
                $tipo,
                $cantidad,
                $observacion,
                $_SESSION["id_usuario"]
            ]);

            $conexion->commit();

            $mensaje = "Movimiento registrado correctamente.";

        } catch (Exception $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

// Obtener productos activos
$productos = $conexion->query(
    "SELECT id_producto, codigo, nombre, stock_actual
     FROM productos
     WHERE estado = 1
     ORDER BY nombre"
)->fetchAll(PDO::FETCH_ASSOC);

// Consultar historial
$sqlHistorial = "SELECT m.*, p.nombre AS producto,
                        p.codigo, u.nombre AS usuario
                 FROM movimientos m
                 INNER JOIN productos p
                 ON m.id_producto = p.id_producto
                 INNER JOIN usuarios u
                 ON m.id_usuario = u.id_usuario
                 ORDER BY m.id_movimiento DESC";

$historial = $conexion->query($sqlHistorial)
                      ->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Movimientos | TecnoStock</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    background: #eef2f7;
}

header {
    background: #145b91;
    color: white;
    padding: 20px 30px;
}

header a {
    color: white;
    text-decoration: none;
}

.contenedor {
    padding: 30px;
}

.formulario, .tabla {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

input, select, textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-bottom: 15px;
}

button {
    background: #145b91;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
}

.exito {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    margin-bottom: 15px;
}

.error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    margin-bottom: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #145b91;
    color: white;
}

.tabla-contenedor {
    overflow-x: auto;
}

</style>

</head>

<body>

<header>

<h1>TECNO STOCK</h1>

<a href="dashboard.php">
← Volver al panel principal
</a>

</header>

<div class="contenedor">

<h2>Gestión de movimientos</h2>

<section class="formulario">

<h3>Registrar movimiento de inventario</h3>

<?php if ($mensaje != ""): ?>
<div class="exito">
<?php echo htmlspecialchars($mensaje); ?>
</div>
<?php endif; ?>

<?php if ($error != ""): ?>
<div class="error">
<?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<form method="POST">

<label>Seleccionar producto</label>

<select name="id_producto" required>

<option value="">Seleccionar producto</option>

<?php foreach ($productos as $p): ?>

<option value="<?php echo $p["id_producto"]; ?>">
<?php echo htmlspecialchars($p["codigo"] . " - " . $p["nombre"] . " (Stock: " . $p["stock_actual"] . ")"); ?>
</option>

<?php endforeach; ?>

</select>

<label>Tipo de movimiento</label>

<select name="tipo" required>

<option value="">Seleccionar movimiento</option>
<option value="entrada">Entrada</option>
<option value="salida">Salida</option>

</select>

<label>Cantidad</label>

<input type="number" name="cantidad" min="1" step="1" required>

<label>Observación</label>

<textarea name="observacion" rows="3"></textarea>

<button type="submit">
Registrar movimiento
</button>

</form>

</section>

<section class="tabla">

<h3>Historial de movimientos</h3>

<div class="tabla-contenedor">

<table>

<thead>

<tr>
<th>Producto</th>
<th>Código</th>
<th>Tipo</th>
<th>Cantidad</th>
<th>Usuario</th>
<th>Observación</th>
</tr>

</thead>

<tbody>

<?php foreach ($historial as $m): ?>

<tr>

<td><?php echo htmlspecialchars($m["producto"]); ?></td>

<td><?php echo htmlspecialchars($m["codigo"]); ?></td>

<td><?php echo htmlspecialchars($m["tipo"]); ?></td>

<td><?php echo $m["cantidad"]; ?></td>

<td><?php echo htmlspecialchars($m["usuario"]); ?></td>

<td><?php echo htmlspecialchars($m["observacion"] ?? ""); ?></td>

</tr>

<?php endforeach; ?>

<?php if (count($historial) == 0): ?>

<tr>
<td colspan="6">No existen movimientos registrados.</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</section>

</div>

</body>
</html>
