# 📊 Sistema Administrativo & Módulo de Facturación en Tiempo Real

Sistema web desarrollado para la gestión administrativa de ventas, registro de clientes y 
control automatizado de inventario. El proyecto está construido con **PHP (PDO)** y **MySQL**, 
aplicando transacciones SQL atómicas para garantizar la integridad de los datos en todo momento.

---

## 🚀 Características Principales

* Módulo de Facturación: Emisión de facturas de venta vinculadas dinámicamente a clientes y cajas registradoras.
* Control de Inventario Automático: Descuento inmediato de existencias en la tabla de productos al procesar cada transacción.
* Transacciones Atómicas (SQL): Uso de `beginTransaction()`, `commit()` y `rollBack()` para asegurar que la cabecera, el detalle y la actualización de stock se ejecuten de forma unificada.
* Validación Doble (Cliente/Servidor): Comprobación de existencias mediante JavaScript (`data-attributes`) en la interfaz y validación estricta en PHP antes de procesar la venta.
* Consolidación de Facturas Multi-Ítem: Consultas avanzadas con `GROUP BY`, `GROUP_CONCAT()` y `SUM()` para agrupar ítems y calcular totales por cabecera sin duplicar filas.

---

## 🛠️ Tecnologías Utilizadas

* Backend: PHP 8.x (Conexión segura vía PDO)
* Base de Datos: MySQL / MariaDB (phpMyAdmin)
* Frontend: HTML5, CSS3, JavaScript
* Servidor Local: Apache (WAMP)

---

## 🗄️ Estructura de la Base de Datos

El sistema se fundamenta en las siguientes tablas relacionales:

* `factura_cabecera`: Almacena el ID único de la factura, fecha, cliente (`fk_cliente`) y caja (`fk_caja`).
* `detalle_factura`: Registra los productos de cada factura (`fk_producto`, `fk_factura_cabecera`), cantidad y precio unitario.
* `productos`: Controla el inventario (`id_producto`, `Nombre`, `cod_barra`, `precio_venta`, `stock`).
* `clientes`: Información de compradores (`id_cliente`, `nombre_cliente`, `cedula`).

