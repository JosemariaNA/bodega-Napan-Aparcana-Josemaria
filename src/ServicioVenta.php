<?php

final class ServicioVenta
{
    public static function validarCantidadEntrada(mixed $cantidad): bool
    {
        return is_int($cantidad)
            ? self::validarCantidad($cantidad)
            : is_string($cantidad) && preg_match('/^[1-9][0-9]*$/D', $cantidad) === 1;
    }

    public static function validarCantidad(mixed $cantidad): bool
    {
        return is_int($cantidad) && $cantidad > 0;
    }

    public static function hayStock(int $cantidad, int $stock): bool
    {
        return self::validarCantidad($cantidad) && $cantidad <= $stock;
    }

    public static function calcularSubtotal(array $detalles): float
    {
        $subtotal = 0.0;

        foreach ($detalles as $detalle) {
            $subtotal += (float) $detalle['precio_unitario'] * (int) $detalle['cantidad'];
        }

        return round($subtotal, 2);
    }

    public static function calcularDescuento(float $subtotal): float
    {
        return $subtotal > 100.0 ? round($subtotal * 0.05, 2) : 0.0;
    }

    public static function calcularTotal(float $subtotal): float
    {
        return round($subtotal - self::calcularDescuento($subtotal), 2);
    }

    /**
     * @param array<int, mixed> $productoIds
     * @param array<int, mixed> $cantidades
     * @return array<int, int>
     */
    public static function agruparCantidades(array $productoIds, array $cantidades): array
    {
        if (count($productoIds) !== count($cantidades) || count($productoIds) === 0) {
            throw new InvalidArgumentException('Debe agregar al menos un producto válido.');
        }

        $cantidadesPorProducto = [];
        foreach ($productoIds as $indice => $productoId) {
            if (!is_string($productoId) && !is_int($productoId)) {
                throw new InvalidArgumentException('El producto seleccionado no es válido.');
            }

            $productoIdTexto = (string) $productoId;
            $cantidadEntrada = $cantidades[$indice] ?? '';
            if (!preg_match('/^[1-9][0-9]*$/D', $productoIdTexto)
                || !self::validarCantidadEntrada($cantidadEntrada)) {
                throw new InvalidArgumentException('La cantidad debe ser un entero positivo.');
            }

            $productoIdEntero = (int) $productoIdTexto;
            $cantidadEntera = (int) $cantidadEntrada;
            $cantidadesPorProducto[$productoIdEntero] =
                ($cantidadesPorProducto[$productoIdEntero] ?? 0) + $cantidadEntera;
        }

        return $cantidadesPorProducto;
    }

    public static function validarStock(string $nombre, int $cantidad, int $stock): void
    {
        if (!self::hayStock($cantidad, $stock)) {
            throw new InvalidArgumentException(
                'Stock insuficiente para ' . $nombre . '. Disponible: ' . $stock . '.'
            );
        }
    }

    /**
     * @return array{subtotal: float, descuento: float, total: float}
     */
    public static function calcularResumen(array $detalles): array
    {
        $subtotal = self::calcularSubtotal($detalles);
        $descuento = self::calcularDescuento($subtotal);

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => round($subtotal - $descuento, 2),
        ];
    }
}
