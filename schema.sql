CREATE DATABASE doingsdone
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE doingsdone;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email CHAR(128) NOT NULL UNIQUE,
    password CHAR(64),
    name CHAR(128),
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name CHAR(128) NOT NULL,
    user_id INT
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name CHAR(255),
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_done TIMESTAMP,
    done INT DEFAULT 0,
    file CHAR(255),
    user_id INT,
    project_id INT
);

CREATE INDEX email ON users(email);
CREATE INDEX name ON tasks(name);
