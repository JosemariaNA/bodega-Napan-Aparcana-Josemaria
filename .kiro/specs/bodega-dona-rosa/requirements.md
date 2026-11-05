# Requirements Document

## Introduction

La aplicación web **Bodega «Doña Rosa»** permite a la administradora de la bodega registrar productos, consultar su inventario y registrar ventas, reemplazando el cuaderno manual. La aplicación calcula automáticamente subtotales, aplica descuentos y mantiene el stock actualizado en tiempo real. El sistema opera localmente sobre XAMPP (PHP 8 + MySQL), sin autenticación ni reportes estadísticos.

---

## Glossary

- **Sistema**: La aplicación web Bodega «Doña Rosa».
- **Administradora**: Doña Rosa, única usuaria del sistema.
- **Producto**: Artículo registrado en la bodega con nombre, precio unitario y stock disponible.
- **Stock**: Cantidad de unidades disponibles de un producto en el inventario.
- **Venta**: Transacción que registra uno o más productos vendidos a un cliente.
- **Detalle_de_Venta**: Línea de una venta que indica el producto, la cantidad y el precio unitario en el momento de la transacción.
- **Subtotal**: Suma de (precio unitario × cantidad) de todos los detalles de una venta, antes de aplicar descuento.
- **Descuento**: Reducción del 5 % aplicada al subtotal cuando éste supera S/ 100,00.
- **Total**: Resultado de restar el descuento al subtotal.

---

## Requirements

### Requirement 1: Registrar nuevo producto

**User Story:** Como administradora, quiero registrar nuevos productos con su nombre, precio y stock inicial, para mantener un catálogo actualizado de los artículos disponibles en la bodega.

#### Acceptance Criteria

1. WHEN la Administradora envía el formulario de nuevo producto con nombre, precio unitario y stock inicial, THE Sistema SHALL persistir el producto en la base de datos y mostrar un mensaje de confirmación.
2. WHEN la Administradora omite el nombre, el precio o el stock inicial, THE Sistema SHALL rechazar el envío y mostrar un mensaje indicando qué campo es obligatorio.
3. WHEN la Administradora ingresa un precio unitario igual o menor a S/ 0,00, THE Sistema SHALL rechazar el envío y mostrar un mensaje indicando que el precio debe ser mayor que cero.
4. WHEN la Administradora ingresa un stock inicial que no es un entero mayor que cero, THE Sistema SHALL rechazar el envío y mostrar un mensaje indicando que el stock debe ser un entero positivo.

---

### Requirement 2: Consultar productos e inventario

**User Story:** Como administradora, quiero consultar los productos registrados y sus existencias actuales, para conocer el stock disponible antes de atender a un cliente.

#### Acceptance Criteria

1. WHEN la Administradora accede a la sección de inventario, THE Sistema SHALL mostrar la lista completa de productos con su nombre, precio unitario y stock disponible.
2. WHEN no existen productos registrados, THE Sistema SHALL mostrar un mensaje indicando que el inventario está vacío.
3. WHEN el stock de un producto es igual a cero, THE Sistema SHALL mostrar el producto en la lista con su stock en cero.

---

### Requirement 3: Registrar una venta

**User Story:** Como administradora, quiero registrar las ventas realizadas a los clientes indicando los productos y cantidades, para llevar un control preciso de cada transacción.

#### Acceptance Criteria

1. WHEN la Administradora confirma una venta con al menos un detalle de venta válido, THE Sistema SHALL persistir la venta y sus detalles en la base de datos en la misma transacción. [R4]
2. WHEN la Administradora ingresa una cantidad que no es un entero mayor que cero (incluye cero, negativos y decimales), THE Sistema SHALL rechazar el detalle y mostrar un mensaje indicando que la cantidad debe ser un entero positivo. [R3]
3. IF la cantidad solicitada para un producto supera el stock disponible de ese producto, THEN THE Sistema SHALL rechazar la venta y mostrar un mensaje indicando el stock disponible del producto. [R1]
4. WHEN la cantidad solicitada para un producto es igual al stock disponible, THE Sistema SHALL aceptar la venta como válida. [R1]

---

### Requirement 4: Calcular subtotal, descuento y total

**User Story:** Como administradora, quiero que la aplicación calcule automáticamente el subtotal, el descuento y el total de cada venta, para evitar errores aritméticos en el cobro a los clientes.

#### Acceptance Criteria

1. WHEN la Administradora agrega uno o más detalles de venta, THE Sistema SHALL calcular el subtotal como la suma de (precio unitario × cantidad) de cada Detalle_de_Venta.
2. WHEN el subtotal de la venta es mayor a S/ 100,00, THE Sistema SHALL aplicar un descuento del 5 % sobre el subtotal y calcular el total como subtotal − descuento. [R2]
3. WHEN el subtotal de la venta es igual o menor a S/ 100,00, THE Sistema SHALL calcular el total igual al subtotal sin aplicar descuento. [R2]
4. WHEN la Administradora confirma la venta, THE Sistema SHALL mostrar el subtotal, el monto del descuento aplicado (S/ 0,00 si no aplica) y el total.

---

### Requirement 5: Actualizar inventario al confirmar la venta

**User Story:** Como administradora, quiero que el stock de cada producto se descuente automáticamente al confirmar una venta, para mantener el inventario siempre actualizado sin pasos adicionales.

#### Acceptance Criteria

1. WHEN la Administradora confirma una venta, THE Sistema SHALL reducir el stock de cada producto involucrado en la cantidad vendida, dentro de la misma transacción de base de datos. [R4]
2. IF la transacción de base de datos falla al actualizar el stock, THEN THE Sistema SHALL revertir todos los cambios de esa venta y mostrar un mensaje de error indicando que la operación no se completó. [R4]
3. WHEN la venta es confirmada exitosamente, THE Sistema SHALL reflejar el stock actualizado de cada producto en la sección de inventario.
