CREATE DATABASE IF NOT EXISTS vulcan_dziennik CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci;
USE vulcan_dziennik;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    class_name VARCHAR(20) NOT NULL,
    role ENUM('uczen','nauczyciel','admin') NOT NULL DEFAULT 'uczen',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
