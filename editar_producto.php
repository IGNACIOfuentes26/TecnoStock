<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "config/conexion.php";

$error = "";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    die("Producto no valido.");
}

// Obtener producto
$stmt = $conexion->prepare(
    "SELECT * FROM productos WHERE id_producto = ? AND estado = 1"
);
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado o inactivo.");
}

// Obtener categorias
$categorias = $conexion->query(
    "SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre"
)->fetchAll(PDO::FETCH_ASSOC);

// Actualizar producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = $_POST["precio"];
    $stock = $_POST["stock_actual"];
    $minimo = $_POST["stock_minimo"];
    $categoria = $_POST["id_categoria"];

    if (
        $codigo == "" ||
        $nombre == "" ||
        !is_numeric($precio) ||
        !is_numeric($stock) ||
        !is_numeric($minimo) ||
        !ctype_digit((string)$stock) ||
        !ctype_digit((string)$minimo) ||
        !ctype_digit((string)$categoria) ||
        $precio < 0 || $stock < 0 || $minimo < 0
    ) {
        $error = "Verifica los datos ingresados.";
    } else {

        $buscar = $conexion->prepare(
            "SELECT id_producto FROM productos
             WHERE codigo = ? AND id_producto != ?"
        );
        $buscar->execute([$codigo, $id]);

        $categoriaValida = $conexion->prepare(
            "SELECT id_categoria FROM categorias
             WHERE id_categoria = ? AND estado = 1"
        );
        $categoriaValida->execute([$categoria]);

        if ($buscar->fetch()) {
            $error = "Ese codigo ya pertenece a otro producto.";
        } elseif (!$categoriaValida->fetch()) {
            $error = "Selecciona una categoria valida.";
        } else {

            $sql = "UPDATE productos SET
                    codigo = ?,
                    nombre = ?,
                    descripcion = ?,
                    precio = ?,
                    stock_actual = ?,
                    stock_minimo = ?,
                    id_categoria = ?
                    WHERE id_producto = ? AND estado = 1";

            $actualizar = $conexion->prepare($sql);

            $actualizar->execute([
                $codigo,
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $minimo,
                $categoria,
                $id
            ]);

            header("Location: productos.php");
            exit;
        }
    }

    // Mantener los datos ingresados si existe un error
    $producto = array_merge($producto, [
        "codigo" => $codigo,
        "nombre" => $nombre,
        "descripcion" => $descripcion,
        "precio" => $precio,
        "stock_actual" => $stock,
        "stock_minimo" => $minimo,
        "id_categoria" => $categoria
    ]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar producto | TecnoStock</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #eef2f7;
    padding: 25px;
}
.contenedor {
    background: white;
    max-width: 750px;
    margin: auto;
    padding: 30px;
    border-radius: 12px;
}
label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
input, select, textarea {
    width: 100%;
    padding: 11px;
    margin-top: 7px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 6px;
}
button {
    background: #145b91;
    color: white;
    border: 0;
    padding: 12px 22px;
    margin-top: 20px;
    border-radius: 6px;
    cursor: pointer;
}
a {
    color: #145b91;
}
.error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
}
</style>
</head>

<body>

<div class="contenedor">

<h1>Editar producto</h1>

<p>Modifica la información del producto seleccionado.</p>

<?php if ($error != ""): ?>
    <div class="error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form method="POST">

<input type="hidden" name="id" value="<?php echo $id; ?>">

<label>Codigo</label>
<input type="text" name="codigo" maxlength="30"
value="<?php echo htmlspecialchars($producto["codigo"]); ?>" required>

<label>Nombre</label>
<input type="text" name="nombre" maxlength="120"
value="<?php echo htmlspecialchars($producto["nombre"]); ?>" required>

<label>Categoria</label>
<select name="id_categoria" required>

<?php foreach ($categorias as $cat): ?>
<option value="<?php echo $cat["id_categoria"]; ?>"
<?php echo $producto["id_categoria"] == $cat["id_categoria"] ? "selected" : ""; ?>>
<?php echo htmlspecialchars($cat["nombre"]); ?>
</option>
<?php endforeach; ?>

</select>

<label>Precio</label>
<input type="number" name="precio" min="0" step="0.01"
value="<?php echo htmlspecialchars($producto["precio"]); ?>" required>

<label>Stock actual</label>
<input type="number" name="stock_actual" min="0" step="1"
value="<?php echo htmlspecialchars($producto["stock_actual"]); ?>" required>

<label>Stock minimo</label>
<input type="number" name="stock_minimo" min="0" step="1"
value="<?php echo htmlspecialchars($producto["stock_minimo"]); ?>" required>

<label>Descripcion</label>
<textarea name="descripcion" rows="4"><?php echo htmlspecialchars($producto["descripcion"] ?? ""); ?></textarea>

<button type="submit">Guardar cambios</button>

<a href="productos.php">Cancelar</a>

</form>

</div>

</body>
</html>
