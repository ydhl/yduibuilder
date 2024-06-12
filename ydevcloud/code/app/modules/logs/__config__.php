<?php
namespace app\logs;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
use yangzie\YZE_DBAImpl;

/**
 *
 * 公共日志模块
 *
 * 对访问的action记录日志，并记录这次访问请求中增删改查了那些表及其字段，
 * 通过在控制器的action上使用注解actionName 来指定当前操作的描述，
 * 默认所有action都会记录日志，如果要忽略日志，在action的注释中用igonreLog来忽略
 *
 * @version $Id$
 * @package Logs
 */
class Logs_Module extends YZE_Base_Module{
    public $auths = "*";
    public $no_auths = array();
    public function check()
    {
        // 判断如果表不存在，则创建日志表
        YZE_DBAImpl::get_instance()->native_Query("
        CREATE TABLE IF NOT EXISTS `log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` TINYINT NOT NULL DEFAULT 0,
  `uuid` VARCHAR(45) NOT NULL,
  `user_name` VARCHAR(45) NULL COMMENT '用户名',
  `user_id` INT NULL COMMENT '系统中的用户id',
  `action_time` DATETIME NULL COMMENT '操作时间',
  `action_name` VARCHAR(45) NULL COMMENT '操作, 如新增用户',
  `request_method` VARCHAR(45) NULL COMMENT '请求的方法，如post,get',
  `request_url` TEXT NULL COMMENT '访问地址',
  `client_info` TEXT NULL COMMENT '终端信息，如浏览器，操作系统',
  `client_ip` VARCHAR(45) NULL COMMENT '终端ip',
  PRIMARY KEY (`id`))  ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;
  CREATE TABLE IF NOT EXISTS `log_column` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` TINYINT NOT NULL DEFAULT 0,
  `uuid` VARCHAR(45) NOT NULL,
  `column` VARCHAR(45) NULL COMMENT '字段名',
  `old_value` TEXT NULL COMMENT '原值',
  `new_value` TEXT NULL,
  `db_type` ENUM('C', 'R', 'U', 'D') NULL DEFAULT 'R' COMMENT '数据库操作类型',
  `table` VARCHAR(45) NULL,
  `log_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_log_column_log1_idx` (`log_id` ASC),
  CONSTRAINT `fk_log_column_log1`
    FOREIGN KEY (`log_id`)
    REFERENCES `log` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)  ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;");
    }

    protected function config(){
        return array(
            'name'=>'Logs',
            'routers' => array(
                'logs/remove'	=> array(
                    'controller'	=> 'index',
                    'args'	=> array(
                        'action'=>'remove'
                    ),
                ),
                'logs/(?P<uuid>.+)'	=> array(
                'controller'	=> 'index',
                    'args'	=> array(
                        'action'=>'detail'
                    ),
                )
            )
        );
    }

    public function js_bundle($bundle)
    {
        // TODO: Implement js_bundle() method.
    }

    public function css_bundle($bundle)
    {
        // TODO: Implement css_bundle() method.
    }
}
?>
