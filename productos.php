<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

require_once "config/conexion.php";

$mensaje = "";
$error = "";

// Obtener categorias activas
$sqlCategorias = "SELECT * FROM categorias
                  WHERE estado = 1 ORDER BY nombre";

$categorias = $conexion->query($sqlCategorias)
                       ->fetchAll(PDO::FETCH_ASSOC);

// Registrar producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = $_POST["precio"] ?? "";
    $stock = $_POST["stock_actual"] ?? "";
    $minimo = $_POST["stock_minimo"] ?? "";
    $categoria = $_POST["id_categoria"] ?? "";

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
            "SELECT id_producto FROM productos WHERE codigo = ?"
        );

        $buscar->execute([$codigo]);

        if ($buscar->fetch()) {

            $error = "El codigo de producto ya existe.";

        } else {

            $validarCategoria = $conexion->prepare(
                "SELECT id_categoria FROM categorias
                 WHERE id_categoria = ? AND estado = 1"
            );

            $validarCategoria->execute([$categoria]);

            if (!$validarCategoria->fetch()) {

                $error = "Selecciona una categoria valida.";

            } else {

                $sql = "INSERT INTO productos
                (codigo, nombre, descripcion, precio,
                stock_actual, stock_minimo, estado, id_categoria)
                VALUES (?, ?, ?, ?, ?, ?, 1, ?)";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    $codigo,
                    $nombre,
                    $descripcion,
                    $precio,
                    $stock,
                    $minimo,
                    $categoria
                ]);

                $mensaje = "Producto registrado correctamente.";
            }
        }
    }
}

// Buscar productos
$busqueda = trim($_GET["buscar"] ?? "");

$sql = "SELECT p.*, c.nombre AS categoria
        FROM productos p
        INNER JOIN categorias c
        ON p.id_categoria = c.id_categoria
        WHERE p.estado = 1";

if ($busqueda != "") {
    $sql .= " AND (
        p.nombre LIKE :busqueda
        OR p.codigo LIKE :busqueda
        OR c.nombre LIKE :busqueda
    )";
}

$sql .= " ORDER BY p.id_producto DESC";

$stmt = $conexion->prepare($sql);

if ($busqueda != "") {
    $stmt->execute([
        "busqueda" => "%$busqueda%"
    ]);
} else {
    $stmt->execute();
}

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Productos | TecnoStock</title>

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

.campos {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
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

button:hover {
    background: #0d426c;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 950px;
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

.exito {
    color: #155724;
    background: #d4edda;
    padding: 12px;
    margin-bottom: 15px;
}

.error {
    color: #721c24;
    background: #f8d7da;
    padding: 12px;
    margin-bottom: 15px;
}

.alerta {
    color: #b45309;
    font-weight: bold;
}

.btn-editar {
    background: #145b91;
    color: white;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 5px;
}

.btn-desactivar {
    background: #c0392b;
    color: white;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 5px;
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

    <h2>Gestión de productos</h2>

    <section class="formulario">

        <h3>Registrar nuevo producto</h3>

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

            <div class="campos">

                <div>
                    <label>Codigo</label>
                    <input type="text" name="codigo"
                           maxlength="30" required>
                </div>

                <div>
                    <label>Nombre del producto</label>
                    <input type="text" name="nombre"
                           maxlength="120" required>
                </div>

                <div>
                    <label>Categoria</label>
                    <select name="id_categoria" required>
                        <option value="">Seleccionar categoria</option>

                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat["id_categoria"]; ?>">
                                <?php echo htmlspecialchars($cat["nombre"]); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div>
                    <label>Precio</label>
                    <input type="number" name="precio"
                           min="0" step="0.01" required>
                </div>

                <div>
                    <label>Stock actual</label>
                    <input type="number" name="stock_actual"
                           min="0" step="1" required>
                </div>

                <div>
                    <label>Stock minimo</label>
                    <input type="number" name="stock_minimo"
                           min="0" step="1" required>
                </div>

            </div>

            <label>Descripcion</label>
            <textarea name="descripcion" rows="3"></textarea>

            <button type="submit">
                Registrar producto
            </button>

        </form>

    </section>

    <section class="tabla">

        <h3>Listado de productos</h3>

        <form method="GET">

            <input
                type="text"
                name="buscar"
                placeholder="Buscar por nombre, codigo o categoria..."
                value="<?php echo htmlspecialchars($busqueda); ?>"
            >

            <button type="submit">Buscar</button>

            <a href="productos.php">Ver todos</a>

        </form>

        <div class="tabla-contenedor">

            <table>

                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Categoria</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Stock minimo</th>
                        <th>Estado del stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($productos as $p): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($p["codigo"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($p["nombre"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($p["categoria"]); ?>
                            </td>

                            <td>
                                $<?php echo number_format($p["precio"], 0, ",", "."); ?>
                            </td>

                            <td>
                                <?php echo $p["stock_actual"]; ?>
                            </td>

                            <td>
                                <?php echo $p["stock_minimo"]; ?>
                            </td>

                            <td>

                                <?php if ($p["stock_actual"] < $p["stock_minimo"]): ?>
                                    <span class="alerta">Stock bajo</span>
                                <?php else: ?>
                                    Disponible
                                <?php endif; ?>

                            </td>

                            <td>

                                <a
                                    class="btn-editar"
                                    href="editar_producto.php?id=<?php echo $p["id_producto"]; ?>"
                                >
                                    Editar
                                </a>

                                <a
                                    class="btn-desactivar"
                                    href="desactivar_producto.php?id=<?php echo $p["id_producto"]; ?>"
                                    onclick="return confirm('¿Estas seguro de desactivar este producto?');"
                                >
                                    Desactivar
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (count($productos) == 0): ?>
                        <tr>
                            <td colspan="8">
                                No hay productos registrados.
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>
