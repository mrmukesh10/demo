-- ═══════════════════════════════════════════════════════════
--  NexCore Solutions — Database Schema
--  Run this SQL in phpMyAdmin or MySQL CLI before launching
-- ═══════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS nexcore_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE nexcore_db;

CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(180)  NOT NULL UNIQUE,
    phone       VARCHAR(20)   DEFAULT NULL,
    department  VARCHAR(80)   DEFAULT NULL,
    job_title   VARCHAR(80)   DEFAULT NULL,
    bio         TEXT          DEFAULT NULL,
    password    VARCHAR(255)  NOT NULL,           -- bcrypt hash
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
