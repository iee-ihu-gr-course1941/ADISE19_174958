
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


CREATE DATABASE IF NOT EXISTS `blackjack` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `blackjack`;

CREATE TABLE IF NOT EXISTS `cards` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `cards`;
/*!40000 ALTER TABLE `cards` DISABLE KEYS */;
INSERT INTO `cards` (`card_color`, `card_value`) VALUES
	('C', '2'),
	('C', '3'),
	('C', '4'),
	('C', '5'),
	('C', '6'),
	('C', '7'),
	('C', '8'),
	('C', '9'),
	('C', '10'),
	('C', 'J'),
	('C', 'Q'),
	('C', 'K'),
	('C', 'A'),
	('D', '2'),
	('D', '3'),
	('D', '4'),
	('D', '5'),
	('D', '6'),
	('D', '7'),
	('D', '8'),
	('D', '9'),
	('D', '10'),
	('D', 'J'),
	('D', 'Q'),
	('D', 'K'),
	('D', 'A'),
	('H', '2'),
	('H', '3'),
	('H', '4'),
	('H', '5'),
	('H', '6'),
	('H', '7'),
	('H', '8'),
	('H', '9'),
	('H', '10'),
	('H', 'J'),
	('H', 'Q'),
	('H', 'K'),
	('H', 'A'),
	('S', '2'),
	('S', '3'),
	('S', '4'),
	('S', '5'),
	('S', '6'),
	('S', '7'),
	('S', '8'),
	('S', '9'),
	('S', '10'),
	('S', 'J'),
	('S', 'Q'),
	('S', 'K'),
	('S', 'A');
/*!40000 ALTER TABLE `cards` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `cards_images` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  `image_name` varchar(30) NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`,`image_name`),
  UNIQUE KEY `unique_cards_images_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_cards_images_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELETE FROM `cards_images`;
/*!40000 ALTER TABLE `cards_images` DISABLE KEYS */;
INSERT INTO `cards_images` (`card_color`, `card_value`, `image_name`) VALUES
	('C', '2', '2_C'),
	('C', '3', '3_C'),
	('C', '4', '4_C'),
	('C', '5', '5_C'),
	('C', '6', '6_C'),
	('C', '7', '7_C'),
	('C', '8', '8_C'),
	('C', '9', '9_C'),
	('C', '10', '10_C'),
	('C', 'J', 'J_C'),
	('C', 'Q', 'Q_C'),
	('C', 'K', 'K_C'),
	('C', 'A', 'A_C'),
	('D', '2', '2_D'),
	('D', '3', '3_D'),
	('D', '4', '4_D'),
	('D', '5', '5_D'),
	('D', '6', '6_D'),
	('D', '7', '7_D'),
	('D', '8', '8_D'),
	('D', '9', '9_D'),
	('D', '10', '10_D'),
	('D', 'J', 'J_D'),
	('D', 'Q', 'Q_D'),
	('D', 'K', 'K_D'),
	('D', 'A', 'A_D'),
	('H', '2', '2_H'),
	('H', '3', '3_H'),
	('H', '4', '4_H'),
	('H', '5', '5_H'),
	('H', '6', '6_H'),
	('H', '7', '7_H'),
	('H', '8', '8_H'),
	('H', '9', '9_H'),
	('H', '10', '10_H'),
	('H', 'J', 'J_H'),
	('H', 'Q', 'Q_H'),
	('H', 'K', 'K_H'),
	('H', 'A', 'A_H'),
	('S', '2', '2_S'),
	('S', '3', '3_S'),
	('S', '4', '4_S'),
	('S', '5', '5_S'),
	('S', '6', '6_S'),
	('S', '7', '7_S'),
	('S', '8', '8_S'),
	('S', '9', '9_S'),
	('S', '10', '10_S'),
	('S', 'J', 'J_S'),
	('S', 'Q', 'Q_S'),
	('S', 'K', 'K_S'),
	('S', 'A', 'A_S');
/*!40000 ALTER TABLE `cards_images` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `cards_points` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  `points` tinyint(4) NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`),
  CONSTRAINT `FK_CARDS_POINTS_CARDS` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `CHECK_CARDS_POINTS_POINTS_GREATER_THAN_ZERO` CHECK (`points` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `cards_points`;
/*!40000 ALTER TABLE `cards_points` DISABLE KEYS */;
INSERT INTO `cards_points` (`card_color`, `card_value`, `points`) VALUES
	('C', '2', 2),
	('C', '3', 3),
	('C', '4', 4),
	('C', '5', 5),
	('C', '6', 6),
	('C', '7', 7),
	('C', '8', 8),
	('C', '9', 9),
	('C', '10', 10),
	('C', 'J', 10),
	('C', 'Q', 10),
	('C', 'K', 10),
	('C', 'A', 11),
	('D', '2', 2),
	('D', '3', 3),
	('D', '4', 4),
	('D', '5', 5),
	('D', '6', 6),
	('D', '7', 7),
	('D', '8', 8),
	('D', '9', 9),
	('D', '10', 10),
	('D', 'J', 10),
	('D', 'Q', 10),
	('D', 'K', 10),
	('D', 'A', 11),
	('H', '2', 2),
	('H', '3', 3),
	('H', '4', 4),
	('H', '5', 5),
	('H', '6', 6),
	('H', '7', 7),
	('H', '8', 8),
	('H', '9', 9),
	('H', '10', 10),
	('H', 'J', 10),
	('H', 'Q', 10),
	('H', 'K', 10),
	('H', 'A', 11),
	('S', '2', 2),
	('S', '3', 3),
	('S', '4', 4),
	('S', '5', 5),
	('S', '6', 6),
	('S', '7', 7),
	('S', '8', 8),
	('S', '9', 9),
	('S', '10', 10),
	('S', 'J', 10),
	('S', 'Q', 10),
	('S', 'K', 10),
	('S', 'A', 11);
/*!40000 ALTER TABLE `cards_points` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `computer_hands` (
  `game_id` int(11) NOT NULL,
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`game_id`,`card_color`,`card_value`),
  KEY `fk_computer_hands_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_computer_hands_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_player_hands_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `computer_hands`;
/*!40000 ALTER TABLE `computer_hands` DISABLE KEYS */;
/*!40000 ALTER TABLE `computer_hands` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `games_status` enum('betting','plaers_turn','computer_turn') DEFAULT NULL,
  `points` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `games`;
/*!40000 ALTER TABLE `games` DISABLE KEYS */;
/*!40000 ALTER TABLE `games` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `my_users` (
  `user_name` varchar(30) NOT NULL,
  `pass_word` varchar(30) NOT NULL,
  PRIMARY KEY (`user_name`),
  CONSTRAINT `pass_word_length` CHECK (octet_length(`pass_word`) > 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `my_users` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `players` (
  `user_name` varchar(30) NOT NULL,
  `game_id` int(11) NOT NULL,
  `token` varchar(1000) NOT NULL,
  `last_action` datetime DEFAULT NULL,
  `player_status` enum('waiting','hitting','betting','overflow','done') NOT NULL,
  `points` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`user_name`,`game_id`),
  UNIQUE KEY `unique_user_name` (`user_name`),
  KEY `fk_game_id` (`game_id`),
  CONSTRAINT `fk_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_name` FOREIGN KEY (`user_name`) REFERENCES `my_users` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `players`;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
/*!40000 ALTER TABLE `players` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `player_hands` (
  `user_name` varchar(30) NOT NULL,
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`user_name`,`card_color`,`card_value`),
  KEY `fk_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_player_hands_user_name` FOREIGN KEY (`user_name`) REFERENCES `players` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `player_hands`;
/*!40000 ALTER TABLE `player_hands` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_hands` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
