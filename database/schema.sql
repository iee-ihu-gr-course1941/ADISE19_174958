-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.10-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             10.3.0.5771
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for blackjack
CREATE DATABASE IF NOT EXISTS `blackjack` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `blackjack`;

-- Dumping structure for table blackjack.bets
CREATE TABLE IF NOT EXISTS `bets` (
  `token` varchar(34) NOT NULL,
  `amount` bigint(20) NOT NULL,
  PRIMARY KEY (`token`,`amount`),
  CONSTRAINT `fk_bets_players` FOREIGN KEY (`token`) REFERENCES `players` (`token`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `check_amount_positive` CHECK (`amount` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.bets: ~0 rows (approximately)
DELETE FROM `bets`;
/*!40000 ALTER TABLE `bets` DISABLE KEYS */;
/*!40000 ALTER TABLE `bets` ENABLE KEYS */;

-- Dumping structure for table blackjack.cards
CREATE TABLE IF NOT EXISTS `cards` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.cards: ~52 rows (approximately)
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

-- Dumping structure for table blackjack.cards_images
CREATE TABLE IF NOT EXISTS `cards_images` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  `image_name` varchar(30) NOT NULL DEFAULT concat(`card_value`,'_',`card_color`),
  PRIMARY KEY (`card_color`,`card_value`,`image_name`),
  UNIQUE KEY `unique_cards_images_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_cards_images_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table blackjack.cards_images: ~52 rows (approximately)
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

-- Dumping structure for table blackjack.cards_points
CREATE TABLE IF NOT EXISTS `cards_points` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  `points` tinyint(4) NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`),
  CONSTRAINT `FK_CARDS_POINTS_CARDS` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `CHECK_CARDS_POINTS_POINTS_GREATER_THAN_ZERO` CHECK (`points` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.cards_points: ~52 rows (approximately)
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

-- Dumping structure for table blackjack.computer_hands
CREATE TABLE IF NOT EXISTS `computer_hands` (
  `game_id` int(11) NOT NULL,
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`game_id`,`card_color`,`card_value`),
  KEY `fk_computer_hands_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_computer_hands_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_player_hands_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.computer_hands: ~0 rows (approximately)
DELETE FROM `computer_hands`;
/*!40000 ALTER TABLE `computer_hands` DISABLE KEYS */;
/*!40000 ALTER TABLE `computer_hands` ENABLE KEYS */;

-- Dumping structure for table blackjack.games
CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `games_status` enum('initialized','betting','players_turn','computer_turn','end_game') DEFAULT NULL,
  `points` tinyint(4) DEFAULT 0,
  `nums_of_players` tinyint(4) DEFAULT 0,
  `initialized` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`game_id`),
  CONSTRAINT `games_number_of_players_maximum_3` CHECK (`nums_of_players` <= 3)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.games: ~0 rows (approximately)
DELETE FROM `games`;
/*!40000 ALTER TABLE `games` DISABLE KEYS */;
/*!40000 ALTER TABLE `games` ENABLE KEYS */;

-- Dumping structure for table blackjack.game_cards
CREATE TABLE IF NOT EXISTS `game_cards` (
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  `game_id` int(11) NOT NULL,
  `taken` tinyint(1) NOT NULL,
  PRIMARY KEY (`card_color`,`card_value`,`game_id`),
  KEY `fk_game_cards_games` (`game_id`),
  CONSTRAINT `fk_game_cards_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_game_cards_games` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table blackjack.game_cards: ~0 rows (approximately)
DELETE FROM `game_cards`;
/*!40000 ALTER TABLE `game_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_cards` ENABLE KEYS */;

-- Dumping structure for table blackjack.my_users
CREATE TABLE IF NOT EXISTS `my_users` (
  `user_name` varchar(30) NOT NULL,
  `pass_word` varchar(30) NOT NULL,
  `balance` bigint(20) DEFAULT 100,
  PRIMARY KEY (`user_name`),
  CONSTRAINT `pass_word_length` CHECK (octet_length(`pass_word`) > 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.my_users: ~0 rows (approximately)
DELETE FROM `my_users`;
/*!40000 ALTER TABLE `my_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `my_users` ENABLE KEYS */;

-- Dumping structure for table blackjack.players
CREATE TABLE IF NOT EXISTS `players` (
  `game_id` int(11) NOT NULL,
  `token` varchar(34) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `last_action` datetime DEFAULT current_timestamp(),
  `player_status` enum('waiting','hitting','betting','overflow','done_betting','done_hitting','left_game') NOT NULL DEFAULT 'waiting',
  `points` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`token`,`game_id`,`user_name`),
  UNIQUE KEY `token` (`token`),
  KEY `fk_game_id` (`game_id`),
  KEY `fk_user_name` (`user_name`),
  CONSTRAINT `fk_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_name` FOREIGN KEY (`user_name`) REFERENCES `my_users` (`user_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.players: ~0 rows (approximately)
DELETE FROM `players`;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
/*!40000 ALTER TABLE `players` ENABLE KEYS */;

-- Dumping structure for table blackjack.player_hands
CREATE TABLE IF NOT EXISTS `player_hands` (
  `token` varchar(34) NOT NULL,
  `card_color` enum('C','D','H','S') NOT NULL,
  `card_value` enum('2','3','4','5','6','7','8','9','10','J','Q','K','A') NOT NULL,
  PRIMARY KEY (`token`,`card_color`,`card_value`),
  KEY `fk_cards` (`card_color`,`card_value`),
  CONSTRAINT `fk_cards` FOREIGN KEY (`card_color`, `card_value`) REFERENCES `cards` (`card_color`, `card_value`),
  CONSTRAINT `fk_player_hands_token` FOREIGN KEY (`token`) REFERENCES `players` (`token`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table blackjack.player_hands: ~0 rows (approximately)
DELETE FROM `player_hands`;
/*!40000 ALTER TABLE `player_hands` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_hands` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
