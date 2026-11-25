<?php
session_start();

require_once "config/conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST["correo"]);
    $clave = $_POST["clave"];

    $sql = "SELECT * FROM usuarios
            WHERE correo = ? AND estado = 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$correo]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($clave, $usuario["clave"])) {

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["nombre"] = $usuario["nombre"];

        header("Location: dashboard.php");
        exit;

    } else {

        $error = "Correo o contraseña incorrectos.";

    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | TecnoStock</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #eef2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login {
            background: white;
            width: 380px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.10);
        }

        h1 {
            color: #145b91;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitulo {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 7px;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #145b91;
            color: white;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0d426c;
        }

        .error {
            color: #c62828;
            background: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="login">

        <h1>TECNO STOCK</h1>

        <p class="subtitulo">Sistema de gestión de inventario</p>

        <?php if ($error != ""): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Correo electrónico</label>

            <input
                type="email"
                name="correo"
                placeholder="Ingrese su correo"
                required
            >

            <label>Contraseña</label>

            <input
                type="password"
                name="clave"
                placeholder="Ingrese su contraseña"
                required
            >

            <button type="submit">
                Iniciar sesión
            </button>

        </form>

    </div>

</body>
</html>
