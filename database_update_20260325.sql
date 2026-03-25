USE `u488847015_quest_baseDado`;

ALTER TABLE `questions`
    MODIFY `drawing_size` ENUM('small', 'medium', 'large', 'custom') NULL DEFAULT NULL;

ALTER TABLE `questions`
    ADD COLUMN `drawing_height_px` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `drawing_size`;
