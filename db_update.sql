ALTER TABLE `page_bind_data` CHANGE `type` `type` VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'string';
ALTER TABLE `action` CHANGE `type` `type` ENUM('output','redirect','popup','call','webapi','emit','mutation','closepopup','interval') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'output';
ALTER TABLE `action` ADD `interval_duration` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `index`, ADD `interval_delay` SMALLINT NOT NULL DEFAULT '0' AFTER `interval_duration`, ADD `interval_action` VARCHAR(145) NULL AFTER `interval_delay`, ADD `interval_complete` VARCHAR(145) NULL AFTER `interval_action`;
ALTER TABLE `action` CHANGE `interval_duration` `interval_duration` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `action` CHANGE `interval_delay` `interval_delay` INT NOT NULL DEFAULT '0';

ALTER TABLE `ydecloud`.`mutation` 
ADD COLUMN `mutation_operator` VARCHAR(45) NOT NULL DEFAULT '=' COMMENT '赋值操作符' AFTER `expression`;

ALTER TABLE `page` CHANGE `config` `config` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '页面的组成配置文件';
ALTER TABLE `page_version` CHANGE `config` `config` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '页面的组成配置文件';