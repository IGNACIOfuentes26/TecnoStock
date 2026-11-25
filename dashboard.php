<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

$nombre = $_SESSION["nombre"];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel principal | TecnoStock</title>

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
            padding: 22px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        .contenido {
            padding: 35px;
        }

        .bienvenida {
            margin-bottom: 30px;
            color: #333;
        }

        .tarjetas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .tarjeta h3 {
            color: #145b91;
        }

        .tarjeta p {
            color: #777;
        }

        .salir {
            color: white;
            text-decoration: none;
            background: #0d426c;
            padding: 10px 18px;
            border-radius: 6px;
        }

    </style>
</head>

<body>

    <header>
        <h1>TECNO STOCK</h1>

        <a class="salir" href="logout.php">
            Cerrar sesión
        </a>
    </header>

    <main class="contenido">

        <div class="bienvenida">
            <h2>¡Bienvenido/a, <?php echo htmlspecialchars($nombre); ?>!</h2>

            <p>
                Sistema de gestión y control de inventario.
            </p>
        </div>

        <div class="tarjetas">

            <div class="tarjeta">
                <h3>Productos</h3>
                <p>Administración del catálogo de productos.</p>
            </div>

            <div class="tarjeta">
                <h3>Categorías</h3>
                <p>Organización de productos por categorías.</p>
            </div>

            <div class="tarjeta">
                <h3>Movimientos</h3>
                <p>Control de entradas y salidas de existencias.</p>
            </div>

            <div class="tarjeta">
                <h3>Inventario</h3>
                <p>Consulta y control del stock disponible.</p>
            </div>

        </div>

    </main>

</body>
</html>
