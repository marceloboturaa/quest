USE `u488847015_quest_baseDado`;

ALTER TABLE `questions`
    ADD COLUMN `source_name` VARCHAR(180) NULL DEFAULT NULL AFTER `true_false_answer`,
    ADD COLUMN `source_url` VARCHAR(500) NULL DEFAULT NULL AFTER `source_name`,
    ADD COLUMN `source_reference` VARCHAR(255) NULL DEFAULT NULL AFTER `source_url`;
