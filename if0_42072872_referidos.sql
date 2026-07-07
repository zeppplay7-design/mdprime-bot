-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql101.infinityfree.com
-- Tiempo de generación: 07-07-2026 a las 06:10:55
-- Versión del servidor: 11.4.12-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_42072872_referidos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `contacto` varchar(150) DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `fecha_alta` timestamp NULL DEFAULT current_timestamp(),
  `legacy_id` varchar(80) DEFAULT NULL,
  `telefono` varchar(150) DEFAULT '',
  `telegram` varchar(100) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `contacto`, `nota`, `fecha_alta`, `legacy_id`, `telefono`, `telegram`) VALUES
(1, 'Canelobel', '', '', '2026-06-07 07:00:00', 'cli_6a25eaaad4751', '', 'BELTROL'),
(2, 'juan angel th (signal)', 'signal', '', '2026-06-08 20:58:54', NULL, 'signal', 'Signal'),
(3, 'Juliodelalba', '', '', '2026-06-08 22:08:20', NULL, '', 'Juliodelalba'),
(4, 'CARLOS IBAÑEZ', '', '', '2026-06-09 15:57:24', NULL, '', 'SIGNAL'),
(5, 'RAUL CHINCHON', '', '', '2026-06-09 20:34:27', NULL, '', 'SIGNAL'),
(6, '@bnt309', '', '', '2026-06-09 21:28:51', NULL, '', 'bnt309'),
(8, 'jugargar', 'signal', '', '2026-06-11 22:26:33', NULL, 'signal', 'signal'),
(9, 'XIMO', 'SIGNAL', '', '2026-06-11 22:31:58', NULL, 'SIGNAL', 'SIGNAL'),
(10, 'Victor', 'Signal', '', '2026-06-14 15:49:05', NULL, 'Signal', 'Signal'),
(11, 'RAUL SAINZ', 'SIGNAL', '', '2026-06-19 15:58:14', NULL, 'SIGNAL', 'SIGNAL'),
(12, 'LUCYO', 'SIGNAL', '', '2026-06-19 21:43:10', NULL, 'SIGNAL', 'SIGNAL'),
(13, 'Tonycid32', 'telegram', '', '2026-07-04 11:46:54', NULL, 'telegram', 'telegram'),
(14, 'jesus A.', 'SIGNAL', '', '2026-07-06 18:46:58', NULL, 'SIGNAL', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_niveles`
--

CREATE TABLE `configuracion_niveles` (
  `id` int(11) NOT NULL,
  `nivel` varchar(50) DEFAULT NULL,
  `min_activos` int(11) DEFAULT NULL,
  `trimestral` decimal(10,2) DEFAULT NULL,
  `semestral` decimal(10,2) DEFAULT NULL,
  `anual` decimal(10,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `configuracion_niveles`
--

INSERT INTO `configuracion_niveles` (`id`, `nivel`, `min_activos`, `trimestral`, `semestral`, `anual`) VALUES
(1, 'COBRE', 4, '30.00', '45.00', '65.00'),
(2, 'PLATA', 8, '27.00', '40.00', '58.00'),
(3, 'ORO', 12, '25.00', '37.00', '52.00'),
(4, 'PLATINUM', 20, '22.00', '33.00', '45.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referidos`
--

CREATE TABLE `referidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `fecha_alta` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `estado_manual` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_inactivo` date DEFAULT NULL,
  `auto_inactivo` tinyint(1) DEFAULT 0,
  `legacy_id` varchar(80) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Activo',
  `telegram` varchar(100) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `referidos`
--

INSERT INTO `referidos` (`id`, `cliente_id`, `nombre`, `fecha_alta`, `fecha_caducidad`, `nota`, `estado_manual`, `fecha_inactivo`, `auto_inactivo`, `legacy_id`, `estado`, `telegram`) VALUES
(1, 1, 'Pepelidia', '2026-10-14', '2026-10-14', '', 'Activo', NULL, 0, 'ref_6a25ebe1aa3a9', 'Activo', ''),
(2, 1, 'Josébeltrol', '2026-10-14', '2026-10-14', '', 'Activo', NULL, 0, 'ref_6a25ec21362e8', 'Activo', ''),
(3, 1, 'Brutusdurany', '2026-12-08', '2026-12-08', '', 'Activo', NULL, 0, 'ref_6a25ec5292e30', 'Activo', ''),
(15, 1, 'Brandon10', '2026-06-08', '2026-07-22', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(5, 1, 'Davidpay', '2026-09-24', '2026-09-24', '', 'Activo', NULL, 0, 'ref_6a25eca12f4d3', 'Activo', ''),
(17, 1, 'Payguay10', '2026-06-08', '2026-09-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(18, 1, 'Albert52', '2026-06-08', '2027-01-07', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(8, 1, 'Bombidrac', '2026-10-16', '2026-10-16', '', 'Activo', NULL, 0, 'ref_6a25ed176ecc6', 'Activo', ''),
(9, 1, 'Carolciu77', '2026-11-05', '2026-11-05', '', 'Activo', NULL, 0, 'ref_6a25ed342f078', 'Activo', ''),
(10, 1, 'Ivaneric10', '2026-09-22', '2026-09-22', '', 'Activo', NULL, 0, 'ref_6a25ed6210e42', 'Activo', ''),
(11, 1, 'Selene10', '2026-10-03', '2026-10-03', '', 'Activo', NULL, 0, 'ref_6a25ed9217328', 'Activo', ''),
(19, 1, 'Marc8080', '2026-08-11', '2026-08-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(21, 1, 'Josemiguel1', '2026-06-08', '2026-11-05', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(14, 1, 'Ezequiel1', '2026-06-08', '2026-08-28', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(22, 1, 'Canelobel', '2026-06-08', '2026-10-14', 'AUTO · Cliente referente', 'Activo', NULL, 0, NULL, 'Activo', ''),
(23, 1, 'Poletegala', '2026-06-08', '2026-12-08', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(24, 1, 'Carlosperros', '2026-06-08', '2026-12-08', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(25, 1, 'Josepgala', '2026-06-08', '2026-12-08', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(26, 1, 'Pauako1010', '2026-06-08', '2026-12-08', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(32, 2, 'gS5kJEBBBz', '2026-06-08', '2027-09-04', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(29, 2, 'vFER459YTe', '2026-06-08', '2027-08-07', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(30, 2, '9EJEMtHJnUhM', '2026-06-08', '2027-07-06', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(31, 2, 'kHv7LyhUkz4Y', '2026-06-08', '2027-07-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(33, 3, 'Juliodelalba', '2026-06-08', '2026-07-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(34, 3, 'Cofironallan1', '2026-06-08', '2026-08-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(35, 3, 'Cofironallan2', '2026-06-08', '2026-08-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(36, 3, 'Cofironallan3', '2026-06-08', '2026-08-24', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(37, 3, 'Cofironallan4', '2026-06-08', '2026-09-03', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(38, 3, 'Gonzalosevilla', '2026-06-08', '2026-09-16', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(39, 3, 'Antonvalle', '2026-06-08', '2026-10-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(40, 3, 'JuanMalpa', '2026-06-08', '2026-10-02', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(41, 3, 'JesusCuchara', '2026-06-08', '2026-10-08', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(42, 3, 'Pepecampa', '2026-06-08', '2026-10-19', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(43, 3, 'Josecord1', '2026-06-08', '2026-10-21', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(44, 3, 'Juantosina', '2026-06-08', '2026-10-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(45, 3, 'Julimeno', '2026-06-08', '2026-11-18', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(46, 3, 'Adrianvalle', '2026-06-08', '2026-11-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(47, 3, 'ÁngelCosta', '2026-06-08', '2027-03-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(48, 3, 'JuanMargari', '2026-06-08', '2026-08-14', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(49, 3, 'Sobrinajulio', '2026-06-08', '2026-06-09', '', 'Activo', NULL, 0, NULL, 'Inactivo', ''),
(50, 3, 'Hectorlopez', '2026-06-08', '2026-09-14', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(51, 3, 'SergioPesado', '2026-06-08', '2027-02-24', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(52, 3, '334597088  (JorgePalacete)', '2026-06-08', '2027-08-29', 'ESTE USARIO SE LLAMA JorgePalacete', 'Activo', NULL, 0, NULL, 'Activo', ''),
(53, 3, 'Ainhoavalle', '2026-06-08', '2026-08-12', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(54, 3, 'Pacohorma', '2026-06-08', '2026-08-13', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(58, 4, 'gbn8s7zdJ86A', '2026-06-09', '2027-03-23', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(59, 4, '6R5PmmXhETrR', '2026-06-09', '2027-08-19', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(60, 4, '674434862', '2026-06-09', '2027-06-09', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(61, 3, 'Hermanovito', '2026-06-09', '2026-07-03', '', 'Activo', NULL, 0, NULL, 'Inactivo', ''),
(62, 5, 'ThpSnPHqvu', '2026-06-09', '2027-05-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(63, 5, 'GWrUPsrLwCQH', '2026-06-09', '2027-02-18', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(64, 5, 'QNkjmgMtV2vt', '2026-06-09', '2027-05-20', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(65, 5, 'mYFBu56Ruw', '2026-06-09', '2027-03-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(66, 5, 'jd9Qp6BXYS6e', '2026-06-09', '2026-12-21', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(67, 6, 'Miguelsanc', '2026-06-09', '2026-08-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(68, 6, 'Padrerodrigoarg', '2026-06-09', '2026-07-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(69, 6, 'juanperezrod', '2026-06-09', '2026-08-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(70, 6, 'Rodrigoarg', '2026-06-09', '2026-07-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(71, 6, 'Padrerodrigoarg', '2026-06-09', '2026-07-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(72, 5, 'rMXEjVKGskV7', '2026-06-10', '2027-03-27', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(73, 5, '3zyqjybHza', '2026-06-10', '2026-09-21', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(74, 5, 'eDda3HU9bb', '2026-06-10', '2027-01-12', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(75, 5, 'rn5LpkF5NXt6', '2026-06-10', '2026-11-22', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(76, 5, '594535291', '2026-06-10', '2027-01-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(77, 5, 'aR4epAqhze', '2026-06-10', '2027-04-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(78, 5, 'sRWj5WQXbc', '2026-06-10', '2026-09-21', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(79, 5, '6HqYBv2Xaa', '2026-06-10', '2026-09-29', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(80, 5, '795806082', '2026-06-10', '2027-04-02', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(81, 5, '445197303', '2026-06-10', '2027-04-02', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(82, 5, 'D9dBhduvMH', '2026-06-10', '2027-02-14', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(83, 1, 'Jordiglesias', '2026-06-11', '2026-12-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(84, 8, '454636972', '2026-06-12', '2026-09-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(85, 8, 'ezpQFRXmHg87', '2026-06-12', '2026-10-22', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(86, 8, 'hHFt7h2NUX', '2026-06-12', '2026-09-16', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(87, 8, '666639806', '2026-06-12', '2027-04-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(88, 8, 'fCRdkcdP7E7B', '2026-06-12', '2027-06-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(89, 9, '451633940', '2026-06-12', '2027-04-06', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(90, 9, '715335387', '2026-06-12', '2026-11-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(91, 9, '527854381', '2026-06-12', '2026-11-13', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(92, 9, 'y6rZvuS5z3fM', '2026-06-12', '2026-12-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(93, 9, '58tsYQr5eU5m', '2026-06-12', '2026-10-28', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(94, 9, 'XEDd8uXdpS', '2026-06-12', '2027-04-02', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(95, 9, 'GEX4vv4KBK', '2026-06-12', '2026-07-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(96, 9, '686183480', '2026-06-12', '2026-11-26', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(97, 9, 'BUw7Gjb73A', '2026-06-12', '2026-11-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(98, 9, '289291262', '2026-06-12', '2026-11-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(99, 9, '587253805', '2026-06-12', '2026-10-24', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(100, 9, 'E828ja2Pc5', '2026-06-12', '2026-09-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(101, 9, '225Tu8P5D7Pg', '2026-06-12', '2026-09-12', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(102, 9, '421669698', '2026-06-12', '2026-08-19', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(103, 9, 'Chnmnmj5JenG', '2026-06-12', '2026-08-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(104, 9, 'whxTxVkEBh', '2026-06-12', '2026-07-03', '', 'Activo', NULL, 0, NULL, 'Inactivo', ''),
(105, 5, '375339107', '2026-06-12', '2027-06-12', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(106, 5, '711747248', '2026-06-12', '2027-06-12', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(107, 9, '507603555', '2026-06-13', '2027-06-13', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(108, 10, 'NeW5ULq3mYqz', '2026-06-14', '2027-06-13', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(109, 10, '280949248', '2026-06-14', '2027-03-16', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(110, 10, 'aZwPHNHxHq', '2026-06-14', '2027-11-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(111, 10, 'q3TRB3298g', '2026-06-14', '2026-08-29', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(112, 10, 'RE2k7bjS9pTQ', '2026-06-14', '2026-09-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(113, 10, 'bPHmKUg2rJ', '2026-06-14', '2026-10-03', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(114, 10, 'uKrvxqdySArw', '2026-06-14', '2026-08-02', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(115, 10, '420935805', '2026-06-14', '2027-06-14', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(116, 5, '099141646', '2026-06-15', '2027-06-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(117, 5, '895073470', '2026-06-15', '2027-06-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(118, 1, 'jessica10', '2026-06-16', '2026-12-16', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(119, 5, '669694630', '2026-06-17', '2027-06-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(120, 5, '075351103', '2026-06-17', '2027-06-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(121, 11, '028806866', '2026-06-19', '2026-09-18', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(122, 11, '852090682', '2026-06-19', '2027-04-18', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(123, 11, '953671520', '2026-06-19', '2027-03-20', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(124, 11, '198763446', '2026-06-19', '2026-10-04', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(125, 11, '198270293', '2026-06-19', '2026-10-04', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(126, 11, 'u685gzH9Cb', '2026-06-19', '2027-02-03', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(127, 11, 'PX4ez7fYdC', '2026-06-19', '2026-12-14', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(128, 11, '166776604', '2026-06-19', '2026-12-27', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(129, 11, '570744996', '2026-06-19', '2026-12-21', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(130, 11, 'u685gzH9Cb', '2026-06-19', '2027-02-03', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(131, 12, 'qQ2BNFct9W', '2026-06-19', '2026-09-18', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(132, 12, 'DnVrFNS8gs', '2026-06-19', '2026-12-23', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(133, 12, 'NzYzWbQbqM', '2026-06-19', '2026-11-26', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(134, 12, 'YyGB5Ucs7saA', '2026-06-19', '2026-09-09', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(135, 12, 'XcwSmNmkRSNk', '2026-06-19', '2026-09-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(136, 12, 'XcwSmNmkRSNk', '2026-06-19', '2026-09-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(137, 12, '2rpaNJJ9rwYb', '2026-06-19', '2026-09-11', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(138, 12, 'hakrpFtXTSFM', '2026-06-19', '2026-09-09', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(139, 12, 'akvcDRuWwVKG', '2026-06-19', '2026-09-09', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(140, 12, '840993817', '2026-06-19', '2027-06-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(141, 12, '871408562', '2026-06-19', '2027-06-15', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(142, 12, '372002040', '2026-06-19', '2026-10-18', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(143, 12, '726768793', '2026-06-19', '2026-08-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(144, 12, '724644713', '2026-06-19', '2026-08-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(145, 12, '344870477', '2026-06-19', '2026-09-09', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(146, 12, '975838386', '2026-06-19', '2027-05-29', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(147, 11, '186991740', '2026-06-20', '2027-06-20', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(148, 1, 'Tonibruna10', '2026-06-25', '2026-12-25', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(149, 13, 'Ivancuinas', '2026-07-04', '2026-08-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(150, 13, 'Josecuinas', '2026-07-04', '2026-08-01', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(151, 13, 'Diegosalgado', '2026-07-04', '2026-08-12', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(152, 13, 'Ferfreire', '2026-07-04', '2026-09-02', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(153, 13, 'Joseluisalv', '2026-07-04', '2026-07-06', '', 'Activo', NULL, 0, NULL, 'Inactivo', ''),
(154, 13, 'Danielrey', '2026-07-04', '2026-08-19', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(155, 13, 'Josemanuelmota', '2026-07-04', '2026-10-10', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(156, 13, 'Javiercarabel', '2026-07-04', '2027-04-22', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(157, 13, 'Borjaciid', '2026-07-04', '2027-01-08', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(158, 13, 'Luisalvarez', '2026-07-04', '2026-07-06', '', 'Activo', NULL, 0, NULL, 'Inactivo', ''),
(159, 13, 'Manuelserta', '2026-07-05', '2026-09-26', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(160, 13, 'Luisalvarez', '2026-07-06', '2027-07-06', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(161, 13, 'Joseluisalv', '2026-07-06', '2027-07-06', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(162, 14, 'EQAG4ZTpjdq4', '2026-07-06', '2027-04-17', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(163, 14, '210144012', '2026-07-06', '2027-04-22', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(164, 14, '834176195', '2026-07-06', '2026-07-20', '', 'Activo', NULL, 0, NULL, 'Activo', ''),
(165, 14, 'vCY5SZ2EYqzK', '2026-07-06', '2026-07-21', '', 'Activo', NULL, 0, NULL, 'Activo', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_id` (`legacy_id`);

--
-- Indices de la tabla `configuracion_niveles`
--
ALTER TABLE `configuracion_niveles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `referidos`
--
ALTER TABLE `referidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_id` (`legacy_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `configuracion_niveles`
--
ALTER TABLE `configuracion_niveles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `referidos`
--
ALTER TABLE `referidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
