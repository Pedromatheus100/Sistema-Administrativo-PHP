-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 08-08-2026 a las 14:37:43
-- Versión del servidor: 5.7.36
-- Versión de PHP: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_administrativo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja`
--

DROP TABLE IF EXISTS `caja`;
CREATE TABLE IF NOT EXISTS `caja` (
  `id_caja` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto_apertura` decimal(10,2) NOT NULL,
  `monto_cierre` decimal(10,2) NOT NULL,
  `estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_caja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre_categoria` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `Nombre_categoria`) VALUES
(60, 'Viveres'),
(61, 'Juguetes'),
(62, 'Limpieza'),
(63, 'Articulos para el Hogar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_cliente` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fk_tipo_cliente` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `fk_tipo_cliente` (`fk_tipo_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `cedula`, `nombre_cliente`, `telefono`, `fk_tipo_cliente`) VALUES
(11, 'V-10.566.214', 'Pedro', '04141332210', 'XX'),
(12, 'J-3098765', 'Abasto El Sol', '04127655110', 'LL'),
(13, 'J-00000001', 'Alcaldia', '04122123320', 'ZZ'),
(14, 'J-54566632', 'Polar', '04262302110', 'LL'),
(15, 'V-20.870.315', 'Luis', '04141342211', 'XX'),
(24, 'v-29331990', 'Gabriel Jimenez', '04147789198', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_factura`
--

DROP TABLE IF EXISTS `detalle_factura`;
CREATE TABLE IF NOT EXISTS `detalle_factura` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `fk_producto` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fk_factura_cabecera` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `fk_factura_cabecera` (`fk_factura_cabecera`),
  KEY `fk_producto` (`fk_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_factura`
--

INSERT INTO `detalle_factura` (`id_detalle`, `fk_producto`, `fk_factura_cabecera`, `cantidad`, `precio_unitario`) VALUES
(1, '01', 333, 2, '3.00'),
(2, '02', 334, 3, '2.50'),
(3, '03', 335, 1, '3.50'),
(4, '04', 336, 2, '20.00'),
(5, '05', 336, 1, '3.00'),
(6, '2', 337, 4, '2.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_cabecera`
--

DROP TABLE IF EXISTS `factura_cabecera`;
CREATE TABLE IF NOT EXISTS `factura_cabecera` (
  `id_factura_cabecera` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `fk_cliente` int(11) DEFAULT NULL,
  `fk_caja` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_factura_cabecera`),
  KEY `fk_cliente` (`fk_cliente`),
  KEY `fk_caja` (`fk_caja`)
) ENGINE=InnoDB AUTO_INCREMENT=338 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `factura_cabecera`
--

INSERT INTO `factura_cabecera` (`id_factura_cabecera`, `fecha`, `fk_cliente`, `fk_caja`) VALUES
(333, '2026-08-12', 11, 66),
(334, '2026-08-12', 13, 66),
(335, '2026-08-15', 14, 68),
(336, '2026-08-16', 14, 69),
(337, '2026-08-07', 13, 66);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` int(10) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_barra` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio_compra` decimal(10,0) NOT NULL,
  `precio_venta` decimal(10,0) NOT NULL,
  `stock` int(11) NOT NULL,
  `fk_categoria` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `fk_categoria` (`fk_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `Nombre`, `cod_barra`, `precio_compra`, `precio_venta`, `stock`, `fk_categoria`) VALUES
(1, 'Hotwheels', 'Z002X', '2', '3', 5, 61),
(2, 'Cloro', 'Z003X', '2', '2', 2, 62),
(3, 'Pasta', 'Z004X', '2', '4', 7, 60),
(4, 'Ventilador', 'Z005X', '15', '20', 4, 63),
(5, 'Harina', 'Z006X', '1', '3', 11, 60),
(6, 'Azucar', 'Z007X', '2', '2', 3, 60),
(14, 'McFarlane', 'Z008X', '20', '50', 10, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_cliente`
--

DROP TABLE IF EXISTS `tipo_cliente`;
CREATE TABLE IF NOT EXISTS `tipo_cliente` (
  `id_tipo_cliente` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tipo_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_cliente`
--

INSERT INTO `tipo_cliente` (`id_tipo_cliente`, `Tipo`) VALUES
('LL', 'Empresa Privada'),
('XX', 'Persona Natural'),
('ZZ', 'Empresa Publica');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
