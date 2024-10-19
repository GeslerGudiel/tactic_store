-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: emprendedores_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administrador`
--

DROP TABLE IF EXISTS `administrador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrador` (
  `id_administrador` int(11) NOT NULL AUTO_INCREMENT,
  `nombre1` varchar(100) NOT NULL,
  `nombre2` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) NOT NULL,
  `telefono1` varchar(100) NOT NULL,
  `telefono2` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` varchar(50) DEFAULT 'administrador',
  PRIMARY KEY (`id_administrador`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrador`
--

LOCK TABLES `administrador` WRITE;
/*!40000 ALTER TABLE `administrador` DISABLE KEYS */;
INSERT INTO `administrador` VALUES (2,'Admin','','Principal','','','','ggmq20@gmail.com','$2y$10$U6sV6HVmSjqr.DTk856S/OsBvnjMXWkPI2jplK9oXmCDqltanIEJK','2024-09-09 21:10:31','superadmin');
/*!40000 ALTER TABLE `administrador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banco`
--

DROP TABLE IF EXISTS `banco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banco` (
  `id_banco` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_banco` varchar(255) NOT NULL,
  PRIMARY KEY (`id_banco`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banco`
--

LOCK TABLES `banco` WRITE;
/*!40000 ALTER TABLE `banco` DISABLE KEYS */;
INSERT INTO `banco` VALUES (1,'Banrural'),(2,'Bam'),(3,'Industrial'),(4,'Bantrab');
/*!40000 ALTER TABLE `banco` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(255) NOT NULL,
  `descripcion_categoria` text DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (1,'Computación','Accesorios dedicados a la computación.'),(2,'Hogar','Utensilios para el hogar.'),(3,'Útiles Escolares','Todo tipo de materiales para el estudiante.'),(5,'Mascotas','Todo lo relacionado a mascotas.');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `NIT` varchar(20) DEFAULT NULL,
  `nombre1` varchar(255) NOT NULL,
  `nombre2` varchar(255) DEFAULT NULL,
  `nombre3` varchar(255) DEFAULT NULL,
  `apellido1` varchar(255) NOT NULL,
  `apellido2` varchar(255) DEFAULT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono1` varchar(20) DEFAULT NULL,
  `telefono2` varchar(20) DEFAULT NULL,
  `id_direccion` int(11) DEFAULT NULL,
  `id_estado_usuario` int(11) DEFAULT NULL,
  `token_activacion` varchar(255) DEFAULT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cliente`),
  KEY `id_direccion` (`id_direccion`),
  KEY `id_estado_usuario` (`id_estado_usuario`),
  CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`),
  CONSTRAINT `cliente_ibfk_2` FOREIGN KEY (`id_estado_usuario`) REFERENCES `estado_usuario` (`id_estado_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,'123','Gudiel','Gesler','','Mó','Quej','geslermo@vida.edu.gt','$2y$10$QLm/uGTqPepBB8ebwnomcOUz0/lAgAirnggXxDacMPd3RXVBDPrle','123','123',3,4,'5b4614878918b89866d73a27622eb235f0ba84f0b74da2b667793878cf237b57b47a86c608925d4f7de3c48a7e40050b7b44',NULL,'2024-08-29 04:45:43');
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentario`
--

DROP TABLE IF EXISTS `comentario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comentario` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `calificacion` int(11) NOT NULL,
  `fecha_comentario` datetime DEFAULT current_timestamp(),
  `respuesta` text DEFAULT NULL,
  PRIMARY KEY (`id_comentario`),
  KEY `id_producto` (`id_producto`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `comentario_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE,
  CONSTRAINT `comentario_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentario`
--

LOCK TABLES `comentario` WRITE;
/*!40000 ALTER TABLE `comentario` DISABLE KEYS */;
INSERT INTO `comentario` VALUES (2,14,1,'',4,'2024-09-03 09:33:23','Muchas gracias.');
/*!40000 ALTER TABLE `comentario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comision`
--

DROP TABLE IF EXISTS `comision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comision` (
  `id_comision` int(11) NOT NULL AUTO_INCREMENT,
  `id_emprendedor` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `monto_comision` decimal(10,2) NOT NULL,
  `estado_comision` enum('Pendiente','Pagada') NOT NULL DEFAULT 'Pendiente',
  `fecha_comision` datetime NOT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `comprobante_pago` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_comision`),
  KEY `id_emprendedor` (`id_emprendedor`),
  KEY `id_pedido` (`id_pedido`),
  CONSTRAINT `comision_ibfk_1` FOREIGN KEY (`id_emprendedor`) REFERENCES `emprendedor` (`id_emprendedor`),
  CONSTRAINT `comision_ibfk_2` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comision`
--

LOCK TABLES `comision` WRITE;
/*!40000 ALTER TABLE `comision` DISABLE KEYS */;
INSERT INTO `comision` VALUES (1,6,136,124.50,'Pendiente','2024-09-03 20:00:23',NULL,NULL),(2,5,136,62.25,'Pendiente','2024-09-03 20:00:23',NULL,NULL),(3,6,137,37.35,'Pagada','2024-09-03 21:45:54','2024-09-13 22:44:58','comprobante_1726289098.pdf'),(4,6,137,83.00,'Pagada','2024-09-03 21:45:54','2024-09-13 22:44:58','comprobante_1726289098.pdf'),(5,5,137,66.40,'Pagada','2024-09-03 21:45:54','2024-09-13 22:43:32','comprobante_1726289012.pdf'),(6,5,137,62.25,'Pagada','2024-09-03 21:45:54','2024-09-13 22:43:32','comprobante_1726289012.pdf'),(7,6,138,37.35,'Pagada','2024-09-05 23:47:54','2024-09-13 23:05:15','comprobante_1726290315.pdf'),(8,5,138,62.25,'Pendiente','2024-09-05 23:47:54',NULL,NULL),(9,5,139,66.40,'Pendiente','2024-09-06 00:02:11',NULL,NULL);
/*!40000 ALTER TABLE `comision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_pedido` (
  `id_detalle_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_emprendedor` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `nombre_producto` varchar(255) DEFAULT NULL,
  `factura_emprendedor` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_detalle_pedido`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

LOCK TABLES `detalle_pedido` WRITE;
/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
INSERT INTO `detalle_pedido` VALUES (1,1,10,5,2,20.00,40.00,'Escoba',NULL),(2,1,13,5,2,75.00,150.00,'Teclado numérico',NULL),(3,1,14,6,1,150.00,150.00,'Mesa',NULL),(4,1,15,6,2,45.00,90.00,'Caballito de palo',NULL),(5,1,17,5,1,80.00,80.00,'Chaqueta',NULL),(6,2,10,5,1,20.00,20.00,'Escoba',NULL),(7,3,13,5,1,75.00,75.00,'Teclado numérico',NULL),(8,4,15,6,1,45.00,45.00,'Caballito de palo',NULL),(9,5,17,5,3,80.00,240.00,'Chaqueta',NULL),(10,6,17,5,5,80.00,400.00,'Chaqueta',NULL),(18,14,13,5,2,75.00,150.00,NULL,NULL),(19,15,13,5,1,75.00,75.00,NULL,NULL),(21,17,17,5,5,80.00,400.00,NULL,NULL),(22,18,10,5,5,20.00,100.00,'Escoba',NULL),(23,19,13,5,2,75.00,150.00,'Teclado numérico',NULL),(25,21,14,6,1,150.00,150.00,NULL,NULL),(27,23,15,6,3,45.00,135.00,NULL,NULL),(43,47,10,5,2,20.00,40.00,'Escoba',NULL),(44,48,10,5,2,20.00,40.00,'Escoba',NULL),(45,48,13,5,1,75.00,75.00,'Teclado numérico',NULL),(46,49,10,5,2,20.00,40.00,'Escoba',NULL),(47,49,13,5,1,75.00,75.00,'Teclado numérico',NULL),(48,49,14,6,1,150.00,150.00,'Mesa',NULL),(53,53,17,5,1,80.00,80.00,'Chaqueta',NULL),(54,54,17,5,1,80.00,80.00,'Chaqueta',NULL),(55,55,17,5,1,80.00,80.00,'Chaqueta',NULL),(56,56,17,5,1,80.00,80.00,'Chaqueta',NULL),(57,57,17,5,1,80.00,80.00,'Chaqueta',NULL),(60,60,10,5,1,20.00,20.00,'Escoba',NULL),(61,61,10,5,1,20.00,20.00,'Escoba',NULL),(62,62,10,5,1,20.00,20.00,'Escoba',NULL),(63,63,10,5,1,20.00,20.00,'Escoba',NULL),(64,67,13,5,1,75.00,75.00,'Teclado numérico',NULL),(67,70,13,5,1,75.00,75.00,'Teclado numérico',NULL),(68,71,13,5,1,75.00,75.00,'Teclado numérico',NULL),(73,76,13,5,1,75.00,75.00,'Teclado numérico',NULL),(74,77,13,5,1,75.00,75.00,'Teclado numérico',NULL),(75,78,13,5,1,75.00,75.00,'Teclado numérico',NULL),(77,80,13,5,1,75.00,75.00,'Teclado numérico',NULL),(78,81,13,5,1,75.00,75.00,'Teclado numérico',NULL),(81,84,16,6,1,100.00,100.00,'Chaqueta',NULL),(82,85,16,6,1,100.00,100.00,'Chaqueta',NULL),(83,86,10,5,1,20.00,20.00,'Escoba',NULL),(84,87,10,5,1,20.00,20.00,'Escoba',NULL),(85,88,10,5,1,20.00,20.00,'Escoba',NULL),(86,89,10,5,1,20.00,20.00,'Escoba',NULL),(87,90,10,5,1,20.00,20.00,'Escoba',NULL),(91,94,16,6,1,100.00,100.00,'Chaqueta',NULL),(95,98,16,6,1,100.00,100.00,'Chaqueta',NULL),(97,100,16,6,1,100.00,100.00,'Chaqueta',NULL),(98,101,16,6,1,100.00,100.00,'Chaqueta',NULL),(99,102,10,5,1,20.00,20.00,'Escoba',NULL),(100,103,10,5,1,20.00,20.00,'Escoba',NULL),(101,104,10,5,1,20.00,20.00,'Escoba',NULL),(103,106,10,5,1,20.00,20.00,'Escoba',NULL),(105,108,10,5,1,20.00,20.00,'Escoba',NULL),(106,109,10,5,1,20.00,20.00,'Escoba',NULL),(107,110,10,5,1,20.00,20.00,'Escoba',NULL),(118,121,10,5,1,20.00,20.00,'Escoba',NULL),(119,122,10,5,1,20.00,20.00,'Escoba',NULL),(120,123,10,5,1,20.00,20.00,'Escoba',NULL),(121,124,10,5,1,20.00,20.00,'Escoba',NULL),(122,125,10,5,1,20.00,20.00,'Escoba',NULL),(123,126,10,5,1,20.00,20.00,'Escoba',NULL),(124,127,10,5,1,20.00,20.00,'Escoba',NULL),(125,128,10,5,1,20.00,20.00,'Escoba',NULL),(126,129,10,5,1,20.00,20.00,'Escoba',NULL),(127,130,10,5,4,20.00,80.00,'Escoba',NULL),(132,134,14,6,1,150.00,150.00,'Mesa',NULL),(133,134,13,5,1,75.00,75.00,'Teclado numérico',NULL),(134,135,14,6,1,150.00,150.00,'Mesa','factura_135_6.webp'),(135,135,13,5,1,75.00,75.00,'Teclado numérico',NULL),(136,136,14,6,1,150.00,150.00,'Mesa',NULL),(137,136,13,5,1,75.00,75.00,'Teclado numérico','factura_136_5.jpeg'),(138,137,15,6,1,45.00,45.00,'Caballito de palo','factura_137_6.pdf'),(139,137,16,6,1,100.00,100.00,'Chaqueta','factura_137_6.pdf'),(140,137,17,5,1,80.00,80.00,'Chaqueta','factura_137_5.jpg'),(141,137,13,5,1,75.00,75.00,'Teclado numérico','factura_137_5.jpg'),(142,138,15,6,1,45.00,45.00,'Caballito de palo',NULL),(143,138,13,5,1,75.00,75.00,'Teclado numérico',NULL),(144,139,17,5,1,80.00,80.00,'Chaqueta',NULL);
/*!40000 ALTER TABLE `detalle_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pedido_old`
--

DROP TABLE IF EXISTS `detalle_pedido_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_pedido_old` (
  `id_detalle_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle_pedido`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalle_pedido_old_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido_old` (`id_pedido`),
  CONSTRAINT `detalle_pedido_old_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido_old`
--

LOCK TABLES `detalle_pedido_old` WRITE;
/*!40000 ALTER TABLE `detalle_pedido_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_pedido_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direccion`
--

DROP TABLE IF EXISTS `direccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(255) NOT NULL,
  `municipio` varchar(255) NOT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_direccion`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direccion`
--

LOCK TABLES `direccion` WRITE;
/*!40000 ALTER TABLE `direccion` DISABLE KEYS */;
INSERT INTO `direccion` VALUES (1,'Alta Verapaz','Tactic','Chiixim'),(2,'Alta Verapaz','Tactic','San Jacinto'),(3,'Alta Verapaz','Tactic','Cahaboncito 1'),(4,'Alta Verapaz','Tactic','Asunción'),(5,'','',''),(6,'','',''),(7,'Alta Verapaz','Tactic','Villas del Cármen');
/*!40000 ALTER TABLE `direccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emprendedor`
--

DROP TABLE IF EXISTS `emprendedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emprendedor` (
  `id_emprendedor` int(11) NOT NULL AUTO_INCREMENT,
  `nombre1` varchar(255) NOT NULL,
  `nombre2` varchar(255) DEFAULT NULL,
  `nombre3` varchar(255) DEFAULT NULL,
  `apellido1` varchar(255) NOT NULL,
  `apellido2` varchar(255) DEFAULT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono1` varchar(20) DEFAULT NULL,
  `telefono2` varchar(20) DEFAULT NULL,
  `dpi` varchar(20) DEFAULT NULL,
  `id_banco` int(11) DEFAULT NULL,
  `no_cuenta_bancaria` varchar(50) DEFAULT NULL,
  `tipo_cuenta_bancaria` varchar(50) DEFAULT NULL,
  `nombre_cuenta_bancaria` varchar(255) DEFAULT NULL,
  `id_direccion` int(11) DEFAULT NULL,
  `id_estado_usuario` int(11) DEFAULT NULL,
  `id_suscripcion` int(11) DEFAULT NULL,
  `token_activacion` varchar(255) DEFAULT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `documento_identificacion` varchar(255) DEFAULT NULL,
  `registro_completo` tinyint(1) NOT NULL DEFAULT 0,
  `id_negocio` int(11) DEFAULT NULL,
  `estado_documento` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  PRIMARY KEY (`id_emprendedor`),
  KEY `id_banco` (`id_banco`),
  KEY `id_direccion` (`id_direccion`),
  KEY `id_estado_usuario` (`id_estado_usuario`),
  KEY `id_suscripcion` (`id_suscripcion`),
  KEY `fk_id_negocio` (`id_negocio`),
  CONSTRAINT `emprendedor_ibfk_1` FOREIGN KEY (`id_banco`) REFERENCES `banco` (`id_banco`),
  CONSTRAINT `emprendedor_ibfk_2` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`),
  CONSTRAINT `emprendedor_ibfk_3` FOREIGN KEY (`id_estado_usuario`) REFERENCES `estado_usuario` (`id_estado_usuario`),
  CONSTRAINT `emprendedor_ibfk_4` FOREIGN KEY (`id_suscripcion`) REFERENCES `suscripcion` (`id_suscripcion`),
  CONSTRAINT `fk_id_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emprendedor`
--

LOCK TABLES `emprendedor` WRITE;
/*!40000 ALTER TABLE `emprendedor` DISABLE KEYS */;
INSERT INTO `emprendedor` VALUES (5,'Jeziel','Jeziel','','Band','Band','jezielbandtactic@gmail.com','$2y$10$tXjyoqKkmyUgrNNW3WW6veSdILhVaavaNLslR5LYAbvL4iQwVr5pK','123','123','123456',4,'123456789','Ahorro','Jeziel Band',2,2,1,'d6aa82b1badc88c01ef70472d7426cdaff2ce87f6bfd5f566542a100fea0870bf9b89a970c6e621fc9db74959a8f91b60071',NULL,'2024-08-27 16:59:39','123456_1724777979.pdf',1,1,'pendiente'),(6,'Gesler','Gudiel','','Mó','Quej','gmoq@miumg.edu.gt','$2y$10$8TkhJaEeSU.yZdz2M59TGeL2a4B9Z5Ucpet.cGUUIgJ32FNaXxIs2','456','798','2976945531604',2,'123456789','Motenaria','TecnoG',3,2,NULL,'ada4bb9f97ef3ec9d8a402f5af706eec334ac631c5bcb9ed3cd92ad0b41d36c9b0d95a38e4d19e23d1e4dd92982b39c8a44a',NULL,'2024-08-27 21:47:48','2976945531604_1724795268.pdf',1,3,'pendiente'),(7,'Maria','Luisa','Marta','Marcos','Marcas','maria@luisa.com','$2y$10$li6.PE2li/O3SaAReFymR..xyccazKj.t3eegAPbCBf7N1IT0B4Oi','123','456','156',2,'11122233344','Ahorro','Maria Luisa',2,2,NULL,'c54c4442b3df3e452eed8617dc50b91bd9a533117b8f3b11e5aa897850d8515ac9cb0ed59fcaba0e322732c87ed39085c907',NULL,'2024-09-09 22:24:20','inventario (16).pdf',1,5,'aprobado');
/*!40000 ALTER TABLE `emprendedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `envio`
--

DROP TABLE IF EXISTS `envio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `envio` (
  `id_envio` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) DEFAULT NULL,
  `metodo_envio` varchar(255) DEFAULT NULL,
  `costo_envio` decimal(10,2) DEFAULT NULL,
  `fecha_envio` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `estado_envio` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_envio`),
  KEY `id_pedido` (`id_pedido`),
  CONSTRAINT `envio_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido_old` (`id_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `envio`
--

LOCK TABLES `envio` WRITE;
/*!40000 ALTER TABLE `envio` DISABLE KEYS */;
/*!40000 ALTER TABLE `envio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado_usuario`
--

DROP TABLE IF EXISTS `estado_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estado_usuario` (
  `id_estado_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(255) NOT NULL,
  PRIMARY KEY (`id_estado_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado_usuario`
--

LOCK TABLES `estado_usuario` WRITE;
/*!40000 ALTER TABLE `estado_usuario` DISABLE KEYS */;
INSERT INTO `estado_usuario` VALUES (1,'Pendiente de activación'),(2,'Activado'),(3,'Pendiente de validación'),(4,'Desactivado'),(5,'Revisando datos de emprendedor'),(6,'Revisando datos de tu negocio');
/*!40000 ALTER TABLE `estado_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `factura`
--

DROP TABLE IF EXISTS `factura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factura` (
  `id_factura` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `fecha_factura` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_factura`),
  KEY `fk_factura_pedido` (`id_pedido`),
  CONSTRAINT `fk_factura_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `factura`
--

LOCK TABLES `factura` WRITE;
/*!40000 ALTER TABLE `factura` DISABLE KEYS */;
INSERT INTO `factura` VALUES (1,139,'2024-09-13 16:03:50',80.00),(2,139,'2024-09-13 16:18:58',80.00),(3,138,'2024-09-13 16:37:54',120.00),(4,137,'2024-09-13 16:44:04',300.00);
/*!40000 ALTER TABLE `factura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes_chat`
--

DROP TABLE IF EXISTS `mensajes_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mensajes_chat` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `id_emprendedor` int(11) DEFAULT NULL,
  `id_administrador` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `enviado_por` enum('admin','emprendedor','cliente') NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_mensaje`),
  KEY `id_emprendedor` (`id_emprendedor`),
  KEY `id_administrador` (`id_administrador`),
  KEY `fk_cliente` (`id_cliente`),
  CONSTRAINT `fk_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  CONSTRAINT `mensajes_chat_ibfk_1` FOREIGN KEY (`id_emprendedor`) REFERENCES `emprendedor` (`id_emprendedor`),
  CONSTRAINT `mensajes_chat_ibfk_2` FOREIGN KEY (`id_administrador`) REFERENCES `administrador` (`id_administrador`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes_chat`
--

LOCK TABLES `mensajes_chat` WRITE;
/*!40000 ALTER TABLE `mensajes_chat` DISABLE KEYS */;
INSERT INTO `mensajes_chat` VALUES (3,6,NULL,NULL,'Hola.','admin','2024-09-14 07:12:16',1,NULL),(4,6,NULL,NULL,'¿Cómo puedo ayudarte?','admin','2024-09-14 07:25:39',1,NULL),(5,6,NULL,NULL,'Hola, muchas gracias.','emprendedor','2024-09-14 07:26:21',1,NULL),(6,6,NULL,NULL,'Hola.','emprendedor','2024-09-14 07:34:39',1,NULL),(7,6,NULL,NULL,'Saludos.','admin','2024-09-14 07:34:58',1,NULL),(8,6,NULL,NULL,'Soy administrador','admin','2024-09-14 07:36:42',1,NULL),(9,6,NULL,NULL,'Mucho gusto','emprendedor','2024-09-14 07:43:09',1,NULL),(10,6,NULL,NULL,'Saludos','admin','2024-09-14 07:46:09',1,NULL),(11,6,NULL,NULL,'Hola...','emprendedor','2024-09-14 07:49:22',1,NULL),(12,6,NULL,NULL,'Hola.','admin','2024-09-14 07:57:37',1,NULL),(13,6,NULL,NULL,'Buenas...','emprendedor','2024-09-14 08:00:36',1,NULL),(14,6,NULL,NULL,'hola.','emprendedor','2024-09-14 08:21:18',1,NULL),(15,6,NULL,NULL,'Hola...','emprendedor','2024-09-14 08:21:34',1,NULL),(16,6,NULL,NULL,'buenas...','emprendedor','2024-09-14 08:26:17',1,NULL),(17,6,NULL,NULL,'Hola.','admin','2024-09-14 08:34:54',1,NULL),(18,6,NULL,NULL,'Jesús.','emprendedor','2024-09-14 08:35:05',1,NULL),(19,6,NULL,NULL,'Gracias.','admin','2024-09-14 08:35:23',1,NULL),(20,6,NULL,NULL,'Jesús.','admin','2024-09-14 08:37:36',1,NULL),(21,6,NULL,NULL,'Hola.','emprendedor','2024-09-14 08:38:05',1,NULL),(22,6,NULL,NULL,'Jesús','emprendedor','2024-09-14 08:39:55',1,NULL),(23,6,NULL,NULL,'Gracias.','admin','2024-09-14 08:41:41',1,NULL),(24,6,NULL,NULL,'Bendiciones.','emprendedor','2024-09-14 08:41:48',1,NULL),(25,6,NULL,NULL,'No leído.','emprendedor','2024-09-14 08:46:50',1,NULL),(26,6,NULL,NULL,'no leído.','admin','2024-09-14 08:47:20',1,NULL),(27,6,NULL,NULL,'Hola.','admin','2024-09-14 08:50:47',1,NULL),(28,5,NULL,NULL,'Saludos.','admin','2024-09-14 08:51:25',1,NULL),(29,6,NULL,NULL,'Saludos.','emprendedor','2024-09-17 21:42:14',1,NULL),(30,6,NULL,NULL,'¿Cómo puedo ayudarte?','admin','2024-09-17 21:43:30',1,NULL),(31,6,NULL,NULL,'No puedo vender.','emprendedor','2024-09-17 21:44:31',1,NULL),(32,6,NULL,NULL,'Quiero enviarte una imagen.','emprendedor','2024-09-17 22:26:32',1,NULL),(33,6,NULL,NULL,'','emprendedor','2024-09-17 22:27:47',1,'1726612067_chaqueta.jpg'),(34,6,NULL,NULL,'He recibido tu imagen.','admin','2024-09-17 22:46:49',1,NULL),(35,6,NULL,NULL,'Muchas gracias.','emprendedor','2024-09-17 22:48:34',1,NULL),(36,NULL,NULL,1,'Hola.','','2024-09-17 23:07:37',1,NULL),(37,NULL,NULL,1,'Buenas tardes.','cliente','2024-09-17 23:33:27',0,NULL),(38,NULL,NULL,1,'Hola, como puedo ayudarte.','admin','2024-09-17 23:49:18',1,NULL),(39,NULL,NULL,1,'Necesito comprar.','cliente','2024-09-17 23:50:16',0,NULL),(40,NULL,NULL,1,'Qué necesitas.','cliente','2024-09-17 23:50:38',0,NULL),(41,NULL,NULL,1,'Qué producto necesitas.','admin','2024-09-17 23:50:51',1,NULL),(42,NULL,NULL,1,'','cliente','2024-09-17 23:52:57',0,'img_66ea1659acbee.jpg'),(43,NULL,NULL,1,'He recibido tu imagen.','admin','2024-09-17 23:56:41',1,NULL),(44,6,NULL,NULL,'Hola.','admin','2024-09-18 21:49:36',1,NULL);
/*!40000 ALTER TABLE `mensajes_chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `negocio`
--

DROP TABLE IF EXISTS `negocio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `negocio` (
  `id_negocio` int(11) NOT NULL AUTO_INCREMENT,
  `id_emprendedor` int(11) DEFAULT NULL,
  `nombre_negocio` varchar(255) NOT NULL,
  `id_direccion` int(11) DEFAULT NULL,
  `referencia_direccion` varchar(255) DEFAULT NULL,
  `patente_comercio` varchar(255) DEFAULT NULL,
  `tienda_fisica` tinyint(1) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_negocio`),
  KEY `id_emprendedor` (`id_emprendedor`),
  KEY `id_direccion` (`id_direccion`),
  CONSTRAINT `negocio_ibfk_1` FOREIGN KEY (`id_emprendedor`) REFERENCES `emprendedor` (`id_emprendedor`),
  CONSTRAINT `negocio_ibfk_2` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `negocio`
--

LOCK TABLES `negocio` WRITE;
/*!40000 ALTER TABLE `negocio` DISABLE KEYS */;
INSERT INTO `negocio` VALUES (1,5,'Jeziel',1,'Por la despensa familiar','Jezielpatente_1724780196.pdf',0,'2024-08-27 17:36:36'),(3,6,'TecnoG',4,NULL,'TecnoGpatente_1724795772.pdf',0,'2024-08-27 21:56:12'),(5,7,'Las Marías',7,'En casa','Las Marías_patente_1726094540.pdf',1,'2024-09-09 22:55:15');
/*!40000 ALTER TABLE `negocio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacion`
--

DROP TABLE IF EXISTS `notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_emprendedor` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `id_emprendedor` (`id_emprendedor`),
  CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`id_emprendedor`) REFERENCES `emprendedor` (`id_emprendedor`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacion`
--

LOCK TABLES `notificacion` WRITE;
/*!40000 ALTER TABLE `notificacion` DISABLE KEYS */;
INSERT INTO `notificacion` VALUES (1,5,NULL,'Revisar Stock','Revisa tus productos sin stock.',1,'2024-09-05 14:13:22'),(2,5,NULL,'Un producto sin stock.','Revisa tu inventario, un producto se ha quedado sin stock.',0,'2024-09-05 14:46:26'),(3,6,NULL,'Nuevo pedido recibido','Has recibido un nuevo pedido con ID #138 que incluye el producto Caballito de palo',0,'2024-09-05 15:47:54'),(4,5,NULL,'Nuevo pedido recibido','Has recibido un nuevo pedido con ID #138 que incluye el producto Teclado numérico',0,'2024-09-05 15:47:54'),(5,5,NULL,'Producto sin Stock','El producto con ID #Array con nombre Chaqueta ha quedado sin stock y su estado ha cambiado a No disponible.',0,'2024-09-05 16:02:11'),(6,5,NULL,'Nuevo pedido recibido','Has recibido un nuevo pedido con ID #139 que incluye el producto Chaqueta',0,'2024-09-05 16:02:11'),(7,7,NULL,'Corrección de Datos','El número de teléfono principal ha sido rechazado.',1,'2024-09-11 16:17:46'),(8,7,NULL,'Corrección de Datos','El número de teléfono secundario ha sido rechazado.',1,'2024-09-11 16:17:46'),(9,7,NULL,'Corrección de Datos','Algunos datos de tu perfil fueron rechazados. Por favor, revisa que todos los campos sean con tus datos reales.',1,'2024-09-11 16:32:47'),(10,7,NULL,'Corrección de Datos','Tus datos han sido aprobados por el administrador, ya puedes gestionar tu tienda.',1,'2024-09-11 16:34:21'),(11,7,NULL,'Corrección de Datos','Tus datos han sido aprobados por el administrador, ya puedes gestionar tu tienda.',1,'2024-09-11 16:36:56'),(12,7,NULL,'Corrección de Datos','Algunos datos de tu perfil fueron rechazados. Por favor, revisa que todos los campos sean con tus datos reales.',1,'2024-09-11 16:41:35'),(13,7,NULL,'Corrección de Datos','Tus datos han sido aprobados por el administrador, ya puedes gestionar tu tienda.',1,'2024-09-11 16:43:35'),(14,7,NULL,'Saludos','Gracias por usar nuestra app para vender tus productos.',1,'2024-09-12 10:38:12'),(15,5,NULL,'Actualización de Suscripción','Tu suscripción ha sido asignada/modificada. Revisa los detalles en tu panel de control.',0,'2024-09-12 11:27:57'),(16,5,NULL,'Actualización de Suscripción','Tu suscripción ha sido asignada/modificada. Revisa los detalles en tu panel de control.',0,'2024-09-12 11:28:49'),(17,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: Pendiente',0,'2024-09-12 16:14:29'),(18,NULL,1,'Confirmación de Pago','El estado de tu pago para el pedido #138 es: Pendiente',0,'2024-09-12 16:14:29'),(19,6,NULL,'Pago recibido para el pedido #138','El pago de tu producto ha sido pendiente.',0,'2024-09-12 16:14:29'),(20,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #135 ha sido actualizado a: Enviado',0,'2024-09-12 16:18:54'),(21,NULL,1,'Confirmación de Pago','El estado de tu pago para el pedido #135 es: Pendiente',0,'2024-09-12 16:18:54'),(22,6,NULL,'Pago recibido para el pedido #135','El estado del pago de tu pedido ha sido \'pendiente\'.',0,'2024-09-12 16:18:54'),(23,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #135 ha sido actualizado a: En Proceso',0,'2024-09-12 16:22:36'),(24,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #135 ha sido actualizado a: En Proceso',0,'2024-09-12 16:22:36'),(25,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #135 es: Confirmado',0,'2024-09-12 16:22:36'),(26,6,NULL,'Pago recibido para el pedido #135','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-12 16:22:36'),(27,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Pendiente',0,'2024-09-12 16:56:42'),(28,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Pendiente',0,'2024-09-12 16:56:42'),(29,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-12 16:56:42'),(30,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-12 16:56:42'),(31,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Enviado',0,'2024-09-12 17:04:25'),(32,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Enviado',0,'2024-09-12 17:04:25'),(33,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-12 17:04:25'),(34,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-12 17:04:25'),(35,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: En Proceso',0,'2024-09-12 17:08:34'),(36,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: En Proceso',0,'2024-09-12 17:08:34'),(37,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-12 17:08:34'),(38,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-12 17:08:34'),(39,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Enviado',0,'2024-09-13 13:30:14'),(40,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Enviado',0,'2024-09-13 13:30:14'),(41,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-13 13:30:14'),(42,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 13:30:14'),(43,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: ',0,'2024-09-13 13:45:47'),(44,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: ',0,'2024-09-13 13:45:47'),(45,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Confirmado',0,'2024-09-13 13:45:47'),(46,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 13:45:47'),(47,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:47:00'),(48,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:47:00'),(49,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Confirmado',0,'2024-09-13 13:47:00'),(50,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 13:47:00'),(51,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:47:20'),(52,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:47:20'),(53,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Pendiente',0,'2024-09-13 13:47:20'),(54,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'pendiente\'.',0,'2024-09-13 13:47:20'),(55,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:47:36'),(56,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:47:36'),(57,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Confirmado',0,'2024-09-13 13:47:36'),(58,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 13:47:36'),(59,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:59:00'),(60,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 13:59:00'),(61,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Confirmado',0,'2024-09-13 13:59:00'),(62,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 13:59:00'),(63,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 15:15:09'),(64,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: En Proceso',0,'2024-09-13 15:15:09'),(65,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Confirmado',0,'2024-09-13 15:15:09'),(66,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 15:15:09'),(67,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: En Proceso',0,'2024-09-13 16:03:44'),(68,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: En Proceso',0,'2024-09-13 16:03:44'),(69,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-13 16:03:44'),(70,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 16:03:44'),(71,NULL,1,'Factura disponible para el pedido #139','La factura de tu pedido está lista. Puedes descargarla aquí: ../../uploads/facturas/factura_139.pdf',0,'2024-09-13 16:03:50'),(72,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Entregado',0,'2024-09-13 16:03:50'),(73,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Entregado',0,'2024-09-13 16:03:50'),(74,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-13 16:03:50'),(75,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 16:03:50'),(76,NULL,1,'Factura disponible para el pedido #139','La factura de tu pedido está lista. Puedes descargarla aquí: ../../uploads/facturas/factura_139.pdf',0,'2024-09-13 16:18:58'),(77,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Entregado',0,'2024-09-13 16:18:58'),(78,5,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #139 ha sido actualizado a: Entregado',0,'2024-09-13 16:18:58'),(79,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #139 es: Confirmado',0,'2024-09-13 16:18:58'),(80,5,NULL,'Pago recibido para el pedido #139','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 16:18:58'),(81,NULL,1,'Factura disponible para el pedido #138','La factura de tu pedido está lista. Puedes descargarla aquí: ../../uploads/facturas/factura_138.pdf',0,'2024-09-13 16:37:54'),(82,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: Entregado',0,'2024-09-13 16:37:54'),(83,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #138 ha sido actualizado a: Entregado',0,'2024-09-13 16:37:54'),(84,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #138 es: Confirmado',0,'2024-09-13 16:37:54'),(85,6,NULL,'Pago recibido para el pedido #138','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 16:37:54'),(86,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #137 ha sido actualizado a: En Proceso',0,'2024-09-13 16:43:23'),(87,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #137 ha sido actualizado a: En Proceso',0,'2024-09-13 16:43:23'),(88,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #137 es: Pendiente',0,'2024-09-13 16:43:23'),(89,6,NULL,'Pago recibido para el pedido #137','El estado del pago de tu pedido ha sido \'pendiente\'.',0,'2024-09-13 16:43:23'),(90,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #137 ha sido actualizado a: En Proceso',0,'2024-09-13 16:43:47'),(91,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #137 ha sido actualizado a: En Proceso',0,'2024-09-13 16:43:47'),(92,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #137 es: Confirmado',0,'2024-09-13 16:43:47'),(93,6,NULL,'Pago recibido para el pedido #137','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 16:43:47'),(94,NULL,1,'Factura disponible para el pedido #137','La factura de tu pedido está lista. Puedes descargarla aquí: ../../uploads/facturas/factura_137.pdf',0,'2024-09-13 16:44:04'),(95,NULL,1,'Actualización de estado de tu pedido','El estado de tu pedido #137 ha sido actualizado a: Entregado',0,'2024-09-13 16:44:04'),(96,6,NULL,'Actualización de estado de tu pedido','El estado de tu pedido #137 ha sido actualizado a: Entregado',0,'2024-09-13 16:44:04'),(97,NULL,1,'Confirmación de Pago','El estado del pago para el pedido #137 es: Confirmado',0,'2024-09-13 16:44:04'),(98,6,NULL,'Pago recibido para el pedido #137','El estado del pago de tu pedido ha sido \'confirmado\'.',0,'2024-09-13 16:44:04'),(99,NULL,NULL,'Respuesta de comentario actualizada','Tu respuesta al comentario #1 ha sido modificada por el administrador.',0,'2024-09-14 00:22:13'),(100,NULL,NULL,'Respuesta de comentario actualizada','Tu respuesta al comentario #1 ha sido modificada por el administrador.',0,'2024-09-14 00:23:46'),(101,NULL,NULL,'Respuesta de comentario actualizada','Tu respuesta al comentario #1 ha sido modificada por el administrador.',0,'2024-09-14 00:24:39'),(102,6,NULL,'Respuesta de comentario actualizada','Tu respuesta al comentario #1 ha sido modificada por el administrador.',0,'2024-09-14 00:27:42'),(103,6,NULL,'Respuesta de comentario actualizada','Tu respuesta al comentario del producto nombre_producto ha sido modificada por el administrador.',0,'2024-09-14 00:31:06'),(104,6,NULL,'Respuesta de comentario actualizada','Tu respuesta al comentario del producto Chaqueta ha sido modificada por el administrador.',0,'2024-09-14 00:32:14'),(105,6,NULL,'Comentario eliminado','El comentario en el producto Chaqueta ha sido eliminado por el administrador.',0,'2024-09-14 00:44:09'),(106,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 01:34:39'),(107,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 01:34:58'),(108,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 01:36:42'),(109,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 01:43:09'),(110,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 01:46:09'),(111,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 01:49:22'),(112,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 01:57:37'),(113,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:00:36'),(114,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:21:18'),(115,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:21:34'),(116,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:26:17'),(117,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:34:54'),(118,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:35:05'),(119,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:35:23'),(120,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:37:36'),(121,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:38:05'),(122,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:39:55'),(123,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:41:41'),(124,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:41:48'),(125,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-14 02:46:50'),(126,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:47:20'),(127,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:50:47'),(128,NULL,5,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-14 02:51:25'),(129,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-17 15:42:14'),(130,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-17 15:43:30'),(131,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-17 15:44:31'),(132,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-17 16:26:32'),(133,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-17 16:27:47'),(134,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-17 16:46:49'),(135,NULL,NULL,'Nuevo mensaje','Has recibido un nuevo mensaje del emprendedor.',0,'2024-09-17 16:48:34'),(144,NULL,6,'Nuevo mensaje','Has recibido un nuevo mensaje del administrador.',0,'2024-09-18 15:49:36');
/*!40000 ALTER TABLE `notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago`
--

DROP TABLE IF EXISTS `pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) DEFAULT NULL,
  `metodo_pago` varchar(255) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_pago` varchar(50) DEFAULT NULL,
  `imagen_comprobante` varchar(255) DEFAULT NULL,
  `id_factura` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `fk_pago_pedido` (`id_pedido`),
  KEY `fk_pago_factura` (`id_factura`),
  CONSTRAINT `fk_pago_factura` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`) ON DELETE SET NULL,
  CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago`
--

LOCK TABLES `pago` WRITE;
/*!40000 ALTER TABLE `pago` DISABLE KEYS */;
INSERT INTO `pago` VALUES (2,14,'deposito_bancario',0.00,'2024-08-30 04:15:26','Pendiente',NULL,NULL),(3,15,'tarjeta',0.00,'2024-08-30 04:19:41','Completado',NULL,NULL),(5,17,'tarjeta',0.00,'2024-08-30 04:27:10','Completado',NULL,NULL),(6,18,'tarjeta',100.00,'2024-08-30 04:40:29','Completado',NULL,NULL),(7,19,'tarjeta',150.00,'2024-08-30 04:50:10','Completado',NULL,NULL),(9,21,'deposito_bancario',0.00,'2024-08-30 05:07:55','Pendiente',NULL,NULL),(11,23,'deposito_bancario',0.00,'2024-08-30 05:12:55','Pendiente',NULL,NULL),(29,56,'deposito_bancario',0.00,'2024-08-30 10:53:23','Pendiente',NULL,NULL),(30,57,'deposito_bancario',0.00,'2024-08-30 10:54:37','Pendiente',NULL,NULL),(33,62,'deposito_bancario',0.00,'2024-08-30 11:11:40','Pendiente',NULL,NULL),(34,63,'deposito_bancario',0.00,'2024-08-30 11:12:46','Pendiente',NULL,NULL),(37,70,'tarjeta',0.00,'2024-08-30 12:00:56','Completado',NULL,NULL),(38,71,'tarjeta',0.00,'2024-08-30 12:05:13','Completado',NULL,NULL),(39,78,'tarjeta',0.00,'2024-08-30 12:41:07','Completado',NULL,NULL),(40,80,'deposito_bancario',0.00,'2024-08-30 12:42:23','Pendiente',NULL,NULL),(41,81,'tarjeta',0.00,'2024-08-30 12:55:47','Completado',NULL,NULL),(43,84,'tarjeta',0.00,'2024-08-30 12:59:36','Completado',NULL,NULL),(44,85,'deposito_bancario',0.00,'2024-08-30 13:01:21','Pendiente',NULL,NULL),(45,86,'deposito_bancario',0.00,'2024-08-31 03:43:32','Pendiente',NULL,NULL),(46,87,'deposito_bancario',0.00,'2024-08-31 03:48:14','Pendiente',NULL,NULL),(47,88,'tarjeta',0.00,'2024-08-31 03:50:47','Completado',NULL,NULL),(48,89,'tarjeta',0.00,'2024-08-31 04:04:51','Completado',NULL,NULL),(49,90,'tarjeta',0.00,'2024-08-31 04:07:47','Completado',NULL,NULL),(53,94,'tarjeta',0.00,'2024-08-31 04:17:48','Completado',NULL,NULL),(54,98,'tarjeta',0.00,'2024-08-31 04:31:18','Completado',NULL,NULL),(55,100,'tarjeta',0.00,'2024-08-31 04:46:11','Completado',NULL,NULL),(56,101,'deposito_bancario',0.00,'2024-08-31 04:46:45','Pendiente',NULL,NULL),(57,102,'deposito_bancario',0.00,'2024-08-31 04:49:47','Pendiente','comprobante_102.avif',NULL),(58,103,'tarjeta',0.00,'2024-08-31 04:54:32','Completado',NULL,NULL),(59,104,'deposito_bancario',20.00,'2024-08-31 05:05:31','Pendiente','comprobante_104.jpg',NULL),(60,106,'tarjeta',20.00,'2024-08-31 05:16:57','Completado',NULL,NULL),(61,108,'deposito_bancario',20.00,'2024-08-31 05:36:00','Pendiente','comprobante_108.avif',NULL),(62,109,'tarjeta',20.00,'2024-08-31 05:36:30','Completado',NULL,NULL),(63,110,'deposito_bancario',20.00,'2024-08-31 05:37:03','Pendiente','comprobante_110.webp',NULL),(64,121,'tarjeta',20.00,'2024-08-31 06:26:05','Completado',NULL,NULL),(65,122,'deposito_bancario',20.00,'2024-08-31 06:28:42','Pendiente','comprobante_122.jpeg',NULL),(66,123,'deposito_bancario',20.00,'2024-08-31 06:31:11','Pendiente','comprobante_123.jpeg',NULL),(67,124,'deposito_bancario',20.00,'2024-09-03 06:35:21','Pendiente',NULL,NULL),(68,125,'deposito_bancario',20.00,'2024-09-03 06:40:32','Pendiente',NULL,NULL),(69,126,'deposito_bancario',20.00,'2024-09-03 06:52:08','Pendiente',NULL,NULL),(70,127,'tarjeta',20.00,'2024-09-03 06:52:49','Completado',NULL,NULL),(71,128,'deposito_bancario',20.00,'2024-09-03 06:53:18','Pendiente','comprobante_128.jpeg',NULL),(72,129,'deposito_bancario',20.00,'2024-09-03 07:18:54','Pendiente',NULL,NULL),(73,130,'tarjeta',80.00,'2024-09-04 01:15:27','Completado',NULL,NULL),(75,134,'deposito_bancario',225.00,'2024-09-04 01:55:48','Pendiente',NULL,NULL),(76,135,'deposito_bancario',225.00,'2024-09-04 01:58:32','Confirmado','comprobante_135.jpg',NULL),(77,136,'deposito_bancario',225.00,'2024-09-04 02:00:23','Pendiente',NULL,NULL),(78,137,'tarjeta',300.00,'2024-09-04 03:45:54','Confirmado',NULL,4),(79,138,'tarjeta',120.00,'2024-09-06 05:47:54','Confirmado',NULL,3),(80,139,'tarjeta',80.00,'2024-09-06 06:02:11','Confirmado',NULL,2);
/*!40000 ALTER TABLE `pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido`
--

DROP TABLE IF EXISTS `pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `fecha_pedido` datetime NOT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  `estado_pedido` varchar(50) NOT NULL,
  `direccion_envio` text DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_pedido`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido`
--

LOCK TABLES `pedido` WRITE;
/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
INSERT INTO `pedido` VALUES (1,1,NULL,'2024-08-29 18:37:33',NULL,'Pendiente','',''),(2,1,NULL,'2024-08-29 18:49:38',NULL,'Pendiente','',''),(3,1,NULL,'2024-08-29 18:53:15',NULL,'Pendiente','',''),(4,1,NULL,'2024-08-29 18:59:20',NULL,'Pendiente','Arriba',''),(5,1,NULL,'2024-08-29 19:02:08',NULL,'Pendiente','Abajo','45678913'),(6,1,NULL,'2024-08-29 19:07:35',NULL,'Pendiente','Cerca de la tienda','31499491'),(14,1,NULL,'2024-08-29 22:15:26',NULL,'Pendiente','Cerca de la tienda','45678913'),(15,1,NULL,'2024-08-29 22:19:41',NULL,'Pendiente','En casa','12345678'),(17,1,NULL,'2024-08-29 22:27:10',NULL,'Pendiente','Cerca de la tienda','31499491'),(18,1,NULL,'2024-08-29 22:40:29',NULL,'Pendiente','Cerca de la tienda','45678913'),(19,1,NULL,'2024-08-29 22:50:10',NULL,'Pendiente','En la casa','12312312'),(21,1,NULL,'2024-08-29 23:07:55',NULL,'Pendiente','Arriba','1111111'),(23,1,NULL,'2024-08-29 23:12:55',NULL,'Pendiente','Colonia 2000','22222222'),(42,1,NULL,'2024-08-30 00:34:40',NULL,'Pendiente','Abajo','12345678'),(43,1,NULL,'2024-08-30 00:36:37',NULL,'Pendiente','Abajo','12345678'),(45,1,NULL,'2024-08-30 00:40:56',NULL,'Pendiente','Cerca de la tienda','33333333'),(46,1,NULL,'2024-08-30 00:41:37',NULL,'Pendiente','',''),(47,1,NULL,'2024-08-30 00:44:47',NULL,'Pendiente','Cerca de la tienda','45678913'),(48,1,NULL,'2024-08-30 00:46:54',NULL,'Pendiente','Arriba','66666666'),(49,1,NULL,'2024-08-30 00:47:57',NULL,'Pendiente','Colonia 2000','77777777'),(53,1,NULL,'2024-08-30 03:51:53',NULL,'Pendiente','Zona 1','1'),(54,1,NULL,'2024-08-30 03:53:06',NULL,'Pendiente','Zona 2','2'),(55,1,NULL,'2024-08-30 03:54:03',NULL,'Pendiente','Zona 3','3'),(56,1,NULL,'2024-08-30 04:53:23',NULL,'Pendiente','Zona 4','4'),(57,1,NULL,'2024-08-30 04:54:37',NULL,'Pendiente','Zona 5','5'),(60,1,NULL,'2024-08-30 05:05:06',NULL,'Pendiente','Zona 6','6'),(61,1,NULL,'2024-08-30 05:06:47',NULL,'Pendiente','Zona 7','7'),(62,1,NULL,'2024-08-30 05:11:40',NULL,'Pendiente','Zona 8','8'),(63,1,NULL,'2024-08-30 05:12:46',NULL,'Pendiente','Zona 9','9'),(67,1,NULL,'2024-08-30 05:42:56',NULL,'Pendiente','Cerca de la tienda','45678913'),(70,1,NULL,'2024-08-30 06:00:56',NULL,'Pendiente','Zona 11','11'),(71,1,NULL,'2024-08-30 06:05:13',NULL,'Pendiente','Cerca de la tienda','33333333'),(76,1,NULL,'2024-08-30 06:23:50',NULL,'Pendiente','Abajo','33333333'),(77,1,NULL,'2024-08-30 06:27:23',NULL,'Pendiente','En la casa','22222222'),(78,1,NULL,'2024-08-30 06:41:07',NULL,'Pendiente','En casa','12345678'),(80,1,NULL,'2024-08-30 06:42:23',NULL,'Pendiente','Colonia 2000','45678913'),(81,1,NULL,'2024-08-30 06:55:47',NULL,'Pendiente','Arriba','1111111'),(84,1,NULL,'2024-08-30 06:59:36',NULL,'Pendiente','En casa','1111111'),(85,1,NULL,'2024-08-30 07:01:21',NULL,'Pendiente','Zona 3','12345678'),(86,1,NULL,'2024-08-30 21:43:32',NULL,'Pendiente','Cerca de la tienda','33333333'),(87,1,NULL,'2024-08-30 21:48:14',NULL,'Pendiente','En la casa','33333333'),(88,1,NULL,'2024-08-30 21:50:47',NULL,'Pendiente','Abajo','1111111'),(89,1,NULL,'2024-08-30 22:04:51',NULL,'Pendiente','Colonia 2000','1111111'),(90,1,NULL,'2024-08-30 22:07:47',NULL,'Pendiente','Cerca de la tienda','12345678'),(94,1,NULL,'2024-08-30 22:17:48',NULL,'Pendiente','Colonia 2000','22222222'),(98,1,NULL,'2024-08-30 22:31:18',NULL,'Pendiente','Colonia 2000','1111111'),(100,1,NULL,'2024-08-30 22:46:11',NULL,'Pendiente','En casa','12345678'),(101,1,NULL,'2024-08-30 22:46:45',NULL,'Pendiente','En la casa','12345678'),(102,1,NULL,'2024-08-30 22:49:47',NULL,'Pendiente','En casa','33333333'),(103,1,NULL,'2024-08-30 22:54:32',NULL,'Pendiente','Zona 1','1111111'),(104,1,NULL,'2024-08-30 23:05:31',NULL,'Pendiente','Abajo','12345678'),(106,1,NULL,'2024-08-30 23:16:57',NULL,'Pendiente','Zona 3','31499491'),(108,1,NULL,'2024-08-30 23:36:00',NULL,'Pendiente','Colonia 2000','31499491'),(109,1,NULL,'2024-08-30 23:36:30',NULL,'Pendiente','Abajo','12345678'),(110,1,NULL,'2024-08-30 23:37:03',NULL,'Pendiente','Zona 6','33333333'),(121,1,NULL,'2024-08-31 00:26:05',NULL,'Pendiente','Cerca de la tienda','1111111'),(122,1,NULL,'2024-08-31 00:28:42',NULL,'Pendiente','Abajo','33333333'),(123,1,NULL,'2024-08-31 00:31:11',NULL,'Pendiente','En casa','22222222'),(124,1,NULL,'2024-09-03 00:35:21',NULL,'Pendiente','Colonia San Juan','123456789'),(125,1,NULL,'2024-09-03 00:40:32',NULL,'Pendiente','Zona 6','12345678'),(126,1,NULL,'2024-09-03 00:52:08',NULL,'Pendiente','En casa','45678913'),(127,1,NULL,'2024-09-03 00:52:49',NULL,'Pendiente','Colonia 2000','1111111'),(128,1,NULL,'2024-09-03 00:53:18',NULL,'Pendiente','En casa','33333333'),(129,1,NULL,'2024-09-03 01:18:54','2024-09-04 01:18:54','Pendiente','En casa','45678913'),(130,1,NULL,'2024-09-03 19:15:27',NULL,'Pendiente','Colegio Vida','11559977'),(134,1,NULL,'2024-09-03 19:55:48','2024-09-04 19:55:48','Pendiente','Colonia 2000','12345678'),(135,1,NULL,'2024-09-03 19:58:32','2024-09-04 19:58:32','En Proceso','Zona 3','33333333'),(136,1,NULL,'2024-09-03 20:00:23','2024-09-04 20:00:23','entregado a centro de empaquetado','Zona 2','22222222'),(137,1,NULL,'2024-09-03 21:45:54',NULL,'Entregado','Cerca de la tienda','31499491'),(138,1,NULL,'2024-09-05 23:47:54',NULL,'Entregado','En casa','33333333'),(139,1,NULL,'2024-09-06 00:02:11',NULL,'Entregado','Colonia 2000','31499491');
/*!40000 ALTER TABLE `pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido_old`
--

DROP TABLE IF EXISTS `pedido_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedido_old` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_pedido` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  CONSTRAINT `pedido_old_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  CONSTRAINT `pedido_old_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedor` (`id_vendedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_old`
--

LOCK TABLES `pedido_old` WRITE;
/*!40000 ALTER TABLE `pedido_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedido_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `id_negocio` int(11) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `costo` decimal(10,0) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_emprendedor` int(11) NOT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `id_negocio` (`id_negocio`),
  KEY `id_categoria` (`id_categoria`),
  KEY `fk_producto_emprendedor` (`id_emprendedor`),
  CONSTRAINT `fk_producto_emprendedor` FOREIGN KEY (`id_emprendedor`) REFERENCES `emprendedor` (`id_emprendedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`),
  CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES (10,NULL,2,'Escoba','Funcional',15,20.00,0,'escoba.webp','no disponible','2024-09-06 21:49:33',5),(13,1,1,'Teclado numérico','USB',50,75.00,0,'teclado1.jpg','no disponible','2024-09-06 21:49:37',5),(14,3,2,'Mesa','Mesa rectangular',100,150.00,0,'mesa.jpg','no disponible','2024-09-06 21:49:42',6),(15,3,2,'Caballito de palo','Colores',40,45.00,0,'Caballo-de-Palo-Spi-1-16171.webp','no disponible','2024-09-06 21:49:50',6),(16,3,2,'Chaqueta','Negra',85,100.00,1,'chaqueta.jpg','disponible','2024-09-12 03:29:37',6),(17,1,2,'Chaqueta','Negra',70,80.00,0,'chaqueta colchon.webp','no disponible','2024-09-06 21:49:56',5);
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocion`
--

DROP TABLE IF EXISTS `promocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promocion` (
  `id_promocion` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) DEFAULT NULL,
  `descripcion_promocion` text DEFAULT NULL,
  `descuento` decimal(5,2) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  PRIMARY KEY (`id_promocion`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `promocion_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocion`
--

LOCK TABLES `promocion` WRITE;
/*!40000 ALTER TABLE `promocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `promocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcategoria`
--

DROP TABLE IF EXISTS `subcategoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcategoria` (
  `id_subcategoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_categoria` int(11) DEFAULT NULL,
  `nombre_subcategoria` varchar(255) NOT NULL,
  `descripcion_subcategoria` text DEFAULT NULL,
  PRIMARY KEY (`id_subcategoria`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `subcategoria_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcategoria`
--

LOCK TABLES `subcategoria` WRITE;
/*!40000 ALTER TABLE `subcategoria` DISABLE KEYS */;
INSERT INTO `subcategoria` VALUES (1,1,'Computadoras de escritorio',''),(2,1,'Computadoras portátiles',''),(3,2,'Muebles','');
/*!40000 ALTER TABLE `subcategoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suscripcion`
--

DROP TABLE IF EXISTS `suscripcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suscripcion` (
  `id_suscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_suscripcion` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `duracion` int(11) NOT NULL COMMENT 'Duración en meses',
  `estado` varchar(50) NOT NULL,
  PRIMARY KEY (`id_suscripcion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suscripcion`
--

LOCK TABLES `suscripcion` WRITE;
/*!40000 ALTER TABLE `suscripcion` DISABLE KEYS */;
INSERT INTO `suscripcion` VALUES (1,'Prueba','Es una versión de prueba',0.00,1,'activo');
/*!40000 ALTER TABLE `suscripcion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendedor`
--

DROP TABLE IF EXISTS `vendedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendedor` (
  `id_vendedor` int(11) NOT NULL AUTO_INCREMENT,
  `id_negocio` int(11) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_vendedor`),
  KEY `id_negocio` (`id_negocio`),
  CONSTRAINT `vendedor_ibfk_1` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendedor`
--

LOCK TABLES `vendedor` WRITE;
/*!40000 ALTER TABLE `vendedor` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendedor` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-19  9:26:20
