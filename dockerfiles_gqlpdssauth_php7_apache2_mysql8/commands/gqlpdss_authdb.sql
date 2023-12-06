-- MySQL dump 10.13  Distrib 5.7.24, for osx11.1 (x86_64)
--
-- Host: 127.0.0.1    Database: gqlpdss_authdb
-- ------------------------------------------------------
-- Server version	8.0.28

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `gpd_auth_permissions`
--

DROP TABLE IF EXISTS `gpd_auth_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gpd_auth_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resource_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `permission_access` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permision_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_D4E9499D89329D25` (`resource_id`),
  KEY `IDX_D4E9499DA76ED395` (`user_id`),
  KEY `IDX_D4E9499DD60322AC` (`role_id`),
  CONSTRAINT `FK_D4E9499D89329D25` FOREIGN KEY (`resource_id`) REFERENCES `gpd_auth_resources` (`id`),
  CONSTRAINT `FK_D4E9499DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `gpd_auth_users` (`id`),
  CONSTRAINT `FK_D4E9499DD60322AC` FOREIGN KEY (`role_id`) REFERENCES `gpd_auth_roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gpd_auth_permissions`
--

LOCK TABLES `gpd_auth_permissions` WRITE;
/*!40000 ALTER TABLE `gpd_auth_permissions` DISABLE KEYS */;
INSERT INTO `gpd_auth_permissions` VALUES (1,1,1,NULL,'ALLOW','ALL',NULL,'2023-12-06 17:58:30','2023-12-06 17:58:30');
/*!40000 ALTER TABLE `gpd_auth_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gpd_auth_resources`
--

DROP TABLE IF EXISTS `gpd_auth_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gpd_auth_resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` json DEFAULT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8096B5E77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gpd_auth_resources`
--

LOCK TABLES `gpd_auth_resources` WRITE;
/*!40000 ALTER TABLE `gpd_auth_resources` DISABLE KEYS */;
INSERT INTO `gpd_auth_resources` VALUES (1,'test_resource','Test Resource','','[\"ALL\"]','2023-12-06 17:56:52','2023-12-06 17:56:52');
/*!40000 ALTER TABLE `gpd_auth_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gpd_auth_roles`
--

DROP TABLE IF EXISTS `gpd_auth_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gpd_auth_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DBDB49FB77153098` (`code`),
  KEY `role_code_idx` (`code`),
  KEY `role_title_idx` (`title`),
  KEY `role_created_idx` (`created`),
  KEY `role_updated_idx` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gpd_auth_roles`
--

LOCK TABLES `gpd_auth_roles` WRITE;
/*!40000 ALTER TABLE `gpd_auth_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `gpd_auth_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gpd_auth_users`
--

DROP TABLE IF EXISTS `gpd_auth_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gpd_auth_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `algorithm` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_expiration` date DEFAULT NULL,
  `picture` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  `updated` datetime NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7966C2D5F85E0677` (`username`),
  KEY `user_username_idx` (`username`),
  KEY `user_email_idx` (`email`),
  KEY `user_firstname_idx` (`firstname`),
  KEY `user_lastname_idx` (`lastname`),
  KEY `user_created_idx` (`created`),
  KEY `user_updated_idx` (`updated`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gpd_auth_users`
--

LOCK TABLES `gpd_auth_users` WRITE;
/*!40000 ALTER TABLE `gpd_auth_users` DISABLE KEYS */;
INSERT INTO `gpd_auth_users` VALUES (1,'Pancho','López','p.lopez@demo.local.lan','p.lopez','sha256',NULL,'3eff2ffd7f82c57cfd6401e912742a0e3481c2149cf21c7ed859f0296d4fd636',NULL,NULL,1,NULL,'2023-12-06 01:05:59','2023-12-06 01:05:59');
/*!40000 ALTER TABLE `gpd_auth_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gpd_auth_users_roles`
--

DROP TABLE IF EXISTS `gpd_auth_users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gpd_auth_users_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_A84D0F7CA76ED395` (`user_id`),
  KEY `IDX_A84D0F7CD60322AC` (`role_id`),
  CONSTRAINT `FK_A84D0F7CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `gpd_auth_users` (`id`),
  CONSTRAINT `FK_A84D0F7CD60322AC` FOREIGN KEY (`role_id`) REFERENCES `gpd_auth_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gpd_auth_users_roles`
--

LOCK TABLES `gpd_auth_users_roles` WRITE;
/*!40000 ALTER TABLE `gpd_auth_users_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `gpd_auth_users_roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-12-06 13:09:13
