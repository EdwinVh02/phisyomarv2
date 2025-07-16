/*
SQLyog Community v13.1.6 (64 bit)
MySQL - 10.4.32-MariaDB : Database - phisyomar_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`phisyomar_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `phisyomar_db`;

/*Table structure for table `adjuntos` */

DROP TABLE IF EXISTS `adjuntos`;

CREATE TABLE `adjuntos` (
  `id ` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entidad` varchar(50) NOT NULL,
  `entidad_id` bigint(20) unsigned NOT NULL,
  `nombre_archivo` varchar(200) NOT NULL,
  `url` varchar(250) NOT NULL,
  `fecha_subida` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `adjuntos` */

/*Table structure for table `administradors` */

DROP TABLE IF EXISTS `administradors`;

CREATE TABLE `administradors` (
  `id` bigint(20) unsigned NOT NULL,
  `cedula_profesional` varchar(30) DEFAULT NULL,
  `clinica_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `administradors_clinica_id_foreign` (`clinica_id`),
  CONSTRAINT `administradors_clinica_id_foreign` FOREIGN KEY (`clinica_id`) REFERENCES `clinicas` (`id`),
  CONSTRAINT `administradors_id_foreign` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `administradors` */

insert  into `administradors`(`id`,`cedula_profesional`,`clinica_id`) values 
(1,'asdsadsa',1);

/*Table structure for table `atiendes` */

DROP TABLE IF EXISTS `atiendes`;

CREATE TABLE `atiendes` (
  `terapeuta_id` bigint(20) unsigned NOT NULL,
  `cita_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`terapeuta_id`,`cita_id`),
  KEY `atiendes_cita_id_foreign` (`cita_id`),
  CONSTRAINT `atiendes_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`),
  CONSTRAINT `atiendes_terapeuta_id_foreign` FOREIGN KEY (`terapeuta_id`) REFERENCES `terapeutas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `atiendes` */

/*Table structure for table `bitacoras` */

DROP TABLE IF EXISTS `bitacoras`;

CREATE TABLE `bitacoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `registro_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `detalle` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bitacoras_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `bitacoras_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `bitacoras` */

/*Table structure for table `cache` */

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache` */

/*Table structure for table `cache_locks` */

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache_locks` */

/*Table structure for table `citas` */

DROP TABLE IF EXISTS `citas`;

CREATE TABLE `citas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `duracion` int(11) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `equipo_asignado` varchar(100) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `estado` enum('agendada','atendida','cancelada','no_asistio','reprogramada') NOT NULL,
  `paciente_id` bigint(20) unsigned NOT NULL,
  `terapeuta_id` bigint(20) unsigned DEFAULT NULL,
  `registro_id` bigint(20) unsigned DEFAULT NULL,
  `paquete_paciente_id` bigint(20) unsigned DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `escala_dolor_eva_inicio` int(11) DEFAULT NULL,
  `escala_dolor_eva_fin` int(11) DEFAULT NULL,
  `como_fue_lesion` text DEFAULT NULL,
  `antecedentes_patologicos` text DEFAULT NULL,
  `antecedentes_no_patologicos` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `citas_paciente_id_foreign` (`paciente_id`),
  KEY `citas_terapeuta_id_foreign` (`terapeuta_id`),
  KEY `citas_registro_id_foreign` (`registro_id`),
  KEY `citas_paquete_paciente_id_foreign` (`paquete_paciente_id`),
  KEY `citas_fecha_hora_index` (`fecha_hora`),
  CONSTRAINT `citas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `citas_paquete_paciente_id_foreign` FOREIGN KEY (`paquete_paciente_id`) REFERENCES `paquete_pacientes` (`id`),
  CONSTRAINT `citas_registro_id_foreign` FOREIGN KEY (`registro_id`) REFERENCES `registros` (`id`),
  CONSTRAINT `citas_terapeuta_id_foreign` FOREIGN KEY (`terapeuta_id`) REFERENCES `terapeutas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `citas` */

/*Table structure for table `clinicas` */

DROP TABLE IF EXISTS `clinicas`;

CREATE TABLE `clinicas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  `razon_social` varchar(150) NOT NULL,
  `rfc` varchar(20) NOT NULL,
  `no_licencia_sanitaria` varchar(50) DEFAULT NULL,
  `no_registro_patronal` varchar(50) DEFAULT NULL,
  `no_aviso_de_funcionamiento` varchar(50) DEFAULT NULL,
  `colores_corporativos` varchar(50) DEFAULT NULL,
  `logo_url` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `clinicas` */

insert  into `clinicas`(`id`,`nombre`,`direccion`,`razon_social`,`rfc`,`no_licencia_sanitaria`,`no_registro_patronal`,`no_aviso_de_funcionamiento`,`colores_corporativos`,`logo_url`,`created_at`,`updated_at`) values 
(1,'PhysioMar','Av. Salud 123','Clinica PhysioMar SA de CV','PMR123456789','LIC123456','REG987654','AVF456789','#00BFFF','http://logo.com/logo.png',NULL,NULL);

/*Table structure for table `consentimiento_informados` */

DROP TABLE IF EXISTS `consentimiento_informados`;

CREATE TABLE `consentimiento_informados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint(20) unsigned NOT NULL,
  `fecha_firma` date NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `documento_url` varchar(250) DEFAULT NULL,
  `firmado_por` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `consentimiento_informados_paciente_id_foreign` (`paciente_id`),
  CONSTRAINT `consentimiento_informados_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `consentimiento_informados` */

/*Table structure for table `defines` */

DROP TABLE IF EXISTS `defines`;

CREATE TABLE `defines` (
  `padecimiento_id` bigint(20) unsigned NOT NULL,
  `tratamiento_id` bigint(20) unsigned NOT NULL,
  `administrador_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`padecimiento_id`,`tratamiento_id`),
  KEY `defines_tratamiento_id_foreign` (`tratamiento_id`),
  KEY `defines_administrador_id_foreign` (`administrador_id`),
  CONSTRAINT `defines_administrador_id_foreign` FOREIGN KEY (`administrador_id`) REFERENCES `administradors` (`id`),
  CONSTRAINT `defines_padecimiento_id_foreign` FOREIGN KEY (`padecimiento_id`) REFERENCES `padecimientos` (`id`),
  CONSTRAINT `defines_tratamiento_id_foreign` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `defines` */

/*Table structure for table `encuestas` */

DROP TABLE IF EXISTS `encuestas`;

CREATE TABLE `encuestas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `recepcionista_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` enum('satisfaccion','dolor','otro') NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `encuestas_recepcionista_id_foreign` (`recepcionista_id`),
  CONSTRAINT `encuestas_recepcionista_id_foreign` FOREIGN KEY (`recepcionista_id`) REFERENCES `recepcionistas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `encuestas` */

/*Table structure for table `especialidads` */

DROP TABLE IF EXISTS `especialidads`;

CREATE TABLE `especialidads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `especialidads_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `especialidads` */

insert  into `especialidads`(`id`,`nombre`) values 
(1,'Fisioterapia deportiva'),
(3,'Fisioterapia ortopédica'),
(2,'Rehabilitación neurológica');

/*Table structure for table `experiencia_terapeutas` */

DROP TABLE IF EXISTS `experiencia_terapeutas`;

CREATE TABLE `experiencia_terapeutas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `terapeuta_id` bigint(20) unsigned NOT NULL,
  `tipo` enum('exito','fallo','otro') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `experiencia_terapeutas_terapeuta_id_foreign` (`terapeuta_id`),
  CONSTRAINT `experiencia_terapeutas_terapeuta_id_foreign` FOREIGN KEY (`terapeuta_id`) REFERENCES `terapeutas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `experiencia_terapeutas` */

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `historial_medicos` */

DROP TABLE IF EXISTS `historial_medicos`;

CREATE TABLE `historial_medicos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint(20) unsigned NOT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `observacion_general` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `historial_medicos_paciente_id_index` (`paciente_id`),
  CONSTRAINT `historial_medicos_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `historial_medicos` */

/*Table structure for table `job_batches` */

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `job_batches` */

/*Table structure for table `jobs` */

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `jobs` */

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2025_07_10_161729_create_rols_table',1),
(5,'2025_07_10_161729_create_usuarios_table',1),
(6,'2025_07_10_161730_create_especialidads_table',1),
(7,'2025_07_10_161730_create_pacientes_table',1),
(8,'2025_07_10_161730_create_terapeutas_table',1),
(9,'2025_07_10_161731_create_clinicas_table',1),
(10,'2025_07_10_161731_create_experiencia_terapeutas_table',1),
(11,'2025_07_10_161731_create_recepcionistas_table',1),
(12,'2025_07_10_161731_create_terapeuta_especialidads_table',1),
(13,'2025_07_10_161732_create_administradors_table',1),
(14,'2025_07_10_161732_create_padecimientos_table',1),
(15,'2025_07_10_161733_create_paquete_sesions_table',1),
(16,'2025_07_10_161733_create_tarifas_table',1),
(17,'2025_07_10_161733_create_tratamientos_table',1),
(18,'2025_07_10_161734_create_consentimiento_informados_table',1),
(19,'2025_07_10_161734_create_defines_table',1),
(20,'2025_07_10_161734_create_historial_medicos_table',1),
(21,'2025_07_10_161734_create_paquete_pacientes_table',1),
(22,'2025_07_10_161734_create_registros_table',1),
(23,'2025_07_10_161735_create_citas_table',1),
(24,'2025_07_10_161736_create_atiendes_table',1),
(25,'2025_07_10_161736_create_pagos_table',1),
(26,'2025_07_10_161737_create_adjuntos_table',1),
(27,'2025_07_10_161737_create_tarjetas_table',1),
(28,'2025_07_10_161737_create_valoracions_table',1),
(29,'2025_07_10_161738_create_encuestas_table',1),
(30,'2025_07_10_161738_create_preguntas_table',1),
(31,'2025_07_10_161738_create_smartwatches_table',1),
(32,'2025_07_10_161739_create_bitacoras_table',1),
(33,'2025_07_10_161739_create_respuestas_table',1),
(34,'2025_07_11_063041_create_personal_access_tokens_table',1);

/*Table structure for table `pacientes` */

DROP TABLE IF EXISTS `pacientes`;

CREATE TABLE `pacientes` (
  `id` bigint(20) unsigned NOT NULL,
  `contacto_emergencia_nombre` varchar(100) DEFAULT NULL,
  `contacto_emergencia_telefono` varchar(20) DEFAULT NULL,
  `contacto_emergencia_parentesco` varchar(50) DEFAULT NULL,
  `tutor_nombre` varchar(100) DEFAULT NULL,
  `tutor_telefono` varchar(20) DEFAULT NULL,
  `tutor_parentesco` varchar(50) DEFAULT NULL,
  `tutor_direccion` varchar(150) DEFAULT NULL,
  `historial_medico_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pacientes_historial_medico_id_index` (`historial_medico_id`),
  CONSTRAINT `pacientes_id_foreign` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `pacientes` */

/*Table structure for table `padecimientos` */

DROP TABLE IF EXISTS `padecimientos`;

CREATE TABLE `padecimientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `sintomas` text DEFAULT NULL,
  `clasificacion` varchar(100) DEFAULT NULL,
  `nivel_gravedad` varchar(30) DEFAULT NULL,
  `codigo_cie10` varchar(20) DEFAULT NULL,
  `origen` varchar(100) DEFAULT NULL,
  `estudios_imagen` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `padecimientos` */

/*Table structure for table `pagos` */

DROP TABLE IF EXISTS `pagos`;

CREATE TABLE `pagos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `monto` decimal(8,2) NOT NULL,
  `forma_pago` enum('efectivo','transferencia','terminal') NOT NULL,
  `recibo` varchar(100) DEFAULT NULL,
  `cita_id` bigint(20) unsigned DEFAULT NULL,
  `paquete_paciente_id` bigint(20) unsigned DEFAULT NULL,
  `autorizacion` varchar(100) DEFAULT NULL,
  `factura_emitida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_cita_id_foreign` (`cita_id`),
  KEY `pagos_paquete_paciente_id_foreign` (`paquete_paciente_id`),
  KEY `pagos_fecha_hora_index` (`fecha_hora`),
  CONSTRAINT `pagos_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`),
  CONSTRAINT `pagos_paquete_paciente_id_foreign` FOREIGN KEY (`paquete_paciente_id`) REFERENCES `paquete_pacientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `pagos` */

/*Table structure for table `paquete_pacientes` */

DROP TABLE IF EXISTS `paquete_pacientes`;

CREATE TABLE `paquete_pacientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint(20) unsigned NOT NULL,
  `paquete_sesion_id` bigint(20) unsigned NOT NULL,
  `fecha_compra` date NOT NULL,
  `sesiones_usadas` int(11) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `paquete_pacientes_paciente_id_foreign` (`paciente_id`),
  KEY `paquete_pacientes_paquete_sesion_id_foreign` (`paquete_sesion_id`),
  CONSTRAINT `paquete_pacientes_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `paquete_pacientes_paquete_sesion_id_foreign` FOREIGN KEY (`paquete_sesion_id`) REFERENCES `paquete_sesions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `paquete_pacientes` */

/*Table structure for table `paquete_sesions` */

DROP TABLE IF EXISTS `paquete_sesions`;

CREATE TABLE `paquete_sesions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `numero_sesiones` int(11) NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `tipo_terapia` varchar(100) DEFAULT NULL,
  `especifico_enfermedad` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `paquete_sesions` */

insert  into `paquete_sesions`(`id`,`nombre`,`numero_sesiones`,`precio`,`tipo_terapia`,`especifico_enfermedad`,`fecha_creacion`) values 
(1,'Paquete Básico',5,1500.00,'General','N/A','2025-07-11 15:16:46');

/*Table structure for table `password_reset_tokens` */

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `password_reset_tokens` */

/*Table structure for table `personal_access_tokens` */

DROP TABLE IF EXISTS `personal_access_tokens`;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `personal_access_tokens` */

/*Table structure for table `preguntas` */

DROP TABLE IF EXISTS `preguntas`;

CREATE TABLE `preguntas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `texto` text NOT NULL,
  `encuesta_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `preguntas_encuesta_id_foreign` (`encuesta_id`),
  CONSTRAINT `preguntas_encuesta_id_foreign` FOREIGN KEY (`encuesta_id`) REFERENCES `encuestas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `preguntas` */

/*Table structure for table `recepcionistas` */

DROP TABLE IF EXISTS `recepcionistas`;

CREATE TABLE `recepcionistas` (
  `id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `recepcionistas_id_foreign` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `recepcionistas` */

/*Table structure for table `registros` */

DROP TABLE IF EXISTS `registros`;

CREATE TABLE `registros` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `historial_medico_id` bigint(20) unsigned NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `antecedentes` text DEFAULT NULL,
  `medicacion_actual` text DEFAULT NULL,
  `postura` text DEFAULT NULL,
  `marcha` text DEFAULT NULL,
  `fuerza_muscular` text DEFAULT NULL,
  `rango_movimiento_muscular_rom` text DEFAULT NULL,
  `tono_muscular` text DEFAULT NULL,
  `localizacion_dolor` text DEFAULT NULL,
  `intensidad_dolor` int(11) DEFAULT NULL,
  `tipo_dolor` varchar(100) DEFAULT NULL,
  `movilidad_articular` text DEFAULT NULL,
  `balance_y_coordinacion` text DEFAULT NULL,
  `sensibilidad` text DEFAULT NULL,
  `reflejos_osteotendinosos` text DEFAULT NULL,
  `motivo_visita` text DEFAULT NULL,
  `numero_sesion` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `registros_historial_medico_id_index` (`historial_medico_id`),
  CONSTRAINT `registros_historial_medico_id_foreign` FOREIGN KEY (`historial_medico_id`) REFERENCES `historial_medicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `registros` */

/*Table structure for table `respuestas` */

DROP TABLE IF EXISTS `respuestas`;

CREATE TABLE `respuestas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `texto` text NOT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `pregunta_id` bigint(20) unsigned DEFAULT NULL,
  `paciente_id` bigint(20) unsigned DEFAULT NULL,
  `cita_id` bigint(20) unsigned DEFAULT NULL,
  `tratamiento_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_respuesta` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `respuestas_pregunta_id_foreign` (`pregunta_id`),
  KEY `respuestas_paciente_id_foreign` (`paciente_id`),
  KEY `respuestas_cita_id_foreign` (`cita_id`),
  KEY `respuestas_tratamiento_id_foreign` (`tratamiento_id`),
  CONSTRAINT `respuestas_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`),
  CONSTRAINT `respuestas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `respuestas_pregunta_id_foreign` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`),
  CONSTRAINT `respuestas_tratamiento_id_foreign` FOREIGN KEY (`tratamiento_id`) REFERENCES `tratamientos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `respuestas` */

/*Table structure for table `rols` */

DROP TABLE IF EXISTS `rols`;

CREATE TABLE `rols` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `rols` */

insert  into `rols`(`id`,`name`) values 
(1,'Administrador'),
(2,'Terapeuta'),
(3,'Recepcionista'),
(4,'Paciente');

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `sessions` */

/*Table structure for table `smartwatches` */

DROP TABLE IF EXISTS `smartwatches`;

CREATE TABLE `smartwatches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `valoracion_id` bigint(20) unsigned DEFAULT NULL,
  `paciente_id` bigint(20) unsigned DEFAULT NULL,
  `datos` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `smartwatches_valoracion_id_foreign` (`valoracion_id`),
  KEY `smartwatches_paciente_id_foreign` (`paciente_id`),
  CONSTRAINT `smartwatches_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `smartwatches_valoracion_id_foreign` FOREIGN KEY (`valoracion_id`) REFERENCES `valoracions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `smartwatches` */

/*Table structure for table `tarifas` */

DROP TABLE IF EXISTS `tarifas`;

CREATE TABLE `tarifas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `tipo` enum('General','Reducida','Especializada') NOT NULL,
  `condiciones` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tarifas` */

/*Table structure for table `tarjetas` */

DROP TABLE IF EXISTS `tarjetas`;

CREATE TABLE `tarjetas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(16) NOT NULL,
  `titular` varchar(100) NOT NULL,
  `banco` varchar(50) DEFAULT NULL,
  `cvv` varchar(4) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `pago_id` bigint(20) unsigned NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tarjetas_pago_id_foreign` (`pago_id`),
  CONSTRAINT `tarjetas_pago_id_foreign` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tarjetas` */

/*Table structure for table `terapeuta_especialidads` */

DROP TABLE IF EXISTS `terapeuta_especialidads`;

CREATE TABLE `terapeuta_especialidads` (
  `terapeuta_id` bigint(20) unsigned NOT NULL,
  `especialidad_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`terapeuta_id`,`especialidad_id`),
  KEY `terapeuta_especialidads_especialidad_id_foreign` (`especialidad_id`),
  CONSTRAINT `terapeuta_especialidads_especialidad_id_foreign` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidads` (`id`),
  CONSTRAINT `terapeuta_especialidads_terapeuta_id_foreign` FOREIGN KEY (`terapeuta_id`) REFERENCES `terapeutas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `terapeuta_especialidads` */

/*Table structure for table `terapeutas` */

DROP TABLE IF EXISTS `terapeutas`;

CREATE TABLE `terapeutas` (
  `id` bigint(20) unsigned NOT NULL,
  `cedula_profesional` varchar(30) DEFAULT NULL,
  `especialidad_principal` varchar(100) DEFAULT NULL,
  `experiencia_anios` int(11) DEFAULT NULL,
  `estatus` enum('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  CONSTRAINT `terapeutas_id_foreign` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `terapeutas` */

insert  into `terapeutas`(`id`,`cedula_profesional`,`especialidad_principal`,`experiencia_anios`,`estatus`) values 
(1,'CED123456','Rehabilitación',5,'activo');

/*Table structure for table `tratamientos` */

DROP TABLE IF EXISTS `tratamientos`;

CREATE TABLE `tratamientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion` int(11) DEFAULT NULL,
  `frecuencia` varchar(50) DEFAULT NULL,
  `requisitos` text DEFAULT NULL,
  `padecimiento_id` bigint(20) unsigned DEFAULT NULL,
  `tarifa_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tratamientos_padecimiento_id_foreign` (`padecimiento_id`),
  KEY `tratamientos_tarifa_id_foreign` (`tarifa_id`),
  CONSTRAINT `tratamientos_padecimiento_id_foreign` FOREIGN KEY (`padecimiento_id`) REFERENCES `padecimientos` (`id`),
  CONSTRAINT `tratamientos_tarifa_id_foreign` FOREIGN KEY (`tarifa_id`) REFERENCES `tarifas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `tratamientos` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) NOT NULL,
  `apellido_materno` varchar(50) NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `contraseña` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` enum('Masculino','Femenino','Otro') NOT NULL,
  `curp` varchar(18) NOT NULL,
  `ocupacion` varchar(50) DEFAULT NULL,
  `estatus` enum('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
  `rol_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_correo_electronico_unique` (`correo_electronico`),
  UNIQUE KEY `usuarios_curp_unique` (`curp`),
  KEY `usuarios_rol_id_foreign` (`rol_id`),
  KEY `usuarios_telefono_index` (`telefono`),
  KEY `usuarios_curp_index` (`curp`),
  CONSTRAINT `usuarios_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `rols` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `usuarios` */

insert  into `usuarios`(`id`,`nombre`,`apellido_paterno`,`apellido_materno`,`correo_electronico`,`contraseña`,`telefono`,`direccion`,`fecha_nacimiento`,`sexo`,`curp`,`ocupacion`,`estatus`,`rol_id`,`created_at`,`updated_at`) values 
(1,'Ana','Gomez','Ramos','ana@example.com','hashed123','5551234567','Calle Falsa 123','1990-01-01','Femenino','GORA900101MDFRMN01','Doctora','activo',4,NULL,NULL),
(2,'Edwin','Vazquez','Hernandez','edwin@gmail.com','$2y$12$O8q715k1XPJ4Zf0wUG1aFeGQ.CiPsCiBsVThYP3xVqPvo2Qzg33Ji','5561234567',NULL,'1993-11-05','Masculino','FEOC931105HDFNRSAA',NULL,'activo',4,'2025-07-12 03:34:07','2025-07-12 03:34:07');

/*Table structure for table `valoracions` */

DROP TABLE IF EXISTS `valoracions`;

CREATE TABLE `valoracions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `puntuacion` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `paciente_id` bigint(20) unsigned NOT NULL,
  `terapeuta_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `valoracions_paciente_id_foreign` (`paciente_id`),
  KEY `valoracions_terapeuta_id_foreign` (`terapeuta_id`),
  CONSTRAINT `valoracions_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `valoracions_terapeuta_id_foreign` FOREIGN KEY (`terapeuta_id`) REFERENCES `terapeutas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `valoracions` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
