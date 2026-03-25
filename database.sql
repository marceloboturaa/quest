CREATE DATABASE IF NOT EXISTS `u488847015_quest_baseDado`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `u488847015_quest_baseDado`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(180) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('master_admin', 'local_admin', 'user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `password_resets_token_hash_unique` (`token_hash`),
    KEY `password_resets_user_id_index` (`user_id`),
    CONSTRAINT `password_resets_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(180) NOT NULL,
    `prompt` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice', 'discursive', 'true_false') NOT NULL,
    `difficulty` ENUM('facil', 'medio', 'dificil') NOT NULL DEFAULT 'medio',
    `status` ENUM('draft', 'review', 'published') NOT NULL DEFAULT 'draft',
    `discursive_answer` TEXT NULL,
    `true_false_answer` TINYINT(1) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `questions_author_id_index` (`author_id`),
    CONSTRAINT `questions_author_id_foreign`
        FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `question_options` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` INT UNSIGNED NOT NULL,
    `option_text` VARCHAR(255) NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `display_order` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `question_options_question_id_index` (`question_id`),
    CONSTRAINT `question_options_question_id_foreign`
        FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`name`, `email`, `password_hash`, `role`)
SELECT 'Master Quest', 'master@quest.local', '$2y$10$4uy5xP2kTuuxNHQRA9j2g.K6rFnszj5gHt8rJBcjgOP3ZjV.qsVHC', 'master_admin'
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'master@quest.local'
);
