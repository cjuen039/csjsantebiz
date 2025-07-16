-- Create database
CREATE DATABASE IF NOT EXISTS `work_priorities` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `work_priorities`;

-- --------------------------------------------
-- Table: users
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `security_question` VARCHAR(255) DEFAULT NULL,
  `security_answer` TEXT NOT NULL,
  `security_answer_hash` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------
-- Table: tasks
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `quadrant` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending','completed','expired') DEFAULT 'pending',
  `due_date` DATE DEFAULT NULL,
  `reminder_datetime` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tasks_user` (`user_id`),
  CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------
-- Table: task_completed
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS `task_completed` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `completed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('completed','pending','expired') NOT NULL DEFAULT 'completed',
  PRIMARY KEY (`id`),
  KEY `fk_task_completed_user` (`user_id`),
  CONSTRAINT `fk_task_completed_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
