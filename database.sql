-- ============================================================
--  DriveEase Support Desk — Complete Database Schema
--  Run this in phpMyAdmin on InfinityFree to create all tables
-- ============================================================

-- 1. CUSTOMERS TABLE (Front-end authentication)
-- ============================================================
CREATE TABLE IF NOT EXISTS `customers` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(150)    NOT NULL,
    `email`             VARCHAR(255)    NOT NULL UNIQUE,
    `password`          VARCHAR(255)    DEFAULT NULL,
    `google_id`         VARCHAR(255)    DEFAULT NULL,
    `security_question` VARCHAR(255)    DEFAULT NULL,
    `security_answer`   VARCHAR(255)    DEFAULT NULL,
    `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. EMPLOYEES TABLE (Back-end authentication for admin interface)
-- ============================================================
CREATE TABLE IF NOT EXISTS `employees` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(150)    NOT NULL,
    `email`             VARCHAR(255)    NOT NULL UNIQUE,
    `password`          VARCHAR(255)    NOT NULL,
    `role`              ENUM('admin','support','manager') DEFAULT 'support',
    `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. FLEET TABLE
--    Stores the actual cars we own/manage.
--    car_api_trim_id links this car to CarAPI for fetching make/model/year/specs.
--    rent_cost_per_day and status are managed by us — CarAPI does NOT provide these.
-- ============================================================
CREATE TABLE IF NOT EXISTS `fleet` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `car_api_trim_id`   INT UNSIGNED    DEFAULT NULL,      -- CarAPI trim ID for catalog data
    `car_api_year`      SMALLINT UNSIGNED DEFAULT NULL,    -- Year for display / CarAPI filter
    `car_label`         VARCHAR(255)    NOT NULL,          -- e.g. "2022 Toyota Camry LE"
    `plate`             VARCHAR(30)     NOT NULL UNIQUE,   -- Physical license plate
    `rent_cost_per_day` DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `status`            ENUM('available','booked','maintenance') DEFAULT 'available',
    `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. BOOKINGS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id`   INT UNSIGNED    NOT NULL,
    `fleet_id`      INT UNSIGNED    NOT NULL,
    `start_date`    DATE            NOT NULL,
    `end_date`      DATE            NOT NULL,
    `total_cost`    DECIMAL(10,2)   DEFAULT 0.00,
    `status`        ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fleet_id`)    REFERENCES `fleet`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. PAYMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `booking_id`    INT UNSIGNED    NOT NULL,
    `amount`        DECIMAL(10,2)   NOT NULL,
    `status`        ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    `payment_date`  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. MAINTENANCE TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `maintenance` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fleet_id`      INT UNSIGNED    NOT NULL,
    `description`   TEXT            NOT NULL,
    `cost`          DECIMAL(10,2)   DEFAULT 0.00,
    `status`        ENUM('scheduled','in_progress','completed') DEFAULT 'scheduled',
    `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`fleet_id`) REFERENCES `fleet`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. SUPPORT_TICKETS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id`   INT UNSIGNED    NOT NULL,
    `subject`       VARCHAR(255)    NOT NULL,
    `description`   TEXT            NOT NULL,
    `status`        ENUM('open','pending','resolved','closed') DEFAULT 'open',
    `priority`      ENUM('low','medium','high','urgent')       DEFAULT 'medium',
    `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. TICKET_COMMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `ticket_comments` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id`     INT UNSIGNED    NOT NULL,
    `customer_id`   INT UNSIGNED    DEFAULT NULL,
    `employee_id`   INT UNSIGNED    DEFAULT NULL,
    `comment`       TEXT            NOT NULL,
    `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`)   REFERENCES `support_tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`)       ON DELETE SET NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
