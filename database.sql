-- --------------------------------------------------------
-- Script de Base de Datos para "Clave del Peluquero"
-- Importar este archivo en phpMyAdmin para crear las tablas
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
-- Asegura los datos del cliente, profesionales o administradores.
--

CREATE TABLE IF NOT EXISTS `usuario` (
  `DocumentoUsua` varchar(20) NOT NULL,
  `TipoDocumento` varchar(50) NOT NULL,
  `Rol` varchar(50) NOT NULL DEFAULT 'Cliente',
  `Genero` varchar(30) NOT NULL,
  `NombreUsuario` varchar(100) NOT NULL,
  `ApellidoUsuario` varchar(100) NOT NULL,
  `FechaNacUsua` date NOT NULL,
  `CelUsua` varchar(20) NOT NULL,
  `CorreoUsua` varchar(100) NOT NULL,
  PRIMARY KEY (`DocumentoUsua`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariologin`
-- Administra las contraseñas hasheadas y bloqueos por intentos fallidos.
--

CREATE TABLE IF NOT EXISTS `usuariologin` (
  `DocumentoUsua` varchar(20) NOT NULL,
  `ClaveUsuario` varchar(255) NOT NULL,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  PRIMARY KEY (`DocumentoUsua`),
  CONSTRAINT `fk_usuariologin_usuario` FOREIGN KEY (`DocumentoUsua`) REFERENCES `usuario` (`DocumentoUsua`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
