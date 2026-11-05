<?php

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/RepositorioMySQL.php';

$error = null;
$productos = [];

try {
    $productos = (new RepositorioMySQL(conectar()))->listarProductos();
} catch (Throwable $exception) {
    $error = 'No se pudo cargar el catálogo. Verifique la conexión con MySQL.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar venta - Bodega Doña Rosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Bodega Doña Rosa</a>
    </div>
</nav>
<main class="container">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3 mb-4">Registrar venta</h1>
            <?php if ($error !== null): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php elseif ($productos === []): ?>
                <div class="alert alert-info">No hay productos disponibles para vender.</div>
            <?php else: ?>
                <form method="post" action="guardar_venta.php">
                    <div class="mb-3">
                        <label class="form-label" for="cliente">Cliente</label>
                        <input class="form-control" id="cliente" name="cliente" required maxlength="150">
                    </div>
                    <div id="lineas">
                        <div class="row g-2 mb-2 linea">
                            <div class="col-md-8">
                                <label class="form-label">Producto</label>
                                <select class="form-select" name="producto_id[]" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?= (int) $producto['id'] ?>"
                                            <?= (int) $producto['stock'] === 0 ? 'disabled' : '' ?>>
                                            <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            (S/ <?= number_format((float) $producto['precio'], 2, ',', '.') ?>,
                                            stock <?= (int) $producto['stock'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cantidad</label>
                                <input class="form-control" type="number" name="cantidad[]" min="1" step="1" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button class="btn btn-outline-danger quitar" type="button" aria-label="Quitar línea">X</button>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-outline-success mb-3" id="agregar" type="button">Agregar producto</button>
                    <div>
                        <button class="btn btn-success" type="submit">Confirmar venta</button>
                        <a class="btn btn-secondary" href="index.php">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
const lineas = document.getElementById('lineas');
const plantilla = lineas ? lineas.firstElementChild.cloneNode(true) : null;
document.getElementById('agregar')?.addEventListener('click', () => {
    const nueva = plantilla.cloneNode(true);
    nueva.querySelector('select').selectedIndex = 0;
    nueva.querySelector('input').value = '';
    lineas.appendChild(nueva);
});
lineas?.addEventListener('click', (evento) => {
    if (evento.target.classList.contains('quitar') && lineas.children.length > 1) {
        evento.target.closest('.linea').remove();
    }
});
</script>
</body>
</html>
