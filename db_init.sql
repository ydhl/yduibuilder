-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-12-04 06:42:41
-- 服务器版本： 8.0.26
-- PHP 版本： 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `ydecloud`
--

-- --------------------------------------------------------

--
-- 表的结构 `action`
--

CREATE TABLE `action` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `type` enum('output','redirect','popup','call','webapi','emit','mutation','closepopup','interval','validate','break') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'output',
  `redirect` varchar(2995) NOT NULL DEFAULT '',
  `redirect_type` enum('inside','outside','unset') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'unset',
  `popup_type` enum('page','alert','unset') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'unset',
  `popupPageId` varchar(45) NOT NULL DEFAULT '',
  `call_uiid` varchar(45) DEFAULT NULL,
  `input` text,
  `output` text,
  `bind_api_id` int NOT NULL DEFAULT '0',
  `popup_target` varchar(45) DEFAULT NULL,
  `popup_page_type` enum('page','popup','unset') NOT NULL DEFAULT 'unset',
  `bind_class` varchar(45) DEFAULT NULL,
  `bind_uuid` varchar(45) DEFAULT NULL,
  `bind_condition` enum('true','false') NOT NULL DEFAULT 'true' COMMENT '绑定的条件',
  `page_id` int NOT NULL,
  `emit_event_id` int DEFAULT NULL,
  `index` tinyint NOT NULL DEFAULT '0',
  `interval_duration` int UNSIGNED NOT NULL DEFAULT '0',
  `interval_delay` int NOT NULL DEFAULT '0',
  `interval_action` varchar(145) DEFAULT NULL,
  `interval_complete` varchar(145) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `activity`
--

CREATE TABLE `activity` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `project_id` int NOT NULL,
  `project_member_id` int NOT NULL,
  `content` text COMMENT '日志内容，可包含html',
  `type` varchar(45) DEFAULT NULL COMMENT '活动类型'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目活动日志表';

-- --------------------------------------------------------

--
-- 表的结构 `api_folder`
--

CREATE TABLE `api_folder` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL COMMENT '目录名',
  `api_folder_id` int DEFAULT NULL COMMENT '上级目录',
  `comment` varchar(145) DEFAULT NULL COMMENT '备注',
  `project_id` int NOT NULL,
  `index` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='api的目录';

-- --------------------------------------------------------

--
-- 表的结构 `code`
--

CREATE TABLE `code` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `target` varchar(45) DEFAULT NULL,
  `code` varchar(45) DEFAULT NULL,
  `expirein` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `file`
--

CREATE TABLE `file` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `file_name` varchar(145) DEFAULT NULL,
  `url` varchar(545) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `upload_date` datetime DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `function`
--

CREATE TABLE `function` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `module_id` int NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `desc` varchar(245) DEFAULT NULL,
  `screen` varchar(145) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='功能';

-- --------------------------------------------------------

--
-- 表的结构 `label`
--

CREATE TABLE `label` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) DEFAULT NULL COMMENT '标签名',
  `pinyin` varchar(200) DEFAULT NULL,
  `pinyin_sort` varchar(45) DEFAULT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `label_target`
--

CREATE TABLE `label_target` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `label_id` int NOT NULL,
  `target_class` varchar(45) DEFAULT NULL,
  `target_id` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `log`
--

CREATE TABLE `log` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `user_name` varchar(45) DEFAULT NULL COMMENT '用户名',
  `user_id` int DEFAULT NULL COMMENT '系统中的用户id',
  `action_time` datetime DEFAULT NULL COMMENT '操作时间',
  `action_name` varchar(45) DEFAULT NULL COMMENT '操作, 如新增用户',
  `request_method` varchar(45) DEFAULT NULL COMMENT '请求的方法，如post,get',
  `request_url` text COMMENT '访问地址',
  `client_info` text COMMENT '终端信息，如浏览器，操作系统',
  `client_ip` varchar(45) DEFAULT NULL COMMENT '终端ip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `log_column`
--

CREATE TABLE `log_column` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `column` varchar(45) DEFAULT NULL COMMENT '字段名',
  `old_value` text COMMENT '原值',
  `new_value` text,
  `db_type` enum('C','R','U','D') DEFAULT 'R' COMMENT '数据库操作类型',
  `table` varchar(45) DEFAULT NULL,
  `log_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `module`
--

CREATE TABLE `module` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `project_id` int NOT NULL,
  `desc` varchar(245) DEFAULT NULL,
  `folder` varchar(45) DEFAULT NULL COMMENT '模块存储目录名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目中的模块';

-- --------------------------------------------------------

--
-- 表的结构 `mutation`
--

CREATE TABLE `mutation` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mutation_from_uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mutation_data_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mutation_data_type` varchar(45) DEFAULT NULL,
  `action_id` int NOT NULL,
  `expression` text NOT NULL,
  `mutation_operator` varchar(45) NOT NULL DEFAULT '=' COMMENT '赋值操作符'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `option`
--

CREATE TABLE `option` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uuid` varchar(45) DEFAULT NULL,
  `option_name` varchar(45) DEFAULT NULL,
  `option_value` text,
  `is_deleted` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- 表的结构 `page`
--

CREATE TABLE `page` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) DEFAULT NULL COMMENT '页面名称',
  `config` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '页面的组成配置文件',
  `screen` varchar(145) DEFAULT NULL COMMENT '截屏地址',
  `module_id` int DEFAULT NULL,
  `file` varchar(45) DEFAULT NULL COMMENT '页面存储文件名',
  `url` varchar(145) NOT NULL DEFAULT '',
  `is_snapshoting` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在生成预览图',
  `function_id` int DEFAULT NULL,
  `last_member_id` int NOT NULL DEFAULT '-1' COMMENT '最新的保存者',
  `last_version_id` int NOT NULL DEFAULT '-1' COMMENT '最新一个版本',
  `page_type` enum('page','popup','master','subpage','component') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'page',
  `ref_page_id` int NOT NULL DEFAULT '0' COMMENT '当前页面引用的目标页面id',
  `create_user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `is_component` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是组件',
  `component_end_kind` enum('pc','mobile') NOT NULL DEFAULT 'pc' COMMENT '跨项目共享的终端类型',
  `component_uiid` varchar(45) NOT NULL DEFAULT '' COMMENT '作为组件的根元素id',
  `create_user_is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '作者是否删除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目中的页面';

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_api`
--

CREATE TABLE `page_bind_api` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `web_api_id` int NOT NULL,
  `name` varchar(45) NOT NULL COMMENT 'api名称',
  `method` varchar(45) NOT NULL COMMENT 'api方法',
  `path` varchar(45) NOT NULL COMMENT '请求路径',
  `input` text,
  `output` text,
  `requestBodyType` enum('none','form-data','x-www-form-urlencoded','json','xml','raw','binary','GraphQL','msgpack') NOT NULL DEFAULT 'form-data',
  `major` tinyint NOT NULL DEFAULT '0',
  `minor` smallint NOT NULL DEFAULT '0',
  `revision` smallint NOT NULL DEFAULT '1',
  `version` int NOT NULL DEFAULT '1',
  `bind_class` varchar(45) DEFAULT NULL COMMENT '调用api的对象',
  `bind_uuid` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='绑定的接口';

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_api_action`
--

CREATE TABLE `page_bind_api_action` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `from_class` varchar(45) NOT NULL COMMENT '绑定关联的对象',
  `from_uuid` varchar(45) NOT NULL,
  `output_data_id` varchar(45) DEFAULT NULL,
  `page_id` int NOT NULL,
  `mode` enum('setting','code') NOT NULL DEFAULT 'setting',
  `code` text,
  `expression` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='页面绑定的行为';

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_data`
--

CREATE TABLE `page_bind_data` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `page_id` int NOT NULL,
  `type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'string',
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `title` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `comment` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `data_from` enum('page','path','query') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'page',
  `defaultValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `deprecated` tinyint(1) NOT NULL DEFAULT '0',
  `nullable` tinyint(1) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `min` int DEFAULT NULL,
  `max` int DEFAULT NULL,
  `pattern` varchar(945) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '验证模式',
  `enumValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '枚举值列表',
  `sample` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '样例',
  `action` enum('ReadOnly','WriteOnly','ReadWrite') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'ReadWrite' COMMENT '动作',
  `mock` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `initLength` tinyint UNSIGNED DEFAULT NULL,
  `validRegular` varchar(145) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '数据验证正则表达式字符串',
  `validRule` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '默认的验证规则',
  `invalidMsg` varchar(145) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='绑定的数据';

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_event`
--

CREATE TABLE `page_bind_event` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `event` varchar(45) NOT NULL COMMENT '绑定的事件',
  `uiid` varchar(445) DEFAULT NULL COMMENT '绑定的uiid',
  `uicomponent_event_id` int DEFAULT NULL,
  `desc` varchar(45) DEFAULT NULL,
  `modifier` varchar(245) DEFAULT NULL COMMENT '事件修饰符',
  `timeout` int NOT NULL DEFAULT '0' COMMENT '防抖截流的周期',
  `immediate` tinyint NOT NULL DEFAULT '0',
  `custom_key` varchar(45) DEFAULT NULL COMMENT '自定义按键key'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='页面事件绑定';

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_io`
--

CREATE TABLE `page_bind_io` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `type` enum('in','out') NOT NULL DEFAULT 'in',
  `data_id` varchar(95) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `uiid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `from_class` varchar(45) NOT NULL,
  `from_uuid` varchar(45) NOT NULL,
  `output_as` varchar(45) DEFAULT NULL COMMENT '输出绑定到ui到哪个属性（label，value）上',
  `bound_as` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_state`
--

CREATE TABLE `page_bind_state` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `page_id` int NOT NULL,
  `uiid` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `state_type` enum('activated','disabled','hidden','pseudo','custom') COLLATE utf8mb4_general_ci NOT NULL,
  `state_name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `style` text COLLATE utf8mb4_general_ci COMMENT '样式内容',
  `style_id` varchar(145) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Selector',
  `expression` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_style`
--

CREATE TABLE `page_bind_style` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int DEFAULT NULL,
  `style_id` int NOT NULL,
  `uiid` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `page_bind_variable`
--

CREATE TABLE `page_bind_variable` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `from_page_id` int NOT NULL,
  `from_class` varchar(45) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'data_id的来源',
  `from_uuid` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `from_expression` text COLLATE utf8mb4_general_ci NOT NULL,
  `to_page_id` int NOT NULL,
  `to_class` varchar(45) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'data_id的来源',
  `to_uuid` varchar(45) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Root data uuid',
  `to_data_id` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `to_data_path` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `page_user`
--

CREATE TABLE `page_user` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `member_id` int NOT NULL,
  `fd` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='当前打开页面的用户';

-- --------------------------------------------------------

--
-- 表的结构 `page_version`
--

CREATE TABLE `page_version` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL,
  `project_member_id` int NOT NULL,
  `config` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '页面的组成配置文件',
  `screen` varchar(145) DEFAULT NULL COMMENT '截屏地址',
  `index` int NOT NULL,
  `message` varchar(999) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='页面版本';

-- --------------------------------------------------------

--
-- 表的结构 `project`
--

CREATE TABLE `project` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `last_page_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的页面id',
  `last_function_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的功能',
  `desc` varchar(145) DEFAULT NULL COMMENT '简要描述',
  `home_page_id` int NOT NULL DEFAULT '0' COMMENT '默认主页id',
  `end_kind` enum('pc','mobile') NOT NULL DEFAULT 'pc' COMMENT '终端类型'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户的项目';

-- --------------------------------------------------------

--
-- 表的结构 `project_member`
--

CREATE TABLE `project_member` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `role` enum('admin','developer','reporter','guest') NOT NULL COMMENT '角色',
  `is_creater` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是项目的创建者',
  `last_page_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的页面id',
  `last_function_id` int NOT NULL DEFAULT '0' COMMENT '最近编辑的功能',
  `is_invited` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否正在邀请'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目成员';

-- --------------------------------------------------------

--
-- 表的结构 `project_setting`
--

CREATE TABLE `project_setting` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `project_id` int NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='项目设置';

-- --------------------------------------------------------

--
-- 表的结构 `style`
--

CREATE TABLE `style` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `class_name` varchar(45) DEFAULT NULL,
  `meta` text,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='共享的样式class';

-- --------------------------------------------------------

--
-- 表的结构 `uicomponent_event`
--

CREATE TABLE `uicomponent_event` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `page_id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci NOT NULL COMMENT '事件名',
  `args` text COLLATE utf8mb4_general_ci COMMENT '输入参数',
  `desc` varchar(145) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='组件定义的事件';

-- --------------------------------------------------------

--
-- 表的结构 `uicomponent_instance`
--

CREATE TABLE `uicomponent_instance` (
  `id` int NOT NULL COMMENT 'Ui 组件实例',
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `page_id` int NOT NULL COMMENT '实例页面',
  `uicomponent_page_id` int NOT NULL COMMENT '组件',
  `instance_uuid` varchar(45) NOT NULL COMMENT '实例uiid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) NOT NULL,
  `openid` varchar(45) DEFAULT NULL,
  `fromsite` varchar(145) DEFAULT NULL,
  `avatar` varchar(145) DEFAULT NULL,
  `nickname` varchar(45) DEFAULT NULL,
  `cellphone` varchar(45) NOT NULL DEFAULT '',
  `sso_token` varchar(45) DEFAULT NULL,
  `sso_token_expire` datetime DEFAULT NULL,
  `phone_region` varchar(45) NOT NULL DEFAULT '',
  `email` varchar(45) NOT NULL DEFAULT '',
  `login_pwd` varchar(45) NOT NULL DEFAULT '',
  `user_type` varchar(45) NOT NULL DEFAULT 'individual',
  `account_duedate` date DEFAULT NULL COMMENT '到期时间，null表示不过期',
  `account_setting` varchar(1000) DEFAULT NULL COMMENT '账户设置',
  `account_type` varchar(45) NOT NULL DEFAULT 'base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `validate_data`
--

CREATE TABLE `validate_data` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `action_id` int NOT NULL,
  `from_class` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `from_uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `data_uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `web_api`
--

CREATE TABLE `web_api` (
  `id` int NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'api名称',
  `method` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'api方法',
  `path` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '请求路径',
  `status` enum('develop','test','deprecated','released') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '状态',
  `api_folder_id` int DEFAULT NULL COMMENT '目录',
  `project_member_id` int DEFAULT NULL COMMENT '负责人',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT '说明',
  `requestBodyType` enum('none','form-data','x-www-form-urlencoded','json','xml','raw','binary','GraphQL','msgpack') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'form-data',
  `project_id` int NOT NULL,
  `index` tinyint NOT NULL DEFAULT '0',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `major` tinyint NOT NULL DEFAULT '0',
  `minor` smallint NOT NULL DEFAULT '0',
  `revision` smallint NOT NULL DEFAULT '1',
  `version` int NOT NULL DEFAULT '1',
  `commit_msg` varchar(145) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_action_page1_idx` (`page_id`),
  ADD KEY `fk_action_uicomponent_event1_idx` (`emit_event_id`),
  ADD KEY `bind_class` (`bind_class`,`bind_uuid`),
  ADD KEY `popupPageId` (`popupPageId`);

--
-- 表的索引 `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_project1_idx` (`project_id`),
  ADD KEY `fk_activity_project_member1_idx` (`project_member_id`);

--
-- 表的索引 `api_folder`
--
ALTER TABLE `api_folder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_api_folder_api_folder1_idx` (`api_folder_id`),
  ADD KEY `fk_api_folder_project1_idx` (`project_id`);

--
-- 表的索引 `code`
--
ALTER TABLE `code`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_file_project1_idx` (`project_id`);

--
-- 表的索引 `function`
--
ALTER TABLE `function`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_function_module1_idx` (`module_id`);

--
-- 表的索引 `label`
--
ALTER TABLE `label`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_label_project1_idx` (`project_id`);

--
-- 表的索引 `label_target`
--
ALTER TABLE `label_target`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category2_label1_idx` (`label_id`);

--
-- 表的索引 `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `log_column`
--
ALTER TABLE `log_column`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_column_log1_idx` (`log_id`);

--
-- 表的索引 `module`
--
ALTER TABLE `module`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_module_project_idx` (`project_id`);

--
-- 表的索引 `mutation`
--
ALTER TABLE `mutation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mutation_action1_idx` (`action_id`);

--
-- 表的索引 `option`
--
ALTER TABLE `option`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_module1_idx` (`module_id`),
  ADD KEY `fk_page_function1_idx` (`function_id`),
  ADD KEY `fk_page_project1_idx` (`project_id`),
  ADD KEY `fk_page_user1` (`create_user_id`);

--
-- 表的索引 `page_bind_api`
--
ALTER TABLE `page_bind_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_api_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_api_action`
--
ALTER TABLE `page_bind_api_action`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_action_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_data`
--
ALTER TABLE `page_bind_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_data_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_event`
--
ALTER TABLE `page_bind_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_event_page1_idx` (`page_id`),
  ADD KEY `fk_page_bind_event_uicomponent_event1` (`uicomponent_event_id`);

--
-- 表的索引 `page_bind_io`
--
ALTER TABLE `page_bind_io`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_io_page1_idx` (`page_id`),
  ADD KEY `idx_from` (`from_uuid`,`from_class`);

--
-- 表的索引 `page_bind_state`
--
ALTER TABLE `page_bind_state`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category2_page1_idx` (`page_id`);

--
-- 表的索引 `page_bind_style`
--
ALTER TABLE `page_bind_style`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_style_page1_idx` (`page_id`),
  ADD KEY `fk_page_bind_style_style1_idx` (`style_id`),
  ADD KEY `uiid` (`uiid`);

--
-- 表的索引 `page_bind_variable`
--
ALTER TABLE `page_bind_variable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_bind_io_page1_idx` (`from_page_id`),
  ADD KEY `idx_from` (`from_class`),
  ADD KEY `fk_page_bind_variable_page1_idx` (`to_page_id`);

--
-- 表的索引 `page_user`
--
ALTER TABLE `page_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category_page1_idx` (`page_id`),
  ADD KEY `fk_page_user_project_member1_idx` (`member_id`);

--
-- 表的索引 `page_version`
--
ALTER TABLE `page_version`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_page_version_page1_idx` (`page_id`),
  ADD KEY `fk_page_version_project_member1_idx` (`project_member_id`);

--
-- 表的索引 `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `project_member`
--
ALTER TABLE `project_member`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_member_user1_idx` (`user_id`),
  ADD KEY `fk_project_member_project1_idx` (`project_id`);

--
-- 表的索引 `project_setting`
--
ALTER TABLE `project_setting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project_setting_project1_idx` (`project_id`);

--
-- 表的索引 `style`
--
ALTER TABLE `style`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_style_project1_idx` (`project_id`);

--
-- 表的索引 `uicomponent_event`
--
ALTER TABLE `uicomponent_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_uicomponent_event_page1_idx` (`page_id`);

--
-- 表的索引 `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_uicomponent_instance_page1_idx` (`page_id`),
  ADD KEY `fk_uicomponent_instance_page2_idx` (`uicomponent_page_id`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `validate_data`
--
ALTER TABLE `validate_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_validate_data_action1_idx` (`action_id`);

--
-- 表的索引 `web_api`
--
ALTER TABLE `web_api`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_web_api_api_folder1_idx` (`api_folder_id`),
  ADD KEY `fk_web_api_project_member1_idx` (`project_member_id`),
  ADD KEY `fk_web_api_project1_idx` (`project_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `action`
--
ALTER TABLE `action`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `api_folder`
--
ALTER TABLE `api_folder`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `code`
--
ALTER TABLE `code`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `file`
--
ALTER TABLE `file`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `function`
--
ALTER TABLE `function`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `label`
--
ALTER TABLE `label`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `label_target`
--
ALTER TABLE `label_target`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `log`
--
ALTER TABLE `log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `log_column`
--
ALTER TABLE `log_column`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `module`
--
ALTER TABLE `module`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mutation`
--
ALTER TABLE `mutation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `option`
--
ALTER TABLE `option`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page`
--
ALTER TABLE `page`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_api`
--
ALTER TABLE `page_bind_api`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_api_action`
--
ALTER TABLE `page_bind_api_action`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_data`
--
ALTER TABLE `page_bind_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_event`
--
ALTER TABLE `page_bind_event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_io`
--
ALTER TABLE `page_bind_io`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_state`
--
ALTER TABLE `page_bind_state`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_style`
--
ALTER TABLE `page_bind_style`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_bind_variable`
--
ALTER TABLE `page_bind_variable`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_user`
--
ALTER TABLE `page_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `page_version`
--
ALTER TABLE `page_version`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `project`
--
ALTER TABLE `project`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `project_member`
--
ALTER TABLE `project_member`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `project_setting`
--
ALTER TABLE `project_setting`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `style`
--
ALTER TABLE `style`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `uicomponent_event`
--
ALTER TABLE `uicomponent_event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'Ui 组件实例';

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `validate_data`
--
ALTER TABLE `validate_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `web_api`
--
ALTER TABLE `web_api`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `action`
--
ALTER TABLE `action`
  ADD CONSTRAINT `fk_action_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_action_uicomponent_event1` FOREIGN KEY (`emit_event_id`) REFERENCES `uicomponent_event` (`id`);

--
-- 限制表 `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `fk_activity_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_activity_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `api_folder`
--
ALTER TABLE `api_folder`
  ADD CONSTRAINT `fk_api_folder_api_folder1` FOREIGN KEY (`api_folder_id`) REFERENCES `api_folder` (`id`),
  ADD CONSTRAINT `fk_api_folder_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `fk_file_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `function`
--
ALTER TABLE `function`
  ADD CONSTRAINT `fk_function_module1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`);

--
-- 限制表 `label`
--
ALTER TABLE `label`
  ADD CONSTRAINT `fk_label_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `label_target`
--
ALTER TABLE `label_target`
  ADD CONSTRAINT `fk_category2_label1` FOREIGN KEY (`label_id`) REFERENCES `label` (`id`);

--
-- 限制表 `log_column`
--
ALTER TABLE `log_column`
  ADD CONSTRAINT `fk_log_column_log1` FOREIGN KEY (`log_id`) REFERENCES `log` (`id`);

--
-- 限制表 `module`
--
ALTER TABLE `module`
  ADD CONSTRAINT `fk_module_project` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `mutation`
--
ALTER TABLE `mutation`
  ADD CONSTRAINT `fk_mutation_action1` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`);

--
-- 限制表 `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `fk_page_function1` FOREIGN KEY (`function_id`) REFERENCES `function` (`id`),
  ADD CONSTRAINT `fk_page_module1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`),
  ADD CONSTRAINT `fk_page_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_page_user1` FOREIGN KEY (`create_user_id`) REFERENCES `user` (`id`);

--
-- 限制表 `page_bind_api`
--
ALTER TABLE `page_bind_api`
  ADD CONSTRAINT `fk_page_bind_api_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_api_action`
--
ALTER TABLE `page_bind_api_action`
  ADD CONSTRAINT `fk_page_bind_action_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_data`
--
ALTER TABLE `page_bind_data`
  ADD CONSTRAINT `fk_page_bind_data_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_event`
--
ALTER TABLE `page_bind_event`
  ADD CONSTRAINT `fk_page_bind_event_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_event_uicomponent_event1` FOREIGN KEY (`uicomponent_event_id`) REFERENCES `uicomponent_event` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- 限制表 `page_bind_io`
--
ALTER TABLE `page_bind_io`
  ADD CONSTRAINT `fk_page_bind_io_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_state`
--
ALTER TABLE `page_bind_state`
  ADD CONSTRAINT `fk_category2_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_bind_style`
--
ALTER TABLE `page_bind_style`
  ADD CONSTRAINT `fk_page_bind_style_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_style_style1` FOREIGN KEY (`style_id`) REFERENCES `style` (`id`);

--
-- 限制表 `page_bind_variable`
--
ALTER TABLE `page_bind_variable`
  ADD CONSTRAINT `fk_page_bind_io_page10` FOREIGN KEY (`from_page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_bind_variable_page1` FOREIGN KEY (`to_page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `page_user`
--
ALTER TABLE `page_user`
  ADD CONSTRAINT `fk_category_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_user_project_member1` FOREIGN KEY (`member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `page_version`
--
ALTER TABLE `page_version`
  ADD CONSTRAINT `fk_page_version_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_page_version_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);

--
-- 限制表 `project_member`
--
ALTER TABLE `project_member`
  ADD CONSTRAINT `fk_project_member_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_project_member_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- 限制表 `project_setting`
--
ALTER TABLE `project_setting`
  ADD CONSTRAINT `fk_project_setting_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `style`
--
ALTER TABLE `style`
  ADD CONSTRAINT `fk_style_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`);

--
-- 限制表 `uicomponent_event`
--
ALTER TABLE `uicomponent_event`
  ADD CONSTRAINT `fk_uicomponent_event_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `uicomponent_instance`
--
ALTER TABLE `uicomponent_instance`
  ADD CONSTRAINT `fk_uicomponent_instance_page1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`),
  ADD CONSTRAINT `fk_uicomponent_instance_page2` FOREIGN KEY (`uicomponent_page_id`) REFERENCES `page` (`id`);

--
-- 限制表 `validate_data`
--
ALTER TABLE `validate_data`
  ADD CONSTRAINT `fk_validate_data_action1` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`);

--
-- 限制表 `web_api`
--
ALTER TABLE `web_api`
  ADD CONSTRAINT `fk_web_api_api_folder1` FOREIGN KEY (`api_folder_id`) REFERENCES `api_folder` (`id`),
  ADD CONSTRAINT `fk_web_api_project1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `fk_web_api_project_member1` FOREIGN KEY (`project_member_id`) REFERENCES `project_member` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
