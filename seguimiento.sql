-- ==========================================================================
-- SEGUIMIENTO.SQL - Base de Datos para Sistema de Geocercas y Seguimiento
-- Clave del Peluquero Exitoso
-- Fecha: 2026-04-06
-- Descripción: Esquema SQL para gestión de paquetes, ubicaciones GPS,
--              geocercas dinámicas y asignaciones de repartidores.
-- Importar en phpMyAdmin junto con el database.sql principal.
-- ==========================================================================

-- --------------------------------------------------------
-- BASE DE DATOS: Usar la misma BD del proyecto principal
-- --------------------------------------------------------
-- Si no existe la BD, crearla:
-- CREATE DATABASE IF NOT EXISTS clave_peluquero
--   DEFAULT CHARACTER SET utf8mb4
--   COLLATE utf8mb4_general_ci;
-- USE clave_peluquero;

-- --------------------------------------------------------
-- TABLA 1: repartidores
-- Gestiona los repartidores/domiciliarios con GPS activo.
-- Cada repartidor tiene un dispositivo GPS asignado.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `repartidores` (
  `id_repartidor`    INT AUTO_INCREMENT NOT NULL,
  `documento_usua`   VARCHAR(20)  NOT NULL,                 -- FK → usuario.DocumentoUsua
  `nombre_completo`  VARCHAR(200) NOT NULL,                 -- Nombre del repartidor
  `celular`          VARCHAR(20)  NOT NULL,                 -- Teléfono de contacto
  `vehiculo_tipo`    ENUM('moto','bicicleta','carro','a_pie') DEFAULT 'moto',
  `placa_vehiculo`   VARCHAR(15)  DEFAULT NULL,             -- Placa si aplica
  `estado`           ENUM('disponible','en_ruta','inactivo') DEFAULT 'disponible',
  `ultima_lat`       DECIMAL(10,7) DEFAULT NULL,            -- Última latitud GPS
  `ultima_lng`       DECIMAL(10,7) DEFAULT NULL,            -- Última longitud GPS
  `ultima_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_registro`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_repartidor`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_documento` (`documento_usua`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Repartidores/domiciliarios con GPS activo';

-- --------------------------------------------------------
-- TABLA 2: paquetes
-- Registro de cada paquete vendido con detalles del producto,
-- estado de entrega y código de rastreo único.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `paquetes` (
  `id_paquete`       INT AUTO_INCREMENT NOT NULL,
  `codigo_rastreo`   VARCHAR(20)  NOT NULL UNIQUE,          -- Código único tipo "CLV-2026-XXXXX"
  `documento_cliente` VARCHAR(20) NOT NULL,                 -- FK → usuario.DocumentoUsua
  `id_repartidor`    INT DEFAULT NULL,                      -- FK → repartidores.id_repartidor
  `producto_nombre`  VARCHAR(200) NOT NULL,                 -- Nombre del producto
  `producto_detalle` TEXT DEFAULT NULL,                     -- Descripción detallada
  `peso_kg`          DECIMAL(6,2) NOT NULL DEFAULT 0.50,    -- Peso en kilogramos
  `cantidad`         INT NOT NULL DEFAULT 1,                -- Unidades
  `precio_total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,   -- Precio total en COP
  `estado`           ENUM('procesando','preparado','en_transito','en_geocerca','entregado','devuelto')
                     NOT NULL DEFAULT 'procesando',
  `direccion_entrega` VARCHAR(300) NOT NULL,                -- Dirección de destino
  `lat_destino`      DECIMAL(10,7) DEFAULT NULL,            -- Latitud del destino
  `lng_destino`      DECIMAL(10,7) DEFAULT NULL,            -- Longitud del destino
  `fecha_creacion`   DATETIME DEFAULT CURRENT_TIMESTAMP,    -- Cuándo se creó el pedido
  `fecha_despacho`   DATETIME DEFAULT NULL,                 -- Cuándo salió de bodega
  `fecha_entrega`    DATETIME DEFAULT NULL,                 -- Cuándo se entregó
  `fecha_estimada`   DATETIME DEFAULT NULL,                 -- Fecha estimada de entrega
  `notas`            TEXT DEFAULT NULL,                     -- Observaciones
  PRIMARY KEY (`id_paquete`),
  INDEX `idx_codigo` (`codigo_rastreo`),
  INDEX `idx_cliente` (`documento_cliente`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_repartidor` (`id_repartidor`),
  INDEX `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Paquetes vendidos con código de rastreo y estado';

-- --------------------------------------------------------
-- TABLA 3: ubicaciones_paquete
-- Historial de ubicaciones GPS de cada paquete.
-- Cada registro es un "ping" del GPS del repartidor.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ubicaciones_paquete` (
  `id_ubicacion`     INT AUTO_INCREMENT NOT NULL,
  `id_paquete`       INT NOT NULL,                          -- FK → paquetes.id_paquete
  `latitud`          DECIMAL(10,7) NOT NULL,                -- Latitud GPS
  `longitud`         DECIMAL(10,7) NOT NULL,                -- Longitud GPS
  `velocidad_kmh`    DECIMAL(5,2) DEFAULT NULL,             -- Velocidad del repartidor
  `precision_metros` DECIMAL(6,2) DEFAULT NULL,             -- Precisión del GPS
  `descripcion`      VARCHAR(200) DEFAULT NULL,             -- Ej: "Centro de distribución"
  `timestamp_gps`    DATETIME DEFAULT CURRENT_TIMESTAMP,    -- Momento del registro
  PRIMARY KEY (`id_ubicacion`),
  INDEX `idx_paquete` (`id_paquete`),
  INDEX `idx_timestamp` (`timestamp_gps`),
  CONSTRAINT `fk_ubicacion_paquete`
    FOREIGN KEY (`id_paquete`) REFERENCES `paquetes` (`id_paquete`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Historial de ubicaciones GPS por paquete';

-- --------------------------------------------------------
-- TABLA 4: geocercas
-- Define las zonas de agrupamiento dinámico para entregas.
-- Cada geocerca es un círculo con centro y radio adaptativo.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `geocercas` (
  `id_geocerca`      INT AUTO_INCREMENT NOT NULL,
  `nombre`           VARCHAR(100) NOT NULL,                 -- Ej: "Zona Centro Caldas"
  `centro_lat`       DECIMAL(10,7) NOT NULL,                -- Latitud del centro
  `centro_lng`       DECIMAL(10,7) NOT NULL,                -- Longitud del centro
  `radio_km`         DECIMAL(6,3) NOT NULL DEFAULT 1.500,   -- Radio en kilómetros
  `capacidad_max`    INT NOT NULL DEFAULT 15,               -- Máximo de paquetes
  `paquetes_actual`  INT NOT NULL DEFAULT 0,                -- Paquetes actualmente asignados
  `color_hex`        VARCHAR(7) DEFAULT '#d4a853',          -- Color para visualización
  `activa`           BOOLEAN DEFAULT TRUE,                  -- Si la geocerca está activa
  `fecha_creacion`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_geocerca`),
  INDEX `idx_activa` (`activa`),
  INDEX `idx_centro` (`centro_lat`, `centro_lng`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Geocercas dinámicas para agrupamiento de entregas';

-- --------------------------------------------------------
-- TABLA 5: asignaciones_geocerca
-- Relación muchos a muchos entre paquetes y geocercas.
-- Un paquete puede pasar por varias geocercas durante su ruta.
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `asignaciones_geocerca` (
  `id_asignacion`    INT AUTO_INCREMENT NOT NULL,
  `id_paquete`       INT NOT NULL,                          -- FK → paquetes.id_paquete
  `id_geocerca`      INT NOT NULL,                          -- FK → geocercas.id_geocerca
  `id_repartidor`    INT DEFAULT NULL,                      -- FK → repartidores.id_repartidor
  `estado_asignacion` ENUM('pendiente','recogido','entregado') DEFAULT 'pendiente',
  `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_completado` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_asignacion`),
  INDEX `idx_paquete` (`id_paquete`),
  INDEX `idx_geocerca` (`id_geocerca`),
  CONSTRAINT `fk_asig_paquete`
    FOREIGN KEY (`id_paquete`) REFERENCES `paquetes` (`id_paquete`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_asig_geocerca`
    FOREIGN KEY (`id_geocerca`) REFERENCES `geocercas` (`id_geocerca`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Asignaciones de paquetes a geocercas';

-- --------------------------------------------------------
-- DATOS DE EJEMPLO: Geocercas predefinidas para Caldas, Antioquia
-- Centro: Carrera 49 #134 sur-41, Caldas (6.090722, -75.638787)
-- --------------------------------------------------------
INSERT INTO `geocercas` (`nombre`, `centro_lat`, `centro_lng`, `radio_km`, `capacidad_max`, `color_hex`) VALUES
('Zona Centro Caldas',      6.0907220, -75.6387870, 1.200, 20, '#d4a853'),
('Zona Norte - La Valeria',  6.1020000, -75.6350000, 1.500, 15, '#6f42c1'),
('Zona Sur - La Inmaculada', 6.0780000, -75.6420000, 1.000, 12, '#e74c8b'),
('Zona Este - La Planta',   6.0900000, -75.6250000, 0.800, 10, '#28a745'),
('Zona Oeste - El Recreo',  6.0850000, -75.6550000, 1.300, 18, '#17a2b8');

-- --------------------------------------------------------
-- DATOS DE EJEMPLO: Paquetes de prueba
-- --------------------------------------------------------
INSERT INTO `paquetes` (`codigo_rastreo`, `documento_cliente`, `producto_nombre`, `peso_kg`, `precio_total`, `estado`, `direccion_entrega`, `lat_destino`, `lng_destino`, `fecha_estimada`) VALUES
('CLV-2026-00001', '1234567890', 'Shampoo Profesional Keratina 500ml',   0.60, 45000.00, 'en_transito',  'Calle 130 Sur #48-20, Caldas',   6.0920, -75.6400, DATE_ADD(NOW(), INTERVAL 2 DAY)),
('CLV-2026-00002', '1234567890', 'Kit Tinte + Oxidante 90ml',            0.35, 32000.00, 'procesando',   'Carrera 50 #132-15, Caldas',     6.0895, -75.6370, DATE_ADD(NOW(), INTERVAL 3 DAY)),
('CLV-2026-00003', '0987654321', 'Proteína Capilar Reconstructora 250g', 0.30, 55000.00, 'entregado',    'Calle 135 Sur #51-10, Caldas',   6.0950, -75.6350, DATE_ADD(NOW(), INTERVAL -1 DAY)),
('CLV-2026-00004', '1122334455', 'Secador Profesional 2200W',            1.20, 180000.00, 'en_transito', 'Diagonal 134 #49-30, Caldas',    6.0870, -75.6410, DATE_ADD(NOW(), INTERVAL 1 DAY)),
('CLV-2026-00005', '5566778899', 'Plancha de Titanio Pro',               0.80, 95000.00,  'preparado',   'Transversal 48 #136-05, Caldas', 6.0980, -75.6300, DATE_ADD(NOW(), INTERVAL 4 DAY));

-- ==========================================================================
-- FIN DEL ARCHIVO SEGUIMIENTO.SQL
-- ==========================================================================
