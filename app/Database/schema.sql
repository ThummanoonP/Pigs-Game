CREATE DATABASE IF NOT EXISTS piggame CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE piggame;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) UNIQUE NOT NULL,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_banned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level INT DEFAULT 1,
    exp INT DEFAULT 0,
    coin INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pigs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    level INT DEFAULT 1,
    exp INT DEFAULT 0,
    last_fed_at DATETIME NULL,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    qty INT NOT NULL DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS daily_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    target INT NOT NULL,
    reward_coin INT NOT NULL,
    active_date DATE NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quest_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    quest_id INT NOT NULL,
    for_date DATE NOT NULL,
    progress INT NOT NULL DEFAULT 0,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    claimed TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_player_quest_date (player_id, quest_id, for_date),
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES daily_quests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `val` VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


INSERT INTO settings(`key`,`val`) VALUES ('feed_cooldown_seconds','10')
ON DUPLICATE KEY UPDATE val=VALUES(val);


INSERT INTO daily_quests(name,target,reward_coin,active_date) VALUES
    ('ให้อาหาร 5 ครั้ง', 5, 20, NULL),
    ('ให้อาหาร 10 ครั้ง', 10, 50, NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name), target=VALUES(target), reward_coin=VALUES(reward_coin), active_date=VALUES(active_date);
