CREATE DATABASE doingsdone
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE doingsdone;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(128) NOT NULL UNIQUE,
    password CHAR(64),
    name VARCHAR(128),
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    dt_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_done DATE,
    done TINYINT DEFAULT 0,
    file VARCHAR(255),
    user_id INT,
    project_id INT,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (project_id) REFERENCES projects (id)
);

CREATE INDEX in_email ON users(email);
CREATE INDEX in_name ON tasks(name);
