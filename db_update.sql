ALTER TABLE `page_bind_data` CHANGE `type` `type` VARCHAR(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'string';
ALTER TABLE `action` CHANGE `type` `type` ENUM('output','redirect','popup','call','webapi','emit','mutation','closepopup','interval') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'output';
ALTER TABLE `action` ADD `interval_duration` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `index`, ADD `interval_delay` SMALLINT NOT NULL DEFAULT '0' AFTER `interval_duration`, ADD `interval_action` VARCHAR(145) NULL AFTER `interval_delay`, ADD `interval_complete` VARCHAR(145) NULL AFTER `interval_action`;
ALTER TABLE `action` CHANGE `interval_duration` `interval_duration` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `action` CHANGE `interval_delay` `interval_delay` INT NOT NULL DEFAULT '0';

ALTER TABLE `mutation` 
ADD COLUMN `mutation_operator` VARCHAR(45) NOT NULL DEFAULT '=' COMMENT '赋值操作符' AFTER `expression`;

ALTER TABLE `page` CHANGE `config` `config` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '页面的组成配置文件';
ALTER TABLE `page_version` CHANGE `config` `config` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '页面的组成配置文件';
ALTER TABLE `action` 
ADD COLUMN `bind_condition` ENUM('true', 'false') NOT NULL DEFAULT 'true' COMMENT '绑定的条件' AFTER `bind_uuid`;
ALTER TABLE `page_bind_data` 
ADD COLUMN `validRegular` VARCHAR(145) NULL COMMENT '数据验证正则表达式字符串' AFTER `initLength`,
ADD COLUMN `validRule` VARCHAR(45) NULL COMMENT '默认的验证规则' AFTER `validRegular`;
ALTER TABLE `action` 
CHANGE COLUMN `type` `type` ENUM('output', 'redirect', 'popup', 'call', 'webapi', 'emit', 'mutation', 'closepopup', 'interval', 'validate') CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT 'output' ;

CREATE TABLE IF NOT EXISTS `validate_data` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` TINYINT NOT NULL DEFAULT 0,
  `uuid` VARCHAR(45) NOT NULL,
  `action_id` INT NOT NULL,
  `from_class` VARCHAR(45) NULL,
  `from_uuid` VARCHAR(45) NULL,
  `data_uuid` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_validate_data_action1_idx` (`action_id` ASC) VISIBLE,
  CONSTRAINT `fk_validate_data_action1`
    FOREIGN KEY (`action_id`)
    REFERENCES `action` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

ALTER TABLE `ydecloud`.`action` 
CHANGE COLUMN `type` `type` ENUM('output', 'redirect', 'popup', 'call', 'webapi', 'emit', 'mutation', 'closepopup', 'interval', 'validate', 'break') CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT 'output' ;


