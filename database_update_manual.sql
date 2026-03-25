USE `u488847015_quest_baseDado`;

-- Faca backup antes de executar este arquivo.
-- Este script atualiza a base antiga do Quest para a estrutura nova.

CREATE TABLE IF NOT EXISTS `disciplines` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `created_by` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `disciplines_name_unique` (`name`),
    KEY `disciplines_created_by_index` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `discipline_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(140) NOT NULL,
    `created_by` INT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subjects_discipline_name_unique` (`discipline_id`, `name`),
    KEY `subjects_created_by_index` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `disciplines`
    ADD CONSTRAINT `disciplines_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `subjects`
    ADD CONSTRAINT `subjects_discipline_id_foreign`
        FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `subjects_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `questions`
    ADD COLUMN `based_on_question_id` INT UNSIGNED NULL DEFAULT NULL AFTER `author_id`,
    ADD COLUMN `prompt_image_url` VARCHAR(500) NULL DEFAULT NULL AFTER `prompt`,
    MODIFY COLUMN `question_type` ENUM('multiple_choice', 'discursive', 'drawing', 'true_false') NOT NULL,
    ADD COLUMN `visibility` ENUM('private', 'public') NOT NULL DEFAULT 'private' AFTER `question_type`,
    ADD COLUMN `discipline_id` INT UNSIGNED NULL DEFAULT NULL AFTER `visibility`,
    ADD COLUMN `subject_id` INT UNSIGNED NULL DEFAULT NULL AFTER `discipline_id`,
    ADD COLUMN `education_level` ENUM('fundamental', 'medio', 'tecnico', 'superior') NOT NULL DEFAULT 'medio' AFTER `subject_id`,
    ADD COLUMN `allow_multiple_correct` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN `response_lines` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `discursive_answer`,
    ADD COLUMN `drawing_size` ENUM('small', 'medium', 'large') NULL DEFAULT NULL AFTER `response_lines`,
    ADD COLUMN `usage_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `true_false_answer`;

ALTER TABLE `questions`
    ADD KEY `questions_based_on_question_id_index` (`based_on_question_id`),
    ADD KEY `questions_discipline_id_index` (`discipline_id`),
    ADD KEY `questions_subject_id_index` (`subject_id`),
    ADD KEY `questions_visibility_index` (`visibility`);

ALTER TABLE `questions`
    ADD CONSTRAINT `questions_based_on_question_id_foreign`
        FOREIGN KEY (`based_on_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `questions_discipline_id_foreign`
        FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `questions_subject_id_foreign`
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL;

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

-- Ajustes iniciais para dados antigos:
UPDATE `questions`
SET `visibility` = 'private'
WHERE `visibility` IS NULL;

UPDATE `questions`
SET `response_lines` = 5
WHERE `question_type` = 'discursive'
  AND `response_lines` IS NULL;

UPDATE `questions`
SET `drawing_size` = 'medium'
WHERE `question_type` = 'drawing'
  AND `drawing_size` IS NULL;
