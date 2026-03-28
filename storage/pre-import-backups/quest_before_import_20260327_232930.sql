-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: u488847015_quest_baseDado
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
-- Current Database: `u488847015_quest_baseDado`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `u488847015_quest_basedado` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `u488847015_quest_baseDado`;

--
-- Table structure for table `backup_runs`
--

DROP TABLE IF EXISTS `backup_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_runs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trigger_type` enum('manual','scheduled') NOT NULL DEFAULT 'manual',
  `status` enum('running','success','failed') NOT NULL DEFAULT 'running',
  `triggered_by_user_id` int(10) unsigned DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `local_path` varchar(500) DEFAULT NULL,
  `drive_file_id` varchar(255) DEFAULT NULL,
  `drive_file_link` varchar(500) DEFAULT NULL,
  `size_bytes` bigint(20) unsigned DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `finished_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_runs_status_index` (`status`),
  KEY `backup_runs_started_at_index` (`started_at`),
  KEY `backup_runs_triggered_by_user_id_index` (`triggered_by_user_id`),
  CONSTRAINT `backup_runs_triggered_by_user_id_foreign` FOREIGN KEY (`triggered_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_runs`
--

LOCK TABLES `backup_runs` WRITE;
/*!40000 ALTER TABLE `backup_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disciplines`
--

DROP TABLE IF EXISTS `disciplines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disciplines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `disciplines_name_unique` (`name`),
  KEY `disciplines_created_by_index` (`created_by`),
  CONSTRAINT `disciplines_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disciplines`
--

LOCK TABLES `disciplines` WRITE;
/*!40000 ALTER TABLE `disciplines` DISABLE KEYS */;
INSERT INTO `disciplines` VALUES (1,'Matematica',1,'2026-03-27 00:55:55'),(2,'Portugues',1,'2026-03-27 00:55:55'),(3,'Fisica',1,'2026-03-27 00:55:55'),(4,'Linguagens, Codigos e suas Tecnologias',2,'2026-03-27 00:58:45');
/*!40000 ALTER TABLE `disciplines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `exam_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `display_order` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_questions_unique` (`exam_id`,`question_id`),
  KEY `exam_questions_question_id_index` (`question_id`),
  CONSTRAINT `exam_questions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
INSERT INTO `exam_questions` VALUES (1,1,1,1,'2026-03-27 01:44:38');
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `instructions` text DEFAULT NULL,
  `xerox_status` enum('not_sent','sent','in_progress','finished') NOT NULL DEFAULT 'not_sent',
  `xerox_target_user_id` int(10) unsigned DEFAULT NULL,
  `xerox_requested_at` timestamp NULL DEFAULT NULL,
  `xerox_started_at` timestamp NULL DEFAULT NULL,
  `xerox_finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exams_user_id_index` (`user_id`),
  CONSTRAINT `exams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
INSERT INTO `exams` VALUES (1,2,'Nova prova',NULL,'finished',2,'2026-03-28 00:31:07','2026-03-28 00:31:13','2026-03-28 00:31:16','2026-03-27 01:44:38','2026-03-28 00:31:16');
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `password_resets_token_hash_unique` (`token_hash`),
  KEY `password_resets_user_id_index` (`user_id`),
  CONSTRAINT `password_resets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_favorites`
--

DROP TABLE IF EXISTS `question_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_favorites_unique` (`question_id`,`user_id`),
  KEY `question_favorites_user_id_index` (`user_id`),
  CONSTRAINT `question_favorites_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_favorites`
--

LOCK TABLES `question_favorites` WRITE;
/*!40000 ALTER TABLE `question_favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_options`
--

DROP TABLE IF EXISTS `question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `question_options_question_id_index` (`question_id`),
  CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_options`
--

LOCK TABLES `question_options` WRITE;
/*!40000 ALTER TABLE `question_options` DISABLE KEYS */;
INSERT INTO `question_options` VALUES (1,1,'A) Olhar diferenciado para com o outro gera mudanças.',1,1,'2026-03-27 00:58:45'),(2,1,'B) Estudante com dislexia apresenta um tom questionador.',0,2,'2026-03-27 00:58:45'),(3,1,'C) Abordagem para lidar com a dislexia é pautada na disciplina.',0,3,'2026-03-27 00:58:45'),(4,1,'D) Contato com os pais prejudica o acompanhamento da dislexia.',0,4,'2026-03-27 00:58:45'),(5,1,'E) Mudança de interesses ocorre na transição da infância para a vida adulta.',0,5,'2026-03-27 00:58:45'),(6,2,'A) Olhar diferenciado para com o outro gera mudanças.',1,1,'2026-03-27 03:42:52'),(7,2,'B) Estudante com dislexia apresenta um tom questionador.',0,2,'2026-03-27 03:42:52'),(8,2,'C) Abordagem para lidar com a dislexia é pautada na disciplina.',0,3,'2026-03-27 03:42:52'),(9,2,'D) Contato com os pais prejudica o acompanhamento da dislexia.',0,4,'2026-03-27 03:42:52'),(10,2,'E) Mudança de interesses ocorre na transição da infância para a vida adulta.',0,5,'2026-03-27 03:42:52'),(16,4,'A) Olhar diferenciado para com o outro gera mudanças.',1,1,'2026-03-27 03:43:02'),(17,4,'B) Estudante com dislexia apresenta um tom questionador.',0,2,'2026-03-27 03:43:02'),(18,4,'C) Abordagem para lidar com a dislexia é pautada na disciplina.',0,3,'2026-03-27 03:43:02'),(19,4,'D) Contato com os pais prejudica o acompanhamento da dislexia.',0,4,'2026-03-27 03:43:02'),(20,4,'E) Mudança de interesses ocorre na transição da infância para a vida adulta.',0,5,'2026-03-27 03:43:02'),(21,3,'A) Olhar diferenciado para com o outro gera mudanças.',1,1,'2026-03-27 03:43:17'),(22,3,'B) Estudante com dislexia apresenta um tom questionador.',0,2,'2026-03-27 03:43:17'),(23,3,'C) Abordagem para lidar com a dislexia é pautada na disciplina.',0,3,'2026-03-27 03:43:17'),(24,3,'D) Contato com os pais prejudica o acompanhamento da dislexia.',0,4,'2026-03-27 03:43:17'),(25,3,'E) Mudança de interesses ocorre na transição da infância para a vida adulta.',0,5,'2026-03-27 03:43:17'),(29,5,'Quem comeu mais pizza? Justifique sua resposta.',1,1,'2026-03-28 01:32:09'),(30,5,'A pizza foi totalmente consumida? Mostre os cálculos.',0,2,'2026-03-28 01:32:09'),(31,5,'Se ainda restar pizza, qual fração sobrou?',0,3,'2026-03-28 01:32:09'),(32,6,'R$ 15,00',0,1,'2026-03-28 01:36:02'),(33,6,'R$ 20,00',0,2,'2026-03-28 01:36:02'),(34,6,'R$ 25,00',1,3,'2026-03-28 01:36:02'),(35,6,'R$ 30,00',0,4,'2026-03-28 01:36:02'),(36,6,'R$ 35,00',0,5,'2026-03-28 01:36:02');
/*!40000 ALTER TABLE `question_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `based_on_question_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `prompt` text NOT NULL,
  `prompt_image_url` varchar(500) DEFAULT NULL,
  `question_type` enum('multiple_choice','discursive','drawing','true_false') NOT NULL,
  `visibility` enum('private','public') NOT NULL DEFAULT 'private',
  `discipline_id` int(10) unsigned DEFAULT NULL,
  `subject_id` int(10) unsigned DEFAULT NULL,
  `education_level` enum('fundamental','medio','tecnico','superior') NOT NULL DEFAULT 'medio',
  `difficulty` enum('facil','medio','dificil') NOT NULL DEFAULT 'medio',
  `status` enum('draft','review','published') NOT NULL DEFAULT 'draft',
  `allow_multiple_correct` tinyint(1) NOT NULL DEFAULT 0,
  `discursive_answer` text DEFAULT NULL,
  `response_lines` smallint(5) unsigned DEFAULT NULL,
  `drawing_size` enum('small','medium','large','custom') DEFAULT NULL,
  `drawing_height_px` smallint(5) unsigned DEFAULT NULL,
  `true_false_answer` tinyint(1) DEFAULT NULL,
  `source_name` varchar(180) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `source_reference` varchar(255) DEFAULT NULL,
  `usage_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `questions_author_id_index` (`author_id`),
  KEY `questions_based_on_question_id_index` (`based_on_question_id`),
  KEY `questions_discipline_id_index` (`discipline_id`),
  KEY `questions_subject_id_index` (`subject_id`),
  KEY `questions_visibility_index` (`visibility`),
  CONSTRAINT `questions_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_based_on_question_id_foreign` FOREIGN KEY (`based_on_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `questions_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `questions_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,2,NULL,'Questão 1 - ENEM 2023','TEXTO I\n\n¿QUÉ ME PASA?:\n¿PorQUE ME CUESTA TANTO ESTUDIAR?\n¿PORQUE ME CUESTA TANTO CONCENTRARME?\n¿PoRQUE……\n¿PORQUÉ…..\n¿PORQUÉ NO CONSIGO APRENDER COMO LOS DEMÁS?\n\n! (https://enem.dev/broken-image.svg)\n\nDisponível em: www.otrasvoceseneducacion.org. Acesso em: 8 nov. 2022.\n\nТЕХТО II\n\nIshaan Awashi es un niño de 8 años cuyo mundo está plagado de maravillas que nadie más parece apreciar: colores, peces, perros y cometas, que simplemente no son importantes en la vida de los adultos, que parecen más interesados en cosas como los deberes, las notas o la limpieza. E Ishaan parece no poder hacer nada bien en clase. Cuando los problemas que ocasiona superan a sus padres, es internado en un colegio para que le disciplinen. Las cosas no mejoran en el nuevo colegio, donde Ishaan tiene además que aceptar estar lejos de sus padres. Hasta que un dia, el nuevo profesor de arte, Ram Shankar Nikumbh, entra en escena, se interesa por el pequeño Ishaan y todo cambia.\n\nDisponível em: https://elfinalde.com. Acesso em: 26 out. 2021 (adaptado)\n\nO filme Como estrellas en la tierra aborda o tema da dislexia. Relacionando o cartaz do filme com a sinopse, constata-se que o(a)',NULL,'multiple_choice','private',4,4,'medio','medio','published',0,NULL,NULL,NULL,NULL,NULL,'API ENEM','https://api.enem.dev/v1/exams/2023/questions/1?language=espanhol','ENEM 2023 Q1 [espanhol]',1,'2026-03-27 00:58:45','2026-03-27 01:44:38'),(2,2,1,'Questão 1 - ENEM 2023 (copia)','TEXTO I\n\n¿QUÉ ME PASA?:\n¿PorQUE ME CUESTA TANTO ESTUDIAR?\n¿PORQUE ME CUESTA TANTO CONCENTRARME?\n¿PoRQUE……\n¿PORQUÉ…..\n¿PORQUÉ NO CONSIGO APRENDER COMO LOS DEMÁS?\n\n! (https://enem.dev/broken-image.svg)\n\nDisponível em: www.otrasvoceseneducacion.org. Acesso em: 8 nov. 2022.\n\nТЕХТО II\n\nIshaan Awashi es un niño de 8 años cuyo mundo está plagado de maravillas que nadie más parece apreciar: colores, peces, perros y cometas, que simplemente no son importantes en la vida de los adultos, que parecen más interesados en cosas como los deberes, las notas o la limpieza. E Ishaan parece no poder hacer nada bien en clase. Cuando los problemas que ocasiona superan a sus padres, es internado en un colegio para que le disciplinen. Las cosas no mejoran en el nuevo colegio, donde Ishaan tiene además que aceptar estar lejos de sus padres. Hasta que un dia, el nuevo profesor de arte, Ram Shankar Nikumbh, entra en escena, se interesa por el pequeño Ishaan y todo cambia.\n\nDisponível em: https://elfinalde.com. Acesso em: 26 out. 2021 (adaptado)\n\nO filme Como estrellas en la tierra aborda o tema da dislexia. Relacionando o cartaz do filme com a sinopse, constata-se que o(a)',NULL,'multiple_choice','private',4,4,'medio','medio','published',0,NULL,NULL,NULL,NULL,NULL,'API ENEM','https://api.enem.dev/v1/exams/2023/questions/1?language=espanhol','ENEM 2023 Q1 [espanhol]',0,'2026-03-27 03:42:52','2026-03-27 03:42:52'),(3,2,1,'Questão 1 - ENEM 2023 (copia)','TEXTO I\r\n\r\n¿QUÉ ME PASA?:\r\n¿PorQUE ME CUESTA TANTO ESTUDIAR?\r\n¿PORQUE ME CUESTA TANTO CONCENTRARME?\r\n¿PoRQUE……\r\n¿PORQUÉ…..\r\n¿PORQUÉ NO CONSIGO APRENDER COMO LOS DEMÁS?\r\n\r\n! (https://enem.dev/broken-image.svg)\r\n\r\nDisponível em: www.otrasvoceseneducacion.org. Acesso em: 8 nov. 2022.\r\n\r\nТЕХТО II\r\n\r\nIshaan Awashi es un niño de 8 años cuyo mundo está plagado de maravillas que nadie más parece apreciar: colores, peces, perros y cometas, que simplemente no son importantes en la vida de los adultos, que parecen más interesados en cosas como los deberes, las notas o la limpieza. E Ishaan parece no poder hacer nada bien en clase. Cuando los problemas que ocasiona superan a sus padres, es internado en un colegio para que le disciplinen. Las cosas no mejoran en el nuevo colegio, donde Ishaan tiene además que aceptar estar lejos de sus padres. Hasta que un dia, el nuevo profesor de arte, Ram Shankar Nikumbh, entra en escena, se interesa por el pequeño Ishaan y todo cambia.\r\n\r\nDisponível em: https://elfinalde.com. Acesso em: 26 out. 2021 (adaptado)\r\n\r\nO filme Como estrellas en la tierra aborda o tema da dislexia. Relacionando o cartaz do filme com a sinopse, constata-se que o(a)',NULL,'multiple_choice','public',4,4,'medio','medio','published',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ENEM 2023 Q1 [espanhol]',0,'2026-03-27 03:42:55','2026-03-27 03:43:17'),(4,2,1,'Questão 1 - ENEM 2023 (copia)','TEXTO I\n\n¿QUÉ ME PASA?:\n¿PorQUE ME CUESTA TANTO ESTUDIAR?\n¿PORQUE ME CUESTA TANTO CONCENTRARME?\n¿PoRQUE……\n¿PORQUÉ…..\n¿PORQUÉ NO CONSIGO APRENDER COMO LOS DEMÁS?\n\n! (https://enem.dev/broken-image.svg)\n\nDisponível em: www.otrasvoceseneducacion.org. Acesso em: 8 nov. 2022.\n\nТЕХТО II\n\nIshaan Awashi es un niño de 8 años cuyo mundo está plagado de maravillas que nadie más parece apreciar: colores, peces, perros y cometas, que simplemente no son importantes en la vida de los adultos, que parecen más interesados en cosas como los deberes, las notas o la limpieza. E Ishaan parece no poder hacer nada bien en clase. Cuando los problemas que ocasiona superan a sus padres, es internado en un colegio para que le disciplinen. Las cosas no mejoran en el nuevo colegio, donde Ishaan tiene además que aceptar estar lejos de sus padres. Hasta que un dia, el nuevo profesor de arte, Ram Shankar Nikumbh, entra en escena, se interesa por el pequeño Ishaan y todo cambia.\n\nDisponível em: https://elfinalde.com. Acesso em: 26 out. 2021 (adaptado)\n\nO filme Como estrellas en la tierra aborda o tema da dislexia. Relacionando o cartaz do filme com a sinopse, constata-se que o(a)',NULL,'multiple_choice','private',4,4,'medio','medio','published',0,NULL,NULL,NULL,NULL,NULL,'API ENEM','https://api.enem.dev/v1/exams/2023/questions/1?language=espanhol','ENEM 2023 Q1 [espanhol]',0,'2026-03-27 03:43:02','2026-03-27 03:43:02'),(5,3,NULL,'Em uma escola, foi distribuída uma pizza durante uma atividade.','- João comeu \\frac{1}{4} da pizza.\n\n- Maria comeu \\frac{2}{8} da pizza.\n\n- Pedro comeu \\frac{1}{2} da pizza.',NULL,'multiple_choice','public',1,1,'fundamental','facil','published',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-03-28 01:24:13','2026-03-28 01:32:09'),(6,3,NULL,'Uma empresa de entregas cobra uma taxa fixa mais um valor por quilômetro rodado. O valor total V(x)V(x)V(x), em reais, pago por uma entrega é dado pela função:','V(x)=5+2xV(x) = 5 + 2xV(x)=5+2x\n\nem que xxx representa a distância, em quilômetros, percorrida.\n\nCom base nessas informações, qual será o valor cobrado por uma entrega de 10 km?',NULL,'multiple_choice','private',1,1,'medio','medio','published',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-03-28 01:36:02','2026-03-28 01:36:02');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `discipline_id` int(10) unsigned NOT NULL,
  `name` varchar(140) NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_discipline_name_unique` (`discipline_id`,`name`),
  KEY `subjects_created_by_index` (`created_by`),
  CONSTRAINT `subjects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subjects_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,1,'Funcoes',1,'2026-03-27 00:55:55'),(2,1,'Porcentagem',1,'2026-03-27 00:55:55'),(3,2,'Leitura e interpretacao',1,'2026-03-27 00:55:55'),(4,4,'ENEM',2,'2026-03-27 00:58:45');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('master_admin','local_admin','xerox','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Master Quest','master@quest.local','$2y$10$4uy5xP2kTuuxNHQRA9j2g.K6rFnszj5gHt8rJBcjgOP3ZjV.qsVHC','master_admin','2026-03-27 00:55:55','2026-03-27 00:55:55'),(2,'Celo','cellusmat@gmail.com','$2y$10$fktHe0fGZu77zjrFXbDrtug8bc.9fOHkyi/UTk6sdo3GmgVzxJZn2','xerox','2026-03-27 00:56:58','2026-03-28 00:23:10'),(3,'Jéssica Fernandes','jessifernandes0@gmail.com','$2y$10$evnt6lJlNv722/2oa/J2kuJ1stswR1DkPc/oUkr7DyWoiJxtkmsum','user','2026-03-28 00:45:15','2026-03-28 00:45:15'),(4,'Marcelo Botura','mbsfoz@gmail.com','$2y$10$TxothqCQg0yqPG.0ZcgU7e6M6CO6F528.pcTBNrMKZiukXJ1sJ4sa','user','2026-03-25 07:15:29','2026-03-25 07:15:29'),(5,'Camila de Oliveira','bueno.camila@escola.pr.gov.br','$2y$10$mtEefQsaGBU2MYCNfZcqSuF0IfTctSd4Fj/6NqiGSyuwm/2Q4BD7.','user','2026-03-27 18:39:35','2026-03-27 18:39:35'),(6,'Marta Aparecida Ferreira','mart_ferreira@hotmail.com','$2y$10$M0i2cZPVkoX55obzwc9fo.4f5B59FgmY1klVHm7wzakj1o8WnyI/q','user','2026-03-27 19:04:03','2026-03-27 19:04:03'),(7,'Darci Marques','marques12342008@hotmail.com','$2y$10$sPxm/J1FTDqvNxgFvxL1jeWpLYo/Q4C6Ixvoqc3QVyXvu9b9Ij6la','user','2026-03-27 22:04:59','2026-03-27 22:04:59');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-27 23:29:30
