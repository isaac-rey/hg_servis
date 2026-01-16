-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-01-2026 a las 02:58:32
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hg_servi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_stock`
--

CREATE TABLE `historial_stock` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `cantidad_minima` int(11) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `lote` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_eliminacion` datetime DEFAULT NULL,
  `usuario_elimino` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_stock`
--

CREATE TABLE `movimientos_stock` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tipo` enum('ENTRADA','SALIDA') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_stock`
--

INSERT INTO `movimientos_stock` (`id`, `producto_id`, `tipo`, `cantidad`, `stock_anterior`, `stock_actual`, `motivo`, `usuario`, `fecha`) VALUES
(18, 11, 'ENTRADA', 5, 2, 7, 'Stock inicial de producto nuevo', 'admin', '2026-01-16 01:20:04'),
(19, 11, 'SALIDA', 1, 6, 5, 'Venta #32', 'isaac', '2026-01-16 01:28:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `calidad` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `imagen` varchar(1000) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `precio` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `modelo`, `marca`, `calidad`, `color`, `imagen`, `categoria`, `precio`, `created_at`, `updated_at`) VALUES
(11, 'ch120', '14 pro', 'Iphone', 'Alta', 'blanco', 'uploads/productos/prod_6963b4cadb01f.jpg', 'electronica', 1000, '2026-01-11 14:33:46', '2026-01-11 14:33:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permisos`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`, `created_at`) VALUES
(1, 'Administrador', 'Acceso total al sistema', NULL, '2025-12-19 22:33:04'),
(2, 'Técnico', 'Puede ver y gestionar reparaciones', NULL, '2025-12-19 22:33:04'),
(3, 'Vendedor', 'Puede gestionar ventas y stock', NULL, '2025-12-19 22:33:04'),
(4, 'Almacenero', 'Solo gestión de inventario', NULL, '2025-12-19 22:33:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `cantidad_minima` int(11) DEFAULT 1,
  `ubicacion` varchar(100) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `lote` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `stock`
--

INSERT INTO `stock` (`id`, `producto_id`, `cantidad`, `cantidad_minima`, `ubicacion`, `proveedor`, `lote`, `fecha_ingreso`) VALUES
(11, 11, 5, 5, 'china', 'china', '001', '2026-01-16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `usuario`, `password`, `rol_id`, `telefono`, `direccion`, `activo`, `ultimo_login`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Sistema', 'admin@reparacion.com', 'admin', 'admin', 1, NULL, NULL, 1, NULL, '2025-12-19 22:33:04', '2025-12-21 17:38:47'),
(2, 'isaac', 'Miranda', 'isaacmiranda@gmail.com', 'isaac_miranda', '$2y$10$PvanUPzRA61gfvXUOkrezux8rpL2SeveejRLS1t7dtvjHChq/57tC', 1, '0976670007', NULL, 1, '2026-01-16 01:30:33', '2025-12-22 21:40:17', '2026-01-16 01:30:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ruc_cliente` varchar(20) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `apellido_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(150) NOT NULL,
  `telefono_cliente` varchar(30) NOT NULL,
  `direccion_entrega` text NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta','transferencia') DEFAULT 'efectivo',
  `condicion_pago` enum('Contado','Credito') DEFAULT 'Contado',
  `subtotal` int(11) NOT NULL,
  `envio` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL,
  `notas` text DEFAULT NULL,
  `estado` enum('pendiente','confirmado','preparando','listo','entregado','cancelado') DEFAULT 'pendiente',
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `usuario_id`, `ruc_cliente`, `nombre_cliente`, `apellido_cliente`, `email_cliente`, `telefono_cliente`, `direccion_entrega`, `metodo_pago`, `condicion_pago`, `subtotal`, `envio`, `total`, `notas`, `estado`, `fecha`) VALUES
(15, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 11000, '', 'pendiente', '2026-01-14 19:48:33'),
(16, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 2000, 0, 12000, '', 'pendiente', '2026-01-14 20:22:26'),
(17, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-14 20:27:10'),
(18, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 11000, '', 'pendiente', '2026-01-14 20:33:59'),
(19, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-14 21:17:59'),
(20, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 101000, 0, 101000, '', 'pendiente', '2026-01-15 07:55:15'),
(21, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 11000, '', 'pendiente', '2026-01-15 21:15:34'),
(22, 2, '4659300', 'samira', 'miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 21:24:13'),
(23, 2, '4659300', 'rey', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 11000, '', 'pendiente', '2026-01-15 21:42:14'),
(24, 2, '4659300', 'isaac', 'gonzalez', 'isaacmiranda@gmail.com', '062145554', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 21:46:25'),
(25, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san ignacio', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 21:48:04'),
(26, 2, '4659300', 'isaac', 'miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 21:56:23'),
(27, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 22:06:13'),
(28, 2, '1234564', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '13565525', 'dfdss', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 22:07:22'),
(29, 2, '1234564', 'isaac', 'gonzalez', 'isaacmiranda@gmail.com', '0976670007', 'san ignacio', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 22:10:56'),
(30, 2, '1234564', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san jan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 22:11:58'),
(31, 2, '4659300', 'isaac', 'Miranda', 'isaacmiranda@gmail.com', '0976670007', 'san jan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 22:20:54'),
(32, 2, '4659300', 'isaac', 'gonzalez', 'isaacmiranda@gmail.com', '0976670007', 'san juan', 'efectivo', 'Contado', 1000, 0, 1000, '', 'pendiente', '2026-01-15 22:28:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `marca` varchar(200) NOT NULL,
  `modelo` varchar(200) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `venta_detalle`
--

INSERT INTO `venta_detalle` (`id`, `venta_id`, `producto_id`, `marca`, `modelo`, `cantidad`, `precio`, `subtotal`) VALUES
(7, 15, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(8, 16, 11, 'Iphone 14 pro', '', 2, 1000, 2000),
(9, 17, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(10, 18, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(11, 19, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(12, 20, 12, 'samsung A01 core', '', 1, 100000, 100000),
(13, 20, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(14, 21, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(15, 22, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(16, 23, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(17, 24, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(18, 25, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(19, 26, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(20, 27, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(21, 28, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(22, 29, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(23, 30, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(24, 31, 11, 'Iphone 14 pro', '', 1, 1000, 1000),
(25, 32, 11, 'Iphone 14 pro', '', 1, 1000, 1000);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `historial_stock`
--
ALTER TABLE `historial_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_stock`
--
ALTER TABLE `historial_stock`
  ADD CONSTRAINT `historial_stock_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  ADD CONSTRAINT `movimientos_stock_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `venta_detalle_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
