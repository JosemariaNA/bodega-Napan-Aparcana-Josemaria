<?php

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

$error = null;
$productos = [];

try {
    $repositorio = new RepositorioMySQL(conectar());
    $productos = $repositorio->listarProductos();
} catch (Throwable $exception) {
    $error = 'No se pudo consultar el inventario. Verifique la conexión con MySQL.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario - Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bodega Doña Rosa</a>
        <a class="btn btn-light" href="venta.php">Registrar venta</a>
    </div>
</nav>
<main class="container">
    <h1 class="mb-4">Inventario</h1>
    <?php if ($error !== null): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php elseif ($productos === []): ?>
        <div class="alert alert-info">El inventario está vacío.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle bg-white">
                <thead class="table-success">
                <tr><th>Producto</th><th>Precio</th><th>Stock disponible</th></tr>
                </thead>
                <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>S/ <?= number_format((float) $producto['precio'], 2, ',', '.') ?></td>
                        <td><?= (int) $producto['stock'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
