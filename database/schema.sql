-- MySQL dump 10.13  Distrib 8.0.18, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: blackjack
-- ------------------------------------------------------
-- Server version	8.0.18

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cards` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cards`
--

LOCK TABLES `cards` WRITE;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
INSERT INTO `cards` VALUES ('C','2'),('C','3'),('C','4'),('C','5'),('C','6'),('C','7'),('C','8'),('C','9'),('C','10'),('C','J'),('C','Q'),('C','K'),('C','A'),('D','2'),('D','3'),('D','4'),('D','5'),('D','6'),('D','7'),('D','8'),('D','9'),('D','10'),('D','J'),('D','Q'),('D','K'),('D','A'),('H','2'),('H','3'),('H','4'),('H','5'),('H','6'),('H','7'),('H','8'),('H','9'),('H','10'),('H','J'),('H','Q'),('H','K'),('H','A'),('S','2'),('S','3'),('S','4'),('S','5'),('S','6'),('S','7'),('S','8'),('S','9'),('S','10'),('S','J'),('S','Q'),('S','K'),('S','A');
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cards_points`
--

DROP TABLE IF EXISTS `cards_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cards_points` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  `points` tinyint(4) NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`),
  CONSTRAINT `FK_CARDS_POINTS_CARDS` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `CHECK_CARDS_POINTS_POINTS_GREATER_THAN_ZERO` CHECK ((`points` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cards_points`
--

LOCK TABLES `cards_points` WRITE;
/*!40000 ALTER TABLE `cards_points` DISABLE KEYS */;
INSERT INTO `cards_points` VALUES ('C','2',2),('C','3',3),('C','4',4),('C','5',5),('C','6',6),('C','7',7),('C','8',8),('C','9',9),('C','10',10),('C','J',10),('C','Q',10),('C','K',10),('C','A',11),('D','2',2),('D','3',3),('D','4',4),('D','5',5),('D','6',6),('D','7',7),('D','8',8),('D','9',9),('D','10',10),('D','J',10),('D','Q',10),('D','K',10),('D','A',11),('H','2',2),('H','3',3),('H','4',4),('H','5',5),('H','6',6),('H','7',7),('H','8',8),('H','9',9),('H','10',10),('H','J',10),('H','Q',10),('H','K',10),('H','A',11),('S','2',2),('S','3',3),('S','4',4),('S','5',5),('S','6',6),('S','7',7),('S','8',8),('S','9',9),('S','10',10),('S','J',10),('S','Q',10),('S','K',10),('S','A',11);
/*!40000 ALTER TABLE `cards_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `computer_hands`
--

DROP TABLE IF EXISTS `computer_hands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `computer_hands` (
  `game_id` int(11) NOT NULL,
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`game_id`,`card_color`,`card_value`),
  KEY `fk_computer_hands_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_computer_hands_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_player_hands_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `computer_hands`
--

LOCK TABLES `computer_hands` WRITE;
/*!40000 ALTER TABLE `computer_hands` DISABLE KEYS */;
/*!40000 ALTER TABLE `computer_hands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `games_status` enum('betting','plaers_turn','computer_turn') DEFAULT NULL,
  `points` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `games`
--

LOCK TABLES `games` WRITE;
/*!40000 ALTER TABLE `games` DISABLE KEYS */;
/*!40000 ALTER TABLE `games` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `my_users`
--

DROP TABLE IF EXISTS `my_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `my_users` (
  `user_name` varchar(30) NOT NULL,
  `pass_word` varchar(30) NOT NULL,
  PRIMARY KEY (`user_name`),
  CONSTRAINT `pass_word_length` CHECK ((length(`pass_word`) > 8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `my_users`
--

LOCK TABLES `my_users` WRITE;
/*!40000 ALTER TABLE `my_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `my_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_hands`
--

DROP TABLE IF EXISTS `player_hands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `player_hands` (
  `user_name` varchar(30) NOT NULL,
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`user_name`,`card_color`,`card_value`),
  KEY `fk_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_player_hands_user_name` FOREIGN KEY (`user_name`) REFERENCES `players` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_hands`
--

LOCK TABLES `player_hands` WRITE;
/*!40000 ALTER TABLE `player_hands` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_hands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `players` (
  `user_name` varchar(30) NOT NULL,
  `game_id` int(11) NOT NULL,
  `token` varchar(1000) NOT NULL,
  `last_action` datetime DEFAULT NULL,
  `player_status` enum('waiting','hitting','betting','overflow','done') NOT NULL,
  `points` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`user_name`,`game_id`),
  UNIQUE KEY `unique_user_name` (`user_name`),
  KEY `fk_game_id` (`game_id`),
  CONSTRAINT `fk_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_name` FOREIGN KEY (`user_name`) REFERENCES `my_users` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-12-13  7:15:18
