<?php

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/ServicioVenta.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

$resultado = null;
$error = null;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new InvalidArgumentException('Solicitud no válida.');
    }

    $cliente = trim((string) ($_POST['cliente'] ?? ''));
    if ($cliente === '') {
        throw new InvalidArgumentException('El cliente es obligatorio.');
    }

    $productoIds = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    if (!is_array($productoIds) || !is_array($cantidades)) {
        throw new InvalidArgumentException('Debe agregar al menos un producto válido.');
    }

    $cantidadesPorProducto = ServicioVenta::agruparCantidades($productoIds, $cantidades);

    $repositorio = new RepositorioMySQL(conectar());
    $items = [];
    foreach ($cantidadesPorProducto as $productoId => $cantidad) {
        $producto = $repositorio->obtenerProducto($productoId);
        if ($producto === null) {
            throw new InvalidArgumentException('El producto seleccionado no existe.');
        }
        ServicioVenta::validarStock($producto['nombre'], $cantidad, (int) $producto['stock']);
        $items[] = [
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'precio_unitario' => (float) $producto['precio'],
        ];
    }

    $resumen = ServicioVenta::calcularResumen($items);
    $ventaId = $repositorio->registrarVenta($cliente, $items);
    $resultado = ['ventaId' => $ventaId] + $resumen;
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
} catch (Throwable $exception) {
    $error = 'La operación no se completó. Verifique la base de datos e inténtelo nuevamente.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultado de venta - Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <?php if ($error !== null): ?>
        <div class="alert alert-danger">
            <h1 class="h4">No se pudo registrar la venta</h1>
            <p class="mb-0"><?= e($error) ?></p>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <h1 class="h4">Venta registrada correctamente</h1>
            <p>Venta número <?= (int) $resultado['ventaId'] ?> para <?= e($cliente) ?>.</p>
            <dl class="row mb-0">
                <dt class="col-sm-3">Subtotal</dt><dd class="col-sm-9">S/ <?= number_format($resultado['subtotal'], 2, ',', '.') ?></dd>
                <dt class="col-sm-3">Descuento</dt><dd class="col-sm-9">S/ <?= number_format($resultado['descuento'], 2, ',', '.') ?></dd>
                <dt class="col-sm-3">Total</dt><dd class="col-sm-9"><strong>S/ <?= number_format($resultado['total'], 2, ',', '.') ?></strong></dd>
            </dl>
        </div>
    <?php endif; ?>
    <a class="btn btn-success" href="venta.php">Registrar otra venta</a>
    <a class="btn btn-secondary" href="index.php">Ver inventario</a>
</main>
</body>
</html>
