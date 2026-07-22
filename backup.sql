-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: benutzerverwaltung
-- ------------------------------------------------------
-- Server version	11.8.8-MariaDB-ubu2404

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
-- Current Database: `benutzerverwaltung`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `benutzerverwaltung` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `benutzerverwaltung`;

--
-- Table structure for table `benutzer`
--

DROP TABLE IF EXISTS `benutzer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `benutzer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vorname` varchar(100) NOT NULL,
  `nachname` varchar(100) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefonnummer` varchar(50) NOT NULL,
  `passwort_hash` varchar(255) NOT NULL,
  `rolle` enum('benutzer','admin','owner') NOT NULL DEFAULT 'benutzer',
  `erstellt_am` timestamp NOT NULL DEFAULT current_timestamp(),
  `aktualisiert_am` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `benutzer`
--

LOCK TABLES `benutzer` WRITE;
/*!40000 ALTER TABLE `benutzer` DISABLE KEYS */;
INSERT INTO `benutzer` VALUES (1,'Taylor','Kofler','Europastraße 9a','taylor.kofler@itlabs.at','067762574873','$2y$10$F0u0kevnp6v3TwSOehNXUOTYBywjjDQVW85QUHKJvKwMxA03mgXm2','admin','2026-07-22 08:54:21','2026-07-22 09:26:55'),(2,'Admin','Kofler','Europastraße 9a','kofler.admin@owner.com','+49 176 0390500','$2y$10$KNJvJtEU1XuP8lebz121GO5itg/uDsU5lCHpPDbDb8h4Vs7d1GDES','owner','2026-07-22 09:26:27','2026-07-22 09:26:27');
/*!40000 ALTER TABLE `benutzer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passwort_reset_tokens`
--

DROP TABLE IF EXISTS `passwort_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passwort_reset_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `benutzer_id` int(10) unsigned NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `gueltig_bis` datetime NOT NULL,
  `verwendet` tinyint(1) NOT NULL DEFAULT 0,
  `erstellt_am` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `fk_passwort_reset_tokens_benutzer` (`benutzer_id`),
  KEY `idx_passwort_reset_tokens_gueltig_bis` (`gueltig_bis`),
  CONSTRAINT `fk_passwort_reset_tokens_benutzer` FOREIGN KEY (`benutzer_id`) REFERENCES `benutzer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passwort_reset_tokens`
--

LOCK TABLES `passwort_reset_tokens` WRITE;
/*!40000 ALTER TABLE `passwort_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `passwort_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'benutzerverwaltung'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-22 11:29:24
