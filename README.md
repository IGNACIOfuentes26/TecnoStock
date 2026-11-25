# TecnoStock

Sistema de gestión y control de inventario de productos tecnológicos, desarrollado con PHP y MySQL.

## Tecnologías
- PHP
- MySQL
- PDO
- HTML y CSS
- phpMyAdmin (administración de la base de datos)
- Visual Studio Code (editor)

## Funcionalidades
- Inicio de sesión de administrador mediante sesiones PHP y verificación de contraseñas.
- Registro y consulta de productos.
- Búsqueda de productos por nombre, código o categoría.
- Edición y desactivación de productos.
- Registro de entradas y salidas de inventario.
- Validación de stock disponible.
- Historial de movimientos.

## Estructura del proyecto
```text
TecnoStock/
├── config/
│   └── conexion.php
├── dashboard.php
├── desactivar_producto.php
├── editar_producto.php
├── index.php
├── logout.php
├── movimientos.php
├── productos.php
└── probar_conexion.php
```

## Requisitos
- Un servidor local con PHP y MySQL (por ejemplo, XAMPP).
- PHP con la extensión PDO MySQL habilitada.

## Configuración
1. Copiar la carpeta del proyecto dentro del directorio web del servidor local (por ejemplo, `htdocs` en XAMPP).
2. Crear en MySQL una base de datos llamada `tecnostock`.
3. Crear/importar las tablas necesarias: `usuarios`, `categorias`, `productos` y `movimientos`, respetando los campos utilizados por el código.
4. Revisar `config/conexion.php` y ajustar host, nombre de base de datos, usuario y contraseña según la configuración local de MySQL.
5. Abrir `http://localhost/TecnoStock/` en el navegador.

## Nota sobre la base de datos
Este repositorio contiene los archivos PHP del proyecto. No incluye un volcado SQL de la base de datos, por lo que las tablas deben importarse o crearse por separado antes de ejecutar la aplicación.

## Seguridad
La configuración incluida está preparada para un entorno local de desarrollo (usuario MySQL `root` y contraseña vacía). No usar esos valores por defecto en un servidor público. Configurar credenciales seguras antes de desplegar.
