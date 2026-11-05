<?php

final class RepositorioMySQL
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listarProductos(): array
    {
        $sentencia = $this->pdo->query(
            'SELECT id, nombre, precio, stock FROM producto ORDER BY nombre'
        );

        return $sentencia->fetchAll();
    }

    public function obtenerProducto(int $id): ?array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, nombre, precio, stock FROM producto WHERE id = :id'
        );
        $sentencia->execute(['id' => $id]);
        $producto = $sentencia->fetch();

        return $producto === false ? null : $producto;
    }

    /**
     * @param list<array{producto_id: int, cantidad: int, precio_unitario: float}> $items
     */
    public function registrarVenta(string $cliente, array $items): int
    {
        if (trim($cliente) === '') {
            throw new InvalidArgumentException('El cliente es obligatorio.');
        }

        if ($items === []) {
            throw new InvalidArgumentException('La venta debe tener al menos un producto.');
        }

        $this->pdo->beginTransaction();

        try {
            $itemsConPrecio = [];
            foreach ($items as $item) {
                $producto = $this->obtenerProductoBloqueado((int) $item['producto_id']);
                $cantidad = (int) $item['cantidad'];

                if ($producto === null) {
                    throw new InvalidArgumentException('El producto seleccionado no existe.');
                }

                ServicioVenta::validarStock(
                    $producto['nombre'],
                    $cantidad,
                    (int) $producto['stock']
                );

                $itemsConPrecio[] = [
                    'producto_id' => (int) $producto['id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => (float) $producto['precio'],
                ];
            }

            $resumen = ServicioVenta::calcularResumen($itemsConPrecio);

            $insertarVenta = $this->pdo->prepare(
                'INSERT INTO venta (subtotal, descuento, total) VALUES (:subtotal, :descuento, :total)'
            );
            $insertarVenta->execute([
                'subtotal' => $resumen['subtotal'],
                'descuento' => $resumen['descuento'],
                'total' => $resumen['total'],
            ]);
            $ventaId = (int) $this->pdo->lastInsertId();

            $insertarDetalle = $this->pdo->prepare(
                'INSERT INTO detalle_venta
                    (venta_id, producto_id, cantidad, precio_unitario)
                 VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario)'
            );
            $actualizarStock = $this->pdo->prepare(
                'UPDATE producto SET stock = stock - :cantidad_restar
                 WHERE id = :producto_id AND stock >= :cantidad_minima'
            );

            foreach ($itemsConPrecio as $item) {
                $insertarDetalle->execute([
                    'venta_id' => $ventaId,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                ]);
                $actualizarStock->execute([
                    'cantidad_restar' => $item['cantidad'],
                    'producto_id' => $item['producto_id'],
                    'cantidad_minima' => $item['cantidad'],
                ]);

                if ($actualizarStock->rowCount() !== 1) {
                    throw new RuntimeException('No se pudo actualizar el stock.');
                }
            }

            $this->pdo->commit();

            return $ventaId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function obtenerProductoBloqueado(int $id): ?array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, nombre, precio, stock
             FROM producto
             WHERE id = :id
             FOR UPDATE'
        );
        $sentencia->execute(['id' => $id]);
        $producto = $sentencia->fetch();

        return $producto === false ? null : $producto;
    }
}
