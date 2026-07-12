-- ============================================================
--  DriveEase Support Desk — Complete Database Schema
--  Run this in phpMyAdmin on InfinityFree to create all tables
-- ============================================================

-- 1. USERS TABLE
--    Stores all registered users (email/password & Google OAuth)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fullname`          VARCHAR(150)    NOT NULL,
    `username`          VARCHAR(100)    NOT NULL,
    `email`             VARCHAR(255)    NOT NULL UNIQUE,
    `password`          VARCHAR(255)    DEFAULT NULL,          -- NULL for Google-only accounts
    `security_question` VARCHAR(255)    DEFAULT NULL,
    `security_answer`   VARCHAR(255)    DEFAULT NULL,
    `provider`          VARCHAR(20)     DEFAULT 'email',       -- 'email' or 'google'
    `role`              ENUM('admin','agent','customer') DEFAULT 'customer',
    `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. FLEET TABLE
--    Stores vehicles managed by the support desk
-- ============================================================
CREATE TABLE IF NOT EXISTS `fleet` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `make`         VARCHAR(100)    NOT NULL,
    `model`        VARCHAR(100)    NOT NULL,
    `year`         YEAR            NOT NULL,
    `plate`        VARCHAR(30)     NOT NULL UNIQUE,
    `status`       ENUM('available','in_use','maintenance') DEFAULT 'available',
    `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. BOOKINGS TABLE
--    Stores vehicle rental bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT UNSIGNED    NOT NULL,
    `fleet_id`     INT UNSIGNED    NOT NULL,
    `start_date`   DATE            NOT NULL,
    `end_date`     DATE            NOT NULL,
    `status`       ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fleet_id`) REFERENCES `fleet`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TICKETS TABLE
--    Support tickets raised by customers
-- ============================================================
CREATE TABLE IF NOT EXISTS `tickets` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT UNSIGNED    NOT NULL,
    `subject`      VARCHAR(255)    NOT NULL,
    `description`  TEXT            NOT NULL,
    `status`       ENUM('open','pending','resolved','closed') DEFAULT 'open',
    `priority`     ENUM('low','medium','high','urgent')       DEFAULT 'medium',
    `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TICKET COMMENTS TABLE
--    Comments/replies on support tickets
-- ============================================================
CREATE TABLE IF NOT EXISTS `ticket_comments` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id`  INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `comment`    TEXT         NOT NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
