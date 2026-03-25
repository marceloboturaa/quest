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

CREATE TABLE IF NOT EXISTS `disciplines` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `created_by` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `disciplines_name_unique` (`name`),
    KEY `disciplines_created_by_index` (`created_by`),
    CONSTRAINT `disciplines_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `discipline_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(140) NOT NULL,
    `created_by` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subjects_discipline_name_unique` (`discipline_id`, `name`),
    KEY `subjects_created_by_index` (`created_by`),
    CONSTRAINT `subjects_discipline_id_foreign`
        FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE CASCADE,
    CONSTRAINT `subjects_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id` INT UNSIGNED NOT NULL,
    `based_on_question_id` INT UNSIGNED NULL DEFAULT NULL,
    `title` VARCHAR(180) NOT NULL,
    `prompt` TEXT NOT NULL,
    `prompt_image_url` VARCHAR(500) NULL DEFAULT NULL,
    `question_type` ENUM('multiple_choice', 'discursive', 'drawing', 'true_false') NOT NULL,
    `visibility` ENUM('private', 'public') NOT NULL DEFAULT 'private',
    `discipline_id` INT UNSIGNED NULL DEFAULT NULL,
    `subject_id` INT UNSIGNED NULL DEFAULT NULL,
    `education_level` ENUM('fundamental', 'medio', 'tecnico', 'superior') NOT NULL DEFAULT 'medio',
    `difficulty` ENUM('facil', 'medio', 'dificil') NOT NULL DEFAULT 'medio',
    `status` ENUM('draft', 'review', 'published') NOT NULL DEFAULT 'draft',
    `allow_multiple_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `discursive_answer` TEXT NULL,
    `response_lines` SMALLINT UNSIGNED NULL DEFAULT NULL,
    `drawing_size` ENUM('small', 'medium', 'large') NULL DEFAULT NULL,
    `true_false_answer` TINYINT(1) NULL DEFAULT NULL,
    `usage_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `questions_author_id_index` (`author_id`),
    KEY `questions_based_on_question_id_index` (`based_on_question_id`),
    KEY `questions_discipline_id_index` (`discipline_id`),
    KEY `questions_subject_id_index` (`subject_id`),
    KEY `questions_visibility_index` (`visibility`),
    CONSTRAINT `questions_author_id_foreign`
        FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `questions_based_on_question_id_foreign`
        FOREIGN KEY (`based_on_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `questions_discipline_id_foreign`
        FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE SET NULL,
    CONSTRAINT `questions_subject_id_foreign`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
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

CREATE TABLE IF NOT EXISTS `question_favorites` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `question_favorites_unique` (`question_id`, `user_id`),
    KEY `question_favorites_user_id_index` (`user_id`),
    CONSTRAINT `question_favorites_question_id_foreign`
        FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `question_favorites_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exams` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(180) NOT NULL,
    `instructions` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `exams_user_id_index` (`user_id`),
    CONSTRAINT `exams_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exam_questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `exam_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NOT NULL,
    `display_order` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `exam_questions_unique` (`exam_id`, `question_id`),
    KEY `exam_questions_question_id_index` (`question_id`),
    CONSTRAINT `exam_questions_exam_id_foreign`
        FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `exam_questions_question_id_foreign`
        FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`name`, `email`, `password_hash`, `role`)
SELECT 'Master Quest', 'master@quest.local', '$2y$10$4uy5xP2kTuuxNHQRA9j2g.K6rFnszj5gHt8rJBcjgOP3ZjV.qsVHC', 'master_admin'
WHERE NOT EXISTS (
    SELECT 1 FROM `users` WHERE `email` = 'master@quest.local'
);

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Matematica', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Matematica'
);

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Portugues', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Portugues'
);

INSERT INTO `disciplines` (`name`, `created_by`)
SELECT 'Fisica', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `disciplines` WHERE `name` = 'Fisica'
);

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT disciplines.id, 'Funcoes', 1
FROM disciplines
WHERE disciplines.name = 'Matematica'
  AND NOT EXISTS (
      SELECT 1 FROM `subjects`
      WHERE `subjects`.`discipline_id` = disciplines.id AND `subjects`.`name` = 'Funcoes'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT disciplines.id, 'Porcentagem', 1
FROM disciplines
WHERE disciplines.name = 'Matematica'
  AND NOT EXISTS (
      SELECT 1 FROM `subjects`
      WHERE `subjects`.`discipline_id` = disciplines.id AND `subjects`.`name` = 'Porcentagem'
  );

INSERT INTO `subjects` (`discipline_id`, `name`, `created_by`)
SELECT disciplines.id, 'Leitura e interpretacao', 1
FROM disciplines
WHERE disciplines.name = 'Portugues'
  AND NOT EXISTS (
      SELECT 1 FROM `subjects`
      WHERE `subjects`.`discipline_id` = disciplines.id AND `subjects`.`name` = 'Leitura e interpretacao'
  );
