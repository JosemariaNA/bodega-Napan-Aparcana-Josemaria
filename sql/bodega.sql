CREATE DATABASE bodega;
USE bodega;

CREATE TABLE producto (
    id      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(150)    NOT NULL,
    precio  DECIMAL(10,2)   NOT NULL,
    stock   INT UNSIGNED    NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE venta (
    id        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    subtotal  DECIMAL(10,2)   NOT NULL,
    descuento DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    total     DECIMAL(10,2)   NOT NULL,
    fecha     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detalle_venta (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    venta_id        INT UNSIGNED    NOT NULL,
    producto_id     INT UNSIGNED    NOT NULL,
    cantidad        INT UNSIGNED    NOT NULL,
    precio_unitario DECIMAL(10,2)   NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_detalle_venta
        FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO producto (nombre, precio, stock) VALUES
    ('Arroz Costeño', 5.50, 25),
    ('Aceite Primor', 9.90, 10),
    ('Leche Gloria', 4.20, 0),
    ('Azúcar Cartavio', 3.80, 40),
    ('Atún Florida', 7.50, 15),
    ('Gaseosa Inca Kola', 12.00, 8);
