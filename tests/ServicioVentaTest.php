<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/ServicioVenta.php';

final class ServicioVentaTest extends TestCase
{
    public function testDescuentoCeroCuandoSubtotalEsExactamente100(): void
    {
        $this->assertEqualsWithDelta(0.0, ServicioVenta::calcularDescuento(100.00), 0.001);
    }

    public function testRechazaCantidadDecimal(): void
    {
        $this->assertFalse(ServicioVenta::validarCantidad(1.5));
    }
}