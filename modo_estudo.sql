ALTER TABLE `users`
    MODIFY `role` ENUM('master_admin', 'local_admin', 'professor', 'xerox', 'user', 'aluno') NOT NULL DEFAULT 'user';

ALTER TABLE `questions`
    ADD COLUMN `explanation` TEXT NULL DEFAULT NULL AFTER `source_reference`;

CREATE TABLE IF NOT EXISTS `answers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NOT NULL,
    `resposta` VARCHAR(10) NOT NULL,
    `correta` TINYINT(1) NOT NULL DEFAULT 0,
    `data` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `answers_user_id_index` (`user_id`),
    KEY `answers_question_id_index` (`question_id`),
    KEY `answers_data_index` (`data`),
    CONSTRAINT `answers_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `answers_question_id_foreign`
        FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
